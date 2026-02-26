<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_Orders {

    public static function create_order( $data ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();

        // Generate unique order number using timestamp and random suffix.
        $order_number = 'MR-' . gmdate( 'ymd' ) . '-' . strtoupper( substr( uniqid(), -5 ) );

        // Create form hash to prevent duplicate submissions.
        $form_hash = hash( 'sha256', wp_json_encode( $data ) . microtime() );

        // Check for duplicate submission by hash.
        if ( ! empty( $data['form_hash'] ) ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$prefix}orders WHERE form_hash = %s",
                sanitize_text_field( $data['form_hash'] )
            ) );
            if ( $exists ) {
                return new WP_Error( 'duplicate', 'This order has already been submitted.' );
            }
            $form_hash = sanitize_text_field( $data['form_hash'] );
        }

        $staff = MGI_Auth::current_staff();

        $wpdb->insert( $prefix . 'orders', array(
            'order_number'    => $order_number,
            'staff_id'        => $staff->id,
            'customer_name'   => sanitize_text_field( $data['customer_name'] ),
            'payment_method'  => sanitize_text_field( $data['payment_method'] ),
            'cash_amount'     => floatval( isset( $data['cash_amount'] ) ? $data['cash_amount'] : 0 ),
            'transfer_amount' => floatval( isset( $data['transfer_amount'] ) ? $data['transfer_amount'] : 0 ),
            'grand_total'     => floatval( $data['grand_total'] ),
            'form_hash'       => $form_hash,
        ) );

        $order_id = $wpdb->insert_id;

        // Insert order items and update stock.
        if ( ! empty( $data['items'] ) && is_array( $data['items'] ) ) {
            foreach ( $data['items'] as $item ) {
                $product = MGI_Products::get_product( intval( $item['product_id'] ) );
                if ( ! $product ) continue;

                $qty   = intval( $item['quantity'] );
                $total = floatval( $product->price ) * $qty;

                $wpdb->insert( $prefix . 'order_items', array(
                    'order_id'      => $order_id,
                    'product_id'    => $product->id,
                    'product_code'  => $product->product_code,
                    'product_name'  => $product->name,
                    'model'         => $product->model,
                    'category_name' => $product->category_name,
                    'cost'          => $product->cost,
                    'price'         => $product->price,
                    'quantity'      => $qty,
                    'total'         => $total,
                ) );

                // Decrease stock.
                MGI_Products::update_stock( $product->id, -$qty );

                // Update inventory for today.
                MGI_Inventory::record_sale( $product->id, $qty );
            }
        }

        // Update financial summary.
        MGI_Financial::record_sale(
            floatval( $data['grand_total'] ),
            floatval( isset( $data['cash_amount'] ) ? $data['cash_amount'] : 0 ),
            floatval( isset( $data['transfer_amount'] ) ? $data['transfer_amount'] : 0 )
        );

        return self::get_order( $order_id );
    }

    public static function get_order( $id ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();

        $order = $wpdb->get_row( $wpdb->prepare(
            "SELECT o.*, s.full_name as staff_name FROM {$prefix}orders o
             LEFT JOIN {$prefix}staff s ON o.staff_id = s.id
             WHERE o.id = %d", $id
        ) );

        if ( $order ) {
            $order->items = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$prefix}order_items WHERE order_id = %d", $id
            ) );
        }

        return $order;
    }

    public static function get_orders( $date = null, $page = 1, $per_page = 50 ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $offset = ( $page - 1 ) * $per_page;

        $where = '';
        $params = array();
        if ( $date ) {
            $where = "WHERE DATE(o.created_at) = %s";
            $params[] = $date;
        }

        $sql = "SELECT o.*, s.full_name as staff_name FROM {$prefix}orders o
                LEFT JOIN {$prefix}staff s ON o.staff_id = s.id
                $where ORDER BY o.created_at DESC LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    public static function get_today_orders() {
        return self::get_orders( current_time( 'Y-m-d' ) );
    }

    public static function get_orders_count( $date = null ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        if ( $date ) {
            return $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$prefix}orders WHERE DATE(created_at) = %s", $date
            ) );
        }
        return $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}orders" );
    }
}
