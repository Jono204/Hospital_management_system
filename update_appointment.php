<?php
// ============================================================
// update_appointment.php
// UPDATE — Updates appointment status and saves medical record
// READ   — Fetches appointment details by ID
// ============================================================

session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forms.html');
    exit;
}

// ── Sanitise inputs ──────────────────────────────────────────
$appointment_id = (int)($_POST['appointment_id'] ?? 0);
$status         = trim($_POST['status']          ?? '');
$diagnosis      = htmlspecialchars(trim($_POST['diagnosis']  ?? ''));
$treatment      = htmlspecialchars(trim($_POST['treatment']  ?? ''));
$notes          = htmlspecialchars(trim($_POST['notes']      ?? ''));
$created_at     = trim($_POST['created_at']      ?? date('Y-m-d'));

// ── Validation ───────────────────────────────────────────────
$allowed_statuses = ['requested', 'confirmed', 'completed', 'cancelled'];

if ($appointment_id <= 0) {
    die('<p>Invalid appointment ID. <a href="../forms.html">Go back</a></p>');
}
if (!in_array($status, $allowed_statuses)) {
    die('<p>Invalid status value. <a href="../forms.html">Go back</a></p>');
}

// ── READ: Check appointment exists ───────────────────────────
$stmt = $pdo->prepare("SELECT * FROM appointment WHERE appointment_id = :id LIMIT 1");
$stmt->execute([':id' => $appointment_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    die('<p>Appointment not found. <a href="../forms.html">Go back</a></p>');
}

// ── UPDATE: appointment status ────────────────────────────────
$update = $pdo->prepare("
    UPDATE appointment
    SET status = :status
    WHERE appointment_id = :id
");
$update->execute([
    ':status' => $status,
    ':id'     => $appointment_id,
]);

// ── CREATE or UPDATE: medical record (only if diagnosis given) ─
$record_saved = false;
if (!empty($diagnosis) && !empty($treatment)) {

    // Check if a record already exists for this appointment
    $check = $pdo->prepare("SELECT record_id FROM medical_record WHERE appointment_id = :id LIMIT 1");
    $check->execute([':id' => $appointment_id]);
    $existing = $check->fetch();

    if ($existing) {
        // UPDATE existing record
        $rec = $pdo->prepare("
            UPDATE medical_record
            SET diagnosis = :diagnosis,
                treatment = :treatment,
                notes     = :notes,
                created_at = :created_at
            WHERE appointment_id = :id
        ");
    } else {
        // CREATE new record
        $rec = $pdo->prepare("
            INSERT INTO medical_record
                (appointment_id, diagnosis, treatment, notes, created_at)
            VALUES
                (:id, :diagnosis, :treatment, :notes, :created_at)
        ");
    }

    $rec->execute([
        ':id'         => $appointment_id,
        ':diagnosis'  => $diagnosis,
        ':treatment'  => $treatment,
        ':notes'      => $notes,
        ':created_at' => $created_at,
    ]);
    $record_saved = true;
}

// ── Success response ─────────────────────────────────────────
$record_msg = $record_saved ? 'Medical record saved.' : 'No medical record saved (diagnosis/treatment were empty).';

echo "
<!DOCTYPE html>
<html>
<head><title>Appointment Updated</title></head>
<body style='font-family:sans-serif;padding:2rem;'>
  <h2 style='color:#1a5fa8;'>&#10003; Appointment updated!</h2>
  <p>Appointment <strong>#$appointment_id</strong> status set to <strong>$status</strong>.</p>
  <p>$record_msg</p>
  <a href='../forms.html'>Back to forms</a>
</body>
</html>";
?>