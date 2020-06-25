<?php

include_once('../../includes/include.php');
include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
	header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}

ecell_redirect_if_below('AR', '../home/home.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('staff_modify_rank.html'); 

$msg = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (isset($_POST['staff_id']) &&  (trim($_POST['staff_id']) != '')) {
		$staff_id = $_POST['staff_id'];
	} else {
		$msg = 'Please select username';
	}

	if (isset($_POST['designation'])) {
		$rank = $_POST['designation'];
	} else {
		$msg = 'Please select a designation';
	}

	if($msg == "") {

		$sql = "SELECT username, staff_name FROM staff WHERE staff_id=:staff_id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		$username = $result['username'];
		$staff_name = $result['staff_name'];

		$msghash = md5($rank, $username);
		if(isset($_SESSION['msghash']) && ($_SESSION['msghash'] == $msghash)){
			$msg = "New designation is same as old";
		}
		else {

			$sql = "UPDATE staff SET rank = :rank WHERE staff_id = :staff_id;";

			$ac_on = "Modifying rank for $username to $rank";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = "staff";
			$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':rank', $rank);
			$stmt->bindParam(':staff_id', $staff_id);
			$stmt->execute();

			$_SESSION['msghash'] = $msghash;
			$success = "Rank is successfully modified for $staff_name";
		}
	}
}

//echo "SELECT distinct(staff_designation.designation), rank FROM staff_designation,status_value,status WHERE status.status_name = 'on' AND rank <= " . $_SESSION['rank'] . "\n";

$status_on_id = ecell_get_status('on');
$TBS->MergeBlock('staff', $conn, "SELECT staff_name, username, staff_id FROM staff WHERE rank < " . $_SESSION['rank']);
$TBS->MergeBlock('designation', $conn, "SELECT distinct(staff_designation.designation), rank FROM staff_designation,status_value,status WHERE status.status_name = 'on' AND rank < " . $_SESSION['rank']);
$TBS->Show();

?>
