<?php
/**
 * Stock Inventory Page
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$branch = Muchroom_Gadget_Inventory::get_current_branch();

$page_title = 'Inventory';
include MGI_PLUGIN_DIR . 'templates/header.php';

$today = current_time( 'Y-m-d' );

// Initialize inventory records for all products today.
$products = MGI_Products::get_products();
foreach ( $products as $p ) {
    MGI_Inventory::ensure_record( $p->id, $today );
}

$inventory = MGI_Inventory::get_inventory( $today );
?>

<div class="page-header">
    <h1 class="heading">Stock Inventory</h1>
    <div class="flex gap-8">
        <a href="<?php echo esc_url( home_url( '/muchroom/' . $branch . '/inventory/history/' ) ); ?>" class="pill-btn pill-btn-outline btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
            📋 History
        </a>
        <a href="<?php echo esc_url( home_url( '/muchroom/' . $branch . '/import/' ) ); ?>" class="pill-btn beam-btn btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
            📥 Import Stock
        </a>
    </div>
</div>

<div class="glass-card mb-24" id="inventory-page">
    <div class="flex items-center justify-between mb-16 flex-wrap gap-8">
        <h2 class="font-display" style="font-size:16px;">Inventory for</h2>
        <input type="date" id="inventory-date" class="mgi-input" style="width:auto;" value="<?php echo esc_attr( $today ); ?>" />
    </div>

    <div class="mgi-table-wrap">
        <table class="mgi-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Code</th>
                    <th>Product</th>
                    <th class="text-center">Opening</th>
                    <th class="text-center">Imported</th>
                    <th class="text-center">Sold</th>
                    <th class="text-center">Closing</th>
                </tr>
            </thead>
            <tbody id="inventory-body">
                <?php if ( empty( $inventory ) ) : ?>
                <tr><td colspan="7" class="text-center text-muted">No inventory data</td></tr>
                <?php else : ?>
                    <?php foreach ( $inventory as $item ) : ?>
                    <tr>
                        <td><?php echo esc_html( $item->category_name ); ?></td>
                        <td><?php echo esc_html( $item->product_code ); ?></td>
                        <td><?php echo esc_html( $item->product_name ); ?></td>
                        <td class="text-center"><?php echo intval( $item->opening_stock ); ?></td>
                        <td class="text-center text-success"><?php echo intval( $item->imported ); ?></td>
                        <td class="text-center text-danger"><?php echo intval( $item->sold ); ?></td>
                        <td class="text-center fw-bold"><?php echo intval( $item->closing_stock ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
