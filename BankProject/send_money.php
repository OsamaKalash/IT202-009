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

    <h3>Send Money</h3>
    <form method="POST">
        
	
	<label>Choose an Account</label>
	<select name="act_src_id">
		<?php foreach($items as $row):?>
			<option value="<?php echo $row["id"]?>">
				<?php echo $row["account_number"]?>
			</option>
		<?php endforeach;?>
	</select>
	
	<label>Receiver's Last Name</label>
		<input type="text" name="input_last_name"/>
	
	<label>Last 4 Digits of Receiver's Account</label>
		<input type="number" min="0000" max = "9999" name="last_digits" step="1"/>
		
	<label>Amount</label>
		<input type="number" min="0.00" name="amount" step="0.01"/>
		
	<label>Memo</label>
		<input type="text" name="memo"/>
		
        <input type="submit" name="save" value="Create"/>
    
	</form>
	


<?php
if (isset($_POST["save"])) {
	
	$db = getDB();
	
	$stmt = $db->prepare("SELECT id FROM Users WHERE last_name = :last");
	$e = $stmt->execute([
	":last" => $_POST["input_last_name"]
	]);
	$r = $stmt->fetch(PDO::FETCH_ASSOC);
	if($r)
	{
	
	$receive_id = $r["id"];
    $act_src_id = $_POST["act_src_id"];
	$amount = $_POST["amount"];
	$memo = $_POST["memo"];
	$last_digits = $_POST["last_digits"];
	}
	else{
		echo("We couldn't find a user with that last name!");
		exit;
	}
    
	$stmt = $db->prepare("SELECT id FROM Accounts WHERE user_id = :id AND RIGHT(account_number,4) = :digits");
	$e = $stmt->execute([
	":id" => $receive_id,
	":digits" => $last_digits
	]);
	$r = $stmt->fetch(PDO::FETCH_ASSOC);
	if($r){
		$act_dest_id = $r["id"];
	}
	else{
		echo("There was an error while finding the receiver's account!");
		exit;
	}
	$stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :dest_id");
		$r = $stmt->execute([
		":dest_id" => $act_dest_id
		]);
		$r2 = $stmt->fetch(PDO::FETCH_ASSOC);
		$destBal = (float)$r2["balance"];
	
	$stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :my_id");
		$stmt->execute([
		":my_id" => $act_src_id
		]);
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		$myBal = (float)$r["balance"];
	
	if($amount > $myBal){
		echo("You can't send more money than what the acccount has!");
		exit;
	}
	
	$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:act_src_id, :act_dest_id, :amount, :action_type, :memo, :expected_total)");
	$r = $stmt->execute([
		":act_src_id" => $act_src_id,
		":act_dest_id" => $act_dest_id,
		":amount" => (-1 * $amount),
		":action_type" => 3,
		":memo" => $memo,
		":expected_total" => ($myBal - $amount)
	]);

	$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo,expected_total) VALUES(:act_src_id, :act_dest_id, :amount, :action_type, :memo, :expected_total)");
	$r = $stmt->execute([
		":act_src_id" => $act_dest_id,
		":act_dest_id" => $act_src_id,
		":amount" => ($amount),
		":action_type" => 3,
		":memo" => $memo,
		":expected_total" => ($destBal + $amount)
	]);   
	
	$stmt = $db->prepare("UPDATE Accounts set balance=:balance where id=:id");
	$r = $stmt->execute([
	":balance" => ($destBal + $amount),
	":id" => $act_dest_id
	]);
		
	$stmt = $db->prepare("UPDATE Accounts set balance=:balance where id=:id");
	$r = $stmt->execute([
	":balance" => ($myBal - $amount),
	":id" => $act_src_id
	]);
    
	
	
	
    if ($r) {
        flash("Your money was sent successfully!");
    }
    else {
        //$e = $stmt->errorInfo();
        flash("Uh oh, there was an error while sending your money!");
    }
}

?>





<?php require(__DIR__ . "/partials/flash.php");