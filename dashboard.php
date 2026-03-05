<?php
require_once "auth.php";

// user must be logged in
require_login();

$role = $_SESSION["user"]["role"] ?? "";

// redirect by role
if ($role === "admin") {

    header("Location: doctors_manage.php");
    exit();

} elseif ($role === "staff") {

    header("Location: book.php");
    exit();

} else {

    // unknown role → logout
    header("Location: logout.php");
    exit();

}
?>