<?php
require_once "config/db.php";
require_once "auth.php";

// ✅ Only admin can access
require_role("admin");

$success = "";
$error = "";

if (isset($_POST["save"])) {
  $doctor_name = trim($_POST["doctor_name"] ?? "");
  $spec        = trim($_POST["specialization"] ?? "");
  $from        = $_POST["available_from"] ?? "";
  $to          = $_POST["available_to"] ?? "";

  // Basic validation
  if ($doctor_name === "" || $spec === "" || $from === "" || $to === "") {
    $error = "Please fill all fields.";
  } elseif ($from >= $to) {
    $error = "Available time 'From' must be earlier than 'To'.";
  } else {
    $stmt = $conn->prepare("INSERT INTO doctors (doctor_name, specialization, available_from, available_to) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $doctor_name, $spec, $from, $to);

    if ($stmt->execute()) {
      $success = "Doctor added successfully ✅";
      $_POST = []; // clear form
    } else {
      $error = "Insert failed: " . $conn->error;
    }
    $stmt->close();
  }
}
?>

<?php include "header.php"; ?>

<section class="page">
  <div class="page-head">
    <h2>Add Doctor</h2>
    <p>Admin can add new doctors to the system.</p>
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
               value="<?php echo htmlspecialchars($_POST["doctor_name"] ?? ""); ?>">
      </div>

      <div>
        <label>Specialization *</label>
        <input class="input" type="text" name="specialization"
               value="<?php echo htmlspecialchars($_POST["specialization"] ?? ""); ?>">
      </div>

      <div>
        <label>Available From *</label>
        <input class="input" type="time" name="available_from"
               value="<?php echo htmlspecialchars($_POST["available_from"] ?? "09:00"); ?>">
      </div>

      <div>
        <label>Available To *</label>
        <input class="input" type="time" name="available_to"
               value="<?php echo htmlspecialchars($_POST["available_to"] ?? "12:00"); ?>">
      </div>

    </div>

    <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">
      <button class="btn" type="submit" name="save">Save Doctor</button>
      <a class="btn ghost" href="doctors_manage.php" style="text-decoration:none;display:inline-block;">Back</a>
    </div>
  </form>
</section>

<?php include "footer.php"; ?>