<?php
/**
 * Footer partial
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
</div><!-- .mgi-content -->

<footer class="mgi-footer">
    <div class="mgi-footer-inner">
        <div class="mgi-footer-info">
            <strong>Muchroom Limited</strong> &mdash; Gadget Inventory Management System &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
        </div>
        <button class="pill-btn pill-btn-danger btn-text" onclick="doLogout()" style="padding:8px 20px;font-size:13px;">
            🚪 Logout
        </button>
    </div>
</footer>

</div><!-- .mgi-wrapper -->

<?php wp_footer(); ?>
</body>
</html>
