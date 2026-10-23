<?php
/**
 * CSRF Protection — SkillBridge
 * 
 * Generates and validates CSRF tokens to protect POST forms
 * against Cross-Site Request Forgery attacks.
 */

/**
 * Generate a CSRF token and store it in the session.
 * Returns the token for embedding in a hidden form field.
 *
 * @return string The CSRF token
 */
function generateCSRFToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden input field with the CSRF token.
 * Use this inside <form> tags.
 *
 * @return string HTML hidden input element
 */
function csrfField(): string {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Validate the CSRF token from the submitted form.
 * Call this at the top of POST request handlers.
 *
 * @return bool True if valid, false otherwise
 */
function validateCSRFToken(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Validate CSRF and die with error if invalid.
 * Convenience function for POST handlers.
 */
function requireValidCSRF(): void {
    if (!validateCSRFToken()) {
        http_response_code(403);
        die('Invalid or missing security token. Please go back and try again.');
    }
}

