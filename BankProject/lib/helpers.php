<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

//end flash

function getAccType($n) {
    switch ($n) {
        case 0:
            echo "Checking";
            break;
        case 1:
            echo "Saving";
            break;
        case 2:
            echo "Loan";
            break;
		case 3:
            echo "World";
            break;
        default:
            echo "Unsupported type: " . safer_echo($n);
            break;
    }
}

function get_dropdown_items(){
	//require("config.php");
	//$conn_string = "mysql:host=$host;dbname=$database;charset=utf8mb4";
	$db = getDB();
	$query = "SELECT DISTINCT account_number from Accounts";
	$stmt = $db->prepare($query);
	$r = $stmt->execute();
	return $stmt->fetchAll();
}

function get_acc_number(){
	//require("config.php");
	//$conn_string = "mysql:host=$host;dbname=$database;charset=utf8mb4";
	$user = get_user_id;
	$db = getDB();
	$query = "SELECT DISTINCT account_number from Accounts WHERE user_id = :user";
	$stmt = $db->prepare($query);
	$r = $stmt->execute([
	":user" => $user
	]);
	return $stmt->fetchAll();
}

?>
