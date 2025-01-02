<?php
/**
 * Shortcodes for Personal Finance Tracker
 *
 * @package PersonalFinanceTracker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PFT_Shortcodes {
    public function __construct() {
        add_shortcode('personal_finance_tracker', array($this, 'render_finance_tracker'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_shortcode_assets'));
    }

    /**
     * Enqueue necessary scripts and styles for the shortcode
     */
    public function enqueue_shortcode_assets() {
        wp_enqueue_style('pft-shortcode-styles', PFT_PLUGIN_URL . 'assets/css/pft-shortcode-styles.css', array(), PFT_VERSION);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.1', true);
        wp_enqueue_script('pft-shortcode-scripts', PFT_PLUGIN_URL . 'assets/js/pft-shortcode-scripts.js', array('jquery', 'chart-js'), PFT_VERSION, true);
        
        wp_localize_script('pft-shortcode-scripts', 'pftShortcode', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pft_shortcode_nonce')
        ));
    }

    /**
     * Render the finance tracker shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function render_finance_tracker($atts) {
        $atts = shortcode_atts(array(
            'months' => 6,
            'show_categories' => 'yes',
            'chart_type' => 'bar'
        ), $atts, 'personal_finance_tracker');

        ob_start();
        ?>
        <div id="pft-finance-tracker" class="pft-finance-tracker" data-months="<?php echo esc_attr($atts['months']); ?>" data-show-categories="<?php echo esc_attr($atts['show_categories']); ?>" data-chart-type="<?php echo esc_attr($atts['chart_type']); ?>">
            <h2><?php esc_html_e('Personal Finance Tracker', 'personal-finance-tracker'); ?></h2>
            
            <div class="pft-summary-cards">
                <div class="pft-summary-card pft-income">
                    <h3><?php esc_html_e('Total Income', 'personal-finance-tracker'); ?></h3>
                    <div class="pft-amount" id="pft-total-income">$0.00</div>
                </div>
                <div class="pft-summary-card pft-expense">
                    <h3><?php esc_html_e('Total Expenses', 'personal-finance-tracker'); ?></h3>
                    <div class="pft-amount" id="pft-total-expenses">$0.00</div>
                </div>
                <div class="pft-summary-card pft-balance">
                    <h3><?php esc_html_e('Balance', 'personal-finance-tracker'); ?></h3>
                    <div class="pft-amount" id="pft-balance">$0.00</div>
                </div>
            </div>

            <div class="pft-chart-section">
                <h3><?php esc_html_e('Financial Overview', 'personal-finance-tracker'); ?></h3>
                <div class="pft-chart-container">
                    <canvas id="pft-finance-chart"></canvas>
                </div>
            </div>

            <?php if ($atts['show_categories'] === 'yes') : ?>
            <div class="pft-categories-section">
                <div class="pft-income-categories">
                    <h3><?php esc_html_e('Income Categories', 'personal-finance-tracker'); ?></h3>
                    <ul id="pft-income-categories-list"></ul>
                </div>
                <div class="pft-expense-categories">
                    <h3><?php esc_html_e('Expense Categories', 'personal-finance-tracker'); ?></h3>
                    <ul id="pft-expense-categories-list"></ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="pft-monthly-details">
                <h3><?php esc_html_e('Monthly Details', 'personal-finance-tracker'); ?></h3>
                <div id="pft-monthly-details-content"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}