/**
 * Inventory & Import Management
 */
document.addEventListener('DOMContentLoaded', function() {
    initInventoryPage();
    initImportForm();
});

function initInventoryPage() {
    var page = document.getElementById('inventory-page');
    if (!page) return;

    var dateFilter = document.getElementById('inventory-date');
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            loadInventory(this.value);
        });
        // Load today's inventory
        loadInventory(dateFilter.value);
    }
}

function loadInventory(date) {
    var tableBody = document.getElementById('inventory-body');
    if (!tableBody) return;

    showLoading('#inventory-body');

    mgiAjax('get_inventory', { date: date || '' }, function(err, res) {
        if (err || !res.success) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Failed to load inventory</td></tr>';
            return;
        }

        var items = res.data.inventory;
        if (!items || !items.length) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No inventory data for this date</td></tr>';
            return;
        }

        var html = '';
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

        tableBody.innerHTML = html;
    });
}

function initImportForm() {
    var form = document.getElementById('import-form');
    if (!form) return;

    // Category change loads products
    var catSelect = document.getElementById('import-category');
    if (catSelect) {
        catSelect.addEventListener('change', function() {
            loadImportProducts(this.value);
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitImport();
    });
}

function loadImportProducts(categoryId) {
    var productSelect = document.getElementById('import-product');
    if (!productSelect) return;

    productSelect.innerHTML = '<option value="">Loading...</option>';

    var data = {};
    if (categoryId) data.category_id = categoryId;

    mgiAjax('get_products', data, function(err, res) {
        if (err || !res.success) {
            productSelect.innerHTML = '<option value="">Failed to load</option>';
            return;
        }

        var products = res.data.products;
        var html = '<option value="">Select product</option>';
        products.forEach(function(p) {
            var name = p.name;
            if (p.model) name += ' (' + p.model + ')';
            html += '<option value="' + p.id + '">' + name + ' [' + p.product_code + ']</option>';
        });
        productSelect.innerHTML = html;
    });
}

function submitImport() {
    var productId = document.getElementById('import-product').value;
    var quantity = document.getElementById('import-quantity').value;
    var note = document.getElementById('import-note') ? document.getElementById('import-note').value : '';

    if (!productId) {
        showToast('Please select a product', 'error');
        return;
    }
    if (!quantity || parseInt(quantity) <= 0) {
        showToast('Please enter a valid quantity', 'error');
        return;
    }

    var submitBtn = document.querySelector('#import-form button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Importing...';
    }

    mgiAjax('import_stock', {
        product_id: productId,
        quantity: quantity,
        note: note
    }, function(err, res) {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Import Stock';
        }

        if (err || !res.success) {
            showToast(res && res.data ? res.data.message : 'Import failed', 'error');
            return;
        }

        showToast('Stock imported successfully!', 'success');

        // Reset form
        document.getElementById('import-product').value = '';
        document.getElementById('import-quantity').value = '';
        if (document.getElementById('import-note')) document.getElementById('import-note').value = '';

        // Reload inventory if on inventory page
        var invDate = document.getElementById('inventory-date');
        if (invDate) loadInventory(invDate.value);

        // Reload imports list if exists
        if (typeof loadImportHistory === 'function') loadImportHistory();
    });
}

function loadImportHistory(date) {
    var tableBody = document.getElementById('imports-body');
    if (!tableBody) return;

    showLoading('#imports-body');

    mgiAjax('get_imports', { date: date || '' }, function(err, res) {
        if (err || !res.success) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Failed to load imports</td></tr>';
            return;
        }

        var imports = res.data.imports;
        if (!imports || !imports.length) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No imports found</td></tr>';
            return;
        }

        var html = '';
        imports.forEach(function(imp) {
            html += '<tr>';
            html += '<td>' + (imp.product_name || '') + '</td>';
            html += '<td>' + (imp.product_code || '') + '</td>';
            html += '<td>' + (imp.category_name || '') + '</td>';
            html += '<td>' + imp.quantity + '</td>';
            html += '<td>' + (imp.staff_name || '') + '</td>';
            html += '<td>' + (imp.created_at || '') + '</td>';
            html += '</tr>';
        });

        tableBody.innerHTML = html;
    });
}
