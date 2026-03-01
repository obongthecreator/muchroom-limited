/**
 * Order Management - Take order form logic with smart payment fields
 */
document.addEventListener('DOMContentLoaded', function() {
    initOrderForm();
});

function initOrderForm() {
    var form = document.getElementById('take-order-form');
    if (!form) return;

    // Generate form hash for duplicate prevention
    var formHash = 'order_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    var hashField = document.getElementById('form-hash');
    if (hashField) hashField.value = formHash;

    // Category filter change
    var catFilter = document.getElementById('order-category-filter');
    if (catFilter) {
        catFilter.addEventListener('change', function() {
            loadOrderProducts(this.value);
        });
    }

    // Payment method selection
    initPaymentMethods();

    // Submit form
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitOrder();
    });

    // Load initial products
    loadOrderProducts('');
}

function loadOrderProducts(categoryId) {
    var tableBody = document.getElementById('order-products-body');
    if (!tableBody) return;

    showLoading('#order-products-body');

    var data = {};
    if (categoryId) data.category_id = categoryId;

    mgiAjax('get_products', data, function(err, res) {
        if (err || !res.success) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Failed to load products</td></tr>';
            return;
        }

        var products = res.data.products;
        if (!products.length) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No products found</td></tr>';
            return;
        }

        var html = '';
        products.forEach(function(p) {
            var displayName = p.name;
            if (p.model) displayName += ' (' + p.model + ')';

            html += '<tr data-product-id="' + p.id + '">';
            html += '<td>' + escapeHtml(p.category_name || '') + '</td>';
            html += '<td>' + escapeHtml(p.product_code) + '</td>';
            html += '<td>' + escapeHtml(displayName) + '</td>';
            html += '<td class="amount">' + formatNaira(p.cost) + '</td>';
            html += '<td class="amount">' + formatNaira(p.price) + '</td>';
            html += '<td><input type="number" class="mgi-input qty-input" min="0" value="0" data-price="' + p.price + '" data-product-id="' + p.id + '" style="width:80px" /></td>';
            html += '<td class="amount row-total">₦0</td>';
            html += '</tr>';
        });

        tableBody.innerHTML = html;

        // Attach quantity change listeners
        var qtyInputs = tableBody.querySelectorAll('.qty-input');
        qtyInputs.forEach(function(input) {
            // Select all on focus so typing replaces the "0" instead of appending
            input.addEventListener('focus', function() {
                this.select();
            });
            input.addEventListener('input', function() {
                // Strip leading zeros (e.g. "03" → "3")
                if (this.value.length > 1 && this.value.charAt(0) === '0') {
                    this.value = parseInt(this.value, 10) || 0;
                }
                updateRowTotal(this);
                updateGrandTotal();
            });
        });
    });
}

function updateRowTotal(input) {
    var qty = parseInt(input.value) || 0;
    var price = parseFloat(input.getAttribute('data-price')) || 0;
    var total = qty * price;
    var row = input.closest('tr');
    var totalCell = row.querySelector('.row-total');
    if (totalCell) {
        totalCell.textContent = formatNaira(total);
    }
}

function updateGrandTotal() {
    var total = 0;
    var qtyInputs = document.querySelectorAll('#order-products-body .qty-input');
    qtyInputs.forEach(function(input) {
        var qty = parseInt(input.value) || 0;
        var price = parseFloat(input.getAttribute('data-price')) || 0;
        total += qty * price;
    });

    var grandTotalEl = document.getElementById('grand-total');
    if (grandTotalEl) grandTotalEl.textContent = formatNaira(total);

    var grandTotalInput = document.getElementById('grand-total-value');
    if (grandTotalInput) grandTotalInput.value = total;

    // Auto-fill payment amounts if 'both' is selected
    var method = getSelectedPaymentMethod();
    if (method === 'both') {
        autoFillPaymentSplit(total);
    }
}

function initPaymentMethods() {
    var buttons = document.querySelectorAll('.payment-method-btn');
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            buttons.forEach(function(b) { b.classList.remove('selected'); });
            this.classList.add('selected');

            var method = this.getAttribute('data-method');
            var splitFields = document.getElementById('payment-split-fields');
            if (splitFields) {
                splitFields.style.display = method === 'both' ? 'block' : 'none';
            }

            if (method === 'both') {
                var total = parseFloat(document.getElementById('grand-total-value').value) || 0;
                autoFillPaymentSplit(total);
            }
        });
    });

    // Smart split: when transfer amount changes, auto-fill cash
    var transferInput = document.getElementById('transfer-amount');
    var cashInput = document.getElementById('cash-amount');

    if (transferInput) {
        transferInput.addEventListener('input', function() {
            var total = parseFloat(document.getElementById('grand-total-value').value) || 0;
            var transfer = parseFloat(this.value) || 0;
            if (transfer > total) {
                this.value = total;
                transfer = total;
            }
            if (cashInput) {
                cashInput.value = (total - transfer).toFixed(2);
            }
        });
    }

    if (cashInput) {
        cashInput.addEventListener('input', function() {
            var total = parseFloat(document.getElementById('grand-total-value').value) || 0;
            var cash = parseFloat(this.value) || 0;
            if (cash > total) {
                this.value = total;
                cash = total;
            }
            if (transferInput) {
                transferInput.value = (total - cash).toFixed(2);
            }
        });
    }
}

