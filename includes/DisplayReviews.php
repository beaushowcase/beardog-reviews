<?php

namespace BeardogReviews;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class DisplayReviews
 * Handles the display of reviews in templates
 * 
 * @package BeardogReviews
 */
class DisplayReviews {

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize if needed
    }

    /**
     * Get all reviews by business term
     * 
     * @param bool $five_star_only Whether to return only 5-star reviews
     * @return array Array of reviews data
     */
    public static function get_all_reviews_by_term($five_star_only = false) {
        // Get all terms from 'business' taxonomy
        $terms = get_terms(array(
            'taxonomy' => 'business',
            'hide_empty' => true,
            'fields' => 'ids'
        ));

        if (is_wp_error($terms) || empty($terms)) {
            return array(
                'reviews_type' => 'All Reviews',
                'total_posts' => 0,
                'job_id' => '',
                'all_reviews' => array(),
            );
        }

        $args = array(
            'post_type' => 'agr_google_review',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'business',
                    'field' => 'id',
                    'terms' => $terms,
                    'operator' => 'IN',
                ),
            ),
            'order' => 'ASC',
        );

        $reviews_query = new \WP_Query($args);
        $total_posts = 0;
        $all_reviews = array();
        $job_id = '';
        $review_type = 'All Reviews';

        if ($reviews_query->have_posts()) {
            while ($reviews_query->have_posts()) {
                $reviews_query->the_post();
                $review_id = get_the_ID();
                $rating = get_post_meta($review_id, 'rating', true);
                $job_id = get_post_meta($review_id, 'place_id', true); // Updated from job_id to place_id
                
                // Get the review text - updated meta keys
                $original_text = get_post_meta($review_id, 'original_text', true);
                $custom_text = get_post_meta($review_id, 'custom_text', true);
                
                // Skip this review if both text fields are empty
                if (empty($original_text) && empty($custom_text)) {
                    continue;
                }
                
                // Use custom_text if available, otherwise use original_text
                $final_text = !empty($custom_text) ? $custom_text : $original_text;
                
                // Updated meta keys based on the plugin's current structure
                $author_name = get_post_meta($review_id, 'author_name', true);
                $profile_photo_url = get_post_meta($review_id, 'profile_photo_url', true);
                $review_url = get_post_meta($review_id, 'review_url', true);
                $published_date = get_post_meta($review_id, 'published_date', true);

                $review_data = array(
                    'reviewer_name' => $author_name,
                    'reviewer_picture_url' => $profile_photo_url,
                    'url' => $review_url,
                    'text' => $final_text,
                    'publish_date' => $published_date,
                );

                if ($five_star_only) {
                    if ($rating == 5) {
                        $review_type = '5 Star Reviews only';
                        $all_reviews[] = $review_data;
                    }
                } else {
                    $all_reviews[] = $review_data;
                }
            }
            $total_posts = count($all_reviews);
            wp_reset_postdata();
        }

        return array(
            'reviews_type' => $review_type,
            'total_posts' => $total_posts,
            'job_id' => $job_id,
            'all_reviews' => $all_reviews,
        );
    }
}

// Function wrapper for backward compatibility
if (!function_exists('get_all_reviews_by_term')) {
    /**
     * Get all reviews by business term
     * 
     * @param bool $five_star_only Whether to return only 5-star reviews
     * @return array Array of reviews data
     */
    function get_all_reviews_by_term($five_star_only = false) {
        return DisplayReviews::get_all_reviews_by_term($five_star_only);
    }
}