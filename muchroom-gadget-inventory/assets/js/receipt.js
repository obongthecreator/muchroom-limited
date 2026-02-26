/**
 * Receipt Generation - 80mm thermal printer compatible
 * ESC/POS compatible commands for Bluetooth mobile printers
 */

function generateReceipt(order) {
    var modal = document.getElementById('receipt-modal');
    if (!modal) {
        // Create receipt modal dynamically
        modal = document.createElement('div');
        modal.id = 'receipt-modal';
        modal.className = 'mgi-modal-overlay';
        modal.innerHTML = '<div class="mgi-modal" style="max-width:400px">' +
            '<div class="mgi-modal-header"><h3>Receipt</h3>' +
            '<button class="mgi-modal-close" onclick="closeModal(\'receipt-modal\')">&times;</button></div>' +
            '<div id="receipt-content"></div>' +
            '<div style="margin-top:16px;display:flex;gap:8px;justify-content:center">' +
            '<button class="pill-btn beam-btn" onclick="printReceipt()">🖨️ Print</button>' +
            '<button class="pill-btn pill-btn-outline" onclick="closeModal(\'receipt-modal\')">Close</button>' +
            '</div></div>';
        document.body.appendChild(modal);
    }

    var receiptHtml = buildReceiptHtml(order);
    var content = document.getElementById('receipt-content');
    if (content) content.innerHTML = receiptHtml;

    openModal('receipt-modal');

    // Store receipt data for printing
    window._currentReceipt = order;
}

function buildReceiptHtml(order) {
    var html = '<div class="receipt-container receipt-print-area" id="receipt-printable">';

    // Header
    html += '<div class="receipt-header">';
    html += '<h2>MUCHROOM LIMITED</h2>';
    html += '<p>Gadget Inventory System</p>';
    html += '<p>━━━━━━━━━━━━━━━━━━━━━━</p>';
    html += '</div>';

    // Order Info
    html += '<div class="receipt-info">';
    html += '<div><span>Order #:</span><span>' + (order.order_number || '') + '</span></div>';
    html += '<div><span>Date:</span><span>' + formatReceiptDate(order.created_at) + '</span></div>';
    html += '<div><span>Staff:</span><span>' + (order.staff_name || '') + '</span></div>';
    html += '<div><span>Customer:</span><span>' + (order.customer_name || '') + '</span></div>';
    html += '</div>';

    // Items
    html += '<table class="receipt-items">';
    html += '<thead><tr><th>Item</th><th>Qty</th><th>Price</th><th style="text-align:right">Total</th></tr></thead>';
    html += '<tbody>';

    if (order.items && order.items.length) {
        order.items.forEach(function(item) {
            var name = item.product_name || '';
            if (item.model) name += ' (' + item.model + ')';
            html += '<tr>';
            html += '<td>' + name + '</td>';
            html += '<td>' + item.quantity + '</td>';
            html += '<td>' + formatReceiptAmount(item.price) + '</td>';
            html += '<td class="item-total">' + formatReceiptAmount(item.total) + '</td>';
            html += '</tr>';
        });
    }

    html += '</tbody></table>';

    // Totals
    html += '<div class="receipt-totals">';
    var method = order.payment_method || '';
    if (method === 'both' || method === 'cash') {
        html += '<div><span>Cash:</span><span>' + formatReceiptAmount(order.cash_amount) + '</span></div>';
    }
    if (method === 'both' || method === 'transfer') {
        html += '<div><span>Transfer/Card:</span><span>' + formatReceiptAmount(order.transfer_amount) + '</span></div>';
    }
    html += '<div class="grand-total"><span>GRAND TOTAL:</span><span>' + formatReceiptAmount(order.grand_total) + '</span></div>';
    html += '</div>';

    // Footer
    html += '<div class="receipt-footer">';
    html += '<p>Thank you for your patronage!</p>';
    html += '<p>Muchroom Limited</p>';
    html += '<p>━━━━━━━━━━━━━━━━━━━━━━</p>';
    html += '</div>';

    html += '</div>';
    return html;
}

function formatReceiptDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr);
    var day = String(d.getDate()).padStart(2, '0');
    var mon = String(d.getMonth() + 1).padStart(2, '0');
    var year = d.getFullYear();
    var h = String(d.getHours()).padStart(2, '0');
    var m = String(d.getMinutes()).padStart(2, '0');
    return day + '/' + mon + '/' + year + ' ' + h + ':' + m;
}

function formatReceiptAmount(amount) {
    var num = parseFloat(amount) || 0;
    return '₦' + num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Print receipt - tries Bluetooth ESC/POS printer first, falls back to browser print.
 */
function printReceipt() {
    var order = window._currentReceipt;
    if (!order) return;

    // Try Web Bluetooth ESC/POS printing
    if (navigator.bluetooth) {
        printViaBluetooth(order).catch(function() {
            // Fallback to browser print
            browserPrint();
        });
    } else {
        browserPrint();
    }
}

/**
 * ESC/POS Bluetooth printing for mobile thermal printers.
 */
async function printViaBluetooth(order) {
    try {
        var device = await navigator.bluetooth.requestDevice({
            filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
            optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
        });

        var server = await device.gatt.connect();
        var service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
        var characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');

        var data = buildEscPosData(order);
        await characteristic.writeValue(data);

        showToast('Receipt printed successfully!', 'success');
        server.disconnect();
    } catch (e) {
        console.warn('Bluetooth print failed:', e.message || e);
        throw e;
    }
}

/**
 * Build ESC/POS byte array for thermal printer.
 */
function buildEscPosData(order) {
    var encoder = new TextEncoder();
    var commands = [];

    // ESC/POS Commands
    var ESC = 0x1B;
    var GS = 0x1D;
    var LF = 0x0A;

    // Initialize printer
    commands.push(new Uint8Array([ESC, 0x40]));

    // Center alignment
    commands.push(new Uint8Array([ESC, 0x61, 0x01]));

    // Bold on + Double size
    commands.push(new Uint8Array([ESC, 0x45, 0x01]));
    commands.push(new Uint8Array([GS, 0x21, 0x11]));
    commands.push(encoder.encode('MUCHROOM LIMITED'));
    commands.push(new Uint8Array([LF]));

    // Normal size
    commands.push(new Uint8Array([GS, 0x21, 0x00]));
    commands.push(new Uint8Array([ESC, 0x45, 0x00]));
    commands.push(encoder.encode('Gadget Inventory System'));
    commands.push(new Uint8Array([LF]));
    commands.push(encoder.encode('========================'));
    commands.push(new Uint8Array([LF]));

    // Left alignment
    commands.push(new Uint8Array([ESC, 0x61, 0x00]));

    // Order info
    commands.push(encoder.encode('Order: ' + (order.order_number || '')));
    commands.push(new Uint8Array([LF]));
    commands.push(encoder.encode('Date: ' + formatReceiptDate(order.created_at)));
    commands.push(new Uint8Array([LF]));
    commands.push(encoder.encode('Staff: ' + (order.staff_name || '')));
    commands.push(new Uint8Array([LF]));
    commands.push(encoder.encode('Customer: ' + (order.customer_name || '')));
    commands.push(new Uint8Array([LF]));
    commands.push(encoder.encode('------------------------'));
    commands.push(new Uint8Array([LF]));

    // Column headers
    commands.push(encoder.encode(padRight('Item', 16) + padRight('Qty', 4) + padLeft('Total', 12)));
    commands.push(new Uint8Array([LF]));
    commands.push(encoder.encode('------------------------'));
    commands.push(new Uint8Array([LF]));

    // Items
    if (order.items) {
        order.items.forEach(function(item) {
            var name = (item.product_name || '').substring(0, 16);
            if (item.model) name = (name + ' ' + item.model).substring(0, 16);
            var line = padRight(name, 16) + padRight(String(item.quantity), 4) + padLeft(formatReceiptAmount(item.total), 12);
            commands.push(encoder.encode(line));
            commands.push(new Uint8Array([LF]));
        });
    }

    commands.push(encoder.encode('========================'));
    commands.push(new Uint8Array([LF]));

    // Payment details
    var method = order.payment_method || '';
    if (method === 'both' || method === 'cash') {
        commands.push(encoder.encode(padRight('Cash:', 16) + padLeft(formatReceiptAmount(order.cash_amount), 16)));
        commands.push(new Uint8Array([LF]));
    }
    if (method === 'both' || method === 'transfer') {
        commands.push(encoder.encode(padRight('Transfer:', 16) + padLeft(formatReceiptAmount(order.transfer_amount), 16)));
        commands.push(new Uint8Array([LF]));
    }

    // Grand total - bold
    commands.push(new Uint8Array([ESC, 0x45, 0x01]));
    commands.push(new Uint8Array([GS, 0x21, 0x01]));
    commands.push(encoder.encode(padRight('TOTAL:', 16) + padLeft(formatReceiptAmount(order.grand_total), 16)));
    commands.push(new Uint8Array([LF]));
    commands.push(new Uint8Array([GS, 0x21, 0x00]));
    commands.push(new Uint8Array([ESC, 0x45, 0x00]));

    // Footer - center
    commands.push(new Uint8Array([LF]));
    commands.push(new Uint8Array([ESC, 0x61, 0x01]));
    commands.push(encoder.encode('Thank you for your patronage!'));
    commands.push(new Uint8Array([LF]));
    commands.push(encoder.encode('Muchroom Limited'));
    commands.push(new Uint8Array([LF]));

    // Feed and cut
    commands.push(new Uint8Array([LF, LF, LF]));
    commands.push(new Uint8Array([GS, 0x56, 0x00]));

    // Concatenate all commands
    var totalLength = commands.reduce(function(sum, arr) { return sum + arr.length; }, 0);
    var result = new Uint8Array(totalLength);
    var offset = 0;
    commands.forEach(function(arr) {
        result.set(arr, offset);
        offset += arr.length;
    });

    return result;
}

function padRight(str, len) {
    while (str.length < len) str += ' ';
    return str.substring(0, len);
}

function padLeft(str, len) {
    while (str.length < len) str = ' ' + str;
    return str.substring(0, len);
}

/**
 * Fallback: browser window.print()
 */
function browserPrint() {
    var receiptEl = document.getElementById('receipt-printable');
    if (!receiptEl) return;

    var printWindow = window.open('', '_blank', 'width=350,height=600');
    printWindow.document.write('<html><head><title>Receipt</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body{margin:0;padding:4mm;font-family:"Courier New",monospace;font-size:11px;width:72mm;}');
    printWindow.document.write('.receipt-header{text-align:center;margin-bottom:8px;border-bottom:1px dashed #000;padding-bottom:8px;}');
    printWindow.document.write('.receipt-header h2{font-size:16px;margin:0 0 2px;}');
    printWindow.document.write('.receipt-header p{font-size:10px;margin:1px 0;}');
    printWindow.document.write('.receipt-info{margin-bottom:8px;font-size:10px;}');
    printWindow.document.write('.receipt-info div{display:flex;justify-content:space-between;margin:2px 0;}');
    printWindow.document.write('.receipt-items{width:100%;border-collapse:collapse;margin-bottom:8px;font-size:10px;}');
    printWindow.document.write('.receipt-items th{border-top:1px dashed #000;border-bottom:1px dashed #000;padding:4px 2px;text-align:left;font-size:9px;}');
    printWindow.document.write('.receipt-items td{padding:3px 2px;font-size:10px;}');
    printWindow.document.write('.item-total{text-align:right;}');
    printWindow.document.write('.receipt-totals{border-top:1px dashed #000;padding-top:6px;margin-bottom:8px;}');
    printWindow.document.write('.receipt-totals div{display:flex;justify-content:space-between;margin:3px 0;font-size:11px;}');
    printWindow.document.write('.grand-total{font-weight:bold;font-size:14px;border-top:1px dashed #000;padding-top:4px;margin-top:4px;}');
    printWindow.document.write('.receipt-footer{text-align:center;border-top:1px dashed #000;padding-top:8px;font-size:10px;}');
    printWindow.document.write('@page{size:80mm auto;margin:0;}');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write(receiptEl.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() {
        printWindow.print();
    }, 300);
}

/**
 * Reprint a receipt from history.
 */
function reprintReceipt(orderId) {
    mgiAjax('get_order', { order_id: orderId }, function(err, res) {
        if (err || !res.success) {
            showToast('Failed to load order', 'error');
            return;
        }
        generateReceipt(res.data.order);
    });
}
