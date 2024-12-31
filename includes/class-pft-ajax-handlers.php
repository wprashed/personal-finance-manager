<?php
class PFT_Ajax_Handlers {
    public function __construct() {
        add_action('wp_ajax_pft_add_transaction', array($this, 'add_transaction'));
        add_action('wp_ajax_pft_get_monthly_data', array($this, 'get_monthly_data'));
        add_action('wp_ajax_pft_get_recent_transactions', array($this, 'get_recent_transactions'));
        add_action('wp_ajax_pft_get_summary', array($this, 'get_summary'));

        // For non-logged in users
        add_action('wp_ajax_nopriv_pft_get_monthly_data', array($this, 'get_monthly_data'));
        add_action('wp_ajax_nopriv_pft_get_recent_transactions', array($this, 'get_recent_transactions'));
        add_action('wp_ajax_nopriv_pft_get_summary', array($this, 'get_summary'));
    }

    public function add_transaction() {
        if (!wp_verify_nonce($_POST['nonce'], 'pft_ajax_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $post_id = wp_insert_post(array(
            'post_title'  => sanitize_text_field($_POST['description']),
            'post_type'   => 'pft_transaction',
            'post_status' => 'publish',
        ));

        if ($post_id) {
            update_post_meta($post_id, 'amount', floatval($_POST['amount']));
            update_post_meta($post_id, 'type', sanitize_text_field($_POST['type']));
            update_post_meta($post_id, 'date', sanitize_text_field($_POST['date']));
            wp_send_json_success(array('message' => 'Transaction added successfully'));
        } else {
            wp_send_json_error(array('message' => 'Error adding transaction'));
        }
    }

    public function get_monthly_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'pft_ajax_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $args = array(
            'post_type' => 'pft_transaction',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'date',
                    'value' => array(date('Y-m-d', strtotime('-6 months')), date('Y-m-d')),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        );

        $transactions = get_posts($args);

        $data = array(
            'labels' => array(),
            'income' => array(),
            'expenses' => array()
        );

        for ($i = 5; $i >= 0; $i--) {
            $month = date('M', strtotime("-$i months"));
            $data['labels'][] = $month;
            $data['income'][$month] = 0;
            $data['expenses'][$month] = 0;
        }

        foreach ($transactions as $transaction) {
            $amount = get_post_meta($transaction->ID, 'amount', true);
            $type = get_post_meta($transaction->ID, 'type', true);
            $date = get_post_meta($transaction->ID, 'date', true);
            $month = date('M', strtotime($date));

            if ($type === 'income') {
                $data['income'][$month] += $amount;
            } else {
                $data['expenses'][$month] += $amount;
            }
        }

        $data['income'] = array_values($data['income']);
        $data['expenses'] = array_values($data['expenses']);

        wp_send_json_success($data);
    }

    public function get_recent_transactions() {
        if (!wp_verify_nonce($_POST['nonce'], 'pft_ajax_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $args = array(
            'post_type' => 'pft_transaction',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $transactions = get_posts($args);

        $currency_symbol = get_option('pft_currency_symbol', '$');

        ob_start();
        foreach ($transactions as $transaction) {
            $amount = get_post_meta($transaction->ID, 'amount', true);
            $type = get_post_meta($transaction->ID, 'type', true);
            $date = get_post_meta($transaction->ID, 'date', true);
            $formatted_amount = $currency_symbol . number_format($amount, 2);
            $class = $type === 'income' ? 'pft-income' : 'pft-expense';
            echo "<li class='$class'>{$transaction->post_title} - {$formatted_amount} - {$date}</li>";
        }
        $html = ob_get_clean();

        wp_send_json_success($html);
    }

    public function get_summary() {
        if (!wp_verify_nonce($_POST['nonce'], 'pft_ajax_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $args = array(
            'post_type' => 'pft_transaction',
            'posts_per_page' => -1,
        );
        $transactions = get_posts($args);

        $total_income = 0;
        $total_expenses = 0;

        foreach ($transactions as $transaction) {
            $amount = get_post_meta($transaction->ID, 'amount', true);
            $type = get_post_meta($transaction->ID, 'type', true);

            if ($type === 'income') {
                $total_income += $amount;
            } else {
                $total_expenses += $amount;
            }
        }

        $balance = $total_income - $total_expenses;
        $currency_symbol = get_option('pft_currency_symbol', '$');

        $data = array(
            'income' => $currency_symbol . number_format($total_income, 2),
            'expenses' => $currency_symbol . number_format($total_expenses, 2),
            'balance' => $currency_symbol . number_format($balance, 2)
        );

        wp_send_json_success($data);
    }
}