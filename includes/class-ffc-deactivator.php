<?php
<<<<<<< Updated upstream
=======
/**
 * FFC_Deactivator
 * Handles logic when the plugin is deactivated.
 * * @package FastFormCertificates
 */

>>>>>>> Stashed changes
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FFC_Deactivator {

<<<<<<< Updated upstream
=======
    /**
     * POINT 1 & 3: Deactivation Hook.
     * Cleans up transient states and scheduled tasks without deleting user data.
     */
    public static function deactivate() {
        // 1. Clear scheduled CRON tasks to prevent orphan processes
        wp_clear_scheduled_hook( 'ffc_daily_cleanup_hook' );
        wp_clear_scheduled_hook( 'ffc_process_queue_hook' );

        // 2. Flush rewrite rules to remove plugin-specific permalinks
        flush_rewrite_rules();
    }

    /**
     * POINT 2 & 3: Destructive Cleanup (Uninstall Logic).
     * WARNING: This deletes ALL plugin data. 
     * To be called via a dedicated uninstall.php or a "Reset" button.
     */
>>>>>>> Stashed changes
    public static function uninstall_cleanup() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        
       // Add confirmation to prevent accidental deletion
        if ( ! isset( $_POST['confirm_uninstall'] ) || $_POST['confirm_uninstall'] !== 'yes' ) {
            wp_die( 'Confirme a desinstalação para prosseguir.' );
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ffc_submissions';
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
        
=======

        global $wpdb;
        $table_name = $wpdb->prefix . 'ffc_submissions';

        // 1. Drop the custom database table
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

        // 2. Delete global options
>>>>>>> Stashed changes
=======

        global $wpdb;
        $table_name = $wpdb->prefix . 'ffc_submissions';

        // 1. Drop the custom database table
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

        // 2. Delete global options
>>>>>>> Stashed changes
        delete_option( 'ffc_db_version' );
        delete_option( 'ffc_settings' );

        // 3. Delete all Custom Post Types (Forms and Email Queue)
        $post_types = array( 'ffc_form', 'ffc_email_queue' );
        
<<<<<<< Updated upstream
<<<<<<< Updated upstream
        wp_clear_scheduled_hook( 'ffc_daily_cleanup_hook' );
        wp_clear_scheduled_hook( 'ffc_process_submission_hook' );
        
        $args = array(
            'post_type'      => 'ffc_form',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
            'fields'         => 'ids'
        );
        
        $forms = get_posts( $args );
        
        foreach ( $forms as $form_id ) {
            wp_delete_post( $form_id, true );
        }
=======
=======
>>>>>>> Stashed changes
        foreach ( $post_types as $post_type ) {
            $posts = get_posts( array(
                'post_type'      => $post_type,
                'posts_per_page' => -1,
                'post_status'    => 'any',
                'fields'         => 'ids'
            ) );

            if ( ! empty( $posts ) ) {
                foreach ( $posts as $post_id ) {
                    // Force delete (bypass trash)
                    wp_delete_post( $post_id, true );
                }
            }
        }

        // 4. Final permalink refresh
        flush_rewrite_rules();
>>>>>>> Stashed changes
    }
}