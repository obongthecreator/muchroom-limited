<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_Inventory {

    /**
     * Ensure an inventory record exists for a product on a given date.
     * Opening stock = previous day's closing stock.
     */
    public static function ensure_record( $product_id, $date = null ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $date   = $date ?: current_time( 'Y-m-d' );

        $exists = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$prefix}inventory WHERE product_id = %d AND date = %s",
            $product_id, $date
        ) );

        if ( $exists ) {
            return $exists;
        }

        // Get yesterday's closing stock.
        $yesterday = gmdate( 'Y-m-d', strtotime( $date . ' -1 day' ) );
        $prev = $wpdb->get_var( $wpdb->prepare(
            "SELECT closing_stock FROM {$prefix}inventory WHERE product_id = %d AND date = %s",
            $product_id, $yesterday
        ) );

        if ( null === $prev ) {
            // No previous record; use current product stock.
            $product = MGI_Products::get_product( $product_id );
            $prev    = $product ? $product->stock : 0;
        }

        $opening = intval( $prev );

        $wpdb->insert( $prefix . 'inventory', array(
            'product_id'    => $product_id,
            'date'          => $date,
            'opening_stock' => $opening,
            'imported'      => 0,
            'sold'          => 0,
            'closing_stock' => $opening,
        ) );

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$prefix}inventory WHERE product_id = %d AND date = %s",
            $product_id, $date
        ) );
    }

    public static function record_sale( $product_id, $qty ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $date   = current_time( 'Y-m-d' );

        self::ensure_record( $product_id, $date );

        $wpdb->query( $wpdb->prepare(
            "UPDATE {$prefix}inventory SET sold = sold + %d, closing_stock = closing_stock - %d
             WHERE product_id = %d AND date = %s",
            $qty, $qty, $product_id, $date
        ) );
    }

    public static function record_import( $product_id, $qty, $staff_id, $note = '' ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $date   = current_time( 'Y-m-d' );

        self::ensure_record( $product_id, $date );

        // Update inventory.
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$prefix}inventory SET imported = imported + %d, closing_stock = closing_stock + %d
             WHERE product_id = %d AND date = %s",
            $qty, $qty, $product_id, $date
        ) );

        // Update product stock.
        MGI_Products::update_stock( $product_id, $qty );

        // Record import entry.
        $wpdb->insert( $prefix . 'imports', array(
            'product_id' => $product_id,
            'staff_id'   => $staff_id,
            'quantity'    => $qty,
            'note'       => sanitize_textarea_field( $note ),
        ) );

        return true;
    }

    public static function get_inventory( $date = null ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $date   = $date ?: current_time( 'Y-m-d' );

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT i.*, p.name as product_name, p.product_code, c.name as category_name
             FROM {$prefix}inventory i
             LEFT JOIN {$prefix}products p ON i.product_id = p.id
             LEFT JOIN {$prefix}categories c ON p.category_id = c.id
             WHERE i.date = %s
             ORDER BY c.name, p.name",
            $date
        ) );
    }

    public static function get_imports( $date = null, $page = 1, $per_page = 50 ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $offset = ( $page - 1 ) * $per_page;

        $where  = '';
        $params = array();
        if ( $date ) {
            $where    = "WHERE DATE(imp.created_at) = %s";
            $params[] = $date;
        }

        $sql      = "SELECT imp.*, p.name as product_name, p.product_code, c.name as category_name, s.full_name as staff_name
                     FROM {$prefix}imports imp
                     LEFT JOIN {$prefix}products p ON imp.product_id = p.id
                     LEFT JOIN {$prefix}categories c ON p.category_id = c.id
                     LEFT JOIN {$prefix}staff s ON imp.staff_id = s.id
                     $where ORDER BY imp.created_at DESC LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }
}
