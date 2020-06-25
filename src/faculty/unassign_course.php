<?php

include_once('../../includes/include.php');

// error_reporting(E_ALL); ini_set('display_errors', 1);
// error_reporting(E_ERROR | E_PARSE);

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('unassign_course.html');

$success = "";
$show_up = "y";
$show_down = "";
$msg_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach ($_POST as $k => $v) {      
		if (isset($_POST[$k])) {
			$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
		}   
	}

	if (isset($_POST['faculty_id']) && filter_var($_POST['faculty_id'], FILTER_VALIDATE_INT)) {
		$faculty_id = $_POST['faculty_id'];
	}

	if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
		$sem_code = $_POST['sem_code'];
	}

	if (isset($_POST['year'])) {
		$year = $_POST['year'];
	}

	if (isset($_POST['year'])) {
		$year = $_POST['year'];
	}

	if (isset($_POST['course_id']) && filter_var($_POST['course_id'], FILTER_VALIDATE_INT)) {
		$course_id = $_POST['course_id'];
	}

}

if (isset($_POST['submit_up'])) {

	$_SESSION['temp_year'] = $year;
	$_SESSION['temp_sem_code'] = $sem_code;
	$_SESSION['temp_faculty_id'] = $faculty_id;

	if (!preg_match('/^[12]{1}[0-9]{3}$/', $year)) {
		$msg_err .= "Please enter proper year.\n";
	}

//	if (!(date('Y') <= $_POST['year'])) {
//		$msg_err .= "Previous year courses cannot be unassigned.\n";
//	}
	$on_id = ecell_check_status('on');
	
	$TBS->MergeBlock('course', $conn, "SELECT * FROM faculty_course, courses, status_value WHERE courses.sem_code = '$sem_code' AND courses.year = '$year' AND faculty_course.faculty_id='$faculty_id' AND faculty_course.course_id=courses.course_id AND faculty_course.status_value_id = status_value.status_value_id AND status_value.status_id = $on_id");

	if (!$msg_err) {
		$show_up = "";
		$show_down = "y";
	}

}

if (!$msg_err) {
	if (isset($_POST['submit_down'])) {
		try {
			$year = $_SESSION['temp_year'];
			$sem_code = $_SESSION['temp_sem_code'];
			$faculty_id = $_SESSION['temp_faculty_id'];

			unset($_SESSION['temp_year']);
			unset($_SESSION['temp_sem_code']);
			unset($_SESSION['temp_faculty_id']);

			$sql = "SELECT status_value_id, log_id FROM faculty_course WHERE faculty_id = :faculty_id AND course_id = :course_id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':faculty_id', $faculty_id);
			$stmt->bindParam(':course_id', $course_id);
			$stmt->execute();
			$row = $stmt->fetchAll();
			$old_status = $row[0]['status_value_id'];
			$old_log_id = $row[0]['log_id'];

			$new_status = ecell_get_status('off');

			if (empty($row)) {
				$msg_err = "Data does not exist. Please enter valid data.";
			} else {
				$sql = "UPDATE faculty_course SET status_value_id = :new_status WHERE faculty_id = :faculty_id AND course_id = :course_id";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(":new_status", $new_status);
				$stmt->bindParam(":faculty_id", $faculty_id);
				$stmt->bindParam(":course_id", $course_id);
				$stmt->execute();

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

				$sql = "SELECT faculty_name FROM faculty WHERE faculty_id = :faculty_id";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':faculty_id', $faculty_id);
				$stmt->execute();
				$row = $stmt->fetchAll();
				$faculty_name = $row[0]['faculty_name'];

				$sql = "SELECT course_code FROM courses WHERE course_id = :course_id";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':course_id', $course_id);
				$stmt->execute();
				$row = $stmt->fetchAll();
				$course_code = $row[0]['course_code'];

				$ac_on = "Status updated from ".$old_status_name." to ".$new_status_name." for faculty ".$faculty_name." and course ".$course_code." in year ".$year." and semester code ".$sem_code.".";
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'faculty_course';
				$attribute_name = "status_value_id";

				$new_log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);
				
				$sql = "INSERT INTO update_history VALUES (:old_log_id, :log_id, :old_status, :table_name, :attribute_name)";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(":log_id", $new_log_id);
				$stmt->bindParam(":old_log_id", $old_log_id);
				$stmt->bindParam(":old_status", $old_status);
				$stmt->bindParam(":table_name", $tn);
				$stmt->bindParam(":attribute_name", $attribute_name);

				$stmt->execute();

				$success = "Course unassigned successfully.";
			}
		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}

		$show_form = "";
	}
}

$TBS->MergeBlock('status', $conn, 'SELECT * FROM status');
$TBS->MergeBlock('course', $conn, 'SELECT * FROM courses ORDER BY course_code');
$TBS->MergeBlock('faculty', $conn, 'SELECT * FROM faculty ORDER BY abbreviation');
$TBS->MergeBlock('sem_code_description', $conn, "SELECT * FROM sem_code_description, status WHERE status_name = 'on'");

$TBS->Show();

?>
