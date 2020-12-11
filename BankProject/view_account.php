<?php require_once(__DIR__ . "/partials/nav.php"); ?>

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
    $stmt = $db->prepare("SELECT Acc.id, account_number, account_type, balance, opened_date, last_updated, user_id, Users.username FROM Accounts as Acc JOIN Users on Acc.user_id = Users.id where Acc.id = :id");
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
		
        <div class="list-group-item">
		
            <div>
				<div><br>Account Number: <?php safer_echo($result["account_number"]); ?></br></div>
                <div>Account Type: <?php getAccType($result["account_type"]); ?></div>
                <div>Balance: <?php safer_echo($result["balance"]); ?></div>
                <div>Date Opened: <?php safer_echo($result["opened_date"]); ?></div>
                <div>
					<a type = "button" href="list_transactions.php?id=<?php safer_echo($result["id"]);?>">View Transaction History</a>
				</div>
            </div>
			
        </div>
		
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
