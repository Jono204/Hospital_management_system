<?php
// ============================================================
// book_appointment.php
// CREATE — Inserts a new appointment into the database
// ============================================================

session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forms.html');
    exit;
}

// ── Sanitise all inputs ──────────────────────────────────────
$first_name        = htmlspecialchars(trim($_POST['first_name']        ?? ''));
$last_name         = htmlspecialchars(trim($_POST['last_name']         ?? ''));
$email             = trim($_POST['email']                              ?? '');
$phone             = trim($_POST['phone']                              ?? '');
$doctor_id         = (int)($_POST['doctor_id']                        ?? 0);
$appointment_date  = trim($_POST['appointment_date']                   ?? '');
$appointment_time  = trim($_POST['appointment_time']                   ?? '');
$reason            = htmlspecialchars(trim($_POST['reason']            ?? ''));

// ── Validation ───────────────────────────────────────────────
$errors = [];

if (empty($first_name) || empty($last_name))           $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors[] = 'Invalid email address.';
if (!preg_match('/^[0-9]{10}$/', $phone))               $errors[] = 'Phone must be 10 digits.';
if ($doctor_id <= 0)                                    $errors[] = 'Please select a doctor.';
if (empty($appointment_date))                           $errors[] = 'Date is required.';
if (empty($appointment_time))                           $errors[] = 'Time is required.';
if (strlen($reason) < 5)                                $errors[] = 'Please provide a reason.';

// Validate date is not in the past
if (!empty($appointment_date) && strtotime($appointment_date) < strtotime('today')) {
    $errors[] = 'Appointment date cannot be in the past.';
}

if (!empty($errors)) {
    echo '<p>Errors:<br>' . implode('<br>', $errors) . '</p>';
    echo '<a href="../forms.html">Go back</a>';
    exit;
}

// ── Find or create patient record by email ───────────────────
$stmt = $pdo->prepare("SELECT patient_id FROM patient WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$patient = $stmt->fetch();

if (!$patient) {
    // Patient doesn't exist — insert minimal record
    // (Full registration would collect more details)
    $insert = $pdo->prepare("
        INSERT INTO patient (first_name, last_name, email, phone, date_of_birth, address, password_hash)
        VALUES (:fn, :ln, :email, :phone, '2000-01-01', 'Not provided', :pw)
    ");
    $insert->execute([
        ':fn'    => $first_name,
        ':ln'    => $last_name,
        ':email' => $email,
        ':phone' => $phone,
        ':pw'    => hash('sha256', 'changeme123'),
    ]);
    $patient_id = (int)$pdo->lastInsertId();
} else {
    $patient_id = (int)$patient['patient_id'];
}

// ── INSERT new appointment ───────────────────────────────────
$stmt = $pdo->prepare("
    INSERT INTO appointment
        (patient_id, doctor_id, appointment_date, appointment_time, status, reason)
    VALUES
        (:patient_id, :doctor_id, :appt_date, :appt_time, 'requested', :reason)
");

$stmt->execute([
    ':patient_id' => $patient_id,
    ':doctor_id'  => $doctor_id,
    ':appt_date'  => $appointment_date,
    ':appt_time'  => $appointment_time,
    ':reason'     => $reason,
]);

$new_id = $pdo->lastInsertId();

// ── Success response ─────────────────────────────────────────
echo "
<!DOCTYPE html>
<html>
<head><title>Appointment Booked</title></head>
<body style='font-family:sans-serif;padding:2rem;'>
  <h2 style='color:#1a5fa8;'>&#10003; Appointment requested!</h2>
  <p>Your appointment (ID: <strong>$new_id</strong>) has been submitted successfully.</p>
  <p>You will be notified once the doctor confirms it.</p>
  <a href='../forms.html'>Back to forms</a>
</body>
</html>";
?>