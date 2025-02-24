<?php
namespace BeardogReviews;

class Database {
    private $wpdb;
    private $table_name;
    private $charset_collate;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'agr_businesses';
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    public function activate() {
        // Create or update the businesses table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            term_id bigint(20) NOT NULL,
            place_id varchar(255) NOT NULL,
            created datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY term_id (term_id)
        ) {$this->charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Set up initial options
        add_option('agr_version', AGR_VERSION);

        // Force refresh rewrite rules
        flush_rewrite_rules();
    }

    public function deactivate() {
        // No specific deactivation tasks needed
    }

    public function uninstall() {
        // Drop the businesses table
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->table_name}");

        // Remove all reviews
        $posts = get_posts([
            'post_type' => 'agr_google_review',
            'numberposts' => -1,
            'post_status' => 'any'
        ]);

        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }

        // Remove all business terms
        $terms = get_terms([
            'taxonomy' => 'business',
            'hide_empty' => false
        ]);

        foreach ($terms as $term) {
            wp_delete_term($term->term_id, 'business');
        }

        // Delete plugin options
        delete_option('agr_version');
    }

    public function get_business($term_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE term_id = %d",
                $term_id
            )
        );
    }

    public function get_business_place_id($term_id) {
        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT place_id FROM {$this->table_name} WHERE term_id = %d",
                $term_id
            )
        );
    }

    public function save_business($term_id, $place_id) {
        $existing = $this->get_business($term_id);

        if ($existing) {
            return $this->wpdb->update(
                $this->table_name,
                [
                    'place_id' => $place_id
                ],
                ['term_id' => $term_id]
            );
        } else {
            return $this->wpdb->insert(
                $this->table_name,
                [
                    'term_id' => $term_id,
                    'place_id' => $place_id,
                    'created' => current_time('mysql')
                ]
            );
        }
    }
} 