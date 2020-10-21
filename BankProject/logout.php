<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//since this function call is included we can omit it here. Having multiple calls to session_start() will cause errors/warnings
//session_start();
// remove all session variables
session_unset();
// destroy the session
session_destroy();
header("Location: login.php");
echo "You've been logged out<br>";
//echo "<pre>" . var_export($_SESSION, true) . "</pre>";
?>
