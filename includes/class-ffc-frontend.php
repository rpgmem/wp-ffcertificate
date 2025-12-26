<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
<<<<<<< Updated upstream
<<<<<<< Updated upstream
 * Classe responsável pelo Frontend e Shortcodes do Plugin
=======
 * Handles all frontend displays, shortcodes, and AJAX interactions.
>>>>>>> Stashed changes
=======
 * Handles all frontend displays, shortcodes, and AJAX interactions.
>>>>>>> Stashed changes
 */
class FFC_Frontend {
    
    private $submission_handler;

    public function __construct( FFC_Submission_Handler $handler ) {
        $this->submission_handler = $handler;
        
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        // Ativos do Frontend
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
        
        // Shortcodes
        add_shortcode( 'ffc_form', array( $this, 'shortcode_form' ) );
        add_shortcode( 'ffc_verification', array( $this, 'shortcode_verification_page' ) );
        
        // AJAX Handles - Submissão de Formulário
        add_action( 'wp_ajax_ffc_submit_form', array( $this, 'handle_submission_ajax' ) );
        add_action( 'wp_ajax_nopriv_ffc_submit_form', array( $this, 'handle_submission_ajax' ) );

        // AJAX Handles - Verificação de Certificado
        add_action( 'wp_ajax_ffc_verify_certificate', array( $this, 'handle_verification_ajax' ) );
        add_action( 'wp_ajax_nopriv_ffc_verify_certificate', array( $this, 'handle_verification_ajax' ) );
    }

    /**
     * Carrega Scripts e Estilos condicionalmente
=======
        // Assets & Shortcodes
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_shortcode( 'ffc_form', array( $this, 'render_form_shortcode' ) );
        add_shortcode( 'ffc_verification', array( $this, 'render_verification_shortcode' ) );
        
        // AJAX Actions
        $ajax_actions = array( 'ffc_submit_form', 'ffc_verify_certificate' );
        foreach ( $ajax_actions as $action ) {
            add_action( "wp_ajax_$action", array( $this, "handle_{$action}_ajax" ) );
            add_action( "wp_ajax_nopriv_$action", array( $this, "handle_{$action}_ajax" ) );
        }
    }

    /**
     * 1 & 4 - Assets management with centralized CSS
>>>>>>> Stashed changes
=======
        // Assets & Shortcodes
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_shortcode( 'ffc_form', array( $this, 'render_form_shortcode' ) );
        add_shortcode( 'ffc_verification', array( $this, 'render_verification_shortcode' ) );
        
        // AJAX Actions
        $ajax_actions = array( 'ffc_submit_form', 'ffc_verify_certificate' );
        foreach ( $ajax_actions as $action ) {
            add_action( "wp_ajax_$action", array( $this, "handle_{$action}_ajax" ) );
            add_action( "wp_ajax_nopriv_$action", array( $this, "handle_{$action}_ajax" ) );
        }
    }

    /**
     * 1 & 4 - Assets management with centralized CSS
>>>>>>> Stashed changes
     */
    public function enqueue_frontend_assets() {
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) ) return;

        if ( has_shortcode( $post->post_content, 'ffc_form' ) || has_shortcode( $post->post_content, 'ffc_verification' ) ) {
            wp_enqueue_style( 'ffc-pdf-core', FFC_PLUGIN_URL . 'assets/css/ffc-pdf-core.css', array(), '1.0.0' );
            wp_enqueue_style( 'ffc-frontend', FFC_PLUGIN_URL . 'assets/css/frontend.css', array('ffc-pdf-core'), '1.0.0' );
            
<<<<<<< Updated upstream
<<<<<<< Updated upstream
            // Bibliotecas de geração de PDF (Frontend)
            wp_enqueue_script( 'html2canvas', FFC_PLUGIN_URL . 'assets/js/html2canvas.min.js', array(), '1.4.1', true );
            wp_enqueue_script( 'jspdf', FFC_PLUGIN_URL . 'assets/js/jspdf.umd.min.js', array(), '2.5.1', true );
            
            wp_enqueue_script( 
                'ffc-frontend-js', 
                FFC_PLUGIN_URL . 'assets/js/frontend.js', 
                array( 'jquery', 'html2canvas', 'jspdf' ), 
                time(), // Cache busting para desenvolvimento
                true 
            );
=======
            wp_enqueue_script( 'html2canvas', FFC_PLUGIN_URL . 'assets/js/html2canvas.min.js', array(), '1.4.1', true );
            wp_enqueue_script( 'jspdf', FFC_PLUGIN_URL . 'assets/js/jspdf.umd.min.js', array(), '2.5.1', true );
            wp_enqueue_script( 'ffc-pdf-engine', FFC_PLUGIN_URL . 'assets/js/ffc-pdf-engine.js', array( 'jquery', 'html2canvas', 'jspdf' ), '1.0.0', true );
            wp_enqueue_script( 'ffc-frontend', FFC_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery', 'ffc-pdf-engine' ), '1.0.0', true );
>>>>>>> Stashed changes
=======
            wp_enqueue_script( 'html2canvas', FFC_PLUGIN_URL . 'assets/js/html2canvas.min.js', array(), '1.4.1', true );
            wp_enqueue_script( 'jspdf', FFC_PLUGIN_URL . 'assets/js/jspdf.umd.min.js', array(), '2.5.1', true );
            wp_enqueue_script( 'ffc-pdf-engine', FFC_PLUGIN_URL . 'assets/js/ffc-pdf-engine.js', array( 'jquery', 'html2canvas', 'jspdf' ), '1.0.0', true );
            wp_enqueue_script( 'ffc-frontend', FFC_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery', 'ffc-pdf-engine' ), '1.0.0', true );
>>>>>>> Stashed changes

            wp_localize_script( 'ffc-frontend', 'ffc_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'ffc_frontend_nonce' ),
                'strings'  => array(
                    'verifying'             => __( 'Verifying...', 'ffc' ),
                    'verify'                => __( 'Verify', 'ffc' ),
                    'processing'            => __( 'Processing...', 'ffc' ),
                    'submit'                => __( 'Generate Certificate', 'ffc' ),
                    'connectionError'       => __( 'Connection error.', 'ffc' ),
                    'generatingCertificate' => __( 'Generating certificate...', 'ffc' ),
                    'idInvalid'             => __( 'The ID/CPF must have 7 or 11 digits.', 'ffc' ),
                )
            ));
        }
    }

