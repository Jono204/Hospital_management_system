<?php
// ============================================================
// delete_appointment.php
// DELETE — Removes an appointment and its medical record
// ============================================================

session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forms.html');
    exit;
}

// ── Sanitise input ───────────────────────────────────────────
$appointment_id = (int)($_POST['appointment_id'] ?? 0);

if ($appointment_id <= 0) {
    die('<p>Invalid appointment ID. <a href="../forms.html">Go back</a></p>');
}

// ── Check the appointment exists ─────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM appointment WHERE appointment_id = :id LIMIT 1");
$stmt->execute([':id' => $appointment_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    die('<p>Appointment not found. <a href="../forms.html">Go back</a></p>');
}

// ── DELETE medical record first (if exists) ───────────────────
// ON DELETE CASCADE handles this automatically, but shown
// explicitly here to demonstrate awareness of the relationship
$del_record = $pdo->prepare("DELETE FROM medical_record WHERE appointment_id = :id");
$del_record->execute([':id' => $appointment_id]);

// ── DELETE the appointment ────────────────────────────────────
$del_appt = $pdo->prepare("DELETE FROM appointment WHERE appointment_id = :id");
$del_appt->execute([':id' => $appointment_id]);

// ── Success response ─────────────────────────────────────────
echo "
<!DOCTYPE html>
<html>
<head><title>Appointment Deleted</title></head>
<body style='font-family:sans-serif;padding:2rem;'>
  <h2 style='color:#c0392b;'>&#10003; Appointment deleted</h2>
  <p>Appointment <strong>#$appointment_id</strong> and its medical record have been removed.</p>
  <a href='../forms.html'>Back to forms</a>
</body>
</html>";
?>