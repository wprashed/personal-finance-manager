<?php
class PFM_Reports {
    public function __construct() {
        add_action('wp_ajax_pfm_get_report_data', array($this, 'get_report_data'));
    }

    public function get_report_data() {
        check_ajax_referer('pfm_reports', 'nonce');

        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

        $args = array(
            'post_type' => 'pfm_entry',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_pfm_date',
                    'value' => array($start_date, $end_date),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        );

        $query = new WP_Query($args);
        $data = array(
            'income' => array(),
            'expense' => array(),
            'total_income' => 0,
            'total_expense' => 0
        );

        while ($query->have_posts()) {
            $query->the_post();
            $amount = get_post_meta(get_the_ID(), '_pfm_amount', true);
            $type = get_post_meta(get_the_ID(), '_pfm_type', true);
            $category = wp_get_post_terms(get_the_ID(), 'pfm_category', array('fields' => 'names'));

            if (!empty($category)) {
                $category = $category[0];
                if ($type === 'income') {
                    $data['income'][$category] = isset($data['income'][$category]) ? $data['income'][$category] + $amount : $amount;
                    $data['total_income'] += $amount;
                } else {
                    $data['expense'][$category] = isset($data['expense'][$category]) ? $data['expense'][$category] + $amount : $amount;
                    $data['total_expense'] += $amount;
                }
            }
        }

        wp_reset_postdata();

        wp_send_json_success($data);
    }
}

