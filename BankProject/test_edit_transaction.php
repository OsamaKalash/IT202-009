
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
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $amount = $_POST["amount"];
    $action_type = $_POST["action_type"];
	$memo = $_POST["memo"];
    //$user = get_user_id();
    $db = getDB();
	if(isset($id)){
	switch($action_type){
		case 0:
			$stmt = $db->prepare("UPDATE Transactions set amount=:amount, action_type=:action_type, memo=:memo,expected_total=:expected_total where id=:id");
			$r = $stmt->execute([
				":id" => $id,
				":amount" => ($amount * -1),
				":action_type" => $action_type,
				":memo" => $memo,
				":expected_total" => ($bal1 - $amount)
			]);
	
			$stmt = $db->prepare("UPDATE Transactions set amount=:amount, action_type=:action_type, memo=:memo,expected_total=:expected_total where id=:id");
			$r = $stmt->execute([
				":id" => $id,
				":amount" => $amount,
				":action_type" => $action_type,
				":memo" => $memo,
				":expected_total" => ($bal1 + $amount)
			]);   
			break;  
		case 1:
			$stmt = $db->prepare("UPDATE Transactions set amount=:amount, action_type=:action_type, memo=:memo,expected_total=:expected_total where id=:id");
			$r = $stmt->execute([
				":id" => $id,
				":amount" => ($amount),
				":action_type" => $action_type,
				":memo" => $memo,
				":expected_total" => ($bal2 + $amount)
			   
			]);
			$stmt = $db->prepare("UPDATE Transactions set amount=:amount, action_type=:action_type, memo=:memo,expected_total=:expected_total where id=:id");
			$r = $stmt->execute([
				":id" => $id,
				":amount" => ($amount * -1),
				":action_type" => $action_type,
				":memo" => $memo,
				":expected_total" => ($bal2 - $amount)
			]);
			break;
	}
	if ($r) {
            flash("Updated successfully with id: " . $id);
        }
        else {
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
	$stmt = $db->prepare("SELECT * FROM Transactions where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

	<h3>Edit Transaction</h3>
    <form method="POST">
       
	<label>Transaction Type</label>
	<select name="action_type" value="<?php echo $result["action_type"];?>">
		<option value="0" <?php echo ($result["action_type"] == "0"?'selected="selected"':'');?>>Deposit</option>
        <option value="1" <?php echo ($result["action_type"] == "1"?'selected="selected"':'');?>>Withdraw</option>
        <option value="2" <?php echo ($result["action_type"] == "2"?'selected="selected"':'');?>>Transfer</option>
	</select>
	
	
	<label>Amount</label>
		<input type="number" min="0.00" name="amount" step="0.01" value="<?php echo $result["amount"];?>"/>
		
	<label>Memo</label>
		<input type="text" name="memo" value="<?php echo $result["memo"];?>"/>
		
    <input type="submit" name="save" value="Create"/>
    
	</form>


<?php require(__DIR__ . "/partials/flash.php");
