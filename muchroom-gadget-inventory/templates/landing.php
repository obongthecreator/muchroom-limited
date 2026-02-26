<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muchroom Limited - Gadget Inventory System</title>
    <?php wp_head(); ?>
</head>
<body class="mgi-page">

<div class="mgi-noodle-bg"></div>
<div class="landing-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
</div>

<div class="landing-page">
    <div class="landing-hero">
        <div class="hero-badge">⚡ Gadget Inventory Management</div>

        <h1 class="letter-reveal heading" style="visibility:hidden;">Muchroom Limited</h1>

        <p class="hero-subtitle body-text">
            Complete gadget inventory management system — track sales, manage stock, generate receipts, and analyze performance in real-time.
        </p>

        <div class="hero-actions">
            <a href="<?php echo esc_url( home_url( '/muchroom/login/' ) ); ?>" class="pill-btn beam-btn btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
                🔐 Staff Login
            </a>
            <a href="#features" class="pill-btn pill-btn-glass btn-text">
                ✨ View Features
            </a>
        </div>
    </div>

    <div class="landing-features stagger-in" id="features">
        <div class="glass-card landing-feature-card">
            <span class="feature-icon">📦</span>
            <h3>Inventory Tracking</h3>
            <p>Real-time stock management with opening and closing values, automated imports and sales tracking.</p>
        </div>
        <div class="glass-card landing-feature-card">
            <span class="feature-icon">🧾</span>
            <h3>Smart Orders</h3>
            <p>Intelligent order forms with automatic calculations, split payments, and 80mm thermal receipt printing.</p>
        </div>
        <div class="glass-card landing-feature-card">
            <span class="feature-icon">📊</span>
            <h3>Analytics Dashboard</h3>
            <p>Comprehensive analytics with pie charts, graphs, and filters for daily, weekly, monthly, and yearly views.</p>
        </div>
        <div class="glass-card landing-feature-card">
            <span class="feature-icon">💰</span>
            <h3>Financial Summary</h3>
            <p>Complete financial overview with cash tracking, transfer records, and automated calculations.</p>
        </div>
    </div>

    <div class="landing-footer">
        <p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Muchroom Limited. All rights reserved.</p>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
