<?php

/**
 * Plugin Name: Beardog Reviews
 * Plugin URI: https://beardog.digital/
 * Description: A modern, efficient plugin to display Google Reviews using Place IDs
 * Version: 2.0.0
 * Requires PHP: 7.4
 * Author: #beaubhavik
 * Author URI: https://beardog.digital/
 * Text Domain: beardog-reviews
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace BeardogReviews;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

ini_set('display_errors', 0);

// Define plugin constants
define('AGR_VERSION', '2.0.0');
define('AGR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AGR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AGR_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('CUSTOM_HOST_URL', 'https://api.spiderdunia.com:3000');

// Include required files
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'BeardogReviews\\') === 0) {
        $class = str_replace('BeardogReviews\\', '', $class);
        $class = str_replace('\\', '/', $class);
        $path = AGR_PLUGIN_DIR . 'includes/' . $class . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

use BeardogReviews\Admin\ReviewMetaBox;

// Initialize the plugin
class BeardogReviews {
    private static $instance = null;
    private $db;
    private $admin;
    private $reviews;
    private $review_meta_box;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Load required files
        require_once AGR_PLUGIN_DIR . 'includes/Database.php';
        require_once AGR_PLUGIN_DIR . 'includes/Admin.php';
        require_once AGR_PLUGIN_DIR . 'includes/Reviews.php';
        require_once AGR_PLUGIN_DIR . 'includes/DisplayReviews.php';

        // Initialize components
        $this->db = new Database();
        $this->admin = new Admin();
        $this->reviews = new Reviews();
        $this->review_meta_box = new ReviewMetaBox();

        // Initialize hooks
        $this->init_hooks();
        
        // Initialize plugin updater
        $this->init_updater();
    }

    private function init_hooks() {
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall']);

        // Register post type and taxonomy
        add_action('init', [$this->reviews, 'register_post_type']);
        
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . AGR_PLUGIN_BASENAME, [$this->admin, 'add_settings_link']);
    }

    public function activate() {
        // Initialize database
        if (!$this->db) {
            $this->db = new Database();
        }
        $this->db->activate();

        // Register post type and taxonomy
        if (!$this->reviews) {
            $this->reviews = new Reviews();
        }
        $this->reviews->register_post_type();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function deactivate() {
        if (!$this->db) {
            $this->db = new Database();
        }
        $this->db->deactivate();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    private function init_updater() {
        // Only proceed if the update checker file exists
        if (!file_exists(__DIR__ . '/update-checker/update-checker.php')) {
            return;
        }

        // Try to include the update checker
        try {
            require_once __DIR__ . '/update-checker/update-checker.php';
            
            // Check if the class exists in the global namespace
            if (class_exists('\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')) {
                $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                    'https://github.com/beaushowcase/beardog-reviews/',
                    __FILE__,
                    'beardog-reviews'
                );
                
                if (method_exists($myUpdateChecker, 'setBranch')) {
                    $myUpdateChecker->setBranch('main');
                }
            }
        } catch (\Exception $e) {
            // Log error but don't break plugin functionality
            error_log('Beardog Reviews Plugin Update Checker Error: ' . $e->getMessage());
        }
    }

    public static function uninstall() {
        if (class_exists('BeardogReviews\\Database')) {
            $db = new Database();
            $db->uninstall();
        }
    }
}

// Initialize the plugin
BeardogReviews::get_instance();
