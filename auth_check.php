<?php
// ============================================================
// auth_check.php
// SECURITY: Role-based access control helper
// Include this at the top of any page that requires login
//
// Usage:
//   require 'php/auth_check.php';
//   requireRole('admin');    // only admins
//   requireRole('doctor');   // only doctors
//   requireLogin();          // any logged-in user
// ============================================================

session_start();

// Check if user is logged in at all
function requireLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header('Location: forms.html');
        exit;
    }
}

// Check if user has a specific role
function requireRole(string $role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        http_response_code(403);
        die('
            <p style="font-family:sans-serif;padding:2rem;">
                <strong>Access denied.</strong>
                You do not have permission to view this page.<br><br>
                <a href="forms.html">Go back</a>
            </p>
        ');
    }
}

// Get the current logged-in user details from session
function getCurrentUser(): array {
    return [
        'id'   => $_SESSION['user_id']   ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'name' => $_SESSION['user_name'] ?? 'Unknown',
    ];
}

// Log the user out and destroy session
function logout() {
    session_unset();
    session_destroy();
    header('Location: forms.html');
    exit;
}
?>