<<<<<<< Updated upstream
<<<<<<< Updated upstream
    // =========================================================================
    // HELPER FUNCTIONS
    // =========================================================================

    private function recursive_sanitize( $data ) {
        if ( is_array( $data ) ) {
            $sanitized = array();
            foreach ( $data as $key => $value ) {
                $sanitized[ sanitize_key( $key ) ] = $this->recursive_sanitize( $value );
            }
            return $sanitized;
        }
        return wp_kses( $data, FFC_Utils::get_allowed_html_tags() );
    }

    private function get_new_captcha_data() {
        $n1 = rand( 1, 9 );
        $n2 = rand( 1, 9 );
        return array(
            'label' => sprintf( esc_html__( 'Security: How much is %d + %d?', 'ffc' ), $n1, $n2 ) . ' <span class="required">*</span>',
            'hash'  => wp_hash( ($n1 + $n2) . 'ffc_math_salt' )
        );
    }

    private function generate_security_fields() {
        $captcha = $this->get_new_captcha_data();
        ob_start();
        ?>
        <div class="ffc-security-container">
            <div style="position: absolute; left: -9999px; display: none;">
                <label><?php esc_html_e('Do not fill this field if you are human:', 'ffc'); ?></label>
                <input type="text" name="ffc_honeypot_trap" value="" tabindex="-1" autocomplete="off">
            </div>

            <div class="ffc-captcha-row">
                <label for="ffc_captcha_ans">
                    <?php echo $captcha['label']; ?>
                </label>
                <input type="number" name="ffc_captcha_ans" id="ffc_captcha_ans" class="ffc-input" required>
                <input type="hidden" name="ffc_captcha_hash" value="<?php echo esc_attr( $captcha['hash'] ); ?>">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function validate_security_fields( $data ) {
        if ( ! empty( $data['ffc_honeypot_trap'] ) ) {
            return __( 'Security Error: Request blocked (Honeypot).', 'ffc' );
        }
        if ( ! isset( $data['ffc_captcha_ans'] ) || ! isset( $data['ffc_captcha_hash'] ) ) {
            return __( 'Error: Please answer the security question.', 'ffc' );
        }
        $user_ans = trim( $data['ffc_captcha_ans'] );
        $hash_sent = $data['ffc_captcha_hash'];
        $check_hash = wp_hash( $user_ans . 'ffc_math_salt' );

        if ( $check_hash !== $hash_sent ) {
            return __( 'Error: The math answer is incorrect.', 'ffc' );
        }
        return true; 
    }

    // =========================================================================
    // 1. VERIFICATION PAGE (SHORTCODE)
    // =========================================================================
    public function shortcode_verification_page( $atts ) {
        ob_start();
        $input_raw = isset( $_POST['ffc_auth_code'] ) ? sanitize_text_field( $_POST['ffc_auth_code'] ) : '';
        $result_html = '';
        $error_msg = '';

        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ffc_auth_code']) ) {
            $security_check = $this->validate_security_fields( $_POST );
            
            if ( $security_check !== true ) {
                $error_msg = $security_check;
            } elseif ( empty( $input_raw ) ) {
                $error_msg = __( 'Please enter the code.', 'ffc' );
            } else {
                global $wpdb;
                $table_name = $wpdb->prefix . 'ffc_submissions';
                $clean_code = preg_replace( '/[^A-Z0-9]/', '', strtoupper( $input_raw ) );
                $like_query = '%' . $wpdb->esc_like( '"auth_code":"' . $clean_code . '"' ) . '%';
                $submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE data LIKE %s LIMIT 1", $like_query ) );

                if ( $submission ) {
                    $data = json_decode( $submission->data, true );
                    if ( is_null( $data ) ) $data = json_decode( stripslashes( $submission->data ), true );
                    $form = get_post( $submission->form_id );
                    $form_title = $form ? $form->post_title : 'N/A';
                    $date_generated = date_i18n( get_option('date_format') . ' ' . get_option('time_format'), strtotime( $submission->submission_date ) );
                    $display_code = isset($data['auth_code']) ? $data['auth_code'] : $clean_code;

                    $result_html .= '<div class="ffc-verify-success">';
                    $result_html .= '<h3>✅ ' . esc_html__( 'Authentic Certificate', 'ffc' ) . '</h3>';
                    $result_html .= '<p><strong>' . esc_html__( 'Code:', 'ffc' ) . '</strong> ' . esc_html( $display_code ) . '</p>';
                    $result_html .= '<p><strong>' . esc_html__( 'Event:', 'ffc' ) . '</strong> ' . esc_html( $form_title ) . '</p>';
                    $result_html .= '<p><strong>' . esc_html__( 'Issued on:', 'ffc' ) . '</strong> ' . esc_html( $date_generated ) . '</p>';
                    $result_html .= '<hr>';
                    $result_html .= '<h4>' . esc_html__( 'Participant Data:', 'ffc' ) . '</h4><ul style="list-style:none; padding:0;">';
                    if ( is_array( $data ) ) {
                        foreach ( $data as $key => $value ) {
                            if ( in_array( $key, array('auth_code', 'cpf_rf', 'ticket', 'fill_date', 'date', 'submission_date', 'fill_time'), true ) ) continue;
                            $label = ucfirst( str_replace( '_', ' ', $key ) );
                            $result_html .= '<li><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $value ) . '</li>';
                        }
                    }
                    $result_html .= '</ul></div>';
                } else {
                    $error_msg = __( 'Certificate not found or invalid code.', 'ffc' );
                }
            }
            if ( ! empty( $error_msg ) ) {
                $result_html .= '<div class="ffc-verify-error">❌ ' . esc_html( $error_msg ) . '</div>';
            }
        }
        ?>
        <div class="ffc-verification-container">
            <form method="POST" class="ffc-verification-form">
                <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center; align-items: flex-start;">
                    <input type="text" name="ffc_auth_code" class="ffc-input ffc-verify-input" value="<?php echo esc_attr( $input_raw ); ?>" placeholder="<?php esc_attr_e( 'Enter code...', 'ffc' ); ?>" required maxlength="14">
                    <button type="submit" class="ffc-submit-btn"><?php esc_html_e( 'Verify', 'ffc' ); ?></button>
                </div>
                <div class="ffc-no-js-security"><?php echo $this->generate_security_fields(); ?></div>
            </form>
            <div class="ffc-verify-result"><?php echo $result_html; ?></div>
        </div>
        <?php
        return ob_get_clean();
    }

    // =========================================================================
    // 2. ISSUANCE FORM (SHORTCODE)
    // =========================================================================
    public function shortcode_form( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'ffc_form' );
