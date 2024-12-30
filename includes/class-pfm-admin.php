<?php
class PFM_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_box_data'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Personal Finance Manager',
            'Finance Manager',
            'manage_options',
            'personal-finance-manager',
            array($this, 'display_plugin_admin_page'),
            'dashicons-chart-area',
            6
        );

        add_submenu_page(
            'personal-finance-manager',
            'Reports',
            'Reports',
            'manage_options',
            'pfm-reports',
            array($this, 'display_reports_page')
        );

        add_submenu_page(
            'personal-finance-manager',
            'Settings',
            'Settings',
            'manage_options',
            'pfm-settings',
            array($this, 'display_settings_page')
        );
    }

    public function display_plugin_admin_page() {
        include_once PFM_PLUGIN_DIR . 'templates/admin-page.php';
    }

    public function display_reports_page() {
        include_once PFM_PLUGIN_DIR . 'templates/reports-page.php';
    }

    public function display_settings_page() {
        include_once PFM_PLUGIN_DIR . 'templates/settings-page.php';
    }

    public function add_meta_boxes() {
        add_meta_box(
            'pfm_entry_details',
            'Finance Entry Details',
            array($this, 'render_meta_box'),
            'pfm_entry',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        $amount = get_post_meta($post->ID, '_pfm_amount', true);
        $type = get_post_meta($post->ID, '_pfm_type', true);
        $date = get_post_meta($post->ID, '_pfm_date', true);

        wp_nonce_field('pfm_save_meta_box_data', 'pfm_meta_box_nonce');

        echo '<p>';
        echo '<label for="pfm_amount">Amount:</label> ';
        echo '<input type="number" id="pfm_amount" name="pfm_amount" value="' . esc_attr($amount) . '" step="0.01" required>';
        echo '</p>';

        echo '<p>';
        echo '<label for="pfm_type">Type:</label> ';
        echo '<select id="pfm_type" name="pfm_type" required>';
        echo '<option value="income"' . selected($type, 'income', false) . '>Income</option>';
        echo '<option value="expense"' . selected($type, 'expense', false) . '>Expense</option>';
        echo '</select>';
        echo '</p>';

        echo '<p>';
        echo '<label for="pfm_date">Date:</label> ';
        echo '<input type="date" id="pfm_date" name="pfm_date" value="' . esc_attr($date) . '" required>';
        echo '</p>';
    }

    public function save_meta_box_data($post_id) {
        if (!isset($_POST['pfm_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['pfm_meta_box_nonce'], 'pfm_save_meta_box_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $amount = isset($_POST['pfm_amount']) ? sanitize_text_field($_POST['pfm_amount']) : '';
        $type = isset($_POST['pfm_type']) ? sanitize_text_field($_POST['pfm_type']) : '';
        $date = isset($_POST['pfm_date']) ? sanitize_text_field($_POST['pfm_date']) : '';

        update_post_meta($post_id, '_pfm_amount', $amount);
        update_post_meta($post_id, '_pfm_type', $type);
        update_post_meta($post_id, '_pfm_date', $date);
    }
}

