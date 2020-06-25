<?php
include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('course_registration_gui.html');

$form1 = true;
$form2 = false;
$enrollment_no = '';
$year = '';
$disabled = '';

$success="";
$msg_err="";
$on_id = ecell_check_status('on');
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (isset($_POST['submit1'])) {

		foreach ($_POST as $k => $v) {
			if (isset($_POST[$k])) {
				$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
			}
		}

		if (isset($_POST['enrollment_no'])) {
			$enrollment_no = $_POST['enrollment_no'];
			$student_id = ecell_get_val_on('student', 'enrollment_no', $enrollment_no, 'student_id');
			if ($student_id == 0) {
				$msg_err .= "Student with enrollment no $enrollment_no does not exist.\n";
			}
		}


		if (isset($_POST['year'])) {
			$year = $_POST['year'];
		} else {
			$msg_err .= "Enter the year.\n";
		}

		if (isset($_POST['sem_code'])) {
			$sem_code_id = $_POST['sem_code'];
		}

		$_SESSION['course_registration_gui']['enroll'] = $enrollment_no;
		$_SESSION['course_registration_gui']['student_id'] = $student_id;
		$_SESSION['course_registration_gui']['sem_code'] = $sem_code_id;
		$_SESSION['course_registration_gui']['year'] = $year;

		$from1 = '';
		$form2 = true;
		$disabled = 'disabled';

		$TBS->MergeBlock('course', $conn, "SELECT * FROM courses WHERE year=$year AND sem_code=$sem_code_id");
	} else if ($_POST['submit2']) {

		if (isset($_POST['course_id'])) {
			$course_id = $_POST['course_id'];
		}

		$enrollment_no = $_SESSION['course_registration_gui']['enroll'];
		$student_id = $_SESSION['course_registration_gui']['student_id'];
		$sem_code_id = $_SESSION['course_registration_gui']['sem_code'];
		$year = $_SESSION['course_registration_gui']['year'];

		$sql = "SELECT * FROM course_registration WHERE course_id =:course AND student_id=:st_id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':course', $course_id);
		$stmt->bindParam(':st_id', $student_id);
		$stmt->execute();

		if ($stmt->rowCount() == 1) {
			$msg_err .= "The student with enrollment no $enrollment_no is already registered in the requested course.\n";
		}
		if (!$msg_err) {
			$sql = "INSERT INTO course_registration(course_id, student_id, grade_card_year, grade_card_sem_code, status_value_id, log_id) VALUES (:course, :st_id, :year, :sem_code_id, :status_id, :log_id)";
			$status_id = ecell_get_status('on');
			$ac_on = "Registered a student with $enrollment_no in $course_id in $sem_code_id of $year.";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'course_registration';
			$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':course', $course_id);
			$stmt->bindParam(':st_id', $student_id);
			$stmt->bindParam(':sem_code_id', $sem_code_id);
			$stmt->bindParam(':status_id', $status_id);
			$stmt->bindParam(':log_id', $log_id);
			$stmt->bindParam(':year', $year);

			$stmt->execute();
			$success = "Success";
		}
	}
}


$TBS->MergeBlock('sem_code_description', $conn, "SELECT * FROM sem_code_description, status_value WHERE sem_code_description.status_value_id = status_value.status_value_id AND status_value.status_id = $on_id");

$TBS->show();

?>