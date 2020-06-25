<?php


date_default_timezone_set('Asia/Calcutta');

ini_set("display_errors", "1");
error_reporting(E_ALL);

include_once('connect.php');
include_once('session.php');
include_once('functions.php');

if(!ecell_sec_session_start()) {
	header("Location: ../index.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../index.php");
}


$sql = "LOGOUT";
$ac_on = "Logout by staff menber " . $_SESSION['staff_name'] . " from IP address " . $_SERVER['HTTP_X_FORWARDED_FOR'];
$s_i = $_SESSION['staff_id'];
$r = $_SESSION['rank'];
$t_n="logout";
$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$t_n);

$_SESSION = array();

$params = session_get_cookie_params();

setcookie(session_name(),
		'', time() - 42000,
		$params["path"],
		$params["domain"],
		$params["secure"],
		$params["httponly"]);

session_destroy();


// (if (isset($_GET['pc'])) {
// 	header("Location: ../index.php?pc=1");
// }
header('Location: ../index.php');

?>

