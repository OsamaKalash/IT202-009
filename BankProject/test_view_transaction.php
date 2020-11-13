<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT act_src_id, act_dest_id, amount, action_type, memo,expected_total from Transactions");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <p><b>Account Info:</b></p>
        </div>
        <div class="card-body">
            <div>
				<div>Source Account: <?php safer_echo($result["act_src_id"]); ?></div>
                <div>Destination Account: <?php safer_echo($result["act_dest_id"]); ?></div>
                <div>Transaction Type: <?php safer_echo($result["action_type"]); ?></div>
                <div>Amount: <?php safer_echo($result["amount"]); ?></div>
                <div>Expected Total:<?php safer_echo($result["expected_total"]); ?></div>
                <div>Memo:<?php safer_echo($result["memo"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");

