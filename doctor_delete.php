<?php
require_once "config/db.php";
require_once "auth.php";

// ✅ Only admin can access
require_role("admin");

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
  header("Location: doctors_manage.php");
  exit;
}

// Optional safety: prevent delete if doctor has appointments
$check = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE doctor_id=?");
$check->bind_param("i", $id);
$check->execute();
$total = $check->get_result()->fetch_assoc()["total"] ?? 0;
$check->close();

if ($total > 0) {
  // doctor has bookings -> do not delete
  header("Location: doctors_manage.php?msg=cannot_delete");
  exit;
}

// delete doctor
$stmt = $conn->prepare("DELETE FROM doctors WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: doctors_manage.php?msg=deleted");
exit;