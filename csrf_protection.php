<?php
// Ensure session is only started if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token function
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Validate CSRF token function
function validate_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

// CSRF token field generation function
function csrf_token_field() {
    generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}
?>
