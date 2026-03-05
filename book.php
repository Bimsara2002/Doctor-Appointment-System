<?php
require_once "config/db.php";
require_once "auth.php";

// ✅ Only staff can book appointments
require_role("staff");

/* Fetch doctors for dropdown */
$doctors = [];
$res = $conn->query("SELECT id, doctor_name, specialization, available_from, available_to FROM doctors ORDER BY doctor_name ASC");
if ($res) {
  while ($row = $res->fetch_assoc()) $doctors[] = $row;
}

/* Prefill doctor from URL */
$prefill_doctor_id = (int)($_GET["doctor_id"] ?? 0);
$selectedDoctor = null;

if ($prefill_doctor_id > 0) {
  $stmtDoc = $conn->prepare("SELECT id, doctor_name, specialization, available_from, available_to FROM doctors WHERE id=?");
  $stmtDoc->bind_param("i", $prefill_doctor_id);
  $stmtDoc->execute();
  $selectedDoctor = $stmtDoc->get_result()->fetch_assoc();
  $stmtDoc->close();
}

/* Keep typed values when doctor reload happens via GET */
if (!isset($_POST["submit"])) {
  $_POST["patient_name"] = $_GET["patient_name"] ?? ($_POST["patient_name"] ?? "");
  $_POST["phone"] = $_GET["phone"] ?? ($_POST["phone"] ?? "");
  $_POST["email"] = $_GET["email"] ?? ($_POST["email"] ?? "");
  $_POST["appointment_date"] = $_GET["appointment_date"] ?? ($_POST["appointment_date"] ?? "");
  $_POST["reason"] = $_GET["reason"] ?? ($_POST["reason"] ?? "");
}

$success = "";
$error = "";

/* Current selected doctor id (POST first, else URL) */
$currentSelectedDoctorId = (int)($_POST["doctor_id"] ?? ($prefill_doctor_id ?: 0));

/* If POST selected a different doctor, update selectedDoctor */
if ($currentSelectedDoctorId > 0 && (!$selectedDoctor || (int)$selectedDoctor["id"] !== $currentSelectedDoctorId)) {
  $stmtDoc2 = $conn->prepare("SELECT id, doctor_name, specialization, available_from, available_to FROM doctors WHERE id=?");
  $stmtDoc2->bind_param("i", $currentSelectedDoctorId);
  $stmtDoc2->execute();
  $selectedDoctor = $stmtDoc2->get_result()->fetch_assoc();
  $stmtDoc2->close();
}

