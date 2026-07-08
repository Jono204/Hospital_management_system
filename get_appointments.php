<?php
// ============================================================
// get_appointments.php
// READ — Fetches and displays all appointments with
//        patient and doctor details joined from related tables
// ============================================================

session_start();
require 'php/db_connect.php';

// ── READ: Fetch all appointments with joins ──────────────────
$stmt = $pdo->prepare("
    SELECT
        a.appointment_id,
        CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
        p.email                                 AS patient_email,
        p.phone                                 AS patient_phone,
        CONCAT(d.first_name, ' ', d.last_name) AS doctor_name,
        d.specialisation,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.reason,
        mr.diagnosis,
        mr.treatment
    FROM appointment a
    JOIN patient p       ON a.patient_id       = p.patient_id
    JOIN doctor  d       ON a.doctor_id        = d.doctor_id
    LEFT JOIN medical_record mr ON a.appointment_id = mr.appointment_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute();
$appointments = $stmt->fetchAll();

// ── Status badge colours ─────────────────────────────────────
function statusColor($status) {
    return match($status) {
        'confirmed'  => '#1e8449',
        'completed'  => '#1a5fa8',
        'cancelled'  => '#c0392b',
        default      => '#b7950b',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>All Appointments – MediSystem</title>
  <style>
    body { font-family:'Segoe UI',sans-serif; background:#f4f6f9; color:#1c2b3a; padding:2rem 1rem; }
    h1   { color:#1a5fa8; margin-bottom:1.5rem; }
    .back { display:inline-block; margin-bottom:1rem; color:#1a5fa8; text-decoration:none; font-size:0.9rem; }
    table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08); }
    th    { background:#1a5fa8; color:#fff; padding:12px 14px; text-align:left; font-size:0.82rem; text-transform:uppercase; letter-spacing:0.04em; }
    td    { padding:11px 14px; border-bottom:1px solid #e8edf2; font-size:0.88rem; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:#f0f4f9; }
    .badge {
      display:inline-block; padding:3px 10px; border-radius:99px;
      font-size:0.78rem; font-weight:600; color:#fff;
    }
    .no-data { text-align:center; padding:2rem; color:#6b7c93; }
    form.delete-form { display:inline; }
    .btn-delete {
      background:#c0392b; color:#fff; border:none; border-radius:5px;
      padding:4px 10px; font-size:0.8rem; cursor:pointer;
    }
    .btn-delete:hover { background:#a93226; }
  </style>
</head>
<body>

<a class="back" href="forms.html">&larr; Back to forms</a>
<h1>All Appointments</h1>

<?php if (empty($appointments)): ?>
  <p class="no-data">No appointments found.</p>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Patient</th>
        <th>Doctor</th>
        <th>Specialisation</th>
        <th>Date</th>
        <th>Time</th>
        <th>Status</th>
        <th>Reason</th>
        <th>Diagnosis</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['appointment_id']) ?></td>
          <td>
            <?= htmlspecialchars($row['patient_name']) ?><br>
            <small style="color:#6b7c93"><?= htmlspecialchars($row['patient_email']) ?></small>
          </td>
          <td><?= htmlspecialchars($row['doctor_name']) ?></td>
          <td><?= htmlspecialchars($row['specialisation']) ?></td>
          <td><?= htmlspecialchars($row['appointment_date']) ?></td>
          <td><?= htmlspecialchars($row['appointment_time']) ?></td>
          <td>
            <span class="badge" style="background:<?= statusColor($row['status']) ?>">
              <?= htmlspecialchars(ucfirst($row['status'])) ?>
            </span>
          </td>
          <td><?= htmlspecialchars($row['reason']) ?></td>
          <td><?= $row['diagnosis'] ? htmlspecialchars($row['diagnosis']) : '<em style="color:#aaa">None</em>' ?></td>
          <td>
            <form class="delete-form" action="php/delete_appointment.php" method="POST"
                  onsubmit="return confirm('Delete appointment #<?= $row['appointment_id'] ?>?')">
              <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
              <button type="submit" class="btn-delete">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

</body>
</html>