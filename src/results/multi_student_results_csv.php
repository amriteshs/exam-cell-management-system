<?php

include_once('../../includes/include.php');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('multi_student_results_csv.html'); 

$success = "";
$show_form = "y";
$msg_err = "";


if (isset($_FILES["file"]["error"]) && isset($_FILES['file']['tmp_name'])) {


	foreach ($_POST as $k=>$v) {
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
		}
	}
	
	if (isset($_POST['program_id'])) {
		$program_id = $_POST['program_id'];
	}

	if (isset($_POST['year_of_joining']) && preg_match('/^[12]{1}[0-9]{3}$/', $_POST['year_of_joining'])) {
		$year_of_joining = $_POST['year_of_joining'];
	} else {
		$msg_err .= "Please enter proper year of joining.\n";
	}
	
	if (isset($_POST['sem_code_of_joining'])) {
		$sem_code_of_joining = $_POST['sem_code_of_joining'];
	}
	
	if (isset($_POST['year']) && preg_match('/^[12]{1}[0-9]{3}$/', $_POST['year'])) {
		$year = $_POST['year'];
	} else {
		$msg_err .= "Please enter proper year.\n";
	}

	if (isset($_POST['sem_code'])) {
		$sem_code = $_POST['sem_code'];
	}

	if ($_FILES["file"]["error"] > 0) {

		$msg_err = "File not found.\n";

	} else {

		$blob = fopen($_FILES['file']['tmp_name'], 'rb');
		$mime = mime_content_type($_FILES['file']['tmp_name']);	

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

	if (!$msg_err) {

		//wwww

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

			$sql = "SELECT * FROM courses WHERE course_code = :course_code AND year = :current_year AND sem_code= :sem_code";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':course_code', $course_code);
			$stmt->bindParam(':current_year', $year);
			$stmt->bindParam(':sem_code', $sem_code);
			$stmt->execute();
			if($stmt->rowCount()) {
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
			} else {
				$msg_err .= "Course not found " . $course_code .  "\n";
			}

		}


		$all_correct = 1;
		$cnt = 1;
		if(!$msg_err) {
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
				//echo $msg_err;
				$cnt++;

			}
		} else {
			$all_correct = 0;
		}

		fclose($fp);



		//wwwww
		if($all_correct != 0) {
			$status_value_id = ecell_get_status('on');

			$query = "SELECT * FROM course_structure WHERE program_id=:program_id AND year_of_joining=:year_of_joining AND sem_code_of_joining=:sem_code_of_joining AND sem_id_year=:year AND sem_id_sem_code=:sem_code";
			$stmt = $conn->prepare($query);

			$stmt->bindParam(':program_id', $program_id);
			$stmt->bindParam(':year_of_joining', $year_of_joining);
			$stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
			$stmt->bindParam(':year', $year);
			$stmt->bindParam(':sem_code', $sem_code);

			$stmt->execute();
			if($stmt->rowCount()) {
				$sem_id = $stmt->fetchAll()[0]['sem_id'];

				$sql = "INSERT INTO `temp_results`(`temp_results_id`, `sem_id`, `result_file`, `mime`, `status_value_id`, `log_id`) 
			VALUES (NULL, :sem_id, :result_file, :mime, :status_value_id, :log_id)";

				$ac_on = "Entered grade csv for $program_id in year: $year and semester code: $sem_code.";
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'temp_results';

				$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

				$sql = "INSERT INTO `temp_results`(`temp_results_id`, `sem_id`, `result_file`, `mime`, `status_value_id`, `log_id`) 
			VALUES (NULL, :sem_id, :result_file, :mime, :status_value_id, :log_id)";

				$stmt = $conn->prepare($sql);

				$stmt->bindParam(':sem_id', $sem_id);
				$stmt->bindParam(':result_file', $blob, PDO::PARAM_LOB);
				$stmt->bindParam(':mime', $mime);
				$stmt->bindParam(':status_value_id', $status_value_id);
				$stmt->bindParam(':log_id', $log_id);

				$stmt->execute();

				$success = "Success";
			}else {
				$msg_err .= " \n Not valid course structure found for the Entered program";
			}
		}else {
			$msg_err .= " \n Please upload valid file";
		}

	}

} 

$TBS->MergeBlock('program', $conn, "SELECT * FROM program");
$TBS->MergeBlock('sem_code_description, sem_code_description_joining', $conn, "SELECT * FROM sem_code_description");

$TBS->Show();

?>