/* Submit */
if (isset($_POST["submit"])) {
  $patient_name = trim($_POST["patient_name"] ?? "");
  $phone        = trim($_POST["phone"] ?? "");
  $email        = trim($_POST["email"] ?? "");
  $doctor_id    = (int)($_POST["doctor_id"] ?? 0);
  $app_date     = $_POST["appointment_date"] ?? "";
  $app_time     = $_POST["appointment_time"] ?? "";
  $reason       = trim($_POST["reason"] ?? "");

  // Server-side validation
  if ($patient_name === "" || $phone === "" || $email === "" || $doctor_id === 0 || $app_date === "" || $app_time === "" || $reason === "") {
    $error = "Please fill all required fields.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } else {

    // ✅ Prevent double booking (doctor + date + time)
    $chk = $conn->prepare("SELECT id FROM appointments WHERE doctor_id=? AND appointment_date=? AND appointment_time=? LIMIT 1");
    $chk->bind_param("iss", $doctor_id, $app_date, $app_time);
    $chk->execute();
    $exists = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($exists) {
      $error = "This time slot is already booked. Please select another time ✅";
    } else {

      // ✅ Insert appointment with Pending status
      $stmt = $conn->prepare("INSERT INTO appointments 
        (patient_name, phone, email, doctor_id, appointment_date, appointment_time, reason, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");

      $stmt->bind_param("sssisss", $patient_name, $phone, $email, $doctor_id, $app_date, $app_time, $reason);

      if ($stmt->execute()) {
        $success = "Appointment booked successfully ✅";
        $_POST = []; // clear after success
      } else {
        $error = "Insert failed: " . $conn->error;
      }
      $stmt->close();

    }
  }
}
?>

<?php include "header.php"; ?>

<section class="page">
  <div class="page-head">
    <h2>Book Appointment</h2>
    <p>Fill the form to book an appointment.</p>
  </div>

  <?php if ($selectedDoctor): ?>
    <div class="msg ok" style="margin-bottom:14px;">
      <span class="icon">🩺</span>
      <span>
        Booking with <b><?php echo htmlspecialchars($selectedDoctor["doctor_name"]); ?></b>
        (<?php echo htmlspecialchars($selectedDoctor["specialization"]); ?>)
        — Available:
        <b><?php echo substr(htmlspecialchars($selectedDoctor["available_from"]), 0, 5); ?></b>
        to
        <b><?php echo substr(htmlspecialchars($selectedDoctor["available_to"]), 0, 5); ?></b>
      </span>
    </div>
  <?php endif; ?>

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

  <form id="appointmentForm" method="POST" class="card" action="book.php">
    <div class="grid">

      <div>
        <label>Patient Name *</label>
        <input type="text" name="patient_name" id="patient_name" class="input"
               value="<?php echo htmlspecialchars($_POST['patient_name'] ?? ''); ?>">
        <small class="err" id="err_patient"></small>
      </div>

      <div>
        <label>Phone *</label>
        <input type="text" name="phone" id="phone" class="input" placeholder="07XXXXXXXX"
               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
        <small class="err" id="err_phone"></small>
      </div>

      <div>
        <label>Email *</label>
        <input type="email" name="email" id="email" class="input"
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        <small class="err" id="err_email"></small>
      </div>

      <div>
        <label>Doctor *</label>
        <select name="doctor_id" id="doctor_id" class="input">
          <option value="">-- Select Doctor --</option>
          <?php foreach ($doctors as $d): ?>
            <?php $sel = ((int)$d["id"] === $currentSelectedDoctorId) ? "selected" : ""; ?>
            <option value="<?php echo (int)$d["id"]; ?>" <?php echo $sel; ?>>
              <?php echo htmlspecialchars($d["doctor_name"] . " (" . $d["specialization"] . ")"); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small class="err" id="err_doctor"></small>
      </div>

      <div>
        <label>Appointment Date *</label>
        <input type="date" name="appointment_date" id="appointment_date" class="input"
               value="<?php echo htmlspecialchars($_POST['appointment_date'] ?? ''); ?>">
        <small class="err" id="err_date"></small>
      </div>

      <div>
        <label>Appointment Time *</label>
        <select name="appointment_time" id="appointment_time" class="input">
          <option value="">-- Select Time --</option>

          <?php
            $selected_time = $_POST["appointment_time"] ?? "";
            $selectedDate  = $_POST["appointment_date"] ?? "";

            if ($selectedDoctor && $selectedDate !== "") {

              // Fetch booked slots
              $booked = [];
              $bt = $conn->prepare("SELECT appointment_time FROM appointments WHERE doctor_id=? AND appointment_date=?");
              $bt->bind_param("is", $currentSelectedDoctorId, $selectedDate);
              $bt->execute();
              $rbt = $bt->get_result();
              while ($row = $rbt->fetch_assoc()) {
                $booked[] = substr($row["appointment_time"], 0, 8);
              }
              $bt->close();

              // Generate slots (30 min)
              $start = strtotime($selectedDoctor["available_from"]);
              $end   = strtotime($selectedDoctor["available_to"]);
              $step  = 30 * 60;

              for ($t = $start; $t < $end; $t += $step) {
                $val = date("H:i:s", $t);
                $lbl = date("h:i A", $t);

                $isBooked = in_array($val, $booked);
                $disabled = $isBooked ? "disabled" : "";
                $tag      = $isBooked ? " (Booked)" : "";

                $s = ($selected_time === $val) ? "selected" : "";
                echo "<option value=\"$val\" $s $disabled>$lbl$tag</option>";
              }

            } elseif ($selectedDoctor) {
              echo "<option value=\"\" disabled>(Select a date first)</option>";
            } else {
              echo "<option value=\"\" disabled>(Select a doctor first)</option>";
            }
          ?>
        </select>
        <small class="err" id="err_time"></small>
      </div>

    </div>

    <div style="margin-top:14px;">
      <label>Reason *</label>
      <textarea name="reason" id="reason" rows="4" class="input"><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
      <small class="err" id="err_reason"></small>
    </div>

    <button type="submit" name="submit" class="btn">Submit Appointment</button>
  </form>
</section>

<?php include "footer.php"; ?>