=======
=======
>>>>>>> Stashed changes
    /**
     * 5 - Render Form Shortcode (i18n applied)
     */
    public function render_form_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts );
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        $form_id = absint( $atts['id'] );

        if ( ! $form_id || get_post_type( $form_id ) !== 'ffc_form' ) {
            return sprintf( '<p class="ffc-error">%s</p>', __( 'Invalid form ID.', 'ffc' ) );
        }

        $fields = get_post_meta( $form_id, '_ffc_form_fields', true );
        if ( empty( $fields ) || ! is_array( $fields ) ) {
            return sprintf( '<p class="ffc-error">%s</p>', __( 'No fields configured for this form.', 'ffc' ) );
        }

        // Math Challenge Security
        $n1 = rand(1, 9);
        $n2 = rand(1, 9);
        $hash = wp_hash( ($n1 + $n2) . 'ffc_math_salt' );

        ob_start(); ?>
        <div class="ffc-form-container">
            <form class="ffc-frontend-form" data-form-id="<?php echo $form_id; ?>">
                
                <?php foreach ( $fields as $field ) : 
                    $type     = $field['type'] ?? 'text';
                    $name     = esc_attr( $field['name'] );
                    $label    = esc_html( $field['label'] );
                    $required = ! empty( $field['required'] ) ? 'required' : '';
                    
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                    // Tratamento especial para CPF/RF
                    if ( $name === 'cpf_rf' ) $type = 'tel'; 

                    // CORREÇÃO: Renderiza campo hidden fora da estrutura visual
                    if ( $type === 'hidden' ) : ?>
                        <input type="hidden" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $default ); ?>">
                        <?php continue; ?>
                    <?php endif; ?>
                    
                    <div class="ffc-form-field">
                        <label for="<?php echo esc_attr( $name ); ?>">
                            <?php echo esc_html( $label ); ?> 
                            <?php if ( $is_req ) echo '<span class="required">*</span>'; ?>
