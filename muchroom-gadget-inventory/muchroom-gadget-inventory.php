<?php
/**
 * Plugin Name: Muchroom Gadget Inventory
 * Plugin URI: https://muchroomlimited.com
 * Description: A comprehensive gadget inventory management system for Muchroom Limited with sales, orders, analytics, and financial tracking.
 * Version: 1.0.0
 * Author: Muchroom Limited
 * Text Domain: muchroom-gadget-inventory
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MGI_VERSION', '1.0.0' );
define( 'MGI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MGI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Include core files.
require_once MGI_PLUGIN_DIR . 'includes/class-database.php';
require_once MGI_PLUGIN_DIR . 'includes/class-auth.php';
require_once MGI_PLUGIN_DIR . 'includes/class-products.php';
require_once MGI_PLUGIN_DIR . 'includes/class-orders.php';
require_once MGI_PLUGIN_DIR . 'includes/class-inventory.php';
require_once MGI_PLUGIN_DIR . 'includes/class-financial.php';
require_once MGI_PLUGIN_DIR . 'includes/class-analytics.php';
require_once MGI_PLUGIN_DIR . 'includes/class-admin-panel.php';
require_once MGI_PLUGIN_DIR . 'includes/class-users.php';
require_once MGI_PLUGIN_DIR . 'includes/class-api.php';

/**
 * Main plugin class.
 */
class Muchroom_Gadget_Inventory {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook( __FILE__, array( 'MGI_Database', 'activate' ) );
        register_deactivation_hook( __FILE__, array( 'MGI_Database', 'deactivate' ) );

