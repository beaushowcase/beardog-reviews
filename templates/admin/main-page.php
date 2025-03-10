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
<div class="wrap beardog-reviews-wrap">
    <div class="reviews-settings-container">
        <div class="settings-header">
            <h1><?php _e('Google Reviews Dashboard', 'beardog-reviews'); ?></h1>
            <p class="settings-description"><?php _e('Manage your business locations and Google reviews in one place', 'beardog-reviews'); ?></p>
        </div>
        
        <div class="settings-content">
            <div class="settings-column settings-main">
                <div class="settings-card businesses-card">
                    <div class="settings-card-header">
                        <h2><span class="dashicons dashicons-store"></span> <?php _e('Business Locations', 'beardog-reviews'); ?></h2>
                    </div>
                    
                    <div class="settings-card-body">
                        <?php if (empty($businesses)) : ?>
                            <div class="no-businesses">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <span class="dashicons dashicons-location"></span>
                                    </div>
                                    <h3><?php _e('No Businesses Added', 'beardog-reviews'); ?></h3>
                                    <p><?php _e('Add your first business location to start syncing Google reviews.', 'beardog-reviews'); ?></p>
                                    <button type="button" class="button button-primary add-business-toggle">
                                        <span class="dashicons dashicons-plus"></span>
                                        <?php _e('Add Business', 'beardog-reviews'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="businesses-table-wrapper">
                                <table class="businesses-table">
                                    <thead>
                                        <tr>
                                            <th class="column-name"><?php _e('Business', 'beardog-reviews'); ?></th>
                                            <th class="column-place-id"><?php _e('Place ID', 'beardog-reviews'); ?></th>
                                            <th class="column-actions"><?php _e('Actions', 'beardog-reviews'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($businesses as $business) :
                                            $business_data = $this->db->get_business($business->term_id);
                                            if (!$business_data) continue;
                                        ?>
                                            <tr>
                                                <td class="column-name">
                                                    <strong><?php echo esc_html($business->name); ?></strong>
                                                </td>
                                                <td class="column-place-id">
                                                    <code><?php echo esc_html($business_data->place_id); ?></code>
                                                </td>
                                                <td class="column-actions">
                                                    <div class="action-buttons">
                                                        <a href="<?php echo esc_url(get_edit_term_link($business->term_id, 'business')); ?>" class="button action-button edit-button" title="<?php _e('Edit', 'beardog-reviews'); ?>">
                                                            <span class="dashicons dashicons-edit"></span>
                                                        </a>
                                                        <a href="<?php echo esc_url(admin_url('admin.php?page=agr-sync-reviews&business=' . $business->term_id)); ?>" class="button action-button sync-button" title="<?php _e('Sync Now', 'beardog-reviews'); ?>">
                                                            <span class="dashicons dashicons-update"></span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="settings-column settings-sidebar">
                <div class="settings-card add-business-card">
                    <div class="settings-card-header">
                        <h2><span class="dashicons dashicons-plus"></span> <?php _e('Add New Business', 'beardog-reviews'); ?></h2>
                    </div>
                    
                    <div class="settings-card-body">
                        <form method="post" action="" id="add-business-form">
                            <?php wp_nonce_field('agr_add_business'); ?>
                            
                            <div class="form-field">
                                <label for="business_name"><?php _e('Business Name', 'beardog-reviews'); ?></label>
                                <input type="text" name="business_name" id="business_name" required>
                                <p class="field-description"><?php _e('Enter your business name as you want it to appear', 'beardog-reviews'); ?></p>
                            </div>
                            
                            <div class="form-field">
                                <label for="place_id"><?php _e('Google Place ID', 'beardog-reviews'); ?></label>
                                <input type="text" name="place_id" id="place_id" required>
                                <p class="field-description"><?php _e('Enter the Google Place ID for this business location', 'beardog-reviews'); ?></p>
                            </div>
                            
                            <div class="finder-help-box">
                                <p><strong><?php _e('To find your Google Place ID:', 'beardog-reviews'); ?></strong></p>
                                <ol>
                                    <li><?php _e('Visit the ', 'beardog-reviews'); ?><a href="https://developers.google.com/places/place-id" target="_blank"><?php _e('Place ID Finder', 'beardog-reviews'); ?></a></li>
                                    <li><?php _e('Enter your business name or address', 'beardog-reviews'); ?></li>
                                    <li><?php _e('Select your business from the results', 'beardog-reviews'); ?></li>
                                    <li><?php _e('Copy the Place ID shown', 'beardog-reviews'); ?></li>
                                </ol>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="agr_add_business" class="button button-primary">
                                    <span class="dashicons dashicons-plus"></span>
                                    <?php _e('Add Business', 'beardog-reviews'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Main layout and components */
    .beardog-reviews-wrap {
        max-width: 100%;
        margin: 20px 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    
    .reviews-settings-container {
        background-color: #f9f9f9;
        min-height: calc(100vh - 120px);
        padding: 20px;
    }
    
    /* Header styles */
    .settings-header {
        margin-bottom: 30px;
        text-align: center;
    }
    
    .settings-header h1 {
        color: #23282d;
        font-size: 2.2em;
        margin-bottom: 10px;
    }
    
    .settings-description {
        font-size: 16px;
        color: #50575e;
    }
    
    /* Layout columns */
    .settings-content {
        display: flex;
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .settings-column {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    .settings-main {
        flex: 1;
    }
    
    .settings-sidebar {
        flex: 1;
    }
    
    /* Card styles */
    .settings-card {
        background-color: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .settings-card:hover {
        box-shadow: 0 12px 32px rgba(34, 113, 177, 0.15);
        transform: translateY(-5px);
    }
    
    .settings-card-header {
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
        color: #fff;
    }
    
    .settings-card-header h2 {
        font-size: 1.3em;
        margin: 0;
        display: flex;
        align-items: center;
        color: #fff;
    }
    
    .settings-card-header h2 .dashicons {
        margin-right: 10px;
        color: #fff;
    }
    
    .settings-card-body {
        padding: 25px;
    }
    
    /* Business table styles */
    .businesses-table-wrapper {
        overflow-x: auto;
    }
    
    .businesses-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .businesses-table th,
    .businesses-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .businesses-table th {
        font-weight: 600;
        color: #23282d;
        background-color: #f8f9fa;
    }
    
    .businesses-table tbody tr:hover {
        background-color: #f9f9f9;
    }
    
    .column-place-id code {
        padding: 4px 8px;
        background-color: #f0f0f1;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .column-actions {
        text-align: right;
    }
    
    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
    }
    
    .action-button {
        width: 36px;
        height: 36px;
        padding: 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        background: #f6f7f7;
        border: 1px solid #dcdcde;
        box-shadow: none;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .action-button:hover {
        background: #f0f0f1;
        border-color: #c5c5c7;
    }
    
    .action-button .dashicons {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 18px;
        width: 18px;
        height: 18px;
        margin: 0;
        padding: 0;
    }
    
    .edit-button {
        color: #2271b1;
    }
    
    .edit-button:hover {
        color: #135e96;
    }
    
    .sync-button {
        color: #008a20;
    }
    
    .sync-button:hover {
        color: #006a18;
    }
    
    /* Form styles */
    .form-field {
        margin-bottom: 20px;
    }
    
    .form-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 10px;
        color: #1d2327;
    }
    
    .form-field input[type="text"] {
        width: 100%;
        padding: 10px 15px;
        font-size: 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.07);
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    
    .form-field input[type="text"]:focus {
        border-color: #2271b1;
        box-shadow: 0 0 0 1px #2271b1;
        outline: 2px solid transparent;
    }
    
    .field-description {
        margin: 5px 0 0;
        color: #646970;
        font-size: 13px;
    }
    
    .finder-help-box {
        margin: 20px 0;
        padding: 15px;
        background-color: #f0f6fc;
        border-left: 4px solid #2271b1;
        border-radius: 8px;
    }
    
    .finder-help-box p {
        margin-top: 0;
    }
    
    .finder-help-box ol {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    .finder-help-box li {
        margin-bottom: 5px;
    }
    
    .finder-help-box a {
        color: #2271b1;
        text-decoration: none;
    }
    
    .finder-help-box a:hover {
        text-decoration: underline;
    }
    
    .form-actions {
        margin-top: 25px;
    }
    
    /* Button styles */
    .button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: auto;
        padding: 8px 14px;
        font-size: 13px;
        line-height: 1.5;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        background: #f6f7f7;
        border: 1px solid #dcdcde;
        color: #2c3338;
    }
    
    .button:hover {
        background: #f0f0f1;
        border-color: #c5c5c7;
    }
    
    .button-primary {
        background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
        border: none;
        color: #fff;
        padding: 12px 20px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(34, 113, 177, 0.2);
        position: relative;
        overflow: hidden;
        width: 100%;
    }
    
    .button-primary:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: all 0.6s;
    }
    
    .button-primary:hover {
        background: linear-gradient(135deg, #135e96 0%, #0a4b7a 100%);
        box-shadow: 0 6px 15px rgba(19, 94, 150, 0.3);
        transform: translateY(-2px);
    }
    
    .button-primary:hover:before {
        left: 100%;
    }
    
    .button .dashicons {
        font-size: 16px;
        width: 16px;
        height: 16px;
        margin-right: 8px;
        display: inline-block;
        vertical-align: middle;
    }
    
    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    
    .empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background-color: #f0f6fc;
        border-radius: 50%;
    }
    
    .empty-icon .dashicons {
        font-size: 40px;
        width: 40px;
        height: 40px;
        color: #2271b1;
    }
    
    .empty-state h3 {
        font-size: 18px;
        margin: 0 0 10px;
    }
    
    .empty-state p {
        margin: 0 0 20px;
        color: #646970;
    }
    
    /* Responsive styles */
    @media screen and (max-width: 782px) {
        .settings-content {
            flex-direction: column;
        }
        
        .column-status, .column-place-id {
            display: none;
        }
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle add business form on mobile
    $('.add-business-toggle').on('click', function() {
        $('.add-business-card').fadeIn();
        
        // Scroll to form on mobile
        if ($(window).width() < 783) {
            $('html, body').animate({
                scrollTop: $('.add-business-card').offset().top - 50
            }, 300);
        }
    });
    
    // Initially hide the add business card on mobile if there are businesses
    if ($(window).width() < 783 && $('.businesses-table tbody tr').length > 0) {
        $('.add-business-card').hide();
    }
});
</script> 