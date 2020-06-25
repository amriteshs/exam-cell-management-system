<?php

include_once('../../includes/include.php');

$success = "";
$show_form = "y";
$msg_err = "";

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

if (!empty($_POST['file_id']) && isset($_POST['file_id'])) {

	$id = $_POST['file_id'];
	$sql = "SELECT mime, result_file FROM temp_results WHERE temp_results_id= :id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':id', $id);
	$stmt->execute();

	$output = fopen("php://output",'w') or die("Can't open php://output");
	header("Content-Type:application/csv");
	$filename = $_POST['program']."_".$_POST['year']."_".$_POST['semester'].".csv";
	header("Content-Disposition:attachment;filename=$filename");

	fputs($output, $stmt['result_file']);

	fclose($output);

	exit;

} else if (!empty($_POST['id']) && isset($_POST['id'])) { 

	$id_array = array();

	$id = $_POST['id'];
	$sql = "SELECT mime, sem_id, result_file FROM temp_results WHERE temp_results_id= :id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':id', $id);
	$stmt->execute();

	$res = $stmt->fetch();
	$sem_id = $res['sem_id'];

	$sql = "SELECT sem_id_year, sem_id_sem_code FROM course_structure WHERE sem_id= :sem_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':sem_id', $sem_id);
	$stmt->execute();

	$sem_details = $stmt->fetch();

	$current_year = $sem_details['sem_id_year'];
	$current_sem_code = $sem_details['sem_id_sem_code'];

	$date = date("YYYY-MM-DD");

	$fp = $res['result_file'];
 
	$flines = explode(PHP_EOL, $fp);

	$course_array = explode(',', $flines[0]);

	$applicable_lab_grades = array();

	$year_arr = array();
	$sem_code_array = array();

	$year_of_joining_arr = array();
	$sem_code_of_joining_arr = array();

	$sql = "SELECT * FROM grades";

	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$res = $stmt->fetchAll();

	$allowed_grades = array();
	foreach ($res as $x) {
		array_push($allowed_grades, $x['grade']);
	}

	array_push($applicable_lab_grades, "0", "0", "0");

	array_push($sem_code_array, "0", "0", "0");
	array_push($year_arr, "0", "0", "0");
	array_push($year_of_joining_arr, "0", "0", "0");
	array_push($sem_code_of_joining_arr, "0", "0", "0");

	for ($i = 3; $i < count($course_array); $i += 2) {

		$course_code = $course_array[$i];
		$course_code = explode(' ', $course_code);
		$course_code = $course_code[0];

		$sql = "SELECT * FROM courses WHERE course_code=:course_code AND year=:current_year AND sem_code=:sem_code";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':course_code', $course_code);
		$stmt->bindParam(':current_year', $current_year);
		$stmt->bindParam(':sem_code', $current_sem_code);
		$stmt->execute();

		$course_id_extracted = $stmt->fetch()['course_id'];

		$course_array[$i] = $course_id_extracted;
		$course_array[$i + 1] = $course_id_extracted;

		$course_id = $course_id_extracted;

		$q = "SELECT * FROM `courses` WHERE course_id=:course_id";

		$stmt = $conn->prepare($q);

		$stmt->bindParam(':course_id', $course_id);

		$stmt->execute();

		$f = $stmt->fetch();

		array_push($sem_code_array, $f['sem_code']);
		array_push($sem_code_array, $f['sem_code']);

		array_push($year_arr, $f['year']);
		array_push($year_arr, $f['year']);

		array_push($applicable_lab_grades, 1);

		if ($f['lab_credit'] == 0) {
			array_push($applicable_lab_grades, 0);
		} else {
			array_push($applicable_lab_grades, 1);
		}

	}

	$status_id = ecell_get_status('on');

	$all_correct = 1;
	$cnt = 1;

	while ($cnt < count($flines) - 1) {

		$line = explode(',', $flines[$cnt]);

		$student_id = 0;
		$student_roll_no = trim($line[1]);

		$q = "SELECT `student_id`, `program_id` FROM `student` WHERE enrollment_no=:student_roll_no";
		$stmt = $conn->prepare($q);
		$stmt->bindParam(':student_roll_no', $student_roll_no);
		$stmt->execute();
		$res = $stmt->fetch();
		if (empty($res)) {
			$q = "SELECT `student_id`, `program_id` FROM `old_branch` WHERE roll_no=:student_roll_no";
			$stmt = $conn->prepare($q);
			$stmt->bindParam(':student_roll_no', $student_roll_no);
			$stmt->execute();
			$res = $stmt->fetch();
		}

		$student_id = $res['student_id'];

		array_push($id_array, $student_id);

		if ($student_id == 0) {
			$all_correct = 0;
			$msg_err .= "Student id not found for " . $student_roll_no . " on line no " . ($cnt) . "\n";
		}

		for ($i = 3; $i < count($line); $i += 2) {

			$course_id = $course_array[$i];

			if (in_array($line[$i], $allowed_grades))
				$course_grade = $line[$i];
			else {
				$msg_err .= "Inapplicable theory grade in the given file for " . $student_roll_no . " in the column number " . $i . "\n";
				$all_correct = 0;
			}

			if ($applicable_lab_grades[$i + 1]) {
				$xyz = str_replace('\n', '', $line[$i + 1]);
				$xyz = trim($xyz);
				if (in_array($xyz, $allowed_grades))
					$lab_grade = $xyz;
				else {
					$msg_err .= "Inapplicable lab grade in the given file for " . $student_roll_no . " in the column number " . $i . "\n";
					$all_correct = 0;
				}
			} else {
				$lab_grade = "-";
			}

		}

		$cnt++;

	}

	if ($all_correct) {

		$conn->beginTransaction();

		$cnt = 1;

		while ($cnt < count($flines) - 2) {

			$line = explode(',', $flines[$cnt]);

			$student_id = $id_array[$cnt];

			$q = "SELECT * FROM student WHERE student_id=:student_id";

			$stmt = $conn->prepare($q);

			$stmt->bindParam(':student_id', $student_id);

			$stmt->execute();

			$f = $stmt->fetchAll()[0];
			$year_of_joining = $f['year'];
			$sem_code_of_joining = $f['sem_code'];
			$program_id = $f['program_id'];
			$student_name = $f['first_name'];
			if (strlen($f['middle_name'])) $student_name .= (" " . $f['middle_name']);
			if (strlen($f['last_name'])) $student_name .= (" " . $f['last_name']);

			$enrollment_no = $f['enrollment_no'];

			$testing = $f;

			$kill = 0;

			for ($i = 3; $i < count($line); $i += 2) {

				$year = $year_arr[$i];
				$sem_code = $sem_code_array[$i];

				$course_id = $course_array[$i];

				$course_grade = "NO";
				if (in_array(trim($line[$i]), $allowed_grades))
					$course_grade = trim($line[$i]);

				if ($applicable_lab_grades[$i + 1]) {
					$xyz = str_replace('\n', '', $line[$i + 1]);
					$xyz = trim($xyz);
					if (in_array($xyz, $allowed_grades))
						$lab_grade = $xyz;
				} else {
					$lab_grade = "-";
				}

				if ($course_grade != "NO") {

					$sql = "SELECT sem_id FROM course_structure WHERE year_of_joining=:year_of_joining AND sem_code_of_joining=:sem_code_of_joining AND sem_id_year=:current_year AND sem_id_sem_code=:current_sem_code AND program_id=:program_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':program_id', $program_id);
					$stmt->bindParam(':year_of_joining', $year_of_joining);
					$stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
					$stmt->bindParam(':current_year', $current_year);
					$stmt->bindParam(':current_sem_code', $current_sem_code);
					$stmt->execute();
					$sem_id = $stmt->fetch()['sem_id'];

					$sql = "SELECT * FROM course_registration WHERE course_id=:course_id AND student_id=:student_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':course_id', $course_id);
					$stmt->bindParam(':student_id', $student_id);
					$stmt->execute();



					$ac_on = "Entered grades for student " . $student_roll_no . " with theory grade " . $course_grade . " and lab grade " . $lab_grade . " for course id " . $course_id;

					$course_grade = ecell_get_val('grades', 'grade', $course_grade, 'grade_id');
					$lab_grade = ecell_get_val('grades', 'grade', $lab_grade, 'grade_id');

					$sql = "INSERT INTO `results`(`course_id`, `student_id`, `sem_id`, `timestamp`, `theory_grade`, `lab_grade`, `date_of_declaration`, `status_value_id`, `log_id`) VALUES (:course_id,:student_id,:timestamp,:course_grade,:lab_grade,:date,:status_id,:log_id)";

					$s_i = $_SESSION['staff_id'];
					$r = $_SESSION['rank'];
					$tn = 'results';

					$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

					$sql = "INSERT INTO `results`(`course_id`, `student_id`, `sem_id`, `timestamp`, `theory_grade`, `lab_grade`, `exam_type`, `date_of_exam`, `date_of_declaration`, `status_value_id`, `log_id`) VALUES (:course_id,:student_id,:sem_id,:timestamp,:course_grade,:lab_grade,:exam_type, :date_of_exam,:date,:status_id,:log_id)";

					$sth = $conn->prepare($sql);

					$sth->bindParam(':course_id', $course_id);
					$sth->bindParam(':student_id', $student_id);
					$sth->bindParam(':sem_id', $sem_id);
					$sth->bindParam(':timestamp', $null);
					$test = "END";
					$sth->bindParam(':exam_type', $test);
					$sth->bindParam(':date_of_exam', $null);
					$sth->bindParam(':date', $date);
					$sth->bindParam(':course_grade', $course_grade);
					$sth->bindParam(':lab_grade', $lab_grade);
					$sth->bindParam(':status_id', $status_id);
					$sth->bindParam(':log_id', $log_id);

					try {
						$sth->execute();
					} catch (PDOException $e) {

						$msg_err = "Problem on line no $cnt: " . $e->getMessage() . "\n";
						echo $msg_err;
					}

				}

				if ($kill) {
					break;
				}

			}

			$cnt++;

		}

		if (!$msg_err) {
			$success = 'Grades successfully added.';
			$conn->commit();
			echo $success;

//			$sql = "DELETE FROM temp_results WHERE temp_results_id= :id";
//			$stmt = $conn->prepare($sql);
//			$stmt->bindParam(':id', $id);
//			$stmt->execute();
		} else {
			$conn->rollBack();
		}
	}
	else {
		echo "not correct";
	}

} else if (!empty($_POST['discard']) && isset($_POST['discard'])) {

	$id = $_POST['discard'];
	$sql = "DELETE FROM temp_results WHERE temp_results_id= :id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':id', $id);
	$stmt->execute();
	header('location: declare_results.php');

}

?>