function autoFillPaymentSplit(total) {
    var transferInput = document.getElementById('transfer-amount');
    var cashInput = document.getElementById('cash-amount');
    if (transferInput && cashInput) {
        var transfer = parseFloat(transferInput.value) || 0;
        if (transfer > total) transfer = total;
        cashInput.value = (total - transfer).toFixed(2);
    }
}

function getSelectedPaymentMethod() {
    var selected = document.querySelector('.payment-method-btn.selected');
    return selected ? selected.getAttribute('data-method') : '';
}

function submitOrder() {
    var customerName = document.getElementById('customer-name').value.trim();
    var paymentMethod = getSelectedPaymentMethod();
    var grandTotal = parseFloat(document.getElementById('grand-total-value').value) || 0;

    if (!customerName) {
        showToast('Please enter customer name', 'error');
        return;
    }
    if (!paymentMethod) {
        showToast('Please select a payment method', 'error');
        return;
    }
    if (grandTotal <= 0) {
        showToast('Please add items to the order', 'error');
        return;
    }

    // Collect items
    var items = [];
    var qtyInputs = document.querySelectorAll('#order-products-body .qty-input');
    qtyInputs.forEach(function(input) {
        var qty = parseInt(input.value) || 0;
        if (qty > 0) {
            items.push({
                product_id: input.getAttribute('data-product-id'),
                quantity: qty
            });
        }
    });

    if (!items.length) {
        showToast('Please add at least one item', 'error');
        return;
    }

    // Payment validation
    var cashAmount = 0;
    var transferAmount = 0;

    if (paymentMethod === 'cash') {
        cashAmount = grandTotal;
    } else if (paymentMethod === 'transfer') {
        transferAmount = grandTotal;
    } else if (paymentMethod === 'both') {
        cashAmount = parseFloat(document.getElementById('cash-amount').value) || 0;
        transferAmount = parseFloat(document.getElementById('transfer-amount').value) || 0;
        var paymentTotal = cashAmount + transferAmount;
        if (Math.abs(paymentTotal - grandTotal) > 0.01) {
            showToast('Cash + Transfer must equal the grand total (₦' + grandTotal.toLocaleString() + ')', 'error');
            return;
        }
    }

    // Show confirmation dialog
    showOrderConfirmation({
        customer_name: customerName,
        payment_method: paymentMethod,
        cash_amount: cashAmount,
        transfer_amount: transferAmount,
        grand_total: grandTotal,
        items: items,
        form_hash: document.getElementById('form-hash').value
    });
}

function showOrderConfirmation(orderData) {
    var modal = document.getElementById('order-confirm-modal');
    if (!modal) return;

    var summaryHtml = '<div style="margin-bottom:16px">';
    summaryHtml += '<p><strong>Customer:</strong> ' + escapeHtml(orderData.customer_name) + '</p>';
    summaryHtml += '<p><strong>Payment:</strong> ' + escapeHtml(orderData.payment_method) + '</p>';
    if (orderData.payment_method === 'both') {
        summaryHtml += '<p><strong>Cash:</strong> ' + formatNaira(orderData.cash_amount) + '</p>';
        summaryHtml += '<p><strong>Transfer:</strong> ' + formatNaira(orderData.transfer_amount) + '</p>';
    }
    summaryHtml += '<p><strong>Grand Total:</strong> ' + formatNaira(orderData.grand_total) + '</p>';
    summaryHtml += '<p><strong>Items:</strong> ' + orderData.items.length + ' product(s)</p>';
    summaryHtml += '</div>';

    var summaryEl = document.getElementById('order-summary-content');
    if (summaryEl) summaryEl.innerHTML = summaryHtml;

    var confirmBtn = document.getElementById('confirm-order-btn');
    if (confirmBtn) {
        // Remove old event listeners by cloning
        var newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        newBtn.addEventListener('click', function() {
            this.disabled = true;
            this.textContent = 'Submitting...';
            processOrder(orderData);
        });
    }

    openModal('order-confirm-modal');
}

function processOrder(orderData) {
    mgiAjax('create_order', orderData, function(err, res) {
        closeModal('order-confirm-modal');

        if (err || !res.success) {
            showToast(res && res.data ? res.data.message : 'Failed to submit order', 'error');
            var btn = document.getElementById('confirm-order-btn');
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Confirm Order';
            }
            return;
        }

        showToast('Order submitted successfully!', 'success');

        // Generate and show receipt
        if (typeof generateReceipt === 'function') {
            generateReceipt(res.data.order);
        }

        // Disable form to prevent resubmission
        var form = document.getElementById('take-order-form');
        if (form) {
            var inputs = form.querySelectorAll('input, select, button');
            inputs.forEach(function(i) { i.disabled = true; });
        }
    });
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
