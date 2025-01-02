<?php
class PFT_Post_Editor {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_finance_meta_boxes'));
        add_action('save_post', array($this, 'save_finance_meta'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_editor_scripts'));
        add_action('wp_ajax_pft_get_filtered_data', array($this, 'get_filtered_data'));
    }

    public function enqueue_editor_scripts($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        global $post;
        if ('pft_monthly_finance' !== $post->post_type) {
            return;
        }

        wp_enqueue_style('pft-admin-editor', PFT_PLUGIN_URL . 'assets/css/pft-admin-editor.css', array(), PFT_VERSION);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.1', true);
        wp_enqueue_script('pft-editor-scripts', PFT_PLUGIN_URL . 'assets/js/pft-editor-scripts.js', array('jquery', 'chart-js'), PFT_VERSION, true);
        
        wp_localize_script('pft-editor-scripts', 'pftEditor', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pft_editor_nonce'),
            'post_id' => $post->ID
        ));
    }

    public function add_finance_meta_boxes() {
        add_meta_box(
            'pft_finance_details',
            'Monthly Finance Details',
            array($this, 'render_finance_meta_box'),
            'pft_monthly_finance',
            'normal',
            'high'
        );
    }

    public function render_finance_meta_box($post) {
        wp_nonce_field('pft_finance_meta', 'pft_finance_meta_nonce');
        
        $income_data = get_post_meta($post->ID, '_pft_income_data', true) ?: array();
        $expense_data = get_post_meta($post->ID, '_pft_expense_data', true) ?: array();
        
        $total_income = array_sum(array_column($income_data, 'amount'));
        $total_expenses = array_sum(array_column($expense_data, 'amount'));
        $balance = $total_income - $total_expenses;
        ?>
        <div class="pft-editor-wrap">
            <div class="pft-editor-header">
                <h2><?php echo get_the_date('F Y', $post->ID); ?> Financial Report</h2>
            </div>

            <div class="pft-summary-cards">
                <div class="pft-summary-card income">
                    <h3>Total Income</h3>
                    <div class="amount">$<?php echo number_format($total_income, 2); ?></div>
                    <div class="trend">
                        <?php $this->render_trend_indicator($total_income, 'income'); ?>
                    </div>
                </div>

                <div class="pft-summary-card expense">
                    <h3>Total Expenses</h3>
                    <div class="amount">$<?php echo number_format($total_expenses, 2); ?></div>
                    <div class="trend">
                        <?php $this->render_trend_indicator($total_expenses, 'expense'); ?>
                    </div>
                </div>

                <div class="pft-summary-card balance">
                    <h3>Balance</h3>
                    <div class="amount">$<?php echo number_format($balance, 2); ?></div>
                </div>
            </div>

            <div class="pft-chart-section">
                <h3 class="pft-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 21H4.6c-.6 0-1.1-.5-1.1-1.1V3"/>
                        <path d="M19 7l-6 6-3-3-4 4"/>
                    </svg>
                    Monthly Overview
                </h3>
                <div class="pft-chart-container">
                    <canvas id="pftMonthlyChart"></canvas>
                </div>
            </div>

            <div class="pft-filter-section">
                <h3 class="pft-section-title">Filter Report</h3>
                <form id="pft-filter-form" class="pft-filter-form">
                    <div>
                        <label for="pft-filter-category">Category</label>
                        <select id="pft-filter-category" name="category">
                            <option value="all">All Categories</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div>
                        <label for="pft-filter-month">Month</label>
                        <input type="month" id="pft-filter-month" name="month" value="<?php echo date('Y-m'); ?>">
                    </div>
                    <button type="submit" class="pft-filter-button">Apply Filter</button>
                </form>
            </div>

            <div class="pft-transactions-section">
                <h3 class="pft-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    Income Entries
                </h3>
                <div class="pft-transaction-form">
                    <div id="pft-income-entries" class="pft-transaction-grid">
                        <?php
                        if (!empty($income_data)) {
                            foreach ($income_data as $index => $entry) {
                                $this->render_transaction_row('income', $index, $entry);
                            }
                        }
                        ?>
                    </div>
                    <button type="button" class="pft-add-button" id="pft-add-income">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Income Entry
                    </button>
                </div>
            </div>

            <div class="pft-transactions-section">
                <h3 class="pft-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    Expense Entries
                </h3>
                <div class="pft-transaction-form">
                    <div id="pft-expense-entries" class="pft-transaction-grid">
                        <?php
                        if (!empty($expense_data)) {
                            foreach ($expense_data as $index => $entry) {
                                $this->render_transaction_row('expense', $index, $entry);
                            }
                        }
                        ?>
                    </div>
                    <button type="button" class="pft-add-button" id="pft-add-expense">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Expense Entry
                    </button>
                </div>
            </div>
        </div>

        <!-- Template for transaction types -->
        <script type="text/template" id="pft-transaction-types-template">
            <?php
            $income_types = get_terms(array(
                'taxonomy' => 'pft_income_category',
                'hide_empty' => false
            ));
            $expense_types = get_terms(array(
                'taxonomy' => 'pft_expense_category',
                'hide_empty' => false
            ));
            ?>
            <optgroup label="Income Categories">
                <?php foreach ($income_types as $type): ?>
                    <option value="income_<?php echo esc_attr($type->term_id); ?>"><?php echo esc_html($type->name); ?></option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="Expense Categories">
                <?php foreach ($expense_types as $type): ?>
                    <option value="expense_<?php echo esc_attr($type->term_id); ?>"><?php echo esc_html($type->name); ?></option>
                <?php endforeach; ?>
            </optgroup>
        </script>
        <?php
    }

