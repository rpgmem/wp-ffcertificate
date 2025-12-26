<?php
/**
 * FFC_Submission_Handler
<<<<<<< Updated upstream
<<<<<<< Updated upstream
 * Gerencia o processamento, salvamento, edição e exportação das submissões.
=======
=======
>>>>>>> Stashed changes
 * Manages processing, database storage, and background tasks for certificate submissions.
 *
 * @package FastFormCertificates
 * @version 1.2.1
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Submission_Handler {
    
    /**
     * @var string Database table name.
     */
    protected $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ffc_submissions';

<<<<<<< Updated upstream
<<<<<<< Updated upstream
        // Hook para processamento em background (E-mail/Log)
        add_action( 'ffc_process_submission_hook', array( $this, 'async_process_submission' ), 10, 7 );
        
        // Configuração de SMTP Customizado
=======
        $this->load_dependencies();

        // Hooks para processamento em segundo plano (Melhoria de UX - Critério 1)
        add_action( 'ffc_process_submission_hook', array( $this, 'async_process_submission' ), 10, 7 );
        
        // Integração SMTP Customizada
>>>>>>> Stashed changes
=======
        $this->load_dependencies();

        // Hooks para processamento em segundo plano (Melhoria de UX - Critério 1)
        add_action( 'ffc_process_submission_hook', array( $this, 'async_process_submission' ), 10, 7 );
        
        // Integração SMTP Customizada
