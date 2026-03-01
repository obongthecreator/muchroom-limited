<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_Admin_Panel {
    // Admin panel operations are handled through MGI_Products, MGI_Users, and the API.
    // This class provides helper methods for the admin template.

    public static function get_dashboard_stats() {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $today  = current_time( 'Y-m-d' );

        return array(
            'total_products'   => intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}products" ) ),
            'total_categories' => intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}categories" ) ),
            'total_staff'      => intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}staff" ) ),
            'today_orders'     => intval( $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$prefix}orders WHERE DATE(created_at) = %s", $today
            ) ) ),
            'today_sales'      => floatval( $wpdb->get_var( $wpdb->prepare(
                "SELECT COALESCE(SUM(grand_total), 0) FROM {$prefix}orders WHERE DATE(created_at) = %s", $today
            ) ) ),
        );
    }
}
