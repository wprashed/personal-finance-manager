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
            <div class="pft-form-container">
                <form id="pft-transaction-form">
                    <input type="text" id="pft-description" placeholder="Description" required>
                    <input type="number" id="pft-amount" placeholder="Amount" step="0.01" required>
                    <select id="pft-type">
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                    <input type="date" id="pft-date" required>
                    <button type="submit">Add Transaction</button>
                </form>
            </div>
            <div class="pft-summary">
                <div id="pft-total-income"></div>
                <div id="pft-total-expenses"></div>
                <div id="pft-balance"></div>
            </div>
            <div class="pft-chart-container">
                <canvas id="pft-monthly-chart"></canvas>
            </div>
            <div class="pft-transactions-list">
                <h3>Recent Transactions</h3>
                <ul id="pft-transactions"></ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}