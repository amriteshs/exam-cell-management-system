<?php
include_once('../../includes/include.php');
include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
	header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}

if ($_SESSION['rank'] < $all_ranks['AR']) {
	$denied = true;
} else {
	$permitted = true;
}

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('staff_modify_staff_status.html');

$success = "";
$show_form = "y";
$msg_err = "";

if (!isset($_POST)) {
	$_POST = &$HTTP_POST_VARS;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	foreach ($_POST as $k => $v) {      
		if (isset($_POST[$k])) {
			$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
		}   
	}

	if (isset($_POST['staff_id'])) {
		$staff_id = $_POST['staff_id'];
	}

	if (isset($_POST['new_status']) && filter_var($_POST['new_status'], FILTER_VALIDATE_INT)) {
		$new_status = $_POST['new_status'];
	}

	try {   
		$old_status = "";
		$old_log_id = "";
		$sql = "SELECT * FROM staff WHERE staff_id=:staff_id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':staff_id', $staff_id);
		$stmt->execute();
		$row = $stmt->fetchAll();
		$staff_id = $row[0]['staff_id'];
		$old_status = $row[0]['status_id'];
		$old_log_id = $row[0]['log_id'];
		$staff_name = $row[0]['staff_name'];

		if ($new_status == $old_status) {
			$msg_err = "New status is equal to old status. Please enter a new value.";
			$show_form = "";
		} else {
			$sql = "SELECT status_name FROM status WHERE status_id = :old_status";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':old_status', $old_status);
			$stmt->execute();
			$row = $stmt->fetchAll();
			$old_status_name = $row[0]['status_name'];

			$sql = "SELECT status_name FROM status WHERE status_id = :new_status";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':new_status', $new_status);
			$stmt->execute();
			$row = $stmt->fetchAll();
			$new_status_name = $row[0]['status_name'];

			$ac_on = "Changed the status from " . $old_status_name . " to " . $new_status_name . " for staff " . $staff_name . ".";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'staff';
			$sql = "INSERT INTO update_status_history VALUES (:log_id, :old_log_id, :new_status)";
			$new_log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

			$sql = "UPDATE staff SET status_id = :new_status, log_id = :new_log_id WHERE staff_id = :staff_id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(":new_status", $new_status);
			$stmt->bindParam(":new_log_id", $new_log_id);
			$stmt->bindParam(':staff_id', $staff_id);
			$stmt->execute();

			$sql = "INSERT INTO update_status_history VALUES (:log_id, :old_log_id, :new_status)";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(":log_id", $new_log_id);
			$stmt->bindParam(":old_log_id", $old_log_id);
			$stmt->bindParam(":new_status", $new_status);
			$stmt->execute();

			$show_form = "";
			$success = "Status updated for $staff_name";
		}
	} catch (PDOException $e) {
		echo $sql . "<br>" . $e->getMessage();
	}

	$show_form = "";
}

$TBS->MergeBlock('status', $conn, 'SELECT * FROM status');
$TBS->MergeBlock('staff', $conn, 'SELECT * FROM staff WHERE rank < ' . $_SESSION['rank']);
$TBS->Show();
?>
