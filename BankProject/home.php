<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
?>
<?php
    if($email=""){
        <p><?php echo "Please log in"; ?></p>
    }
    else{
        <p>Welcome, <?php echo $email; ?></p>
    }
?>


