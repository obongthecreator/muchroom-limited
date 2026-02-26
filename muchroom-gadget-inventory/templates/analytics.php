<?php
/**
 * Analytics Dashboard with bento layout, charts, KPIs
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

<!-- KPI Row (1x1 each) -->
<div class="bento-grid mb-24">
    <div class="glass-card kpi-card bento-1x1">
        <div class="kpi-icon">💵</div>
        <div class="kpi-value" id="kpi-total-sales">₦0</div>
        <div class="kpi-label">Total Sales</div>
    </div>
    <div class="glass-card kpi-card bento-1x1">
        <div class="kpi-icon">🧾</div>
        <div class="kpi-value" id="kpi-orders">0</div>
        <div class="kpi-label">Orders</div>
    </div>
    <div class="glass-card kpi-card bento-1x1">
        <div class="kpi-icon">💵</div>
        <div class="kpi-value" id="kpi-cash">₦0</div>
        <div class="kpi-label">Cash</div>
    </div>
    <div class="glass-card kpi-card bento-1x1">
        <div class="kpi-icon">💳</div>
        <div class="kpi-value" id="kpi-transfer">₦0</div>
        <div class="kpi-label">Transfer</div>
    </div>
</div>

<!-- Charts Section -->
<div id="analytics-charts">
    <div class="bento-grid">
        <!-- Large Sales Chart (2x2) -->
        <div class="glass-card bento-2x2" style="padding:20px;">
            <h3 class="font-display mb-8" style="font-size:16px;">Sales Trend</h3>
            <div class="chart-container">
                <canvas id="sales-line-chart"></canvas>
            </div>
        </div>

        <!-- Payment Pie Chart (2x1) -->
        <div class="glass-card bento-2x1" style="padding:20px;">
            <h3 class="font-display mb-8" style="font-size:16px;">Payment Methods</h3>
            <div class="chart-container" style="min-height:200px;">
                <canvas id="payment-pie-chart"></canvas>
            </div>
        </div>

        <!-- Category Bar Chart (2x1) -->
        <div class="glass-card bento-2x1" style="padding:20px;">
            <h3 class="font-display mb-8" style="font-size:16px;">Category Performance</h3>
            <div class="chart-container" style="min-height:200px;">
                <canvas id="category-bar-chart"></canvas>
            </div>
        </div>

        <!-- Top Products (2x1) -->
        <div class="glass-card bento-2x1" style="padding:20px;">
            <h3 class="font-display mb-8" style="font-size:16px;">Top Products</h3>
            <div id="top-products-list" class="activity-feed">
                <div class="loading-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
        </div>

        <!-- Activity Feed (2x1) -->
        <div class="glass-card bento-2x1" style="padding:20px;">
            <h3 class="font-display mb-8" style="font-size:16px;">Recent Activity</h3>
            <div class="activity-feed" id="activity-feed">
                <p class="text-muted text-center">Activity data loads with sales</p>
            </div>
        </div>
    </div>
</div>

</div>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
