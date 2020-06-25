<?php

include_once('../../includes/include.php');
include_once('../../includes/mpdf60/mpdf.php');


$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('upload_old_results_csv.html'); 

$success = "";
$show_form = "y";
$msg_err = "";

// error_reporting(E_ALL | E_STRICT);
// ini_set('display_errors', 'On');

if (isset($_POST['date'])) {
	$_POST['date'] = ecell_convert_date($_POST['date']);
	if (preg_match('/^[12]{1}[0-9]{3}-[01]{1}[0-9]{1}-[0-3]{1}[0-9]{1}$/', $_POST['date'])) {
		$date = $_POST['date'];
	} else {
		$msg_err .= "Please enter proper date.\n";
	}
}

if (isset($_POST['current_year'])) {
	$current_year = $_POST['current_year'];
}

if (isset($_POST['current_sem_code'])) {
	$current_sem_code = $_POST['current_sem_code'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {


	if ($_FILES["file"]["error"] > 0 && !$msg_err) {
		$msg_err .= "File not found.\n";
	} else {
		$uploadedFile = $_FILES['file']['tmp_name'];
		$uploadedFP = fopen($uploadedFile, 'rb');

		if (!feof($uploadedFP)) {

			$len = -1;

			while ($data = fgetcsv($uploadedFP)) {

				if ($len == -1) {
					$len = count($data);
				}
				if ($len != count($data)) {
					$msg_err .= "Please upload correct CSV file.\n";
					break;
				}
			}
		}
	}

	if (isset($_FILES['file']['name']) && !$msg_err) {

		$id_array = array();

		$uploadedFile = $_FILES['file']['tmp_name'];
		$fp = fopen($uploadedFile, 'rb');

		$course_array = str_replace('\n', '', fgets($fp));
		$course_array = explode(',', $course_array);

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

			$course_id_extracted = $stmt->fetchAll()[0]['course_id'];

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

		while (($line = fgets($fp)) != false) {

			$line = str_replace('\n', '', $line);
			$line = explode(',', $line);

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
				$msg_err .= "Student id not found for " . $student_roll_no . " on line no " . ($cnt + 1) . "\n";
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

		fclose($fp);

		$fp = fopen($uploadedFile, 'rb');
		$temp = fgets($fp);

		if ($all_correct) {

			$conn->beginTransaction();

			$cnt = 0;

			while (($line = fgets($fp)) != false) {

				$line = str_replace('\n', '', $line);
				$line = explode(',', $line);

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

						if (!$stmt->rowCount()) {
							$sql = "INSERT INTO `course_registration`(`course_id`, `student_id`, `grade_card_year`, `grade_card_sem_code`, `status_value_id`, `log_id`) VALUES (:course_id, :student_id, :grade_card_year, :grade_card_sem_code, :status_id,:log_id)";

							$ac_on = "Registered " . $enrollment_no . " in course id " . $course_id . ".";
							$s_i = $_SESSION['staff_id'];
							$r = $_SESSION['rank'];
							$tn = 'course_registration';

							$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

							$stmt = $conn->prepare($sql);

							$stmt->bindParam(':course_id', $course_id);
							$stmt->bindParam(':student_id', $student_id);
							$stmt->bindParam(':grade_card_year', $year);
							$stmt->bindParam(':grade_card_sem_code', $sem_code);
							$stmt->bindParam(':status_id', $status_id);
							$stmt->bindParam(':log_id', $log_id);

							try {
								$stmt->execute();
							} catch (PDOException $e) {
								echo $e->getMessage();
								echo "<br>";
								$msg_err .= "Result has been already uploaded for student on line no $cnt.\n";
								$kill = 1;
								break;
							}
						}

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
							// print_r($testing);
							$msg_err .= "Problem on line no $cnt: " . $e->getMessage() . "\n";
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
			} else {
				$conn->rollBack();
			}

		}

	}
}

$TBS->MergeBlock('sem_code_description, sem_code_description1', $conn, "SELECT * FROM sem_code_description, status WHERE status_name='on'"); 
$TBS->Show();

?>