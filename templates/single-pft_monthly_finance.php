<?php
/**
 * Template for displaying single Monthly Finance posts
 *
 * @package PersonalFinanceTracker
 */

get_header();

while ( have_posts() ) :
    the_post();

    $income = get_post_meta(get_the_ID(), '_pft_income', true);
    $expenses = get_post_meta(get_the_ID(), '_pft_expenses', true);
    $total_income = get_post_meta(get_the_ID(), '_pft_total_income', true);
    $total_expenses = get_post_meta(get_the_ID(), '_pft_total_expenses', true);
    ?>

    <div class="pft-single-finance-wrap">
        <div class="pft-editor-header">
            <h1><?php the_title(); ?></h1>
        </div>

        <div class="pft-summary-cards">
            <div class="pft-summary-card income">
                <h3><?php esc_html_e('Total Income', 'personal-finance-tracker'); ?></h3>
                <div class="amount">$<span id="pft-total-income"><?php echo esc_html(number_format(floatval($total_income), 2)); ?></span></div>
            </div>
            <div class="pft-summary-card expense">
                <h3><?php esc_html_e('Total Expenses', 'personal-finance-tracker'); ?></h3>
                <div class="amount">$<span id="pft-total-expenses"><?php echo esc_html(number_format(floatval($total_expenses), 2)); ?></span></div>
            </div>
            <div class="pft-summary-card balance">
                <h3><?php esc_html_e('Balance', 'personal-finance-tracker'); ?></h3>
                <div class="amount">$<span id="pft-balance"><?php echo esc_html(number_format(floatval($total_income) - floatval($total_expenses), 2)); ?></span></div>
            </div>
        </div>

        <div class="pft-transactions-section">
            <h3 class="pft-section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                <?php esc_html_e('Income', 'personal-finance-tracker'); ?>
            </h3>
            <div id="pft-income-entries" class="pft-transaction-grid">
                <?php
                if (!empty($income) && is_array($income)) {
                    foreach ($income as $transaction) {
                        ?>
                        <div class="pft-transaction-row">
                            <div><?php echo esc_html(get_term($transaction['type'], 'pft_income_category')->name); ?></div>
                            <div><?php echo esc_html($transaction['description']); ?></div>
                            <div>$<?php echo esc_html(number_format(floatval($transaction['amount']), 2)); ?></div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>

        <div class="pft-transactions-section">
            <h3 class="pft-section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                <?php esc_html_e('Expenses', 'personal-finance-tracker'); ?>
            </h3>
            <div id="pft-expense-entries" class="pft-transaction-grid">
                <?php
                if (!empty($expenses) && is_array($expenses)) {
                    foreach ($expenses as $transaction) {
                        ?>
                        <div class="pft-transaction-row">
                            <div><?php echo esc_html(get_term($transaction['type'], 'pft_expense_category')->name); ?></div>
                            <div><?php echo esc_html($transaction['description']); ?></div>
                            <div>$<?php echo esc_html(number_format(floatval($transaction['amount']), 2)); ?></div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
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

    <?php
endwhile;

get_footer();