<?php
/**
 * FFC_Settings
 * Handles plugin settings, documentation tab, and data maintenance.
 *
 * @package FastFormCertificates
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Settings {

    /**
     * @var FFC_Submission_Handler
     */
    private $submission_handler;

<<<<<<< Updated upstream
    public function __construct( FFC_Submission_Handler $handler ) {
=======
    /**
     * Initialize the settings class.
     * Updated to handle optional dependency injection or internal instantiation.
     */
    public function __construct( $handler = null ) {
        // Se o handler não for passado, tentamos instanciar (ponto de segurança)
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        $this->submission_handler = $handler;
        
        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'handle_settings_submission' ) );
        }
    }

    public function get_default_settings() { 
        return array( 
            'cleanup_days'    => 30, 
            'smtp_mode'       => 'wp', 
            'smtp_port'       => 587, 
            'smtp_secure'     => 'tls',
            'smtp_host'       => '',
            'smtp_user'       => '',
            'smtp_pass'       => '',
            'smtp_from_email' => get_option( 'admin_email' ),
            'smtp_from_name'  => get_bloginfo( 'name' )
        ); 
    }
    
<<<<<<< Updated upstream
=======
    /**
     * Retrieve a specific option with fallback to default.
     */
>>>>>>> Stashed changes
    public function get_option( $key ) { 
        $defaults = $this->get_default_settings();
        $saved    = get_option( 'ffc_settings', array() ); 
        
        if ( isset( $saved[ $key ] ) && '' !== $saved[ $key ] ) {
            return $saved[ $key ];
        }

        return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
    }

<<<<<<< Updated upstream
    public function handle_settings_submission() {
=======
    /**
     * Process settings form submissions and data deletion requests.
     * Point 2: Added Capability checks.
     */
    public function handle_settings_submission() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // 1. Handle General/SMTP Settings
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        if ( isset( $_POST['ffc_settings_nonce'] ) && wp_verify_nonce( $_POST['ffc_settings_nonce'], 'ffc_settings_action' ) ) {
            $current = get_option( 'ffc_settings', array() );
            $new     = isset( $_POST['ffc_settings'] ) ? $_POST['ffc_settings'] : array();
            
            $clean = array( 
                'cleanup_days'    => absint( $new['cleanup_days'] ),
                'smtp_mode'       => sanitize_key( $new['smtp_mode'] ),
                'smtp_host'       => sanitize_text_field( $new['smtp_host'] ),
                'smtp_port'       => absint( $new['smtp_port'] ),
                'smtp_user'       => sanitize_text_field( $new['smtp_user'] ),
                'smtp_pass'       => sanitize_text_field( $new['smtp_pass'] ),
                'smtp_secure'     => sanitize_key( $new['smtp_secure'] ),
                'smtp_from_email' => sanitize_email( $new['smtp_from_email'] ),
                'smtp_from_name'  => sanitize_text_field( $new['smtp_from_name'] ),
            );
            
            update_option( 'ffc_settings', array_merge( $current, $clean ) );
            add_settings_error( 'ffc_settings', 'ffc_settings_updated', __( 'Settings saved.', 'ffc' ), 'updated' );
        }
        
<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
        // 2. Handle Global Data Deletion (Danger Zone)
>>>>>>> Stashed changes
=======
        // 2. Handle Global Data Deletion (Danger Zone)
>>>>>>> Stashed changes
        if ( isset( $_POST['ffc_delete_all_data'] ) && check_admin_referer( 'ffc_delete_all_data', 'ffc_critical_nonce' ) ) {
            if ( ! $this->submission_handler ) {
                $this->submission_handler = new FFC_Submission_Handler();
            }

            $target = isset( $_POST['delete_target'] ) ? $_POST['delete_target'] : 'all';
            $form_id = ( 'all' === $target ) ? null : absint( $target );
            
            $this->submission_handler->delete_all_submissions( $form_id );
            add_settings_error( 'ffc_settings', 'ffc_data_deleted', __( 'Data deleted successfully.', 'ffc' ), 'updated' );
        }
    }
    
    public function display_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'help'; 
        $forms = get_posts( array( 'post_type' => 'ffc_form', 'posts_per_page' => -1 ) );
        ?>
        <div class="wrap ffc-settings-wrap">
            <h1><?php esc_html_e( 'Certificate Settings', 'ffc' ); ?></h1>
            <?php settings_errors( 'ffc_settings' ); ?>
            
            <h2 class="nav-tab-wrapper">
                <a href="?post_type=ffc_form&page=ffc-settings&tab=help" class="nav-tab <?php echo $active_tab=='help'?'nav-tab-active':''; ?>"><?php esc_html_e( 'Documentation', 'ffc' ); ?></a>
                <a href="?post_type=ffc_form&page=ffc-settings&tab=general" class="nav-tab <?php echo $active_tab=='general'?'nav-tab-active':''; ?>"><?php esc_html_e( 'General', 'ffc' ); ?></a>
                <a href="?post_type=ffc_form&page=ffc-settings&tab=smtp" class="nav-tab <?php echo $active_tab=='smtp'?'nav-tab-active':''; ?>"><?php esc_html_e( 'SMTP', 'ffc' ); ?></a>
            </h2>
            
            <div class="ffc-tab-content">
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                <?php if($active_tab=='help'): ?>
=======
                <?php if ( 'help' === $active_tab ) : ?>
