<?php require_once(__DIR__ . "/partials/nav.php"); ?>


<?php

	$user = get_user_id();
	$db = getDB();
	
	$stmt = $db->prepare("SELECT id, account_number from Accounts WHERE user_id = :user");
	$r = $stmt->execute([
	":user" => $user
	]);
	$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <h3>Transfer</h3>
    <form method="POST">
        
	
	<label>Source Account:</label>
	<select name="act_src_id">
		<?php foreach($items as $row):?>
			<option value="<?php echo $row["id"]?>">
				<?php echo $row["account_number"]?>
			</option>
		<?php endforeach;?>
	</select>
	
	<label>Destination Account:</label>
	<select name="act_dest_id">
		<?php foreach($items as $row):?>
			<option value="<?php echo $row["id"]?>">
				<?php echo $row["account_number"]?>
			</option>
		<?php endforeach;?>
	</select>
	
	
	<label>Amount</label>
		<input type="number" min="0.00" name="amount" step="0.01"/>
		
	<label>Memo</label>
		<input type="text" name="memo"/>
		
        <input type="submit" name="save" value="Create"/>
    
	</form>
	


<?php
if (isset($_POST["save"])) {
	
	$db = getDB();
    $act_dest_id = $_POST["act_dest_id"];
	$act_src_id = $_POST["act_src_id"];
    $amount = $_POST["amount"];
    //$action_type = $_POST["action_type"];
	$memo = $_POST["memo"];
    //$user = get_user_id();
    
	
	$stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :src_id");
		$stmt->execute([
		":src_id" => $act_src_id
		]);
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		$srcBal = (float)$r["balance"];
	
	$stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :dest_id");
		$stmt->execute([
		":dest_id" => $act_dest_id
		]);
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		$destBal = (float)$r["balance"];
	
	if($amount > $srcBal){
		echo("You can't transfer more money than what the acccount has!");
		exit;
	}
	
	
	$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:act_src_id, :act_dest_id, :amount, :action_type, :memo, :expected_total)");
	$r = $stmt->execute([
		":act_src_id" => $act_src_id,
		":act_dest_id" => $act_dest_id,
		":amount" => ($amount * -1),
		":action_type" => 2,
		":memo" => $memo,
		":expected_total" => ($srcBal - $amount)
	]);

	$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo,expected_total) VALUES(:act_src_id, :act_dest_id, :amount, :action_type, :memo, :expected_total)");
	$r = $stmt->execute([
		":act_src_id" => $act_dest_id,
		":act_dest_id" => $act_src_id,
		":amount" => $amount,
		":action_type" => 2,
		":memo" => $memo,
		":expected_total" => ($destBal + $amount)
	]);   
	
	$stmt = $db->prepare("UPDATE Accounts set balance=:balance where id=:src_id");
	$r = $stmt->execute([
	":balance" => ($srcBal - $amount),
	":src_id" => $act_src_id
	]);
		
	$stmt = $db->prepare("UPDATE Accounts set balance=:balance where id=:dest_id");
	$r = $stmt->execute([
	":balance" => ($destBal + $amount),
	":dest_id" => $act_dest_id
	]);
    
	
	
	
    if ($r) {
        flash("Your transfer was successful!");
    }
    else {
        //$e = $stmt->errorInfo();
        flash("Uh oh, there was an error while completing your transfer!");
    }
}

?>





<?php require(__DIR__ . "/partials/flash.php");
