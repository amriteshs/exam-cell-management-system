<?php

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('add_course_gui.html'); 

$show_form = "y";
$success = "";
$msg_err = "";

$course_name = '';
$course_code = '';
$course_type = '';
$theory_credit = '';
$lab_credit = '';
$year = '';
$sem_code = '';
$mid_sem_exam_time = '';
$end_sem_exam_time = '';
$contact_person = '';

ini_set("display_errors", "1");
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach($_POST as $k=>$v) {
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
		}
	}

	if (isset($_POST['course_name']) && preg_match('/^[A-Za-z ]{1,}$/', $_POST['course_name'])) {
		$course_name = $_POST['course_name'];
	} else {
		$msg_err .= "Course name should not contain numbers or sepecial characters.\n";
	}

	if (isset($_POST['course_code']) && preg_match('/^[A-Z0-9]{1,}$/', $_POST['course_code'])) {
		$course_code = $_POST['course_code'];
	} else {
		$msg_err .= "Course code should be a alphanumeric string(characters in CAPITAL) with no spaces in between.\n";
	}

	if (isset($_POST['course_type'])) {
		$course_type = $_POST['course_type']; 
	}

	if (isset($_POST['theory_credit'])) {
		$theory_credit = $_POST['theory_credit'];
	}

	if (isset($_POST['lab_credit'])) {
		$lab_credit = $_POST['lab_credit'];
	}

	if (!$theory_credit && !$lab_credit) {
		$msg_err = "Both theory and lab credit can't be zero for a course.\n";
	}

	if (isset($_POST['year']) && preg_match('/^[12]{1}[0-9]{3}$/', $_POST['year'])) {
		$year = $_POST['year'];
	} else {
		$msg_err .= "Please enter proper year.\n";
	}

	if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT) && date('Y') <= $_POST['year']) {
		$year = $_POST['year'];
	} else {
		$msg_err .= "Courses for previous years can't be added.\n";
	}	    

	if (isset($_POST['sem_code'])) {
		$sem_code = $_POST['sem_code'];
	}

	if (isset($_POST['mid_sem_exam_time']) && preg_match('/^[12]{1}[0-9]{3}-[01]{1}[0-9]{1}-[0-3]{1}[0-9]{1}$/', $_POST['mid_sem_exam_time']) && strtotime(date("Y-m-d")) <= strtotime($_POST['mid_sem_exam_time'])) {
		$mid_sem_exam_time = $_POST['mid_sem_exam_time'];
	} else {
		$msg_err .= "Please enter proper mid semester exam date and don't enter past dates.\n";
	}

	if (isset($_POST['end_sem_exam_time']) && preg_match('/^[12]{1}[0-9]{3}-[01]{1}[0-9]{1}-[0-3]{1}[0-9]{1}$/', $_POST['end_sem_exam_time']) && strtotime(date("Y-m-d")) <= strtotime($_POST['end_sem_exam_time'])) {
		$end_sem_exam_time = $_POST['end_sem_exam_time'];
	} else {
		$msg_err .= "Please enter proper end semester exam date and don't enter past dates.\n";
	}

	if (strtotime($_POST['mid_sem_exam_time']) >= strtotime($_POST['end_sem_exam_time'])) {
		$msg_err .= "End semester examination should be held after mid semester examinations.\n";
	}


	$sql = "SELECT * FROM courses, status WHERE course_code=:course_code AND year=:year AND sem_code=:sem_code AND status_name='on'";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':course_code', $course_code);
	$stmt->bindParam(':year', $year);
	$stmt->bindParam(':sem_code', $sem_code);
	$stmt->execute();

	if ($stmt->rowCount() == 1) {
		$msg_err = "The course has been entered already.";
	} 

	if (!$msg_err) {
		try {

			$status_id = ecell_get_status('on');

			$sql = "INSERT INTO `courses`(`course_id`, `course_code`, `course_name`, `course_type`, `year`, `sem_code`, `theory_credit`, `lab_credit`, `mid_sem_exam_date`, `end_sem_exam_date`, `status_value_id`, `log_id`) VALUES (NULL,:course_code,:course_name,:course_type,:year,:sem_code,:theory_credit,:lab_credit,:mid_sem_exam_time,:end_sem_exam_time,:status_id,:log_id)";

			$ac_on = "Registered a new course $course_code in $sem_code of $year.";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'courses';

			$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

			$sql = "INSERT INTO `courses`(`course_id`, `course_code`, `course_name`, `course_type`, `year`, `sem_code`, `theory_credit`, `lab_credit`, `mid_sem_exam_date`, `end_sem_exam_date`, `status_value_id`, `log_id`) VALUES (NULL, :course_code, :course_name, :course_type, :year, :sem_code, :theory_credit, :lab_credit, :mid_sem_exam_time, :end_sem_exam_time, :status_id, :log_id)";

			$stmt = $conn->prepare($sql);

			$stmt->bindParam(':course_code', $course_code);
			$stmt->bindParam(':course_name', $course_name);
			$stmt->bindParam(':course_type', $course_type);
			$stmt->bindParam(':year', $year);
			$stmt->bindParam(':sem_code', $sem_code);
			$stmt->bindParam(':theory_credit', $theory_credit);
			$stmt->bindParam(':lab_credit', $lab_credit);
			$stmt->bindParam(':mid_sem_exam_time', $mid_sem_exam_time);
			$stmt->bindParam(':end_sem_exam_time', $end_sem_exam_time);
			$stmt->bindParam(':status_id', $status_id);
			$stmt->bindParam(':log_id', $log_id);

			$stmt->execute();

			$show_form = "";
			$success = "Course successfully added.";

		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}
	} else {
		unset($_POST);
	}
}

$type_array = array('C'=>'C (Compulsory)', 'E'=>'E (Elective)', 'P'=>'P (Project)', 'G'=>'G (GIAN)');

$TBS->MergeBlock('type', $conn, 'SELECT course_type_description, course_type_id from course_type');
$TBS->MergeBlock('sem_code_description', $conn, "SELECT * FROM sem_code_description, status WHERE status_name='on'");

$TBS->Show();


?>
