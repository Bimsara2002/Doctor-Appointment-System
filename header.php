<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$current = basename($_SERVER["PHP_SELF"]);
$isLoginPage = ($current === "index.php");
$user = $_SESSION["user"] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>DocCare</title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>

<header class="topbar">
<div class="container">
<div class="nav">

<div class="logo">Doc<span>Care</span></div>

<?php if (!$isLoginPage): ?>

<div class="menu">


<?php if ($user && $user["role"] == "staff"): ?>
<a href="dashboard.php">Home</a>
<a href="book.php">Book</a>
<a href="view.php">Appointments</a>
<a href="doctors.php">Doctors</a>
<?php endif; ?>

<?php if ($user && $user["role"] == "admin"): ?>
<a href="doctors_manage.php">Manage Doctors</a>
<a href="appointments_manage.php">Appointments</a>
<?php endif; ?>

<?php if ($user): ?>
<a href="logout.php">Logout</a>
<?php endif; ?>

</div>

<?php endif; ?>

</div>
</div>
</header>

<main class="container">