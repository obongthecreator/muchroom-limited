<?php if ( ! MGI_Shortcodes::is_shortcode_mode() ) : ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( Muchroom_Gadget_Inventory::get_branch_name() ); ?> - Muchroom Limited</title>
    <?php wp_head(); ?>
</head>
<body class="mgi-page">
<?php endif; ?>

<?php $branch = Muchroom_Gadget_Inventory::get_current_branch(); ?>

<div class="mgi-noodle-bg"></div>
<div class="landing-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
</div>

<div class="landing-page">
    <div class="landing-hero">
        <div class="hero-badge"><iconify-icon icon="solar:bolt-linear"></iconify-icon> <?php echo esc_html( Muchroom_Gadget_Inventory::get_branch_name() ); ?></div>

        <h1 class="letter-reveal heading" style="visibility:hidden;">Muchroom Limited</h1>

        <p class="hero-subtitle body-text">
            Complete gadget inventory management system — track sales, manage stock, generate receipts, and analyze performance in real-time.
        </p>

        <div class="hero-actions">
            <a href="<?php echo esc_url( home_url( '/muchroom/' . $branch . '/login/' ) ); ?>" class="pill-btn beam-btn btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
                <iconify-icon icon="solar:lock-keyhole-linear"></iconify-icon> Staff Login
            </a>
            <a href="<?php echo esc_url( home_url( '/muchroom/' ) ); ?>" class="pill-btn pill-btn-glass btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
                <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> All Branches
            </a>
        </div>
    </div>

    <div class="landing-features stagger-in" id="features">
        <div class="glass-card landing-feature-card">
            <span class="feature-icon"><iconify-icon icon="solar:box-linear"></iconify-icon></span>
            <h3>Inventory Tracking</h3>
            <p>Real-time stock management with opening and closing values, automated imports and sales tracking.</p>
        </div>
        <div class="glass-card landing-feature-card">
            <span class="feature-icon"><iconify-icon icon="solar:document-text-linear"></iconify-icon></span>
            <h3>Smart Orders</h3>
            <p>Intelligent order forms with automatic calculations, split payments, and 80mm thermal receipt printing.</p>
        </div>
        <div class="glass-card landing-feature-card">
            <span class="feature-icon"><iconify-icon icon="solar:chart-2-linear"></iconify-icon></span>
            <h3>Analytics Dashboard</h3>
            <p>Comprehensive analytics with pie charts, graphs, and filters for daily, weekly, monthly, and yearly views.</p>
        </div>
        <div class="glass-card landing-feature-card">
            <span class="feature-icon"><iconify-icon icon="solar:money-bag-linear"></iconify-icon></span>
            <h3>Financial Summary</h3>
            <p>Complete financial overview with cash tracking, transfer records, and automated calculations.</p>
        </div>
    </div>

    <div class="landing-footer">
        <p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Muchroom Limited. All rights reserved.</p>
    </div>
</div>

<?php if ( ! MGI_Shortcodes::is_shortcode_mode() ) : ?>
<?php wp_footer(); ?>
</body>
</html>
<?php endif; ?>