>>>>>>> Stashed changes
        add_action( 'phpmailer_init', array( $this, 'configure_custom_smtp' ) );
    }

    private function load_dependencies() {
        if ( file_exists( FFC_PLUGIN_DIR . 'libs/phpqrcode/qrlib.php' ) ) {
            require_once FFC_PLUGIN_DIR . 'libs/phpqrcode/qrlib.php';
        }
    }

    /**
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * Configura o PHPMailer para usar SMTP se definido nas configurações
=======
     * Configura o SMTP se ativado nas configurações globais.
>>>>>>> Stashed changes
=======
     * Configura o SMTP se ativado nas configurações globais.
>>>>>>> Stashed changes
     */
    public function configure_custom_smtp( $phpmailer ) {
        $settings = get_option( 'ffc_settings', array() );
        
        if ( isset( $settings['smtp_mode'] ) && 'custom' === $settings['smtp_mode'] ) {
            $phpmailer->isSMTP();
            $phpmailer->Host       = $settings['smtp_host'] ?? '';
            $phpmailer->SMTPAuth   = true;
            $phpmailer->Port       = absint( $settings['smtp_port'] ?: 587 );
            $phpmailer->Username   = $settings['smtp_user'] ?? '';
            $phpmailer->Password   = $settings['smtp_pass'] ?? '';
            $phpmailer->SMTPSecure = $settings['smtp_secure'] ?? 'tls';
            
            if ( ! empty( $settings['smtp_from_email'] ) ) {
                $phpmailer->From     = $settings['smtp_from_email'];
                $phpmailer->FromName = $settings['smtp_from_name'] ?: get_bloginfo( 'name' );
            }
        }
    }

    /**
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * Busca uma submissão pelo ID
=======
     * Resolve qual template usar baseado em lógica condicional.
     * Critério 4: Permite múltiplos designs para o mesmo formulário.
>>>>>>> Stashed changes
=======
     * Resolve qual template usar baseado em lógica condicional.
     * Critério 4: Permite múltiplos designs para o mesmo formulário.
>>>>>>> Stashed changes
     */
    public function get_resolved_template( $form_id, $submission_data ) {
        $form_config = get_post_meta( $form_id, '_ffc_form_config', true );
        $default_bg  = get_post_meta( $form_id, '_ffc_form_bg', true );
        
        $resolved = array(
            'html'     => $form_config['pdf_layout'] ?? '',
            'bg_image' => $default_bg,
            'rule_id'  => 'default'
        );

        $extra_templates = get_post_meta( $form_id, '_ffc_extra_templates', true );

        if ( is_array( $extra_templates ) ) {
            foreach ( $extra_templates as $rule ) {
                $field_name   = $rule['condition_field'] ?? '';
                $expected_val = strtolower( trim( (string) ($rule['condition_value'] ?? '') ) );

                if ( isset( $submission_data[ $field_name ] ) ) {
                    $user_val = $submission_data[ $field_name ];
                    $match    = is_array( $user_val ) 
                        ? in_array( $expected_val, array_map( 'strtolower', $user_val ) )
                        : ( strtolower( trim( (string) $user_val ) ) === $expected_val );

                    if ( $match ) {
                        $resolved['html']     = $rule['pdf_layout'];
                        $resolved['bg_image'] = ! empty( $rule['bg_image'] ) ? $rule['bg_image'] : $default_bg;
                        $resolved['rule_id']  = $rule['id'];
                        break; 
                    }
                }
            }
        }
        return $resolved;
    }

    /**
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * Processa a submissão inicial e salva no banco de dados
=======
     * Ponto de entrada principal. Salva no banco e agenda o e-mail.
>>>>>>> Stashed changes
=======
     * Ponto de entrada principal. Salva no banco e agenda o e-mail.
>>>>>>> Stashed changes
     */
    public function process_submission( $form_id, $form_title, &$submission_data, $user_email, $fields_config, $form_config ) {
        global $wpdb;

<<<<<<< Updated upstream
<<<<<<< Updated upstream
        // 1. Gera código de autenticação se não existir
        if ( empty( $submission_data['auth_code'] ) ) {
            $submission_data['auth_code'] = strtoupper( wp_generate_password( 12, false ) );
        }

        // 2. Limpeza e padronização de campos de identificação
        $keys_to_clean = array( 'auth_code', 'codigo', 'verification_code', 'cpf', 'cpf_rf', 'rg', 'ticket' );
        foreach ( $submission_data as $key => $value ) {
            if ( in_array( $key, $keys_to_clean ) ) {
                // Remove qualquer coisa que não seja letra ou número e coloca em maiúsculo
                $submission_data[$key] = strtoupper( preg_replace( '/[^a-zA-Z0-9]/', '', $value ) );
            }
        }

        // 3. Prepara dados para o JSON (Removendo e-mails duplicados para salvar espaço)
        $data_to_save = $submission_data;
        $email_keys = array( 'email', 'user_email', 'your-email', 'ffc_email' );
        foreach ( $email_keys as $key ) {
            if ( isset( $data_to_save[$key] ) ) unset( $data_to_save[$key] );
=======
        // 1. Verificação de Bloqueio (Critério 2)
        foreach ( $submission_data as $val ) {
            if ( FFC_Utils::is_identifier_blocked( $val, $form_id ) ) {
                return new WP_Error( 'blocked_user', __( 'Registration restricted for this identifier.', 'ffc' ) );
            }
        }

        // 2. Geração de Código de Autenticação Único
        if ( empty( $submission_data['auth_code'] ) ) {
            $submission_data['auth_code'] = FFC_Utils::generate_random_code( 12 );
>>>>>>> Stashed changes
        }
=======
        // 1. Verificação de Bloqueio (Critério 2)
        foreach ( $submission_data as $val ) {
            if ( FFC_Utils::is_identifier_blocked( $val, $form_id ) ) {
                return new WP_Error( 'blocked_user', __( 'Registration restricted for this identifier.', 'ffc' ) );
            }
        }

        // 2. Geração de Código de Autenticação Único
        if ( empty( $submission_data['auth_code'] ) ) {
            $submission_data['auth_code'] = FFC_Utils::generate_random_code( 12 );
        }
>>>>>>> Stashed changes
        $auth_hash = FFC_Utils::generate_auth_hash( $submission_data['auth_code'] );

        // 3. QR Code (Base64 para embutir direto no PDF/E-mail)
        if ( class_exists( 'QRcode' ) ) {
            $submission_data['qr_code_base64'] = $this->generate_qr_base64( $submission_data['auth_code'] );
        }

        // 4. Sanitização Recursiva
        $sanitized_data = FFC_Utils::sanitize_recursive( $submission_data );

        // 5. Persistência
        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'form_id'         => absint( $form_id ),
                'submission_date' => current_time( 'mysql' ),
                'data'            => wp_json_encode( $sanitized_data ), 
                'user_ip'         => FFC_Utils::get_client_ip(),
                'email'           => sanitize_email( $user_email ),
                'status'          => 'publish',
                'auth_code'       => $sanitized_data['auth_code'],
                'auth_hash'       => $auth_hash
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );
        
        if ( ! $inserted ) {
<<<<<<< Updated upstream
<<<<<<< Updated upstream
            return new WP_Error( 'db_error', __( 'Erro ao salvar submissão no banco de dados.', 'ffc' ) );
=======
            return new WP_Error( 'db_error', __( 'Database storage failed.', 'ffc' ) );
>>>>>>> Stashed changes
=======
            return new WP_Error( 'db_error', __( 'Database storage failed.', 'ffc' ) );
>>>>>>> Stashed changes
        }
        
        $submission_id = $wpdb->insert_id;
        
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        // 4. Agenda o envio de e-mail assíncrono
        wp_schedule_single_event( time() + 2, 'ffc_process_submission_hook', array( $submission_id, $form_id, $form_title, $submission_data, $user_email, $fields_config, $form_config ) );
=======
=======
>>>>>>> Stashed changes
        // 6. Offloading Assíncrono via WP-Cron (Critério 1)
        // Isso evita que o usuário espere o envio de e-mails pesados.
        wp_schedule_single_event( 
            time() + 2, 
            'ffc_process_submission_hook', 
            array( $submission_id, $form_id, $form_title, $sanitized_data, $user_email, $fields_config, $form_config ) 
        );
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes

        return $submission_id;
    }

    /**
<<<<<<< Updated upstream
<<<<<<< Updated upstream
     * Processamento Assíncrono (E-mails)
     */
    public function async_process_submission( $submission_id, $form_id, $form_title, $submission_data, $user_email, $fields_config, $form_config ) {
        // Garante que o e-mail esteja nos dados para a geração do PDF
        if ( ! isset( $submission_data['email'] ) ) {
            $submission_data['email'] = $user_email;
        }
        
        // Gera o HTML final
        $pdf_content = $this->generate_pdf_html( $submission_data, $form_title, $form_config );
        
        // Envio para o Usuário
        if ( isset( $form_config['send_user_email'] ) && $form_config['send_user_email'] == 1 ) {
            $this->send_user_email( $user_email, $form_title, $pdf_content, $form_config );
        }

        // Notificação para o Admin
        $this->send_admin_notification( $form_title, $submission_data, $form_config );
    }

    /**
     * GERA O HTML DO CERTIFICADO
=======
     * Processa a substituição de Merge Tags para gerar o HTML final do certificado.
     */
    public function generate_pdf_html( $submission_data, $form_title, $layout = '', $bg_image = '' ) {
        if ( empty( $layout ) ) {
            $layout = '<div style="text-align:center; padding:50px;"><h1>{{form_title}}</h1><h2>{{name}}</h2><p>{{qrcode}}</p></div>';
        }

        $merge_tags = array(
            '{{form_title}}'      => esc_html( $form_title ),
            '{{submission_date}}' => date_i18n( get_option( 'date_format' ) ),
            '{{validation_url}}'  => esc_url( site_url( '/verificar' ) ),
            '{{qrcode}}'          => ! empty( $submission_data['qr_code_base64'] ) ? '<img src="' . $submission_data['qr_code_base64'] . '" width="120">' : ''
        );

        // Mapeia todos os campos do formulário para tags dinâmicas
        foreach ( $submission_data as $key => $value ) {
            $display_val = is_array( $value ) ? implode( ', ', $value ) : $value;
            
            // Formatadores automáticos por nome de campo
            if ( stripos( $key, 'cpf' ) !== false || stripos( $key, 'document' ) !== false ) {
                $display_val = FFC_Utils::format_document( $display_val );
            }
            if ( 'auth_code' === $key ) {
                $display_val = FFC_Utils::format_auth_code( $display_val );
            }
            
            $merge_tags[ '{{' . $key . '}}' ] = wp_kses( (string) $display_val, FFC_Utils::get_allowed_html_tags() );
        }

        $final_html = str_replace( array_keys( $merge_tags ), array_values( $merge_tags ), $layout );

        if ( ! empty( $bg_image ) ) {
            $final_html = sprintf(
                '<div class="ffc-cert-wrapper" style="background-image:url(\'%s\'); background-repeat:no-repeat; background-size:100%% 100%%; min-height:600px;">%s</div>',
                esc_url( $bg_image ),
                $final_html
            );
        }

        return $final_html;
    }

    /**
     * Execução em background: Gera o certificado e envia os e-mails.
>>>>>>> Stashed changes
     */
