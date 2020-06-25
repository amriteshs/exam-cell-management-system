<?php

include_once('../../includes/include.php');

error_reporting(-1);
ini_set('display_errors', 'On');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('student_result.html');

$show_first_form = true;
$show_second_form = false;

$success = "";
$show_form = "y";
$msg_err = "";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach($_POST as $k=>$v) {
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
		}
	}

	if (isset($_POST['submit_1'])) {		
		if (isset($_POST['student_roll_no']) && (!empty($_POST['student_roll_no']))) {
			$student_roll_no = $_POST['student_roll_no'];
		}
		if (isset($_POST['semester_code']) && filter_var($_POST['semester_code'], FILTER_VALIDATE_INT)) {
			$sem_code = $_POST['semester_code'];
		}	
		if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
			$year = $_POST['year'];
		}


		$q = "SELECT * FROM student WHERE enrollment_no=:roll_no";
		$stmt = $conn->prepare($q);
		$stmt->bindParam(':roll_no', $student_roll_no);
		$stmt->execute();
		$f = $stmt->fetchAll()[0];

		if (count($f)) {

			$student_id = $f['student_id'];
			$year_of_joining = $f['year'];
			$sem_code_of_joining = $f['sem_code'];

			$sql = "SELECT * FROM course_registration, courses WHERE student_id=:student_id AND courses.year=:year AND courses.sem_code=:sem_code AND courses.course_id = course_registration.course_id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':student_id', $student_id);
			$stmt->bindParam(':year', $year);
			$stmt->bindParam(':sem_code', $sem_code);
			$stmt->execute();
			$courseBlk = $stmt->fetchAll(PDO::FETCH_ASSOC);


			if (count($courseBlk)) {
				$TBS->MergeBlock('courseBlk', $courseBlk);
				$TBS->MergeBlock('grades, grades1', $conn, "SELECT * FROM grades");
				
//				$lab_display = array_map(function($c) {
//						$lab = $c['lab_credit'] == 0 ? false : true;
//						// return $lab;
//						return true;
//						}, $courseBlk);
//
//				$theory_display = array_map(function($c) {
//						$theory = $c['theory_credit'] == 0 ? false : true;
//						// return $theory;
//						return true;
//						}, $courseBlk);
//
//				$offset_display = array_map(function($t, $l) {
//						return (!$t && $l);
//						}, $theory_display, $lab_display);

//				$i = 0;
//				foreach ($courseBlk as $x) {
//					$TBS->MergeBlock('courseBlk_' . $i, $conn, "SELECT * FROM course_registration, courses WHERE student_id=$student_id AND courses.year=$year AND courses.sem_code=$sem_code AND courses.course_id = course_registration.course_id LIMIT $i, 1");
//					$TBS->MergeBlock('grades_' . $i . '_2', $conn, "SELECT * FROM grades");
//					++$i;
//				}

				$_SESSION['stage_1_data'] = array(
						'enrollment_no' => $student_roll_no,
						'student_id' => $student_id,
						'year_of_joining' => $year_of_joining,
						'sem_code_of_joining' => $sem_code_of_joining,
						'year' => $year,
						'sem_code' => $sem_code,
						);

				$show_first_form = false;
				$show_second_form = true;
			} else {
				$msg = "No courses for $student_roll_no in the year $year and semester $sem_code.";
			}
		} else {
			$msg = "Enrollment number $student_roll_no does not exist.";
		}
	}

	if (isset($_POST['submit_2'])) {

		$enrollment_no = $_SESSION['stage_1_data']['enrollment_no'];
		$student_id = $_SESSION['stage_1_data']['student_id'];
		$year_of_joining = $_SESSION['stage_1_data']['year_of_joining'];
		$sem_code_of_joining = $_SESSION['stage_1_data']['sem_code_of_joining'];
		$year = $_SESSION['stage_1_data']['year'];
		$sem_code = $_SESSION['stage_1_data']['sem_code'];

		$course_id = $_POST['course_id'];
		$grade_theory = $_POST['grade_theory'];
		$grade_lab = $_POST['grade_lab'];
		$sem_id = ecell_get_val('sem_structure', 'course_id', $course_id, 'sem_id');
		$exam_type = $_POST['exam_type'];

		$date_of_exam = date('Y-m-d', strtotime($_POST['date_of_exam']));
		$date_of_dec = date('Y-m-d', strtotime($_POST['date_of_dec']));

		if ($date_of_exam && $date_of_dec) {

			$status_value_id = ecell_get_status('on');

			$sql = "INSERT INTO results(course_id, student_id, sem_id, theory_grade, lab_grade, exam_type, date_of_exam, date_of_declaration, status_value_id, log_id) VALUES 								( :course_id, :student_id, :sem_id, :grade_theory, :grade_lab, :exam_type, :date_of_exam, :date_of_dec, :status_value_id, :log_id )";
			$ac_on = "Entered grades for student with enrollment_numner=$enrollment_no for course_id=$course_id";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'grade';
			$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':course_id', $course_id);
			$stmt->bindParam(':student_id', $student_id);
			$stmt->bindParam(':sem_id', $sem_id);
			$stmt->bindParam(':grade_theory', $grade_theory);
			$stmt->bindParam(':grade_lab', $grade_lab);
			$stmt->bindParam(':exam_type', $exam_type);
			$stmt->bindParam(':date_of_exam', $date_of_exam);
			$stmt->bindParam(':date_of_dec', $date_of_dec);
			$stmt->bindParam(':status_value_id', $status_value_id);
			$stmt->bindParam(':log_id', $log_id);
			$stmt->execute();

			$success = "Success";
		} else {
			$msg = "Enter date in proper format.";
		}


	}
}

$TBS->MergeBlock('sem_code_description', $conn, 'SELECT * FROM sem_code_description');
$TBS->MergeBlock('allowed_grades', $conn, 'SELECT * FROM allowed_grades');
$TBS->MergeBlock('allowed_grades_1', $conn, 'SELECT * FROM allowed_grades');

$TBS->Show();

?>
