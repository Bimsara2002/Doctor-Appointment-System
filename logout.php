<?php
// logout.php

session_start();

// remove all session data
session_unset();
session_destroy();

// redirect to login page
header("Location: index.php");
exit();