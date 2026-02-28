<?php
/**
 * Shortcodes for embedding Muchroom Gadget Inventory pages into WordPress posts/pages.
 *
 * Usage examples:
 *   [mgi_branches]
 *   [mgi_home branch="nsukka"]
 *   [mgi_sales branch="nsukka"]
 *   [mgi_sales_history branch="nsukka"]
 *   [mgi_category branch="nsukka" category="laptops"]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MGI_Shortcodes {

	/**
	 * Whether templates are currently rendering inside a shortcode.
	 */
	private static $shortcode_mode = false;

	/**
	 * Check if we are currently rendering in shortcode mode.
	 */
	public static function is_shortcode_mode() {
		return self::$shortcode_mode;
	}

	/**
	 * Map of shortcode tag => internal page slug.
	 */
	private static $shortcode_map = array(
		'mgi_branches'          => 'branches',
		'mgi_landing'           => 'landing',
		'mgi_login'             => 'login',
		'mgi_home'              => 'home',
		'mgi_take_order'        => 'take-order',
		'mgi_sales'             => 'sales',
		'mgi_sales_history'     => 'sales-history',
		'mgi_inventory'         => 'inventory',
		'mgi_inventory_history' => 'inventory-history',
		'mgi_import'            => 'import',
		'mgi_import_history'    => 'import-history',
		'mgi_financial'         => 'financial',
		'mgi_financial_history' => 'financial-history',
		'mgi_analytics'         => 'analytics',
		'mgi_admin'             => 'admin',
		'mgi_category'          => 'category',
	);

	/**
	 * Register all shortcodes.
	 */
	public static function init() {
		foreach ( self::$shortcode_map as $tag => $page ) {
			add_shortcode( $tag, array( __CLASS__, 'handle_shortcode' ) );
		}
	}

	/**
	 * Unified shortcode handler — determines the page from the shortcode tag.
	 */
	public static function handle_shortcode( $atts, $content, $tag ) {
		$page = isset( self::$shortcode_map[ $tag ] ) ? self::$shortcode_map[ $tag ] : '';
		if ( empty( $page ) ) {
			return '<p class="mgi-shortcode-error">Unknown shortcode.</p>';
		}

		return self::render( $page, $atts );
	}

	/**
	 * Render a plugin page and return its HTML.
	 */
	public static function render( $page, $atts = array() ) {
		$atts = shortcode_atts( array(
			'branch'   => '',
			'category' => '',
		), $atts );

		$branch   = sanitize_text_field( $atts['branch'] );
		$category = sanitize_text_field( $atts['category'] );

		// Validate branch for pages that require one.
		$no_branch_pages = array( 'branches' );
		if ( ! in_array( $page, $no_branch_pages, true ) ) {
			if ( empty( $branch ) ) {
				return '<p class="mgi-shortcode-error">Please specify a branch attribute. Example: <code>[mgi_home branch="nsukka"]</code></p>';
			}
			$valid_branches = array_keys( Muchroom_Gadget_Inventory::get_branches() );
			if ( ! in_array( $branch, $valid_branches, true ) ) {
				return '<p class="mgi-shortcode-error">Invalid branch "<code>' . esc_html( $branch ) . '</code>". Available branches: ' . esc_html( implode( ', ', $valid_branches ) ) . '</p>';
			}
		}

		// Set query vars so templates read the correct context.
		set_query_var( 'mgi_page', $page );
		set_query_var( 'mgi_branch', $branch );
		if ( ! empty( $category ) ) {
			set_query_var( 'mgi_category', $category );
		}

		// Enqueue the same CSS/JS the page needs.
		self::enqueue_assets( $page, $branch );

		// Template map (mirrors handle_routes).
		$template_map = array(
			'branches'          => 'branches.php',
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

		if ( ! isset( $template_map[ $page ] ) ) {
			return '<p class="mgi-shortcode-error">Unknown page.</p>';
		}

		$template = MGI_PLUGIN_DIR . 'templates/' . $template_map[ $page ];
		if ( ! file_exists( $template ) ) {
			return '<p class="mgi-shortcode-error">Template not found.</p>';
		}

		// Tell templates to skip the full HTML document wrapper.
		self::$shortcode_mode = true;

		ob_start();
		include $template;
		$output = ob_get_clean();

		self::$shortcode_mode = false;

		return '<div class="mgi-shortcode-wrap mgi-page">' . $output . '</div>';
	}

	/**
	 * Enqueue CSS and JS for the given page (same logic as the main plugin class).
	 */
	private static function enqueue_assets( $page, $branch = '' ) {
		wp_enqueue_script( 'iconify', 'https://code.iconify.design/iconify-icon/2.3.0/iconify-icon.min.js', array(), '2.3.0', false );

		wp_enqueue_style( 'mgi-animations', MGI_PLUGIN_URL . 'assets/css/animations.css', array(), MGI_VERSION );
		wp_enqueue_style( 'mgi-responsive', MGI_PLUGIN_URL . 'assets/css/responsive.css', array(), MGI_VERSION );
		wp_enqueue_style( 'mgi-main', MGI_PLUGIN_URL . 'assets/css/main.css', array(), MGI_VERSION );

		if ( in_array( $page, array( 'landing', 'branches' ), true ) ) {
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
			'branch'   => $branch,
		) );
	}
}
