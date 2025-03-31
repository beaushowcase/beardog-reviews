<?php
namespace BeardogReviews;

class Reviews {
    private $api_endpoint = 'https://repocean.com/reviews/getJson/';
    private $db;

    public function __construct() {
        $this->db = new Database();
        
        // Add AJAX handler for toggle review disable state
        add_action('wp_ajax_toggle_review_disabled', array($this, 'ajax_toggle_review_disabled'));
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
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
            'supports' => array(''),
            'has_archive' => false,
            'show_in_rest' => true,
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
                $new_columns['disabled'] = __('Disable', 'beardog-reviews');
                $new_columns['rating'] = __('Rating', 'beardog-reviews');
                $new_columns['reviewer'] = __('Reviewer', 'beardog-reviews');
                $new_columns['business'] = __('Business', 'beardog-reviews');
                $new_columns['review_date'] = __('Review Date', 'beardog-reviews');
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
                
            case 'disabled':
                $is_disabled = get_post_meta($post_id, '_review_disabled', true);
                $checked = $is_disabled ? 'checked' : '';
                echo '<label class="agr-switch">';
                echo '<input type="checkbox" class="agr-review-disable-toggle" data-review-id="' . esc_attr($post_id) . '" ' . $checked . '>';
                echo '<span class="agr-slider round"></span>';
                echo '</label>';
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
        $updated_reviews_count = 0;
        $new_reviews_count = 0;
    
        foreach ($reviews as $review) {
            // Unique identifier for the review
            $review_unique_id = $this->generate_review_unique_key($review);
    
            // Check for existing review
            $existing_review = $this->find_existing_review($review_unique_id);
    
            if ($existing_review) {
                // Check if review needs update (older than 1 month)
                $update_result = $this->maybe_update_existing_review($existing_review, $review, $term_id);
                if ($update_result) {
                    $updated_reviews_count++;
                }
                continue;
            }
    
            // If no existing review, create new
            $new_post_id = $this->create_new_review_post($review, $term_id, $review_unique_id);
            
            if ($new_post_id) {
                $new_reviews_count++;
            }
        }
    
        // Log or return update statistics
        return [
            'new_reviews' => $new_reviews_count,
            'updated_reviews' => $updated_reviews_count
        ];
    }
    
    /**
     * Generate a unique key for the review
     * 
     * @param array $review Review data
     * @return string Unique identifier
     */
    private function generate_review_unique_key($review) {
        // Combine multiple unique identifiers to create a robust unique key
        $unique_parts = [
            $review['id'] ?? '', // Google review ID
            $review['author_name'] ?? '',
            $review['text'] ?? '',
            $review['rating'] ?? ''
        ];
    
        return md5(implode('|', $unique_parts));
    }
    
    /**
     * Find existing review by unique identifier
     * 
     * @param string $unique_id Unique review identifier
     * @return WP_Post|false Existing review post or false
     */
    private function find_existing_review($unique_id) {
        $existing_reviews = get_posts([
            'post_type' => 'agr_google_review',
            'meta_key' => '_review_unique_id',
            'meta_value' => $unique_id,
            'posts_per_page' => 1
        ]);
    
        return !empty($existing_reviews) ? $existing_reviews[0] : false;
    }
    
    /**
     * Potentially update an existing review
     * 
     * @param WP_Post $existing_post Existing review post
     * @param array $new_review New review data
     * @param int $term_id Business term ID
     * @return bool Whether the review was updated
     */
    private function maybe_update_existing_review($existing_post, $new_review, $term_id) {
        // Check if review is older than 1 month
        $last_updated = get_post_meta($existing_post->ID, 'last_update_timestamp', true);
        $current_time = current_time('timestamp');
        
        // Update if no previous update or older than 1 month
        $one_month_ago = strtotime('-1 month', $current_time);
        if (!$last_updated || $last_updated < $one_month_ago) {
            // Update review metadata
            $updated_meta = [
                'rating' => $new_review['rating'],
                'original_text' => $new_review['text'],
                'published_date' => $this->convert_timestamp_to_mysql($new_review['published_date']),
                'profile_photo_url' => $new_review['profile_photo_url']
            ];
    
            // Update post meta
            foreach ($updated_meta as $key => $value) {
                update_post_meta($existing_post->ID, $key, $value);
            }
    
            // Update last update timestamp
            update_post_meta($existing_post->ID, 'last_update_timestamp', $current_time);
    
            return true;
        }
    
        return false;
    }
    
