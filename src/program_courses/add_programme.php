<?php

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('add_programme.html'); 
if(!ecell_sec_session_start()) {
	header("Location: includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}
$success = "";
$show_form = "y";
$msg_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach($_POST as $k=>$v) {
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v,FILTER_SANITIZE_STRING);
		}			
	}
	
	if (isset($_POST['program_code']) && preg_match('/^[A-Za-z-,.() ]{2,}$/', $_POST['program_code'])) {
		$program_code = $_POST['program_code']; 
	} else {
		$msg_err .= "Program code must contain only alphabets, spaces, '.', '-', ',', '(' and ')'.\n";
	}

	if (isset($_POST['program_name']) && preg_match('/^[A-Za-z-,.()& ]{2,}$/', $_POST['program_name'])) {
		$program_name = $_POST['program_name'];
	} else {
		$msg_err .= "Program name must contain only alphabets, spaces, '.', '-', ',', '(', ')' and '&'.\n";
	}

	if (isset($_POST['program_prefix'])) {
		$_POST['program_prefix'] = strtoupper($_POST['program_prefix']);
	}

	if (isset($_POST['program_prefix']) && preg_match('/^[A-Z]{3,}$/', $_POST['program_prefix'])) {
		$program_prefix = $_POST['program_prefix'];
	} else {
		$msg_err .= "Program prefix should contain at least 3 CAPITAL letters without spaces.";
	}

	// && preg_match('/^[a-zA-Z]{2,}$/', $_POST['program_type'])
	if (isset($_POST['program_type'])) {
		$program_type = $_POST['program_type'];
	} 
	// else {
	// 	$msg_err .= "Program type should contain only alphabets without spaces.";
	// }

	if (isset($_POST['program_duration'])) {
		$program_duration = $_POST['program_duration']; 
	}

	$program_id = ecell_get_val('program', 'program_prefix', $program_prefix, 'program_id');
	$program_code_exists = ecell_get_val('program', 'program_code', $program_code, 'program_id');

	if ($program_id) {
		$msg_err = "The program has already been added.";
	} else if ($program_code_exists) {
		$msg_err = "Program code $program_code already exists.";
	}

	if (!$msg_err) {

		try {

			$status_value_id = ecell_get_status('on');

			$sql = "INSERT INTO `program`(`program_id`, `program_type`, `program_code`, `program_name`, `program_prefix`, `program_duration`, `status_value_id`, `log_id`) VALUES (NULL,:program_type, :program_code, :program_name, :program_prefix, :program_duration, :status_value_id, :log_id)";

			$ac_on = "Entered a new program ".$program_name." with code ".$program_code;
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'program';

			$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

			$sql = "INSERT INTO `program`(`program_id`, `program_type`, `program_code`, `program_name`, `program_prefix`, `program_duration`, `status_value_id`, `log_id`) VALUES (NULL,:program_type, :program_code, :program_name, :program_prefix, :program_duration, :status_value_id, :log_id)";

			$stmt = $conn->prepare($sql);

			$stmt->bindParam(':program_type', $program_type);
			$stmt->bindParam(':program_code', $program_code);
			$stmt->bindParam(':program_name', $program_name);
			$stmt->bindParam(':program_prefix', $program_prefix);
			$stmt->bindParam(':program_duration', $program_duration);
			$stmt->bindParam(':status_value_id', $status_value_id);
			$stmt->bindParam(':log_id', $log_id);

			$stmt->execute();

		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}

		$show_form = "";
		$success = "Success";

	}

}

$program_type_arr = array('1'=>'B.Tech', '2'=>'M.Tech', '3'=>'Dual Degree Ph.D', '4'=>'Dual Degree M.Tech', '5'=>'MBA', '6'=>'Ph.D');
$TBS->MergeBlock('program_type', $program_type_arr);

$TBS->Show();

?>
