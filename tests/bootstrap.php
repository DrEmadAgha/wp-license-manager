<?php
/**
 * Bootstrap file for PHPUnit tests
 */

// Define test constants
define('WP_TESTS_DIR', './tmp/wordpress-tests-lib');
define('WP_CORE_DIR', './tmp/wordpress/');
define('WPLM_PLUGIN_DIR', './wp-license-manager/');
define('WPLM_PLUGIN_FILE', WPLM_PLUGIN_DIR . 'wp-license-manager.php');

// Load WordPress test framework
require_once WP_TESTS_DIR . '/includes/functions.php';

function _manually_load_plugin() {
    require WPLM_PLUGIN_FILE;
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

require WP_TESTS_DIR . '/includes/bootstrap.php';