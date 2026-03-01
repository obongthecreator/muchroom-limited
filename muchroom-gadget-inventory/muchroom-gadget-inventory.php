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
require_once MGI_PLUGIN_DIR . 'includes/class-shortcodes.php';

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

        add_action( 'init', array( $this, 'start_session' ), 1 );
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'rest_api_init', array( 'MGI_API', 'register_routes' ) );
        add_action( 'template_redirect', array( $this, 'handle_routes' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

        add_action( 'wp_ajax_mgi_action', array( 'MGI_API', 'handle_ajax' ) );
        add_action( 'wp_ajax_nopriv_mgi_action', array( 'MGI_API', 'handle_ajax' ) );

        add_action( 'init', array( 'MGI_Shortcodes', 'init' ) );
    }

    /**
     * Start PHP session early to prevent "headers already sent" issues.
     * Scoped to plugin pages and AJAX to avoid overhead on unrelated requests.
     */
    public function start_session() {
        if ( session_id() || headers_sent() ) {
            return;
        }
        $is_mgi_page = isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], '/muchroom/' );
        $is_mgi_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) && 'mgi_action' === $_POST['action'];
        if ( $is_mgi_page || $is_mgi_ajax ) {
            // Ensure session cookie is valid for the entire site so that a
            // session started via /wp-admin/admin-ajax.php (login AJAX) is
            // also sent when the browser navigates to /muchroom/ pages.
            session_set_cookie_params( array(
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => is_ssl(),
                'httponly'  => true,
                'samesite'  => 'Lax',
            ) );
            session_start();
        }
    }

    public function init() {
        // Branch selection dashboard.
        add_rewrite_rule( '^muchroom/?$', 'index.php?mgi_page=branches', 'top' );

        // Branch-specific routes (e.g. /muchroom/nsukka/home/).
        $branches = array_keys( self::get_branches() );
        foreach ( $branches as $branch ) {
            add_rewrite_rule( '^muchroom/' . $branch . '/?$', 'index.php?mgi_page=landing&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/login/?$', 'index.php?mgi_page=login&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/home/?$', 'index.php?mgi_page=home&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/take-order/?$', 'index.php?mgi_page=take-order&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/sales/?$', 'index.php?mgi_page=sales&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/sales/history/?$', 'index.php?mgi_page=sales-history&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/inventory/?$', 'index.php?mgi_page=inventory&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/inventory/history/?$', 'index.php?mgi_page=inventory-history&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/import/?$', 'index.php?mgi_page=import&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/import/history/?$', 'index.php?mgi_page=import-history&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/financial/?$', 'index.php?mgi_page=financial&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/financial/history/?$', 'index.php?mgi_page=financial-history&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/analytics/?$', 'index.php?mgi_page=analytics&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/admin/?$', 'index.php?mgi_page=admin&mgi_branch=' . $branch, 'top' );
            add_rewrite_rule( '^muchroom/' . $branch . '/category/([^/]+)/?$', 'index.php?mgi_page=category&mgi_branch=' . $branch . '&mgi_category=$matches[1]', 'top' );
        }

        if ( get_option( 'mgi_flush_rewrite', false ) ) {
            flush_rewrite_rules();
            delete_option( 'mgi_flush_rewrite' );
        }
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'mgi_page';
        $vars[] = 'mgi_branch';
        $vars[] = 'mgi_category';
        return $vars;
    }

    /**
     * Get the list of branches with their details.
     */
    public static function get_branches() {
        return array(
            'nsukka' => array(
                'name'   => 'Nsukka Branch',
                'city'   => 'Nsukka',
                'icon'   => 'solar:shop-linear',
                'active' => true,
            ),
            'lagos' => array(
                'name'   => 'Lagos Branch',
                'city'   => 'Lagos',
                'icon'   => 'solar:shop-2-linear',
                'active' => false,
            ),
            'enugu' => array(
                'name'   => 'Enugu Branch',
                'city'   => 'Enugu',
                'icon'   => 'solar:buildings-linear',
                'active' => false,
            ),
            'owerri' => array(
                'name'   => 'Owerri Branch',
                'city'   => 'Owerri',
                'icon'   => 'solar:buildings-2-linear',
                'active' => false,
            ),
        );
    }

    /**
     * Get the current branch slug from the URL.
     */
    public static function get_current_branch() {
        $branch = get_query_var( 'mgi_branch' );
        return ! empty( $branch ) ? sanitize_text_field( $branch ) : '';
    }

    /**
     * Get branch display name.
     */
    public static function get_branch_name( $slug = '' ) {
        if ( empty( $slug ) ) {
            $slug = self::get_current_branch();
        }
        $branches = self::get_branches();
        return isset( $branches[ $slug ] ) ? $branches[ $slug ]['name'] : '';
    }

    public function enqueue_assets() {
        $page = get_query_var( 'mgi_page' );
        if ( empty( $page ) ) {
            return;
        }

        wp_enqueue_script( 'iconify', 'https://code.iconify.design/iconify-icon/2.3.0/iconify-icon.min.js', array(), '2.3.0', false );

        wp_enqueue_style( 'mgi-animations', MGI_PLUGIN_URL . 'assets/css/animations.css', array(), MGI_VERSION );
        wp_enqueue_style( 'mgi-responsive', MGI_PLUGIN_URL . 'assets/css/responsive.css', array(), MGI_VERSION );
        wp_enqueue_style( 'mgi-main', MGI_PLUGIN_URL . 'assets/css/main.css', array(), MGI_VERSION );

        if ( 'landing' === $page || 'branches' === $page ) {
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
            'branch'   => self::get_current_branch(),
        ) );
    }

    public function handle_routes() {
        $page = get_query_var( 'mgi_page' );
        if ( empty( $page ) ) {
            return;
        }

        $branch = self::get_current_branch();

        // Branch selector is always public.
        if ( 'branches' === $page ) {
            include MGI_PLUGIN_DIR . 'templates/branches.php';
            exit;
        }

        // Pages that require a branch context.
        $valid_branches = array_keys( self::get_branches() );
        if ( empty( $branch ) || ! in_array( $branch, $valid_branches, true ) ) {
            wp_redirect( home_url( '/muchroom/' ) );
            exit;
        }

        // Check if branch is active; show coming-soon for inactive branches.
        $branches = self::get_branches();
        if ( ! empty( $branch ) && isset( $branches[ $branch ] ) && ! $branches[ $branch ]['active'] ) {
            include MGI_PLUGIN_DIR . 'templates/coming-soon.php';
            exit;
        }

        // Public pages within a branch (landing & login don't require auth).
        $public_pages = array( 'landing', 'login' );
        if ( ! in_array( $page, $public_pages, true ) && ! MGI_Auth::is_logged_in() ) {
            wp_redirect( home_url( '/muchroom/' . $branch . '/login/' ) );
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
                // Prevent browser/proxy caching so data changes appear immediately.
                nocache_headers();
                include $template;
                exit;
            }
        }
    }
}

Muchroom_Gadget_Inventory::get_instance();
