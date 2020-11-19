<?php require_once(__DIR__ . "/partials/nav.php"); ?>


<form method="POST">

	<label>Account Type</label>
	<select name="account_type">
		<option value="0">Checking</option>
		<option value="1">Saving</option>
		<option value="2">Loan</option>

	</select>


	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){

	//TODO add proper validation/checks
	//$account_number = $_POST["account_number"];
	$account_number = rand(100000000000,999999999999);
	$account_type = $_POST["account_type"];
	$balance = 0.0;
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, balance, user_id) VALUES(:account_number, :account_type, :balance, :user)");
	$r = $stmt->execute([
		":account_number"=>$account_number,
		":account_type"=>$account_type,
		":balance"=>$balance,
		":user"=>$user
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");