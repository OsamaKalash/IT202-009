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
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>
<?php
//saving
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$account_number = $_POST["account_number"];
	$account_type = $_POST["account_type"];
	$balance = $_POST["balance"];
	$nst = date('Y-m-d H:i:s');//calc
	$user = get_user_id();
	$db = getDB();
	if(isset($id)){
		$stmt = $db->prepare("UPDATE Accounts set account_number=:account_number, account_type=:account_type, balance=:balance, opened_date=:nst, last_updated=:nst where id=:id");
		//$stmt = $db->prepare("INSERT INTO Accounts (name, state, base_rate, mod_min, mod_max, next_stage_time, user_id) VALUES(:name, :state, :br, :min,:max,:nst,:user)");
		$r = $stmt->execute([
			":account_number"=>$account_number,
			":account_type"=>$account_type,
			":balance"=>$balance,
			":nst"=>$nst,
			":id"=>$id
		]);
		if($r){
			flash("Updated successfully with id: " . $id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
	}
	else{
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>
<?php
//fetching
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Accounts where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
	<label>Account Number</label>
	<input name="account_number" type="number" max="999999999999" min="1" value="<?php echo $result["account_number"];?>"/>
	<label>Account Type</label>
	<select name="account_type" value="<?php echo $result["account_type"];?>">
		<option value="0" <?php echo ($result["account_type"] == "0"?'selected="selected"':'');?>>Checking</option>
        <option value="1" <?php echo ($result["account_type"] == "1"?'selected="selected"':'');?>>Saving</option>
        <option value="2" <?php echo ($result["account_type"] == "2"?'selected="selected"':'');?>>Loan</option>
                
	</select>
	<label>Balance</label>
	<input type="number" min="0.00" name="balance" step="0.01" value="<?php echo $result["balance"];?>" />
	
</form>


<?php require(__DIR__ . "/partials/flash.php");
