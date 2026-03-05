<?php
require_once "config/db.php";
require_once "auth.php";
require_login();

$user = $_SESSION["user"];
$role = $user["role"];

$msg = "";

// Update status
if (isset($_POST["update_status"])) {
  $id = (int)($_POST["id"] ?? 0);
  $status = $_POST["status"] ?? "Pending";

  // Only allow valid values
  $allowed = ["Pending","Approved","Completed","Cancelled"];
  if ($id > 0 && in_array($status, $allowed)) {
    $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    $msg = "Status updated ✅";
  }
}

// Fetch appointments
$res = $conn->query("
  SELECT a.*, d.doctor_name
  FROM appointments a
  JOIN doctors d ON a.doctor_id = d.id
  ORDER BY a.id DESC
");

$appointments = [];
if ($res) while ($row = $res->fetch_assoc()) $appointments[] = $row;
?>

<?php include "header.php"; ?>

<section class="page">
  <div class="page-head">
    <h2>Appointments</h2>
    <p>Update booking status (Pending / Approved / Completed / Cancelled).</p>
  </div>

  <?php if ($msg): ?>
    <div class="msg ok"><span class="icon">✅</span><span><?php echo htmlspecialchars($msg); ?></span></div>
  <?php endif; ?>

  <div class="card" style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;min-width:1000px;">
      <thead>
        <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,.18);">
          <th style="padding:12px;">ID</th>
          <th style="padding:12px;">Patient</th>
          <th style="padding:12px;">Doctor</th>
          <th style="padding:12px;">Date</th>
          <th style="padding:12px;">Time</th>
          <th style="padding:12px;">Status</th>
          <th style="padding:12px;">Action</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($appointments as $a): ?>
          <tr style="border-bottom:1px solid rgba(255,255,255,.10);">
            <td style="padding:12px;"><?php echo (int)$a["id"]; ?></td>
            <td style="padding:12px;"><?php echo htmlspecialchars($a["patient_name"]); ?></td>
            <td style="padding:12px;"><?php echo htmlspecialchars($a["doctor_name"]); ?></td>
            <td style="padding:12px;"><?php echo htmlspecialchars($a["appointment_date"]); ?></td>
            <td style="padding:12px;"><?php echo substr(htmlspecialchars($a["appointment_time"]),0,5); ?></td>

            <td style="padding:12px;">
              <span class="badge status-<?php echo strtolower($a["status"]); ?>">
                <?php echo htmlspecialchars($a["status"]); ?>
              </span>
            </td>

            <td style="padding:12px;">
              <form method="POST" style="display:flex;gap:8px;align-items:center;">
                <input type="hidden" name="id" value="<?php echo (int)$a["id"]; ?>">
                <select name="status" class="input" style="padding:8px;border-radius:10px;">
                  <?php
                    $st = $a["status"];
                    $opts = ["Pending","Approved","Completed","Cancelled"];
                    foreach ($opts as $op) {
                      $sel = ($st === $op) ? "selected" : "";
                      echo "<option value=\"$op\" $sel>$op</option>";
                    }
                  ?>
                </select>
                <button type="submit" name="update_status" class="btn" style="padding:10px 14px;">Update</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>

    </table>
  </div>
</section>

<?php include "footer.php"; ?>