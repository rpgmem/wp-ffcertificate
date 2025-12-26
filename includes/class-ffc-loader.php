<?php
/**
 * FFC_Loader
 * * The core engine that orchestrates hooks, dependencies, and component initialization.
 *
 * @package FastFormCertificates
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

<<<<<<< Updated upstream
<<<<<<< Updated upstream
class Free_Form_Certificate_Loader {

    protected $submission_handler;
    protected $cpt;
    protected $admin;
    protected $frontend;
=======
class FFC_Loader {
>>>>>>> Stashed changes
=======
class FFC_Loader {
>>>>>>> Stashed changes

    public function __construct() {
        // 1 & 2 - Load dependencies in correct order
        $this->load_dependencies();
        
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }

    public function run() {
        // Entry point for future execution, if needed.
    }

=======
        // Register Cron Intervals
        add_filter( 'cron_schedules', array( $this, 'register_cron_schedules' ) );
    }

=======
        // Register Cron Intervals
        add_filter( 'cron_schedules', array( $this, 'register_cron_schedules' ) );
    }

>>>>>>> Stashed changes
    /**
     * 1, 2 & 3 - Load required files.
     * Centralized loading to ensure FFC_Utils is available for everyone.
     */
>>>>>>> Stashed changes
    private function load_dependencies() {
        // Fundamental Helpers (Must be first)
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-utils.php';
        
        // Core Logic & Database
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-cpt.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-submission-handler.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-email-manager.php';
        
        // Admin-specific components
        if ( is_admin() ) {
            require_once FFC_PLUGIN_DIR . 'includes/class-ffc-admin.php';
            require_once FFC_PLUGIN_DIR . 'includes/class-ffc-settings.php';
            require_once FFC_PLUGIN_DIR . 'includes/class-ffc-form-editor.php';
            require_once FFC_PLUGIN_DIR . 'includes/class-ffc-submissions-list-table.php';
        }
        
        // Frontend logic (Depends on Handler and Utils)
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-frontend.php';
    }

<<<<<<< Updated upstream
<<<<<<< Updated upstream
    private function define_activation_hooks() {
        register_activation_hook( FFC_PLUGIN_DIR . 'wp-ffcertificate.php', array( 'FFC_Activator', 'activate' ) );
        register_uninstall_hook( FFC_PLUGIN_DIR . 'wp-ffcertificate.php', array( 'FFC_Deactivator', 'uninstall_cleanup' ) );
    }

    private function define_admin_hooks() {
        add_action( 'ffc_daily_cleanup_hook', array( $this->submission_handler, 'run_data_cleanup' ) );
        add_action( 'ffc_process_submission_hook', array( $this->submission_handler, 'async_process_submission' ), 10, 7 );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'ffc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
=======
    /**
     * 3 & 5 - Custom intervals for WP-Cron (i18n applied).
     */
    public function register_cron_schedules( $schedules ) {
        if ( ! isset( $schedules['every_five_minutes'] ) ) {
            $schedules['every_five_minutes'] = array(
                'interval' => 300,
                'display'  => esc_html__( 'Every 5 Minutes', 'ffc' )
            );
        }
        return $schedules;
    }

    /**
     * 1, 2 & 3 - Execution flow.
     * Instantiates components and registers their hooks.
     */
    public function run() {
        // 1. Register Custom Post Types
        if ( class_exists( 'FFC_CPT' ) ) {
            $cpt = new FFC_CPT();
            $cpt->register();
        }

=======
    /**
     * 3 & 5 - Custom intervals for WP-Cron (i18n applied).
     */
    public function register_cron_schedules( $schedules ) {
        if ( ! isset( $schedules['every_five_minutes'] ) ) {
            $schedules['every_five_minutes'] = array(
                'interval' => 300,
                'display'  => esc_html__( 'Every 5 Minutes', 'ffc' )
            );
        }
        return $schedules;
    }

    /**
     * 1, 2 & 3 - Execution flow.
     * Instantiates components and registers their hooks.
     */
    public function run() {
        // 1. Register Custom Post Types
        if ( class_exists( 'FFC_CPT' ) ) {
            $cpt = new FFC_CPT();
            $cpt->register();
        }

>>>>>>> Stashed changes
        // 2. Initialize Email Manager (Static/Singleton-like)
        if ( class_exists( 'FFC_Email_Manager' ) ) {
            new FFC_Email_Manager();
        }

        // 3. Initialize Admin logic (Only on Admin side)
        if ( is_admin() ) {
            if ( class_exists( 'FFC_Admin' ) ) {
                new FFC_Admin();
            }
            if ( class_exists( 'FFC_Settings' ) ) {
                new FFC_Settings();
            }
            if ( class_exists( 'FFC_Form_Editor' ) ) {
                new FFC_Form_Editor();
            }
        }

        // 4. Initialize Frontend with its dependency injected
        // Point 3: Checking for both classes prevents fatal errors if a file is missing
        if ( class_exists( 'FFC_Submission_Handler' ) && class_exists( 'FFC_Frontend' ) ) {
            $handler = new FFC_Submission_Handler();
            new FFC_Frontend( $handler );
        }
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
    }
}