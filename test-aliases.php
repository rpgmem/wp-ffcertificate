<?php
/**
 * Test script for Backward Compatibility Aliases
 *
 * This script tests that alias registration works correctly.
 * Run with: php test-aliases.php
 */

// Define required constants
define('ABSPATH', __DIR__ . '/');
define('FFC_PLUGIN_DIR', __DIR__ . '/');

// Load aliases functions
require_once __DIR__ . '/includes/class-ffc-aliases.php';

echo "✅ Aliases loaded successfully\n\n";

// Test alias mapping retrieval
echo "=== Testing Alias Mapping ===\n\n";

$aliases = ffc_get_class_alias_map();
echo "Total aliases registered: " . count($aliases) . "\n\n";

// Show sample mappings
echo "Sample mappings:\n";
$sample_count = 0;
foreach ($aliases as $old => $new) {
    echo "  {$old} => {$new}\n";
    $sample_count++;
    if ($sample_count >= 10) {
        echo "  ... and " . (count($aliases) - 10) . " more\n";
        break;
    }
}

// Test utility functions
echo "\n=== Testing Utility Functions ===\n\n";

$test_class = 'FFC_Utils';
$has_alias = ffc_has_class_alias($test_class);
$new_name = ffc_get_new_class_name($test_class);

echo "Testing class: {$test_class}\n";
echo "  Has alias: " . ($has_alias ? '✅ YES' : '❌ NO') . "\n";
echo "  New name: " . ($new_name ?? 'NOT FOUND') . "\n";

// Test non-existent class
echo "\nTesting non-existent class: FFC_NonExistent\n";
$has_alias = ffc_has_class_alias('FFC_NonExistent');
$new_name = ffc_get_new_class_name('FFC_NonExistent');
echo "  Has alias: " . ($has_alias ? '✅ YES' : '❌ NO (expected)') . "\n";
echo "  New name: " . ($new_name ?? '❌ NULL (expected)') . "\n";

// Test statistics
echo "\n=== Alias Statistics ===\n\n";
$stats = ffc_get_alias_statistics();
echo "Total aliases: {$stats['total_aliases']}\n";
echo "Registered (old classes exist): {$stats['registered']}\n";
echo "New classes exist: {$stats['new_classes_exist']}\n";
echo "Old classes exist: {$stats['old_classes_exist']}\n";

echo "\n✅ All alias tests completed!\n";
echo "\n📝 Note: Aliases won't be active until classes are actually loaded.\n";
echo "   This is expected behavior - aliases are registered on-demand.\n";