    /**
     * Create a new review post
     * 
     * @param array $review Review data
     * @param int $term_id Business term ID
     * @param string $unique_id Unique review identifier
     * @return int|false Post ID or false on failure
     */
    private function create_new_review_post($review, $term_id, $unique_id) {
        // Convert timestamp
        $published_date = $this->convert_timestamp_to_mysql($review['published_date']);
    
        // Create new review post
        $post_id = wp_insert_post([
            'post_type' => 'agr_google_review',
            'post_title' => wp_strip_all_tags($review['author_name']),
            'post_content' => '', // Empty content, will store in meta
            'post_status' => 'publish',
            'post_date' => current_time('mysql'),
            'post_date_gmt' => get_gmt_from_date(current_time('mysql'))
        ]);
    
        if ($post_id) {
            // Set business taxonomy
            wp_set_object_terms($post_id, $term_id, 'business');
    
            // Store review metadata
            $meta = [
                '_review_unique_id' => $unique_id, // Unique identifier
                'review_id' => $review['id'],
                'author_name' => $review['author_name'],
                'profile_photo_url' => $review['profile_photo_url'],
                'rating' => $review['rating'],
                'original_text' => $review['text'],
                'custom_text' => '',
                'published_date' => $published_date,
                'business_name' => $review['location_name'],
                'business_address' => $review['formatted_address'],
                'business_phone' => $review['formatted_phone_number'],
                'place_id' => $review['place_id'],
                'last_update_timestamp' => current_time('timestamp')
            ];
    
            foreach ($meta as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
    
            return $post_id;
        }
    
        return false;
    }
    
    /**
     * Convert various timestamp formats to MySQL datetime format
     * 
     * @param mixed $timestamp Timestamp to convert
     * @return string MySQL formatted date or current time if conversion fails
     */
    private function convert_timestamp_to_mysql($timestamp) {
        // If no timestamp provided, use current time
        if (empty($timestamp)) {
            return current_time('mysql');
        }
    
        // Ensure we're working with a numeric value
        if (!is_numeric($timestamp)) {
            // Try to convert string to timestamp
            $converted = strtotime($timestamp);
            return $converted ? date('Y-m-d H:i:s', $converted) : current_time('mysql');
        }
    
        // Handle microsecond timestamps (very long numbers)
        if (strlen((string)$timestamp) > 13) {
            // Convert microseconds to seconds
            $seconds = $timestamp / 1000000;
        } 
        // Handle millisecond timestamps (13 digits)
        elseif (strlen((string)$timestamp) === 13) {
            $seconds = $timestamp / 1000;
        } 
        // Handle standard Unix timestamps (10 digits)
        else {
            $seconds = $timestamp;
        }
    
        // Convert to MySQL datetime format
        return date('Y-m-d H:i:s', $seconds);
    }
    
    /**
     * Cleanup old or irrelevant reviews
     * Optional method to remove very old reviews
     * 
     * @param int $months Number of months to keep reviews
     */
    public function cleanup_old_reviews($months = 12) {
        $args = [
            'post_type' => 'agr_google_review',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'published_date',
                    'value' => date('Y-m-d H:i:s', strtotime("-{$months} months")),
                    'compare' => '<',
                    'type' => 'DATETIME'
                ]
            ]
        ];
    
        $old_reviews = get_posts($args);
    
        foreach ($old_reviews as $review) {
            wp_delete_post($review->ID, true);
        }
    
        return count($old_reviews);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        // Only load on Google Reviews list page
        if ($hook == 'edit.php' && $post_type == 'agr_google_review') {
            // Enqueue inline CSS for the switch
            wp_add_inline_style('wp-admin', '
                .agr-switch {
                    position: relative;
                    display: inline-block;
                    width: 40px;
                    height: 22px;
                }
                
                .agr-switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                
                .agr-slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: .4s;
                }
                
                .agr-slider:before {
                    position: absolute;
                    content: "";
                    height: 16px;
                    width: 16px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    transition: .4s;
                }
                
                input:checked + .agr-slider {
                    background-color: #f44336;
                }
                
                input:focus + .agr-slider {
                    box-shadow: 0 0 1px #f44336;
                }
                
                input:checked + .agr-slider:before {
                    transform: translateX(18px);
                }
                
                .agr-slider.round {
                    border-radius: 34px;
                }
                
                .agr-slider.round:before {
                    border-radius: 50%;
                }
            ');
            
            // Enqueue inline JavaScript for AJAX with fixes for double loader
            wp_add_inline_script('jquery', '
                jQuery(document).ready(function($) {
                    $(".agr-review-disable-toggle").on("change", function() {
                        var reviewId = $(this).data("review-id");
                        var isDisabled = $(this).is(":checked") ? 1 : 0;
                        var toggleSwitch = $(this);
                        
                        // Remove any existing spinners
                        toggleSwitch.parent().find(".spinner").remove();
                        
                        // Show spinner
                        toggleSwitch.parent().append("<span class=\"spinner is-active\" style=\"float:none;margin-left:10px;\"></span>");
                        
                        // Disable the toggle while processing
                        toggleSwitch.prop("disabled", true);
                        
                        $.ajax({
                            url: ajaxurl,
                            type: "POST",
                            data: {
                                action: "toggle_review_disabled",
                                review_id: reviewId,
                                is_disabled: isDisabled,
                                nonce: "' . wp_create_nonce('agr_toggle_review_disabled') . '"
                            },
                            success: function(response) {
                                // Remove spinner
                                toggleSwitch.parent().find(".spinner").remove();
                                
                                // Re-enable the toggle
                                toggleSwitch.prop("disabled", false);
                            },
                            error: function() {
                                // Remove spinner in case of error
                                toggleSwitch.parent().find(".spinner").remove();
                                
                                // Re-enable the toggle
                                toggleSwitch.prop("disabled", false);
                            }
                        });
                    });
                });
            ');
        }
    }
    
    /**
     * AJAX handler for toggling review disabled state
     */
    public function ajax_toggle_review_disabled() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'agr_toggle_review_disabled')) {
            wp_send_json_error('Invalid security token');
        }
        
        // Get parameters
        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        $is_disabled = isset($_POST['is_disabled']) ? (bool)intval($_POST['is_disabled']) : false;
        
        // Update post meta
        update_post_meta($review_id, '_review_disabled', $is_disabled);
        
        wp_send_json_success();
    }
}