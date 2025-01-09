<?php
/**
 * Frontend functionality for Personal Finance Tracker
 *
 * @package PersonalFinanceTracker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PFT_Frontend {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('single_template', array($this, 'load_single_template'));
    }

    public function enqueue_scripts() {
        if (is_singular('pft_monthly_finance')) {
            wp_enqueue_style('pft-frontend-styles', PFT_PLUGIN_URL . 'assets/css/pft-frontend-styles.css', array(), PFT_VERSION);
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.1', true);
            wp_enqueue_script('pft-frontend-scripts', PFT_PLUGIN_URL . 'assets/js/pft-frontend-scripts.js', array('jquery', 'chart-js'), PFT_VERSION, true);
            
            wp_localize_script('pft-frontend-scripts', 'pftFrontend', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pft_frontend_nonce')
            ));
        }
    }

    public function load_single_template($single_template) {
        global $post;

        if ($post->post_type === 'pft_monthly_finance') {
            $single_template = PFT_PLUGIN_DIR . 'templates/single-pft_monthly_finance.php';
        }

        return $single_template;
    }
}