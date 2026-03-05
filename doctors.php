<?php
require_once "config/db.php";

$doctors = [];
$res = $conn->query("SELECT id, doctor_name, specialization, available_from, available_to FROM doctors ORDER BY doctor_name ASC");
if ($res) {
  while ($row = $res->fetch_assoc()) $doctors[] = $row;
}
?>

<?php include "header.php"; ?>

<section class="page">
  <div class="page-head">
    <h2>Doctors</h2>
    <p>Available doctors and their clinic hours.</p>
  </div>

  <div class="card">
    <?php if (count($doctors) === 0): ?>
      <p style="opacity:.9;">No doctors found. Insert sample doctors into the database.</p>
    <?php else: ?>
      <div class="doctor-grid">
        <?php foreach ($doctors as $d): ?>
          <div class="doctor-card">
            <div class="doctor-name">
              <?php echo htmlspecialchars($d["doctor_name"]); ?>
            </div>

            <div class="doctor-spec">
              <?php echo htmlspecialchars($d["specialization"]); ?>
            </div>

            <div class="doctor-time">
              🕒 Available:
              <b><?php echo substr(htmlspecialchars($d["available_from"]), 0, 5); ?></b>
              to
              <b><?php echo substr(htmlspecialchars($d["available_to"]), 0, 5); ?></b>
            </div>

            <a href="book.php?doctor_id=<?php echo (int)$d['id']; ?>" class="btn" style="display:inline-block;text-decoration:none;margin-top:12px;">
              Book Now
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include "footer.php"; ?>