<?php
/**
 * Free_Form_Certificate_Loader
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Free_Form_Certificate_Loader {

    protected $submission_handler;
    protected $email_handler;
    protected $csv_exporter;
    protected $cpt;
    protected $admin;
    protected $frontend;
    protected $admin_ajax;

    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 10 );
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_rate_limit_assets' ) );
        $this->define_activation_hooks();
    }

    public function init_plugin() {
        $this->load_dependencies();
        $this->submission_handler = new FFC_Submission_Handler();
        $this->email_handler      = new FFC_Email_Handler();
        $this->csv_exporter       = new FFC_CSV_Exporter();
        $this->cpt                = new FFC_CPT();
        $this->admin              = new FFC_Admin( $this->submission_handler, $this->csv_exporter, $this->email_handler );
        $this->frontend           = new FFC_Frontend( $this->submission_handler, $this->email_handler );
        $this->admin_ajax         = new FFC_Admin_Ajax();
        $this->define_admin_hooks();
    }

    private function load_dependencies() {
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-utils.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-migration-manager.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-activator.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-deactivator.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-rate-limiter.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-magic-link-helper.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-encryption.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-submission-handler.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-submissions-list-table.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-admin-ajax.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-email-handler.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-csv-exporter.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-qrcode-generator.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-pdf-generator.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-cpt.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-form-editor.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-settings.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-admin.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-frontend.php';
    }
    
    public function load_textdomain() {
        load_plugin_textdomain( 'ffc', false, dirname( plugin_basename( FFC_PLUGIN_DIR . 'wp-ffcertificate.php' ) ) . '/languages' );
    }

    private function define_activation_hooks() {
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-activator.php';
        require_once FFC_PLUGIN_DIR . 'includes/class-ffc-deactivator.php';
        register_activation_hook( FFC_PLUGIN_DIR . 'wp-ffcertificate.php', array( 'FFC_Activator', 'activate' ) );
        register_deactivation_hook( FFC_PLUGIN_DIR . 'wp-ffcertificate.php', array( 'FFC_Deactivator', 'deactivate' ) );
    }

    private function define_admin_hooks() {
        add_action( 'ffc_daily_cleanup_hook', array( $this->submission_handler, 'run_data_cleanup' ) );
    }
    
    public function enqueue_rate_limit_assets() {
        wp_enqueue_script( 'ffc-rate-limit', FFC_PLUGIN_URL . 'assets/js/rate-limit-frontend.js', array('jquery'), FFC_VERSION, true );
        wp_enqueue_style( 'ffc-rate-limit', FFC_PLUGIN_URL . 'assets/css/rate-limit-styles.css', array(), FFC_VERSION );
    }
    
    public function run() {}
}
