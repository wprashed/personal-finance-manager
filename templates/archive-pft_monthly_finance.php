<?php
/**
 * Template for displaying archive of Monthly Finance posts
 *
 * @package PersonalFinanceTracker
 */

get_header();
?>

<div class="pft-archive-wrap">
    <h1 class="pft-archive-title"><?php echo esc_html__('Monthly Finance Reports', 'personal-finance-tracker'); ?></h1>

    <div class="pft-filter-section">
        <form id="pft-filter-form">
            <label for="pft-year-filter"><?php echo esc_html__('Year:', 'personal-finance-tracker'); ?></label>
            <select id="pft-year-filter" name="year">
                <option value=""><?php echo esc_html__('All Years', 'personal-finance-tracker'); ?></option>
                <?php
                $years = range(date('Y'), date('Y') - 5);
                foreach ($years as $year) {
                    echo '<option value="' . esc_attr($year) . '">' . esc_html($year) . '</option>';
                }
                ?>
            </select>

            <label for="pft-month-filter"><?php echo esc_html__('Month:', 'personal-finance-tracker'); ?></label>
            <select id="pft-month-filter" name="month">
                <option value=""><?php echo esc_html__('All Months', 'personal-finance-tracker'); ?></option>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $month_name = date('F', mktime(0, 0, 0, $i, 1));
                    echo '<option value="' . esc_attr($i) . '">' . esc_html($month_name) . '</option>';
                }
                ?>
            </select>

            <button type="submit"><?php echo esc_html__('Filter', 'personal-finance-tracker'); ?></button>
        </form>
    </div>

    <div id="pft-archive-list" class="pft-archive-list">
        <?php
        // Initial query without filters
        $args = array(
            'post_type' => 'pft_monthly_finance',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        $query = new WP_Query($args);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $income_data = get_post_meta(get_the_ID(), '_pft_income_data', true) ?: array();
                $expense_data = get_post_meta(get_the_ID(), '_pft_expense_data', true) ?: array();
                $total_income = array_sum(array_column($income_data, 'amount'));
                $total_expenses = array_sum(array_column($expense_data, 'amount'));
                $balance = $total_income - $total_expenses;
                ?>
                <div class="pft-archive-item">
                    <h2><a href="<?php the_permalink(); ?>"><?php echo get_the_date('F Y'); ?></a></h2>
                    <div class="pft-archive-summary">
                        <span class="pft-income">Income: $<?php echo number_format($total_income, 2); ?></span>
                        <span class="pft-expenses">Expenses: $<?php echo number_format($total_expenses, 2); ?></span>
                        <span class="pft-balance">Balance: $<?php echo number_format($balance, 2); ?></span>
                    </div>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>' . esc_html__('No finance reports found.', 'personal-finance-tracker') . '</p>';
        endif;
        ?>
    </div>
</div>

<?php
get_footer();
?>