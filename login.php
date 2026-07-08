<?php
// ============================================================
// login.php
// Handles secure login for patients, doctors, and admins
// SECURITY: password_verify(), prepared statements, sessions
// ============================================================

session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forms.html');
    exit;
}

// ── Sanitise inputs ──────────────────────────────────────────
$role     = trim($_POST['role']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

// ── Basic validation ─────────────────────────────────────────
if (empty($role) || empty($email) || empty($password)) {
    die('<p>All fields are required. <a href="../forms.html">Go back</a></p>');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('<p>Invalid email address. <a href="../forms.html">Go back</a></p>');
}

// ── Whitelist allowed roles to prevent SQL table injection ────
$allowed_roles = ['patient', 'doctor', 'admin'];
if (!in_array($role, $allowed_roles)) {
    die('<p>Invalid role. <a href="../forms.html">Go back</a></p>');
}

$table = $role;

// ── Fetch user by email — prepared statement prevents SQL injection ──
$stmt = $pdo->prepare("SELECT * FROM $table WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

// ── SECURITY: Use password_verify() to check bcrypt hash ─────
// password_verify() safely compares the plain password against
// the bcrypt hash stored in the database — timing-safe comparison
if (!$user || !password_verify($password, $user['password_hash'])) {
    // Deliberately vague error — don't reveal whether email exists
    die('<p>Incorrect email or password. <a href="../forms.html">Go back</a></p>');
}

// ── Regenerate session ID on login to prevent session fixation ─
session_regenerate_id(true);

// ── Store only minimal data in session ───────────────────────
$_SESSION['user_id']   = $user[$role . '_id'];
$_SESSION['user_role'] = $role;
$_SESSION['user_name'] = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);

// ── Redirect based on role (role-based access control) ───────
switch ($role) {
    case 'patient': header('Location: ../patient_dashboard.php'); break;
    case 'doctor':  header('Location: ../doctor_dashboard.php');  break;
    case 'admin':   header('Location: ../admin_dashboard.php');   break;
}
exit;
?>