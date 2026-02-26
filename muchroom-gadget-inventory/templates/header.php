<?php
/**
 * Header partial - Navigation with responsive menu, clock, staff info
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$staff = MGI_Auth::current_staff();
$current_page = get_query_var( 'mgi_page' );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( isset( $page_title ) ? $page_title . ' - ' : '' ); ?>Muchroom Limited</title>
    <?php wp_head(); ?>
</head>
<body class="mgi-page">

<div class="mgi-noodle-bg"></div>

<!-- Mobile Drawer Overlay -->
<div class="mgi-drawer-overlay" id="mgi-drawer-overlay"></div>

<!-- Mobile Slide-out Drawer -->
<nav class="mgi-drawer" id="mgi-drawer">
    <ul class="mgi-drawer-links">
        <li><a href="<?php echo esc_url( home_url( '/muchroom/home/' ) ); ?>" class="<?php echo 'home' === $current_page ? 'active' : ''; ?>">🏠 Home</a></li>
        <li><a href="<?php echo esc_url( home_url( '/muchroom/take-order/' ) ); ?>" class="<?php echo 'take-order' === $current_page ? 'active' : ''; ?>">🧾 Take Order</a></li>
        <li><a href="<?php echo esc_url( home_url( '/muchroom/sales/' ) ); ?>" class="<?php echo 'sales' === $current_page ? 'active' : ''; ?>">💵 Sales</a></li>
        <li><a href="<?php echo esc_url( home_url( '/muchroom/inventory/' ) ); ?>" class="<?php echo 'inventory' === $current_page ? 'active' : ''; ?>">📦 Inventory</a></li>
        <li><a href="<?php echo esc_url( home_url( '/muchroom/import/' ) ); ?>" class="<?php echo 'import' === $current_page ? 'active' : ''; ?>">📥 Import</a></li>
        <li><a href="<?php echo esc_url( home_url( '/muchroom/financial/' ) ); ?>" class="<?php echo 'financial' === $current_page ? 'active' : ''; ?>">💰 Financial</a></li>
        <li><a href="<?php echo esc_url( home_url( '/muchroom/analytics/' ) ); ?>" class="<?php echo 'analytics' === $current_page ? 'active' : ''; ?>">📊 Analytics</a></li>
        <?php if ( MGI_Auth::is_admin() ) : ?>
        <li><a href="<?php echo esc_url( home_url( '/muchroom/admin/' ) ); ?>" class="<?php echo 'admin' === $current_page ? 'active' : ''; ?>">⚙️ Admin</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Top Navigation -->
<nav class="mgi-nav">
    <div class="mgi-nav-inner">
        <a href="<?php echo esc_url( home_url( '/muchroom/home/' ) ); ?>" class="mgi-nav-brand">
            <span>Muchroom</span>
        </a>

        <!-- Desktop/Tablet Navigation Links -->
        <ul class="mgi-nav-links">
            <li><a href="<?php echo esc_url( home_url( '/muchroom/home/' ) ); ?>" class="<?php echo 'home' === $current_page ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo esc_url( home_url( '/muchroom/take-order/' ) ); ?>" class="<?php echo 'take-order' === $current_page ? 'active' : ''; ?>">Take Order</a></li>
            <li><a href="<?php echo esc_url( home_url( '/muchroom/sales/' ) ); ?>" class="<?php echo 'sales' === $current_page ? 'active' : ''; ?>">Sales</a></li>
            <li><a href="<?php echo esc_url( home_url( '/muchroom/inventory/' ) ); ?>" class="<?php echo 'inventory' === $current_page ? 'active' : ''; ?>">Inventory</a></li>
            <li><a href="<?php echo esc_url( home_url( '/muchroom/import/' ) ); ?>" class="<?php echo 'import' === $current_page ? 'active' : ''; ?>">Import</a></li>
            <li><a href="<?php echo esc_url( home_url( '/muchroom/financial/' ) ); ?>" class="<?php echo 'financial' === $current_page ? 'active' : ''; ?>">Financial</a></li>
            <li><a href="<?php echo esc_url( home_url( '/muchroom/analytics/' ) ); ?>" class="<?php echo 'analytics' === $current_page ? 'active' : ''; ?>">Analytics</a></li>
            <?php if ( MGI_Auth::is_admin() ) : ?>
            <li><a href="<?php echo esc_url( home_url( '/muchroom/admin/' ) ); ?>" class="<?php echo 'admin' === $current_page ? 'active' : ''; ?>">Admin</a></li>
            <?php endif; ?>
        </ul>

        <div class="mgi-nav-staff">
            <span class="mgi-nav-clock" id="mgi-clock">00:00:00</span>
            <span class="staff-name"><?php echo esc_html( $staff ? $staff->full_name : '' ); ?></span>
            <!-- Hamburger Menu Button -->
            <button class="mgi-hamburger" id="mgi-hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<div class="mgi-wrapper">
<div class="mgi-content page-transition-in">
