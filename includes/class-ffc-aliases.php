<?php
declare(strict_types=1);

/**
 * Backward Compatibility Aliases
 *
 * Maps old class names (FFC_*) to new namespaced classes (FreeFormCertificate\*)
 * This ensures that existing code continues to work during the namespace migration.
 *
 * These aliases will be removed in v4.0.0 (see Fase 4 of namespace migration)
 *
 * @since 3.2.0
 * @package FreeFormCertificate
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all backward compatibility aliases
 *
 * @return void
 */
function ffc_register_class_aliases(): void {
    $aliases = ffc_get_class_alias_map();

    foreach ($aliases as $old_class => $new_class) {
        if (!class_exists($old_class) && class_exists($new_class)) {
            class_alias($new_class, $old_class);
        }
    }
}

/**
 * Get the complete class alias mapping
 *
 * @return array<string, string> Map of old class names to new namespaced names
 */
function ffc_get_class_alias_map(): array {
    return [
        // Root level classes
        'FFC_Activator' => 'FreeFormCertificate\\Activator',
        'FFC_Deactivator' => 'FreeFormCertificate\\Deactivator',
        'FFC_Loader' => 'FreeFormCertificate\\Loader',
        'Free_Form_Certificate_Loader' => 'FreeFormCertificate\\Loader', // Legacy alias

        // Admin namespace
        'FFC_Admin' => 'FreeFormCertificate\\Admin\\Admin',
        'FFC_Admin_Activity_Log_Page' => 'FreeFormCertificate\\Admin\\ActivityLogPage',
        'FFC_Admin_Ajax' => 'FreeFormCertificate\\Admin\\Ajax',
        'FFC_Admin_Assets_Manager' => 'FreeFormCertificate\\Admin\\AssetsManager',
        'FFC_Admin_Notice_Manager' => 'FreeFormCertificate\\Admin\\NoticeManager',
        'FFC_Admin_Submission_Edit_Page' => 'FreeFormCertificate\\Admin\\SubmissionEditPage',
        'FFC_Admin_User_Columns' => 'FreeFormCertificate\\Admin\\UserColumns',
        'FFC_CPT' => 'FreeFormCertificate\\Admin\\CustomPostType',
        'FFC_CSV_Exporter' => 'FreeFormCertificate\\Admin\\CsvExporter',
        'FFC_Form_Editor' => 'FreeFormCertificate\\Admin\\FormEditor',
        'FFC_Form_Editor_Metabox_Renderer' => 'FreeFormCertificate\\Admin\\FormEditorMetaboxRenderer',
        'FFC_Form_Editor_Save_Handler' => 'FreeFormCertificate\\Admin\\FormEditorSaveHandler',
        'FFC_Settings' => 'FreeFormCertificate\\Admin\\Settings',
        'FFC_Settings_Save_Handler' => 'FreeFormCertificate\\Admin\\SettingsSaveHandler',
        'FFC_Submissions_List_Table' => 'FreeFormCertificate\\Admin\\SubmissionsListTable',

        // API namespace
        'FFC_REST_Controller' => 'FreeFormCertificate\\API\\RestController',

        // Core namespace
        'FFC_Activity_Log' => 'FreeFormCertificate\\Core\\ActivityLog',
        'FFC_Debug' => 'FreeFormCertificate\\Core\\Debug',
        'FFC_Encryption' => 'FreeFormCertificate\\Core\\Encryption',
        'FFC_Page_Manager' => 'FreeFormCertificate\\Core\\PageManager',
        'FFC_Utils' => 'FreeFormCertificate\\Core\\Utils',

        // Frontend namespace
        'FFC_Form_Processor' => 'FreeFormCertificate\\Frontend\\FormProcessor',
        'FFC_Frontend' => 'FreeFormCertificate\\Frontend\\Frontend',
        'FFC_Shortcodes' => 'FreeFormCertificate\\Frontend\\Shortcodes',
        'FFC_Verification_Handler' => 'FreeFormCertificate\\Frontend\\VerificationHandler',

        // Generators namespace
        'FFC_Magic_Link_Helper' => 'FreeFormCertificate\\Generators\\MagicLinkHelper',
        'FFC_PDF_Generator' => 'FreeFormCertificate\\Generators\\PdfGenerator',
        'FFC_QRCode_Generator' => 'FreeFormCertificate\\Generators\\QRCodeGenerator',

        // Integrations namespace
        'FFC_Email_Handler' => 'FreeFormCertificate\\Integrations\\EmailHandler',
        'FFC_IP_Geolocation' => 'FreeFormCertificate\\Integrations\\IpGeolocation',

        // Migrations namespace
        'FFC_Data_Sanitizer' => 'FreeFormCertificate\\Migrations\\DataSanitizer',
        'FFC_Migration_Manager' => 'FreeFormCertificate\\Migrations\\MigrationManager',
        'FFC_Migration_Registry' => 'FreeFormCertificate\\Migrations\\MigrationRegistry',
        'FFC_Migration_Status_Calculator' => 'FreeFormCertificate\\Migrations\\MigrationStatusCalculator',
        'FFC_Migration_User_Link' => 'FreeFormCertificate\\Migrations\\MigrationUserLink',

        // Migrations\Strategies namespace
        'FFC_Migration_Strategy' => 'FreeFormCertificate\\Migrations\\Strategies\\MigrationStrategyInterface',
        'FFC_Cleanup_Migration_Strategy' => 'FreeFormCertificate\\Migrations\\Strategies\\CleanupMigrationStrategy',
        'FFC_Encryption_Migration_Strategy' => 'FreeFormCertificate\\Migrations\\Strategies\\EncryptionMigrationStrategy',
        'FFC_Field_Migration_Strategy' => 'FreeFormCertificate\\Migrations\\Strategies\\FieldMigrationStrategy',
        'FFC_Magic_Token_Migration_Strategy' => 'FreeFormCertificate\\Migrations\\Strategies\\MagicTokenMigrationStrategy',
        'FFC_User_Link_Migration_Strategy' => 'FreeFormCertificate\\Migrations\\Strategies\\UserLinkMigrationStrategy',

        // Repositories namespace
        'FFC_Abstract_Repository' => 'FreeFormCertificate\\Repositories\\AbstractRepository',
        'FFC_Form_Repository' => 'FreeFormCertificate\\Repositories\\FormRepository',
        'FFC_Submission_Repository' => 'FreeFormCertificate\\Repositories\\SubmissionRepository',

        // Security namespace
        'FFC_Geofence' => 'FreeFormCertificate\\Security\\Geofence',
        'FFC_Rate_Limit_Activator' => 'FreeFormCertificate\\Security\\RateLimitActivator',
        'FFC_Rate_Limiter' => 'FreeFormCertificate\\Security\\RateLimiter',

        // Settings\Tabs namespace
        'FFC_Tab_Documentation' => 'FreeFormCertificate\\Settings\\Tabs\\Documentation',
        'FFC_Tab_General' => 'FreeFormCertificate\\Settings\\Tabs\\General',
        'FFC_Tab_Geolocation' => 'FreeFormCertificate\\Settings\\Tabs\\Geolocation',
        'FFC_Tab_Migrations' => 'FreeFormCertificate\\Settings\\Tabs\\Migrations',
        'FFC_Tab_QRCode' => 'FreeFormCertificate\\Settings\\Tabs\\QRCode',
        'FFC_Tab_Rate_Limit' => 'FreeFormCertificate\\Settings\\Tabs\\RateLimit',
        'FFC_Tab_SMTP' => 'FreeFormCertificate\\Settings\\Tabs\\SMTP',
        'FFC_Tab_User_Access' => 'FreeFormCertificate\\Settings\\Tabs\\UserAccess',

        // Settings\Views namespace (abstract classes)
        'FFC_Settings_Tab' => 'FreeFormCertificate\\Settings\\Views\\AbstractSettingsTab',

        // Shortcodes namespace
        'FFC_Dashboard_Shortcode' => 'FreeFormCertificate\\Shortcodes\\DashboardShortcode',

        // Submissions namespace
        'FFC_Form_Cache' => 'FreeFormCertificate\\Submissions\\FormCache',
        'FFC_Submission_Handler' => 'FreeFormCertificate\\Submissions\\SubmissionHandler',

        // User Dashboard namespace
        'FFC_Access_Control' => 'FreeFormCertificate\\UserDashboard\\AccessControl',
        'FFC_User_Manager' => 'FreeFormCertificate\\UserDashboard\\UserManager',
    ];
}

