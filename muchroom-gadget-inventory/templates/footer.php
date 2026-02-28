<?php
/**
 * Footer partial
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$footer_branch = Muchroom_Gadget_Inventory::get_current_branch();
?>
</div><!-- .mgi-content -->

<footer class="mgi-footer">
    <div class="mgi-footer-inner">
        <div class="mgi-footer-info">
            <strong>Muchroom Limited</strong> &mdash; <?php echo esc_html( Muchroom_Gadget_Inventory::get_branch_name() ); ?> &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
            &nbsp;|&nbsp;
            <a href="<?php echo esc_url( home_url( '/muchroom/' ) ); ?>" style="color:var(--primary);text-decoration:none;font-size:13px;"><iconify-icon icon="solar:buildings-linear"></iconify-icon> Switch Branch</a>
        </div>
        <button class="pill-btn pill-btn-danger btn-text" onclick="doLogout()" style="padding:8px 20px;font-size:13px;">
            <iconify-icon icon="solar:logout-2-linear"></iconify-icon> Logout
        </button>
    </div>
</footer>

</div><!-- .mgi-wrapper -->

<script>
// Store branch for logout redirect
window.mgiBranch = '<?php echo esc_js( $footer_branch ); ?>';
</script>

<?php if ( ! MGI_Shortcodes::is_shortcode_mode() ) : ?>
<?php wp_footer(); ?>
</body>
</html>
<?php endif; ?>
