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
$TBS->LoadTemplate('university_list.html'); 

// error_reporting(E_ALL); ini_set('display_errors', 1);

$success = "";
$msg_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach($_POST as $k=>$v) {		
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v,FILTER_SANITIZE_STRING);
		}
	}

	if (isset($_POST['university_name']) && !empty($_POST['university_name'])) {
		if (preg_match("/^[a-zA-Z ]*$/", $_POST['university_name'])) {
			$university_name = trim($_POST['university_name']);
		} else {
			$msg_err = "Only letters and spaces are allowed";
		}
	} else {
		$msg_err = "University name is required";
	}

	if (isset($_POST['state_id'])) {
		$state_id = $_POST['state_id'];
	}

	if (!$msg_err) {

		try {
			$sql = "SELECT * FROM universities WHERE university_name=:university_name";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':university_name', $university_name);
			$stmt->execute();
			$result = $stmt->fetchAll();
			if ($stmt->rowCount() == 1) {
				$msg_err = "Data already entered";
			} else {
				$sql = "INSERT INTO `universities` (`university_id`, `university_name`, `state_id`, `status_value_id`, `log_id`) VALUES (NULL, :university_name, :state_id, :status_value_id, :log_id)";

				$ac_on = "Entered new university: " . $university_name;
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'universities';

				// $log_id = 1;
				$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);		

				$status_value_id = ecell_get_status('on');
				// $status_value_id = 1;

				$stmt = $conn->prepare($sql);

				$stmt->bindParam(':university_name', $university_name);
				$stmt->bindParam(':status_value_id', $status_value_id);
				$stmt->bindParam(':state_id', $state_id);
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