    private function render_transaction_row($type, $index, $entry = null) {
        $entry = $entry ?: array('type' => '', 'description' => '', 'amount' => '');
        $category_taxonomy = $type === 'income' ? 'pft_income_category' : 'pft_expense_category';
        ?>
        <div class="pft-transaction-row">
            <select name="pft_<?php echo $type; ?>[<?php echo $index; ?>][type]" required>
                <option value="">Select Category</option>
                <?php
                $categories = get_terms(array(
                    'taxonomy' => $category_taxonomy,
                    'hide_empty' => false
                ));
                if (!is_wp_error($categories) && !empty($categories)) {
                    foreach ($categories as $category) {
                        $selected = selected($entry['type'], $category->term_id, false);
                        echo '<option value="' . esc_attr($category->term_id) . '" ' . $selected . '>' . 
                             esc_html($category->name) . '</option>';
                    }
                }
                ?>
            </select>
            <input type="text" 
                   name="pft_<?php echo $type; ?>[<?php echo $index; ?>][description]" 
                   placeholder="Description" 
                   value="<?php echo esc_attr($entry['description']); ?>" 
                   required>
            <input type="number" 
                   name="pft_<?php echo $type; ?>[<?php echo $index; ?>][amount]" 
                   placeholder="Amount" 
                   step="0.01" 
                   value="<?php echo esc_attr($entry['amount']); ?>" 
                   required>
            <button type="button" class="pft-remove-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <?php
    }

    private function render_trend_indicator($current_amount, $type) {
        $prev_month = date('Y-m', strtotime('-1 month'));
        $prev_amount = $this->get_previous_month_amount($prev_month, $type);
        
        if ($prev_amount > 0) {
            $percentage = (($current_amount - $prev_amount) / $prev_amount) * 100;
            $trend_class = $percentage >= 0 ? 'trend-up' : 'trend-down';
            $trend_icon = $percentage >= 0 ? '↑' : '↓';
            echo '<span class="' . $trend_class . '">' . $trend_icon . ' ' . 
                 abs(round($percentage, 1)) . '% vs last month</span>';
        }
    }

    private function get_previous_month_amount($month, $type) {
        $args = array(
            'post_type' => 'pft_monthly_finance',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_pft_month',
                    'value' => $month
                )
            )
        );
        
        $posts = get_posts($args);
        if (empty($posts)) {
            return 0;
        }
        
        $post = $posts[0];
        $data = get_post_meta($post->ID, "_pft_{$type}_data", true);
        
        return array_sum(array_column($data, 'amount'));
    }

    public function save_finance_meta($post_id) {
        if (!isset($_POST['pft_finance_meta_nonce']) || 
            !wp_verify_nonce($_POST['pft_finance_meta_nonce'], 'pft_finance_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $income_data = isset($_POST['pft_income']) ? $this->sanitize_entries($_POST['pft_income']) : array();
        $expense_data = isset($_POST['pft_expense']) ? $this->sanitize_entries($_POST['pft_expense']) : array();

        update_post_meta($post_id, '_pft_income_data', $income_data);
        update_post_meta($post_id, '_pft_expense_data', $expense_data);
        update_post_meta($post_id, '_pft_month', date('Y-m'));
    }

    private function sanitize_entries($entries) {
        $sanitized = array();
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                if (!empty($entry['type']) && !empty($entry['amount'])) {
                    $sanitized[] = array(
                        'type' => absint($entry['type']),
                        'description' => sanitize_text_field($entry['description']),
                        'amount' => floatval($entry['amount'])
                    );
                }
            }
        }
        return $sanitized;
    }

    public function get_filtered_data() {
        check_ajax_referer('pft_editor_nonce', 'nonce');

        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'all';
        $month = isset($_POST['month']) ? sanitize_text_field($_POST['month']) : date('Y-m');

        $args = array(
            'post_type' => 'pft_monthly_finance',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_pft_month',
                    'value' => $month
                )
            )
        );

        $posts = get_posts($args);

        if (empty($posts)) {
            wp_send_json_error('No data found for the selected month.');
            return;
        }

        $post = $posts[0];
        $income_data = get_post_meta($post->ID, '_pft_income_data', true) ?: array();
        $expense_data = get_post_meta($post->ID, '_pft_expense_data', true) ?: array();

        $filtered_data = array();

        if ($category === 'all' || $category === 'income') {
            foreach ($income_data as $entry) {
                $term = get_term($entry['type'], 'pft_income_category');
                $filtered_data[] = array(
                    'category' => $term->name,
                    'description' => $entry['description'],
                    'amount' => $entry['amount'],
                    'type' => 'Income'
                );
            }
        }

        if ($category === 'all' || $category === 'expense') {
            foreach ($expense_data as $entry) {
                $term = get_term($entry['type'], 'pft_expense_category');
                $filtered_data[] = array(
                    'category' => $term->name,
                    'description' => $entry['description'],
                    'amount' => $entry['amount'],
                    'type' => 'Expense'
                );
            }
        }

        wp_send_json_success($filtered_data);
    }
}