=======
     * Processa a substituição de Merge Tags para gerar o HTML final do certificado.
     */
    public function generate_pdf_html( $submission_data, $form_title, $layout = '', $bg_image = '' ) {
        if ( empty( $layout ) ) {
            $layout = '<div style="text-align:center; padding:50px;"><h1>{{form_title}}</h1><h2>{{name}}</h2><p>{{qrcode}}</p></div>';
        }

        $merge_tags = array(
            '{{form_title}}'      => esc_html( $form_title ),
            '{{submission_date}}' => date_i18n( get_option( 'date_format' ) ),
            '{{validation_url}}'  => esc_url( site_url( '/verificar' ) ),
            '{{qrcode}}'          => ! empty( $submission_data['qr_code_base64'] ) ? '<img src="' . $submission_data['qr_code_base64'] . '" width="120">' : ''
        );

        // Mapeia todos os campos do formulário para tags dinâmicas
        foreach ( $submission_data as $key => $value ) {
            $display_val = is_array( $value ) ? implode( ', ', $value ) : $value;
            
            // Formatadores automáticos por nome de campo
            if ( stripos( $key, 'cpf' ) !== false || stripos( $key, 'document' ) !== false ) {
                $display_val = FFC_Utils::format_document( $display_val );
            }
            if ( 'auth_code' === $key ) {
                $display_val = FFC_Utils::format_auth_code( $display_val );
            }
            
            $merge_tags[ '{{' . $key . '}}' ] = wp_kses( (string) $display_val, FFC_Utils::get_allowed_html_tags() );
        }

        $final_html = str_replace( array_keys( $merge_tags ), array_values( $merge_tags ), $layout );

        if ( ! empty( $bg_image ) ) {
            $final_html = sprintf(
                '<div class="ffc-cert-wrapper" style="background-image:url(\'%s\'); background-repeat:no-repeat; background-size:100%% 100%%; min-height:600px;">%s</div>',
                esc_url( $bg_image ),
                $final_html
            );
        }

        return $final_html;
    }

    /**
     * Execução em background: Gera o certificado e envia os e-mails.
     */
