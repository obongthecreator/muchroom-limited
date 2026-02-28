<?php
/**
 * Sales Page - Today's sales
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$branch = Muchroom_Gadget_Inventory::get_current_branch();

$page_title = 'Sales';
include MGI_PLUGIN_DIR . 'templates/header.php';

$today  = current_time( 'Y-m-d' );
$orders = MGI_Orders::get_orders( $today );
$total  = 0;
foreach ( $orders as $o ) {
    $total += floatval( $o->grand_total );
}
?>

<div class="page-header">
    <h1 class="heading">Today's Sales</h1>
    <div class="flex gap-8">
        <a href="<?php echo esc_url( home_url( '/muchroom/' . $branch . '/sales/history/' ) ); ?>" class="pill-btn pill-btn-outline btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
            📋 Sales History
        </a>
        <a href="<?php echo esc_url( home_url( '/muchroom/' . $branch . '/take-order/' ) ); ?>" class="pill-btn beam-btn btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
            ➕ New Order
        </a>
    </div>
</div>

<div class="glass-card mb-24">
    <div class="flex justify-between items-center mb-16">
        <h2 class="font-display" style="font-size:16px;">Sales for <?php echo esc_html( $today ); ?></h2>
        <span class="font-display fw-800 text-primary" style="font-size:20px;"><?php echo esc_html( '₦' . number_format( $total, 0 ) ); ?></span>
    </div>

    <div class="mgi-table-wrap">
        <table class="mgi-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Staff</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $orders ) ) : ?>
                <tr><td colspan="7" class="text-center text-muted">No sales today</td></tr>
                <?php else : ?>
                    <?php foreach ( $orders as $order ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $order->order_number ); ?></strong></td>
                        <td><?php echo esc_html( $order->customer_name ); ?></td>
                        <td>
                            <?php
                            if ( 'cash' === $order->payment_method ) {
                                echo '<span class="badge badge-success">Cash</span>';
                            } elseif ( 'transfer' === $order->payment_method ) {
                                echo '<span class="badge badge-warning">Transfer</span>';
                            } else {
                                echo '<span class="badge badge-success">Cash</span> <span class="badge badge-warning">Transfer</span>';
                            }
                            ?>
                        </td>
                        <td class="amount fw-bold"><?php echo esc_html( '₦' . number_format( $order->grand_total, 0 ) ); ?></td>
                        <td><?php echo esc_html( $order->staff_name ); ?></td>
                        <td><?php echo esc_html( gmdate( 'H:i', strtotime( $order->created_at ) ) ); ?></td>
                        <td>
                            <button class="pill-btn pill-btn-glass btn-text" style="padding:4px 12px;font-size:11px;" onclick="reprintReceipt(<?php echo intval( $order->id ); ?>)">
                                🖨️ Receipt
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
