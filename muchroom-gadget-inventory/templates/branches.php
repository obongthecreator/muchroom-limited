<?php
/**
 * Branch Selection Dashboard - Choose a location before accessing inventory
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$branches = Muchroom_Gadget_Inventory::get_branches();
?>
<?php if ( empty( $GLOBALS['mgi_shortcode_mode'] ) ) : ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muchroom Limited - Select Branch</title>
    <?php wp_head(); ?>
</head>
<body class="mgi-page">
<?php endif; ?>

<div class="mgi-noodle-bg"></div>
<div class="landing-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
</div>

<div class="landing-page">
    <div class="landing-hero" style="flex:0;padding-bottom:20px;">
        <div class="hero-badge">⚡ Gadget Inventory Management</div>

        <h1 class="letter-reveal heading" style="visibility:hidden;">Muchroom Limited</h1>

        <p class="hero-subtitle body-text">
            Select a branch location to access the inventory management system.
        </p>
    </div>

    <!-- Branch Selection Grid -->
    <div class="branch-grid stagger-in" style="position:relative;z-index:1;">
        <?php foreach ( $branches as $slug => $branch ) : ?>
            <?php if ( $branch['active'] ) : ?>
                <a href="<?php echo esc_url( home_url( '/muchroom/' . $slug . '/' ) ); ?>" class="glass-card branch-card branch-active" onclick="event.preventDefault(); navigateTo(this.href);">
                    <span class="branch-icon"><?php echo esc_html( $branch['icon'] ); ?></span>
                    <span class="branch-name"><?php echo esc_html( $branch['name'] ); ?></span>
                    <span class="branch-status badge-active">● Active</span>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/muchroom/' . $slug . '/' ) ); ?>" class="glass-card branch-card branch-coming-soon">
                    <span class="branch-icon"><?php echo esc_html( $branch['icon'] ); ?></span>
                    <span class="branch-name"><?php echo esc_html( $branch['name'] ); ?></span>
                    <span class="branch-status badge-soon">🔜 Coming Soon</span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="landing-features stagger-in" id="features" style="margin-top:40px;">
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

<?php if ( empty( $GLOBALS['mgi_shortcode_mode'] ) ) : ?>
<?php wp_footer(); ?>
</body>
</html>
<?php endif; ?>
