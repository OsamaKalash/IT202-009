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

    <h3>Withdraw</h3>
    <form method="POST">
        
	
	<label>Choose an Account</label>
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
	$stmt = $db->prepare("SELECT id FROM Accounts WHERE account_number = '000000000000' ");
	$stmt->execute();
	$r = $stmt->fetch(PDO::FETCH_ASSOC);
	$world_id = $r["id"];
	
    $act_src_id = $world_id;
    $act_dest_id = $_POST["act_dest_id"];
    $amount = $_POST["amount"];
    //$action_type = $_POST["action_type"];
	$memo = $_POST["memo"];
    //$user = get_user_id();
    
	$stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :world_id");
		$r = $stmt->execute([
		":world_id" => $world_id
		]);
		$r2 = $stmt->fetch(PDO::FETCH_ASSOC);
		$worldBal = $r2["balance"];
	
	$stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :my_id");
		$stmt->execute([
		":my_id" => $act_dest_id
		]);
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		$myBal = (float)$r["balance"];
	
	
	
	$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:act_src_id, :act_dest_id, :amount, :action_type, :memo, :expected_total)");
	$r = $stmt->execute([
		":act_src_id" => $act_src_id,
		":act_dest_id" => $act_dest_id,
		":amount" => ($amount),
		":action_type" => 1,
		":memo" => $memo,
		":expected_total" => ($worldBal + $amount)
	]);

	$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo,expected_total) VALUES(:act_src_id, :act_dest_id, :amount, :action_type, :memo, :expected_total)");
	$r = $stmt->execute([
		":act_src_id" => $act_dest_id,
		":act_dest_id" => $act_src_id,
		":amount" => ($amount * -1),
		":action_type" => 1,
		":memo" => $memo,
		":expected_total" => ($myBal - $amount)
	]);   
	
	$stmt = $db->prepare("UPDATE Accounts set balance=:balance where id=:id");
	$r = $stmt->execute([
	":balance" => ($worldBal + $amount),
	":id" => $world_id
	]);
		
	$stmt = $db->prepare("UPDATE Accounts set balance=:balance where id=:id");
	$r = $stmt->execute([
	":balance" => ($myBal - $amount),
	":id" => $act_dest_id
	]);
    
	
	
	
    if ($r) {
        flash("Your withdrawal was successful!");
    }
    else {
        //$e = $stmt->errorInfo();
        flash("Uh oh, there was an error while completing your withdrawal!");
    }
}

?>





<?php require(__DIR__ . "/partials/flash.php");
