<?php
/**
 * QR Code Settings Tab View
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
    <input type="hidden" name="_ffc_tab" value="qr_code">
    
    <h2>📱 <?php esc_html_e( 'QR Code Generation Settings', 'ffc' ); ?></h2>
    
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Enable QR Code Cache', 'ffc' ); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="ffc_settings[qr_cache_enabled]" value="1" <?php checked( 1, $get_option( 'qr_cache_enabled' ) ); ?>>
                    <?php esc_html_e( 'Store generated QR Codes in database', 'ffc' ); ?>
                </label>
                <p class="description">
                    <?php esc_html_e( 'Improves performance by caching QR Codes. Increases database size (~4KB per submission).', 'ffc' ); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th><?php esc_html_e( 'Default QR Code Size', 'ffc' ); ?></th>
            <td>
                <input type="number" name="ffc_settings[qr_default_size]" value="<?php echo esc_attr( $get_option( 'qr_default_size' ) ); ?>" min="100" max="500" step="10" class="small-text"> px
                <p class="description">
                    <?php esc_html_e( 'Default size when {{qr_code}} placeholder is used without size parameter. Range: 100-500px.', 'ffc' ); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th><?php esc_html_e( 'Default QR Code Margin', 'ffc' ); ?></th>
            <td>
                <input type="number" name="ffc_settings[qr_default_margin]" value="<?php echo esc_attr( $get_option( 'qr_default_margin' ) ); ?>" min="0" max="10" step="1" class="small-text">
                <p class="description">
                    <?php esc_html_e( 'White space around QR Code in modules. 0 = no margin, higher values = more white space.', 'ffc' ); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th><?php esc_html_e( 'Default Error Correction Level', 'ffc' ); ?></th>
            <td>
                <select name="ffc_settings[qr_default_error_level]">
                    <option value="L" <?php selected( 'L', $get_option( 'qr_default_error_level' ) ); ?>><?php esc_html_e( 'L - Low (7% correction)', 'ffc' ); ?></option>
                    <option value="M" <?php selected( 'M', $get_option( 'qr_default_error_level' ) ); ?>><?php esc_html_e( 'M - Medium (15% correction) - Recommended', 'ffc' ); ?></option>
                    <option value="Q" <?php selected( 'Q', $get_option( 'qr_default_error_level' ) ); ?>><?php esc_html_e( 'Q - Quartile (25% correction)', 'ffc' ); ?></option>
                    <option value="H" <?php selected( 'H', $get_option( 'qr_default_error_level' ) ); ?>><?php esc_html_e( 'H - High (30% correction)', 'ffc' ); ?></option>
                </select>
                <p class="description">
                    <?php esc_html_e( 'Higher levels allow more damage to QR Code but create denser patterns.', 'ffc' ); ?>
                </p>
            </td>
        </tr>
    </table>
    
    <?php submit_button(); ?>
</form>

<hr>

<h3><?php esc_html_e( 'Cache Statistics', 'ffc' ); ?></h3>

<?php
// Render QR Cache Stats
if ( ! class_exists( 'FFC_QRCode_Generator' ) ) {
    require_once FFC_PLUGIN_DIR . 'includes/class-ffc-qrcode-generator.php';
}

$qr_generator = new FFC_QRCode_Generator();
$stats = $qr_generator->get_cache_stats();
?>

<div class="ffc-qr-stats" style="background: #f0f0f1; padding: 15px; border-radius: 4px; border-left: 4px solid #2271b1; max-width: 600px;">
    <table style="width: 100%;">
        <tr>
            <td style="padding: 8px 0; width: 50%;"><strong><?php _e( 'Cache Status:', 'ffc' ); ?></strong></td>
            <td style="padding: 8px 0;">
                <?php if ( $stats['enabled'] ): ?>
                    <span style="color: #00a32a; font-weight: 600;">✓ <?php _e( 'Enabled', 'ffc' ); ?></span>
                <?php else: ?>
                    <span style="color: #d63638; font-weight: 600;">✗ <?php _e( 'Disabled', 'ffc' ); ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <tr style="background: rgba(255,255,255,0.5);">
            <td style="padding: 8px 0;"><strong><?php _e( 'Total Submissions:', 'ffc' ); ?></strong></td>
            <td style="padding: 8px 0;"><?php echo number_format_i18n( $stats['total_submissions'] ); ?></td>
        </tr>
        <tr>
            <td style="padding: 8px 0;"><strong><?php _e( 'Cached QR Codes:', 'ffc' ); ?></strong></td>
            <td style="padding: 8px 0;"><?php echo number_format_i18n( $stats['cached_qr_codes'] ); ?></td>
        </tr>
        <tr style="background: rgba(255,255,255,0.5);">
            <td style="padding: 8px 0;"><strong><?php _e( 'Estimated Cache Size:', 'ffc' ); ?></strong></td>
            <td style="padding: 8px 0;"><?php echo esc_html( $stats['cache_size'] ); ?></td>
        </tr>
    </table>
</div>
<p class="description" style="margin-top: 10px;">
    <?php _e( 'Cache stores generated QR Codes to improve performance. Enable "QR Code Cache" above to start caching.', 'ffc' ); ?>
</p>

<hr>

<h3><?php esc_html_e( 'Maintenance', 'ffc' ); ?></h3>

<?php
// Clear cache button
$clear_url = wp_nonce_url(
    add_query_arg( array(
        'post_type' => 'ffc_form',
        'page' => 'ffc-settings',
        'tab' => 'qr_code',
        'ffc_clear_qr_cache' => '1'
    ), admin_url( 'edit.php' ) ),
    'ffc_clear_qr_cache'
);
?>

<a href="<?php echo esc_url( $clear_url ); ?>" 
   class="button button-secondary" 
   onclick="return confirm('<?php echo esc_js( __( 'Clear all cached QR Codes? They will be regenerated on next use.', 'ffc' ) ); ?>')">
    🗑️ <?php _e( 'Clear All QR Code Cache', 'ffc' ); ?>
</a>
<p class="description" style="margin-top: 10px;">
    <?php _e( 'Use this if QR Codes are outdated or to free database space. QR Codes will be regenerated automatically when needed.', 'ffc' ); ?>
</p>
