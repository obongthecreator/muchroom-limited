<?php
/**
 * Coming Soon page for inactive branches
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$branch_slug = Muchroom_Gadget_Inventory::get_current_branch();
$branch_name = Muchroom_Gadget_Inventory::get_branch_name( $branch_slug );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $branch_name ); ?> - Coming Soon - Muchroom Limited</title>
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
        <div class="hero-badge">🔜 Coming Soon</div>

        <h1 class="heading font-display" style="margin-bottom:16px;">
            <span style="background:linear-gradient(135deg,var(--primary),var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                <?php echo esc_html( $branch_name ); ?>
            </span>
        </h1>

        <p class="hero-subtitle body-text" style="max-width:500px;">
            The <?php echo esc_html( $branch_name ); ?> inventory system is currently under development. We're working to bring it online soon.
        </p>

        <div class="glass-card" style="padding:32px;margin-top:24px;max-width:400px;width:100%;text-align:center;">
            <span style="font-size:64px;display:block;margin-bottom:16px;">🚧</span>
            <h3 class="font-display" style="font-size:20px;margin-bottom:8px;">Under Construction</h3>
            <p class="text-muted" style="font-size:14px;margin-bottom:20px;">This branch will be available once setup is complete. Please check back later.</p>
            <a href="<?php echo esc_url( home_url( '/muchroom/' ) ); ?>" class="pill-btn beam-btn btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
                ← Back to Branches
            </a>
        </div>
    </div>

    <div class="landing-footer">
        <p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Muchroom Limited. All rights reserved.</p>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
