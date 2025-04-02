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
        // No initialization needed
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

        // Add meta query for 5-star only reviews if requested
        if ($five_star_only) {
            $args['meta_query'] = array(
                array(
                    'key' => 'rating',
                    'value' => 5,
                    'compare' => '=',
                    'type' => 'NUMERIC'
                )
            );
        } else {
            $args['meta_query'] = array();
        }
        
        // Exclude disabled reviews
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key' => '_review_disabled',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => '_review_disabled',
                'value' => '1',
                'compare' => '!='
            )
        );

        $reviews_query = new \WP_Query($args);
        $total_posts = 0;
        $all_reviews = array();
        $job_id = '';
        $review_type = 'All Reviews';

        if ($reviews_query->have_posts()) {
            $total_posts = $reviews_query->found_posts;
            
            if ($five_star_only) {
                $review_type = '5 Star Reviews';
            }

            while ($reviews_query->have_posts()) {
                $reviews_query->the_post();
                $post_id = get_the_ID();
                
                // Get all post meta
                $post_meta = get_post_meta($post_id);
                
                // Process review data
                $review = array(
                    'id' => $post_id,
                    'author_name' => isset($post_meta['author_name'][0]) ? $post_meta['author_name'][0] : '',
                    'author_url' => isset($post_meta['author_url'][0]) ? $post_meta['author_url'][0] : '',
                    'language' => isset($post_meta['language'][0]) ? $post_meta['language'][0] : '',
                    'profile_photo_url' => isset($post_meta['profile_photo_url'][0]) ? $post_meta['profile_photo_url'][0] : '',
                    'rating' => isset($post_meta['rating'][0]) ? intval($post_meta['rating'][0]) : 0,
                    'relative_time_description' => isset($post_meta['relative_time_description'][0]) ? $post_meta['relative_time_description'][0] : '',
                    'text' => isset($post_meta['text'][0]) ? $post_meta['text'][0] : '',
                    'time' => isset($post_meta['time'][0]) ? intval($post_meta['time'][0]) : 0,
                    'published_date' => isset($post_meta['published_date'][0]) ? $post_meta['published_date'][0] : '',
                    'translated' => isset($post_meta['translated'][0]) ? $post_meta['translated'][0] === 'true' : false,
                    'custom_review_text' => !empty($post_meta['custom_text'][0]) ? $post_meta['custom_text'][0] : $post_meta['original_text'][0],
                ); 
                // Get business info
                $business_terms = wp_get_post_terms($post_id, 'business');
                if (!empty($business_terms) && !is_wp_error($business_terms)) {
                    $review['business_name'] = $business_terms[0]->name;
                    $review['business_id'] = $business_terms[0]->term_id;
                }
                
                $all_reviews[] = $review;               
            }
            
            wp_reset_postdata();
        }

        return array(
            'reviews_type' => $review_type,
            'total_posts' => $total_posts,
            'job_id' => $job_id,
            'all_reviews' => $all_reviews,
        );
    }

    /**
     * Process and display Google reviews in a consistent format
     * Returns author information, review date, and review text
     *
     * @param bool $five_star_only Optional. Whether to display only 5-star reviews. Default true.
     * @return array Processed reviews with only essential display fields
     */
    public static function process_reviews_for_display($five_star_only = true) {
        // Get all reviews using the existing function
        $google_reviews = self::get_all_reviews_by_term($five_star_only);        

        $processed_reviews = array();
        
        if (!empty($google_reviews['all_reviews'])) {
            foreach ($google_reviews['all_reviews'] as $review) {
                // Skip disabled reviews (additional safety check)
                $is_disabled = get_post_meta($review['id'], '_review_disabled', true);
                if ($is_disabled) {
                    continue;
                }
                
                // Get author information
                $author_name = isset($review['author_name']) ? $review['author_name'] : '';
                $author_initial = !empty($author_name) ? mb_substr($author_name, 0, 1) : '';
                
                // Get initials of each word in author name (e.g., "John Smith" -> "JS")
                // $author_initial_two = '';
                // if (!empty($author_name)) {
                //     $name_parts = explode(' ', $author_name);
                //     foreach ($name_parts as $part) {
                //         if (!empty($part)) {
                //             $author_initial_two .= mb_substr($part, 0, 1);
                //         }
                //     }
                // }
                
                $author_initial_two = '';
                if (!empty($author_name)) {
                    $name_parts = explode(' ', $author_name);
                    $count = 0;
                    foreach ($name_parts as $part) {
                        if (!empty($part) && $count < 2) {
                            $author_initial_two .= mb_substr($part, 0, 1);
                            $count++;
                        }
                    }
                }

                
                // Get review date in human-readable format
                $review_date = '';
                
                // Try to get the review date from different meta fields
                $published_date = get_post_meta($review['id'], 'published_date', true);
                if (empty($published_date)) {
                    $published_date = get_post_meta($review['id'], 'time', true);
                }
                
                if (!empty($published_date)) {
                    // If it's a timestamp (numeric), convert it
                    if (is_numeric($published_date)) {
                        $date = new \DateTime();
                        $date->setTimestamp($published_date);
                    } else {
                        // Try to parse the date string
                        $date = \DateTime::createFromFormat('F j, Y g:i a', $published_date);
                        if (!$date) {
                            // Try alternative format
                            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $published_date);
                        }
                    }
                    
                    if ($date) {
                        $now = new \DateTime();
                        $diff = $now->diff($date);
                        
                        if ($diff->y > 0) {
                            $review_date = $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->m > 0) {
                            $review_date = $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->d > 6) {
                            $weeks = floor($diff->d / 7);
                            $review_date = $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->d > 0) {
                            $review_date = $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->h > 0) {
                            $review_date = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->i > 0) {
                            $review_date = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                        } else {
                            $review_date = 'just now';
                        }
                    } else {
                        $review_date = 'Unknown date';
                    }
                } else {
                    // If no date found in meta, try to get from post date
                    $post = get_post($review['id']);
                    if ($post) {
                        $date = new \DateTime($post->post_date);
                        $now = new \DateTime();
                        $diff = $now->diff($date);
                        
                        if ($diff->y > 0) {
                            $review_date = $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->m > 0) {
                            $review_date = $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->d > 6) {
                            $weeks = floor($diff->d / 7);
                            $review_date = $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->d > 0) {
                            $review_date = $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->h > 0) {
                            $review_date = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                        } elseif ($diff->i > 0) {
                            $review_date = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                        } else {
                            $review_date = 'just now';
                        }
                    } else {
                        $review_date = 'Unknown date';
                    }
                }               
               
                
                // Get business info
                $business_terms = wp_get_post_terms($review['id'], 'business');
                $business_name = '';
                if (!empty($business_terms) && !is_wp_error($business_terms)) {
                    $business_name = $business_terms[0]->name;
                }
                
                // Add fields to the processed reviews array
                $processed_reviews[] = array(
                    'author_initial' => $author_initial,
                    'author_initial_two' => $author_initial_two,
                    'author_full_name' => $author_name,
                    'review_date' => $review_date,
                    'review_text' => $review['custom_review_text'],
                    'business_term' => $business_name
                );
            }
        }
        
        return array(
            'reviews_type' => $google_reviews['reviews_type'],
            'total_count' => count($processed_reviews),
            'reviews' => $processed_reviews
        );
    }

    /**
     * Convert datetime to human-readable time difference (e.g., "2 days ago")
     *
     * @param string|int $datetime DateTime string or timestamp
     * @param bool $full Whether to show full date parts
     * @return string Human-readable time difference
     */
    private static function time_elapsed_string($datetime) {
        if (!is_numeric($datetime)) {
            $datetime = strtotime($datetime);
        }
        
        $now = new \DateTime();
        $ago = new \DateTime();
        $ago->setTimestamp($datetime);
        
        $diff = $now->diff($ago);
        
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
        
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        
        foreach ($string as $key => &$value) {
            if ($diff->$key) {
                $value = $diff->$key . ' ' . $value . ($diff->$key > 1 ? 's' : '');
            } else {
                unset($string[$key]);
            }
        }
        
        // Get only the most significant time unit
        $string = array_slice($string, 0, 1);
        
        return $string ? implode(', ', $string) . ' ago' : 'just now';
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