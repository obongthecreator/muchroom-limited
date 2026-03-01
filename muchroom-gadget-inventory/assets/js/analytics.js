/**
 * Analytics - Charts and data visualization
 */
document.addEventListener('DOMContentLoaded', function() {
    initAnalytics();
});

function initAnalytics() {
    var page = document.getElementById('analytics-page');
    if (!page) return;

    // Period filter
    var periodFilter = document.getElementById('analytics-period');
    var dateFilter = document.getElementById('analytics-date');

    if (periodFilter) {
        periodFilter.addEventListener('change', function() { loadAnalytics(); });
    }
    if (dateFilter) {
        dateFilter.addEventListener('change', function() { loadAnalytics(); });
    }

    loadAnalytics();
}

function loadAnalytics() {
    var period = document.getElementById('analytics-period');
    var date = document.getElementById('analytics-date');

    var periodVal = period ? period.value : 'daily';
    var dateVal = date ? date.value : '';

    // Show loading state on the top-products list only; chart canvases must stay in DOM.
    var topList = document.getElementById('top-products-list');
    if (topList) showLoading('#top-products-list');

    mgiAjax('get_analytics', { period: periodVal, date: dateVal }, function(err, res) {
        if (err || !res.success) {
            if (topList) topList.innerHTML = '<p class="text-center text-muted">Failed to load analytics</p>';
            return;
        }

        var data = res.data.analytics;
        renderAnalytics(data);
    });
}

function renderAnalytics(data) {
    var container = document.getElementById('analytics-charts');
    if (!container) return;

    // Update KPI cards
    updateKPIs(data);

    // Render charts
    renderSalesChart(data);
    renderCategoryChart(data);
    renderPaymentChart(data);
    renderTopProductsTable(data);
}

function updateKPIs(data) {
    var salesEl = document.getElementById('kpi-total-sales');
    var ordersEl = document.getElementById('kpi-orders');
    var cashEl = document.getElementById('kpi-cash');
    var transferEl = document.getElementById('kpi-transfer');

    if (salesEl) salesEl.textContent = formatNaira(data.total_sales);
    if (ordersEl) ordersEl.textContent = data.orders_count;
    if (cashEl && data.payment_split) cashEl.textContent = formatNaira(data.payment_split.total_cash);
    if (transferEl && data.payment_split) transferEl.textContent = formatNaira(data.payment_split.total_transfer);
}

function renderSalesChart(data) {
    var canvas = document.getElementById('sales-line-chart');
    if (!canvas || typeof Chart === 'undefined') return;

    // Destroy existing chart
    if (window._salesChart) window._salesChart.destroy();

    var labels = (data.daily_sales || []).map(function(d) { return d.sale_date; });
    var totals = (data.daily_sales || []).map(function(d) { return parseFloat(d.total); });
    var cash = (data.daily_sales || []).map(function(d) { return parseFloat(d.cash); });
    var transfer = (data.daily_sales || []).map(function(d) { return parseFloat(d.transfer); });

    window._salesChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Sales',
                    data: totals,
                    borderColor: '#6C63FF',
                    backgroundColor: 'rgba(108,99,255,0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Cash',
                    data: cash,
                    borderColor: '#00C48C',
                    backgroundColor: 'rgba(0,196,140,0.1)',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Transfer',
                    data: transfer,
                    borderColor: '#FFB800',
                    backgroundColor: 'rgba(255,184,0,0.1)',
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function renderCategoryChart(data) {
    var canvas = document.getElementById('category-bar-chart');
    if (!canvas || typeof Chart === 'undefined') return;

    if (window._categoryChart) window._categoryChart.destroy();

    var labels = (data.category_sales || []).map(function(d) { return d.category_name; });
    var revenues = (data.category_sales || []).map(function(d) { return parseFloat(d.total_revenue); });
    var quantities = (data.category_sales || []).map(function(d) { return parseInt(d.total_qty); });

    var colors = ['#6C63FF', '#FF6B6B', '#00C48C', '#FFB800', '#8B85FF', '#FF4757', '#2ED573'];

    window._categoryChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (₦)',
                data: revenues,
                backgroundColor: colors.slice(0, labels.length).map(function(c) { return c + '33'; }),
                borderColor: colors.slice(0, labels.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function renderPaymentChart(data) {
    var canvas = document.getElementById('payment-pie-chart');
    if (!canvas || typeof Chart === 'undefined' || !data.payment_split) return;

    if (window._paymentChart) window._paymentChart.destroy();

    window._paymentChart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Transfer/Card'],
            datasets: [{
                data: [
                    parseFloat(data.payment_split.total_cash),
                    parseFloat(data.payment_split.total_transfer)
                ],
                backgroundColor: ['#00C48C', '#6C63FF'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

function renderTopProductsTable(data) {
    var container = document.getElementById('top-products-list');
    if (!container) return;

    if (!data.top_products || !data.top_products.length) {
        container.innerHTML = '<p class="text-center text-muted">No sales data</p>';
        return;
    }

    var html = '<table class="mgi-table"><thead><tr><th>Product</th><th>Category</th><th>Qty Sold</th><th>Revenue</th></tr></thead><tbody>';
    data.top_products.forEach(function(p) {
        html += '<tr>';
        html += '<td>' + (p.product_name || '') + '</td>';
        html += '<td>' + (p.category_name || '') + '</td>';
        html += '<td>' + p.total_qty + '</td>';
        html += '<td class="amount">' + formatNaira(p.total_revenue) + '</td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    container.innerHTML = html;
}
