<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_Users {

    public static function get_all() {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        return $wpdb->get_results( "SELECT id, username, full_name, role, email, phone, is_active, created_at FROM {$prefix}staff ORDER BY full_name ASC" );
    }

    public static function get_user( $id ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT id, username, full_name, role, email, phone, is_active, created_at FROM {$prefix}staff WHERE id = %d", $id
        ) );
    }

    public static function save_user( $data ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();

        $row = array(
            'username'  => sanitize_text_field( $data['username'] ),
            'full_name' => sanitize_text_field( $data['full_name'] ),
            'role'      => sanitize_text_field( isset( $data['role'] ) ? $data['role'] : 'staff' ),
            'email'     => sanitize_email( isset( $data['email'] ) ? $data['email'] : '' ),
            'phone'     => sanitize_text_field( isset( $data['phone'] ) ? $data['phone'] : '' ),
            'is_active' => isset( $data['is_active'] ) ? intval( $data['is_active'] ) : 1,
        );

        if ( ! empty( $data['password'] ) ) {
            $row['password'] = wp_hash_password( $data['password'] );
        }

        if ( ! empty( $data['id'] ) ) {
            return $wpdb->update( $prefix . 'staff', $row, array( 'id' => intval( $data['id'] ) ) );
        }

        if ( empty( $data['password'] ) ) {
            return new WP_Error( 'no_password', 'Password is required for new users.' );
        }

        return $wpdb->insert( $prefix . 'staff', $row );
    }

    public static function delete_user( $id ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        return $wpdb->delete( $prefix . 'staff', array( 'id' => intval( $id ) ) );
    }
}
