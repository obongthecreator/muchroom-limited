<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_Auth {

    public static function login( $username, $password ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();

        $user = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$prefix}staff WHERE username = %s AND is_active = 1",
            sanitize_text_field( $username )
        ) );

        if ( ! $user ) {
            return new WP_Error( 'invalid_user', 'Invalid username or password.' );
        }

        if ( ! wp_check_password( $password, $user->password ) ) {
            return new WP_Error( 'invalid_password', 'Invalid username or password.' );
        }

        // Ensure a session is active.
        if ( ! session_id() ) {
            session_start();
        }

        // Regenerate session ID to prevent fixation and clear stale data.
        session_regenerate_id( true );

        $_SESSION['mgi_staff_id']   = $user->id;
        $_SESSION['mgi_staff_name'] = $user->full_name;
        $_SESSION['mgi_staff_role'] = $user->role;
        $_SESSION['mgi_logged_in']  = true;

        // Flush session data to storage immediately so the next request
        // (the redirect after login) sees the values reliably.
        session_write_close();

        return $user;
    }

    public static function logout() {
        if ( ! session_id() ) {
            session_start();
        }

        // Clear session data.
        $_SESSION = array();

        // Delete the session cookie so the browser discards the old ID.
        if ( ini_get( 'session.use_cookies' ) ) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function is_logged_in() {
        if ( ! session_id() ) {
            session_start();
        }
        return ! empty( $_SESSION['mgi_logged_in'] );
    }

    public static function current_staff() {
        if ( ! self::is_logged_in() ) {
            return null;
        }
        return (object) array(
            'id'        => $_SESSION['mgi_staff_id'],
            'full_name' => $_SESSION['mgi_staff_name'],
            'role'      => $_SESSION['mgi_staff_role'],
        );
    }

    public static function is_admin() {
        $staff = self::current_staff();
        return $staff && 'admin' === $staff->role;
    }
}
