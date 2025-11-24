<?php
// Logout script: accepts GET or POST and destroys the session, then redirects to login.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// If session cookie exists, remove it
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Redirect back to login/register page
header('Location: index.php');
exit;

?>