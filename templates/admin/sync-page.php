<?php
if (!defined('ABSPATH')) {
    exit;
}

$businesses = get_terms([
    'taxonomy' => 'business',
    'hide_empty' => false,
]);
?>
<div class="wrap">
    <div class="sync-page-container">
        <?php if (empty($businesses)) : ?>
            <div class="notice notice-warning">
                <p><?php _e('No businesses found. Please add a business first under Settings.', 'beardog-reviews'); ?></p>
            </div>
        <?php else : ?>
            <div class="sync-loader" style="display: none;">
                <div class="spinner"></div>
                <p><?php _e('Syncing reviews...', 'beardog-reviews'); ?></p>
            </div>

            <div class="card manual-sync-card">
                <h2><?php _e('Manual Sync', 'beardog-reviews'); ?></h2>
                <p><?php _e('Select a business to sync its reviews manually:', 'beardog-reviews'); ?></p>

                <form method="post" action="" id="sync-form">
                    <?php wp_nonce_field('agr_sync_reviews'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="business_id"><?php _e('Business', 'beardog-reviews'); ?></label>
                            </th>
                            <td>
                                <select name="business_id" id="business_id" required>
                                    <option value=""><?php _e('-- Select Business --', 'beardog-reviews'); ?></option>
                                    <?php foreach ($businesses as $business) : ?>
                                        <option value="<?php echo esc_attr($business->term_id); ?>">
                                            <?php echo esc_html($business->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" name="agr_sync_reviews" class="button-primary" value="<?php _e('Sync Now', 'beardog-reviews'); ?>">
                    </p>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#sync-form').on('submit', function() {
        $('.sync-form').hide();
        $('.sync-loader').show();
    });
});
</script> 