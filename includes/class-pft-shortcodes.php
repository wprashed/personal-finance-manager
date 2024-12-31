<?php
class PFT_Shortcodes {
    public function __construct() {
        add_shortcode('personal_finance_tracker', array($this, 'render_finance_tracker'));
    }

    public function render_finance_tracker($atts) {
        ob_start();
        ?>
        <div id="pft-finance-tracker">
            <h2>Personal Finance Tracker</h2>
            <div class="pft-monthly-summary">
                <h3>Monthly Summary</h3>
                <div id="pft-monthly-chart-container">
                    <canvas id="pft-monthly-chart"></canvas>
                </div>
            </div>
            <div class="pft-monthly-details">
                <h3>Monthly Details</h3>
                <div id="pft-monthly-details-content"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}