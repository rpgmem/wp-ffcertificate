<?php
/**
 * FFC_Email_Manager
 * Handles email queuing, WP-Cron scheduling, and delivery logging.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Email_Manager {

    /**
     * Constructor: Sets up Cron and Admin UI hooks.
     */
    public function __construct() {
        // Register Cron Event
        add_action( 'ffc_cron_send_emails', array( $this, 'process_queue' ) );
        
        // Admin Columns for the Email Queue
        add_filter( 'manage_ffc_email_queue_posts_columns', array( $this, 'set_custom_columns' ) );
        add_action( 'manage_ffc_email_queue_posts_custom_column', array( $this, 'fill_custom_columns' ), 10, 2 );

        // Schedule event if not already set
        if ( ! wp_next_scheduled( 'ffc_cron_send_emails' ) ) {
            wp_schedule_event( time(), 'every_five_minutes', 'ffc_cron_send_emails' );
        }
    }

    /**
     * Adds an email to the queue for later delivery.
     * * @param string $to            Recipient email.
     * @param string $subject       Email subject.
     * @param string $body          Email HTML content.
     * @param int    $form_id       Originating Form ID.
     * @param int    $submission_id Database Submission ID.
     */
    public static function queue_email( $to, $subject, $body, $form_id, $submission_id ) {
        return wp_insert_post( array(
            'post_type'    => 'ffc_email_queue',
            'post_title'   => sprintf( __( 'To: %s', 'ffc' ), $to ),
            'post_content' => $body,
            'post_status'  => 'private',
            'meta_input'   => array(
                '_ffc_email_to'      => $to,
                '_ffc_email_subject' => $subject,
                '_ffc_email_status'  => 'pending',
                '_ffc_form_id'       => $form_id,
                '_ffc_submission_id' => $submission_id, 
                '_ffc_attempts'      => 0
            )
        ) );
    }

    /**
     * Processes the pending email queue (Triggered by WP-Cron).
     */
    public function process_queue() {
        $emails = get_posts( array(
            'post_type'      => 'ffc_email_queue',
            'posts_per_page' => 10,
            'meta_query'     => array(
                array(
                    'key'   => '_ffc_email_status',
                    'value' => 'pending'
                )
            )
        ) );

        if ( empty( $emails ) ) {
            return;
        }

        foreach ( $emails as $email_post ) {
            $to            = get_post_meta( $email_post->ID, '_ffc_email_to', true );
            $subject       = get_post_meta( $email_post->ID, '_ffc_email_subject', true );
            $submission_id = get_post_meta( $email_post->ID, '_ffc_submission_id', true );
            $body          = $email_post->post_content;
            $headers       = array( 'Content-Type: text/html; charset=UTF-8' );

            $sent = wp_mail( $to, $subject, $body, $headers );

            if ( $sent ) {
                // Log success and remove from queue
                $this->log_email_sent( $submission_id );
                wp_delete_post( $email_post->ID, true ); 
            } else {
                // Handle failures and retries
                $attempts = (int) get_post_meta( $email_post->ID, '_ffc_attempts', true ) + 1;
                update_post_meta( $email_post->ID, '_ffc_attempts', $attempts );
                
                if ( $attempts >= 3 ) {
                    update_post_meta( $email_post->ID, '_ffc_email_status', 'failed' );
                }
            }
        }
    }

    /**
     * Updates the submission record with the actual sent timestamp.
     */
    private function log_email_sent( $submission_id ) {
        if ( ! $submission_id ) return;
        
        $now = current_time( 'mysql' );

        // 1. Update Post Meta for the Admin View
        update_post_meta( $submission_id, '_ffc_email_sent_at', $now );
        
        // 2. Sync with the custom database table JSON data
        global $wpdb;
        $table_name = $wpdb->prefix . 'ffc_submissions';
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT data FROM $table_name WHERE id = %d", $submission_id ) );
        
        if ( $row ) {
            $data = json_decode( $row->data, true );
            $data['email_sent_at'] = $now;
            
            $wpdb->update(
                $table_name,
                array( 'data' => wp_json_encode( $data ) ),
                array( 'id' => $submission_id ),
                array( '%s' ),
                array( '%d' )
            );
        }
    }

    /**
     * Setup custom columns for the queue administration screen.
     */
    public function set_custom_columns( $columns ) {
        return array(
            'cb'        => $columns['cb'],
            'title'     => $columns['title'],
            'recipient' => __( 'Recipient', 'ffc' ),
            'status'    => __( 'Status', 'ffc' ),
            'attempts'  => __( 'Attempts', 'ffc' ),
            'date'      => $columns['date'],
        );
    }

    /**
     * Renders data for custom columns.
     */
    public function fill_custom_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'recipient':
                echo esc_html( get_post_meta( $post_id, '_ffc_email_to', true ) );
                break;
            case 'status':
                $status = get_post_meta( $post_id, '_ffc_email_status', true );
                $color = ( $status === 'pending' ) ? '#cca300' : '#d63638';
                echo '<strong style="color:' . $color . '">' . esc_html( strtoupper( $status ) ) . '</strong>';
                break;
            case 'attempts':
                $attempts = (int) get_post_meta( $post_id, '_ffc_attempts', true );
                echo esc_html( $attempts ) . ' / 3';
                break;
        }
    }

    /**
     * Replaces bracket placeholders with dynamic data.
     */
    public static function parse_placeholders( $text, $data ) {
        $placeholders = array(
            '{{name}}'       => $data['name'] ?? $data['nome'] ?? '',
            '{{auth_code}}'  => $data['auth_code'] ?? '',
            '{{form_title}}' => $data['form_title'] ?? '',
            '{{date}}'       => date_i18n( get_option( 'date_format' ) ),
            '{{cpf_rf}}'     => $data['cpf_rf'] ?? '',
        );

        return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $text );
    }
}