<?php
/**
 * AJAX Handler for Personal Finance Tracker
 *
 * @package PersonalFinanceTracker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PFT_Ajax_Handler {
    public function __construct() {
        add_action('wp_ajax_pft_get_categories', array($this, 'get_categories'));
        add_action('wp_ajax_pft_add_category', array($this, 'add_category'));
        add_action('wp_ajax_pft_updatecategory', array($this, 'update_category'));
        add_action('wp_ajax_pft_delete_category', array($this, 'delete_category'));
    }

    public function get_categories() {
        check_ajax_referer('pft_admin_nonce', 'nonce');

        $categories = get_terms(array(
            'taxonomy' => array('pft_income_category', 'pft_expense_category'),
            'hide_empty' => false,
        ));

        wp_send_json_success($categories);
    }

    public function add_category() {
        check_ajax_referer('pft_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $name = sanitize_text_field($_POST['name']);
        $type = sanitize_text_field($_POST['type']);

        if (empty($name) || empty($type)) {
            wp_send_json_error('Name and type are required');
        }

        $taxonomy = $type === 'income' ? 'pft_income_category' : 'pft_expense_category';

        $result = wp_insert_term($name, $taxonomy);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array('id' => $result['term_id'], 'name' => $name));
        }
    }

    public function update_category() {
        check_ajax_referer('pft_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $id = intval($_POST['id']);
        $name = sanitize_text_field($_POST['name']);
        $type = sanitize_text_field($_POST['type']);

        if (empty($id) || empty($name) || empty($type)) {
            wp_send_json_error('ID, name, and type are required');
        }

        $taxonomy = $type === 'income' ? 'pft_income_category' : 'pft_expense_category';

        $result = wp_update_term($id, $taxonomy, array('name' => $name));

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array('id' => $id, 'name' => $name));
        }
    }

    public function delete_category() {
        check_ajax_referer('pft_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $id = intval($_POST['id']);
        $type = sanitize_text_field($_POST['type']);

        if (empty($id) || empty($type)) {
            wp_send_json_error('ID and type are required');
        }

        $taxonomy = $type === 'income' ? 'pft_income_category' : 'pft_expense_category';

        $result = wp_delete_term($id, $taxonomy);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array('id' => $id));
        }
    }
}