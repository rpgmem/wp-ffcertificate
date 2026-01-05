<?php
/**
 * FFC_Submission_Handler
 * Manages CRUD operations for submissions (database only).
 * 
 * Email logic moved to: FFC_Email_Handler
 * CSV export logic moved to: FFC_CSV_Exporter
 * 
 * v2.8.0: Added magic token support for magic links
 * v2.9.0: Passes submission_id to email handler for QR Code caching
 * v2.9.1: Uses FFC_Utils::get_user_ip() instead of local method
 * v2.9.13: Added cpf_rf column for performance optimization
 * v2.9.16: CLEAN - Removed property, uses FFC_Utils::get_submissions_table()
 * v2.10.0: ENCRYPTION - Encrypts sensitive data (email, CPF, IP, JSON) + LGPD consent
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Submission_Handler {
    
    /**
     * Generate unique magic token for magic links
     * 
     * @since 2.8.0 Magic Links feature
     * @return string 32-character hex token
     */
    private function generate_magic_token() {
        return bin2hex( random_bytes(16) );
    }

    /**
     * Retrieve a submission by ID.
     * 
     * ✅ v2.10.0: Decrypts data automatically
     */
    public function get_submission( $id ) {
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        $submission = $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM {$table} WHERE id = %d", 
            $id 
        ), ARRAY_A );
        
        if ( ! $submission ) {
            return null;
        }
        
        // ✅ v2.10.0: Decrypt if encrypted data exists
        return $this->decrypt_submission_data( $submission );
    }

    /**
     * Retrieve a submission by magic token.
     * 
     * @since 2.8.0 Magic Links feature
     * ✅ v2.10.0: Decrypts data automatically
     * @param string $token Magic token (32 hex characters)
     * @return array|null Submission data or null if not found
     */
    public function get_submission_by_token( $token ) {
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        // Sanitize token (only allow alphanumeric)
        $clean_token = preg_replace( '/[^a-f0-9]/i', '', $token );
        
        if ( strlen( $clean_token ) !== 32 ) {
            return null; // Invalid token length
        }
        
        $submission = $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM {$table} WHERE magic_token = %s LIMIT 1", 
            $clean_token 
        ), ARRAY_A );
        
        if ( ! $submission ) {
            return null;
        }
        
        // ✅ v2.10.0: Decrypt if encrypted data exists
        return $this->decrypt_submission_data( $submission );
    }

    /**
     * ✅ v2.10.0: Process submission with encryption and LGPD consent
     * 
     * Changes:
     * - Encrypts email, CPF/RF, IP, JSON data
     * - Generates searchable hashes
     * - Captures LGPD consent
     * - Maintains backward compatibility (also saves to old columns)
     * 
     * @param int $form_id Form ID
     * @param string $form_title Form title
     * @param array $submission_data Submission data (by reference)
     * @param string $user_email User email
     * @param array $fields_config Field configuration
     * @param array $form_config Form configuration
     * @return int|WP_Error Submission ID or error
     */
    public function process_submission( $form_id, $form_title, &$submission_data, $user_email, $fields_config, $form_config ) {
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        // 1. Generate unique auth code
        if ( empty( $submission_data['auth_code'] ) ) {
            $submission_data['auth_code'] = $this->generate_unique_auth_code();
        }
        
        // 2. Clean and extract mandatory fields
        $clean_auth_code = FFC_Utils::clean_auth_code( $submission_data['auth_code'] );
        
        $clean_cpf_rf = null;
        if ( isset( $submission_data['cpf_rf'] ) && ! empty( $submission_data['cpf_rf'] ) ) {
            $clean_cpf_rf = preg_replace( '/[^0-9]/', '', $submission_data['cpf_rf'] );
        }
        
        // 3. Generate magic token
        $magic_token = $this->generate_magic_token();
        
        // 4. Extract extra data (remove mandatory from JSON)
        $mandatory_keys = array('email', 'cpf_rf', 'auth_code', 'ffc_lgpd_consent');
        $extra_data = array_diff_key( $submission_data, array_flip( $mandatory_keys ) );

        // Ensure data never is "null" string
        $data_json = wp_json_encode( $extra_data );
        if ( $data_json === 'null' || $data_json === false || empty( $data_json ) ) {
            $data_json = '{}';
        }
        
        // 5. Get user IP
        $user_ip = FFC_Utils::get_user_ip();
        
        // 6. v2.10.0: ENCRYPTION - Prepare encrypted data
        $email_encrypted = null;
        $email_hash = null;
        $cpf_encrypted = null;
        $cpf_hash = null;
        $ip_encrypted = null;
        $data_encrypted = null;
        
        if ( class_exists( 'FFC_Encryption' ) && FFC_Encryption::is_configured() ) {
            // Encrypt email
            if ( ! empty( $user_email ) ) {
                $email_encrypted = FFC_Encryption::encrypt( $user_email );
                $email_hash = FFC_Encryption::hash( $user_email );
            }
            
            // Encrypt CPF/RF
            if ( ! empty( $clean_cpf_rf ) ) {
                $cpf_encrypted = FFC_Encryption::encrypt( $clean_cpf_rf );
                $cpf_hash = FFC_Encryption::hash( $clean_cpf_rf );
            }
            
            // Encrypt IP
            if ( ! empty( $user_ip ) ) {
                $ip_encrypted = FFC_Encryption::encrypt( $user_ip );
            }
            
            // Encrypt JSON data
            if ( ! empty( $data_json ) && $data_json !== '{}' ) {
                $data_encrypted = FFC_Encryption::encrypt( $data_json );
            }
        }
        
        // ✅ 7. v2.10.0: LGPD Consent
        $consent_given = isset( $submission_data['ffc_lgpd_consent'] ) && $submission_data['ffc_lgpd_consent'] == '1' ? 1 : 0;
        $consent_ip = $consent_given ? $user_ip : null;
        $consent_date = $consent_given ? current_time( 'mysql' ) : null;
        $consent_text = $consent_given ? __( 'User agreed to Privacy Policy and data storage', 'ffc' ) : null;

        // 8. Insert into database
        $insert_data = array(
            'form_id'         => $form_id,
            'submission_date' => current_time('mysql'),
            'auth_code'       => $clean_auth_code,
            'status'          => 'publish',
            'magic_token'     => $magic_token,
            
            // ✅ Encrypted fields (NEW)
            'email_encrypted' => $email_encrypted,
            'email_hash'      => $email_hash,
            'cpf_rf_encrypted' => $cpf_encrypted,
            'cpf_rf_hash'     => $cpf_hash,
            'user_ip_encrypted' => $ip_encrypted,
            'data_encrypted'  => $data_encrypted,
            
            // ✅ LGPD Consent (NEW)
            'consent_given'   => $consent_given,
            'consent_ip'      => $consent_ip,
            'consent_date'    => $consent_date,
            'consent_text'    => $consent_text
        );
        
        // ✅ v2.10.0: Old columns - only populate if encryption is NOT configured
        // If encryption is active, these MUST be NULL for LGPD compliance
        if ( class_exists( 'FFC_Encryption' ) && FFC_Encryption::is_configured() ) {
            // Encryption ACTIVE - old columns = NULL
            $insert_data['data'] = null;
            $insert_data['user_ip'] = null;
            $insert_data['email'] = null;
            $insert_data['cpf_rf'] = null;
        } else {
            // Encryption INACTIVE - use old columns (backward compatibility)
            $insert_data['data'] = $data_json;
            $insert_data['user_ip'] = $user_ip;
            $insert_data['email'] = $user_email;
            $insert_data['cpf_rf'] = $clean_cpf_rf;
        }
        
        $insert_format = array(
            '%d', // form_id
            '%s', // submission_date
            '%s', // auth_code
            '%s', // status
            '%s', // magic_token
            '%s', // email_encrypted
            '%s', // email_hash
            '%s', // cpf_rf_encrypted
            '%s', // cpf_rf_hash
            '%s', // user_ip_encrypted
            '%s', // data_encrypted
            '%d', // consent_given
            '%s', // consent_ip
            '%s', // consent_date
            '%s', // consent_text
            '%s', // data (old)
            '%s', // user_ip (old)
            '%s', // email (old)
            '%s'  // cpf_rf (old)
        );
        
        $inserted = $wpdb->insert( $table, $insert_data, $insert_format );
        
        if ( ! $inserted ) {
            return new WP_Error( 'db_error', __( 'Error saving submission to the database.', 'ffc' ) );
        }
        
        $submission_id = $wpdb->insert_id;
        
        // ✅ 9. v2.10.0: Log submission creation
        if ( class_exists( 'FFC_Activity_Log' ) ) {
            FFC_Activity_Log::log_submission_created( $submission_id, array(
                'form_id' => $form_id,
                'has_cpf' => ! empty( $clean_cpf_rf ),
                'encrypted' => ! empty( $email_encrypted )
            ) );
            
            // Log consent if given
            if ( $consent_given ) {
                FFC_Activity_Log::log_consent_given( $submission_id, $consent_ip );
            }
        }
        
        // 10. Schedule asynchronous email delivery
        wp_schedule_single_event( 
            time() + 2, 
            'ffc_process_submission_hook', 
            array( 
                $submission_id,
                $form_id, 
                $form_title, 
                $submission_data,
                $user_email, 
                $fields_config, 
                $form_config,
                $magic_token
            ) 
        );

        return $submission_id;
    }

    /**
     * Update an existing submission.
     * Called from admin edit screen.
     * 
     * ✅ v2.10.0: Also updates encrypted fields
     */
    public function update_submission( $id, $new_email, $clean_data ) {
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        // Remove email from data (stored separately in email column)
        $data_to_save = $clean_data;
        $email_keys = array( 'email', 'user_email', 'your-email', 'ffc_email' );
        foreach ( $email_keys as $key ) {
            if ( isset( $data_to_save[$key] ) ) {
                unset( $data_to_save[$key] );
            }
        }
        
        $data_json = wp_json_encode( $data_to_save );
        
        // ✅ v2.10.0: Conditional update based on encryption status
        $update_data = array();
        $update_format = array();
        
        if ( class_exists( 'FFC_Encryption' ) && FFC_Encryption::is_configured() ) {
            // ✅ ENCRYPTION ACTIVE - Use encrypted columns, NULL in old columns
            
            // Old columns = NULL
            $update_data['email'] = null;
            $update_data['data'] = null;
            $update_format[] = '%s';
            $update_format[] = '%s';
            
            // Encrypted columns = encrypted data
            if ( ! empty( $new_email ) ) {
                $update_data['email_encrypted'] = FFC_Encryption::encrypt( $new_email );
                $update_data['email_hash'] = FFC_Encryption::hash( $new_email );
                $update_format[] = '%s';
                $update_format[] = '%s';
            }
            
            if ( ! empty( $data_json ) && $data_json !== '{}' ) {
                $update_data['data_encrypted'] = FFC_Encryption::encrypt( $data_json );
                $update_format[] = '%s';
            }
            
        } else {
            // ✅ NO ENCRYPTION - Use old columns (backward compatibility)
            
            $update_data['email'] = $new_email;
            $update_data['data'] = $data_json;
            $update_format[] = '%s';
            $update_format[] = '%s';
        }
        
        $result = $wpdb->update(
            $table,
            $update_data,
            array( 'id' => absint( $id ) ),
            $update_format,
            array( '%d' )
        );
        
        // ✅ v2.10.0: Log modification
        if ( $result !== false && class_exists( 'FFC_Activity_Log' ) ) {
            FFC_Activity_Log::log_data_modified( $id, array(
                'email_changed' => true,
                'data_changed' => true
            ) );
        }
        
        return $result;
    }

    /**
     * ✅ v2.10.0: Decrypt submission data (hybrid approach)
     * 
     * Priority:
     * 1. Try encrypted fields first
     * 2. Fallback to old plain fields
     * 
     * Made PUBLIC to allow List Table and other classes to decrypt data
     * 
     * @param array $submission Raw submission from database
     * @return array Submission with decrypted data
     */
    public function decrypt_submission_data( $submission ) {
        if ( ! is_array( $submission ) ) {
            return $submission;
        }
        
        // Check if encryption class available
        if ( ! class_exists( 'FFC_Encryption' ) ) {
            return $submission; // Return as-is
        }
        
        // Decrypt email (priority: encrypted > plain)
        if ( ! empty( $submission['email_encrypted'] ) ) {
            $decrypted_email = FFC_Encryption::decrypt( $submission['email_encrypted'] );
            if ( $decrypted_email !== null ) {
                $submission['email'] = $decrypted_email;
            }
        }
        
        // Decrypt CPF/RF (priority: encrypted > plain)
        if ( ! empty( $submission['cpf_rf_encrypted'] ) ) {
            $decrypted_cpf = FFC_Encryption::decrypt( $submission['cpf_rf_encrypted'] );
            if ( $decrypted_cpf !== null ) {
                $submission['cpf_rf'] = $decrypted_cpf;
            }
        }
        
        // Decrypt IP (priority: encrypted > plain)
        if ( ! empty( $submission['user_ip_encrypted'] ) ) {
            $decrypted_ip = FFC_Encryption::decrypt( $submission['user_ip_encrypted'] );
            if ( $decrypted_ip !== null ) {
                $submission['user_ip'] = $decrypted_ip;
            }
        }
        
        // ✅ v2.10.0: Decrypt JSON data AND decode it
        if ( ! empty( $submission['data_encrypted'] ) ) {
            $decrypted_data = FFC_Encryption::decrypt( $submission['data_encrypted'] );
            if ( $decrypted_data !== null ) {
                $submission['data'] = $decrypted_data;
                
                // ✅ CRITICAL: Decode JSON and merge into submission array
                // This makes individual fields accessible (e.g., {{name}}, {{course}})
                $decoded = json_decode( $decrypted_data, true );
                if ( is_array( $decoded ) && json_last_error() === JSON_ERROR_NONE ) {
                    // Merge decoded fields into submission (don't overwrite existing)
                    foreach ( $decoded as $key => $value ) {
                        if ( ! isset( $submission[$key] ) || $submission[$key] === null ) {
                            $submission[$key] = $value;
                        }
                    }
                }
            }
        } elseif ( ! empty( $submission['data'] ) ) {
            // ✅ Fallback: If data is plain (not encrypted), decode it
            $decoded = json_decode( $submission['data'], true );
            if ( is_array( $decoded ) && json_last_error() === JSON_ERROR_NONE ) {
                foreach ( $decoded as $key => $value ) {
                    if ( ! isset( $submission[$key] ) || $submission[$key] === null ) {
                        $submission[$key] = $value;
                    }
                }
            }
        }
        
        return $submission;
    }

    /**
     * Move submission to trash.
     */
    public function trash_submission( $id ) { 
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        return $wpdb->update(
            $table, 
            array('status'=>'trash'), 
            array('id'=>absint($id)),
            array('%s'),
            array('%d')
        ); 
    }
    
    /**
     * Restore submission from trash.
     */
    public function restore_submission( $id ) { 
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        return $wpdb->update(
            $table, 
            array('status'=>'publish'), 
            array('id'=>absint($id)),
            array('%s'),
            array('%d')
        ); 
    }
    
    /**
     * Permanently delete submission.
     */
    public function delete_submission( $id ) { 
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        return $wpdb->delete(
            $table, 
            array('id'=>absint($id)),
            array('%d')
        ); 
    }

    /**
     * Delete all submissions or submissions from a specific form.
     * Used in settings danger zone.
     * 
     * @param int|null $form_id If null, deletes all. If set, deletes only from that form.
     */
    public function delete_all_submissions( $form_id = null ) {
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        if ( $form_id === null ) {
            // Delete all submissions
            return $wpdb->query( "TRUNCATE TABLE {$table}" );
        } else {
            // Delete submissions from specific form
            return $wpdb->delete( 
                $table, 
                array( 'form_id' => absint( $form_id ) ), 
                array( '%d' ) 
            );
        }
    }

    /**
     * Automatic cleanup of old submissions.
     * Called by daily WP-Cron event.
     */
    public function run_data_cleanup() {
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        $settings = get_option( 'ffc_settings', array() );
        $cleanup_days = isset( $settings['cleanup_days'] ) ? absint( $settings['cleanup_days'] ) : 0;
        
        if ( $cleanup_days <= 0 ) {
            return; // Cleanup disabled
        }
        
        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$cleanup_days} days" ) );
        
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table} WHERE submission_date < %s",
            $cutoff_date
        ) );
    }

    /**
     * Ensure submission has a magic token (fallback for old submissions)
     * 
     * This is a safety fallback for submissions created before v2.8.0
     * or if token generation failed during creation.
     * 
     * @since 2.8.0 Magic Links feature
     * @param int $submission_id
     * @return string Magic token
     */
    public function ensure_magic_token( $submission_id ) {
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        $submission = $this->get_submission( $submission_id );
        
        if ( ! $submission ) {
            return '';
        }
        
        // If token exists, return it
        if ( ! empty( $submission['magic_token'] ) ) {
            return $submission['magic_token'];
        }
        
        // Generate new token
        $token = $this->generate_magic_token();
        
        // Save to database
        $wpdb->update(
            $table,
            array( 'magic_token' => $token ),
            array( 'id' => $submission_id ),
            array( '%s' ),
            array( '%d' )
        );
        
        return $token;
    }

    /**
     * Generate unique auth code with collision check
     * 
     * Generates a random 12-character alphanumeric code and verifies it doesn't
     * already exist in the database. While collisions are extremely rare
     * (1 in 62^12 ≈ 3.2 × 10^21), this provides an extra layer of security.
     * 
     * @since 2.10.0
     * @return string Unique auth code (12 characters, uppercase)
     */
    private function generate_unique_auth_code() {
        global $wpdb;
        $table = FFC_Utils::get_submissions_table();
        
        $max_attempts = 10; // Prevent infinite loop (should never need more than 1)
        $attempt = 0;
        
        do {
            // Generate random code (12 chars, alphanumeric only)
            $auth_code = strtoupper( wp_generate_password( 12, false ) );
            
            // Check if exists in database
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE auth_code = %s",
                $auth_code
            ) );
            
            $attempt++;
            
            // If unique, return it
            if ( ! $exists ) {
                return $auth_code;
            }
            
            // Log collision (extremely rare event worth tracking)
            if ( class_exists( 'FFC_Utils' ) && method_exists( 'FFC_Utils', 'debug_log' ) ) {
                FFC_Utils::debug_log( 'Auth code collision detected', array(
                    'code' => $auth_code,
                    'attempt' => $attempt
                ) );
            }
            
        } while ( $attempt < $max_attempts );
        
        // Fallback: if somehow we hit max attempts, use timestamp suffix
        // This should NEVER happen in practice
        $fallback = strtoupper( wp_generate_password( 8, false ) ) . substr( time(), -4 );
        
        if ( class_exists( 'FFC_Utils' ) && method_exists( 'FFC_Utils', 'debug_log' ) ) {
            FFC_Utils::debug_log( 'Auth code generation fallback used', array(
                'attempts' => $attempt,
                'fallback_code' => $fallback
            ) );
        }
        
        return $fallback;
    }

    /**
     * Migrate emails from data JSON to dedicated email column
     * 
     * @since 2.8.0
     * @deprecated 2.9.15 Moved to FFC_Migration_Manager::migrate_emails()
     * @see FFC_Migration_Manager::migrate_emails()
     * @return int Number of migrated emails
     */
    public function migrate_emails_to_column() {
        // Redirect to Migration Manager
        if ( class_exists( 'FFC_Migration_Manager' ) ) {
            $migration_manager = new FFC_Migration_Manager();
            $result = $migration_manager->run_migration( 'emails', 0 );
            return isset( $result['migrated'] ) ? $result['migrated'] : 0;
        }
        
        return 0;
    }
}