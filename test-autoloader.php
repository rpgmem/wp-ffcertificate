<?php
/**
 * Test script for PSR-4 Autoloader
 *
 * This script tests the autoloader functionality without WordPress.
 * Run with: php test-autoloader.php
 */

// Define required constants
define('ABSPATH', __DIR__ . '/');
define('FFC_PLUGIN_DIR', __DIR__ . '/');

// Load autoloader
require_once __DIR__ . '/includes/class-ffc-autoloader.php';

// Create and register autoloader
$autoloader = new FFC_Autoloader(__DIR__ . '/includes');
$autoloader->register();

echo "✅ Autoloader registered successfully\n\n";

// Test namespace mappings
echo "=== Testing Namespace Mappings ===\n\n";

$test_classes = [
    'FreeFormCertificate\\Core\\Utils',
    'FreeFormCertificate\\Admin\\Admin',
    'FreeFormCertificate\\Migrations\\MigrationManager',
    'FreeFormCertificate\\Repositories\\AbstractRepository',
];

foreach ($test_classes as $class) {
    $debug = $autoloader->debug_class_mapping($class);
    echo "Class: {$debug['class']}\n";
    echo "  Relative: {$debug['relative_class']}\n";
    echo "  File: " . ($debug['file'] ?? 'NOT FOUND') . "\n";
    echo "  Exists: " . ($debug['exists'] ? '✅ YES' : '❌ NO') . "\n";
    echo "\n";
}

// Test file naming conventions
echo "=== Testing File Naming Conventions ===\n\n";

$test_cases = [
    ['class' => 'Utils', 'expected' => 'class-ffc-utils.php'],
    ['class' => 'ActivityLog', 'expected' => 'class-ffc-activity-log.php'],
    ['class' => 'FormEditorSaveHandler', 'expected' => 'class-ffc-form-editor-save-handler.php'],
];

foreach ($test_cases as $test) {
    $class = $test['class'];
    $expected = $test['expected'];

    // Use reflection to test private method
    $reflection = new ReflectionClass($autoloader);
    $method = $reflection->getMethod('get_possible_filenames');
    $method->setAccessible(true);

    $filenames = $method->invoke($autoloader, $class);
    $has_expected = in_array($expected, $filenames);

    echo "Class: {$class}\n";
    echo "  Expected: {$expected}\n";
    echo "  Result: " . ($has_expected ? '✅ FOUND' : '❌ NOT FOUND') . "\n";
    echo "  Generated: " . implode(', ', $filenames) . "\n";
    echo "\n";
}

echo "=== Autoloader Statistics ===\n\n";
$namespaces = $autoloader->get_namespaces();
echo "Registered namespaces: " . count($namespaces) . "\n";
foreach ($namespaces as $ns) {
    echo "  - FreeFormCertificate\\{$ns}\n";
}

echo "\n✅ All autoloader tests completed!\n";
