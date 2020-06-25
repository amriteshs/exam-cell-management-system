<?php

include_once('../../includes/include.php');
include_once('../../includes/session.php');
if(!ecell_sec_session_start()) {
	header("Location: includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('board_list.html'); 

$success = "";
$msg_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach($_POST as $k=>$v) {		
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v,FILTER_SANITIZE_STRING);
		}
	}

	if (isset($_POST['board_name']) && !empty($_POST['board_name'])) {
		if (preg_match("/^[a-zA-Z-,. ]{2,}$/", $_POST['board_name'])) {
			$board_name = trim($_POST['board_name']);
		} else {
			$msg_err = "Only letters, spaces, ',' and '-' are allowed.";
		}
	} else {
		$msg_err = "Board name is required";
	}

	if (isset($_POST['state']) && !empty($_POST['state'])) {
		$state = $_POST['state'];
	} else {
		$msg_err = "State name is required";
	}

	if (!$msg_err) {

		try {
			$sql = "SELECT * FROM board WHERE board_name=:board_name AND state_id=:state";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':board_name', $board_name);
			$stmt->bindParam(':state', $state);
			$stmt->execute();
			$result = $stmt->fetchAll();
			if ($stmt->rowCount() == 1) {
				$msg_err = "Data already entered";
			} else {
				$sql = "INSERT INTO  `board`(`board_id`, `board_name`, `state_id`, `status_value_id`, `log_id`) VALUES (NULL, :board_name, :state, :status_id, :log_id)";

				$ac_on = "Entered new board: " . $board_name;
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'board';
				$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);		

				$status_id = ecell_get_status('on');

				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':board_name', $board_name);
				$stmt->bindParam(':state', $state);
				$stmt->bindParam(':status_id', $status_id);
				$stmt->bindParam(':log_id', $log_id);
				$stmt->execute();

				$success = "Success";
			}

		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}

	}

}

$TBS->MergeBlock('state', $conn, 'SELECT * FROM state');
$TBS->Show();

?>
