<?php
/**
 * Header partial - Navigation with responsive menu, clock, staff info
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$staff = MGI_Auth::current_staff();
$current_page = get_query_var( 'mgi_page' );
$branch = Muchroom_Gadget_Inventory::get_current_branch();
$branch_name = Muchroom_Gadget_Inventory::get_branch_name();
$branch_prefix = '/muchroom/' . $branch;
?>
<?php if ( ! MGI_Shortcodes::is_shortcode_mode() ) : ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( isset( $page_title ) ? $page_title . ' - ' : '' ); ?><?php echo esc_html( $branch_name ); ?> - Muchroom Limited</title>
    <?php wp_head(); ?>
</head>
<body class="mgi-page">
<?php endif; ?>

<div class="mgi-noodle-bg"></div>

<!-- Mobile Drawer Overlay -->
<div class="mgi-drawer-overlay" id="mgi-drawer-overlay"></div>

<!-- Mobile Slide-out Drawer -->
<nav class="mgi-drawer" id="mgi-drawer">
    <div style="padding:0 16px 16px;border-bottom:1px solid rgba(0,0,0,0.06);margin-bottom:8px;">
        <span class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">📍 <?php echo esc_html( $branch_name ); ?></span>
    </div>
    <ul class="mgi-drawer-links">
        <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/home/' ) ); ?>" class="<?php echo 'home' === $current_page ? 'active' : ''; ?>">🏠 Home</a></li>
        <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/take-order/' ) ); ?>" class="<?php echo 'take-order' === $current_page ? 'active' : ''; ?>">🧾 Take Order</a></li>
        <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/sales/' ) ); ?>" class="<?php echo 'sales' === $current_page ? 'active' : ''; ?>">💵 Sales</a></li>
        <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/inventory/' ) ); ?>" class="<?php echo 'inventory' === $current_page ? 'active' : ''; ?>">📦 Inventory</a></li>
        <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/import/' ) ); ?>" class="<?php echo 'import' === $current_page ? 'active' : ''; ?>">📥 Import</a></li>
        <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/financial/' ) ); ?>" class="<?php echo 'financial' === $current_page ? 'active' : ''; ?>">💰 Financial</a></li>
        <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/analytics/' ) ); ?>" class="<?php echo 'analytics' === $current_page ? 'active' : ''; ?>">📊 Analytics</a></li>
        <?php if ( MGI_Auth::is_admin() ) : ?>
        <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/admin/' ) ); ?>" class="<?php echo 'admin' === $current_page ? 'active' : ''; ?>">⚙️ Admin</a></li>
        <?php endif; ?>
        <li style="border-top:1px solid rgba(0,0,0,0.06);margin-top:8px;padding-top:8px;">
            <a href="<?php echo esc_url( home_url( '/muchroom/' ) ); ?>">🏢 Switch Branch</a>
        </li>
    </ul>
</nav>

<!-- Top Navigation -->
<nav class="mgi-nav">
    <div class="mgi-nav-inner">
        <a href="<?php echo esc_url( home_url( $branch_prefix . '/home/' ) ); ?>" class="mgi-nav-brand">
            <span>Muchroom</span>
            <span style="font-size:11px;color:var(--text-muted);font-weight:500;margin-left:4px;">📍 <?php echo esc_html( ucfirst( $branch ) ); ?></span>
        </a>

        <!-- Desktop/Tablet Navigation Links -->
        <ul class="mgi-nav-links">
            <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/home/' ) ); ?>" class="<?php echo 'home' === $current_page ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/take-order/' ) ); ?>" class="<?php echo 'take-order' === $current_page ? 'active' : ''; ?>">Take Order</a></li>
            <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/sales/' ) ); ?>" class="<?php echo 'sales' === $current_page ? 'active' : ''; ?>">Sales</a></li>
            <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/inventory/' ) ); ?>" class="<?php echo 'inventory' === $current_page ? 'active' : ''; ?>">Inventory</a></li>
            <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/import/' ) ); ?>" class="<?php echo 'import' === $current_page ? 'active' : ''; ?>">Import</a></li>
            <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/financial/' ) ); ?>" class="<?php echo 'financial' === $current_page ? 'active' : ''; ?>">Financial</a></li>
            <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/analytics/' ) ); ?>" class="<?php echo 'analytics' === $current_page ? 'active' : ''; ?>">Analytics</a></li>
            <?php if ( MGI_Auth::is_admin() ) : ?>
            <li><a href="<?php echo esc_url( home_url( $branch_prefix . '/admin/' ) ); ?>" class="<?php echo 'admin' === $current_page ? 'active' : ''; ?>">Admin</a></li>
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
