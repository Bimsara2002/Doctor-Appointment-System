<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config/db.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once "auth.php";

if (isset($_SESSION["user"])) {
  header("Location: dashboard.php");
  exit;
}

$error = "";

if (isset($_POST["login"])) {

  $username = trim($_POST["username"] ?? "");
  $password = trim($_POST["password"] ?? "");

  if ($username === "" || $password === "") {
    $error = "Please enter username and password.";
  } else {

    $stmt = $conn->prepare("SELECT id, full_name, username, role FROM users WHERE username=? AND password=? LIMIT 1");
    if (!$stmt) {
      die("SQL Prepare Failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;

    $stmt->close();

    if ($user) {
      $_SESSION["user"] = $user;
      header("Location: dashboard.php");
      exit;
    } else {
      $error = "Invalid username or password.";
    }
  }
}
?>

<?php include "header.php"; ?>

<section class="login-center">
  <div class="login-card">

    <h2 style="text-align:center">Login</h2>
    <p style="text-align:center;opacity:.8">Doctor Appointment System</p>

    <?php if ($error): ?>
      <div class="msg bad"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
      <label>Username</label>
      <input class="input" type="text" name="username" required>

      <label style="margin-top:10px;">Password</label>
      <input class="input" type="password" name="password" required>

      <button class="btn" type="submit" name="login" style="width:100%;margin-top:15px">
        Login
      </button>
    </form>

  </div>
</section>

<?php include "footer.php"; ?>