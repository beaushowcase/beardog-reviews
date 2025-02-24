<?php
namespace BeardogReviews;

class Admin {
    private $db;
    private $reviews;

    public function __construct() {
        $this->db = new Database();
        $this->reviews = new Reviews();

        // Add menu pages
        add_action('admin_menu', [$this, 'add_menu_pages']);
        
        // Add business fields
        add_action('business_add_form_fields', [$this, 'add_business_fields']);
        add_action('business_edit_form_fields', [$this, 'edit_business_fields'], 10, 2);
        
        // Save business fields
        add_action('created_business', [$this, 'save_business_fields']);
        add_action('edited_business', [$this, 'save_business_fields']);
        
        // Handle form submissions
        add_action('admin_init', [$this, 'handle_add_business']);
        add_action('admin_init', [$this, 'handle_manual_sync']);
        add_action('admin_init', [$this, 'handle_sync_settings']);
        
        // Add admin notices
        add_action('admin_notices', [$this, 'show_admin_notices']);
        
        // Add admin styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    }

    public function enqueue_admin_styles($hook) {
        if (strpos($hook, 'beardog-reviews') !== false) {
            wp_enqueue_style('agr-admin', AGR_PLUGIN_URL . 'assets/css/admin.css', [], AGR_VERSION);
        }
    }

    public function add_menu_pages() {
        // Main menu
        add_menu_page(
            __('Review Settings', 'beardog-reviews'),
            __('Review Settings', 'beardog-reviews'),
            'manage_options',
            'beardog-reviews',
            [$this, 'render_main_page'],
            'dashicons-google'
        );

        // Submenu pages - rename the default first item
        add_submenu_page(
            'beardog-reviews',
            __('Settings', 'beardog-reviews'),
            __('Settings', 'beardog-reviews'),
            'manage_options',
            'beardog-reviews'
        );

        // Add sync page
        add_submenu_page(
            'beardog-reviews',
            __('Sync Reviews', 'beardog-reviews'),
            __('Sync Reviews', 'beardog-reviews'),
            'manage_options',
            'agr-sync-reviews',
            [$this, 'render_sync_page']
        );
    }

    public function handle_add_business() {
        if (!isset($_POST['agr_add_business'])) {
            return;
        }

        if (!check_admin_referer('agr_add_business')) {
            wp_die(__('Security check failed', 'beardog-reviews'));
        }

        $business_name = sanitize_text_field($_POST['business_name']);
        $place_id = sanitize_text_field($_POST['place_id']);

        if (empty($business_name) || empty($place_id)) {
            add_settings_error(
                'agr_messages',
                'missing_fields',
                __('Please fill in all required fields.', 'beardog-reviews'),
                'error'
            );
            return;
        }

        // Create business term
        $term = wp_insert_term($business_name, 'business');
        if (is_wp_error($term)) {
            if ($term->get_error_code() === 'term_exists') {
                // If term exists, get the existing term ID
                $existing_term = get_term_by('name', $business_name, 'business');
                if ($existing_term) {
                    $term_id = $existing_term->term_id;
                } else {
                    add_settings_error(
                        'agr_messages',
                        'term_error',
                        __('Error creating business. Please try again.', 'beardog-reviews'),
                        'error'
                    );
                    return;
                }
            } else {
                add_settings_error(
                    'agr_messages',
                    'term_error',
                    $term->get_error_message(),
                    'error'
                );
                return;
            }
        } else {
            $term_id = $term['term_id'];
        }

        // Save business data
        $result = $this->db->save_business($term_id, $place_id);
        
        if ($result === false) {
            add_settings_error(
                'agr_messages',
                'db_error',
                __('Error saving business data. Please try again.', 'beardog-reviews'),
                'error'
            );
            // Clean up the term if database save failed
            wp_delete_term($term_id, 'business');
            return;
        }

        add_settings_error(
            'agr_messages',
            'business_added',
            __('Business added successfully!', 'beardog-reviews'),
            'updated'
        );
    }

    public function add_business_fields() {
        ?>
        <div class="form-field">
            <label for="place_id"><?php _e('Google Place ID', 'beardog-reviews'); ?></label>
            <input type="text" name="place_id" id="place_id" value="" required>
            <p class="description"><?php _e('Enter the Google Place ID for this business location', 'beardog-reviews'); ?></p>
        </div>
        <?php
    }

    public function edit_business_fields($term) {
        $business = $this->db->get_business($term->term_id);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="place_id"><?php _e('Google Place ID', 'beardog-reviews'); ?></label>
            </th>
            <td>
                <input type="text" name="place_id" id="place_id" value="<?php echo esc_attr($business->place_id ?? ''); ?>" required>
                <p class="description"><?php _e('Enter the Google Place ID for this business location', 'beardog-reviews'); ?></p>
            </td>
        </tr>
        <?php
    }

    public function save_business_fields($term_id) {
        if (isset($_POST['place_id'])) {
            $place_id = sanitize_text_field($_POST['place_id']);
            $this->db->save_business($term_id, $place_id);
        }
    }

    public function handle_manual_sync() {
        if (!isset($_POST['agr_sync_reviews'])) {
            return;
        }

        check_admin_referer('agr_sync_reviews');

        $term_id = intval($_POST['business_id']);
        if ($this->reviews->sync_reviews($term_id)) {
            add_settings_error(
                'agr_messages',
                'agr_sync_success',
                __('Reviews synced successfully!', 'beardog-reviews'),
                'updated'
            );
        } else {
            add_settings_error(
                'agr_messages',
                'agr_sync_error',
                __('Error syncing reviews. Please check the Place ID.', 'beardog-reviews'),
                'error'
            );
        }
    }

    public function handle_sync_settings() {
        if (!isset($_POST['agr_save_settings'])) {
            return;
        }

        check_admin_referer('agr_save_settings');

        $frequency = sanitize_text_field($_POST['sync_frequency']);
        update_option('agr_sync_frequency', $frequency);

        wp_clear_scheduled_hook('agr_sync_reviews');
        wp_schedule_event(time(), $frequency, 'agr_sync_reviews');

        add_settings_error(
            'agr_messages',
            'agr_settings_saved',
            __('Settings saved successfully!', 'beardog-reviews'),
            'updated'
        );
    }

    public function show_admin_notices() {
        settings_errors('agr_messages');
    }

    public function render_main_page() {
        include AGR_PLUGIN_DIR . 'templates/admin/main-page.php';
    }

    public function render_sync_page() {
        include AGR_PLUGIN_DIR . 'templates/admin/sync-page.php';
    }

    public function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=beardog-reviews'),
            __('Settings', 'beardog-reviews')
        );
        array_unshift($links, $settings_link);
        return $links;
    }
} 