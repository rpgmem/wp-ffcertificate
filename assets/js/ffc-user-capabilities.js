/**
 * FFC User Capabilities
 *
 * Bulk capability toggle buttons on the user profile page.
 *
 * @since 4.6.0
 * @package FreeFormCertificate\Admin
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        var certCaps = $('input[name^="ffc_cap_"][name*="certificate"]');
        var apptCaps = $('input[name^="ffc_cap_ffc_"][name*="appointment"]');
        var allCaps  = $('input[name^="ffc_cap_"]');

        $('#ffc-grant-all-caps').on('click', function() {
            allCaps.prop('checked', true);
        });

        $('#ffc-revoke-all-caps').on('click', function() {
            allCaps.prop('checked', false);
        });

        $('#ffc-grant-certificates').on('click', function() {
            allCaps.prop('checked', false);
            certCaps.prop('checked', true);
        });

        $('#ffc-grant-appointments').on('click', function() {
            allCaps.prop('checked', false);
            apptCaps.prop('checked', true);
        });
    });

})(jQuery);
