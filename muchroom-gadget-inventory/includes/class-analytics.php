<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_Analytics {

    public static function get_sales_analytics( $period = 'daily', $date = null ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $date   = $date ?: current_time( 'Y-m-d' );

        switch ( $period ) {
            case 'weekly':
                $start = gmdate( 'Y-m-d', strtotime( 'monday this week', strtotime( $date ) ) );
                $end   = gmdate( 'Y-m-d', strtotime( 'sunday this week', strtotime( $date ) ) );
                break;
            case 'monthly':
                $start = gmdate( 'Y-m-01', strtotime( $date ) );
                $end   = gmdate( 'Y-m-t', strtotime( $date ) );
                break;
            case 'yearly':
                $start = gmdate( 'Y-01-01', strtotime( $date ) );
                $end   = gmdate( 'Y-12-31', strtotime( $date ) );
                break;
            default: // daily
                $start = $date;
                $end   = $date;
        }

        // Total sales.
        $total_sales = $wpdb->get_var( $wpdb->prepare(
            "SELECT COALESCE(SUM(grand_total), 0) FROM {$prefix}orders
             WHERE DATE(created_at) BETWEEN %s AND %s", $start, $end
        ) );

        // Orders count.
        $orders_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$prefix}orders
             WHERE DATE(created_at) BETWEEN %s AND %s", $start, $end
        ) );

        // Top products.
        $top_products = $wpdb->get_results( $wpdb->prepare(
            "SELECT oi.product_name, oi.category_name, SUM(oi.quantity) as total_qty, SUM(oi.total) as total_revenue
             FROM {$prefix}order_items oi
             LEFT JOIN {$prefix}orders o ON oi.order_id = o.id
             WHERE DATE(o.created_at) BETWEEN %s AND %s
             GROUP BY oi.product_id
             ORDER BY total_qty DESC LIMIT 10",
            $start, $end
        ) );

        // Category breakdown.
        $category_sales = $wpdb->get_results( $wpdb->prepare(
            "SELECT oi.category_name, SUM(oi.quantity) as total_qty, SUM(oi.total) as total_revenue
             FROM {$prefix}order_items oi
             LEFT JOIN {$prefix}orders o ON oi.order_id = o.id
             WHERE DATE(o.created_at) BETWEEN %s AND %s
             GROUP BY oi.category_name
             ORDER BY total_revenue DESC",
            $start, $end
        ) );

        // Daily breakdown for charts.
        $daily_sales = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(created_at) as sale_date, SUM(grand_total) as total,
                    SUM(cash_amount) as cash, SUM(transfer_amount) as transfer, COUNT(*) as orders
             FROM {$prefix}orders
             WHERE DATE(created_at) BETWEEN %s AND %s
             GROUP BY DATE(created_at) ORDER BY sale_date ASC",
            $start, $end
        ) );

        // Payment method split.
        $payment_split = $wpdb->get_row( $wpdb->prepare(
            "SELECT COALESCE(SUM(cash_amount), 0) as total_cash,
                    COALESCE(SUM(transfer_amount), 0) as total_transfer
             FROM {$prefix}orders
             WHERE DATE(created_at) BETWEEN %s AND %s",
            $start, $end
        ) );

        return array(
            'period'         => $period,
            'start_date'     => $start,
            'end_date'       => $end,
            'total_sales'    => floatval( $total_sales ),
            'orders_count'   => intval( $orders_count ),
            'top_products'   => $top_products,
            'category_sales' => $category_sales,
            'daily_sales'    => $daily_sales,
            'payment_split'  => $payment_split,
        );
    }
}