>>>>>>> Stashed changes
=======
                <?php if ( 'help' === $active_tab ) : ?>
>>>>>>> Stashed changes
                    <div class="card ffc-settings-card">
                        <h3><?php esc_html_e( 'Shortcodes', 'ffc' ); ?></h3>
                        <table class="widefat striped ffc-help-table">
                            <thead>
                                <tr><th>Shortcode</th><th><?php esc_html_e( 'Description', 'ffc' ); ?></th></tr>
                            </thead>
                            <tbody>
<<<<<<< Updated upstream
                                <tr><td><code>[ffc_form id="123"]</code></td><td><?php esc_html_e( 'Displays the issuance form.', 'ffc' ); ?></td></tr>
                                <tr><td><code>[ffc_verification]</code></td><td><?php esc_html_e( 'Displays the verification page.', 'ffc' ); ?></td></tr>
                            </tbody>
                        </table>

                        <h3><?php esc_html_e( 'Template Variables', 'ffc' ); ?></h3>
                        <table class="widefat striped ffc-help-table">
                            <thead>
                                <tr><th><?php esc_html_e( 'Variable', 'ffc' ); ?></th><th><?php esc_html_e( 'Description', 'ffc' ); ?></th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code>{{name}}</code> / <code>{{nome}}</code></td><td><?php esc_html_e( 'Participant full name.', 'ffc' ); ?></td></tr>
                                <tr><td><code>{{auth_code}}</code></td><td><?php esc_html_e( 'Unique validation code.', 'ffc' ); ?></td></tr>
                                <tr><td><code>{{cpf_rf}}</code></td><td><?php esc_html_e( 'Identification ID.', 'ffc' ); ?></td></tr>
=======
                                <tr>
                                    <td><code>[ffc_form id="123"]</code></td>
                                    <td><?php esc_html_e( 'Displays the issuance form. Replace "123" with the Form ID.', 'ffc' ); ?></td>
                                </tr>
                                <tr>
                                    <td><code>[ffc_verification]</code></td>
                                    <td><?php esc_html_e( 'Displays the public verification page.', 'ffc' ); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <br>
                        <h4><?php esc_html_e( '2. PDF Template Variables', 'ffc' ); ?></h4>
                        <p><?php esc_html_e( 'Use these in your HTML layout:', 'ffc' ); ?></p>
                        <table class="widefat striped">
                            <tbody>
                                <tr><td><code>{{name}}</code></td><td><?php esc_html_e( 'Participant Full Name', 'ffc' ); ?></td></tr>
                                <tr><td><code>{{cpf_rf}}</code></td><td><?php esc_html_e( 'Identification ID', 'ffc' ); ?></td></tr>
                                <tr><td><code>{{auth_code}}</code></td><td><?php esc_html_e( 'Unique Certificate Code', 'ffc' ); ?></td></tr>
                                <tr><td><code>{{current_date}}</code></td><td><?php esc_html_e( 'Issuance Date (DD/MM/YYYY)', 'ffc' ); ?></td></tr>
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                            </tbody>
                        </table>
                    </div>
                    
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                <?php elseif($active_tab=='general'): ?>
=======
                <?php elseif ( 'general' === $active_tab ) : ?>
