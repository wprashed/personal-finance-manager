<?php
/**
 * Custom post types and taxonomies for Personal Finance Tracker
 *
 * @package PersonalFinanceTracker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PFT_Post_Types {

    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
    }

    /**
     * Register custom post types
     */
    public function register_post_types() {
        $labels = array(
            'name'                  => _x('Money Metrics', 'Post type general name', 'personal-finance-tracker'),
            'singular_name'         => _x('Money Metrics', 'Post type singular name', 'personal-finance-tracker'),
            'menu_name'             => _x('Money Metrics', 'Admin Menu text', 'personal-finance-tracker'),
            'name_admin_bar'        => _x('Monthly Finance', 'Add New on Toolbar', 'personal-finance-tracker'),
            'add_new'               => __('Add New', 'personal-finance-tracker'),
            'add_new_item'          => __('Add New Transaction', 'personal-finance-tracker'),
            'new_item'              => __('New Transaction', 'personal-finance-tracker'),
            'edit_item'             => __('Edit Transaction', 'personal-finance-tracker'),
            'view_item'             => __('View Transaction', 'personal-finance-tracker'),
            'all_items'             => __('All Transaction', 'personal-finance-tracker'),
            'search_items'          => __('Search Transaction', 'personal-finance-tracker'),
            'parent_item_colon'     => __('Parent Transaction:', 'personal-finance-tracker'),
            'not_found'             => __('No Transaction found.', 'personal-finance-tracker'),
            'not_found_in_trash'    => __('No Transaction found in Trash.', 'personal-finance-tracker'),
            'featured_image'        => _x('Monthly Finance Cover Image', 'Overrides the "Featured Image" phrase for this post type. Added in 4.3', 'personal-finance-tracker'),
            'set_featured_image'    => _x('Set cover image', 'Overrides the "Set featured image" phrase for this post type. Added in 4.3', 'personal-finance-tracker'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the "Remove featured image" phrase for this post type. Added in 4.3', 'personal-finance-tracker'),
            'use_featured_image'    => _x('Use as cover image', 'Overrides the "Use as featured image" phrase for this post type. Added in 4.3', 'personal-finance-tracker'),
            'archives'              => _x('Monthly Finance archives', 'The post type archive label used in nav menus. Default "Post Archives". Added in 4.4', 'personal-finance-tracker'),
            'insert_into_item'      => _x('Insert into monthly finance', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post). Added in 4.4', 'personal-finance-tracker'),
            'uploaded_to_this_item' => _x('Uploaded to this monthly finance', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post). Added in 4.4', 'personal-finance-tracker'),
            'filter_items_list'     => _x('Filter Transaction list', 'Screen reader text for the filter links heading on the post type listing screen. Default "Filter posts list"/"Filter pages list". Added in 4.4', 'personal-finance-tracker'),
            'items_list_navigation' => _x('Transaction list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default "Posts list navigation"/"Pages list navigation". Added in 4.4', 'personal-finance-tracker'),
            'items_list'            => _x('Transaction list', 'Screen reader text for the items list heading on the post type listing screen. Default "Posts list"/"Pages list". Added in 4.4', 'personal-finance-tracker'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'monthly-finance'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'author'),
            'menu_icon'          => 'dashicons-chart-area',
            'template' => array(
                array('core/paragraph', array(
                    'content' => __('Use the fields below to enter your monthly finance details.', 'personal-finance-tracker'),
                )),
            ),
            'template_lock' => 'all',
        );

        register_post_type('pft_monthly_finance', $args);
    }

    /**
     * Register custom taxonomies
     */
    public function register_taxonomies() {
        // Income Category Taxonomy
        $income_labels = array(
            'name'              => _x('Income Types', 'taxonomy general name', 'personal-finance-tracker'),
            'singular_name'     => _x('Income Type', 'taxonomy singular name', 'personal-finance-tracker'),
            'search_items'      => __('Search Income Types', 'personal-finance-tracker'),
            'all_items'         => __('All Income Types', 'personal-finance-tracker'),
            'parent_item'       => __('Parent Income Type', 'personal-finance-tracker'),
            'parent_item_colon' => __('Parent Income Type:', 'personal-finance-tracker'),
            'edit_item'         => __('Edit Income Type', 'personal-finance-tracker'),
            'update_item'       => __('Update Income Type', 'personal-finance-tracker'),
            'add_new_item'      => __('Add New Income Type', 'personal-finance-tracker'),
            'new_item_name'     => __('New Income Type Name', 'personal-finance-tracker'),
            'menu_name'         => __('Income Categories', 'personal-finance-tracker'),
        );

        $income_args = array(
            'hierarchical'      => true,
            'labels'            => $income_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'income-category'),
        );

        register_taxonomy('pft_income_category', array('pft_monthly_finance'), $income_args);

        // Expense Category Taxonomy
        $expense_labels = array(
            'name'              => _x('Expense Types', 'taxonomy general name', 'personal-finance-tracker'),
            'singular_name'     => _x('Expense Type', 'taxonomy singular name', 'personal-finance-tracker'),
            'search_items'      => __('Search Expense Types', 'personal-finance-tracker'),
            'all_items'         => __('All Expense Types', 'personal-finance-tracker'),
            'parent_item'       => __('Parent Expense Type', 'personal-finance-tracker'),
            'parent_item_colon' => __('Parent Expense Type:', 'personal-finance-tracker'),
            'edit_item'         => __('Edit Expense Type', 'personal-finance-tracker'),
            'update_item'       => __('Update Expense Type', 'personal-finance-tracker'),
            'add_new_item'      => __('Add New Expense Type', 'personal-finance-tracker'),
            'new_item_name'     => __('New Expense Type Name', 'personal-finance-tracker'),
            'menu_name'         => __('Expense Types', 'personal-finance-tracker'),
        );

        $expense_args = array(
            'hierarchical'      => true,
            'labels'            => $expense_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'expense-category'),
        );

        register_taxonomy('pft_expense_category', array('pft_monthly_finance'), $expense_args);
    }
}