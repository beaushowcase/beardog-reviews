<?php
namespace BeardogReviews\Admin;

class ReviewMetaBox {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_review_meta_box']);
        add_action('save_post_agr_google_review', [$this, 'save_review_meta']);
    }

    public function add_review_meta_box() {
        add_meta_box(
            'agr_review_details',
            __('Google Review Details', 'beardog-reviews'),
            [$this, 'render_review_meta_box'],
            'agr_google_review',
            'normal',
            'high'
        );
    }

    public function render_review_meta_box($post) {
        // Get the review meta data
        $original_text = get_post_meta($post->ID, 'original_text', true);
        $custom_text = get_post_meta($post->ID, 'custom_text', true);
        $rating = get_post_meta($post->ID, 'rating', true);
        $author_name = get_post_meta($post->ID, 'author_name', true);
        $published_date = get_post_meta($post->ID, 'published_date', true);
        $place_id = get_post_meta($post->ID, 'place_id', true);
        $review_id = get_post_meta($post->ID, 'review_id', true);
        $business_name = get_post_meta($post->ID, 'business_name', true);
        $business_address = get_post_meta($post->ID, 'business_address', true);
        $business_phone = get_post_meta($post->ID, 'business_phone', true);

        // Generate review URL
        $reviewer_url = '';
        if ($place_id && $review_id) {
            $reviewer_url = sprintf(
                'https://search.google.com/local/reviews?placeid=%s#review=%s',
                $place_id,
                $review_id
            );
        }

        // Add nonce for security
        wp_nonce_field('agr_review_meta_box', 'agr_review_meta_box_nonce');
        ?>
        <div class="agr-review-meta">
            <div class="agr-review-section">
                <h3><?php _e('Review Information', 'beardog-reviews'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><label><?php _e('Rating', 'beardog-reviews'); ?></label></th>
                        <td><input type="text" value="<?php echo esc_attr($rating); ?>" readonly class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Author', 'beardog-reviews'); ?></label></th>
                        <td><input type="text" value="<?php echo esc_attr($author_name); ?>" readonly class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Review Date', 'beardog-reviews'); ?></label></th>
                        <td><input type="text" value="<?php echo esc_attr($published_date ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($published_date)) : ''); ?>" readonly class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Review Link', 'beardog-reviews'); ?></label></th>
                        <td>
                            <?php if ($reviewer_url): ?>
                            <a href="<?php echo esc_url($reviewer_url); ?>" target="_blank" class="button">
                                <span class="dashicons dashicons-external" style="vertical-align:middle;"></span>
                                <?php _e('View Review', 'beardog-reviews'); ?>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="agr-review-section">
                <h3><?php _e('Business Information', 'beardog-reviews'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><label><?php _e('Business Name', 'beardog-reviews'); ?></label></th>
                        <td><input type="text" value="<?php echo esc_attr($business_name); ?>" readonly class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Address', 'beardog-reviews'); ?></label></th>
                        <td><input type="text" value="<?php echo esc_attr($business_address); ?>" readonly class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Phone', 'beardog-reviews'); ?></label></th>
                        <td><input type="text" value="<?php echo esc_attr($business_phone); ?>" readonly class="regular-text" /></td>
                    </tr>
                </table>
            </div>

            <div class="agr-review-section">
                <h3><?php _e('Review Text', 'beardog-reviews'); ?></h3>
                <div class="agr-review-text">
                    <label>
                        <strong><?php _e('Original Review Text (Read-only)', 'beardog-reviews'); ?></strong>
                        <textarea readonly rows="5" class="large-text"><?php echo esc_textarea($original_text); ?></textarea>
                    </label>
                </div>
                <div class="agr-review-text">
                    <label>
                        <strong><?php _e('Custom Review Text', 'beardog-reviews'); ?></strong>
                        <textarea name="agr_custom_text" rows="5" class="large-text"><?php echo esc_textarea($custom_text); ?></textarea>
                    </label>
                    <p class="description"><?php _e('Use this field to customize the review text that will be displayed on your website.', 'beardog-reviews'); ?></p>
                </div>
            </div>
        </div>
        <style>
            .agr-review-section {
                margin-bottom: 20px;
                padding: 15px;
                background: #fff;
                border: 1px solid #ccd0d4;
            }
            .agr-review-section h3 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }
            .agr-review-text {
                margin-bottom: 15px;
            }
            .agr-review-text label {
                display: block;
                margin-bottom: 5px;
            }
            .agr-review-text textarea {
                width: 100%;
                margin-top: 5px;
            }
        </style>
        <?php
    }

    public function save_review_meta($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['agr_review_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['agr_review_meta_box_nonce'], 'agr_review_meta_box')) {
            return;
        }

        // If this is an autosave, don't update the meta
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Update the custom text
        if (isset($_POST['agr_custom_text'])) {
            update_post_meta($post_id, 'custom_text', sanitize_textarea_field($_POST['agr_custom_text']));
        }
    }
} 