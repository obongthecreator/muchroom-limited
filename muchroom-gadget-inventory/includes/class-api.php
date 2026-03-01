<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_API {

    public static function register_routes() {
        register_rest_route( 'mgi/v1', '/products', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_products' ),
            'permission_callback' => array( __CLASS__, 'check_auth' ),
        ) );

        register_rest_route( 'mgi/v1', '/categories', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_categories' ),
            'permission_callback' => array( __CLASS__, 'check_auth' ),
        ) );

        register_rest_route( 'mgi/v1', '/analytics', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_analytics' ),
            'permission_callback' => array( __CLASS__, 'check_auth' ),
        ) );
    }

    public static function check_auth() {
        return MGI_Auth::is_logged_in();
    }

    public static function get_products( $request ) {
        $category_id = $request->get_param( 'category_id' );
        return rest_ensure_response( MGI_Products::get_products( $category_id ) );
    }

    public static function get_categories() {
        return rest_ensure_response( MGI_Products::get_categories() );
    }

    public static function get_analytics( $request ) {
        $period = $request->get_param( 'period' ) ?: 'daily';
        $date   = $request->get_param( 'date' ) ?: current_time( 'Y-m-d' );
        return rest_ensure_response( MGI_Analytics::get_sales_analytics( $period, $date ) );
    }

    /**
     * Handle AJAX requests.
     */
    public static function handle_ajax() {
        $action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : '';

        // Login uses password-based auth; skip nonce check to prevent
        // "Login failed" errors caused by stale or cached nonces expiring.
        if ( 'login' !== $action_type ) {
            check_ajax_referer( 'mgi_nonce', 'nonce' );
        }

        switch ( $action_type ) {
            case 'login':
                $username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
                $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
                $branch   = isset( $_POST['branch'] ) ? sanitize_text_field( wp_unslash( $_POST['branch'] ) ) : 'nsukka';
                $result   = MGI_Auth::login( $username, $password );
                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( array( 'message' => $result->get_error_message() ) );
                }
                wp_send_json_success( array( 'message' => 'Login successful', 'redirect' => home_url( '/muchroom/' . $branch . '/home/' ) ) );
                break;

            case 'logout':
                $branch = isset( $_POST['branch'] ) ? sanitize_text_field( wp_unslash( $_POST['branch'] ) ) : 'nsukka';
                MGI_Auth::logout();
                wp_send_json_success( array( 'redirect' => home_url( '/muchroom/' . $branch . '/login/' ) ) );
                break;

            case 'create_order':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $order_data = array(
                    'customer_name'   => isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '',
                    'payment_method'  => isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '',
                    'cash_amount'     => isset( $_POST['cash_amount'] ) ? floatval( $_POST['cash_amount'] ) : 0,
                    'transfer_amount' => isset( $_POST['transfer_amount'] ) ? floatval( $_POST['transfer_amount'] ) : 0,
                    'grand_total'     => isset( $_POST['grand_total'] ) ? floatval( $_POST['grand_total'] ) : 0,
                    'form_hash'       => isset( $_POST['form_hash'] ) ? sanitize_text_field( wp_unslash( $_POST['form_hash'] ) ) : '',
                    'items'           => isset( $_POST['items'] ) ? array_map( function( $item ) {
                        return array(
                            'product_id' => intval( $item['product_id'] ),
                            'quantity'   => intval( $item['quantity'] ),
                        );
                    }, $_POST['items'] ) : array(),
                );
                $result = MGI_Orders::create_order( $order_data );
                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( array( 'message' => $result->get_error_message() ) );
                }
                wp_send_json_success( array( 'order' => $result ) );
                break;

            case 'get_products':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $category_id = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : null;
                wp_send_json_success( array( 'products' => MGI_Products::get_products( $category_id ) ) );
                break;

            case 'search_products':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
                wp_send_json_success( array( 'products' => MGI_Products::search_products( $query ) ) );
                break;

            case 'import_stock':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $staff = MGI_Auth::current_staff();
                $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
                $quantity   = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 0;
                $note       = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
                if ( $product_id && $quantity > 0 ) {
                    MGI_Inventory::record_import( $product_id, $quantity, $staff->id, $note );
                    wp_send_json_success( array( 'message' => 'Import recorded successfully.' ) );
                }
                wp_send_json_error( array( 'message' => 'Invalid import data.' ) );
                break;

            case 'save_product':
                if ( ! MGI_Auth::is_admin() ) {
                    wp_send_json_error( array( 'message' => 'Admin access required.' ) );
                }
                $product_data = array(
                    'id'           => isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0,
                    'category_id'  => isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : 0,
                    'product_code' => isset( $_POST['product_code'] ) ? sanitize_text_field( wp_unslash( $_POST['product_code'] ) ) : '',
                    'name'         => isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '',
                    'model'        => isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : '',
                    'description'  => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
                    'cost'         => isset( $_POST['cost'] ) ? floatval( $_POST['cost'] ) : 0,
                    'price'        => isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : 0,
                );
                MGI_Products::save_product( $product_data );
                wp_send_json_success( array( 'message' => 'Product saved.' ) );
                break;

            case 'delete_product':
                if ( ! MGI_Auth::is_admin() ) {
                    wp_send_json_error( array( 'message' => 'Admin access required.' ) );
                }
                $pid = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
                MGI_Products::delete_product( $pid );
                wp_send_json_success( array( 'message' => 'Product deleted.' ) );
                break;

            case 'save_category':
                if ( ! MGI_Auth::is_admin() ) {
                    wp_send_json_error( array( 'message' => 'Admin access required.' ) );
                }
                $cat_data = array(
                    'id'   => isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : 0,
                    'name' => isset( $_POST['category_name'] ) ? sanitize_text_field( wp_unslash( $_POST['category_name'] ) ) : '',
                    'icon' => isset( $_POST['icon'] ) ? sanitize_text_field( wp_unslash( $_POST['icon'] ) ) : '',
                );
                MGI_Products::save_category( $cat_data );
                wp_send_json_success( array( 'message' => 'Category saved.' ) );
                break;

            case 'delete_category':
                if ( ! MGI_Auth::is_admin() ) {
                    wp_send_json_error( array( 'message' => 'Admin access required.' ) );
                }
                $cid = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : 0;
                MGI_Products::delete_category( $cid );
                wp_send_json_success( array( 'message' => 'Category deleted.' ) );
                break;

            case 'save_user':
                if ( ! MGI_Auth::is_admin() ) {
                    wp_send_json_error( array( 'message' => 'Admin access required.' ) );
                }
                $user_data = array(
                    'id'        => isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0,
                    'username'  => isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '',
                    'full_name' => isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '',
                    'password'  => isset( $_POST['password'] ) ? $_POST['password'] : '',
                    'role'      => isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : 'staff',
                    'email'     => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
                    'phone'     => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
                    'is_active' => isset( $_POST['is_active'] ) ? intval( $_POST['is_active'] ) : 1,
                );
                $result = MGI_Users::save_user( $user_data );
                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( array( 'message' => $result->get_error_message() ) );
                }
                wp_send_json_success( array( 'message' => 'User saved.' ) );
                break;

            case 'delete_user':
                if ( ! MGI_Auth::is_admin() ) {
                    wp_send_json_error( array( 'message' => 'Admin access required.' ) );
                }
                $uid = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
                MGI_Users::delete_user( $uid );
                wp_send_json_success( array( 'message' => 'User deleted.' ) );
                break;

            case 'get_orders':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : null;
                wp_send_json_success( array( 'orders' => MGI_Orders::get_orders( $date ) ) );
                break;

            case 'get_order':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $oid   = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
                $order = MGI_Orders::get_order( $oid );
                if ( $order ) {
                    wp_send_json_success( array( 'order' => $order ) );
                }
                wp_send_json_error( array( 'message' => 'Order not found.' ) );
                break;

            case 'get_analytics':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $period = isset( $_POST['period'] ) ? sanitize_text_field( wp_unslash( $_POST['period'] ) ) : 'daily';
                $date   = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : current_time( 'Y-m-d' );
                wp_send_json_success( array( 'analytics' => MGI_Analytics::get_sales_analytics( $period, $date ) ) );
                break;

            case 'get_financial':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : null;
                wp_send_json_success( array( 'financial' => MGI_Financial::get_summary( $date ) ) );
                break;

            case 'get_financial_history':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $start = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : null;
                $end   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : null;
                wp_send_json_success( array( 'history' => MGI_Financial::get_history( $start, $end ) ) );
                break;

            case 'get_inventory':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : null;
                wp_send_json_success( array( 'inventory' => MGI_Inventory::get_inventory( $date ) ) );
                break;

            case 'get_imports':
                if ( ! MGI_Auth::is_logged_in() ) {
                    wp_send_json_error( array( 'message' => 'Not authenticated.' ) );
                }
                $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : null;
                wp_send_json_success( array( 'imports' => MGI_Inventory::get_imports( $date ) ) );
                break;

            default:
                wp_send_json_error( array( 'message' => 'Unknown action.' ) );
        }
    }
}
