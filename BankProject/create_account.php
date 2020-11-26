<?php require_once(__DIR__ . "/partials/nav.php"); ?>


<form method="POST">

	<label>Account Type</label>
	<select name="account_type">
		<option value="0">Checking</option>
		<option value="1">Saving</option>
		<option value="2">Loan</option>

	</select>

	<label>Balance</label>
	<input type="number" min="0.00" name="balance" step="0.01"/>
	
	
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){

	//TODO add proper validation/checks
	//$account_number = $_POST["account_number"];
	$account_number = 0;
	$account_type = $_POST["account_type"];
	$balance = $_POST["balance"];
	$user = get_user_id();
	$db = getDB();
	$unique=false;
	$count=0;
	$valid=false;
	
	switch($account_type){
		case 0:
			if($balance >= 5){
				$valid =true;
			}
			else{
				echo("You need to have at least 5 dollars in your account!");
				exit;
			}
			break;
	}
	
	while(!$unique && $count<10 && $valid){
		$account_number = rand(100000000000,999999999999);
		$stmt = $db->prepare("SELECT account_number from Accounts WHERE account_number = :newNum");
		$r = $stmt->execute([
		":newNum" => $account_number
		]);
		$r2 = $stmt -> fetch(PDO::FETCH_ASSOC);
		if(empty($r2)){
			$unique = true;
			break;
		}
		$count++;
	
	}
	if($count == 10 && !$unique){
		$valid = false;
		flash("Uh oh, there was an error while creating your account number");
	}
	
	
	
	$stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, balance, user_id) VALUES(:account_number, :account_type, :balance, :user)");
	$r = $stmt->execute([
		":account_number"=>$account_number,
		":account_type"=>$account_type,
		":balance"=>$balance,
		":user"=>$user
	]);
	
	
	$stmt = $db->prepare("SELECT id FROM Accounts WHERE account_number = '000000000000' ");
	$stmt->execute();
	$r = $stmt->fetch(PDO::FETCH_ASSOC);
	$world_id = $r["id"];
	
	switch($account_type){
		
		case 0:
		
			$stmt = $db->prepare("SELECT id FROM Accounts WHERE account_number = :account_number");
			$r = $stmt->execute([
			":account_number" => $account_number
			]);
			$r2 = $stmt->fetch(PDO::FETCH_ASSOC);
			$newAccID = $r2["id"];
			
			
			$stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :world_id");
			$r = $stmt->execute([
			":world_id" => $world_id
			]);
			$r2 = $stmt->fetch(PDO::FETCH_ASSOC);
			$worldBal = $r2["balance"];
			
			
			
			$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:act_src_id, :act_dest_id, :amount,:action_type, :memo, :expected_total)");
				$r = $stmt->execute([
					":act_src_id" => $world_id,
					":act_dest_id" => $newAccID,
					":amount" => ($balance * -1),
					":action_type" => 0,
					":memo" => "",
					":expected_total" => ($worldBal - $balance)
				]);
		
			$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:act_src_id, :act_dest_id, :amount,:action_type, :memo, :expected_total)");
			$r = $stmt->execute([
				":act_src_id" => $newAccID,
				":act_dest_id" => $world_id,
				":amount" => $balance,
				":action_type" => 0,
				":memo" => "",
				":expected_total" => $balance
			]);   
		
			break;
	}
	
	$stmt = $db->prepare("UPDATE Accounts set balance=:balance where id=:id");
	$r = $stmt->execute([
	":balance" => ($worldBal - $balance),
	":id" => $world_id
	]);
	
	
	if($r){
		flash("Yay! Your account was created successfully!");
		/*
		<?php
			header('Location: ViewAccount.php?id= . $accountID');
		?>
		*/
	}
	else{
		
		
		flash("Uh oh! There was an error while creating your account!");
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");