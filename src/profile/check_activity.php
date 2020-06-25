<?php

include_once('../../includes/include.php');
include_once('../../includes/session.php');
if(!ecell_sec_session_start()) {
	header("Location: includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}
$show_val = "";
ini_set('memory_limit', '-1');
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('check_activity.html'); 

$msg = "";

if (!isset($_POST)) {
	$_POST = &$HTTP_POST_VARS;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

	if (isset($_POST['date']) && !empty($_POST['date'])) {
		$date = $_POST['date'];
		$date = explode('/', $date);
		$date = $date[2] . '-' . $date[1] . '-' . $date[0];
		if (!preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
			$msg = "Please enter date in the correct format.";
		}
	} else {
		$msg = "Please enter date";
	}

	if($msg == "") {
		try {

			$sql = "SELECT * FROM log JOIN staff ON log.staff_id=staff.staff_id WHERE username=:username AND DATE(time) >= :date ORDER BY time DESC";
			$stmt = $conn->prepare($sql);
			$stmt->bindparam(':username', $_SESSION['username']);
			$stmt->bindparam(':date', $date);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$count = $stmt->rowCount();
			if ($count) {
				$show_val = "y";
				$TBS->MergeBlock('result', $result);
			} else {
				$msg = "No logs found.";
			}
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
}

$TBS->Show();

?>
