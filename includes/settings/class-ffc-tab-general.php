<?php
/**
 * General Settings Tab
 * 
 * @package FFC
 * @since 2.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Tab_General extends FFC_Settings_Tab {
    
    private $forms;
    
    protected function init() {
        $this->tab_id = 'general';
        $this->tab_title = __( 'General', 'ffc' );
        $this->tab_icon = '⚙️';
        $this->tab_order = 20;
    }
    
    public function render() {
        // Get forms for danger zone
        $this->forms = get_posts( array(
            'post_type' => 'ffc_form',
            'posts_per_page' => -1
        ) );
        
        // Include view file
        $view_file = FFC_PLUGIN_DIR . 'includes/settings/tab-general.php';
        
        if ( file_exists( $view_file ) ) {
            // Make $forms available to view
            $forms = $this->forms;
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__( 'General settings view file not found.', 'ffc' );
            echo '</p></div>';
        }
    }
    
    /**
     * Get option value (for view compatibility)
     */
    public static function get_option( $key, $default = '' ) {
        $settings = get_option( 'ffc_settings', array() );
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }
}
