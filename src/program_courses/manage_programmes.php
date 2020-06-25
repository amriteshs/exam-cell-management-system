<?php

include_once('../../includes/include.php');
if(!ecell_sec_session_start()) {
	header("Location: includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}
error_reporting(E_ALL); ini_set('display_errors', 1);

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('manage_programmes.html'); 

$success = "";
$show_up = "y";
$show_down = "";
$msg_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach($_POST as $k=>$v) {
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v,FILTER_SANITIZE_STRING);
		}			
	}

	if (isset($_POST['program_duration'])) {
		$program_duration = $_POST['program_duration']; 
	}

	if (isset($_POST['program_code'])) {
		$program_code = $_POST['program_code']; 
	}

	if (isset($_POST['program_name'])) {
		$program_name = $_POST['program_name'];
	}

	if (isset($_POST['program_prefix'])) {
		$program_prefix = $_POST['program_prefix'];
	}

	if (isset($_POST['program_id'])) {
		$program_id = $_POST['program_id'];
	}

	if (isset($_POST['program_type'])) {
		$program_type = $_POST['program_type'];
	}

	if (isset($_POST['submit_detail'])) {

		$_SESSION['program_id'] = $program_id;

		$show_up = "";
		$show_down = "y";

		$TBS->MergeBlock('update', $conn, "SELECT * FROM program WHERE program_id='$program_id'");

	} else if (isset($_POST['submit_update'])) {

		try {

			$program_id = $_SESSION['program_id'];

			unset($_SESSION['program_id']);

			$status_id = ecell_get_status('on');

			$sql = "UPDATE `program` SET `program_type`=$program_type,`program_code`=$program_code,`program_name`=$program_name,`program_prefix`=$program_prefix,`status_value_id`=$status_id,`program_duration`=$program_duration WHERE program_id = $program_id";

			$ac_on = "Updated details of $program_name.";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'program';

			$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

			$sql = "UPDATE `program` SET `program_type`=:program_type,`program_code`=:program_code,`program_name`=:program_name,`program_prefix`=:program_prefix,`status_value_id`=:status_value_id,`program_duration`=:program_duration WHERE program_id = :program_id";

			$stmt = $conn->prepare($sql);

			$stmt->bindParam(':program_type', $program_type);
			$stmt->bindParam(':program_code', $program_code);
			$stmt->bindParam(':program_name', $program_name);
			$stmt->bindParam(':program_prefix', $program_prefix);
			$stmt->bindParam(':status_value_id', $status_id);
			$stmt->bindParam(':program_duration', $program_duration);
			$stmt->bindParam(':program_id', $program_id);

			$stmt->execute();

		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}

		$success = "Success";

	}

}

$TBS->MergeBlock('program', $conn, 'SELECT * FROM program');
$TBS->MergeBlock('sem_code_description', $conn, "SELECT * FROM sem_code_description");

$TBS->Show();

?>
