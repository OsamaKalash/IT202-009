<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
if(isset($_GET["id"])){
    $id = $_GET["id"];
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

$db = getDB();

$stmt = $db->prepare("SELECT account_number, balance, account_type FROM Accounts WHERE id = :id and user_id = :user");
$stmt->execute([
":id"=>$id,
":user" => get_user_id()
]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if($result)
{
	$account_number = (int)$result["account_number"];
	$balance = (float)$result["balance"];
	$account_type = (int)$result["account_type"];
}

else
{
	flash("There was a problem fetching the results");
}


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



$stmt = $db->prepare("SELECT action_type, amount, memo, created FROM Transactions WHERE act_src_id = :id LIMIT :offset, :count");
//need to use bindValue to tell PDO to create these as ints
//otherwise it fails when being converted to strings (the default behavior)
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":id", $id);
$stmt->execute();
$e = $stmt->errorInfo();
if($e[0] != "00000"){
    flash(var_export($e, true), "alert");
}
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div>
    <h3>Transaction History</h3>
	<h4>Account Number: <?php safer_echo($account_number); ?></h4>
	<h4>Balance: <?php safer_echo($balance); ?></h4>
	<h4>Account Type: <?php safer_echo($account_type); ?></h4>
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

<?php else:?>
<div>
    <div>
       This account has no transactions!
    </div>
</div>
<?php endif;?>
    </div>
    </div>
        <nav aria-label="Transaction History">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                    <a class="page-link" href="?page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                    <a class="page-link" href="?page=<?php echo $page+1;?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
<?php require(__DIR__ . "/partials/flash.php");