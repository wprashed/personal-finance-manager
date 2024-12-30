<?php
/**
 * Plugin Name: Personal Finance Manager
 * Description: A WordPress plugin for managing personal finances, tracking income and expenses, and generating reports.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: personal-finance-manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('PFM_VERSION', '1.0');
define('PFM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PFM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once PFM_PLUGIN_DIR . 'includes/class-pfm-post-types.php';
require_once PFM_PLUGIN_DIR . 'includes/class-pfm-admin.php';
require_once PFM_PLUGIN_DIR . 'includes/class-pfm-reports.php';
require_once PFM_PLUGIN_DIR . 'includes/class-pfm-api.php';

// Initialize the plugin
function pfm_init() {
    new PFM_Post_Types();
    new PFM_Admin();
    new PFM_Reports();
    new PFM_API();
}
add_action('plugins_loaded', 'pfm_init');

// Activation hook
function pfm_activate() {
    // Create default categories
    $default_categories = array(
        'income' => array('Salary', 'Freelance', 'Investments'),
        'expense' => array('Rent', 'Groceries', 'Utilities', 'Entertainment')
    );

    foreach ($default_categories as $type => $categories) {
        foreach ($categories as $category) {
            wp_insert_term($category, 'pfm_category', array('slug' => sanitize_title($category)));
        }
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'pfm_activate');

// Enqueue scripts and styles
function pfm_enqueue_scripts() {
    wp_enqueue_style('pfm-styles', PFM_PLUGIN_URL . 'assets/css/pfm-styles.css', array(), PFM_VERSION);
    wp_enqueue_script('pfm-scripts', PFM_PLUGIN_URL . 'assets/js/pfm-scripts.js', array('jquery', 'wp-api'), PFM_VERSION, true);
    wp_localize_script('pfm-scripts', 'pfmData', array(
        'root' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest')
    ));
}
add_action('wp_enqueue_scripts', 'pfm_enqueue_scripts');
add_action('admin_enqueue_scripts', 'pfm_enqueue_scripts');