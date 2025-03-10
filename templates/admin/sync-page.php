<?php
if (!defined('ABSPATH')) {
    exit;
}

$businesses = get_terms([
    'taxonomy' => 'business',
    'hide_empty' => false,
]);

// Check if a specific business ID is in the URL parameter
$selected_business_id = isset($_GET['business']) ? intval($_GET['business']) : 0;
?>
<div class="wrap beardog-reviews-wrap">
    <div class="sync-page-container">
        <?php if (empty($businesses)) : ?>
            <div class="notice notice-warning">
                <p><?php _e('No businesses found. Please add a business first under Settings.', 'beardog-reviews'); ?></p>
            </div>
        <?php else : ?>
            <div class="sync-dashboard">
                <div class="sync-header">
                    <h1><?php _e('Review Sync Dashboard', 'beardog-reviews'); ?></h1>
                    <p class="sync-description"><?php _e('Keep your Google reviews up to date by syncing them with your business listing.', 'beardog-reviews'); ?></p>
                </div>
                
                <div class="sync-content-container">
                    <div class="sync-card manual-sync-card">
                        <div class="sync-loader" style="display: none;">
                            <div class="custom-spinner"></div>
                            <p class="sync-status-message"><?php _e('Syncing reviews...', 'beardog-reviews'); ?></p>
                        </div>
                        
                        <div class="sync-card-content">
                            <div class="sync-card-header">
                                <h2><span class="dashicons dashicons-update"></span> <?php _e('Manual Sync', 'beardog-reviews'); ?></h2>
                                <p><?php _e('Select a business below to manually sync its Google reviews', 'beardog-reviews'); ?></p>
                            </div>
                            
                            <div class="sync-card-body">
                                <form method="post" action="" id="sync-form">
                                    <?php wp_nonce_field('agr_sync_reviews'); ?>
                                    
                                    <div class="form-field">
                                        <label for="business_id"><?php _e('Business', 'beardog-reviews'); ?></label>
                                        <div class="select-wrapper">
                                            <select name="business_id" id="business_id" required>
                                                <option value=""><?php _e('-- Select Business --', 'beardog-reviews'); ?></option>
                                                <?php foreach ($businesses as $business) : ?>
                                                    <option value="<?php echo esc_attr($business->term_id); ?>" <?php selected($selected_business_id, $business->term_id); ?>>
                                                        <?php echo esc_html($business->name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" name="agr_sync_reviews" id="sync-button" class="button button-primary">
                                            <span class="dashicons dashicons-update"></span>
                                            <?php _e('Sync Now', 'beardog-reviews'); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="confetti-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;"></div>
        <?php endif; ?>
    </div>
</div>

<!-- Load confetti library -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

<style>
    .beardog-reviews-wrap {
        max-width: 100%;
        margin: 20px 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    
    .sync-page-container {
        min-height: calc(100vh - 120px);
        padding: 20px;
        background-color: #f9f9f9;
    }
    
    .sync-dashboard {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .sync-header {
        margin-bottom: 30px;
        text-align: center;
    }
    
    .sync-header h1 {
        color: #23282d;
        font-size: 2.2em;
        margin-bottom: 10px;
    }
    
    .sync-description {
        font-size: 16px;
        color: #50575e;
    }
    
    .sync-content-container {
        display: flex;
        justify-content: center;
    }
    
    .sync-card {
        background-color: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        min-height: 300px;
        width: 100%;
        max-width: 600px;
        overflow: visible;
        position: relative;
        transition: transform 0.3s, box-shadow 0.3s;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .sync-card:hover {
        box-shadow: 0 12px 32px rgba(34, 113, 177, 0.15);
        transform: translateY(-5px);
    }
    
    .sync-card-header {
        padding: 30px 30px 20px;
        border-bottom: 1px solid #eee;
        background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
        border-radius: 16px 16px 0 0;
        color: #fff;
    }
    
    .sync-card-header h2 {
        font-size: 1.5em;
        color: #fff;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
    }
    
    .sync-card-header h2 .dashicons {
        margin-right: 10px;
        color: #fff;
    }
    
    .sync-card-header p {
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }
    
    .sync-card-body {
        padding: 30px;
    }
    
    .form-field {
        margin-bottom: 25px;
    }
    
    .form-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 10px;
        color: #1d2327;
    }
    
    .select-wrapper {
        position: relative;
    }
    
    .select-wrapper:after {
        display: none; /* Hide the dropdown arrow */
    }
    
    select {
        width: 100%;
        height: 45px;
        padding: 10px 15px;
        font-size: 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        appearance: none;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.07);
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    
    select:focus {
        border-color: #2271b1;
        box-shadow: 0 0 0 1px #2271b1;
        outline: 2px solid transparent;
    }
    
    .sync-help-text {
        margin: 20px 0;
        padding: 15px;
        border-radius: 8px;
        background-color: #f0f6fc;
        border-left: 4px solid #2271b1;
    }
    
    .sync-help-text p {
        margin: 0;
        color: #646970;
        font-size: 14px;
    }
    
    .form-actions {
        margin-top: 30px;
    }
    
    #sync-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 15px 20px;
        height: auto;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
        background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
        border: none;
        color: #fff;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(34, 113, 177, 0.2);
        position: relative;
        overflow: hidden;
    }
    
    #sync-button:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: all 0.6s;
    }
    
    #sync-button:hover {
        background: linear-gradient(135deg, #135e96 0%, #0a4b7a 100%);
        box-shadow: 0 6px 15px rgba(19, 94, 150, 0.3);
        transform: translateY(-2px);
    }
    
    #sync-button:hover:before {
        left: 100%;
    }
    
    #sync-button .dashicons {
        font-size: 18px;
        width: 18px;
        height: 18px;
        margin-right: 10px;
        display: inline-block;
        vertical-align: middle;
        animation: spin 8s linear infinite;
        animation-play-state: paused;
    }
    
    #sync-button:hover .dashicons {
        animation-play-state: running;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .sync-loader {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.95);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 10;
        border-radius: 16px;
    }
    
    .custom-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #2271b1;
        border-radius: 50%;
        margin: 0 auto 25px;
        animation: spin 1s linear infinite;
    }
    
    .sync-status-message {
        font-size: 18px;
        font-weight: 500;
        text-align: center;
        color: #2271b1;
    }
    
    @media screen and (max-width: 782px) {
        .sync-card {
            max-width: 100%;
        }
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Check if business is pre-selected
    if (parseInt($('#business_id').val()) > 0) {
        // Focus on the sync button to make it easy to submit
        $('#sync-button').focus();
        
        // Scroll to the form if needed
        $('html, body').animate({
            scrollTop: $('#sync-form').offset().top - 100
        }, 500);
    }
    
    $('#sync-form').on('submit', function(e) {
        e.preventDefault();
        
        $('.sync-card-content').css('opacity', '0.5');
        $('.sync-loader').show();
        
        var businessId = $('#business_id').val();
        var nonce = $('[name="_wpnonce"]').val();
        
        // AJAX request to sync reviews
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'agr_ajax_sync_reviews',
                business_id: businessId,
                _wpnonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.sync-status-message').text('Sync completed successfully!');
                    
                    // Trigger confetti animation
                    var duration = 3000;
                    var animationEnd = Date.now() + duration;
                    var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 9999 };

                    function randomInRange(min, max) {
                        return Math.random() * (max - min) + min;
                    }

                    var interval = setInterval(function() {
                        var timeLeft = animationEnd - Date.now();

                        if (timeLeft <= 0) {
                            return clearInterval(interval);
                        }

                        var particleCount = 50 * (timeLeft / duration);
                        
                        confetti(Object.assign({}, defaults, { 
                            particleCount,
                            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
                        }));
                        confetti(Object.assign({}, defaults, { 
                            particleCount,
                            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
                        }));
                    }, 250);
                    
                    // Show form again after animation
                    setTimeout(function() {
                        $('.sync-loader').hide();
                        $('.sync-card-content').css('opacity', '1');
                    }, 3500);
                } else {
                    $('.sync-status-message').text('Error: ' + (response.data || 'Unknown error'));
                    
                    // Show form again
                    setTimeout(function() {
                        $('.sync-loader').hide();
                        $('.sync-card-content').css('opacity', '1');
                    }, 2000);
                }
            },
            error: function() {
                $('.sync-status-message').text('An error occurred. Please try again.');
                
                // Show form again
                setTimeout(function() {
                    $('.sync-loader').hide();
                    $('.sync-card-content').css('opacity', '1');
                }, 2000);
            }
        });
    });
});
</script> 