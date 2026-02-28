/**
 * Main JavaScript - Navigation, clock, page transitions, utilities
 */
document.addEventListener('DOMContentLoaded', function() {
    initClock();
    initNavigation();
    initPageTransition();
    initNoodleBg();
});

/* ---- Digital Clock ---- */
function initClock() {
    var clockEl = document.getElementById('mgi-clock');
    if (!clockEl) return;

    function update() {
        var now = new Date();
        var h = String(now.getHours()).padStart(2, '0');
        var m = String(now.getMinutes()).padStart(2, '0');
        var s = String(now.getSeconds()).padStart(2, '0');
        clockEl.textContent = h + ':' + m + ':' + s;
    }
    update();
    setInterval(update, 1000);
}

/* ---- Mobile Navigation ---- */
function initNavigation() {
    var hamburger = document.getElementById('mgi-hamburger');
    var drawer = document.getElementById('mgi-drawer');
    var overlay = document.getElementById('mgi-drawer-overlay');

    if (!hamburger || !drawer) return;

    hamburger.addEventListener('click', function() {
        drawer.classList.toggle('open');
        if (overlay) overlay.classList.toggle('open');
    });

    if (overlay) {
        overlay.addEventListener('click', function() {
            drawer.classList.remove('open');
            overlay.classList.remove('open');
        });
    }
}

/* ---- Page Transition ---- */
function initPageTransition() {
    var content = document.querySelector('.mgi-content');
    if (content) {
        content.classList.add('page-transition-in');
    }
}

function navigateTo(url) {
    var content = document.querySelector('.mgi-content');
    if (content) {
        content.classList.remove('page-transition-in');
        content.classList.add('page-transition-out');
        setTimeout(function() {
            window.location.href = url;
        }, 350);
    } else {
        window.location.href = url;
    }
}

/* ---- Noodle Background ---- */
function initNoodleBg() {
    var container = document.querySelector('.mgi-noodle-bg');
    if (!container || container.querySelector('svg')) return;

    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 1440 900');
    svg.setAttribute('preserveAspectRatio', 'xMidYMid slice');

    var devices = [
        { icon: '📱', x: 100, y: 120 },
        { icon: '💻', x: 400, y: 200 },
        { icon: '🔊', x: 700, y: 100 },
        { icon: '📽️', x: 1000, y: 250 },
        { icon: '🎧', x: 1300, y: 150 },
        { icon: '⌨️', x: 250, y: 500 },
        { icon: '🖱️', x: 550, y: 600 },
        { icon: '📱', x: 900, y: 500 },
        { icon: '💻', x: 1200, y: 550 },
        { icon: '⚡', x: 150, y: 800 },
        { icon: '🔊', x: 600, y: 780 },
        { icon: '📽️', x: 1050, y: 750 },
    ];

    for (var i = 0; i < devices.length; i++) {
        for (var j = i + 1; j < devices.length; j++) {
            var dx = devices[j].x - devices[i].x;
            var dy = devices[j].y - devices[i].y;
            var dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 450) {
                var line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                var mx = (devices[i].x + devices[j].x) / 2;
                var my = (devices[i].y + devices[j].y) / 2 + (Math.random() - 0.5) * 80;
                line.setAttribute('d', 'M' + devices[i].x + ',' + devices[i].y + ' Q' + mx + ',' + my + ' ' + devices[j].x + ',' + devices[j].y);
                line.setAttribute('class', 'noodle-line');
                svg.appendChild(line);
            }
        }
    }

    devices.forEach(function(d, idx) {
        var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', d.x);
        circle.setAttribute('cy', d.y);
        circle.setAttribute('r', 22);
        circle.setAttribute('class', 'noodle-circle');
        svg.appendChild(circle);

        var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', d.x);
        text.setAttribute('y', d.y + 6);
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('class', 'noodle-icon');
        text.style.animationDelay = (idx * 0.4) + 's';
        text.textContent = d.icon;
        svg.appendChild(text);
    });

    container.appendChild(svg);
}

/* ---- Currency Formatting ---- */
function formatNaira(amount) {
    var num = parseFloat(amount) || 0;
    return '₦' + num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function formatNairaWhole(amount) {
    var num = parseInt(amount) || 0;
    return '₦' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/* ---- Toast Notifications ---- */
function showToast(message, type) {
    type = type || 'info';
    var container = document.querySelector('.mgi-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'mgi-toast-container';
        document.body.appendChild(container);
    }

    var toast = document.createElement('div');
    toast.className = 'mgi-toast toast-' + type;
    var icons = { success: '✓', error: '✕', info: 'ℹ' };
    toast.innerHTML = '<span>' + (icons[type] || 'ℹ') + '</span> ' + message;
    container.appendChild(toast);

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

/* ---- Loading Dots ---- */
function showLoading(container) {
    if (typeof container === 'string') {
        container = document.querySelector(container);
    }
    if (!container) return;
    container.innerHTML = '<div class="loading-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>';
}

/* ---- AJAX Helper ---- */
function mgiAjax(actionType, data, callback) {
    var formData = new FormData();
    formData.append('action', 'mgi_action');
    formData.append('nonce', mgiData.nonce);
    formData.append('action_type', actionType);

    if (data) {
        Object.keys(data).forEach(function(key) {
            var val = data[key];
            if (Array.isArray(val)) {
                val.forEach(function(item, i) {
                    if (typeof item === 'object') {
                        Object.keys(item).forEach(function(k) {
                            formData.append(key + '[' + i + '][' + k + ']', item[k]);
                        });
                    } else {
                        formData.append(key + '[]', item);
                    }
                });
            } else {
                formData.append(key, val);
            }
        });
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', mgiData.ajaxUrl, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                callback(null, response);
            } catch (e) {
                callback('Invalid response');
            }
        } else {
            callback('Request failed');
        }
    };
    xhr.onerror = function() { callback('Network error'); };
    xhr.send(formData);
}

/* ---- Logout ---- */
function doLogout() {
    var branch = window.mgiBranch || 'nsukka';
    mgiAjax('logout', { branch: branch }, function(err, res) {
        if (!err && res.success) {
            window.location.href = res.data.redirect;
        }
    });
}

/* ---- Modal Helper ---- */
function openModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
}

function closeModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

/* ---- Tab Switching ---- */
function switchTab(tabGroup, tabName) {
    var tabs = document.querySelectorAll('[data-tab-group="' + tabGroup + '"]');
    tabs.forEach(function(tab) {
        tab.classList.toggle('active', tab.getAttribute('data-tab') === tabName);
    });
    var contents = document.querySelectorAll('[data-tab-content-group="' + tabGroup + '"]');
    contents.forEach(function(c) {
        c.classList.toggle('active', c.getAttribute('data-tab-content') === tabName);
    });
}
