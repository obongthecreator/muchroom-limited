<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_Database {

    public static function activate() {
        self::create_tables();
        update_option( 'mgi_flush_rewrite', true );
        self::seed_defaults();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $prefix  = $wpdb->prefix . 'mgi_';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Categories table.
        $sql = "CREATE TABLE {$prefix}categories (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            icon VARCHAR(100) DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset;";
        dbDelta( $sql );

        // Products table.
        $sql = "CREATE TABLE {$prefix}products (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            category_id BIGINT UNSIGNED NOT NULL,
            product_code VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            model VARCHAR(255) DEFAULT '',
            description TEXT DEFAULT '',
            cost DECIMAL(12,2) NOT NULL DEFAULT 0,
            price DECIMAL(12,2) NOT NULL DEFAULT 0,
            stock INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            UNIQUE KEY product_code (product_code)
        ) $charset;";
        dbDelta( $sql );

        // Orders table.
        $sql = "CREATE TABLE {$prefix}orders (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_number VARCHAR(50) NOT NULL,
            staff_id BIGINT UNSIGNED NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            cash_amount DECIMAL(12,2) DEFAULT 0,
            transfer_amount DECIMAL(12,2) DEFAULT 0,
            grand_total DECIMAL(12,2) NOT NULL DEFAULT 0,
            form_hash VARCHAR(64) DEFAULT '',
            status VARCHAR(20) DEFAULT 'completed',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_number (order_number),
            UNIQUE KEY form_hash (form_hash),
            KEY staff_id (staff_id),
            KEY created_at (created_at)
        ) $charset;";
        dbDelta( $sql );

        // Order items table.
        $sql = "CREATE TABLE {$prefix}order_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            product_code VARCHAR(100) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            model VARCHAR(255) DEFAULT '',
            category_name VARCHAR(255) DEFAULT '',
            cost DECIMAL(12,2) NOT NULL DEFAULT 0,
            price DECIMAL(12,2) NOT NULL DEFAULT 0,
            quantity INT NOT NULL DEFAULT 1,
            total DECIMAL(12,2) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY order_id (order_id)
        ) $charset;";
        dbDelta( $sql );

        // Inventory / stock tracking table.
        $sql = "CREATE TABLE {$prefix}inventory (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT UNSIGNED NOT NULL,
            date DATE NOT NULL,
            opening_stock INT NOT NULL DEFAULT 0,
            imported INT NOT NULL DEFAULT 0,
            sold INT NOT NULL DEFAULT 0,
            closing_stock INT NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY product_date (product_id, date),
            KEY date_idx (date)
        ) $charset;";
        dbDelta( $sql );

        // Import records table.
        $sql = "CREATE TABLE {$prefix}imports (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT UNSIGNED NOT NULL,
            staff_id BIGINT UNSIGNED NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            note TEXT DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id)
        ) $charset;";
        dbDelta( $sql );

        // Financial summary table.
        $sql = "CREATE TABLE {$prefix}financial (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            date DATE NOT NULL,
            total_sales DECIMAL(12,2) DEFAULT 0,
            transfer_total DECIMAL(12,2) DEFAULT 0,
            cash_total DECIMAL(12,2) DEFAULT 0,
            old_cash DECIMAL(12,2) DEFAULT 0,
            cash_left DECIMAL(12,2) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY date_idx (date)
        ) $charset;";
        dbDelta( $sql );

        // Staff / users table.
        $sql = "CREATE TABLE {$prefix}staff (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            username VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'staff',
            email VARCHAR(255) DEFAULT '',
            phone VARCHAR(50) DEFAULT '',
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY username (username)
        ) $charset;";
        dbDelta( $sql );
    }

    public static function seed_defaults() {
        global $wpdb;
        $prefix = $wpdb->prefix . 'mgi_';

        // Default categories.
        $categories = array(
            array( 'name' => 'Projectors',          'slug' => 'projectors',          'icon' => '📽️' ),
            array( 'name' => 'Laptops',              'slug' => 'laptops',              'icon' => '💻' ),
            array( 'name' => 'Bluetooth Speakers',   'slug' => 'bluetooth-speakers',   'icon' => '🔊' ),
            array( 'name' => 'Phones',               'slug' => 'phones',               'icon' => '📱' ),
            array( 'name' => 'Phone Accessories',    'slug' => 'phone-accessories',    'icon' => '🎧' ),
            array( 'name' => 'Laptop Accessories',   'slug' => 'laptop-accessories',   'icon' => '🖱️' ),
            array( 'name' => 'General Gadgets',      'slug' => 'general-gadgets',      'icon' => '⚡' ),
        );

        foreach ( $categories as $cat ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$prefix}categories WHERE slug = %s", $cat['slug']
            ) );
            if ( ! $exists ) {
                $wpdb->insert( $prefix . 'categories', $cat );
            }
        }

        // Default admin user.
        $admin_exists = $wpdb->get_var( "SELECT id FROM {$prefix}staff WHERE username = 'admin'" );
        if ( ! $admin_exists ) {
            $wpdb->insert( $prefix . 'staff', array(
                'username'  => 'admin',
                'password'  => wp_hash_password( 'admin123' ),
                'full_name' => 'Administrator',
                'role'      => 'admin',
            ) );
        }
    }

    public static function get_prefix() {
        global $wpdb;
        return $wpdb->prefix . 'mgi_';
    }
}
