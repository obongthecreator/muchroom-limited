/**
 * Landing page - Letter-by-letter animation & noodle background
 */
document.addEventListener('DOMContentLoaded', function() {
    // Letter-by-letter reveal animation
    initLetterReveal();
    // Noodle connection background
    initNoodleBackground();
});

function initLetterReveal() {
    var elements = document.querySelectorAll('.letter-reveal');
    elements.forEach(function(el) {
        var text = el.textContent;
        el.textContent = '';
        el.style.visibility = 'visible';

        var chars = text.split('');
        chars.forEach(function(char, i) {
            var span = document.createElement('span');
            if (char === ' ') {
                span.className = 'letter-space';
            } else {
                span.className = 'letter';
                span.textContent = char;
                span.style.animationDelay = (i * 80) + 'ms';
            }
            el.appendChild(span);
        });
    });
}

function initNoodleBackground() {
    var container = document.querySelector('.mgi-noodle-bg');
    if (!container) return;

    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 1440 900');
    svg.setAttribute('preserveAspectRatio', 'xMidYMid slice');

    var devices = [
        { icon: '⬡', x: 120, y: 150 },
        { icon: '◇', x: 350, y: 80 },
        { icon: '○', x: 600, y: 200 },
        { icon: '△', x: 850, y: 100 },
        { icon: '□', x: 1100, y: 180 },
        { icon: '⬟', x: 200, y: 450 },
        { icon: '◎', x: 500, y: 550 },
        { icon: '⬡', x: 750, y: 480 },
        { icon: '◇', x: 1000, y: 400 },
        { icon: '✦', x: 1250, y: 300 },
        { icon: '○', x: 300, y: 700 },
        { icon: '△', x: 700, y: 750 },
        { icon: '□', x: 1100, y: 650 },
    ];

    // Draw connection lines between nearby devices
    for (var i = 0; i < devices.length; i++) {
        for (var j = i + 1; j < devices.length; j++) {
            var dx = devices[j].x - devices[i].x;
            var dy = devices[j].y - devices[i].y;
            var dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 400) {
                var line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                var mx = (devices[i].x + devices[j].x) / 2;
                var my = (devices[i].y + devices[j].y) / 2 + (Math.random() - 0.5) * 60;
                line.setAttribute('d', 'M' + devices[i].x + ',' + devices[i].y + ' Q' + mx + ',' + my + ' ' + devices[j].x + ',' + devices[j].y);
                line.setAttribute('class', 'noodle-line');
                line.style.animationDelay = (i * 0.5) + 's';
                svg.appendChild(line);
            }
        }
    }

    // Draw circles and icons
    devices.forEach(function(d, idx) {
        var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', d.x);
        circle.setAttribute('cy', d.y);
        circle.setAttribute('r', 25);
        circle.setAttribute('class', 'noodle-circle');
        svg.appendChild(circle);

        var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', d.x);
        text.setAttribute('y', d.y + 6);
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('class', 'noodle-icon');
        text.style.animationDelay = (idx * 0.3) + 's';
        text.textContent = d.icon;
        svg.appendChild(text);
    });

    container.appendChild(svg);
}