        add_action( 'init', array( $this, 'init' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'rest_api_init', array( 'MGI_API', 'register_routes' ) );
        add_action( 'template_redirect', array( $this, 'handle_routes' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

        add_action( 'wp_ajax_mgi_action', array( 'MGI_API', 'handle_ajax' ) );
        add_action( 'wp_ajax_nopriv_mgi_action', array( 'MGI_API', 'handle_ajax' ) );
    }

    public function init() {
        add_rewrite_rule( '^muchroom/?$', 'index.php?mgi_page=landing', 'top' );
        add_rewrite_rule( '^muchroom/login/?$', 'index.php?mgi_page=login', 'top' );
        add_rewrite_rule( '^muchroom/home/?$', 'index.php?mgi_page=home', 'top' );
        add_rewrite_rule( '^muchroom/take-order/?$', 'index.php?mgi_page=take-order', 'top' );
        add_rewrite_rule( '^muchroom/sales/?$', 'index.php?mgi_page=sales', 'top' );
        add_rewrite_rule( '^muchroom/sales/history/?$', 'index.php?mgi_page=sales-history', 'top' );
        add_rewrite_rule( '^muchroom/inventory/?$', 'index.php?mgi_page=inventory', 'top' );
        add_rewrite_rule( '^muchroom/inventory/history/?$', 'index.php?mgi_page=inventory-history', 'top' );
        add_rewrite_rule( '^muchroom/import/?$', 'index.php?mgi_page=import', 'top' );
        add_rewrite_rule( '^muchroom/import/history/?$', 'index.php?mgi_page=import-history', 'top' );
        add_rewrite_rule( '^muchroom/financial/?$', 'index.php?mgi_page=financial', 'top' );
        add_rewrite_rule( '^muchroom/financial/history/?$', 'index.php?mgi_page=financial-history', 'top' );
        add_rewrite_rule( '^muchroom/analytics/?$', 'index.php?mgi_page=analytics', 'top' );
        add_rewrite_rule( '^muchroom/admin/?$', 'index.php?mgi_page=admin', 'top' );
        add_rewrite_rule( '^muchroom/category/([^/]+)/?$', 'index.php?mgi_page=category&mgi_category=$matches[1]', 'top' );

        if ( get_option( 'mgi_flush_rewrite', false ) ) {
            flush_rewrite_rules();
            delete_option( 'mgi_flush_rewrite' );
        }
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'mgi_page';
        $vars[] = 'mgi_category';
        return $vars;
    }

    public function enqueue_assets() {
        $page = get_query_var( 'mgi_page' );
        if ( empty( $page ) ) {
            return;
        }

        wp_enqueue_style( 'mgi-animations', MGI_PLUGIN_URL . 'assets/css/animations.css', array(), MGI_VERSION );
        wp_enqueue_style( 'mgi-responsive', MGI_PLUGIN_URL . 'assets/css/responsive.css', array(), MGI_VERSION );
        wp_enqueue_style( 'mgi-main', MGI_PLUGIN_URL . 'assets/css/main.css', array(), MGI_VERSION );

        if ( 'landing' === $page ) {
            wp_enqueue_style( 'mgi-landing', MGI_PLUGIN_URL . 'assets/css/landing.css', array( 'mgi-main' ), MGI_VERSION );
            wp_enqueue_script( 'mgi-landing', MGI_PLUGIN_URL . 'assets/js/landing.js', array(), MGI_VERSION, true );
        }

        wp_enqueue_script( 'mgi-main', MGI_PLUGIN_URL . 'assets/js/main.js', array(), MGI_VERSION, true );

        if ( in_array( $page, array( 'take-order', 'sales', 'sales-history' ), true ) ) {
            wp_enqueue_script( 'mgi-order', MGI_PLUGIN_URL . 'assets/js/order.js', array( 'mgi-main' ), MGI_VERSION, true );
            wp_enqueue_script( 'mgi-receipt', MGI_PLUGIN_URL . 'assets/js/receipt.js', array( 'mgi-main' ), MGI_VERSION, true );
        }

        if ( 'analytics' === $page ) {
            wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
            wp_enqueue_script( 'mgi-analytics', MGI_PLUGIN_URL . 'assets/js/analytics.js', array( 'chart-js', 'mgi-main' ), MGI_VERSION, true );
        }

        if ( in_array( $page, array( 'inventory', 'import', 'inventory-history', 'import-history' ), true ) ) {
            wp_enqueue_script( 'mgi-inventory', MGI_PLUGIN_URL . 'assets/js/inventory.js', array( 'mgi-main' ), MGI_VERSION, true );
        }

        wp_localize_script( 'mgi-main', 'mgiData', array(
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'restUrl'  => rest_url( 'mgi/v1/' ),
            'nonce'    => wp_create_nonce( 'mgi_nonce' ),
            'currency' => '₦',
        ) );
    }

    public function handle_routes() {
        $page = get_query_var( 'mgi_page' );
        if ( empty( $page ) ) {
            return;
        }

        // Check auth for protected pages.
        $public_pages = array( 'landing', 'login' );
        if ( ! in_array( $page, $public_pages, true ) && ! MGI_Auth::is_logged_in() ) {
            wp_redirect( home_url( '/muchroom/login/' ) );
            exit;
        }

        $template_map = array(
            'landing'           => 'landing.php',
            'login'             => 'login.php',
            'home'              => 'home.php',
            'take-order'        => 'take-order.php',
            'sales'             => 'sales.php',
            'sales-history'     => 'history.php',
            'inventory'         => 'inventory.php',
            'inventory-history' => 'history.php',
            'import'            => 'import.php',
            'import-history'    => 'history.php',
            'financial'         => 'financial-summary.php',
            'financial-history' => 'history.php',
            'analytics'         => 'analytics.php',
            'admin'             => 'admin.php',
            'category'          => 'category.php',
        );

        if ( isset( $template_map[ $page ] ) ) {
            $template = MGI_PLUGIN_DIR . 'templates/' . $template_map[ $page ];
            if ( file_exists( $template ) ) {
                include $template;
                exit;
            }
        }
    }
}

Muchroom_Gadget_Inventory::get_instance();
