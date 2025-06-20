<?php
if (!defined('ABSPATH')) {
    exit; // Blocare acces direct
}

// Calea către instalarea WordPress locală
$wp_tests_dir = '../../wordpress-tests-lib';

// Încarcă funcțiile de testare WordPress
require_once $wp_tests_dir . '/includes/functions.php';

// Activează pluginul înainte de a rula testele
function ppwwa_manually_load_plugin() {
    require dirname(__DIR__) . '/where-we-are.php'; // Înlocuiește cu calea către fișierul principal al pluginului tău
}
tests_add_filter('muplugins_loaded', 'ppwwa_manually_load_plugin');

// Încarcă mediul de testare WordPress
require $wp_tests_dir . '/includes/bootstrap.php';
