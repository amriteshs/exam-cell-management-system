<?php

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('assign_course.html'); 

$success = "";
$show_up = "y";
$show_down = "";
$msg_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_up'])) {

	foreach($_POST as $k=>$v) {
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v,FILTER_SANITIZE_STRING);
		}			
	}

	if (isset($_POST['program_id'])) {
		$program_id = $_POST['program_id'];
	}



	//&& date('Y') <= $_POST['year']

	if (isset($_POST['year']) ) {
		$year = $_POST['year'];
	} else {
		$msg_err .= "Cannot assign courses of previous years.\n";
	}

	if (isset($_POST['year']) && preg_match('/^[12]{1}[0-9]{3}$/', $_POST['year'])) {
		$year = $_POST['year'];
	} else {
		$msg_err .= "Please enter proper year.\n";
	}

	if (isset($_POST['sem_code'])) {
		$sem_code = $_POST['sem_code'];
	}   

	if (isset($_POST['year_of_joining']) && preg_match('/^[12]{1}[0-9]{3}$/', $_POST['year_of_joining'])) {
		$year_of_joining = $_POST['year_of_joining'];
	} else {
		$msg_err .= "Please enter proper year of joining.\n";
	}

	if (isset($_POST['sem_code_of_joining'])) {
		$sem_code_of_joining = $_POST['sem_code_of_joining'];
	}



//	$_SESSION['temp_year'] = $year;
//	$_SESSION['temp_sem_code'] = $sem_code;

	$sql = "SELECT * FROM course_structure, sem_structure, courses WHERE course_structure.sem_id=sem_structure.sem_id AND courses.course_id=sem_structure.course_id AND course_structure.sem_id_sem_code=$sem_code AND course_structure.sem_id_year=$year AND course_structure.sem_code_of_joining=$sem_code_of_joining AND course_structure.year_of_joining=$year_of_joining AND course_structure.program_id=$program_id ORDER BY courses.course_name ASC";


	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->fetchAll();
//	print_r($result);

	$TBS->MergeBlock('course', $conn, "SELECT * FROM course_structure, sem_structure, courses WHERE course_structure.sem_id=sem_structure.sem_id AND courses.course_id=sem_structure.course_id AND course_structure.sem_id_sem_code=$sem_code AND course_structure.sem_id_year=$year AND course_structure.sem_code_of_joining=$sem_code_of_joining AND course_structure.year_of_joining=$year_of_joining AND course_structure.program_id=$program_id ORDER BY courses.course_name ASC");

	if (!$msg_err) {
		$show_up = "";
		$show_down = "y";
	}

}

if (isset($_POST['submit_down'])) {

	if (isset($_POST['faculty_name'])) {
		$faculty_id = $_POST['faculty_name'];
	}
	if (isset($_POST['course_name'])) {
		$course_id = $_POST['course_name'];
	}


	$sql = "SELECT * FROM faculty_course WHERE faculty_id=:faculty_id AND course_id=:course_id";

	$stmt = $conn->prepare($sql);	

	$stmt->bindParam(':faculty_id', $faculty_id);
	$stmt->bindParam(':course_id', $course_id);

	$stmt->execute();



	if ($stmt->rowCount() == 1) {
		$msg_err .= "The course has been already assigned to the faculty.\n";
	}

	if (!$msg_err) {
		try {
//			$year = $_SESSION['temp_year'];
//			$sem_code = $_SESSION['temp_sem_code'];
//			$faculty_id = $_SESSION['temp_faculty_id'];
//
//			unset($_SESSION['temp_year']);
//			unset($_SESSION['temp_sem_code']);
//			unset($_SESSION['temp_faculty_id']);

			$status_id = ecell_get_status('on');

			$sql = "INSERT INTO `faculty_course`(`faculty_id`, `course_id`,  `status_value_id`, `log_id`) VALUES 
				(:faculty_id,:course_id,:status_id,:log_id)";

			$ac_on = "Assigned course id $course_id to faculty id $faculty_id.";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'faculty_course';

			$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

			$sql = "INSERT INTO `faculty_course`(`faculty_id`, `course_id`, `status_value_id`, `log_id`) VALUES 
				(:faculty_id,:course_id,:status_id,:log_id)";

				$stmt = $conn->prepare($sql);

			$stmt->bindParam(':faculty_id', $faculty_id);
			$stmt->bindParam(':course_id', $course_id);
			$stmt->bindParam(':status_id', $status_id);
			$stmt->bindParam(':log_id', $log_id);

			$stmt->execute();

			$success = "Success";

		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}
	}

}

$TBS->MergeBlock('program', $conn, "SELECT program_id, program_name FROM program, status_value, status where program.status_value_id = status_value.status_value_id AND status_value.status_id = status.status_id AND status.status_name='on';");
$TBS->MergeBlock('faculty', $conn, "SELECT faculty_id, faculty_name FROM faculty, status_value, status where faculty.status_value_id = status_value.status_value_id AND status_value.status_id = status.status_id AND status.status_name='on';");
$TBS->MergeBlock('sem_code_description, sem_code_description1', $conn, "SELECT * FROM sem_code_description");
$TBS->MergeBlock('course', $conn, "SELECT * FROM courses, status_value, status where courses.status_value_id = status_value.status_value_id AND status_value.status_id = status.status_id AND status.status_name='on';");
$TBS->Show();

?>
