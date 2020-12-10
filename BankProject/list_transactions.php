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
	$_SESSION["query"] = $query;
}
else if(isset($_SESSION["query"])){
	$query = $_SESSION["query"];
}

if (isset($_POST["query2"])) {
    $query2 = $_POST["query2"];
	$_SESSION["query2"] = $query2;
}
else if(isset($_SESSION["query2"])){
	$query2 = $_SESSION["query2"];
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


if (isset($_POST["search"]) && !empty($query)) {

	$stmt = $db->prepare("SELECT action_type, amount, memo, created FROM Transactions WHERE act_src_id = :id AND created BETWEEN :query1 AND :query2 LIMIT :offset, :count");
	$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
	$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
	$stmt->bindValue(":id", $id);
	$stmt->bindValue(":query1", $query);
	$stmt->bindValue(":query2", $query2);
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
    <input type = "datetime-local" name="query" value =  <?php echo $query;?>/>
	<input type = "datetime-local" name="query2" value = <?php echo $query2;?>/>
    <input type="submit"/>
</form>


<div>
    <h3><b>Transaction History</b></h3>
	<h4><br>Account Number: <?php echo($account_number); ?></br></h4>
	<h4>Balance: <?php echo($balance); ?></h4>
	<h4>Account Type: <?php echo($account_type); ?></h4>
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
                    <a class="page-link" href="?id=<?php echo($id)?>&page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>">
					<a class="page-link" href="?id=<?php echo($id)?>&page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a>
				</li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                    <a class="page-link" href="?id=<?php echo($id)?>&page=<?php echo $page+1;?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
<?php require(__DIR__ . "/partials/flash.php");