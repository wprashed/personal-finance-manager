<?php
class PFT_Ajax_Handlers {
    public function __construct() {
        add_action('wp_ajax_pft_get_monthly_data', array($this, 'get_monthly_data'));
        add_action('wp_ajax_pft_get_monthly_details', array($this, 'get_monthly_details'));

        // For non-logged in users
        add_action('wp_ajax_nopriv_pft_get_monthly_data', array($this, 'get_monthly_data'));
        add_action('wp_ajax_nopriv_pft_get_monthly_details', array($this, 'get_monthly_details'));
    }

    public function get_monthly_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'pft_ajax_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $args = array(
            'post_type' => 'pft_monthly_finance',
            'posts_per_page' => 6,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $posts = get_posts($args);
        $data = array(
            'labels' => array(),
            'income' => array(),
            'expenses' => array()
        );

        foreach ($posts as $post) {
            $data['labels'][] = get_the_date('M Y', $post->ID);
            
            $income_data = get_post_meta($post->ID, '_pft_income_data', true);
            $expense_data = get_post_meta($post->ID, '_pft_expense_data', true);

            $total_income = 0;
            $total_expenses = 0;

            if (is_array($income_data)) {
                foreach ($income_data as $entry) {
                    $total_income += floatval($entry['amount']);
                }
            }

            if (is_array($expense_data)) {
                foreach ($expense_data as $entry) {
                    $total_expenses += floatval($entry['amount']);
                }
            }

            $data['income'][] = $total_income;
            $data['expenses'][] = $total_expenses;
        }

        $data['labels'] = array_reverse($data['labels']);
        $data['income'] = array_reverse($data['income']);
        $data['expenses'] = array_reverse($data['expenses']);

        wp_send_json_success($data);
    }

    public function get_monthly_details() {
        if (!wp_verify_nonce($_POST['nonce'], 'pft_ajax_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        $args = array(
            'post_type' => 'pft_monthly_finance',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $posts = get_posts($args);

        if (empty($posts)) {
            wp_send_json_error(array('message' => 'No monthly data found'));
        }

        $post = $posts[0];
        $income_data = get_post_meta($post->ID, '_pft_income_data', true);
        $expense_data = get_post_meta($post->ID, '_pft_expense_data', true);

        ob_start();
        ?>
        <h4><?php echo get_the_date('F Y', $post->ID); ?></h4>
        <div class="pft-monthly-income">
            <h5>Income</h5>
            <ul>
                <?php
                if (is_array($income_data)) {
                    foreach ($income_data as $entry) {
                        $type = get_term($entry['type'], 'pft_transaction_type');
                        echo '<li>' . esc_html($type->name) . ': ' . esc_html($entry['description']) . ' - $' . number_format($entry['amount'], 2) . '</li>';
                    }
                } else {
                    echo '<li>No income entries for this month.</li>';
                }
                ?>
            </ul>
        </div>
        <div class="pft-monthly-expenses">
            <h5>Expenses</h5>
            <ul>
                <?php
                if (is_array($expense_data)) {
                    foreach ($expense_data as $entry) {
                        $type = get_term($entry['type'], 'pft_transaction_type');
                        echo '<li>' . esc_html($type->name) . ': ' . esc_html($entry['description']) . ' - $' . number_format($entry['amount'], 2) . '</li>';
                    }
                } else {
                    echo '<li>No expense entries for this month.</li>';
                }
                ?>
            </ul>
        </div>
        <?php
        $html = ob_get_clean();
        wp_send_json_success($html);
    }
}

