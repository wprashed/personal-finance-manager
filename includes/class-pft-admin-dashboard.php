<?php
/**
 * Admin Dashboard functionality for Personal Finance Tracker
 *
 * @package PersonalFinanceTracker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PFT_Admin_Dashboard {
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts($hook) {
        if ('index.php' != $hook) {
            return;
        }
        wp_enqueue_style('pft-admin-styles', PFT_PLUGIN_URL . 'assets/css/pft-admin-styles.css', array(), PFT_VERSION);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.1', true);
        wp_enqueue_script('pft-admin-scripts', PFT_PLUGIN_URL . 'assets/js/pft-admin-scripts.js', array('jquery', 'chart-js'), PFT_VERSION, true);
        wp_localize_script('pft-admin-scripts', 'pftAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pft_admin_nonce')
        ));
    }

    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'pft_monthly_summary',
            __('Financial Summary', 'personal-finance-tracker'),
            array($this, 'render_monthly_summary_widget')
        );
        
        wp_add_dashboard_widget(
            'pft_yearly_trends',
            __('Yearly Financial Trends', 'personal-finance-tracker'),
            array($this, 'render_yearly_trends_widget')
        );
    }

    public function render_monthly_summary_widget() {
        $current_month = date('m');
        $current_year = date('Y');
        $monthly_data = $this->get_monthly_summary($current_month, $current_year);
        ?>
        <div class="pft-admin-summary">
            <div class="pft-summary-cards">
                <div class="pft-card pft-income">
                    <h3><?php esc_html_e('Monthly Income', 'personal-finance-tracker'); ?></h3>
                    <span class="amount"><?php echo esc_html($this->format_currency($monthly_data['income'])); ?></span>
                    <span class="trend <?php echo $monthly_data['income_trend'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $monthly_data['income_trend'] >= 0 ? '↑' : '↓'; ?> 
                        <?php echo abs($monthly_data['income_trend']); ?>%
                    </span>
                </div>
                <div class="pft-card pft-expenses">
                    <h3><?php esc_html_e('Monthly Expenses', 'personal-finance-tracker'); ?></h3>
                    <span class="amount"><?php echo esc_html($this->format_currency($monthly_data['expenses'])); ?></span>
                    <span class="trend <?php echo $monthly_data['expense_trend'] <= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $monthly_data['expense_trend'] >= 0 ? '↑' : '↓'; ?> 
                        <?php echo abs($monthly_data['expense_trend']); ?>%
                    </span>
                </div>
                <div class="pft-card pft-savings">
                    <h3><?php esc_html_e('Net Savings', 'personal-finance-tracker'); ?></h3>
                    <span class="amount"><?php echo esc_html($this->format_currency($monthly_data['savings'])); ?></span>
                    <span class="trend <?php echo $monthly_data['savings_trend'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $monthly_data['savings_trend'] >= 0 ? '↑' : '↓'; ?> 
                        <?php echo abs($monthly_data['savings_trend']); ?>%
                    </span>
                </div>
            </div>
            <div class="pft-chart-container">
                <canvas id="pftMonthlySummaryChart"></canvas>
            </div>
        </div>
        <?php
    }

    public function render_yearly_trends_widget() {
        ?>
        <div class="pft-admin-trends">
            <div class="pft-chart-container">
                <canvas id="pftYearlyTrendsChart"></canvas>
            </div>
            <div class="pft-trends-stats">
                <div class="pft-stat-item">
                    <h4><?php esc_html_e('Highest Income Month', 'personal-finance-tracker'); ?></h4>
                    <p><?php echo esc_html($this->get_highest_income_month()); ?></p>
                </div>
                <div class="pft-stat-item">
                    <h4><?php esc_html_e('Lowest Expense Month', 'personal-finance-tracker'); ?></h4>
                    <p><?php echo esc_html($this->get_lowest_expense_month()); ?></p>
                </div>
                <div class="pft-stat-item">
                    <h4><?php esc_html_e('Average Monthly Savings', 'personal-finance-tracker'); ?></h4>
                    <p><?php echo esc_html($this->get_average_monthly_savings()); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_monthly_summary($month, $year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pft_monthly_finances';

        $results = $wpdb->get_row($wpdb->prepare(
            "SELECT SUM(income) as total_income, SUM(expenses) as total_expenses
            FROM $table_name
            WHERE MONTH(date) = %d AND YEAR(date) = %d",
            $month,
            $year
        ));

        $income = $results ? $results->total_income : 0;
        $expenses = $results ? $results->total_expenses : 0;
        $savings = $income - $expenses;

        return array(
            'income' => $income,
            'expenses' => $expenses,
            'savings' => $savings,
            'income_trend' => $this->calculate_trend('income', $month, $year),
            'expense_trend' => $this->calculate_trend('expenses', $month, $year),
            'savings_trend' => $this->calculate_trend('savings', $month, $year)
        );
    }

    private function format_currency($amount) {
        $currency_symbol = get_option('pft_currency_symbol', '$');
        return $currency_symbol . number_format($amount, 2);
    }

    private function calculate_trend($type, $month, $year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pft_monthly_finances';

        $current = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM($type) as total
            FROM $table_name
            WHERE MONTH(date) = %d AND YEAR(date) = %d",
            $month,
            $year
        ));

        $previous_month = $month - 1;
        $previous_year = $year;
        if ($previous_month == 0) {
            $previous_month = 12;
            $previous_year--;
        }

        $previous = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM($type) as total
            FROM $table_name
            WHERE MONTH(date) = %d AND YEAR(date) = %d",
            $previous_month,
            $previous_year
        ));

        if ($previous == 0) {
            return 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function get_highest_income_month() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pft_monthly_finances';

        $result = $wpdb->get_row(
            "SELECT DATE_FORMAT(date, '%M %Y') as month, income
            FROM $table_name
            WHERE income = (SELECT MAX(income) FROM $table_name)
            ORDER BY date DESC
            LIMIT 1"
        );

        return $result ? $result->month . ' (' . $this->format_currency($result->income) . ')' : __('No data available', 'personal-finance-tracker');
    }

    private function get_lowest_expense_month() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pft_monthly_finances';

        $result = $wpdb->get_row(
            "SELECT DATE_FORMAT(date, '%M %Y') as month, expenses
            FROM $table_name
            WHERE expenses = (SELECT MIN(expenses) FROM $table_name)
            ORDER BY date DESC
            LIMIT 1"
        );

        return $result ? $result->month . ' (' . $this->format_currency($result->expenses) . ')' : __('No data available', 'personal-finance-tracker');
    }

    private function get_average_monthly_savings() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pft_monthly_finances';

        $result = $wpdb->get_var(
            "SELECT AVG(income - expenses) as avg_savings
            FROM $table_name"
        );

        return $result ? $this->format_currency($result) : __('No data available', 'personal-finance-tracker');
    }
}
