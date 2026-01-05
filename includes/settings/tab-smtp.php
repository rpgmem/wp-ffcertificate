<?php
/**
 * SMTP Settings Tab View
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

?>
<form method="post">
    <?php wp_nonce_field( 'ffc_settings_action', 'ffc_settings_nonce' ); ?>
    <input type="hidden" name="_ffc_tab" value="smtp">
    
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Mode', 'ffc' ); ?></th>
            <td>
                <label><input type="radio" name="ffc_settings[smtp_mode]" value="wp" <?php checked( 'wp', $get_option( 'smtp_mode' ) ); ?>> <?php esc_html_e( 'WP Default (PHPMail)', 'ffc' ); ?></label><br>
                <label><input type="radio" name="ffc_settings[smtp_mode]" value="custom" <?php checked( 'custom', $get_option( 'smtp_mode' ) ); ?>> <?php esc_html_e( 'Custom SMTP', 'ffc' ); ?></label>
            </td>
        </tr>
        <tbody id="smtp-options" class="<?php echo ( $get_option( 'smtp_mode' ) === 'custom' ) ? '' : 'ffc-hidden'; ?>">
            <tr>
                <th><?php esc_html_e( 'Host', 'ffc' ); ?></th>
                <td><input type="text" name="ffc_settings[smtp_host]" value="<?php echo esc_attr( $get_option( 'smtp_host' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Port', 'ffc' ); ?></th>
                <td><input type="number" name="ffc_settings[smtp_port]" value="<?php echo esc_attr( $get_option( 'smtp_port' ) ); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'User', 'ffc' ); ?></th>
                <td><input type="text" name="ffc_settings[smtp_user]" value="<?php echo esc_attr( $get_option( 'smtp_user' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Password', 'ffc' ); ?></th>
                <td><input type="password" name="ffc_settings[smtp_pass]" value="<?php echo esc_attr( $get_option( 'smtp_pass' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Encryption', 'ffc' ); ?></th>
                <td>
                    <select name="ffc_settings[smtp_secure]">
                        <option value="tls" <?php selected( 'tls', $get_option( 'smtp_secure' ) ); ?>>TLS</option>
                        <option value="ssl" <?php selected( 'ssl', $get_option( 'smtp_secure' ) ); ?>>SSL</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'From Email', 'ffc' ); ?></th>
                <td><input type="email" name="ffc_settings[smtp_from_email]" value="<?php echo esc_attr( $get_option( 'smtp_from_email' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'From Name', 'ffc' ); ?></th>
                <td><input type="text" name="ffc_settings[smtp_from_name]" value="<?php echo esc_attr( $get_option( 'smtp_from_name' ) ); ?>" class="regular-text"></td>
            </tr>
        </tbody>
    </table>
    <?php submit_button(); ?>
</form>

<script>
jQuery(document).ready(function($) {
    $('input[name="ffc_settings[smtp_mode]"]').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#smtp-options').removeClass('ffc-hidden');
        } else {
            $('#smtp-options').addClass('ffc-hidden');
        }
    });
});
</script>