=======
=======
>>>>>>> Stashed changes
                    $logic_attrs = '';
                    $logic_class = '';
                    if ( ! empty( $field['logic_enabled'] ) && ! empty( $field['logic_target'] ) ) {
                        $logic_class = 'ffc-conditional-field';
                        $logic_attrs = sprintf(
                            'data-logic-target="%s" data-logic-val="%s"',
                            esc_attr( $field['logic_target'] ),
                            esc_attr( $field['logic_value'] ?? '' )
                        );
                    }
                ?>
                    <div class="ffc-field-group <?php echo $logic_class; ?>" <?php echo $logic_attrs; ?>>
                        <label for="ffc_field_<?php echo $name; ?>">
                            <?php echo $label; ?>
                            <?php if ( $required ) echo '<span class="ffc-required">*</span>'; ?>
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                        </label>

                        <?php if ( $type === 'select' ) : ?>
                            <select name="<?php echo $name; ?>" id="ffc_field_<?php echo $name; ?>" class="ffc-input" <?php echo $required; ?>>
                                <option value=""><?php _e( 'Select...', 'ffc' ); ?></option>
                                <?php 
                                $options = explode( ',', $field['options'] ?? '' );
                                foreach ( $options as $opt ) : 
                                    $opt = trim( $opt );
                                    if ( ! $opt ) continue;
                                ?>
                                    <option value="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ( $type === 'radio' ) : ?>
                            <div class="ffc-radio-wrapper">
                                <?php 
                                $options = explode( ',', $field['options'] ?? '' );
                                foreach ( $options as $opt ) : 
                                    $opt = trim( $opt );
                                    if ( ! $opt ) continue;
                                ?>
                                    <label class="ffc-radio-label">
                                        <input type="radio" name="<?php echo $name; ?>" value="<?php echo esc_attr( $opt ); ?>" <?php echo $required; ?>>
                                        <?php echo esc_html( $opt ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php else : ?>
                            <input type="<?php echo esc_attr( $type ); ?>" 
                                   name="<?php echo $name; ?>" 
                                   id="ffc_field_<?php echo $name; ?>" 
                                   class="ffc-input" 
                                   placeholder="<?php echo esc_attr( $label ); ?>" 
                                   <?php echo $required; ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="ffc-security-section">
                    <div class="ffc-math-captcha">
                        <label><?php printf( __( 'Security: How much is %d + %d?', 'ffc' ), $n1, $n2 ); ?></label>
                        <input type="number" name="ffc_captcha_ans" class="ffc-input" required>
                        <input type="hidden" name="ffc_captcha_hash" value="<?php echo $hash; ?>">
                    </div>
                    <div class="ffc-hp-hidden">
                        <input type="text" name="ffc_honeypot_trap" tabindex="-1" autocomplete="off">
                    </div>
                </div>

                <div class="ffc-form-submit">
                    <button type="submit" class="ffc-submit-btn">
                        <span class="btn-text"><?php _e( 'Generate Certificate', 'ffc' ); ?></span>
                        <span class="ffc-spinner"></span>
                    </button>
                </div>

                <div class="ffc-form-response"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * 3 - Mitigate broken logic: Form Submission Handling
     */
    public function handle_ffc_submit_form_ajax() {
        check_ajax_referer( 'ffc_frontend_nonce', 'nonce' );
        
        $security_check = $this->validate_security_fields( $_POST );
        if ( $security_check !== true ) {
            wp_send_json_error( array( 'message' => $security_check ) );
        }

        $form_id = absint( $_POST['form_id'] );
        $fields_config = get_post_meta( $form_id, '_ffc_form_fields', true );
        $form_config = get_post_meta( $form_id, '_ffc_form_config', true );
        
        $submission_data = array();
        $user_email = '';

        foreach ( $fields_config as $field ) {
            $name = $field['name'];
            if ( isset( $_POST[ $name ] ) ) {
                $val = $this->recursive_sanitize( $_POST[ $name ] );
                // Specific cleanup for CPF fields
                if ( strpos($name, 'cpf') !== false ) $val = preg_replace('/\D/', '', $val);
                
                $submission_data[ $name ] = $val;
                if ( ($field['type'] ?? '') === 'email' ) $user_email = sanitize_email( $val );
            }
        }

        $val_cpf = $submission_data['cpf_rf'] ?? $submission_data['cpf'] ?? '';
        $val_ticket = $submission_data['ticket'] ?? '';

        $existing = $this->get_existing_submission($form_id, $val_cpf, $val_ticket);

        if ( $existing ) {
            $saved_data = json_decode( $existing->data, true ) ?: json_decode( stripslashes( $existing->data ), true );
            $submission_data = $saved_data;
            $submission_data['auth_code'] = $existing->auth_code;
            $msg = __( 'Certificate already issued. Generating second copy...', 'ffc' );
        } else {
            // Restriction check logic
            if ( !empty($form_config['enable_restriction']) ) {
                if ( ! FFC_Utils::is_user_authorized( $val_cpf, $val_ticket, $form_config ) ) {
                    wp_send_json_error( array( 'message' => __( 'Access denied or invalid ticket.', 'ffc' ) ) );
                }
            }

            $result_id = $this->submission_handler->process_submission( $form_id, get_the_title($form_id), $submission_data, $user_email, $fields_config, $form_config );
            
            if ( is_wp_error( $result_id ) ) {
                wp_send_json_error( array( 'message' => $result_id->get_error_message() ) );
            }

            // Email automation
            if ( ! empty( $form_config['send_user_email'] ) && ! empty( $user_email ) ) {
                $this->trigger_email_notification( $result_id, $form_id, $submission_data, $user_email, $form_config );
            }

            // Ticket consumption (moved to Utils/Handler)
            if ( ! empty( $val_ticket ) ) {
                FFC_Utils::consume_ticket( $form_id, $val_ticket );
            }
            
            $msg = !empty($form_config['success_message']) ? $form_config['success_message'] : __( 'Certificate generated successfully!', 'ffc' );
        }

        $pdf_html = $this->submission_handler->generate_pdf_html( $submission_data, get_the_title($form_id), $form_config );
        $final_bg = $this->determine_background( $form_id, $submission_data, $form_config );

        wp_send_json_success( array( 
            'message' => $msg,
            'pdf_data' => array( 
                'template'   => $pdf_html, 
                'bg_image'   => $final_bg,
                'form_title' => get_the_title($form_id)
            )
        ) );
    }

    /**
     * 5 - Verification Shortcode (Cleaned and translated)
     */
    public function render_verification_shortcode( $atts ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ffc_submissions';
        
        $auth_code = isset( $_GET['auth'] ) ? sanitize_text_field( $_GET['auth'] ) : '';
        $auth_hash = isset( $_GET['hash'] ) ? sanitize_text_field( $_GET['hash'] ) : '';
        
        $submission = null;
        if ( ! empty( $auth_code ) && ! empty( $auth_hash ) ) {
            $submission = $wpdb->get_row( $wpdb->prepare( 
                "SELECT * FROM $table WHERE auth_code = %s AND auth_hash = %s LIMIT 1", 
                $auth_code, $auth_hash 
            ) );
        }

<<<<<<< Updated upstream
<<<<<<< Updated upstream
        if ( $result ) {
            $data = json_decode( $result->data, true );
            if ( !is_array($data) ) $data = json_decode( stripslashes( $result->data ), true );
            $form_title = get_the_title( $result->form_id );
            $student_name = isset($data['name']) ? $data['name'] : (isset($data['nome']) ? $data['nome'] : 'N/A');
=======
=======
>>>>>>> Stashed changes
        ob_start(); ?>
        <div class="ffc-verification-container">
            <?php if ( $submission ) : 
                $this->render_verification_success_ui($submission);
            else : ?>
                <div class="ffc-manual-verify">
                    <h3><?php _e( 'Verify Certificate', 'ffc' ); ?></h3>
                    <p><?php _e( 'Enter the authentication code to validate the document.', 'ffc' ); ?></p>
                    <form class="ffc-verification-form">
                        <input type="text" name="ffc_auth_code" class="ffc-input" placeholder="Ex: ABCD-1234-EFGH" required>
                        <button type="submit" class="ffc-submit-btn"><?php _e( 'Verify', 'ffc' ); ?></button>
                    </form>
                    <div class="ffc-verify-result"></div>
                </div>
            <?php endif; ?>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * 3 - Handle Manual Verification AJAX
     */
    public function handle_ffc_verify_certificate_ajax() {
        check_ajax_referer( 'ffc_frontend_nonce', 'nonce' );
        $code = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $_POST['ffc_auth_code'] ) );
        
        global $wpdb;
        $res = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ffc_submissions WHERE auth_code = %s LIMIT 1", $code ) );
        
        if ( $res ) {
            $data = json_decode( $res->data, true ) ?: json_decode( stripslashes( $res->data ), true );
            $form_id = $res->form_id;
            $form_config = get_post_meta( $form_id, '_ffc_form_config', true );
            $name = $data['name'] ?? $data['nome'] ?? __( 'Participant', 'ffc' );
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
            
            $pdf_html = $this->submission_handler->generate_pdf_html( $data, get_the_title($form_id), $form_config );
            $pdf_data = array(
                'template'   => $pdf_html,
                'bg_image'   => get_post_meta( $form_id, '_ffc_form_bg', true ),
                'form_title' => get_the_title($form_id)
            );

            $html = sprintf(
                '<div class="ffc-verify-box success">
                    <p>✅ %s</p>
                    <button type="button" class="ffc-submit-btn ffc-btn-download-verify">%s</button>
                </div>',
                sprintf( __( 'Certificate for %s is authentic.', 'ffc' ), '<strong>'.esc_html($name).'</strong>' ),
                __( 'Download PDF', 'ffc' )
            );

            wp_send_json_success( array( 'html' => $html, 'pdf_data' => $pdf_data ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Code not found.', 'ffc' ) ) );
        }
    }

    /**
     * HELPER METHODS
     */

    private function validate_security_fields( $data ) {
        if ( ! empty( $data['ffc_honeypot_trap'] ) ) return __( 'Security Error.', 'ffc' );
        if ( ! isset( $data['ffc_captcha_ans'] ) || ! isset( $data['ffc_captcha_hash'] ) ) return __( 'Please solve the math challenge.', 'ffc' );
        if ( wp_hash( trim($data['ffc_captcha_ans']) . 'ffc_math_salt' ) !== $data['ffc_captcha_hash'] ) return __( 'Incorrect math answer.', 'ffc' );
        return true; 
    }

    private function get_existing_submission( $form_id, $cpf, $ticket ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ffc_submissions';
        $identifier = !empty($ticket) ? $ticket : $cpf;
        if ( empty($identifier) ) return false;
        $like_query = '%' . $wpdb->esc_like( $identifier ) . '%';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE form_id = %d AND data LIKE %s ORDER BY id DESC LIMIT 1", $form_id, $like_query ) );
    }

    private function determine_background( $form_id, $submission_data, $form_config ) {
        $extra_rules = get_post_meta( $form_id, '_ffc_extra_templates', true ) ?: array();
        $final_bg = $form_config['bg_image'] ?? '';

        foreach ( $extra_rules as $rule ) {
            if ( isset($submission_data[$rule['target']]) && $submission_data[$rule['target']] == $rule['value'] ) {
                if ( !empty($rule['bg']) ) $final_bg = $rule['bg'];
                break; 
            }
        }
        return $final_bg;
    }

    private function render_verification_success_ui( $submission ) {
        $data = json_decode( $submission->data, true ) ?: json_decode( stripslashes( $submission->data ), true );
        $form_id = $submission->form_id;
        $form_config = get_post_meta( $form_id, '_ffc_form_config', true );
        
        $pdf_html = $this->submission_handler->generate_pdf_html( $data, get_the_title($form_id), $form_config );
        $pdf_data = array(
            'template'   => $pdf_html,
            'bg_image'   => get_post_meta( $form_id, '_ffc_form_bg', true ),
            'form_title' => get_the_title($form_id)
        );
        ?>
        <script>var lastVerifiedPdfData = <?php echo wp_json_encode($pdf_data); ?>;</script>
        <div class="ffc-verify-box success full-view">
            <h3>✅ <?php _e( 'Authentic Certificate', 'ffc' ); ?></h3>
            <hr>
            <p><strong><?php _e( 'Participant:', 'ffc' ); ?></strong> <?php echo esc_html($data['name'] ?? $data['nome'] ?? 'N/A'); ?></p>
            <p><strong><?php _e( 'Event:', 'ffc' ); ?></strong> <?php echo get_the_title($submission->form_id); ?></p>
            <p><strong><?php _e( 'Code:', 'ffc' ); ?></strong> <code><?php echo $submission->auth_code; ?></code></p>
            
            <button type="button" class="ffc-submit-btn ffc-btn-download-verify">
                <?php _e( 'Download Certificate (PDF)', 'ffc' ); ?>
            </button>
        </div>
        <?php
    }

    private function recursive_sanitize( $data ) {
        if ( is_array( $data ) ) {
            return array_map( array( $this, 'recursive_sanitize' ), $data );
        }
        return wp_kses( $data, FFC_Utils::get_allowed_html_tags() );
    }

    private function trigger_email_notification( $result_id, $form_id, $submission_data, $user_email, $form_config ) {
        $email_data = $submission_data;
        $email_data['form_title'] = get_the_title( $form_id );
        $email_data['auth_code']  = get_post_meta( $result_id, '_ffc_auth_code', true ) ?: ($submission_data['auth_code'] ?? '');
        
        $subject = FFC_Email_Manager::parse_placeholders( $form_config['email_subject'] ?? '', $email_data );
        $body    = FFC_Email_Manager::parse_placeholders( $form_config['email_body'] ?? '', $email_data );
        FFC_Email_Manager::queue_email( $user_email, $subject, $body, $form_id, $result_id );
    }
}