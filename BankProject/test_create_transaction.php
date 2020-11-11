<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
$items = get_dropdown_items();
?>

    <h3>Create Transaction</h3>
    <form method="POST">
        
	<label>Transaction Type</label>
	<select name="action_type">
		<option value="0">Deposit</option>
		<option value="1">Withdraw</option>
		<option value="2">Transfer</option>
	</select>	
	
	<label>Source Account</label>
	<select name="act_src_id">
		<?php foreach($items as $index=>$row):?>
			<option value="<?php echo $index;?>">
				<?php echo $row['account_number'];?>
			</option>
		<?php endforeach;?>
	</select>
	
	<label>Destination Account</label>
	<select name="act_dest_id">
		<?php foreach($items as $index=>$row):?>
			<option value="<?php echo $index;?>">
				<?php echo $row['account_number'];?>
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
    //TODO add proper validation/checks
    $act_src_id = $_POST["act_src_id"];
    $act_dest_id = $_POST["act_dest_id"];
    $amount = $_POST["amount"];
    $action_type = $_POST["action_type"];
	$memo = $_POST["memo"];
	
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo) VALUES(:act_src_id, :act_dest_id, :amount,:action_type, :memo, :user)");
    $r = $stmt->execute([
        ":act_src_id" => $act_src_id,
        ":act_dest_id" => $act_dest_id,
        ":amount" => $amount,
        ":action_type" => $action_type,
		":memo" => $memo,
        ":user" => $user
    ]);
    if ($r) {
        flash("Created successfully with id: " . $db->lastInsertId());
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php");
