<?php
// Add a setting page
function our_google_reviews_add_menu_page()
{
    // Menu registration is now handled by the Admin class
    return;
}

// Main settings page callback
function our_google_reviews_callback()
{
    // This is now handled by the Admin class
    return;
}

// Sync reviews page callback
function sync_reviews_callback() {
    // This is now handled by the Admin class
    return;
}

// Helper functions
function get_business_sync_data($term_id) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}agr_businesses WHERE term_id = %d",
        $term_id
    ));
}

function get_business_place_id($term_id) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT place_id FROM {$wpdb->prefix}agr_businesses WHERE term_id = %d",
        $term_id
    ));
}

function get_sync_status_badge($sync_data) {
    if (!$sync_data || !$sync_data->last_sync) {
        return '<span class="badge badge-warning">Never synced</span>';
    }
    
    $last_sync = strtotime($sync_data->last_sync);
    $diff = time() - $last_sync;
    
    if ($diff < 24 * 3600) {
        return '<span class="badge badge-success">Up to date</span>';
    } elseif ($diff < 48 * 3600) {
        return '<span class="badge badge-warning">Sync recommended</span>';
    } else {
        return '<span class="badge badge-error">Sync required</span>';
    }
}

function delete_all_agr_google_reviews2()
{
    global $wpdb;

    // Define the post type to be deleted
    $post_type = 'agr_google_review';

    // Get all posts of the specified post type
    $posts = get_posts(
        array(
            'post_type' => $post_type,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids',
        )
    );

    // Loop through each post and delete it
    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true); // true parameter ensures the post is permanently deleted
    }
}

function expose_all_meta_fields_in_rest($response, $post, $request)
{
    if ($post->post_type === 'agr_google_review') {
        $meta = get_post_meta($post->ID);
        // Sanitize meta data
        $sanitized_meta = array_map(function ($meta_value) {
            return is_array($meta_value) ? array_map('sanitize_text_field', $meta_value) : sanitize_text_field($meta_value);
        }, $meta);
        $response->data['meta'] = $sanitized_meta;
    }
    return $response;
}
add_filter('rest_prepare_agr_google_review', 'expose_all_meta_fields_in_rest', 10, 3);

// Allow radio button instead of checkboxes for hierarchical taxonomies
function term_radio_checklist($args)
{
    if (!empty($args['taxonomy']) && $args['taxonomy'] === 'business') {
        if (empty($args['walker']) || is_a($args['walker'], 'Walker')) {
            if (!class_exists('term_radio_checklist')) {
                class term_radio_checklist extends Walker_Category_Checklist
                {
                    function walk($elements, $max_depth, ...$args)
                    {
                        $output = parent::walk($elements, $max_depth, ...$args);
                        $output = str_replace(
                            array('type="checkbox"', "type='checkbox'"),
                            array('type="radio"', "type='radio'"),
                            $output
                        );

                        return $output;
                    }
                }
            }

            $args['walker'] = new term_radio_checklist;
        }
    }

    return $args;
}
add_filter('wp_terms_checklist_args', 'term_radio_checklist');
