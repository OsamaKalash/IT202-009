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
	
	$stmt = $db->prepare("SELECT id, act_src_id, act_dest_id, amount, action_type, memo,expected_total from Transactions WHERE act_src_id = :id  LIMIT 10");
    $r = $stmt->execute([":id" => $id]);
	if ($r) {
        $transR = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
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
				<div>Account Number: <?php safer_echo($result["account_number"]); ?></div>
                <div>Account Type: <?php getAccType($result["account_type"]); ?></div>
                <div>Balance: <?php safer_echo($result["balance"]); ?></div>
                <div>Date Opened: <?php safer_echo($result["opened_date"]); ?></div>
                
            </div>
			
        </div>
		
		<div class="card-title">
            <p><b>Transaction History:</b></p>
        </div>
		
		<div class="results">
    <?php if (count($transR) > 0): ?>
        <div class="list-group">
            <?php foreach ($transR as $r): ?>
                <div class="list-group-item">
					<div>
                        <div>Transaction Type:</div>
                        <div><?php safer_echo($r["action_type"]); ?></div>
                    </div>	
					<div>
                        <div>Amount:</div>
                        <div><?php safer_echo($r["amount"]); ?></div>
                    </div>
					<div>
						<div>Memo:</div>
						<div><?php safer_echo($r["memo"]); ?></div>
					</div>
					<div>
						<div>Date Created:</div>
						<div><?php safer_echo($r["created"]); ?></div>
					</div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>





		
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
