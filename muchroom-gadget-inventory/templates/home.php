<?php
/**
 * Homepage - Category cards, financial summary, quick links
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$page_title = 'Home';
include MGI_PLUGIN_DIR . 'templates/header.php';

$categories = MGI_Products::get_categories();
$financial  = MGI_Financial::get_summary();
$staff      = MGI_Auth::current_staff();
$today      = current_time( 'Y-m-d' );
$today_orders = MGI_Orders::get_orders_count( $today );

// Get product counts per category
global $wpdb;
$prefix = MGI_Database::get_prefix();
$cat_counts = array();
foreach ( $categories as $cat ) {
    $cat_counts[ $cat->id ] = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$prefix}products WHERE category_id = %d", $cat->id
    ) );
}
?>

<div class="page-header">
    <div>
        <h1 class="heading">Dashboard</h1>
        <p class="text-muted body-text" style="font-size:14px;">Welcome back, <?php echo esc_html( $staff ? $staff->full_name : 'Staff' ); ?></p>
    </div>
    <div class="mgi-nav-clock" id="mgi-clock-home" style="font-size:20px;padding:8px 20px;">00:00:00</div>
</div>

<!-- Quick Actions Row -->
<div class="mgi-grid mgi-grid-4 stagger-in mb-24" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));">
    <a href="<?php echo esc_url( home_url( '/muchroom/take-order/' ) ); ?>" class="glass-card category-card" onclick="event.preventDefault(); navigateTo(this.href);">
        <span class="cat-icon">🧾</span>
        <span class="cat-name">Take Order</span>
    </a>
    <a href="<?php echo esc_url( home_url( '/muchroom/sales/' ) ); ?>" class="glass-card category-card" onclick="event.preventDefault(); navigateTo(this.href);">
        <span class="cat-icon">💵</span>
        <span class="cat-name">Sales</span>
    </a>
    <a href="<?php echo esc_url( home_url( '/muchroom/import/' ) ); ?>" class="glass-card category-card" onclick="event.preventDefault(); navigateTo(this.href);">
        <span class="cat-icon">📥</span>
        <span class="cat-name">Import</span>
    </a>
    <a href="<?php echo esc_url( home_url( '/muchroom/financial/' ) ); ?>" class="glass-card category-card" onclick="event.preventDefault(); navigateTo(this.href);">
        <span class="cat-icon">💰</span>
        <span class="cat-name">Financial Summary</span>
    </a>
    <a href="<?php echo esc_url( home_url( '/muchroom/analytics/' ) ); ?>" class="glass-card category-card" onclick="event.preventDefault(); navigateTo(this.href);">
        <span class="cat-icon">📊</span>
        <span class="cat-name">Analytics</span>
    </a>
    <a href="<?php echo esc_url( home_url( '/muchroom/inventory/' ) ); ?>" class="glass-card category-card" onclick="event.preventDefault(); navigateTo(this.href);">
        <span class="cat-icon">📦</span>
        <span class="cat-name">Inventory</span>
    </a>
    <?php if ( MGI_Auth::is_admin() ) : ?>
    <a href="<?php echo esc_url( home_url( '/muchroom/admin/' ) ); ?>" class="glass-card category-card" onclick="event.preventDefault(); navigateTo(this.href);">
        <span class="cat-icon">⚙️</span>
        <span class="cat-name">Admin Panel</span>
    </a>
    <?php endif; ?>
</div>

<!-- KPI Summary Row -->
<div class="mgi-grid stagger-in mb-24" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
    <div class="glass-card kpi-card">
        <div class="kpi-icon">💵</div>
        <div class="kpi-value"><?php echo esc_html( '₦' . number_format( $financial->total_sales, 0 ) ); ?></div>
        <div class="kpi-label">Today's Sales</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon">🧾</div>
        <div class="kpi-value"><?php echo esc_html( $today_orders ); ?></div>
        <div class="kpi-label">Today's Orders</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon">💳</div>
        <div class="kpi-value"><?php echo esc_html( '₦' . number_format( $financial->transfer_total, 0 ) ); ?></div>
        <div class="kpi-label">Transfers</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon">💵</div>
        <div class="kpi-value"><?php echo esc_html( '₦' . number_format( $financial->cash_total, 0 ) ); ?></div>
        <div class="kpi-label">Cash</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon">🏦</div>
        <div class="kpi-value"><?php echo esc_html( '₦' . number_format( $financial->cash_left, 0 ) ); ?></div>
        <div class="kpi-label">Cash Left</div>
    </div>
</div>

<!-- Categories Grid -->
<h2 class="subheading font-display fw-800 mb-16">Categories</h2>
<div class="mgi-grid stagger-in mb-24" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
    <?php foreach ( $categories as $cat ) : ?>
    <a href="<?php echo esc_url( home_url( '/muchroom/category/' . $cat->slug . '/' ) ); ?>" class="glass-card category-card" style="text-decoration:none;" onclick="event.preventDefault(); navigateTo(this.href);">
        <span class="cat-icon"><?php echo esc_html( $cat->icon ); ?></span>
        <span class="cat-name"><?php echo esc_html( $cat->name ); ?></span>
        <span class="cat-count"><?php echo esc_html( isset( $cat_counts[ $cat->id ] ) ? $cat_counts[ $cat->id ] : 0 ); ?> products</span>
    </a>
    <?php endforeach; ?>
</div>

<script>
// Second clock for home page
(function() {
    var c = document.getElementById('mgi-clock-home');
    if (!c) return;
    function u() {
        var n = new Date();
        c.textContent = String(n.getHours()).padStart(2,'0') + ':' + String(n.getMinutes()).padStart(2,'0') + ':' + String(n.getSeconds()).padStart(2,'0');
    }
    u(); setInterval(u, 1000);
})();
</script>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
