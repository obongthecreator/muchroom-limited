<?php if ( ! MGI_Shortcodes::is_shortcode_mode() ) : ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo esc_html( Muchroom_Gadget_Inventory::get_branch_name() ); ?> - Muchroom Limited</title>
    <?php wp_head(); ?>
</head>
<body class="mgi-page">
<?php endif; ?>

<?php $branch = Muchroom_Gadget_Inventory::get_current_branch(); ?>

<div class="mgi-noodle-bg"></div>

<div class="mgi-wrapper" style="align-items:center;justify-content:center;min-height:100vh;">
    <div class="mgi-content" style="max-width:420px;width:100%;">
        <div class="glass-card" style="padding:40px 32px;">
            <div class="text-center mb-24">
                <h1 class="heading font-display" style="font-size:28px;margin-bottom:8px;">
                    <span style="background:linear-gradient(135deg,var(--primary),var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Muchroom Limited</span>
                </h1>
                <p class="text-muted body-text" style="font-size:14px;">Staff Login &mdash; <?php echo esc_html( Muchroom_Gadget_Inventory::get_branch_name() ); ?></p>
            </div>

            <form id="login-form" onsubmit="return handleLogin(event)">
                <input type="hidden" id="login-branch" value="<?php echo esc_attr( $branch ); ?>" />
                <div class="mgi-form-group">
                    <label for="login-username">Username</label>
                    <input type="text" id="login-username" class="mgi-input" placeholder="Enter your username" required autocomplete="username" />
                </div>

                <div class="mgi-form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" class="mgi-input" placeholder="Enter your password" required autocomplete="current-password" />
                </div>

                <div id="login-error" class="text-danger" style="font-size:13px;margin-bottom:12px;display:none;"></div>

                <button type="submit" class="pill-btn beam-btn btn-text w-full" id="login-btn" style="width:100%;margin-top:8px;">
                    <iconify-icon icon="solar:lock-keyhole-linear"></iconify-icon> Login
                </button>
            </form>

            <div class="text-center mt-24" style="display:flex;flex-direction:column;gap:8px;align-items:center;">
                <a href="<?php echo esc_url( home_url( '/muchroom/' . $branch . '/' ) ); ?>" class="text-muted" style="font-size:13px;text-decoration:none;">
                    <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Back to <?php echo esc_html( Muchroom_Gadget_Inventory::get_branch_name() ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/muchroom/' ) ); ?>" class="text-muted" style="font-size:13px;text-decoration:none;">
                    <iconify-icon icon="solar:buildings-linear"></iconify-icon> Switch Branch
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function handleLogin(e) {
    e.preventDefault();
    var username = document.getElementById('login-username').value;
    var password = document.getElementById('login-password').value;
    var branch = document.getElementById('login-branch').value;
    var errorEl = document.getElementById('login-error');
    var btn = document.getElementById('login-btn');

    errorEl.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Logging in...';

    mgiAjax('login', { username: username, password: password, branch: branch }, function(err, res) {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="solar:lock-keyhole-linear"></iconify-icon> Login';

        if (err || !res.success) {
            errorEl.textContent = res && res.data ? res.data.message : 'Login failed';
            errorEl.style.display = 'block';
            return;
        }

        window.location.href = res.data.redirect;
    });

    return false;
}
</script>

<?php if ( ! MGI_Shortcodes::is_shortcode_mode() ) : ?>
<?php wp_footer(); ?>
</body>
</html>
<?php endif; ?>
