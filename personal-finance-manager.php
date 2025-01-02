<?php
/**
 * Plugin Name: Personal Finance Tracker
 * Description: Advanced personal finance tracking with beautiful reports
 * Version: 1.0
 * Author: Your Name
 * Text Domain: personal-finance-tracker
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('PFT_VERSION', '1.0');
define('PFT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PFT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once PFT_PLUGIN_DIR . 'includes/class-pft-post-types.php';
require_once PFT_PLUGIN_DIR . 'includes/class-pft-shortcodes.php';
require_once PFT_PLUGIN_DIR . 'includes/class-pft-admin.php';
require_once PFT_PLUGIN_DIR . 'includes/class-pft-ajax-handler.php';
require_once PFT_PLUGIN_DIR . 'includes/class-pft-admin-dashboard.php';

// Initialize the plugin
function pft_init() {
    new PFT_Post_Types();
    new PFT_Shortcodes();
    new PFT_Admin();
    new PFT_Ajax_Handler();
    new PFT_Admin_Dashboard();

    // Load text domain for internationalization
    load_plugin_textdomain('personal-finance-tracker', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'pft_init');

// Enqueue scripts and styles
function pft_enqueue_scripts() {
    wp_enqueue_style('pft-styles', PFT_PLUGIN_URL . 'assets/css/pft-styles.css', array(), PFT_VERSION);
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.1', true);
    wp_enqueue_script('pft-scripts', PFT_PLUGIN_URL . 'assets/js/pft-scripts.js', array('jquery', 'chart-js'), PFT_VERSION, true);
    wp_localize_script('pft-scripts', 'pftAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pft_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'pft_enqueue_scripts');

// Activation hook
function pft_activate() {
    // Create necessary database tables
    global $wpdb;
    $table_name = $wpdb->prefix . 'pft_monthly_finances';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        income decimal(10,2) NOT NULL,
        expenses decimal(10,2) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Set default options
    add_option('pft_currency_symbol', '$');
    add_option('pft_date_format', 'Y-m-d');
}
register_activation_hook(__FILE__, 'pft_activate');

// Deactivation hook
function pft_deactivate() {
    // Cleanup tasks if needed
}
register_deactivation_hook(__FILE__, 'pft_deactivate');