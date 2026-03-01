<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_Products {

    public static function get_categories() {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        return $wpdb->get_results( "SELECT * FROM {$prefix}categories ORDER BY name ASC" );
    }

    public static function get_category( $id ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$prefix}categories WHERE id = %d", $id ) );
    }

    public static function get_category_by_slug( $slug ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$prefix}categories WHERE slug = %s", $slug ) );
    }

    public static function save_category( $data ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();

        $row = array(
            'name' => sanitize_text_field( $data['name'] ),
            'slug' => sanitize_title( $data['name'] ),
            'icon' => isset( $data['icon'] ) ? sanitize_text_field( $data['icon'] ) : '',
        );

        if ( ! empty( $data['id'] ) ) {
            return $wpdb->update( $prefix . 'categories', $row, array( 'id' => intval( $data['id'] ) ) );
        }
        return $wpdb->insert( $prefix . 'categories', $row );
    }

    public static function delete_category( $id ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        return $wpdb->delete( $prefix . 'categories', array( 'id' => intval( $id ) ) );
    }

    public static function get_products( $category_id = null ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();

        $sql = "SELECT p.*, c.name as category_name FROM {$prefix}products p
                LEFT JOIN {$prefix}categories c ON p.category_id = c.id";
        if ( $category_id ) {
            $sql .= $wpdb->prepare( " WHERE p.category_id = %d", $category_id );
        }
        $sql .= " ORDER BY p.name ASC";
        return $wpdb->get_results( $sql );
    }

    public static function get_product( $id ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT p.*, c.name as category_name FROM {$prefix}products p
             LEFT JOIN {$prefix}categories c ON p.category_id = c.id
             WHERE p.id = %d", $id
        ) );
    }

    public static function save_product( $data ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();

        $row = array(
            'category_id'  => intval( $data['category_id'] ),
            'product_code' => sanitize_text_field( $data['product_code'] ),
            'name'         => sanitize_text_field( $data['name'] ),
            'model'        => sanitize_text_field( isset( $data['model'] ) ? $data['model'] : '' ),
            'description'  => sanitize_textarea_field( isset( $data['description'] ) ? $data['description'] : '' ),
            'cost'         => floatval( $data['cost'] ),
            'price'        => floatval( $data['price'] ),
        );

        if ( ! empty( $data['id'] ) ) {
            return $wpdb->update( $prefix . 'products', $row, array( 'id' => intval( $data['id'] ) ) );
        }
        return $wpdb->insert( $prefix . 'products', $row );
    }

    public static function delete_product( $id ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        return $wpdb->delete( $prefix . 'products', array( 'id' => intval( $id ) ) );
    }

    public static function update_stock( $product_id, $quantity_change ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$prefix}products SET stock = stock + %d WHERE id = %d",
            $quantity_change, $product_id
        ) );
    }

    public static function search_products( $query ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $like   = '%' . $wpdb->esc_like( $query ) . '%';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT p.*, c.name as category_name FROM {$prefix}products p
             LEFT JOIN {$prefix}categories c ON p.category_id = c.id
             WHERE p.name LIKE %s OR p.product_code LIKE %s OR p.model LIKE %s
             ORDER BY p.name ASC LIMIT 50",
            $like, $like, $like
        ) );
    }
}
