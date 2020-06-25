<?php

include_once('../../includes/include.php');

error_reporting(-1);
ini_set('display_errors', 'On');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('student_result_old.html'); 

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


		$q = "SELECT * FROM `student` WHERE enrollment_no=:roll_no";
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
				$lab_display = array_map(function($c) {
						$lab = $c['lab_credit'] == 0 ? false : true;
						// return $lab;
						return true;
						}, $courseBlk);

				$theory_display = array_map(function($c) {
						$theory = $c['theory_credit'] == 0 ? false : true;
						// return $theory;
						return true;
						}, $courseBlk);

				$offset_display = array_map(function($t, $l) {
						return (!$t && $l);
						}, $theory_display, $lab_display);

				$i = 0;
				foreach ($courseBlk as $x) {
					$TBS->MergeBlock('courseBlk_' . $i, $conn, "SELECT * FROM course_registration, courses WHERE student_id=$student_id AND courses.year=$year AND courses.sem_code=$sem_code AND courses.course_id = course_registration.course_id LIMIT $i, 1");
					$TBS->MergeBlock('grades_' . $i . '_1', $conn, "SELECT * FROM grades");
					$TBS->MergeBlock('grades_' . $i . '_2', $conn, "SELECT * FROM grades");
					++$i;
				}

				$_SESSION['stage_1_data'] = array(
						'enrollment_no' => $student_roll_no,
						'student_id' => $student_id,
						'year_of_joining' => $year_of_joining,
						'sem_code_of_joining' => $sem_code_of_joining,
						'year' => $year,
						'sem_code' => $sem_code,
						'count_theory' => count(array_filter($theory_display)),
						'count_lab' => count(array_filter($lab_display))
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
		$count_theory = $_SESSION['stage_1_data']['count_theory'];
		$count_lab = $_SESSION['stage_1_data']['count_lab'];

		$lab_grades = array();
		$theory_grades = array();

		foreach ($_POST as $course_type_course_id => $grade_id) {
			if ($course_type_course_id[0] == 'L') {
				$lab_grades[substr($course_type_course_id, 2, strlen($course_type_course_id) - 2)] = $grade_id;
			} else if ($course_type_course_id[0] == 'T') {
				$theory_grades[substr($course_type_course_id, 2, strlen($course_type_course_id) - 2)] = $grade_id;
			}
		}


		if ((count($lab_grades) + count($theory_grades)) == ($count_theory + $count_lab)) {
			foreach ($theory_grades as $course_id => $theory_grade_id) {
				$lab_grade_id = $lab_grades[$course_id];

				$status_on_id = get_status('on');
				$log_id = '';
				$sql = "INSERT INTO `result` (`course_id`, `student_id`, `year_of_joining`, `sem_code_of_joining`, `year`, `sem_code`, `timestamp`, `course_grade`, `lab_grade`, `status_id`, `log_id`) VALUES ($course_id, $student_id, $year_of_joining, $sem_code_of_joining, $year, $sem_code, CURRENT_TIMESTAMP, '$theory_grade_id', '$lab_grade_id', '$status_on_id', '$log_id')";

				$ac_on = "Entered grades for student with enrollment_numner=$enrollment_no for course_id=$course_id";
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'grade';
				$log_id = log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

				$sql = "INSERT INTO `grade` (`course_id`, `student_id`, `year_of_joining`, `sem_code_of_joining`, `year`, `sem_code`, `timestamp`, `course_grade`, `lab_grade`, `status_id`, `log_id`) VALUES (:course_id, :student_id, :year_of_joining, :sem_code_of_joining, :year, :sem_code, CURRENT_TIMESTAMP, :theory_grade_id, :lab_grade_id, :status_on_id, :log_id)";
				$query = $conn->prepare($sql);
				$query->bindParam(':course_id', $course_id);
				$query->bindParam(':student_id', $student_id);
				$query->bindParam(':year_of_joining', $year_of_joining);
				$query->bindParam(':sem_code_of_joining', $sem_code_of_joining);
				$query->bindParam(':year', $year);
				$query->bindParam(':sem_code', $sem_code);
				$query->bindParam(':theory_grade_id', $theory_grade_id);
				$query->bindParam(':lab_grade_id', $lab_grade_id);
				$query->bindParam(':status_on_id', $status_on_id);
				$query->bindParam(':log_id', $log_id);
				$query->execute();

				$success = "Success";
			}
		} else {
			$msg = "Please input all the grades.";
		}

	}

}

$TBS->MergeBlock('sem_code_description', $conn, 'SELECT * FROM sem_code_description');
$TBS->MergeBlock('allowed_grades', $conn, 'SELECT * FROM allowed_grades');
$TBS->MergeBlock('allowed_grades_1', $conn, 'SELECT * FROM allowed_grades');

$TBS->Show();

?>
