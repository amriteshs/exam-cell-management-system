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
$TBS->LoadTemplate('state_list.html'); 

$success = "";
$show_form = "y";
$msg_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach($_POST as $k=>$v) {
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
		}
	}

	if (isset($_POST['state_name']) && !empty($_POST['state_name'])) {
		if (preg_match("/^[a-zA-Z\s]*$/", $_POST['state_name'])) {
			$state_name = trim($_POST['state_name']);
		} else {
			$msg_err = "Only letters and spaces are allowed in state names.";
		}
	} else {
		$msg_err = "Please enter a state name.";
	}

	if (isset($_POST['zone_name']) && !empty($_POST['zone_name'])) {
		if (preg_match("/^[a-zA-Z\s]*$/", $_POST['zone_name'])) {
			$zone_name = trim($_POST['zone_name']);
		} else {
			$msg_err = "Only letters and spaces are allowed in zone names.";
		}
	} else {
		$msg_err = "Please enter a zone name.";
	}

	if ($msg_err == "") {
		try {
			$status_value_id = ecell_get_status('on');
			$sql = "SELECT * FROM state WHERE state_name=:state_name";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':state_name', $state_name);
			$stmt->execute();
			$result = $stmt->fetchAll();
			if ($stmt->rowCount() == 1) {
				$msg_err = "Data already entered";
			} else {

				$sql = "INSERT INTO `state` (`state_id`, `state_name`, `zone`, `status_value_id`, `log_id`) VALUES (NULL, '$state_name', '$zone_name', '$status_value_id', 'LOG_ID')";
				$ac_on = "Entered new state: ".$state_name;
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'state';
				$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

				$sql = "INSERT INTO `state` (`state_id`, `state_name`, `zone`, `status_value_id`, `log_id`) VALUES (NULL, :state_name, :zone_name, :status_value_id, :log_id)";


				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':state_name', $state_name);
				$stmt->bindParam(':zone_name', $zone_name);
				$stmt->bindParam(':status_value_id', $status_value_id);
				$stmt->bindParam(':log_id', $log_id);

				$stmt->execute();

				$success = "Success";
			}
		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}
	}
	$show_form = "";
}

$TBS->MergeBlock('zones', array('North', 'East', 'South', 'West'));
$TBS->Show();

?>
