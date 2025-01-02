<?php
/**
 * Post Editor functionality for Personal Finance Tracker
 *
 * @package PersonalFinanceTracker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PFT_Post_Editor {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_post_meta'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_editor_scripts'));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'pft_finance_details',
            __('Finance Details', 'personal-finance-tracker'),
            array($this, 'render_meta_box'),
            'pft_monthly_finance',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('pft_save_finance_data', 'pft_finance_nonce');

        $income = get_post_meta($post->ID, '_pft_income', true);
        $expenses = get_post_meta($post->ID, '_pft_expenses', true);

        ?>
        <div class="pft-editor-wrap">
            <div class="pft-editor-header">
                <h2><?php esc_html_e('Monthly Finance Details', 'personal-finance-tracker'); ?></h2>
            </div>

            <div class="pft-summary-cards">
                <div class="pft-summary-card income">
                    <h3><?php esc_html_e('Total Income', 'personal-finance-tracker'); ?></h3>
                    <div class="amount">$<span id="pft-total-income"><?php echo esc_html(number_format($income, 2)); ?></span></div>
                </div>
                <div class="pft-summary-card expense">
                    <h3><?php esc_html_e('Total Expenses', 'personal-finance-tracker'); ?></h3>
                    <div class="amount">$<span id="pft-total-expenses"><?php echo esc_html(number_format($expenses, 2)); ?></span></div>
                </div>
                <div class="pft-summary-card balance">
                    <h3><?php esc_html_e('Balance', 'personal-finance-tracker'); ?></h3>
                    <div class="amount">$<span id="pft-balance"><?php echo esc_html(number_format($income - $expenses, 2)); ?></span></div>
                </div>
            </div>

            <div class="pft-transactions-section">
                <h3 class="pft-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <?php esc_html_e('Income', 'personal-finance-tracker'); ?>
                </h3>
                <div id="pft-income-entries" class="pft-transaction-grid">
                    <?php $this->render_transaction_rows('income', $post->ID); ?>
                </div>
                <button type="button" id="pft-add-income" class="pft-add-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?php esc_html_e('Add Income', 'personal-finance-tracker'); ?>
                </button>
            </div>

            <div class="pft-transactions-section">
                <h3 class="pft-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <?php esc_html_e('Expenses', 'personal-finance-tracker'); ?>
                </h3>
                <div id="pft-expense-entries" class="pft-transaction-grid">
                    <?php $this->render_transaction_rows('expense', $post->ID); ?>
                </div>
                <button type="button" id="pft-add-expense" class="pft-add-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?php esc_html_e('Add Expense', 'personal-finance-tracker'); ?>
                </button>
            </div>

            <div class="pft-chart-section">
                <h3 class="pft-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    <?php esc_html_e('Monthly Overview', 'personal-finance-tracker'); ?>
                </h3>
                <div class="pft-chart-container">
                    <canvas id="pftMonthlyChart"></canvas>
                </div>
            </div>
        </div>

        <select id="pft-transaction-types-template" style="display: none;">
            <?php
            $income_categories = get_terms(array('taxonomy' => 'pft_income_category', 'hide_empty' => false));
            $expense_categories = get_terms(array('taxonomy' => 'pft_expense_category', 'hide_empty' => false));

            echo '<optgroup label="' . esc_attr__('Income Categories', 'personal-finance-tracker') . '">';
            foreach ($income_categories as $category) {
                echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
            }
            echo '</optgroup>';

            echo '<optgroup label="' . esc_attr__('Expense Categories', 'personal-finance-tracker') . '">';
            foreach ($expense_categories as $category) {
                echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
            }
            echo '</optgroup>';
            ?>
        </select>
        <?php
    }

    private function render_transaction_rows($type, $post_id) {
        $transactions = get_post_meta($post_id, "_pft_{$type}", true);
        if (!empty($transactions) && is_array($transactions)) {
            foreach ($transactions as $index => $transaction) {
                ?>
                <div class="pft-transaction-row">
                    <select name="pft_<?php echo esc_attr($type); ?>[<?php echo esc_attr($index); ?>][type]" required>
                        <option value=""><?php esc_html_e('Select Category', 'personal-finance-tracker'); ?></option>
                        <?php
                        $categories = get_terms(array('taxonomy' => "pft_{$type}_category", 'hide_empty' => false));
                        foreach ($categories as $category) {
                            echo '<option value="' . esc_attr($category->term_id) . '" ' . selected($transaction['type'], $category->term_id, false) . '>' . esc_html($category->name) . '</option>';
                        }
                        ?>
                    </select>
                    <input type="text" 
                           name="pft_<?php echo esc_attr($type); ?>[<?php echo esc_attr($index); ?>][description]" 
                           value="<?php echo esc_attr($transaction['description']); ?>"
                           placeholder="<?php esc_attr_e('Description', 'personal-finance-tracker'); ?>" 
                           required>
                    <input type="number" 
                           name="pft_<?php echo esc_attr($type); ?>[<?php echo esc_attr($index); ?>][amount]" 
                           value="<?php echo esc_attr($transaction['amount']); ?>"
                           placeholder="<?php esc_attr_e('Amount', 'personal-finance-tracker'); ?>" 
                           step="0.01" 
                           required>
                    <button type="button" class="pft-remove-button">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <?php
            }
        }
    }

    public function save_post_meta($post_id) {
        if (!isset($_POST['pft_finance_nonce']) || !wp_verify_nonce($_POST['pft_finance_nonce'], 'pft_save_finance_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $income_data = isset($_POST['pft_income']) ? $_POST['pft_income'] : array();
        $expense_data = isset($_POST['pft_expense']) ? $_POST['pft_expense'] : array();

        $total_income = 0;
        $total_expenses = 0;

        foreach ($income_data as $income) {
            $total_income += floatval($income['amount']);
        }

        foreach ($expense_data as $expense) {
            $total_expenses += floatval($expense['amount']);
        }

        update_post_meta($post_id, '_pft_income', $income_data);
        update_post_meta($post_id, '_pft_expenses', $expense_data);
        update_post_meta($post_id, '_pft_total_income', $total_income);
        update_post_meta($post_id, '_pft_total_expenses', $total_expenses);
    }

    public function enqueue_editor_scripts($hook) {
        global $post;

        if ($hook == 'post-new.php' || $hook == 'post.php') {
            if ('pft_monthly_finance' === $post->post_type) {
                wp_enqueue_style('pft-admin-editor-styles', PFT_PLUGIN_URL . 'assets/css/pft-admin-editor.css', array(), PFT_VERSION);
                wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.1', true);
                wp_enqueue_script('pft-admin-editor-scripts', PFT_PLUGIN_URL . 'assets/js/pft-editor-scripts.js', array('jquery', 'chart-js'), PFT_VERSION, true);
                
                wp_localize_script('pft-admin-editor-scripts', 'pftEditor', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('pft_editor_nonce')
                ));
            }
        }
    }
}