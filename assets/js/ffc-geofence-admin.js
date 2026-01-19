/**
 * FFC Geofence Admin
 *
 * JavaScript for geofence metabox in form editor
 *
 * v3.1.0: Standardized to use event delegation pattern
 *
 * @since 3.0.0
 */

jQuery(document).ready(function($) {
    // Tab switching - Using event delegation
    $(document).on('click', '.ffc-geo-tab-btn', function() {
        var tab = $(this).data('tab');
        $('.ffc-geo-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.ffc-geo-tab-content').removeClass('active');
        $('#ffc-tab-' + tab).addClass('active');
    });

    // DateTime restrictions - Enable/Disable fields based on checkbox
    function toggleDateTimeFields() {
        var enabled = $('input[name="ffc_geofence[datetime_enabled]"]').is(':checked');
        $('#ffc-tab-datetime input[type="date"], #ffc-tab-datetime input[type="time"], #ffc-tab-datetime select, #ffc-tab-datetime textarea, #ffc-tab-datetime input[type="radio"]')
            .not('input[name="ffc_geofence[datetime_enabled]"]')
            .prop('disabled', !enabled)
            .closest('tr').css('opacity', enabled ? '1' : '0.5');

        // Also check if time mode row should be visible
        toggleTimeModeRow();
    }

    // Using event delegation for datetime enabled checkbox
    $(document).on('change', 'input[name="ffc_geofence[datetime_enabled]"]', toggleDateTimeFields);
    toggleDateTimeFields(); // Run on load

    // Show/hide time mode row based on date range
    function toggleTimeModeRow() {
        var dateStart = $('input[name="ffc_geofence[date_start]"]').val();
        var dateEnd = $('input[name="ffc_geofence[date_end]"]').val();

        // Only show time mode option if different dates are set
        if (dateStart && dateEnd && dateStart !== dateEnd) {
            $('#ffc-time-mode-row').slideDown(200);
        } else {
            $('#ffc-time-mode-row').slideUp(200);
        }
    }

    // Using event delegation for date changes
    $(document).on('change', 'input[name="ffc_geofence[date_start]"], input[name="ffc_geofence[date_end]"]', toggleTimeModeRow);
    toggleTimeModeRow(); // Run on load

    // Geolocation restrictions - Enable/Disable fields based on checkbox
    function toggleGeoFields() {
        var enabled = $('input[name="ffc_geofence[geo_enabled]"]').is(':checked');
        $('#ffc-tab-geolocation input[type="checkbox"], #ffc-tab-geolocation textarea, #ffc-tab-geolocation select')
            .not('input[name="ffc_geofence[geo_enabled]"]')
            .prop('disabled', !enabled)
            .closest('tr').css('opacity', enabled ? '1' : '0.5');

        // If geolocation is enabled, ensure at least one method is selected
        if (enabled) {
            validateGeoMethods();
        }
    }

    // Using event delegation for geo enabled checkbox
    $(document).on('change', 'input[name="ffc_geofence[geo_enabled]"]', function() {
        toggleGeoFields();

        // When geolocation is enabled, validate methods
        if ($(this).is(':checked')) {
            validateGeoMethods();
        }
    });
    toggleGeoFields(); // Run on load

    // Validate that at least GPS or IP is enabled when geolocation is active
    function validateGeoMethods() {
        var geoEnabled = $('input[name="ffc_geofence[geo_enabled]"]').is(':checked');
        var gpsEnabled = $('input[name="ffc_geofence[geo_gps_enabled]"]').is(':checked');
        var ipEnabled = $('input[name="ffc_geofence[geo_ip_enabled]"]').is(':checked');

        if (geoEnabled && !gpsEnabled && !ipEnabled) {
            // Auto-enable GPS as default
            $('input[name="ffc_geofence[geo_gps_enabled]"]').prop('checked', true);
        }
    }

    // Prevent unchecking both GPS and IP when geolocation is enabled - Using event delegation
    $(document).on('change', 'input[name="ffc_geofence[geo_gps_enabled]"], input[name="ffc_geofence[geo_ip_enabled]"]', function() {
        var geoEnabled = $('input[name="ffc_geofence[geo_enabled]"]').is(':checked');
        var gpsEnabled = $('input[name="ffc_geofence[geo_gps_enabled]"]').is(':checked');
        var ipEnabled = $('input[name="ffc_geofence[geo_ip_enabled]"]').is(':checked');

        if (geoEnabled && !gpsEnabled && !ipEnabled) {
            alert(ffc_geofence_admin.alert_message);
            $(this).prop('checked', true);
        }
    });
});
