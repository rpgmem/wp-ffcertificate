<?php
/**
 * FFC_Dashboard_Shortcode
 *
 * Renders the user dashboard via [user_dashboard_personal] shortcode
 *
 * @since 3.1.0
 */

if (!defined('ABSPATH')) exit;

class FFC_Dashboard_Shortcode {

    /**
     * Register shortcode
     */
    public static function init() {
        add_shortcode('user_dashboard_personal', array(__CLASS__, 'render'));
    }

    /**
     * Render dashboard
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public static function render($atts = array()) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return self::render_login_required();
        }

        // Enqueue assets
        self::enqueue_assets();

        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'certificates';

        // Start output buffering
        ob_start();

        ?>
        <div class="ffc-user-dashboard" id="ffc-user-dashboard">

            <?php echo self::render_redirect_message(); ?>

            <nav class="ffc-dashboard-tabs">
                <button class="ffc-tab <?php echo $current_tab === 'certificates' ? 'active' : ''; ?>"
                        data-tab="certificates">
                    📜 <?php esc_html_e('My Certificates', 'ffc'); ?>
                </button>
                <button class="ffc-tab <?php echo $current_tab === 'profile' ? 'active' : ''; ?>"
                        data-tab="profile">
                    👤 <?php esc_html_e('My Profile', 'ffc'); ?>
                </button>
            </nav>

            <div class="ffc-tab-content <?php echo $current_tab === 'certificates' ? 'active' : ''; ?>"
                 id="tab-certificates">
                <div class="ffc-loading">
                    <?php esc_html_e('Loading certificates...', 'ffc'); ?>
                </div>
            </div>

            <div class="ffc-tab-content <?php echo $current_tab === 'profile' ? 'active' : ''; ?>"
                 id="tab-profile">
                <div class="ffc-loading">
                    <?php esc_html_e('Loading profile...', 'ffc'); ?>
                </div>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Render login required message
     *
     * @return string HTML output
     */
    private static function render_login_required() {
        ob_start();
        ?>
        <div class="ffc-dashboard-notice ffc-notice-warning">
            <p><?php esc_html_e('You must be logged in to view your dashboard.', 'ffc'); ?></p>
            <p>
                <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="button">
                    <?php esc_html_e('Login', 'ffc'); ?>
                </a>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render redirect message (from wp-admin block)
     *
     * @return string HTML output
     */
    private static function render_redirect_message() {
        if (!isset($_GET['ffc_redirect']) || $_GET['ffc_redirect'] !== 'access_denied') {
            return '';
        }

        $settings = get_option('ffc_user_access_settings', array());
        $message = $settings['redirect_message'] ?? __('You were redirected from the admin panel. Use this dashboard to access your certificates.', 'ffc');

        ob_start();
        ?>
        <div class="ffc-dashboard-notice ffc-notice-info">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Enqueue dashboard assets
     */
    private static function enqueue_assets() {
        // Enqueue CSS
        wp_enqueue_style(
            'ffc-dashboard',
            FFC_PLUGIN_URL . 'assets/css/user-dashboard.css',
            array(),
            FFC_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'ffc-dashboard',
            FFC_PLUGIN_URL . 'assets/js/user-dashboard.js',
            array('jquery'),
            FFC_VERSION,
            true
        );

        // Localize script
        wp_localize_script('ffc-dashboard', 'ffcDashboard', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('ffc/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'strings' => array(
                'loading' => __('Loading...', 'ffc'),
                'error' => __('Error loading data', 'ffc'),
                'noStuff' => __('No certificates found', 'ffc'),
                'downloadPdf' => __('View PDF', 'ffc'),
                'yes' => __('Yes', 'ffc'),
                'no' => __('No', 'ffc'),
            ),
        ));
    }
}

// Initialize shortcode
FFC_Dashboard_Shortcode::init();
