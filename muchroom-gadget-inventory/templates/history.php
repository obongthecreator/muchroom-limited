<?php
/**
 * History Page - Universal history viewer with filtering and receipt reprint
 * Used for sales-history, inventory-history, import-history, financial-history
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$branch = Muchroom_Gadget_Inventory::get_current_branch();

$mgi_page    = get_query_var( 'mgi_page' );
$history_type = str_replace( '-history', '', $mgi_page );

$titles = array(
    'sales'     => 'Sales History',
    'inventory' => 'Inventory History',
    'import'    => 'Import History',
    'financial' => 'Financial History',
);

$page_title = isset( $titles[ $history_type ] ) ? $titles[ $history_type ] : 'History';
include MGI_PLUGIN_DIR . 'templates/header.php';

$today = current_time( 'Y-m-d' );
?>

<div class="page-header">
    <h1 class="heading"><?php echo esc_html( $page_title ); ?></h1>
    <a href="<?php echo esc_url( home_url( '/muchroom/' . $branch . '/' . $history_type . '/' ) ); ?>" class="pill-btn pill-btn-outline btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
        <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Back to <?php echo esc_html( ucfirst( $history_type ) ); ?>
    </a>
</div>

<div class="glass-card mb-24">
    <div class="filter-bar mb-16">
        <div class="mgi-form-group" style="margin:0;">
            <label style="margin-bottom:4px;">Start Date</label>
            <input type="date" id="history-start-date" class="mgi-input" value="<?php echo esc_attr( $today ); ?>" />
        </div>
        <div class="mgi-form-group" style="margin:0;">
            <label style="margin-bottom:4px;">End Date</label>
            <input type="date" id="history-end-date" class="mgi-input" value="<?php echo esc_attr( $today ); ?>" />
        </div>
        <button class="pill-btn beam-btn btn-text" onclick="loadHistory()" style="align-self:flex-end;"><iconify-icon icon="solar:magnifer-linear"></iconify-icon> Filter</button>
    </div>

    <div id="history-content">
        <div class="loading-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
    </div>
</div>

<script>
var historyType = '<?php echo esc_js( $history_type ); ?>';

document.addEventListener('DOMContentLoaded', function() {
    loadHistory();
});

function loadHistory() {
    var start = document.getElementById('history-start-date').value;
    var end = document.getElementById('history-end-date').value;
    var container = document.getElementById('history-content');

    showLoading('#history-content');

    if (historyType === 'sales') {
        loadSalesHistory(start, end, container);
    } else if (historyType === 'inventory') {
        loadInventoryHistory(start, container);
    } else if (historyType === 'import') {
        loadImportHistoryData(start, container);
    } else if (historyType === 'financial') {
        loadFinancialHistory(start, end, container);
    }
}

function loadSalesHistory(start, end, container) {
    // Load by start date
    mgiAjax('get_orders', { date: start }, function(err, res) {
        if (err || !res.success) {
            container.innerHTML = '<p class="text-center text-muted">Failed to load</p>';
            return;
        }

        var orders = res.data.orders;
        if (!orders || !orders.length) {
            container.innerHTML = '<p class="text-center text-muted">No sales found for this date</p>';
            return;
        }

        var html = '<div class="mgi-table-wrap"><table class="mgi-table">';
        html += '<thead><tr><th>Order #</th><th>Customer</th><th>Payment</th><th>Total</th><th>Staff</th><th>Time</th><th>Action</th></tr></thead><tbody>';

        orders.forEach(function(o) {
            html += '<tr>';
            html += '<td><strong>' + o.order_number + '</strong></td>';
            html += '<td>' + o.customer_name + '</td>';
            html += '<td>' + o.payment_method + '</td>';
            html += '<td class="amount fw-bold">' + formatNaira(o.grand_total) + '</td>';
            html += '<td>' + (o.staff_name || '') + '</td>';
            html += '<td>' + (o.created_at || '') + '</td>';
            html += '<td><button class="pill-btn pill-btn-glass btn-text" style="padding:4px 12px;font-size:11px;" onclick="reprintReceipt(' + o.id + ')"><iconify-icon icon="solar:printer-linear"></iconify-icon></button></td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    });
}

function loadInventoryHistory(date, container) {
    mgiAjax('get_inventory', { date: date }, function(err, res) {
        if (err || !res.success) {
            container.innerHTML = '<p class="text-center text-muted">Failed to load</p>';
            return;
        }

        var items = res.data.inventory;
        if (!items || !items.length) {
            container.innerHTML = '<p class="text-center text-muted">No inventory data for this date</p>';
            return;
        }

        var html = '<div class="mgi-table-wrap"><table class="mgi-table">';
        html += '<thead><tr><th>Category</th><th>Code</th><th>Product</th><th>Opening</th><th>Imported</th><th>Sold</th><th>Closing</th></tr></thead><tbody>';

        items.forEach(function(item) {
            html += '<tr>';
            html += '<td>' + (item.category_name || '') + '</td>';
            html += '<td>' + (item.product_code || '') + '</td>';
            html += '<td>' + (item.product_name || '') + '</td>';
            html += '<td class="text-center">' + item.opening_stock + '</td>';
            html += '<td class="text-center text-success">' + item.imported + '</td>';
            html += '<td class="text-center text-danger">' + item.sold + '</td>';
            html += '<td class="text-center fw-bold">' + item.closing_stock + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    });
}

function loadImportHistoryData(date, container) {
    mgiAjax('get_imports', { date: date }, function(err, res) {
        if (err || !res.success) {
            container.innerHTML = '<p class="text-center text-muted">Failed to load</p>';
            return;
        }

        var imports = res.data.imports;
        if (!imports || !imports.length) {
            container.innerHTML = '<p class="text-center text-muted">No imports found for this date</p>';
            return;
        }

        var html = '<div class="mgi-table-wrap"><table class="mgi-table">';
        html += '<thead><tr><th>Product</th><th>Code</th><th>Category</th><th>Quantity</th><th>Staff</th><th>Time</th></tr></thead><tbody>';

        imports.forEach(function(imp) {
            html += '<tr>';
            html += '<td>' + (imp.product_name || '') + '</td>';
            html += '<td>' + (imp.product_code || '') + '</td>';
            html += '<td>' + (imp.category_name || '') + '</td>';
            html += '<td class="fw-bold text-success">' + imp.quantity + '</td>';
            html += '<td>' + (imp.staff_name || '') + '</td>';
            html += '<td>' + (imp.created_at || '') + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    });
}

function loadFinancialHistory(start, end, container) {
    mgiAjax('get_financial_history', { start_date: start, end_date: end }, function(err, res) {
        if (err || !res.success) {
            container.innerHTML = '<p class="text-center text-muted">Failed to load</p>';
            return;
        }

        var history = res.data.history;
        if (!history || !history.length) {
            container.innerHTML = '<p class="text-center text-muted">No financial data found</p>';
            return;
        }

        var html = '<div class="mgi-table-wrap"><table class="mgi-table">';
        html += '<thead><tr><th>Date</th><th>Total Sales</th><th>Transfers</th><th>Cash</th><th>Old Cash</th><th>Cash Left</th></tr></thead><tbody>';

        history.forEach(function(f) {
            html += '<tr>';
            html += '<td><strong>' + f.date + '</strong></td>';
            html += '<td class="amount">' + formatNaira(f.total_sales) + '</td>';
            html += '<td class="amount">' + formatNaira(f.transfer_total) + '</td>';
            html += '<td class="amount">' + formatNaira(f.cash_total) + '</td>';
            html += '<td class="amount">' + formatNaira(f.old_cash) + '</td>';
            html += '<td class="amount fw-bold text-primary">' + formatNaira(f.cash_left) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    });
}
</script>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
