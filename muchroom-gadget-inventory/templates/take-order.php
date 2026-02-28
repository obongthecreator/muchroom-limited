<?php
/**
 * Take Order Form
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$branch = Muchroom_Gadget_Inventory::get_current_branch();

$page_title = 'Take Order';
include MGI_PLUGIN_DIR . 'templates/header.php';

$categories = MGI_Products::get_categories();
?>

<div class="page-header">
    <h1 class="heading">Take Order</h1>
    <a href="<?php echo esc_url( home_url( '/muchroom/' . $branch . '/sales/history/' ) ); ?>" class="pill-btn pill-btn-outline btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
        <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon> Order History
    </a>
</div>

<form id="take-order-form">
    <input type="hidden" id="form-hash" value="" />
    <input type="hidden" id="grand-total-value" value="0" />

    <!-- Product Selection Table -->
    <div class="glass-card mb-24">
        <div class="flex items-center justify-between mb-16 flex-wrap gap-8">
            <h2 class="subheading font-display" style="font-size:18px;">Products</h2>
            <select id="order-category-filter" class="mgi-select" style="width:auto;min-width:180px;">
                <option value="">All Categories</option>
                <?php foreach ( $categories as $cat ) : ?>
                <option value="<?php echo esc_attr( $cat->id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mgi-table-wrap">
            <table class="mgi-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Code</th>
                        <th>Product / Model</th>
                        <th>Cost</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="order-products-body">
                    <tr><td colspan="7" class="text-center">
                        <div class="loading-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    </td></tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-16" style="border-top:1px solid rgba(0,0,0,0.06);padding-top:16px;">
            <span class="font-display fw-800" style="font-size:18px;">Grand Total:</span>
            <span class="font-display fw-800 text-primary" style="font-size:24px;" id="grand-total">₦0</span>
        </div>
    </div>

    <!-- Customer & Payment Section -->
    <div class="glass-card mb-24">
        <h2 class="subheading font-display mb-16" style="font-size:18px;">Customer & Payment</h2>

        <div class="mgi-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:16px;">
            <div class="mgi-form-group">
                <label for="customer-name">Customer Name</label>
                <input type="text" id="customer-name" class="mgi-input" placeholder="Enter customer name" required />
            </div>

            <div class="mgi-form-group">
                <label>Payment Method</label>
                <div class="payment-methods">
                    <button type="button" class="payment-method-btn" data-method="transfer"><iconify-icon icon="solar:card-transfer-linear"></iconify-icon> Transfer/Card</button>
                    <button type="button" class="payment-method-btn" data-method="cash"><iconify-icon icon="solar:wallet-money-linear"></iconify-icon> Cash</button>
                    <button type="button" class="payment-method-btn" data-method="both"><iconify-icon icon="solar:refresh-linear"></iconify-icon> Both</button>
                </div>
            </div>
        </div>

        <!-- Split Payment Fields (shown when "both" is selected) -->
        <div id="payment-split-fields" style="display:none;" class="mt-16">
            <div class="mgi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
                <div class="mgi-form-group">
                    <label for="transfer-amount">Transfer/Card Amount (₦)</label>
                    <input type="number" id="transfer-amount" class="mgi-input" placeholder="0.00" min="0" step="0.01" />
                </div>
                <div class="mgi-form-group">
                    <label for="cash-amount">Cash Amount (₦)</label>
                    <input type="number" id="cash-amount" class="mgi-input" placeholder="0.00" min="0" step="0.01" />
                </div>
            </div>
            <p class="text-muted" style="font-size:12px;margin-top:4px;"><iconify-icon icon="solar:lightbulb-linear"></iconify-icon> Smart fill: adjusting one amount automatically calculates the other.</p>
        </div>

        <div class="mt-16">
            <button type="submit" class="pill-btn beam-btn btn-text" style="width:100%;">
                <iconify-icon icon="solar:check-circle-linear"></iconify-icon> Submit Order
            </button>
        </div>
    </div>
</form>

<!-- Confirmation Modal -->
<div class="mgi-modal-overlay" id="order-confirm-modal">
    <div class="mgi-modal">
        <div class="mgi-modal-header">
            <h3>Confirm Order</h3>
            <button class="mgi-modal-close" onclick="closeModal('order-confirm-modal')">&times;</button>
        </div>
        <div id="order-summary-content"></div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button class="pill-btn beam-btn btn-text" id="confirm-order-btn" style="flex:1;"><iconify-icon icon="solar:check-circle-linear"></iconify-icon> Confirm</button>
            <button class="pill-btn pill-btn-outline btn-text" onclick="closeModal('order-confirm-modal')" style="flex:1;">Cancel</button>
        </div>
    </div>
</div>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
