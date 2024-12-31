<?php
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
            'Financial Summary',
            array($this, 'render_monthly_summary_widget')
        );
        
        wp_add_dashboard_widget(
            'pft_yearly_trends',
            'Yearly Financial Trends',
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
                    <h3>Monthly Income</h3>
                    <span class="amount"><?php echo esc_html($this->format_currency($monthly_data['income'])); ?></span>
                    <span class="trend <?php echo $monthly_data['income_trend'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $monthly_data['income_trend'] >= 0 ? '↑' : '↓'; ?> 
                        <?php echo abs($monthly_data['income_trend']); ?>%
                    </span>
                </div>
                <div class="pft-card pft-expenses">
                    <h3>Monthly Expenses</h3>
                    <span class="amount"><?php echo esc_html($this->format_currency($monthly_data['expenses'])); ?></span>
                    <span class="trend <?php echo $monthly_data['expense_trend'] <= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $monthly_data['expense_trend'] >= 0 ? '↑' : '↓'; ?> 
                        <?php echo abs($monthly_data['expense_trend']); ?>%
                    </span>
                </div>
                <div class="pft-card pft-savings">
                    <h3>Net Savings</h3>
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
                    <h4>Highest Income Month</h4>
                    <p><?php echo esc_html($this->get_highest_income_month()); ?></p>
                </div>
                <div class="pft-stat-item">
                    <h4>Lowest Expense Month</h4>
                    <p><?php echo esc_html($this->get_lowest_expense_month()); ?></p>
                </div>
                <div class="pft-stat-item">
                    <h4>Average Monthly Savings</h4>
                    <p><?php echo esc_html($this->get_average_monthly_savings()); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_monthly_summary($month, $year) {
        // Implement logic to get monthly summary data
        return array(
            'income' => $this->get_total_income($month, $year),
            'expenses' => $this->get_total_expenses($month, $year),
            'savings' => $this->get_total_savings($month, $year),
            'income_trend' => $this->calculate_trend('income', $month, $year),
            'expense_trend' => $this->calculate_trend('expenses', $month, $year),
            'savings_trend' => $this->calculate_trend('savings', $month, $year)
        );
    }

    private function format_currency($amount) {
        $currency_symbol = get_option('pft_currency_symbol', '$');
        return $currency_symbol . number_format($amount, 2);
    }

    // Add helper methods for calculations
    private function get_total_income($month, $year) {
        // Implement income calculation
        return 0;
    }

    private function get_total_expenses($month, $year) {
        // Implement expenses calculation
        return 0;
    }

    private function get_total_savings($month, $year) {
        // Implement savings calculation
        return 0;
    }

    private function calculate_trend($type, $month, $year) {
        // Implement trend calculation
        return 0;
    }

    private function get_highest_income_month() {
        // Implement highest income month calculation
        return 'December 2023';
    }

    private function get_lowest_expense_month() {
        // Implement lowest expense month calculation
        return 'November 2023';
    }

    private function get_average_monthly_savings() {
        // Implement average monthly savings calculation
        return '$1,500.00';
    }
}