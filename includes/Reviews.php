<?php
namespace BeardogReviews;

class Reviews {
    private $api_endpoint = 'https://repocean.com/reviews/getJson/';
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function register_post_type() {
        register_post_type('agr_google_review', [
            'labels' => [
                'name' => __('Google Reviews', 'beardog-reviews'),
                'singular_name' => __('Google Review', 'beardog-reviews'),
                'menu_name' => __('Google Reviews', 'beardog-reviews'),
                'add_new' => __('Add New', 'beardog-reviews'),
                'add_new_item' => __('Add New Review', 'beardog-reviews'),
                'edit_item' => __('Edit Review', 'beardog-reviews'),
                'view_item' => __('View Review', 'beardog-reviews'),
                'search_items' => __('Search Reviews', 'beardog-reviews'),
                'not_found' => __('No reviews found', 'beardog-reviews'),
                'not_found_in_trash' => __('No reviews found in trash', 'beardog-reviews')
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-google',
            'supports' => ['title'],
            'has_archive' => false,
            'rewrite' => ['slug' => 'google-reviews']
        ]);

        register_taxonomy('business', 'agr_google_review', [
            'labels' => [
                'name' => __('Businesses', 'beardog-reviews'),
                'singular_name' => __('Business', 'beardog-reviews'),
                'menu_name' => __('Businesses', 'beardog-reviews'),
                'all_items' => __('All Businesses', 'beardog-reviews'),
                'edit_item' => __('Edit Business', 'beardog-reviews'),
                'update_item' => __('Update Business', 'beardog-reviews'),
                'add_new_item' => __('Add New Business', 'beardog-reviews'),
                'new_item_name' => __('New Business Name', 'beardog-reviews'),
                'search_items' => __('Search Businesses', 'beardog-reviews')
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'business']
        ]);

        // Add custom columns to the reviews list
        add_filter('manage_agr_google_review_posts_columns', [$this, 'add_review_columns']);
        add_action('manage_agr_google_review_posts_custom_column', [$this, 'manage_review_columns'], 10, 2);
        add_filter('manage_edit-agr_google_review_sortable_columns', [$this, 'sortable_review_columns']);
    }

    public function add_review_columns($columns) {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns[$key] = $value;
                $new_columns['rating'] = __('Rating', 'beardog-reviews');
                $new_columns['reviewer'] = __('Reviewer', 'beardog-reviews');
                $new_columns['business'] = __('Business', 'beardog-reviews');
                $new_columns['review_date'] = __('Review Date', 'beardog-reviews');
                $new_columns['review_link'] = __('Review Link', 'beardog-reviews');
            } else if ($key !== 'date') { // Skip the default date column
                $new_columns[$key] = $value;
            }
        }
        return $new_columns;
    }

    public function manage_review_columns($column, $post_id) {
        switch ($column) {
            case 'rating':
                $rating = get_post_meta($post_id, 'rating', true);
                echo str_repeat('★', intval($rating)) . str_repeat('☆', 5 - intval($rating));
                break;

            case 'reviewer':
                $author_name = get_post_meta($post_id, 'author_name', true);
                $profile_photo = get_post_meta($post_id, 'profile_photo_url', true);
                
                if ($profile_photo && filter_var($profile_photo, FILTER_VALIDATE_URL)) {
                    echo '<img src="' . esc_url($profile_photo) . '" alt="" style="width:32px;height:32px;border-radius:50%;vertical-align:middle;margin-right:5px;">';
                } else {
                    // Get initials from author name
                    $initials = '';
                    $name_parts = explode(' ', trim($author_name));
                    foreach ($name_parts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    echo '<span class="agr-reviewer-initials" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background-color:#' . substr(md5($author_name), 0, 6) . ';color:white;font-weight:bold;margin-right:5px;vertical-align:middle;">' . 
                         esc_html($initials) . 
                         '</span>';
                }
                echo esc_html($author_name);
                break;

            case 'business':
                $terms = get_the_terms($post_id, 'business');
                if ($terms && !is_wp_error($terms)) {
                    $business_names = array_map(function($term) {
                        return esc_html($term->name);
                    }, $terms);
                    echo implode(', ', $business_names);
                }
                break;

            case 'review_date':
                $published_date = get_post_meta($post_id, 'published_date', true);
                if ($published_date) {
                    $timestamp = strtotime($published_date);
                    if ($timestamp) {
                        // Format date as '27 December 2024'
                        echo date_i18n('j F Y', $timestamp);
                    }
                }
                break;

            case 'review_link':
                $place_id = get_post_meta($post_id, 'place_id', true);
                $review_id = get_post_meta($post_id, 'review_id', true);
                if ($place_id && $review_id) {
                    $reviewer_url = sprintf(
                        'https://search.google.com/local/reviews?placeid=%s#review=%s',
                        $place_id,
                        $review_id
                    );
                    echo '<a href="' . esc_url($reviewer_url) . '" target="_blank" class="button button-small">' . 
                         '<span class="dashicons dashicons-external" style="vertical-align:middle;"></span> ' . 
                         __('View Review', 'beardog-reviews') . '</a>';
                }
                break;
        }
    }

    public function sortable_review_columns($columns) {
        $columns['rating'] = 'rating';
        $columns['review_date'] = 'published_date';
        $columns['business'] = 'business';
        return $columns;
    }

    public function sync_all_reviews() {
        $businesses = $this->db->get_businesses_for_sync();

        foreach ($businesses as $business) {
            $this->sync_reviews($business->term_id);
        }
    }

    public function sync_reviews($term_id) {
        $place_id = $this->db->get_business_place_id($term_id);
        if (!$place_id) {
            return false;
        }

        $reviews = $this->fetch_reviews($place_id);
        if (!$reviews) {
            return false;
        }

        $this->store_reviews($reviews, $term_id);
        return true;
    }

    private function fetch_reviews($place_id) {
        $response = wp_remote_get($this->api_endpoint . $place_id);

        if (is_wp_error($response)) {
            error_log('AGR Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);


        if (empty($data) || !is_array($data)) {
            error_log('AGR Error: Invalid response from API for place_id ' . $place_id);
            return false;
        }

        return $data;
    }

    private function store_reviews($reviews, $term_id) {
        foreach ($reviews as $review) {
            // Check if review already exists
            $existing = get_posts([
                'post_type' => 'agr_google_review',
                'meta_key' => 'review_id',
                'meta_value' => $review['id'],
                'posts_per_page' => 1
            ]);

            if (!empty($existing)) {
                continue;
            }

            // Format the published date from the review's published_date
            $published_date = '';
            if (!empty($review['published_date'])) {
                // Handle both timestamp formats (seconds and milliseconds)
                if (is_numeric($review['published_date'])) {
                    $timestamp = strlen($review['published_date']) > 10 ? 
                        intval($review['published_date']) / 1000 : 
                        intval($review['published_date']);
                } else {
                    $timestamp = strtotime($review['published_date']);
                }
                if ($timestamp) {
                    $published_date = date('Y-m-d H:i:s', $timestamp);
                }
            }
            if (empty($published_date)) {
                $published_date = current_time('mysql');
            }

            // Create new review post
            $post_id = wp_insert_post([
                'post_type' => 'agr_google_review',
                'post_title' => wp_strip_all_tags($review['author_name']),
                'post_content' => '', // Empty content, will store in meta
                'post_status' => 'publish',
                'post_date' => current_time('mysql'), // Use current time for post date
                'post_date_gmt' => get_gmt_from_date(current_time('mysql'))
            ]);

            if ($post_id) {
                // Set business taxonomy
                wp_set_object_terms($post_id, $term_id, 'business');

                // Store review metadata
                $meta = [
                    'review_id' => $review['id'],
                    'author_name' => $review['author_name'],
                    'profile_photo_url' => $review['profile_photo_url'],
                    'rating' => $review['rating'],
                    'original_text' => $review['text'], // Store original review text as meta
                    'custom_text' => '', // Empty custom text field
                    'published_date' => $published_date, // Store the actual review date
                    'business_name' => $review['location_name'],
                    'business_address' => $review['formatted_address'],
                    'business_phone' => $review['formatted_phone_number'],
                    'place_id' => $review['place_id']
                ];

                foreach ($meta as $key => $value) {
                    update_post_meta($post_id, $key, $value);
                }
            }
        }
    }
} 