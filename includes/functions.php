<?php
// Functions file
// Contains helpful functions for authentication and other purposes

// Start the session if not already started
function start_session_if_not_started() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Clean user input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
function is_logged_in() {
    start_session_if_not_started();
    return isset($_SESSION['user_id']);
}

// Redirect to a specified page
function redirect($url) {
    header("Location: $url");
    exit();
}

// Show error or success messages
function show_message($message, $type = 'error') {
    $class = ($type == 'success') ? 'success-message' : 'error-message';
    return "<div class='$class'>$message</div>";
}

// Generate a CSRF token
function generate_csrf_token() {
    start_session_if_not_started();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    start_session_if_not_started();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}