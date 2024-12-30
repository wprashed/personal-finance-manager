<?php
class PFM_API {
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route('pfm/v1', '/entries', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_entries'),
            'permission_callback' => array($this, 'check_permission')
        ));

        register_rest_route('pfm/v1', '/entries', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_entry'),
            'permission_callback' => array($this, 'check_permission')
        ));

        register_rest_route('pfm/v1', '/entries/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_entry'),
            'permission_callback' => array($this, 'check_permission')
        ));

        register_rest_route('pfm/v1', '/entries/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_entry'),
            'permission_callback' => array($this, 'check_permission')
        ));
    }

    public function check_permission() {
        return current_user_can('edit_posts');
    }

    public function get_entries($request) {
        $args = array(
            'post_type' => 'pfm_entry',
            'posts_per_page' => -1,
        );

        $entries = get_posts($args);
        $data = array();

        foreach ($entries as $entry) {
            $data[] = $this->prepare_entry_for_response($entry);
        }

        return new WP_REST_Response($data, 200);
    }

    public function create_entry($request) {
        $params = $request->get_params();

        $entry_data = array(
            'post_type' => 'pfm_entry',
            'post_title' => sanitize_text_field($params['title']),
            'post_status' => 'publish',
        );

        $entry_id = wp_insert_post($entry_data);

        if (is_wp_error($entry_id)) {
            return new WP_Error('cant-create', 'Cannot create entry', array('status' => 500));
        }

        update_post_meta($entry_id, '_pfm_amount', sanitize_text_field($params['amount']));
        update_post_meta($entry_id, '_pfm_type', sanitize_text_field($params['type']));
        update_post_meta($entry_id, '_pfm_date', sanitize_text_field($params['date']));

        if (isset($params['category'])) {
            wp_set_object_terms($entry_id, $params['category'], 'pfm_category');
        }

        return new WP_REST_Response($this->prepare_entry_for_response(get_post($entry_id)), 201);
    }

    public function update_entry($request) {
        $entry_id = $request['id'];
        $params = $request->get_params();

        $entry_data = array(
            'ID' => $entry_id,
            'post_title' => sanitize_text_field($params['title']),
        );

        $updated = wp_update_post($entry_data);

        if (is_wp_error($updated)) {
            return new WP_Error('cant-update', 'Cannot update entry', array('status' => 500));
        }

        update_post_meta($entry_id, '_pfm_amount', sanitize_text_field($params['amount']));
        update_post_meta($entry_id, '_pfm_type', sanitize_text_field($params['type']));
        update_post_meta($entry_id, '_pfm_date', sanitize_text_field($params['date']));

        if (isset($params['category'])) {
            wp_set_object_terms($entry_id, $params['category'], 'pfm_category');
        }

        return new WP_REST_Response($this->prepare_entry_for_response(get_post($entry_id)), 200);
    }

    public function delete_entry($request) {
        $entry_id = $request['id'];
        $result = wp_delete_post($entry_id, true);

        if (!$result) {
            return new WP_Error('cant-delete', 'Cannot delete entry', array('status' => 500));
        }

        return new WP_REST_Response(null, 204);
    }

    private function prepare_entry_for_response($post) {
        $entry = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'amount' => get_post_meta($post->ID, '_pfm_amount', true),
            'type' => get_post_meta($post->ID, '_pfm_type', true),
            'date' => get_post_meta($post->ID, '_pfm_date', true),
            'category' => wp_get_post_terms($post->ID, 'pfm_category', array('fields' => 'names'))
        );

        return $entry;
    }
}

