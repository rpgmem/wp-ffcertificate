<?php
/**
 * Settings Tab: Data Migrations
 * 
 * Template for the Data Migrations settings tab
 * 
 * @since 2.9.16
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables scoped to this file

// Autoloader handles class loading
$wp_ffcertificate_migration_manager = new \FreeFormCertificate\Migrations\MigrationManager();
$wp_ffcertificate_migrations = $wp_ffcertificate_migration_manager->get_migrations();
?>
<div class="ffc-settings-wrap">

<div class="ffc-migrations-settings-wrap">
    
    <div class="card">
        <h2>⚙️ <?php esc_html_e( 'Database Migrations', 'wp-ffcertificate' ); ?></h2>
        
        <p class="description">
            <?php esc_html_e( 'Manage database structure migrations to improve performance and data organization. These migrations move data from JSON storage to dedicated database columns for faster queries and better reliability.', 'wp-ffcertificate' ); ?>
        </p>
        
        <div class="ffc-migration-warning">
            <p>
                <strong>ℹ️ <?php esc_html_e( 'Important:', 'wp-ffcertificate' ); ?></strong>
                <?php esc_html_e( 'Migrations are safe to run multiple times. Each migration processes up to 100 records at a time. Run again if needed until 100% complete.', 'wp-ffcertificate' ); ?>
            </p>
        </div>
    </div>

    <?php
    // Display migrations
    if ( empty( $wp_ffcertificate_migrations ) ) :
    ?>
        <div class="notice notice-info inline">
            <p><?php esc_html_e( 'No migrations available at this time.', 'wp-ffcertificate' ); ?></p>
        </div>
    <?php
    else :
        foreach ( $wp_ffcertificate_migrations as $wp_ffcertificate_key => $wp_ffcertificate_migration ) :
            // Check if migration is available
            if ( ! $wp_ffcertificate_migration_manager->is_migration_available( $wp_ffcertificate_key ) ) {
                continue;
            }
            
            // Get migration status
            $wp_ffcertificate_status = $wp_ffcertificate_migration_manager->get_migration_status( $wp_ffcertificate_key );
            
            if ( is_wp_error( $wp_ffcertificate_status ) ) {
                // ✅ v2.9.16: If there's an error, assume no data exists (empty database)
                $wp_ffcertificate_percent = 100;
                $wp_ffcertificate_is_complete = true;
                $wp_ffcertificate_pending = 0;
                $wp_ffcertificate_total = 0;
                $wp_ffcertificate_migrated = 0;
            } else {
                $wp_ffcertificate_percent = $wp_ffcertificate_status['percent'];
                $wp_ffcertificate_is_complete = $wp_ffcertificate_status['is_complete'];
                $wp_ffcertificate_pending = number_format( $wp_ffcertificate_status['pending'] );
                $wp_ffcertificate_total = number_format( $wp_ffcertificate_status['total'] );
                $wp_ffcertificate_migrated = number_format( $wp_ffcertificate_status['migrated'] );
            }
            
            // Generate migration URL
            $wp_ffcertificate_migrate_url = wp_nonce_url(
                add_query_arg( array(
                    'post_type' => 'ffc_form',
                    'page' => 'ffc-settings',
                    'tab' => 'migrations',
                    'ffc_run_migration' => $wp_ffcertificate_key
                ), admin_url( 'edit.php' ) ),
                'ffc_migration_' . $wp_ffcertificate_key
            );
            
            $wp_ffcertificate_status_class = $wp_ffcertificate_is_complete ? 'complete' : 'pending';
            $wp_ffcertificate_progress_color = $wp_ffcertificate_is_complete ? 'complete' : 'pending';
            $wp_ffcertificate_stat_pending_class = $wp_ffcertificate_is_complete ? 'success' : 'pending';
            $wp_ffcertificate_stat_progress_class = $wp_ffcertificate_is_complete ? 'success' : 'info';
            $wp_ffcertificate_label_class = $wp_ffcertificate_percent > 50 ? 'dark' : 'light';
    ?>
    
    <div class="postbox ffc-migration-card ffc-migration-<?php echo esc_attr( $wp_ffcertificate_status_class ); ?>">
        <div class="postbox-header">
            <h3 class="hndle">
                <span><?php echo esc_html( $wp_ffcertificate_migration['icon'] . ' ' . $wp_ffcertificate_migration['name'] ); ?></span>
                <?php if ( $wp_ffcertificate_is_complete ) : ?>
                    <span class="dashicons dashicons-yes-alt"></span>
                <?php endif; ?>
            </h3>
        </div>
        
        <div class="inside">
            <p class="description">
                <?php echo esc_html( $wp_ffcertificate_migration['description'] ); ?>
            </p>
            
            <!-- Migration Statistics -->
            <div class="ffc-migration-stats">
                <div>
                    <div class="ffc-migration-stat-label">
                        <?php esc_html_e( 'Total Records', 'wp-ffcertificate' ); ?>
                    </div>
                    <div class="ffc-migration-stat-value">
                        <?php echo esc_html( $wp_ffcertificate_total ); ?>
                    </div>
                </div>
                
                <div>
                    <div class="ffc-migration-stat-label">
                        <?php esc_html_e( 'Migrated', 'wp-ffcertificate' ); ?>
                    </div>
                    <div class="ffc-migration-stat-value success">
                        <?php echo esc_html( $wp_ffcertificate_migrated ); ?>
                    </div>
                </div>
                
                <div>
                    <div class="ffc-migration-stat-label">
                        <?php esc_html_e( 'Pending', 'wp-ffcertificate' ); ?>
                    </div>
                    <div class="ffc-migration-stat-value <?php echo esc_attr( $wp_ffcertificate_stat_pending_class ); ?>">
                        <?php echo esc_html( $wp_ffcertificate_pending ); ?>
                    </div>
                </div>
                
                <div>
                    <div class="ffc-migration-stat-label">
                        <?php esc_html_e( 'Progress', 'wp-ffcertificate' ); ?>
                    </div>
                    <div class="ffc-migration-stat-value <?php echo esc_attr( $wp_ffcertificate_stat_progress_class ); ?>">
                        <?php echo esc_html( number_format( $wp_ffcertificate_percent, 1 ) ); ?>%
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="ffc-migration-progress-bar">
                <div class="ffc-progress-bar-container">
                    <div class="ffc-progress-bar-fill <?php echo esc_attr( $wp_ffcertificate_progress_color ); ?>"></div>
                    <div class="ffc-progress-bar-label <?php echo esc_attr( $wp_ffcertificate_label_class ); ?>">
                        <?php echo esc_html( number_format( $wp_ffcertificate_percent, 1 ) ); ?>% <?php esc_html_e( 'Complete', 'wp-ffcertificate' ); ?>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="ffc-migration-actions">
                <?php if ( $wp_ffcertificate_is_complete ) : ?>
                    <span class="button button-secondary" disabled>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e( 'Migration Complete', 'wp-ffcertificate' ); ?>
                    </span>
                    
                    <p class="description">
                        ✓ <?php esc_html_e( 'All records have been successfully migrated.', 'wp-ffcertificate' ); ?>
                    </p>
                <?php else : ?>
                    <a href="<?php echo esc_url( $wp_ffcertificate_migrate_url ); ?>"
                       class="button button-primary"
                       onclick="return confirm('<?php echo esc_js( sprintf(
                           /* translators: %s: migration name */
                           __( 'Run %s migration?\n\nThis will automatically process ALL records in batches of 100 until complete.', 'wp-ffcertificate' ), $wp_ffcertificate_migration['name'] ) ); ?>')">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e( 'Run Migration', 'wp-ffcertificate' ); ?>
                    </a>

                    <p class="description">
                        <?php
                        printf(
                            /* translators: %s: number of pending records */
                            esc_html__( 'Click once to process all records automatically. %s records remaining.', 'wp-ffcertificate' ),
                            '<strong>' . esc_html( $wp_ffcertificate_pending ) . '</strong>'
                        );
                        ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php
        endforeach;
    endif;
    ?>
    
    <!-- Help Section -->
    <div class="card ffc-migration-help">
        <h3>❓ <?php esc_html_e( 'Need Help?', 'wp-ffcertificate' ); ?></h3>
        
        <p><strong><?php esc_html_e( 'What are migrations?', 'wp-ffcertificate' ); ?></strong></p>
        <p><?php esc_html_e( 'Migrations improve database performance by moving frequently queried data from JSON format to dedicated database columns. This makes searches and filtering much faster.', 'wp-ffcertificate' ); ?></p>
        
        <p><strong><?php esc_html_e( 'Is it safe?', 'wp-ffcertificate' ); ?></strong></p>
        <p><?php esc_html_e( 'Yes! Migrations only copy data to new columns - your original data remains intact. They are safe to run multiple times.', 'wp-ffcertificate' ); ?></p>
        
        <p><strong><?php esc_html_e( 'How many times should I run it?', 'wp-ffcertificate' ); ?></strong></p>
        <p><?php esc_html_e( 'Just click "Run Migration" once! The process will automatically continue processing batches of 100 records until complete. You can watch the progress bar update in real-time.', 'wp-ffcertificate' ); ?></p>
        
        <p><strong><?php esc_html_e( 'Can I undo a migration?', 'wp-ffcertificate' ); ?></strong></p>
        <p><?php esc_html_e( 'Migrations cannot be undone, but they don\'t delete any data. If you experience issues, your original data remains in the JSON column and can be accessed.', 'wp-ffcertificate' ); ?></p>
    </div>

</div>
</div><!-- .ffc-settings-wrap -->
<!-- Migration batch scripts in ffc-admin-migrations.js -->