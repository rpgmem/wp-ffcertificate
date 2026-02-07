/**
 * FFC Audience Admin
 *
 * Admin-side scripts for the audience scheduling system.
 *
 * @since 4.5.0
 * @package FreeFormCertificate\Audience
 */

(function($) {
    'use strict';

    const FFCAudienceAdmin = {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Day row: toggle time inputs when "closed" checkbox changes
            $(document).on('change', '.ffc-day-row input[type="checkbox"]', function() {
                var row = $(this).closest('.ffc-day-row');
                var inputs = row.find('input[type="time"]');
                inputs.prop('disabled', $(this).is(':checked'));
            });

            // User search (audience group member management)
            this.initUserSearch();

            // Cascading environment filter on bookings page
            this.initEnvironmentFilter();
        },

        /**
         * Live user search with autocomplete for audience group members
         */
        initUserSearch: function() {
            var $searchInput = $('#user_search');
            if (!$searchInput.length) {
                return;
            }

            var selectedUsers = {};
            var searchTimeout;
            var nonce = typeof ffcAudienceAdmin !== 'undefined' ? ffcAudienceAdmin.nonce : '';

            $searchInput.on('input', function() {
                clearTimeout(searchTimeout);
                var query = $(this).val();
                if (query.length < 2) {
                    $('#user_results').removeClass('active').empty();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: ajaxurl,
                        data: {
                            action: 'ffc_search_users',
                            query: query,
                            nonce: nonce
                        },
                        success: function(response) {
                            if (response.success && response.data.length > 0) {
                                var html = '';
                                response.data.forEach(function(user) {
                                    if (!selectedUsers[user.id]) {
                                        html += '<div class="ffc-user-result" data-id="' + user.id + '" data-name="' + user.name + '">' + user.name + ' (' + user.email + ')</div>';
                                    }
                                });
                                $('#user_results').html(html).addClass('active');
                            } else {
                                $('#user_results').removeClass('active').empty();
                            }
                        }
                    });
                }, 300);
            });

            $(document).on('click', '.ffc-user-result', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                selectedUsers[id] = name;
                updateSelectedUsers();
                $('#user_results').removeClass('active').empty();
                $searchInput.val('');
            });

            $(document).on('click', '.ffc-selected-user .remove', function() {
                var id = $(this).data('id');
                delete selectedUsers[id];
                updateSelectedUsers();
            });

            function updateSelectedUsers() {
                var html = '';
                var ids = [];
                for (var id in selectedUsers) {
                    html += '<span class="ffc-selected-user">' + selectedUsers[id] + '<span class="remove" data-id="' + id + '">&times;</span></span>';
                    ids.push(id);
                }
                $('#selected_users').html(html);
                $('#selected_user_ids').val(ids.join(','));
            }
        },

        /**
         * Cascading schedule → environment filter on bookings page
         */
        initEnvironmentFilter: function() {
            var $scheduleSelect = $('#filter-schedule');
            var $environmentSelect = $('#filter-environment');
            if (!$scheduleSelect.length || !$environmentSelect.length) {
                return;
            }

            var strings = typeof ffcAudienceAdmin !== 'undefined' ? ffcAudienceAdmin.strings : {};
            var adminNonce = typeof ffcAudienceAdmin !== 'undefined' ? ffcAudienceAdmin.adminNonce : '';
            var allEnvironmentsText = strings.allEnvironments || 'All Environments';
            var loadingText = strings.loading || 'Loading...';

            $scheduleSelect.on('change', function() {
                var scheduleId = $(this).val();

                if (!scheduleId) {
                    $environmentSelect.html('<option value="">' + allEnvironmentsText + '</option>');
                    return;
                }

                $environmentSelect.html('<option value="">' + loadingText + '</option>');

                $.ajax({
                    url: ajaxurl,
                    type: 'GET',
                    data: {
                        action: 'ffc_audience_get_environments',
                        schedule_id: scheduleId,
                        nonce: adminNonce
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            var html = '<option value="">' + allEnvironmentsText + '</option>';
                            $.each(response.data, function(i, env) {
                                html += '<option value="' + env.id + '">' + $('<div/>').text(env.name).html() + '</option>';
                            });
                            $environmentSelect.html(html);
                        } else {
                            $environmentSelect.html('<option value="">' + allEnvironmentsText + '</option>');
                        }
                    },
                    error: function() {
                        $environmentSelect.html('<option value="">' + allEnvironmentsText + '</option>');
                    }
                });
            });
        }
    };

    $(document).ready(function() {
        FFCAudienceAdmin.init();
    });

})(jQuery);
