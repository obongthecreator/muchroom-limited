<?php
/**
 * Import Stock Page
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$page_title = 'Import Stock';
include MGI_PLUGIN_DIR . 'templates/header.php';

$categories = MGI_Products::get_categories();
$today      = current_time( 'Y-m-d' );
$imports    = MGI_Inventory::get_imports( $today );
?>

<div class="page-header">
    <h1 class="heading">Import Stock</h1>
    <div class="flex gap-8">
        <a href="<?php echo esc_url( home_url( '/muchroom/import/history/' ) ); ?>" class="pill-btn pill-btn-outline btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
            📋 Import History
        </a>
        <a href="<?php echo esc_url( home_url( '/muchroom/inventory/' ) ); ?>" class="pill-btn pill-btn-glass btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
            📦 View Inventory
        </a>
    </div>
</div>

<!-- Import Form -->
<div class="glass-card mb-24">
    <h2 class="font-display mb-16" style="font-size:18px;">Add Import</h2>

    <form id="import-form">
        <div class="mgi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
            <div class="mgi-form-group">
                <label for="import-category">Category</label>
                <select id="import-category" class="mgi-select">
                    <option value="">Select category</option>
                    <?php foreach ( $categories as $cat ) : ?>
                    <option value="<?php echo esc_attr( $cat->id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mgi-form-group">
                <label for="import-product">Product</label>
                <select id="import-product" class="mgi-select">
                    <option value="">Select category first</option>
                </select>
            </div>

            <div class="mgi-form-group">
                <label for="import-quantity">Quantity</label>
                <input type="number" id="import-quantity" class="mgi-input" min="1" placeholder="Enter quantity" required />
            </div>

            <div class="mgi-form-group">
                <label for="import-note">Note (optional)</label>
                <input type="text" id="import-note" class="mgi-input" placeholder="Import note" />
            </div>
        </div>

        <div class="mt-16">
            <button type="submit" class="pill-btn beam-btn btn-text">📥 Import Stock</button>
        </div>
    </form>
</div>

<!-- Today's Imports -->
<div class="glass-card">
    <h2 class="font-display mb-16" style="font-size:18px;">Today's Imports</h2>

    <div class="mgi-table-wrap">
        <table class="mgi-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Code</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Staff</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody id="imports-body">
                <?php if ( empty( $imports ) ) : ?>
                <tr><td colspan="6" class="text-center text-muted">No imports today</td></tr>
                <?php else : ?>
                    <?php foreach ( $imports as $imp ) : ?>
                    <tr>
                        <td><?php echo esc_html( $imp->product_name ); ?></td>
                        <td><?php echo esc_html( $imp->product_code ); ?></td>
                        <td><?php echo esc_html( $imp->category_name ); ?></td>
                        <td class="fw-bold text-success"><?php echo intval( $imp->quantity ); ?></td>
                        <td><?php echo esc_html( $imp->staff_name ); ?></td>
                        <td><?php echo esc_html( gmdate( 'H:i', strtotime( $imp->created_at ) ) ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
