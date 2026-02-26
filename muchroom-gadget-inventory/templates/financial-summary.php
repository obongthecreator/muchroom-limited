<?php
/**
 * Financial Summary Page
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$page_title = 'Financial Summary';
include MGI_PLUGIN_DIR . 'templates/header.php';

$today     = current_time( 'Y-m-d' );
$financial = MGI_Financial::get_summary( $today );
?>

<div class="page-header">
    <h1 class="heading">Financial Summary</h1>
    <a href="<?php echo esc_url( home_url( '/muchroom/financial/history/' ) ); ?>" class="pill-btn pill-btn-outline btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
        📋 Financial History
    </a>
</div>

<div class="glass-card mb-24">
    <div class="flex items-center justify-between mb-16">
        <h2 class="font-display" style="font-size:18px;">Summary for <?php echo esc_html( $today ); ?></h2>
    </div>

    <div class="mgi-grid stagger-in" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
        <div class="glass-card kpi-card">
            <div class="kpi-icon">💵</div>
            <div class="kpi-value"><?php echo esc_html( '₦' . number_format( $financial->total_sales, 0 ) ); ?></div>
            <div class="kpi-label">Total Sales</div>
        </div>

        <div class="glass-card kpi-card">
            <div class="kpi-icon">💳</div>
            <div class="kpi-value"><?php echo esc_html( '₦' . number_format( $financial->transfer_total, 0 ) ); ?></div>
            <div class="kpi-label">Transfers / Cards</div>
        </div>

        <div class="glass-card kpi-card">
            <div class="kpi-icon">💵</div>
            <div class="kpi-value"><?php echo esc_html( '₦' . number_format( $financial->cash_total, 0 ) ); ?></div>
            <div class="kpi-label">Cash</div>
        </div>

        <div class="glass-card kpi-card">
            <div class="kpi-icon">📦</div>
            <div class="kpi-value"><?php echo esc_html( '₦' . number_format( $financial->old_cash, 0 ) ); ?></div>
            <div class="kpi-label">Old Cash (Yesterday's Cash Left)</div>
        </div>

        <div class="glass-card kpi-card pulse-glow" style="border:2px solid var(--primary);">
            <div class="kpi-icon">🏦</div>
            <div class="kpi-value text-primary"><?php echo esc_html( '₦' . number_format( $financial->cash_left, 0 ) ); ?></div>
            <div class="kpi-label">Cash Left (Old Cash + Today's Cash)</div>
        </div>
    </div>
</div>

<div class="glass-card">
    <h2 class="font-display mb-16" style="font-size:18px;">Calculation Breakdown</h2>
    <div class="mgi-table-wrap">
        <table class="mgi-table">
            <tbody>
                <tr>
                    <td class="fw-bold">Total Sales</td>
                    <td class="text-right amount"><?php echo esc_html( '₦' . number_format( $financial->total_sales, 2 ) ); ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">= Transfers/Cards</td>
                    <td class="text-right amount"><?php echo esc_html( '₦' . number_format( $financial->transfer_total, 2 ) ); ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">+ Cash Received</td>
                    <td class="text-right amount"><?php echo esc_html( '₦' . number_format( $financial->cash_total, 2 ) ); ?></td>
                </tr>
                <tr style="border-top:2px solid rgba(0,0,0,0.1);">
                    <td class="fw-bold">Old Cash (Yesterday's Cash Left)</td>
                    <td class="text-right amount"><?php echo esc_html( '₦' . number_format( $financial->old_cash, 2 ) ); ?></td>
                </tr>
                <tr style="background:rgba(108,99,255,0.04);">
                    <td class="fw-bold text-primary" style="font-size:16px;">Cash Left = Old Cash + Cash Received</td>
                    <td class="text-right amount text-primary" style="font-size:16px;"><?php echo esc_html( '₦' . number_format( $financial->cash_left, 2 ) ); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