>>>>>>> Stashed changes
    public function async_process_submission( $submission_id, $form_id, $form_title, $submission_data, $user_email, $fields_config, $form_config ) {
        $template = $this->get_resolved_template( $form_id, $submission_data );
        $html     = $this->generate_pdf_html( $submission_data, $form_title, $template['html'], $template['bg_image'] );
        
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        if ( empty( $layout ) ) {
            $layout = '<div style="text-align:center; padding: 50px;">
                        <h1>' . esc_html( $form_title ) . '</h1>
                        <p>Certificamos que o portador dos dados abaixo concluiu o evento.</p>
                        <h2>{{name}}</h2>
                        <p>Autenticidade: {{auth_code}}</p>
                      </div>';
        }

        // Tags de Sistema (Datas e Títulos)
        $layout = str_replace( '{{validation_url}}', esc_url( site_url( '/validar' ) ), $layout );
        $layout = str_replace( '{{submission_date}}', date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) ), $layout );
        $layout = str_replace( '{{form_title}}', $form_title, $layout );

        // Se o e-mail foi removido do array para salvar no DB, adicionamos aqui para o PDF
        if ( !isset($submission_data['email']) && isset($submission_data['user_email']) ) {
             $submission_data['email'] = $submission_data['user_email'];
        }

        // Tags Dinâmicas do Formulário (Funciona para text, select, radio, hidden)
        foreach ( $submission_data as $key => $value ) {
            if ( is_array( $value ) ) {
                $value = implode( ', ', $value );
            }

            // Formatação de Documentos e Código de Autenticação
            if ( in_array( $key, array( 'cpf', 'cpf_rf', 'rg' ) ) ) {
                $value = $this->format_document( $value );
            }
            if ( $key === 'auth_code' ) {
                $value = $this->format_auth_code( $value );
            }
            
            $safe_value = wp_kses( $value, FFC_Utils::get_allowed_html_tags() );
            $layout = str_replace( '{{' . $key . '}}', $safe_value, $layout );
        }

        // Garante caminhos absolutos para imagens (evita quebra em e-mails)
        $site_url = untrailingslashit( get_home_url() );
        $layout = preg_replace('/(src|href|background)=["\']\/([^"\']+)["\']/i', '$1="' . $site_url . '/$2"', $layout);

        return $layout;
    }

    private function send_user_email( $to, $form_title, $html_content, $form_config ) {
        $subject = ! empty( $form_config['email_subject'] ) ? $form_config['email_subject'] : sprintf( __( 'Seu Certificado: %s', 'ffc' ), $form_title );
        
        // Prepara o corpo do e-mail mesclando o texto definido com o layout do certificado
        $body_text = isset( $form_config['email_body'] ) ? wpautop( $form_config['email_body'] ) : '';
        $body  = '<div style="font-family: sans-serif; line-height: 1.6; color: #333;">';
        $body .= $body_text;
        $body .= '<div style="margin-top:30px; border:1px solid #eee; border-radius: 8px; overflow: hidden;">';
        $body .= $html_content;
        $body .= '</div></div>'; 
=======
        // 1. E-mail para o Aluno
        if ( ! empty( $form_config['send_user_email'] ) ) {
            $this->send_user_email( $user_email, $form_title, $html, $form_config );
        }

        // 2. Notificação para o Admin
        if ( ! empty( $form_config['email_admin'] ) ) {
            $this->send_admin_notification( $form_title, $submission_data, $form_config );
        }
    }

    /* --- Helpers Privados --- */

    private function generate_qr_base64( $code ) {
        // Gera URL amigável de verificação
        $url = add_query_arg( array( 'ffc_verify' => $code ), site_url( '/verificar' ) );
        
=======
        // 1. E-mail para o Aluno
        if ( ! empty( $form_config['send_user_email'] ) ) {
            $this->send_user_email( $user_email, $form_title, $html, $form_config );
        }

        // 2. Notificação para o Admin
        if ( ! empty( $form_config['email_admin'] ) ) {
            $this->send_admin_notification( $form_title, $submission_data, $form_config );
        }
    }

    /* --- Helpers Privados --- */

    private function generate_qr_base64( $code ) {
        // Gera URL amigável de verificação
        $url = add_query_arg( array( 'ffc_verify' => $code ), site_url( '/verificar' ) );
        
>>>>>>> Stashed changes
        ob_start();
        QRcode::png( $url, null, QR_ECLEVEL_L, 4, 2 );
        $image_data = ob_get_clean();
        
        return 'data:image/png;base64,' . base64_encode( $image_data );
    }

    private function send_user_email( $to, $title, $cert_html, $config ) {
        $subject = ! empty( $config['email_subject'] ) ? $config['email_subject'] : $title;
        $body    = '<div style="font-family:sans-serif; color:#333;">' . wpautop( $config['email_body'] ) . '<div style="margin-top:30px;">' . $cert_html . '</div></div>';
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        
        wp_mail( $to, $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
    }

<<<<<<< Updated upstream
<<<<<<< Updated upstream
    private function send_admin_notification( $form_title, $data, $form_config ) {
        $admins = isset( $form_config['email_admin'] ) ? array_filter(array_map('trim', explode( ',', $form_config['email_admin'] ))) : array( get_option( 'admin_email' ) );
        
        $subject = sprintf( __( 'Nova Emissão: %s', 'ffc' ), $form_title );
        $body    = '<h3>' . __( 'Detalhes da Submissão:', 'ffc' ) . '</h3>';
        $body   .= '<table border="1" cellpadding="10" style="border-collapse:collapse; width:100%; font-family: sans-serif;">';
=======
    private function send_admin_notification( $title, $data, $config ) {
        $admins  = explode( ',', $config['email_admin'] );
        $subject = sprintf( __( '[FFC] Nova Emissão: %s', 'ffc' ), $title );
>>>>>>> Stashed changes
        
        $body = '<h2>' . __( 'Detalhes da Emissão', 'ffc' ) . '</h2><ul>';
        foreach ( $data as $k => $v ) {
<<<<<<< Updated upstream
            $display_v = is_array($v) ? implode(', ', $v) : $v;

            if ( in_array( $k, array( 'cpf', 'cpf_rf', 'rg' ) ) ) { $display_v = $this->format_document( $display_v ); }
            if ( $k === 'auth_code' ) { $display_v = $this->format_auth_code( $display_v ); }

            $label = ucfirst( str_replace('_', ' ', $k) );
            $body .= '<tr><td style="background:#f9f9f9; width:30%;"><strong>' . esc_html( $label ) . '</strong></td><td>' . wp_kses( $display_v, FFC_Utils::get_allowed_html_tags() ) . '</td></tr>';
=======
            if ( strpos( $k, 'base64' ) !== false ) continue;
            $val = is_array( $v ) ? implode( ', ', $v ) : $v;
            $body .= sprintf( '<li><strong>%s:</strong> %s</li>', esc_html( ucfirst( $k ) ), esc_html( $val ) );
>>>>>>> Stashed changes
=======
    private function send_admin_notification( $title, $data, $config ) {
        $admins  = explode( ',', $config['email_admin'] );
        $subject = sprintf( __( '[FFC] Nova Emissão: %s', 'ffc' ), $title );
        
        $body = '<h2>' . __( 'Detalhes da Emissão', 'ffc' ) . '</h2><ul>';
        foreach ( $data as $k => $v ) {
            if ( strpos( $k, 'base64' ) !== false ) continue;
            $val = is_array( $v ) ? implode( ', ', $v ) : $v;
            $body .= sprintf( '<li><strong>%s:</strong> %s</li>', esc_html( ucfirst( $k ) ), esc_html( $val ) );
>>>>>>> Stashed changes
        }
        $body .= '</ul>';

        foreach ( $admins as $email ) {
            wp_mail( trim( $email ), $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
        }
    }
<<<<<<< Updated upstream
<<<<<<< Updated upstream

    /* Helpers de Formatação */
    private function format_document( $value ) {
        $value = preg_replace( '/[^0-9]/', '', $value );
        $len = strlen( $value );
        if ( $len === 7 ) { // RF
            return substr( $value, 0, 3 ) . '.' . substr( $value, 3, 3 ) . '-' . substr( $value, 6, 1 );
        } 
        if ( $len === 11 ) { // CPF
            return substr( $value, 0, 3 ) . '.' . substr( $value, 3, 3 ) . '.' . substr( $value, 6, 3 ) . '-' . substr( $value, 9, 2 );
        }
        return $value;
    }

    private function format_auth_code( $value ) {
        $value = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $value));
        if ( strlen( $value ) === 12 ) {
            return substr( $value, 0, 4 ) . '-' . substr( $value, 4, 4 ) . '-' . substr( $value, 8, 4 );
        }
        return $value;
    }

    /* Gerenciamento de Status */
    public function trash_submission( $id ) { global $wpdb; return $wpdb->update($this->submission_table_name, array('status'=>'trash'), array('id'=>absint($id))); }
    public function restore_submission( $id ) { global $wpdb; return $wpdb->update($this->submission_table_name, array('status'=>'publish'), array('id'=>absint($id))); }
    public function delete_submission( $id ) { global $wpdb; return $wpdb->delete($this->submission_table_name, array('id'=>absint($id))); }

    /**
     * Exportação CSV robusta com suporte a campos dinâmicos
     */
    public function export_csv() {
        global $wpdb;
        $rows = $wpdb->get_results( "SELECT * FROM {$this->submission_table_name} WHERE status = 'publish' ORDER BY id DESC", ARRAY_A );
        if ( empty( $rows ) ) wp_die( 'Nenhuma submissão disponível para exportação.' );
        
        $filename = 'certificados-emitidos-' . date( 'Y-m-d' ) . '.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( "Content-Disposition: attachment; filename={$filename}" );
        
        $output = fopen( 'php://output', 'w' );
        fprintf( $output, chr(0xEF).chr(0xBB).chr(0xBF) ); // UTF-8 BOM para Excel
        
        // Identifica todas as chaves únicas presentes nos JSONs para criar as colunas do CSV
        $all_keys = array();
        foreach($rows as $r){ 
            $d = json_decode($r['data'], true); 
            if(is_array($d)) $all_keys = array_merge($all_keys, array_keys($d)); 
        }
        $dynamic_headers = array_unique($all_keys);
        
        // Cabeçalho fixo + dinâmico
        fputcsv($output, array_merge(array('ID_Sistema', 'Data_Emissao', 'Email_Principal'), $dynamic_headers));
        
        foreach($rows as $row){
            $d = json_decode($row['data'], true) ?: array();
            $line = array($row['id'], $row['submission_date'], $row['email']);
            foreach($dynamic_headers as $h) { 
                $val = isset($d[$h]) ? $d[$h] : ''; 
                $line[] = is_array($val) ? implode(' | ',$val) : $val; 
            }
            fputcsv($output, $line);
        }
        fclose($output); 
        exit;
    }

    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return sanitize_text_field(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        return sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
}