>>>>>>> Stashed changes
=======
                <?php elseif ( 'general' === $active_tab ) : ?>
>>>>>>> Stashed changes
                    <form method="post">
                        <?php wp_nonce_field('ffc_settings_action','ffc_settings_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Auto-delete (days)', 'ffc' ); ?></th>
                                <td>
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                                    <input type="number" name="ffc_settings[cleanup_days]" value="<?php echo esc_attr($this->get_option('cleanup_days')); ?>">
                                    <p class="description"><?php esc_html_e( 'Files removed after X days. 0 to disable.', 'ffc' ); ?></p>
=======
=======
>>>>>>> Stashed changes
                                    <input type="number" name="ffc_settings[cleanup_days]" value="<?php echo esc_attr( $this->get_option( 'cleanup_days' ) ); ?>" min="0">
                                    <p class="description"><?php esc_html_e( 'Files removed after X days. Set to 0 to disable.', 'ffc' ); ?></p>
>>>>>>> Stashed changes
                                </td>
                            </tr>
                        </table>
                        <?php submit_button(); ?>
                    </form>
                    
                    <div class="ffc-danger-zone" style="margin-top: 50px; padding: 20px; border: 1px solid #ccd0d4; background: #fff;">
                        <h2 style="color: #d63638;"><?php esc_html_e( 'Danger Zone', 'ffc' ); ?></h2>
                        <p><?php esc_html_e( 'Delete generated certificates and submission records.', 'ffc' ); ?></p>
                        <form method="post" id="ffc-danger-zone-form">
                            <?php wp_nonce_field('ffc_delete_all_data','ffc_critical_nonce'); ?>
                            <input type="hidden" name="ffc_delete_all_data" value="1">
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                            <div class="ffc-admin-flex-row">
                                <select name="delete_target" id="ffc_delete_target">
                                    <option value="all"><?php esc_html_e( 'Delete All Submissions', 'ffc' ); ?></option>
                                    <?php foreach($forms as $f): ?>
                                        <option value="<?php echo $f->ID; ?>"><?php echo esc_html($f->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select> 
                                <button type="submit" class="button button-link-delete"><?php esc_html_e( 'Clear Data', 'ffc' ); ?></button>
                            </div>
                        </form>
                    </div>
                    
                <?php elseif($active_tab=='smtp'): ?>
=======
                            <select name="delete_target" id="ffc_delete_target">
                                <option value="all"><?php esc_html_e( 'Delete All Submissions', 'ffc' ); ?></option>
                                <?php foreach ( $forms as $f ) : ?>
                                    <option value="<?php echo esc_attr( $f->ID ); ?>"><?php echo esc_html( $f->post_title ); ?></option>
                                <?php endforeach; ?>
                            </select> 
                            <button type="submit" class="button button-link-delete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure? This action cannot be undone.', 'ffc' ) ); ?>');">
                                <?php esc_html_e( 'Clear Data', 'ffc' ); ?>
                            </button>
                        </form>
                    </div>
                    
                <?php elseif ( 'smtp' === $active_tab ) : ?>
>>>>>>> Stashed changes
=======
                            <select name="delete_target" id="ffc_delete_target">
                                <option value="all"><?php esc_html_e( 'Delete All Submissions', 'ffc' ); ?></option>
                                <?php foreach ( $forms as $f ) : ?>
                                    <option value="<?php echo esc_attr( $f->ID ); ?>"><?php echo esc_html( $f->post_title ); ?></option>
                                <?php endforeach; ?>
                            </select> 
                            <button type="submit" class="button button-link-delete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure? This action cannot be undone.', 'ffc' ) ); ?>');">
                                <?php esc_html_e( 'Clear Data', 'ffc' ); ?>
                            </button>
                        </form>
                    </div>
                    
                <?php elseif ( 'smtp' === $active_tab ) : ?>
>>>>>>> Stashed changes
                    <form method="post">
                        <?php wp_nonce_field('ffc_settings_action','ffc_settings_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Mode', 'ffc' ); ?></th>
                                <td>
                                    <label><input type="radio" name="ffc_settings[smtp_mode]" value="wp" <?php checked('wp',$this->get_option('smtp_mode')); ?>> <?php esc_html_e( 'WP Default', 'ffc' ); ?></label><br>
                                    <label><input type="radio" name="ffc_settings[smtp_mode]" value="custom" <?php checked('custom',$this->get_option('smtp_mode')); ?>> <?php esc_html_e( 'Custom SMTP', 'ffc' ); ?></label>
                                </td>
                            </tr>
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                            <tbody id="smtp-options" class="<?php echo ($this->get_option('smtp_mode')==='custom') ? '' : 'ffc-hidden'; ?>">
                                <tr><th><?php esc_html_e( 'Host', 'ffc' ); ?></th><td><input type="text" name="ffc_settings[smtp_host]" value="<?php echo esc_attr($this->get_option('smtp_host')); ?>" class="regular-text"></td></tr>
                                <tr><th><?php esc_html_e( 'Port', 'ffc' ); ?></th><td><input type="number" name="ffc_settings[smtp_port]" value="<?php echo esc_attr($this->get_option('smtp_port')); ?>" class="small-text"></td></tr>
                                <tr><th><?php esc_html_e( 'User', 'ffc' ); ?></th><td><input type="text" name="ffc_settings[smtp_user]" value="<?php echo esc_attr($this->get_option('smtp_user')); ?>" class="regular-text"></td></tr>
                                <tr><th><?php esc_html_e( 'Password', 'ffc' ); ?></th><td><input type="password" name="ffc_settings[smtp_pass]" value="<?php echo esc_attr($this->get_option('smtp_pass')); ?>" class="regular-text"></td></tr>
                                <tr><th><?php esc_html_e( 'Encryption', 'ffc' ); ?></th><td>
                                    <select name="ffc_settings[smtp_secure]">
                                        <option value="tls" <?php selected('tls',$this->get_option('smtp_secure')); ?>>TLS</option>
                                        <option value="ssl" <?php selected('ssl',$this->get_option('smtp_secure')); ?>>SSL</option>
                                    </select>
                                </td></tr>
=======
=======
>>>>>>> Stashed changes
                            <tbody id="smtp-options" class="<?php echo ( 'custom' === $this->get_option( 'smtp_mode' ) ) ? '' : 'ffc-hidden'; ?>">
                                <tr>
                                    <th><?php esc_html_e( 'Host', 'ffc' ); ?></th>
                                    <td><input type="text" name="ffc_settings[smtp_host]" value="<?php echo esc_attr( $this->get_option( 'smtp_host' ) ); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Port', 'ffc' ); ?></th>
                                    <td><input type="number" name="ffc_settings[smtp_port]" value="<?php echo esc_attr( $this->get_option( 'smtp_port' ) ); ?>" class="small-text"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'User', 'ffc' ); ?></th>
                                    <td><input type="text" name="ffc_settings[smtp_user]" value="<?php echo esc_attr( $this->get_option( 'smtp_user' ) ); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Password', 'ffc' ); ?></th>
                                    <td><input type="password" name="ffc_settings[smtp_pass]" value="<?php echo esc_attr( $this->get_option( 'smtp_pass' ) ); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Encryption', 'ffc' ); ?></th>
                                    <td>
                                        <select name="ffc_settings[smtp_secure]">
                                            <option value="tls" <?php selected( 'tls', $this->get_option( 'smtp_secure' ) ); ?>>TLS</option>
                                            <option value="ssl" <?php selected( 'ssl', $this->get_option( 'smtp_secure' ) ); ?>>SSL</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'From Email', 'ffc' ); ?></th>
                                    <td><input type="email" name="ffc_settings[smtp_from_email]" value="<?php echo esc_attr( $this->get_option( 'smtp_from_email' ) ); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'From Name', 'ffc' ); ?></th>
                                    <td><input type="text" name="ffc_settings[smtp_from_name]" value="<?php echo esc_attr( $this->get_option( 'smtp_from_name' ) ); ?>" class="regular-text"></td>
                                </tr>
>>>>>>> Stashed changes
                            </tbody>
                        </table>
                        <?php submit_button(); ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}