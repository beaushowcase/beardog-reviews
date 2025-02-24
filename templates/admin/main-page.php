<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get all businesses
$businesses = get_terms([
    'taxonomy' => 'business',
    'hide_empty' => false
]);
?>
<div class="wrap">
    <h1><?php _e('Review Settings', 'awesome-google-review'); ?></h1>
    
    <div class="notice notice-info">
        <p>
            <?php _e('Welcome to Awesome Google Reviews! To get started:', 'awesome-google-review'); ?>
        </p>
        <ol>
            <li><?php _e('Add your business locations using the form below', 'awesome-google-review'); ?></li>
            <li><?php _e('For each business, enter its Google Place ID', 'awesome-google-review'); ?></li>
            <li><?php _e('Use the Sync Now button to manually sync reviews', 'awesome-google-review'); ?></li>
        </ol>
    </div>

    <div class="card">
        <h2><?php _e('Add New Business', 'awesome-google-review'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('agr_add_business'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="business_name"><?php _e('Business Name', 'awesome-google-review'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="business_name" id="business_name" class="regular-text" required>
                        <p class="description"><?php _e('Enter your business name as you want it to appear', 'awesome-google-review'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="place_id"><?php _e('Google Place ID', 'awesome-google-review'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="place_id" id="place_id" class="regular-text" required>
                        <p class="description"><?php _e('Enter the Google Place ID for this business location', 'awesome-google-review'); ?></p>
                        <div class="place-id-help">
                            <p><strong><?php _e('To find your Google Place ID:', 'awesome-google-review'); ?></strong></p>
                            <ol>
                                <li><?php _e('Visit the ', 'awesome-google-review'); ?><a href="https://developers.google.com/places/place-id" target="_blank"><?php _e('Place ID Finder', 'awesome-google-review'); ?></a></li>
                                <li><?php _e('Enter your business name or address', 'awesome-google-review'); ?></li>
                                <li><?php _e('Click on your business in the results', 'awesome-google-review'); ?></li>
                                <li><?php _e('Copy the Place ID shown', 'awesome-google-review'); ?></li>
                            </ol>
                        </div>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="agr_add_business" class="button-primary" value="<?php _e('Add Business', 'awesome-google-review'); ?>">
            </p>
        </form>
    </div>

    <div class="card">
        <h2><?php _e('Existing Businesses', 'awesome-google-review'); ?></h2>
        <?php if (empty($businesses)) : ?>
            <p><?php _e('No businesses added yet.', 'awesome-google-review'); ?></p>
        <?php else : ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Business', 'awesome-google-review'); ?></th>
                        <th><?php _e('Place ID', 'awesome-google-review'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($businesses as $business) :
                        $business_data = $this->db->get_business($business->term_id);
                        if (!$business_data) continue;
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($business->name); ?></strong>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo esc_url(get_edit_term_link($business->term_id, 'business')); ?>"><?php _e('Edit', 'awesome-google-review'); ?></a> |
                                    </span>
                                    <span class="sync">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=agr-sync-reviews&business=' . $business->term_id)); ?>"><?php _e('Sync Now', 'awesome-google-review'); ?></a>
                                    </span>
                                </div>
                            </td>
                            <td><?php echo esc_html($business_data->place_id); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div> 