<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php if(isset($_GET["id"])){
    $id = $_GET["id"];
}
?>




<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$query = "";
$query2 = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
	$timestamp = date('Y-m-d H:i:s',strtotime($query));
	$_SESSION["timestamp"] = $timestamp;
}
else if(isset($_SESSION["timestamp"])){
	$timestamp = $_SESSION["timestamp"];
}

if (isset($_POST["query2"])) {
    $query2 = $_POST["query2"];
	$timestamp2 = date('Y-m-d H:i:s',strtotime($query2));
	$_SESSION["timestamp2"] = $timestamp2;
}
else if(isset($_SESSION["timestamp2"])){
	$timestamp2 = $_SESSION["timestamp2"];
}

$page = 1;
$per_page = 10;
if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}
$date=1;
if(isset($_GET["date"])){
    try {
        $date = (int)$_GET["date"];
    }
    catch(Exception $e){

    }
}



$db = getDB();
$user = get_user_id();

$stmt = $db->prepare("SELECT account_number, balance, account_type FROM Accounts WHERE id = :id and user_id = :user");
$r = $stmt->execute([
":id"=>$id,
":user" => $user
]);

$resultAcc = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($resultAcc && count($resultAcc) > 0):
    foreach($resultAcc as $r):
		$account_number = (int)$r["account_number"];
		$balance = (float)$r["balance"];
		$account_type = (int)$r["account_type"];
	endforeach;
endif;




$stmt = $db->prepare("SELECT count(*) as total FROM Transactions WHERE act_src_id = :id");
$stmt->execute([
":id"=>$id
]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($result)
{
	$total = (int)$result["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;


if (isset($_POST["search"]) && !empty($timestamp)) {

	$stmt = $db->prepare("SELECT action_type, amount, memo, created FROM Transactions WHERE act_src_id = :id AND action_type = :action AND created BETWEEN :query1 AND :query2 LIMIT :offset, :count");
	$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
	$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
	$stmt->bindValue(":id", $id);
	$stmt->bindValue(":query1", $timestamp);
	$stmt->bindValue(":query2", $timestamp2);
	$stmt->bindValue(":action", $action_filter);
	$r = $stmt->execute();
	$e = $stmt->errorInfo();
	if($r){
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
	}
	if($e[0] != "00000"){
		flash(var_export($e, true), "alert");
	}
	
	
}
?>

<form method="POST">
    <input type = "datetime-local" name="query" value = "<?php echo $timestamp;?>"/>
	<input type = "datetime-local" name="query2" value = "<?php echo $timestamp2;?>"/>
    <input type="submit" value="Search" name="search"/>
	
	<label>Transaction Type</label>
	<select name="action_filter">
		<option value="0">Deposit</option>
		<option value="1">Withdraw</option>
		<option value="2">Transfer</option>
		<option value="3">Ext-transfer</option>
	</select>
</form>

<?php 
$action_filter=$_POST["action_filter"];
?>

<div>
    <h3><b>Transaction History</b></h3>
	<h4><br>Account Number: <?php echo($account_number); ?></br></h4>
	<h4>Balance: <?php echo($balance); ?></h4>
	<h4>Account Type: <?php getAccType($account_type); ?></h4>
    <div>
    <div>
<?php if($results && count($results) > 0):?>
    <?php foreach($results as $r):?>
        <div class = "card-body">
			<div><br>Transaction Type: <?php getTransType($r["action_type"]); ?></br></div>
			
			<div>Amount: <?php safer_echo($r["amount"]); ?></div>
			
			<div>Memo: <?php safer_echo($r["memo"]); ?></div>
			
			<div>Date Created: <?php safer_echo($r["created"]); ?></div>
			
		</div>
		
		
    <?php endforeach;?>

<?php endif;?>
    </div>
    </div>
        <nav aria-label="Transaction History">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                    <a class="page-link" href="?id=<?php echo($id)?>&page=<?php echo $page-1;?>&date=<?php echo $date-1;?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>">
					<a class="page-link" href="?id=<?php echo($id)?>&page=<?php echo ($i+1);?>&date=<?php echo ($i+1);?>"><?php echo ($i+1);?></a>
				</li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                    <a class="page-link" href="?id=<?php echo($id)?>&page=<?php echo $page+1;?>&date=<?php echo $date+1;?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
<?php require(__DIR__ . "/partials/flash.php");