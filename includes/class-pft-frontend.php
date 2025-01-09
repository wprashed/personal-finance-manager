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
        add_filter('archive_template', array($this, 'load_archive_template'));
        add_action('wp_ajax_pft_filter_archive', array($this, 'filter_archive'));
        add_action('wp_ajax_nopriv_pft_filter_archive', array($this, 'filter_archive'));
    }

    public function enqueue_scripts() {
        if (is_singular('pft_monthly_finance') || is_post_type_archive('pft_monthly_finance')) {
            wp_enqueue_style('pft-frontend-styles', PFT_PLUGIN_URL . 'assets/css/pft-frontend-styles.css', array(), PFT_VERSION);
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.1', true);
            wp_enqueue_script('pft-frontend-scripts', PFT_PLUGIN_URL . 'assets/js/pft-frontend-scripts.js', array('jquery', 'chart-js'), PFT_VERSION, true);
            
            wp_localize_script('pft-frontend-scripts', 'pftAjax', array(
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

    public function load_archive_template($archive_template) {
        if (is_post_type_archive('pft_monthly_finance')) {
            $archive_template = PFT_PLUGIN_DIR . 'templates/archive-pft_monthly_finance.php';
        }

        return $archive_template;
    }

    public function filter_archive() {
        check_ajax_referer('pft_frontend_nonce', 'nonce');

        $year = isset($_POST['year']) ? intval($_POST['year']) : '';
        $month = isset($_POST['month']) ? intval($_POST['month']) : '';

        $args = array(
            'post_type' => 'pft_monthly_finance',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if (!empty($year)) {
            $args['date_query'][0]['year'] = $year;
        }

        if (!empty($month)) {
            $args['date_query'][0]['month'] = $month;
        }

        $query = new WP_Query($args);
        $output = '';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $income_data = get_post_meta(get_the_ID(), '_pft_income_data', true) ?: array();
                $expense_data = get_post_meta(get_the_ID(), '_pft_expense_data', true) ?: array();
                $total_income = array_sum(array_column($income_data, 'amount'));
                $total_expenses = array_sum(array_column($expense_data, 'amount'));
                $balance = $total_income - $total_expenses;

                $output .= '<div class="pft-archive-item">';
                $output .= '<h2><a href="' . get_permalink() . '">' . get_the_date('F Y') . '</a></h2>';
                $output .= '<div class="pft-archive-summary">';
                $output .= '<span class="pft-income">Income: $' . number_format($total_income, 2) . '</span>';
                $output .= '<span class="pft-expenses">Expenses: $' . number_format($total_expenses, 2) . '</span>';
                $output .= '<span class="pft-balance">Balance: $' . number_format($balance, 2) . '</span>';
                $output .= '</div>';
                $output .= '</div>';
            }
            wp_reset_postdata();
        } else {
            $output = '<p>' . esc_html__('No finance reports found.', 'personal-finance-tracker') . '</p>';
        }

        wp_send_json_success($output);
    }
}