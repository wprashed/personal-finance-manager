<?php
class PFT_Post_Types {
    public function __construct() {
        add_action('init', array($this, 'register_monthly_finance_post_type'));
        add_action('init', array($this, 'register_transaction_type_taxonomy'));
        add_action('add_meta_boxes', array($this, 'add_monthly_finance_meta_boxes'));
        add_action('save_post', array($this, 'save_monthly_finance_meta'));
    }

    public function register_monthly_finance_post_type() {
        $args = array(
            'public' => true,
            'label'  => 'Monthly Finances',
            'supports' => array('title'),
            'menu_icon' => 'dashicons-calendar-alt',
            'show_in_rest' => true,
        );
        register_post_type('pft_monthly_finance', $args);
    }

    public function register_transaction_type_taxonomy() {
        $args = array(
            'hierarchical' => true,
            'label' => 'Transaction Types',
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'transaction-type'),
        );
        register_taxonomy('pft_transaction_type', 'pft_monthly_finance', $args);
    }

    public function add_monthly_finance_meta_boxes() {
        add_meta_box(
            'pft_monthly_finance_meta',
            'Monthly Finance Details',
            array($this, 'render_monthly_finance_meta_box'),
            'pft_monthly_finance',
            'normal',
            'default'
        );
    }

    public function render_monthly_finance_meta_box($post) {
        wp_nonce_field('pft_monthly_finance_meta', 'pft_monthly_finance_meta_nonce');
        $income_data = get_post_meta($post->ID, '_pft_income_data', true);
        $expense_data = get_post_meta($post->ID, '_pft_expense_data', true);
        ?>
        <div id="pft-monthly-finance-form">
            <h3>Income</h3>
            <div id="pft-income-entries">
                <?php
                if (is_array($income_data)) {
                    foreach ($income_data as $index => $entry) {
                        $this->render_transaction_entry('income', $index, $entry);
                    }
                }
                ?>
            </div>
            <button type="button" class="button" id="pft-add-income">Add Income</button>

            <h3>Expenses</h3>
            <div id="pft-expense-entries">
                <?php
                if (is_array($expense_data)) {
                    foreach ($expense_data as $index => $entry) {
                        $this->render_transaction_entry('expense', $index, $entry);
                    }
                }
                ?>
            </div>
            <button type="button" class="button" id="pft-add-expense">Add Expense</button>
        </div>
        <script>
            jQuery(document).ready(function($) {
                let incomeCount = <?php echo is_array($income_data) ? count($income_data) : 0; ?>;
                let expenseCount = <?php echo is_array($expense_data) ? count($expense_data) : 0; ?>;

                $('#pft-add-income').on('click', function() {
                    addEntry('income', incomeCount++);
                });

                $('#pft-add-expense').on('click', function() {
                    addEntry('expense', expenseCount++);
                });

                function addEntry(type, index) {
                    const entryHtml = `
                        <div class="pft-entry">
                            <select name="pft_${type}[${index}][type]">
                                <option value="">Select Type</option>
                                <?php
                                $types = get_terms(array('taxonomy' => 'pft_transaction_type', 'hide_empty' => false));
                                foreach ($types as $type) {
                                    echo '<option value="' . esc_attr($type->term_id) . '">' . esc_html($type->name) . '</option>';
                                }
                                ?>
                            </select>
                            <input type="text" name="pft_${type}[${index}][description]" placeholder="Description">
                            <input type="number" name="pft_${type}[${index}][amount]" placeholder="Amount" step="0.01">
                            <button type="button" class="button pft-remove-entry">Remove</button>
                        </div>
                    `;
                    $(`#pft-${type}-entries`).append(entryHtml);
                }

                $(document).on('click', '.pft-remove-entry', function() {
                    $(this).closest('.pft-entry').remove();
                });
            });
        </script>
        <?php
    }

    private function render_transaction_entry($type, $index, $entry) {
        ?>
        <div class="pft-entry">
            <select name="pft_<?php echo $type; ?>[<?php echo $index; ?>][type]">
                <option value="">Select Type</option>
                <?php
                $types = get_terms(array('taxonomy' => 'pft_transaction_type', 'hide_empty' => false));
                foreach ($types as $term) {
                    echo '<option value="' . esc_attr($term->term_id) . '" ' . selected($entry['type'], $term->term_id, false) . '>' . esc_html($term->name) . '</option>';
                }
                ?>
            </select>
            <input type="text" name="pft_<?php echo $type; ?>[<?php echo $index; ?>][description]" placeholder="Description" value="<?php echo esc_attr($entry['description']); ?>">
            <input type="number" name="pft_<?php echo $type; ?>[<?php echo $index; ?>][amount]" placeholder="Amount" step="0.01" value="<?php echo esc_attr($entry['amount']); ?>">
            <button type="button" class="button pft-remove-entry">Remove</button>
        </div>
        <?php
    }

    public function save_monthly_finance_meta($post_id) {
        if (!isset($_POST['pft_monthly_finance_meta_nonce']) || !wp_verify_nonce($_POST['pft_monthly_finance_meta_nonce'], 'pft_monthly_finance_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $income_data = isset($_POST['pft_income']) ? $this->sanitize_transaction_data($_POST['pft_income']) : array();
        $expense_data = isset($_POST['pft_expense']) ? $this->sanitize_transaction_data($_POST['pft_expense']) : array();

        update_post_meta($post_id, '_pft_income_data', $income_data);
        update_post_meta($post_id, '_pft_expense_data', $expense_data);
    }

    private function sanitize_transaction_data($data) {
        $sanitized_data = array();
        foreach ($data as $entry) {
            if (!empty($entry['type']) && !empty($entry['amount'])) {
                $sanitized_data[] = array(
                    'type' => absint($entry['type']),
                    'description' => sanitize_text_field($entry['description']),
                    'amount' => floatval($entry['amount']),
                );
            }
        }
        return $sanitized_data;
    }
}
1161050160760