<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Submission_Handler {
    
    protected $submission_table_name;
    
    public function __construct() {
        global $wpdb;
        $this->submission_table_name = $wpdb->prefix . 'ffc_submissions';

        // Hook to inject custom SMTP (if configured in settings)
        add_action( 'phpmailer_init', array( $this, 'configure_custom_smtp' ) );
    }

    /**
     * Configures PHPMailer with plugin settings, if SMTP mode is active.
     */
    public function configure_custom_smtp( $phpmailer ) {
        $settings = get_option( 'ffc_settings', array() );
        
        // Check if Custom mode is active
        if ( isset($settings['smtp_mode']) && $settings['smtp_mode'] === 'custom' ) {
            $phpmailer->isSMTP();
            $phpmailer->Host       = isset($settings['smtp_host']) ? $settings['smtp_host'] : '';
            $phpmailer->SMTPAuth   = true;
            $phpmailer->Port       = isset($settings['smtp_port']) ? (int) $settings['smtp_port'] : 587;
            $phpmailer->Username   = isset($settings['smtp_user']) ? $settings['smtp_user'] : '';
            $phpmailer->Password   = isset($settings['smtp_pass']) ? $settings['smtp_pass'] : '';
            $phpmailer->SMTPSecure = isset($settings['smtp_secure']) ? $settings['smtp_secure'] : 'tls';
            
            // Configure sender
            if ( ! empty( $settings['smtp_from_email'] ) ) {
                $phpmailer->From     = $settings['smtp_from_email'];
                $phpmailer->FromName = isset($settings['smtp_from_name']) ? $settings['smtp_from_name'] : get_bloginfo( 'name' );
            }
        }
    }

    /**
     * Processes submission: Sanitizes, Saves to DB and Schedules tasks.
     * @param int    $form_id
     * @param string $form_title
     * @param array  $submission_data  PASSED BY REFERENCE (&)
     * @param string $user_email
     * @param array  $fields_config
     * @param array  $form_config
     */
    public function process_submission( $form_id, $form_title, &$submission_data, $user_email, $fields_config, $form_config ) {
        global $wpdb;

        // 1. AUTH_CODE GENERATION (If not from form)
        // Uses wp_generate_password for higher entropy/security
        if ( empty( $submission_data['auth_code'] ) ) {
            $submission_data['auth_code'] = strtoupper( wp_generate_password( 12, false ) );
        }

        // 2. DATA CLEANUP (Mask Sanitization)
        if ( is_array( $submission_data ) ) {
            foreach ( $submission_data as $key => $value ) {
                // List of fields that should contain only alphanumeric
                if ( in_array( $key, array( 'auth_code', 'codigo', 'verification_code', 'cpf', 'cpf_rf', 'rg' ) ) ) {
                    $submission_data[$key] = preg_replace( '/[^a-zA-Z0-9]/', '', $value );
                }
            }
        }
        
        // 3. SAVE TO DATABASE
        $inserted = $wpdb->insert(
            $this->submission_table_name,
            array(
                'form_id'         => $form_id,
                'submission_date' => current_time( 'mysql' ),
                'data'            => wp_json_encode( $submission_data ), 
                'user_ip'         => $this->get_user_ip(), // Improved function for real IP
                'email'           => $user_email
            ),
            array( '%d', '%s', '%s', '%s', '%s' )
        );
        
        if ( ! $inserted ) {
            return new WP_Error( 'db_error', __( 'Error saving to database.', 'ffc' ) );
        }
        
        $submission_id = $wpdb->insert_id;

        // 4. Schedule asynchronous processing (email sending)
        wp_schedule_single_event( 
            time() + 2, 
            'ffc_process_submission_hook', 
            array( $submission_id, $form_id, $form_title, $submission_data, $user_email, $fields_config, $form_config ) 
        );

        return $submission_id;
    }

    /**
     * Asynchronous Task: Generates PDF and sends emails
     */
    public function async_process_submission( $submission_id, $form_id, $form_title, $submission_data, $user_email, $fields_config, $form_config ) {
        $pdf_content = $this->generate_pdf_html( $submission_data, $form_title, $form_config );
        
        // Check if option to send email to user is active
        if ( isset( $form_config['send_user_email'] ) && $form_config['send_user_email'] == 1 ) {
            $this->send_user_email( $user_email, $form_title, $pdf_content, $form_config );
        }

        // Send notification to admin (always)
        $this->send_admin_notification( $form_title, $submission_data, $form_config );
    }

    /**
     * Generates Certificate HTML by replacing placeholders
     */
    public function generate_pdf_html( $submission_data, $form_title, $form_config ) {
        $layout = isset( $form_config['pdf_layout'] ) ? $form_config['pdf_layout'] : '';
        if ( empty( $layout ) ) {
            $layout = '<h1>' . esc_html__( 'Certificate:', 'ffc' ) . ' ' . esc_html( $form_title ) . '</h1><p>{{submission_date}}</p>';
        }

        // Replace date
        $layout = str_replace( '{{submission_date}}', date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) ), $layout );

        // Replace form fields (including generated auth_code)
        foreach ( $submission_data as $key => $value ) {
            // esc_html here is crucial to prevent XSS in generated PDF
            $layout = str_replace( '{{' . $key . '}}', esc_html( $value ), $layout );
        }

        return $layout;
    }

    private function send_user_email( $to, $form_title, $html_content, $form_config ) {
        $subject = isset( $form_config['email_subject'] ) && ! empty( $form_config['email_subject'] ) ? $form_config['email_subject'] : sprintf( __( 'Certificate: %s', 'ffc' ), $form_title );
        $body    = isset( $form_config['email_body'] ) ? wpautop( $form_config['email_body'] ) : '';
        
        $body .= '<hr>';
        $body .= $html_content; 

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        wp_mail( $to, $subject, $body, $headers );
    }

    private function send_admin_notification( $form_title, $data, $form_config ) {
        $admins = isset( $form_config['email_admin'] ) ? explode( ',', $form_config['email_admin'] ) : array();
        
        // If no email configured, use WP admin email
        if ( empty( array_filter($admins) ) ) {
             $admins[] = get_option( 'admin_email' );
        }
        
        $subject = sprintf( __( 'New Submission: %s', 'ffc' ), $form_title );
        $body    = __( 'New submission received:', 'ffc' ) . '<br><ul>';
        foreach ( $data as $k => $v ) {
            $body .= '<li><strong>' . esc_html( $k ) . ':</strong> ' . esc_html( $v ) . '</li>';
        }
        $body .= '</ul>';

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        foreach ( $admins as $email ) {
            if ( is_email( trim( $email ) ) ) {
                wp_mail( trim( $email ), $subject, $body, $headers );
            }
        }
    }

    public function delete_submission( $id ) {
        global $wpdb;
        $wpdb->delete( $this->submission_table_name, array( 'id' => $id ), array( '%d' ) );
    }

    public function delete_all_submissions() {
        global $wpdb;
        $wpdb->query( "TRUNCATE TABLE {$this->submission_table_name}" );
    }

    /**
     * Exports data to CSV with Injection Protection
     */
    public function export_csv() {
        global $wpdb;
        $rows = $wpdb->get_results( "SELECT * FROM {$this->submission_table_name}", ARRAY_A );

        if ( empty( $rows ) ) {
            wp_die( __( 'No data to export.', 'ffc' ) );
        }

        $filename = 'submissions-' . date( 'Y-m-d' ) . '.csv';
        
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( "Content-Disposition: attachment; filename={$filename}" );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );
        
        // Collect all dynamic fields (JSON)
        $dynamic_headers = array();
        foreach ( $rows as $row ) {
            $data = json_decode( $row['data'], true );
            if ( is_array( $data ) ) {
                $dynamic_headers = array_merge( $dynamic_headers, array_keys( $data ) );
            }
        }
        $dynamic_headers = array_unique( $dynamic_headers );
        
        $headers = array_merge( array( 'ID', 'Form ID', 'Date', 'IP', 'Email' ), $dynamic_headers );
        
        // Add BOM for Excel compatibility (UTF-8)
        fprintf( $output, chr(0xEF).chr(0xBB).chr(0xBF) );
        fputcsv( $output, $headers );

        foreach ( $rows as $row ) {
            $data = json_decode( $row['data'], true );
            if ( ! is_array( $data ) ) $data = array();

            $csv_row = array( 
                $row['id'], 
                $row['form_id'], 
                $row['submission_date'], 
                $row['user_ip'], 
                $row['email'] 
            );
            
            foreach ( $dynamic_headers as $header ) {
                $val = isset( $data[ $header ] ) ? $data[ $header ] : '';
                // APPLY PROTECTION AGAINST CSV INJECTION
                $csv_row[] = $this->prevent_csv_injection( $val );
            }
            fputcsv( $output, $csv_row );
        }
        
        fclose( $output );
        exit;
    }

    /**
     * Prevents formula execution in Excel (CSV Injection)
     * Adds a single quote if value starts with formula characters
     */
    private function prevent_csv_injection( $value ) {
        if ( is_string( $value ) && preg_match( '/^[\=\+\-\@]/', $value ) ) {
            return "'" . $value; 
        }
        return $value;
    }

    /**
     * Gets real user IP (compatible with Proxy/Cloudflare)
     */
    private function get_user_ip() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            return sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            return sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] );
        } else {
            return sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
        }
    }

    /**
     * Automatic cleanup of old data
     */
    public function run_data_cleanup() {
        $settings = get_option( 'ffc_settings', array() );
        $cleanup_days = isset( $settings['cleanup_days'] ) ? intval( $settings['cleanup_days'] ) : 30;

        if ( $cleanup_days <= 0 ) {
            return;
        }

        global $wpdb;
        $wpdb->query( $wpdb->prepare( 
            "DELETE FROM {$this->submission_table_name} WHERE submission_date < DATE_SUB(NOW(), INTERVAL %d DAY)", 
            $cleanup_days 
        ) );
    }
}