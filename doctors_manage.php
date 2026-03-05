<?php
require_once "config/db.php";
require_once "auth.php";

require_role("admin");

$search = trim($_GET["search"] ?? "");
$msg = $_GET["msg"] ?? "";

// Fetch doctors (with search)
if ($search !== "") {
  $stmt = $conn->prepare("SELECT * FROM doctors 
                          WHERE doctor_name LIKE ? OR specialization LIKE ?
                          ORDER BY id DESC");
  $like = "%" . $search . "%";
  $stmt->bind_param("ss", $like, $like);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();
} else {
  $result = $conn->query("SELECT * FROM doctors ORDER BY id DESC");
}

$doctors = [];
if ($result) {
  while ($row = $result->fetch_assoc()) $doctors[] = $row;
}
?>

<?php include "header.php"; ?>

<?php if ($msg === "deleted"): ?>
  <div class="msg ok" style="margin-top:14px;">
    <span class="icon">✅</span>
    <span>Doctor deleted successfully.</span>
  </div>
<?php endif; ?>

<?php if ($msg === "cannot_delete"): ?>
  <div class="msg bad" style="margin-top:14px;">
    <span class="icon">⚠️</span>
    <span>Cannot delete doctor (appointments exist). Delete appointments first.</span>
  </div>
<?php endif; ?>

<section class="page">
  <div class="page-head">
    <h2>Manage Doctors</h2>
    <p>Admin panel: add, edit, delete doctor details.</p>
  </div>

  <div class="card" style="margin-bottom:14px;">
    <form method="GET" class="grid">
      <div>
        <label>Search Doctor</label>
        <input class="input" type="text" name="search"
               value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Doctor name / specialization">
      </div>

      <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
        <button class="btn" type="submit">Search</button>
        <a class="btn ghost" href="doctors_manage.php" style="text-decoration:none;display:inline-block;">Reset</a>
        <a class="btn" href="doctor_add.php" style="text-decoration:none;display:inline-block;">+ Add Doctor</a>
      </div>
    </form>
  </div>

  <div class="card" style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;min-width:900px;">
      <thead>
        <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,.18);">
          <th style="padding:12px;">ID</th>
          <th style="padding:12px;">Doctor</th>
          <th style="padding:12px;">Specialization</th>
          <th style="padding:12px;">From</th>
          <th style="padding:12px;">To</th>
          <th style="padding:12px;">Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php if (count($doctors) === 0): ?>
          <tr>
            <td colspan="6" style="padding:14px;opacity:.9;">No doctors found.</td>
          </tr>
        <?php endif; ?>

        <?php foreach ($doctors as $d): ?>
          <tr style="border-bottom:1px solid rgba(255,255,255,.10);">
            <td style="padding:12px;"><?php echo (int)$d["id"]; ?></td>
            <td style="padding:12px;font-weight:800;"><?php echo htmlspecialchars($d["doctor_name"]); ?></td>
            <td style="padding:12px;"><?php echo htmlspecialchars($d["specialization"]); ?></td>
            <td style="padding:12px;"><?php echo substr(htmlspecialchars($d["available_from"]),0,5); ?></td>
            <td style="padding:12px;"><?php echo substr(htmlspecialchars($d["available_to"]),0,5); ?></td>
            <td style="padding:12px;">
              <a class="btn ghost" style="text-decoration:none;display:inline-block;animation:none"
                 href="doctor_edit.php?id=<?php echo (int)$d["id"]; ?>">Edit</a>

              <a class="btn ghost" style="text-decoration:none;display:inline-block;animation:none;margin-left:8px"
                 href="doctor_delete.php?id=<?php echo (int)$d["id"]; ?>"
                 onclick="return confirm('Delete this doctor?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include "footer.php"; ?>