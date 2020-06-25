
<?php


include_once('../../includes/include.php');
include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
	header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}

$rank = $_SESSION['rank'];

if($rank == $all_ranks['Director']) {
	$msg = "\nWelcome Director";
} else if ($rank == $all_ranks['Dean']) {
	$msg = "\nWelcome Dean";
}  else if ($rank == $all_ranks['Faculty Incharge']) {
	$msg = "\nWelome Faculty Incharge";
}  else if ($rank == $all_ranks['DR']) {
	$msg = "\nWelcome DR";
} else if ($rank == $all_ranks['AR']) {
	$msg = "\nWelcome AR";
} else if ($rank == $all_ranks['Temporary AR']) {
	$msg = "\nWelcome Temporary AR";
} else {
	$msg = "\nWelcome Staff";
}
//
//$msg .= " " . $_SERVER['HTTP_X_FORWARDED_FOR'];

$sql = "SELECT * FROM log WHERE (table_name = 'login' OR table_name = 'logout') AND staff_id = :staff_id ORDER BY time DESC LIMIT 1, 10";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':staff_id', $_SESSION['staff_id']);
$stmt->execute();
$your_login = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM log, staff WHERE (table_name = 'login' OR table_name = 'logout') AND log.staff_id=staff.staff_id AND log.staff_id <> :staff_id ORDER BY time DESC LIMIT 1, 20";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':staff_id', $_SESSION['staff_id']);
$stmt->execute();
$others_login = $stmt->fetchAll(PDO::FETCH_ASSOC);

//print_r($your_login);

function getStat($action, $onlineConnection)
{
	$sqlQuery = $onlineConnection->prepare("SELECT status from admin  where action = :action ORDER by timestamp DESC");
	$sqlQuery->bindParam(':action', $action, PDO::PARAM_STR);
	$sqlQuery->execute();
	if ($sqlQuery->rowCount() > 0) {
		$result = $sqlQuery->fetchAll()[0];
		return $result[0];
	} else {
		return 0;
	}
}
function get_reg_status($onlineConnection)
{
	$btech_status = getStat("btech_reg", $onlineConnection);
	$mtech_status = getStat("mtech_reg", $onlineConnection);
	$mba_status = getStat("mba_reg", $onlineConnection);
	$phd_status = getStat("phd_reg", $onlineConnection);

	$current_status = "";
	if ($btech_status == 1) {
		$current_status = " B.Tech";
	}
	if ($mtech_status == 1) {
		if (empty($current_status)) {
			$current_status = " M.Tech";
		} else {
			$current_status = $current_status . ", M.Tech";
		}
	}
	if ($mba_status == 1) {
		if (empty($current_status)) {
			$current_status = " MBA";
		} else {
			$current_status = $current_status . ", MBA";
		}
	}
	if ($phd_status == 1) {
		if (empty($current_status)) {
			$current_status = " PhD";
		} else {
			$current_status = $current_status . ", PhD";
		}
	}

	return $current_status;
}

//try{
//	$onlineConnection = ecell_get_conn_fedratecd();
//	if($onlineConnection) {
//		$current_status = ecell_get_reg_status($onlineConnection);
//
//		$status = "";
//		if (empty($current_status)) {
//			$status = "Registrations are closed.";
//		} else {
//			$status = "Registrations for " . $current_status . " are active.";
//		}
//	}
//}catch(Exception $e){
//	$status = "Online server connection needs attention!!";
//}

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('home.html');
$TBS->MergeBlock('yourlogin', $your_login);
$TBS->MergeBlock('otherslogin', $others_login);
$TBS->Show();

?>
