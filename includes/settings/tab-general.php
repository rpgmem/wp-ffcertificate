<?php
/**
 * General Settings Tab View
 * 
 * @package FFC
 * @since 2.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get option helper
$get_option = function( $key, $default = '' ) {
    $settings = get_option( 'ffc_settings', array() );
    return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
};

// Date format options
$date_formats = array(
    'Y-m-d H:i:s'           => '2026-01-04 15:30:45 (YYYY-MM-DD HH:MM:SS)',
    'Y-m-d'                 => '2026-01-04 (YYYY-MM-DD)',
    'd/m/Y'                 => '04/01/2026 (DD/MM/YYYY)',
    'd/m/Y H:i'             => '04/01/2026 15:30 (DD/MM/YYYY HH:MM)',
    'd/m/Y H:i:s'           => '04/01/2026 15:30:45 (DD/MM/YYYY HH:MM:SS)',
    'm/d/Y'                 => '01/04/2026 (MM/DD/YYYY)',
    'F j, Y'                => 'January 4, 2026 (Month Day, Year)',
    'j \d\e F \d\e Y'       => '4 de Janeiro de 2026 (Dia de Mês de Ano)',
    'd \d\e F \d\e Y'       => '04 de Janeiro de 2026 (DD de Mês de Ano)',
    'l, j \d\e F \d\e Y'    => 'Sábado, 4 de Janeiro de 2026 (Dia da semana, Dia de Mês de Ano)',
    'custom'                => __( 'Custom Format', 'ffc' )
);

$current_format = $get_option( 'date_format', 'F j, Y' );
$custom_format = $get_option( 'date_format_custom', '' );

?>
<form method="post">
    <?php wp_nonce_field( 'ffc_settings_action', 'ffc_settings_nonce' ); ?>
    <input type="hidden" name="_ffc_tab" value="general">
    
    <h2><?php _e( 'General Settings', 'ffc' ); ?></h2>
    
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Auto-delete (days)', 'ffc' ); ?></th>
            <td>
                <input type="number" name="ffc_settings[cleanup_days]" value="<?php echo esc_attr( $get_option( 'cleanup_days' ) ); ?>">
                <p class="description"><?php esc_html_e( 'Files removed after X days. Set to 0 to disable.', 'ffc' ); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><?php esc_html_e( 'Date Format', 'ffc' ); ?></th>
            <td>
                <select name="ffc_settings[date_format]" id="ffc_date_format" style="width: 100%; max-width: 500px;">
                    <?php foreach ( $date_formats as $format => $label ) : ?>
                        <option value="<?php echo esc_attr( $format ); ?>" <?php selected( $current_format, $format ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">
                    <?php _e( 'Format used for {{submission_date}} placeholder in PDFs and emails.', 'ffc' ); ?>
                    <br>
                    <strong><?php _e( 'Preview:', 'ffc' ); ?></strong> 
                    <span id="ffc_date_preview" style="color: #2271b1; font-weight: 600;">
                        <?php 
                        $preview_date = '2026-01-04 15:30:45';
                        if ( $current_format === 'custom' && ! empty( $custom_format ) ) {
                            echo date_i18n( $custom_format, strtotime( $preview_date ) );
                        } else {
                            echo date_i18n( $current_format, strtotime( $preview_date ) );
                        }
                        ?>
                    </span>
                </p>
                
                <div id="ffc_custom_format_container" style="margin-top: 15px; <?php echo $current_format !== 'custom' ? 'display: none;' : ''; ?>">
                    <label>
                        <strong><?php _e( 'Custom Format:', 'ffc' ); ?></strong><br>
                        <input type="text" name="ffc_settings[date_format_custom]" id="ffc_date_format_custom" value="<?php echo esc_attr( $custom_format ); ?>" placeholder="d/m/Y H:i" style="width: 100%; max-width: 300px;">
                    </label>
                    <p class="description">
                        <?php _e( 'Use PHP date format characters.', 'ffc' ); ?> 
                        <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank"><?php _e( 'See documentation', 'ffc' ); ?></a>
                        <br>
                        <strong><?php _e( 'Examples:', 'ffc' ); ?></strong>
                        <code>d/m/Y</code> = 04/01/2026 &nbsp;|&nbsp; 
                        <code>d/m/Y H:i</code> = 04/01/2026 15:30 &nbsp;|&nbsp;
                        <code>j \d\e F \d\e Y</code> = 4 de Janeiro de 2026
                    </p>
                </div>
                
                <script>
                jQuery(document).ready(function($) {
                    // Show/hide custom format field
                    $('#ffc_date_format').on('change', function() {
                        if ($(this).val() === 'custom') {
                            $('#ffc_custom_format_container').slideDown();
                        } else {
                            $('#ffc_custom_format_container').slideUp();
                        }
                        updateDatePreview();
                    });
                    
                    // Update preview on custom format change
                    $('#ffc_date_format_custom').on('input', function() {
                        updateDatePreview();
                    });
                    
                    function updateDatePreview() {
                        var format = $('#ffc_date_format').val();
                        var customFormat = $('#ffc_date_format_custom').val();
                        
                        // AJAX to get formatted date
                        $.post(ajaxurl, {
                            action: 'ffc_preview_date_format',
                            format: format,
                            custom_format: customFormat,
                            nonce: '<?php echo wp_create_nonce('ffc_preview_date'); ?>'
                        }, function(response) {
                            if (response.success) {
                                $('#ffc_date_preview').text(response.data.formatted);
                            }
                        });
                    }
                });
                </script>
            </td>
        </tr>
    </table>
    
    <?php submit_button(); ?>
</form>

<hr style="margin: 40px 0;">

<div class="ffc-danger-zone">
    <h2><?php esc_html_e( 'Danger Zone', 'ffc' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Warning: These actions cannot be undone.', 'ffc' ); ?></p>
    <form method="post" id="ffc-danger-zone-form">
        <?php wp_nonce_field( 'ffc_delete_all_data', 'ffc_critical_nonce' ); ?>
        <input type="hidden" name="ffc_delete_all_data" value="1">
        <div class="ffc-admin-flex-row">
            <select name="delete_target" id="ffc_delete_target" class="ffc-danger-select">
                <option value="all"><?php esc_html_e( 'Delete All Submissions', 'ffc' ); ?></option>
                <?php if ( isset( $forms ) && ! empty( $forms ) ) : ?>
                    <?php foreach ( $forms as $f ) : ?>
                        <option value="<?php echo esc_attr( $f->ID ); ?>"><?php echo esc_html( $f->post_title ); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select> 
            <button type="submit" class="button button-link-delete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure? This action cannot be undone.', 'ffc' ) ); ?>');">
                <?php esc_html_e( 'Clear Data', 'ffc' ); ?>
            </button>
        </div>
    </form>
</div>