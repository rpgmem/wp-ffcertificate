<?php
/**
 * FFC_Rate_Limit_Activator
 * 
 * Creates database tables for rate limiting system
 * 
 * @since 2.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Rate_Limit_Activator {
    
    /**
     * Create rate limit tables
     * Called on plugin activation or upgrade
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        // ═══════════════════════════════════════════════════════
        // TABLE 1: Rate Limit Tracking
        // ═══════════════════════════════════════════════════════
        $table_limits = $wpdb->prefix . 'ffc_rate_limits';
        
        $sql_limits = "CREATE TABLE $table_limits (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            
            -- Identification
            type ENUM('ip', 'email', 'cpf', 'global') NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            form_id BIGINT UNSIGNED NULL,
            
            -- Counters
            count INT UNSIGNED NOT NULL DEFAULT 1,
            
            -- Time windows
            window_type ENUM('minute', 'hour', 'day', 'week', 'month', 'year') NOT NULL,
            window_start DATETIME NOT NULL,
            window_end DATETIME NOT NULL,
            
            -- Last attempt
            last_attempt DATETIME NOT NULL,
            
            -- Block status
            is_blocked BOOLEAN DEFAULT 0,
            blocked_until DATETIME NULL,
            blocked_reason VARCHAR(255) NULL,
            
            -- Metadata
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            -- Indexes
            INDEX idx_type_identifier (type, identifier),
            INDEX idx_form (form_id),
            INDEX idx_window (window_end),
            INDEX idx_blocked (is_blocked, blocked_until),
            INDEX idx_cleanup (window_end, updated_at),
            UNIQUE KEY unique_tracking (type, identifier, form_id, window_type, window_start)
        ) $charset_collate;";
        
        dbDelta( $sql_limits );
        
        // ═══════════════════════════════════════════════════════
        // TABLE 2: Rate Limit Logs
        // ═══════════════════════════════════════════════════════
        $table_logs = $wpdb->prefix . 'ffc_rate_limit_logs';
        
        $sql_logs = "CREATE TABLE $table_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            
            -- Identification
            type ENUM('ip', 'email', 'cpf', 'global') NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            form_id BIGINT UNSIGNED NULL,
            
            -- Action
            action ENUM('allowed', 'blocked', 'blacklisted', 'whitelisted') NOT NULL,
            reason VARCHAR(255) NULL,
            
            -- Request data
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            
            -- Counters at moment
            current_count INT UNSIGNED NOT NULL,
            max_allowed INT UNSIGNED NOT NULL,
            
            -- Time
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            
            -- Indexes
            INDEX idx_type (type),
            INDEX idx_identifier (identifier),
            INDEX idx_action (action),
            INDEX idx_form (form_id),
            INDEX idx_created (created_at),
            INDEX idx_cleanup (created_at)
        ) $charset_collate;";
        
        dbDelta( $sql_logs );
        
        // Save table creation version
        update_option( 'ffc_rate_limit_db_version', '1.0.0' );
        
        return true;
    }
    
    /**
     * Check if tables exist
     */
    public static function tables_exist() {
        global $wpdb;
        
        $table_limits = $wpdb->prefix . 'ffc_rate_limits';
        $table_logs = $wpdb->prefix . 'ffc_rate_limit_logs';
        
        $limits_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_limits'" ) === $table_limits;
        $logs_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_logs'" ) === $table_logs;
        
        return $limits_exists && $logs_exists;
    }
    
    /**
     * Drop tables (for uninstall)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $table_limits = $wpdb->prefix . 'ffc_rate_limits';
        $table_logs = $wpdb->prefix . 'ffc_rate_limit_logs';
        
        $wpdb->query( "DROP TABLE IF EXISTS $table_limits" );
        $wpdb->query( "DROP TABLE IF EXISTS $table_logs" );
        
        delete_option( 'ffc_rate_limit_db_version' );
        
        return true;
    }
}
