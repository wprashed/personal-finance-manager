<?php
class PFM_Post_Types {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomy'));
    }

    public function register_post_type() {
        $labels = array(
            'name' => 'Finance Entries',
            'singular_name' => 'Finance Entry',
            'menu_name' => 'Finance Entries',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Finance Entry',
            'edit_item' => 'Edit Finance Entry',
            'new_item' => 'New Finance Entry',
            'view_item' => 'View Finance Entry',
            'search_items' => 'Search Finance Entries',
            'not_found' => 'No finance entries found',
            'not_found_in_trash' => 'No finance entries found in trash',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'finance-entry'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'author', 'thumbnail'),
            'show_in_rest' => true,
        );

        register_post_type('pfm_entry', $args);
    }

    public function register_taxonomy() {
        $labels = array(
            'name' => 'Categories',
            'singular_name' => 'Category',
            'search_items' => 'Search Categories',
            'all_items' => 'All Categories',
            'parent_item' => 'Parent Category',
            'parent_item_colon' => 'Parent Category:',
            'edit_item' => 'Edit Category',
            'update_item' => 'Update Category',
            'add_new_item' => 'Add New Category',
            'new_item_name' => 'New Category Name',
            'menu_name' => 'Categories',
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'pfm-category'),
            'show_in_rest' => true,
        );

        register_taxonomy('pfm_category', array('pfm_entry'), $args);
    }
}

