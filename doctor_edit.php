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

// Fetch doctor
$stmt = $conn->prepare("SELECT * FROM doctors WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doctor) {
  header("Location: doctors_manage.php");
  exit;
}

$success = "";
$error = "";

if (isset($_POST["update"])) {
  $doctor_name = trim($_POST["doctor_name"] ?? "");
  $spec        = trim($_POST["specialization"] ?? "");
  $from        = $_POST["available_from"] ?? "";
  $to          = $_POST["available_to"] ?? "";

  if ($doctor_name === "" || $spec === "" || $from === "" || $to === "") {
    $error = "Please fill all fields.";
  } elseif ($from >= $to) {
    $error = "Available time 'From' must be earlier than 'To'.";
  } else {
    $up = $conn->prepare("UPDATE doctors SET doctor_name=?, specialization=?, available_from=?, available_to=? WHERE id=?");
    $up->bind_param("ssssi", $doctor_name, $spec, $from, $to, $id);

    if ($up->execute()) {
      $success = "Doctor updated successfully ✅";

      // refresh values
      $doctor["doctor_name"] = $doctor_name;
      $doctor["specialization"] = $spec;
      $doctor["available_from"] = $from;
      $doctor["available_to"] = $to;

    } else {
      $error = "Update failed: " . $conn->error;
    }
    $up->close();
  }
}
?>

<?php include "header.php"; ?>

<section class="page">
  <div class="page-head">
    <h2>Edit Doctor</h2>
    <p>Update doctor details.</p>
  </div>

  <?php if ($success): ?>
    <div class="msg ok">
      <span class="icon">✅</span>
      <span><?php echo htmlspecialchars($success); ?></span>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="msg bad">
      <span class="icon">⚠️</span>
      <span><?php echo htmlspecialchars($error); ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" class="card" style="max-width:720px;">
    <div class="grid">

      <div>
        <label>Doctor Name *</label>
        <input class="input" type="text" name="doctor_name"
               value="<?php echo htmlspecialchars($doctor["doctor_name"]); ?>">
      </div>

      <div>
        <label>Specialization *</label>
        <input class="input" type="text" name="specialization"
               value="<?php echo htmlspecialchars($doctor["specialization"]); ?>">
      </div>

      <div>
        <label>Available From *</label>
        <input class="input" type="time" name="available_from"
               value="<?php echo substr(htmlspecialchars($doctor["available_from"]),0,5); ?>">
      </div>

      <div>
        <label>Available To *</label>
        <input class="input" type="time" name="available_to"
               value="<?php echo substr(htmlspecialchars($doctor["available_to"]),0,5); ?>">
      </div>

    </div>

    <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">
      <button class="btn" type="submit" name="update">Update Doctor</button>
      <a class="btn ghost" href="doctors_manage.php" style="text-decoration:none;display:inline-block;">Back</a>
    </div>
  </form>
</section>

<?php include "footer.php"; ?>