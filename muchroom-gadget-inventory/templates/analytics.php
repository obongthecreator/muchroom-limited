<?php
/**
 * Analytics Dashboard - Sectioned card layout with KPIs and charts
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$page_title = 'Analytics';
include MGI_PLUGIN_DIR . 'templates/header.php';

$today = current_time( 'Y-m-d' );
?>

<div class="page-header">
    <h1 class="heading">Analytics</h1>
    <div class="filter-bar">
        <select id="analytics-period" class="mgi-select">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly" selected>Monthly</option>
            <option value="yearly">Yearly</option>
        </select>
        <input type="date" id="analytics-date" class="mgi-input" value="<?php echo esc_attr( $today ); ?>" />
    </div>
</div>

<div id="analytics-page">

<!-- Section: KPI Summary -->
<div class="mgi-grid stagger-in mb-24" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
    <div class="glass-card kpi-card">
        <div class="kpi-icon"><iconify-icon icon="solar:wallet-money-linear"></iconify-icon></div>
        <div class="kpi-value" id="kpi-total-sales">₦0</div>
        <div class="kpi-label">Total Sales</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon"><iconify-icon icon="solar:document-text-linear"></iconify-icon></div>
        <div class="kpi-value" id="kpi-orders">0</div>
        <div class="kpi-label">Orders</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon"><iconify-icon icon="solar:wallet-money-linear"></iconify-icon></div>
        <div class="kpi-value" id="kpi-cash">₦0</div>
        <div class="kpi-label">Cash</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon"><iconify-icon icon="solar:card-transfer-linear"></iconify-icon></div>
        <div class="kpi-value" id="kpi-transfer">₦0</div>
        <div class="kpi-label">Transfer</div>
    </div>
</div>

<!-- Section: Sales Trend Chart -->
<h2 class="subheading font-display fw-800 mb-16" style="font-size:18px;">Sales Trend</h2>
<div class="glass-card mb-24" style="padding:20px;">
    <div style="position:relative;height:320px;">
        <canvas id="sales-line-chart"></canvas>
    </div>
</div>

<!-- Section: Payment & Category Charts -->
<div class="mgi-grid mb-24" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
    <div class="glass-card" style="padding:20px;">
        <h3 class="font-display mb-8" style="font-size:16px;">Payment Methods</h3>
        <div style="position:relative;height:260px;">
            <canvas id="payment-pie-chart"></canvas>
        </div>
    </div>
    <div class="glass-card" style="padding:20px;">
        <h3 class="font-display mb-8" style="font-size:16px;">Category Performance</h3>
        <div style="position:relative;height:260px;">
            <canvas id="category-bar-chart"></canvas>
        </div>
    </div>
</div>

<!-- Section: Top Products -->
<h2 class="subheading font-display fw-800 mb-16" style="font-size:18px;">Top Products</h2>
<div class="glass-card mb-24" style="padding:20px;">
    <div id="top-products-list">
        <div class="loading-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
    </div>
</div>

<!-- Section: Recent Activity -->
<h2 class="subheading font-display fw-800 mb-16" style="font-size:18px;">Recent Activity</h2>
<div class="glass-card mb-24" style="padding:20px;">
    <div class="activity-feed" id="activity-feed">
        <p class="text-muted text-center">Activity data loads with sales</p>
    </div>
</div>

</div>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
