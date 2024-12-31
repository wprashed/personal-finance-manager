<?php
class PFT_Post_Types {
    public function __construct() {
        add_action('init', array($this, 'register_transaction_post_type'));
    }

    public function register_transaction_post_type() {
        $args = array(
            'public' => true,
            'label'  => 'Transactions',
            'supports' => array('title', 'custom-fields'),
            'menu_icon' => 'dashicons-money-alt',
        );
        register_post_type('pft_transaction', $args);
    }
}