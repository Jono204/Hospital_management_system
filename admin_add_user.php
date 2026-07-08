<?php
// ============================================================
// admin_add_user.php
// CREATE — Adds a new patient, doctor, or admin
// SECURITY: password_hash(), role-based access, input sanitisation
// ============================================================

session_start();
require 'db_connect.php';

// ── SECURITY: Role-based access control ──────────────────────
// Only admins are allowed to access this script
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('<p>Access denied. Admins only. <a href="../forms.html">Go back</a></p>');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forms.html');
    exit;
}

// ── Sanitise inputs ──────────────────────────────────────────
// htmlspecialchars() prevents HTML/XSS injection
$user_role      = trim($_POST['user_role']         ?? '');
$first_name     = htmlspecialchars(trim($_POST['first_name']     ?? ''));
$last_name      = htmlspecialchars(trim($_POST['last_name']      ?? ''));
$email          = trim($_POST['email']             ?? '');
$specialisation = htmlspecialchars(trim($_POST['specialisation'] ?? ''));
$password       = $_POST['password']               ?? '';
$confirm        = $_POST['confirm_password']       ?? '';

// ── Validation ───────────────────────────────────────────────
$errors = [];
$allowed_roles = ['patient', 'doctor', 'admin'];

if (!in_array($user_role, $allowed_roles))              $errors[] = 'Invalid role selected.';
if (strlen($first_name) < 2)                            $errors[] = 'First name is too short.';
if (strlen($last_name) < 2)                             $errors[] = 'Last name is too short.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors[] = 'Invalid email address.';
if (strlen($password) < 8)                              $errors[] = 'Password must be at least 8 characters.';
if ($password !== $confirm)                             $errors[] = 'Passwords do not match.';
if ($user_role === 'doctor' && empty($specialisation))  $errors[] = 'Specialisation is required for doctors.';

// ── Check email is not already registered ────────────────────
if (empty($errors)) {
    foreach (['patient', 'doctor', 'admin'] as $table) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE email = :email");
        $check->execute([':email' => $email]);
        if ($check->fetchColumn() > 0) {
            $errors[] = 'This email address is already registered.';
            break;
        }
    }
}

if (!empty($errors)) {
    echo '<p>Errors:<br>' . implode('<br>', array_map('htmlspecialchars', $errors)) . '</p>';
    echo '<a href="../forms.html">Go back</a>';
    exit;
}

// ── SECURITY: Hash password using bcrypt (password_hash) ─────
// PASSWORD_DEFAULT uses bcrypt — much stronger than SHA-256
// Automatically generates a unique salt for each password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// ── INSERT into correct table using prepared statements ───────
switch ($user_role) {
    case 'patient':
        $stmt = $pdo->prepare("
            INSERT INTO patient
                (first_name, last_name, email, phone, date_of_birth, address, password_hash)
            VALUES (:fn, :ln, :email, '0000000000', '2000-01-01', 'Not provided', :pw)
        ");
        $stmt->execute([':fn' => $first_name, ':ln' => $last_name,
                        ':email' => $email, ':pw' => $password_hash]);
        break;

    case 'doctor':
        $stmt = $pdo->prepare("
            INSERT INTO doctor (first_name, last_name, email, specialisation, password_hash)
            VALUES (:fn, :ln, :email, :spec, :pw)
        ");
        $stmt->execute([':fn' => $first_name, ':ln' => $last_name,
                        ':email' => $email, ':spec' => $specialisation, ':pw' => $password_hash]);
        break;

    case 'admin':
        $stmt = $pdo->prepare("
            INSERT INTO admin (first_name, last_name, email, password_hash)
            VALUES (:fn, :ln, :email, :pw)
        ");
        $stmt->execute([':fn' => $first_name, ':ln' => $last_name,
                        ':email' => $email, ':pw' => $password_hash]);
        break;
}

$new_id = $pdo->lastInsertId();

echo "
<!DOCTYPE html>
<html>
<head><title>User Created</title></head>
<body style='font-family:sans-serif;padding:2rem;'>
  <h2 style='color:#1a5fa8;'>&#10003; User created!</h2>
  <p><strong>" . htmlspecialchars($first_name) . " " . htmlspecialchars($last_name) . "</strong>
     has been added as a <strong>" . htmlspecialchars($user_role) . "</strong>.</p>
  <p>User ID: <strong>$new_id</strong></p>
  <a href='../forms.html'>Back to forms</a>
</body>
</html>";
?>