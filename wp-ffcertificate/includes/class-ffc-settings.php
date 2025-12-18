<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Settings {

    private $submission_handler;

    public function __construct( FFC_Submission_Handler $handler ) {
        $this->submission_handler = $handler;
        
        // Hooks to save settings are called here
        add_action( 'admin_init', array( $this, 'handle_settings_submission' ) );
    }

    public function get_default_settings() { 
        return array( 
            'cleanup_days' => 30, 
            'smtp_mode'    => 'wp', 
            'smtp_port'    => 587, 
            'smtp_secure'  => 'tls',
            'smtp_host'    => '',
            'smtp_user'    => '',
            'smtp_pass'    => '',
            'smtp_from_email' => '',
            'smtp_from_name'  => ''
        ); 
    }
    
    public function get_option( $key ) { 
        $s = get_option('ffc_settings', $this->get_default_settings()); 
        return isset($s[$key]) ? $s[$key] : (isset($this->get_default_settings()[$key]) ? $this->get_default_settings()[$key] : ''); 
    }

    public function handle_settings_submission() {
        // 1. Save General and SMTP Settings
        if ( isset( $_POST['ffc_settings_nonce'] ) && wp_verify_nonce( $_POST['ffc_settings_nonce'], 'ffc_settings_action' ) ) {
            $current = get_option( 'ffc_settings', array() );
            $new     = isset( $_POST['ffc_settings'] ) ? $_POST['ffc_settings'] : array();
            
            $clean = array_merge( $current, array( 
                'cleanup_days'    => absint( $new['cleanup_days'] ),
                'smtp_mode'       => sanitize_key( $new['smtp_mode'] ),
                'smtp_host'       => sanitize_text_field( $new['smtp_host'] ),
                'smtp_port'       => absint( $new['smtp_port'] ),
                'smtp_user'       => sanitize_text_field( $new['smtp_user'] ),
                'smtp_pass'       => sanitize_text_field( $new['smtp_pass'] ),
                'smtp_secure'     => sanitize_key( $new['smtp_secure'] ),
                'smtp_from_email' => sanitize_email( $new['smtp_from_email'] ),
                'smtp_from_name'  => sanitize_text_field( $new['smtp_from_name'] ),
            ) );
            
            update_option( 'ffc_settings', $clean );
            add_settings_error( 'ffc_settings', 'ffc_settings_updated', __( 'Settings saved successfully.', 'ffc' ), 'updated' );
        }
        
        // 2. Execute Danger Zone Actions (Delete Data)
        if ( isset( $_POST['ffc_delete_all_data'] ) && check_admin_referer( 'ffc_delete_all_data', 'ffc_critical_nonce' ) ) {
            $target = isset($_POST['delete_target']) ? $_POST['delete_target'] : 'all';
            
            if($target === 'all') { 
                $this->submission_handler->delete_all_submissions(); 
            } else { 
                $this->submission_handler->delete_all_submissions(absint($target)); 
            }
            
            add_settings_error( 'ffc_settings', 'ffc_data_deleted', __( 'Data deleted successfully.', 'ffc' ), 'updated' );
        }
    }
    
    public function display_settings_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'help'; 
        $forms = get_posts( array('post_type'=>'ffc_form', 'posts_per_page'=>-1) );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Free Form Certificate - Settings', 'ffc' ); ?></h1>
            <?php settings_errors( 'ffc_settings' ); ?>
            
            <h2 class="nav-tab-wrapper">
                <a href="?post_type=ffc_form&page=ffc-settings&tab=help" class="nav-tab <?php echo $active_tab=='help'?'nav-tab-active':''; ?>"><?php esc_html_e( 'Help & Documentation', 'ffc' ); ?></a>
                <a href="?post_type=ffc_form&page=ffc-settings&tab=general" class="nav-tab <?php echo $active_tab=='general'?'nav-tab-active':''; ?>"><?php esc_html_e( 'General Settings', 'ffc' ); ?></a>
                <a href="?post_type=ffc_form&page=ffc-settings&tab=smtp" class="nav-tab <?php echo $active_tab=='smtp'?'nav-tab-active':''; ?>"><?php esc_html_e( 'SMTP Configuration', 'ffc' ); ?></a>
            </h2>
            
            <?php if($active_tab=='help'): ?>
                <div class="card" style="margin-top:20px; padding:20px; max-width: 100%;">
                    <h3><?php esc_html_e( 'How to use this plugin', 'ffc' ); ?></h3>
                    <p><?php esc_html_e( 'This plugin allows you to create certificate issuance forms, generate PDFs automatically, and verify authenticity.', 'ffc' ); ?></p>
                    
                    <hr>

                    <h4><?php esc_html_e( '1. Shortcodes', 'ffc' ); ?></h4>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Shortcode</th>
                                <th><?php esc_html_e( 'Description', 'ffc' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>[ffc_form id="123"]</code></td>
                                <td><?php esc_html_e( 'Displays the issuance form. Replace "123" with the specific Form ID found in the "All Forms" list.', 'ffc' ); ?></td>
                            </tr>
                            <tr>
                                <td><code>[ffc_verification]</code></td>
                                <td><?php esc_html_e( 'Displays the public Authenticity Verification page where users can validate a certificate code.', 'ffc' ); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <br>
                    <hr>

                    <h4><?php esc_html_e( '2. PDF Template Variables', 'ffc' ); ?></h4>
                    <p><?php esc_html_e( 'When creating your form layout (HTML) in the editor, use these variables. They will be replaced by user data:', 'ffc' ); ?></p>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th style="width: 200px;"><?php esc_html_e( 'Variable', 'ffc' ); ?></th>
                                <th><?php esc_html_e( 'Description', 'ffc' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>{{name}}</code> <?php esc_html_e( 'or', 'ffc' ); ?> <code>{{nome}}</code></td>
                                <td><?php esc_html_e( 'The full name of the participant.', 'ffc' ); ?></td>
                            </tr>
                            <tr>
                                <td><code>{{cpf_rf}}</code></td>
                                <td><?php esc_html_e( 'The Identification ID (CPF/RF) entered by the user.', 'ffc' ); ?></td>
                            </tr>
                            <tr>
                                <td><code>{{email}}</code></td>
                                <td><?php esc_html_e( 'The user email address.', 'ffc' ); ?></td>
                            </tr>
                            <tr>
                                <td><code>{{auth_code}}</code></td>
                                <td><?php esc_html_e( 'The unique authentication code (e.g., A1B2-C3D4). Required for validation.', 'ffc' ); ?></td>
                            </tr>
                            <tr>
                                <td><code>{{form_title}}</code></td>
                                <td><?php esc_html_e( 'The title of this form/event.', 'ffc' ); ?></td>
                            </tr>
                            <tr>
                                <td><code>{{current_date}}</code></td>
                                <td><?php esc_html_e( 'The date the certificate was issued (DD/MM/YYYY).', 'ffc' ); ?></td>
                            </tr>
                            <tr>
                                <td><code>{{custom_field_name}}</code></td>
                                <td><?php esc_html_e( 'Any other custom field you added to the form (use the field name).', 'ffc' ); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <br>
                    <hr>

                    <h4><?php esc_html_e( '3. Security Features', 'ffc' ); ?></h4>
                    <ul>
                        <li><strong><?php esc_html_e( 'Allowlist:', 'ffc' ); ?></strong> <?php esc_html_e( 'Restrict issuance to a specific list of CPFs/IDs.', 'ffc' ); ?></li>
                        <li><strong><?php esc_html_e( 'Ticket Mode:', 'ffc' ); ?></strong> <?php esc_html_e( 'Require a unique ticket code to issue the certificate. Tickets are "burned" (deleted) after use.', 'ffc' ); ?></li>
                        <li><strong><?php esc_html_e( 'Denylist:', 'ffc' ); ?></strong> <?php esc_html_e( 'Block specific IDs or Tickets from generating certificates.', 'ffc' ); ?></li>
                        <li><strong><?php esc_html_e( 'Math Captcha:', 'ffc' ); ?></strong> <?php esc_html_e( 'Built-in protection against bots on all forms.', 'ffc' ); ?></li>
                    </ul>
                </div>
                
            <?php elseif($active_tab=='general'): ?>
                <form method="post">
                    <?php wp_nonce_field('ffc_settings_action','ffc_settings_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Auto-delete (days)', 'ffc' ); ?></th>
                            <td>
                                <input type="number" name="ffc_settings[cleanup_days]" value="<?php echo esc_attr($this->get_option('cleanup_days')); ?>">
                                <p class="description"><?php esc_html_e( 'Temporary files and old submissions will be removed after this many days. Set 0 to disable.', 'ffc' ); ?></p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button( __( 'Save Settings', 'ffc' ) ); ?>
                </form>
                
                <hr>
                
                <h2><?php esc_html_e( 'Danger Zone', 'ffc' ); ?></h2>
                <div style="border:1px solid #d63638; padding:15px; background:#fff;">
                    <form method="post" onsubmit="return confirm('<?php esc_html_e( 'Are you sure? This will delete ALL selected submissions. This action is irreversible.', 'ffc' ); ?>');">
                        <?php wp_nonce_field('ffc_delete_all_data','ffc_critical_nonce'); ?>
                        <input type="hidden" name="ffc_delete_all_data" value="1">
                        <p>
                            <label><strong><?php esc_html_e( 'Delete Submission Data:', 'ffc' ); ?></strong></label><br>
                            <select name="delete_target" style="margin-top:5px;">
                                <option value="all"><?php esc_html_e( 'Delete All Submissions (Global)', 'ffc' ); ?></option>
                                <?php foreach($forms as $f): ?>
                                    <option value="<?php echo $f->ID; ?>"><?php esc_html_e( 'Form:', 'ffc' ); ?> <?php echo esc_html($f->post_title); ?></option>
                                <?php endforeach; ?>
                            </select> 
                            <button class="button button-link-delete" style="margin-left:10px;"><?php esc_html_e( 'Execute Deletion', 'ffc' ); ?></button>
                        </p>
                    </form>
                </div>
                
            <?php elseif($active_tab=='smtp'): ?>
                <form method="post">
                    <?php wp_nonce_field('ffc_settings_action','ffc_settings_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Mode', 'ffc' ); ?></th>
                            <td>
                                <label><input type="radio" name="ffc_settings[smtp_mode]" value="wp" <?php checked('wp',$this->get_option('smtp_mode')); ?>> <?php esc_html_e( 'WP Default (PHP Mail)', 'ffc' ); ?></label><br>
                                <label><input type="radio" name="ffc_settings[smtp_mode]" value="custom" <?php checked('custom',$this->get_option('smtp_mode')); ?>> <?php esc_html_e( 'Custom SMTP', 'ffc' ); ?></label>
                            </td>
                        </tr>
                        <tbody id="smtp-options" style="<?php echo ($this->get_option('smtp_mode')==='custom')?'':'display:none;'; ?>">
                            <tr><th><?php esc_html_e( 'Host', 'ffc' ); ?></th><td><input type="text" name="ffc_settings[smtp_host]" value="<?php echo esc_attr($this->get_option('smtp_host')); ?>" class="regular-text" placeholder="smtp.gmail.com"></td></tr>
                            <tr><th><?php esc_html_e( 'Port', 'ffc' ); ?></th><td><input type="number" name="ffc_settings[smtp_port]" value="<?php echo esc_attr($this->get_option('smtp_port')); ?>" class="small-text" placeholder="587"></td></tr>
                            <tr><th><?php esc_html_e( 'User', 'ffc' ); ?></th><td><input type="text" name="ffc_settings[smtp_user]" value="<?php echo esc_attr($this->get_option('smtp_user')); ?>" class="regular-text"></td></tr>
                            <tr><th><?php esc_html_e( 'Password', 'ffc' ); ?></th><td><input type="password" name="ffc_settings[smtp_pass]" value="<?php echo esc_attr($this->get_option('smtp_pass')); ?>" class="regular-text"></td></tr>
                            <tr><th><?php esc_html_e( 'Encryption', 'ffc' ); ?></th><td><select name="ffc_settings[smtp_secure]"><option value="tls" <?php selected('tls',$this->get_option('smtp_secure')); ?>>TLS</option><option value="ssl" <?php selected('ssl',$this->get_option('smtp_secure')); ?>>SSL</option></select></td></tr>
                            <tr><th><?php esc_html_e( 'From Email', 'ffc' ); ?></th><td><input type="email" name="ffc_settings[smtp_from_email]" value="<?php echo esc_attr($this->get_option('smtp_from_email')); ?>" class="regular-text"></td></tr>
                            <tr><th><?php esc_html_e( 'From Name', 'ffc' ); ?></th><td><input type="text" name="ffc_settings[smtp_from_name]" value="<?php echo esc_attr($this->get_option('smtp_from_name')); ?>" class="regular-text"></td></tr>
                        </tbody>
                    </table>
                    <?php submit_button( __( 'Save SMTP Settings', 'ffc' ) ); ?>
                    <script>
                        jQuery(function($){ 
                            $('input[name="ffc_settings[smtp_mode]"]').change(function(){ 
                                if($(this).val()==='custom') $('#smtp-options').show(); 
                                else $('#smtp-options').hide(); 
                            }); 
                        });
                    </script>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }
}