<?php
require_once "config/db.php";

/* Search */
$search = trim($_GET["search"] ?? "");

/* Query */
$sql = "
  SELECT 
    a.id,
    a.patient_name,
    a.phone,
    a.email,
    a.appointment_date,
    a.appointment_time,
    a.reason,
    a.status,
    a.created_at,
    d.doctor_name,
    d.specialization
  FROM appointments a
  JOIN doctors d ON a.doctor_id = d.id
";

$params = [];
$types = "";

if ($search !== "") {
  $sql .= " WHERE a.patient_name LIKE ? OR a.phone LIKE ? OR a.email LIKE ? OR d.doctor_name LIKE ? OR d.specialization LIKE ? ";
  $like = "%" . $search . "%";
  $params = [$like, $like, $like, $like, $like];
  $types = "sssss";
}

$sql .= " ORDER BY a.id DESC ";

$stmt = $conn->prepare($sql);

if ($search !== "") {
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($r = $result->fetch_assoc()) $rows[] = $r;

$stmt->close();
?>

<?php include "header.php"; ?>

<section class="page">
  <div class="page-head">
    <h2>Appointments</h2>
    <p>View all booked appointments.</p>
  </div>

  <form method="GET" class="card" style="margin-bottom:14px;">
    <div class="grid">
      <div>
        <label>Search (Patient / Doctor / Phone / Email)</label>
        <input class="input" type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Type and press Enter">
      </div>
      <div style="display:flex;align-items:flex-end;gap:10px;">
        <button class="btn" type="submit">Search</button>
        <a class="btn" href="view.php" style="text-decoration:none;display:inline-block;">Reset</a>
      </div>
    </div>
  </form>

  <div class="card" style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;min-width:980px;">
      <thead>
        <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,.18);">
          <th style="padding:12px;">ID</th>
          <th style="padding:12px;">Patient</th>
          <th style="padding:12px;">Doctor</th>
          <th style="padding:12px;">Date</th>
          <th style="padding:12px;">Time</th>
          <th style="padding:12px;">Phone</th>
          <th style="padding:12px;">Email</th>
          <th style="padding:12px;">Status</th>
          <th style="padding:12px;">Created</th>
        </tr>
      </thead>

      <tbody>
        <?php if (count($rows) === 0): ?>
          <tr>
            <td colspan="9" style="padding:14px;opacity:.9;">No appointments found.</td>
          </tr>
        <?php endif; ?>

        <?php foreach ($rows as $row): ?>
          <tr style="border-bottom:1px solid rgba(255,255,255,.10);">
            <td style="padding:12px;"><?php echo (int)$row["id"]; ?></td>
            <td style="padding:12px;">
              <div style="font-weight:800;"><?php echo htmlspecialchars($row["patient_name"]); ?></div>
              <div style="opacity:.8;font-size:12px;max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?php echo htmlspecialchars($row["reason"]); ?>
              </div>
            </td>
            <td style="padding:12px;">
              <div style="font-weight:800;"><?php echo htmlspecialchars($row["doctor_name"]); ?></div>
              <div style="opacity:.8;font-size:12px;"><?php echo htmlspecialchars($row["specialization"]); ?></div>
            </td>
            <td style="padding:12px;"><?php echo htmlspecialchars($row["appointment_date"]); ?></td>
            <td style="padding:12px;"><?php echo substr(htmlspecialchars($row["appointment_time"]), 0, 5); ?></td>
            <td style="padding:12px;"><?php echo htmlspecialchars($row["phone"]); ?></td>
            <td style="padding:12px;"><?php echo htmlspecialchars($row["email"]); ?></td>
            <td style="padding:12px;">
              <span style="padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);">
                <?php echo htmlspecialchars($row["status"]); ?>
              </span>
            </td>
            <td style="padding:12px;opacity:.85;"><?php echo htmlspecialchars($row["created_at"]); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>

    </table>
  </div>
</section>

<?php include "footer.php"; ?>