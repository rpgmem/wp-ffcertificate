<?php
/**
 * FFC_Utils
<<<<<<< Updated upstream
 * Classe de utilitários compartilhada entre Frontend e Admin.
=======
 * Shared utility class for the Fast Form Certificates plugin.
 * Handles string cleaning, document formatting, and security hashing.
 *
 * @package FastFormCertificates
 * @version 1.2.0
>>>>>>> Stashed changes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Utils {

    /**
<<<<<<< Updated upstream
     * Retorna a lista de tags HTML e atributos permitidos.
     * Centralizamos aqui para que o Frontend, E-mail e Gerador de PDF falem a mesma língua.
=======
     * Generate a secure HMAC hash for certificate validation.
     * Criteria 2: Uses WP Salts for high-entropy encryption.
     * * @param string $auth_code The certificate's unique code.
     * @return string Secure SHA256 hash.
     */
    public static function generate_auth_hash( $auth_code ) {
        // Normalizes the code (removes dashes and forces uppercase)
        $clean_code = strtoupper( preg_replace( '/[^A-Z0-9]/', '', (string) $auth_code ) );
        
        // Use WP Salt for high security encryption. Fallback for local environments.
        $salt = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'ffc_default_secure_salt';

        return hash_hmac( 'sha256', $clean_code, $salt );
    }

    /**
     * Verify if a hash matches the authentication code using constant-time comparison.
     * Prevents Timing Attacks.
     */
    public static function verify_auth_hash( $auth_code, $hash_to_verify ) {
        return hash_equals( self::generate_auth_hash( $auth_code ), $hash_to_verify );
    }

    /**
     * Standardize identifiers by removing non-numeric characters.
     */
    public static function clean_string_to_numbers( $string ) {
        return preg_replace( '/\D/', '', (string) $string );
    }

    /**
     * Format documents for display (Supports CPF 11 digits).
     * Criteria 4: Centralized logic allows future expansion (CNPJ, RG).
     */
    public static function format_document( $value ) {
        $value = self::clean_string_to_numbers( $value );
        
        // CPF Formatting: 000.000.000-00
        if ( strlen( $value ) === 11 ) {
            $formatted = substr( $value, 0, 3 ) . '.' . substr( $value, 3, 3 ) . '.' . substr( $value, 6, 3 ) . '-' . substr( $value, 9, 2 );
            return apply_filters( 'ffc_format_cpf', $formatted, $value );
        }
        
        return apply_filters( 'ffc_format_document_default', $value );
    }

    /**
     * Format authentication code for display (e.g., ABCD-EFGH-IJKL).
     * Criteria 1: Improved readability for the user.
     */
    public static function format_auth_code( $value ) {
        $value = strtoupper( preg_replace( '/[^A-Z0-9]/', '', (string) $value ) );
        if ( strlen( $value ) === 12 ) {
            return substr( $value, 0, 4 ) . '-' . substr( $value, 4, 4 ) . '-' . substr( $value, 8, 4 );
        }
        return $value;
    }

    /**
     * Check if an identifier is in the form's denylist.
     * Logic centralized to prevent bypass by formatting differences.
     */
    public static function is_identifier_blocked( $identifier, $form_id ) {
        $form_config = get_post_meta( $form_id, '_ffc_form_config', true );
        $blocked_list = isset( $form_config['denied_users_list'] ) ? $form_config['denied_users_list'] : '';

        $target = self::clean_string_to_numbers( $identifier );
        if ( empty( $target ) ) {
            return false;
        }

        // Split by newline or comma and clean each item
        $blocked_array = preg_split( '/[\n,]+/', $blocked_list );
        $blocked_array = array_map( array( __CLASS__, 'clean_string_to_numbers' ), $blocked_array );
        $blocked_array = array_filter( $blocked_array ); // Remove empty values

        return in_array( $target, $blocked_array, true );
    }

    /**
     * Generate a random code excluding confusing characters (0, O, 1, I).
     * Criteria 1: Better UX by avoiding character ambiguity.
     */
    public static function generate_random_code( $length = 12 ) {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code  = '';
        $max   = strlen( $chars ) - 1;
        for ( $i = 0; $i < $length; $i++ ) { 
            $code .= $chars[ wp_rand( 0, $max ) ]; 
        }
        return $code;
    }

    /**
     * Recursively sanitizes arrays or strings using kses allowed tags.
     * Criteria 2: Essential for cleaning $submission_data arrays.
     */
    public static function sanitize_recursive( $data ) {
        if ( is_array( $data ) ) {
            foreach ( $data as $key => $value ) {
                $data[ $key ] = self::sanitize_recursive( $value );
            }
        } else {
            // Apply different sanitization based on content
            if ( is_email( $data ) ) {
                $data = sanitize_email( $data );
            } else {
                $data = wp_kses( (string) $data, self::get_allowed_html_tags() );
            }
        }
        return $data;
    }

    /**
     * Centralized allowed HTML tags for PDF and Email context.
     * Criteria 4: Maintains consistency between PDF and Email view.
>>>>>>> Stashed changes
     */
    public static function get_allowed_html_tags() {
        $allowed = array(
            'b'      => array(),
            'strong' => array(),
            'i'      => array(),
            'em'     => array(),
            'u'      => array(),
            'br'     => array(),
            'hr'     => array( 'style' => array(), 'class' => array() ),
            'p'      => array( 'style' => array(), 'class' => array(), 'align' => array() ),
            'span'   => array( 'style' => array(), 'class' => array() ),
            'div'    => array( 'style' => array(), 'class' => array(), 'id' => array() ),
            'img'    => array(
                'src'    => array(),
                'alt'    => array(),
                'style'  => array(),
                'width'  => array(),
                'height' => array(),
            ),
<<<<<<< Updated upstream
            // Tags de tabela (essenciais para alinhamento de assinaturas)
            'table'  => array(
                'style'  => array(),
                'class'  => array(),
                'width'  => array(),
                'border' => array(),
                'cellpadding' => array(),
                'cellspacing' => array(),
            ),
            'tr'     => array(
                'style' => array(),
                'class' => array(),
=======
            'table'  => array(
                'style' => array(), 'class' => array(), 'width' => array(),
                'border' => array(), 'cellpadding' => array(), 'cellspacing' => array(),
>>>>>>> Stashed changes
            ),
            'tr'     => array( 'style' => array(), 'class' => array() ),
            'td'     => array(
                'style' => array(), 'width' => array(), 'colspan' => array(),
                'rowspan' => array(), 'align' => array(), 'valign' => array(),
            ),
<<<<<<< Updated upstream
            // Cabeçalhos
            'h1' => array('style' => array(), 'class' => array()),
            'h2' => array('style' => array(), 'class' => array()),
            'h3' => array('style' => array(), 'class' => array()),
            'h4' => array('style' => array(), 'class' => array()),
            
            // Listas (úteis para conteúdo programático no verso ou corpo)
            'ul' => array('style' => array(), 'class' => array()),
            'ol' => array('style' => array(), 'class' => array()),
            'li' => array('style' => array(), 'class' => array()),
        );

        /**
         * Permite que outros desenvolvedores ou você mesmo adicione tags 
         * sem precisar mexer no core do plugin.
         */
=======
            'h1' => array( 'style' => array() ),
            'h2' => array( 'style' => array() ),
            'h3' => array( 'style' => array() ),
            'ul' => array( 'style' => array() ),
            'li' => array( 'style' => array() ),
        );
>>>>>>> Stashed changes
        return apply_filters( 'ffc_allowed_html_tags', $allowed );
    }

    /**
     * Reliable client IP detection.
     */
    public static function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $parts = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $ip = trim( $parts[0] );
        }
        
        // Validate IP format
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return '0.0.0.0';
        }
        
        return sanitize_text_field( $ip );
    }
}