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
	$account_type = $_POST["account_type"];
	$balance = $_POST["balance"];
	$user = get_user_id();
	$db = getDB();
	$unique=true;
	$count=0;
	$valid=true;
	
	if($account_type=0){
		if((float)$balance >=5.0){
			$valid =true;
		}
		else{
			flash("You need to have at least 5 dollars in your account!");
			
		}
		
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
	
	if($account_type = 0){
		
		$newAccID = $db->prepare("SELECT id FROM Accounts WHERE account_number = :account_number");
		
		
		$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo,expected_total) VALUES(:act_src_id, :act_dest_id, :amount,:action_type, :memo, :expected_total)");
			$r = $stmt->execute([
				":act_src_id" => 1,
				":act_dest_id" => $newAccID,
				":amount" => ($balance * -1),
				":action_type" => 0,
				":memo" => "",
				":expected_total" => ($balance)
			]);
	
			$stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo,expected_total) VALUES(:act_src_id, :act_dest_id, :amount,:action_type, :memo, :expected_total)");
			$r = $stmt->execute([
				":act_src_id" => $newAccID,
				":act_dest_id" => 1,
				":amount" => $balance,
				":action_type" => 0,
				":memo" => "",
				":expected_total" => ($balance)
			]);   
	
	
	}
	
	if($r){
		flash("Yay! Your account was created successfully!");
	}
	else{
		$e = $stmt->errorInfo();
		flash("Uh oh! There was an error while creating your account!");
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");