/**
 * Check if a class has been aliased
 *
 * @param string $class_name Class name to check
 * @return bool True if class has an alias
 */
function ffc_has_class_alias(string $class_name): bool {
    $aliases = ffc_get_class_alias_map();
    return isset($aliases[$class_name]);
}

/**
 * Get the new namespaced class name for an old class
 *
 * @param string $old_class Old class name (e.g., 'FFC_Utils')
 * @return string|null New namespaced class name or null if not found
 */
function ffc_get_new_class_name(string $old_class): ?string {
    $aliases = ffc_get_class_alias_map();
    return $aliases[$old_class] ?? null;
}

/**
 * Get statistics about alias usage
 *
 * @return array Statistics array
 */
function ffc_get_alias_statistics(): array {
    $aliases = ffc_get_class_alias_map();
    $stats = [
        'total_aliases' => count($aliases),
        'registered' => 0,
        'new_classes_exist' => 0,
        'old_classes_exist' => 0,
    ];

    foreach ($aliases as $old_class => $new_class) {
        if (class_exists($new_class)) {
            $stats['new_classes_exist']++;
        }
        if (class_exists($old_class)) {
            $stats['registered']++;
            $stats['old_classes_exist']++;
        }
    }

    return $stats;
}

/**
 * Debug function to list all aliases
 *
 * @return void
 */
function ffc_debug_aliases(): void {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    $aliases = ffc_get_class_alias_map();
    $stats = ffc_get_alias_statistics();

    error_log('=== FFC Class Aliases Debug ===');
    error_log(sprintf('Total aliases: %d', $stats['total_aliases']));
    error_log(sprintf('Registered: %d', $stats['registered']));
    error_log(sprintf('New classes exist: %d', $stats['new_classes_exist']));

    foreach ($aliases as $old => $new) {
        $old_exists = class_exists($old) ? '✓' : '✗';
        $new_exists = class_exists($new) ? '✓' : '✗';
        error_log(sprintf('%s %s => %s %s', $old_exists, $old, $new, $new_exists));
    }
}
