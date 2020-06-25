<?php

include_once('../../includes/include.php');
include_once('../../includes/mpdf60/mpdf.php');


$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('upload_old_back_results_csv.html');

$success = "";
$show_form = "y";
$msg_err = "";

$errorArray = array();
$warnArray = array();

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');


if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (isset($_POST['year'])) {
		$year = $_POST['year'];
	}

	if (isset($_POST['month'])) {
		$month = $_POST['month'];
	}

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

		$sql = "SELECT * FROM grades";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
		$res = $stmt->fetchAll();

		$allowed_grades = array();
		foreach ($res as $x) {
			array_push($allowed_grades, $x['grade']);
		}

		$uploadedFile = $_FILES['file']['tmp_name'];
		$fp = fopen($uploadedFile, 'rb');


		$linecount = 1;
		while (($line = fgets($fp)) != false) {
			if ($linecount == 1) {
				++$linecount;
				continue;
			}


			$line = str_replace('\n', '', $line);
			$line = explode(',', $line);

			$enrollment_no = trim($line[1]);
			$course_code = trim($line[3]);

			$student_id = ecell_get_val('student', 'enrollment_no', $enrollment_no, 'student_id');

			if ($student_id == 0) {
				$errorArray[] = "The student $enrollment_no does not exist on line no. $linecount";
			}

			$sql = "SELECT course_registration.course_id FROM courses, course_registration WHERE course_registration.course_id=courses.course_id AND course_registration.student_id=:student_id AND courses.course_code=:course_code";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':student_id', $student_id);
			$stmt->bindParam(':course_code', $course_code);
			$stmt->execute();
			$course_id = $stmt->fetch(PDO::FETCH_ASSOC)['course_id'];
			if($course_id == 0){
				$errorArray[] = "$enrollment_no is not registered in $course_code on line no. $linecount";
			}

			$theory_grade = trim($line[5]);
			$lab_grade = trim($line[6]);

			if (!in_array($theory_grade, $allowed_grades)) {
				$errorArray[] = "The theory grade $theory_grade does not exist on line no. $linecount";
			}
			if (!in_array($lab_grade, $allowed_grades)) {
				$errorArray[] = "The lab grade $lab_grade does not exist on line no. $linecount";
			}

			$sql = "SELECT * FROM results WHERE course_id=:course_id AND student_id=:student_id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':student_id', $student_id);
			$stmt->bindParam(':course_id', $course_id);
			$stmt->execute();
			if ($stmt->rowCount() == 0) {
				$errorArray[] = "No grades were previously submitted for $enrollment_no for $course_code on line no $linecount.";
			}

			++$linecount;
		}

		fclose($fp);

		if (count($errorArray) == 0) {
			$uploadedFile = $_FILES['file']['tmp_name'];
			$fp = fopen($uploadedFile, 'rb');

			$conn->beginTransaction();
			$linecount = 1;
			while (($line = fgets($fp)) != false) {
				if ($linecount == 1) {
					++$linecount;
					continue;
				}

				$line = str_replace('\n', '', $line);
				$line = explode(',', $line);

				$enrollment_no = trim($line[1]);
				$course_code = trim($line[3]);
				$date_of_declaration = trim($line[8]);
				$date_of_exam = trim($line[7]);

				$student_id = ecell_get_val('student', 'enrollment_no', $enrollment_no, 'student_id');

				$sql = "SELECT course_registration.course_id FROM courses, course_registration WHERE course_registration.course_id=courses.course_id AND course_registration.student_id=:student_id AND courses.course_code=:course_code";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':student_id', $student_id);
				$stmt->bindParam(':course_code', $course_code);
				$stmt->execute();
				$course_id = $stmt->fetch(PDO::FETCH_ASSOC)['course_id'];

				$theory_grade = trim($line[5]);
				$lab_grade = trim($line[6]);

				$sql = "SELECT * FROM results WHERE course_id=:course_id AND student_id=:student_id ORDER BY timestamp DESC";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':student_id', $student_id);
				$stmt->bindParam(':course_id', $course_id);
				$stmt->execute();
				$res = $stmt->fetchAll()[0];

				$sem_id = $res['sem_id'];

				if ($theory_grade == '-') {
					$theory_grade = $res['theory_grade'];
				} else {
					$theory_grade = ecell_get_val('grades', 'grade', $theory_grade, 'grade_id');
				}
				if ($lab_grade == '-') {
					$lab_grade = $res['lab_grade'];
				} else {
					$lab_grade = ecell_get_val('grades', 'grade', $lab_grade, 'grade_id');
				}

				$sql = "INSERT INTO `results`(`course_id`, `student_id`, `sem_id`, `timestamp`, `theory_grade`, `lab_grade`, `date_of_declaration`, `status_value_id`, `log_id`) VALUES (:course_id,:student_id,:timestamp,:course_grade,:lab_grade,:date,:status_id,:log_id)";
				$ac_on = "Back exam Entered grades for student " . $enrollment_no . " with theory grade " . $theory_grade . " and lab grade " . $lab_grade . " for course id " . $course_id;
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'results';
				$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

				$status_id = ecell_get_status('on');

				$sql = "INSERT INTO `results`(`course_id`, `student_id`, `sem_id`, `timestamp`, `theory_grade`, `lab_grade`, `exam_type`, `date_of_exam`, `date_of_declaration`, `status_value_id`, `log_id`) VALUES (:course_id,:student_id,:sem_id,:timestamp,:course_grade,:lab_grade,:exam_type, :date_of_exam,:date,:status_id,:log_id)";

				$sth = $conn->prepare($sql);

				$sth->bindParam(':course_id', $course_id);
				$sth->bindParam(':student_id', $student_id);
				$sth->bindParam(':sem_id', $sem_id);
				$sth->bindParam(':timestamp', $null);
				$test = "BACK";
				$sth->bindParam(':exam_type', $test);
				$sth->bindParam(':date_of_exam', $date_of_exam);
				$sth->bindParam(':date', $date_of_declaration);
				$sth->bindParam(':course_grade', $theory_grade);
				$sth->bindParam(':lab_grade', $lab_grade);
				$sth->bindParam(':status_id', $status_id);
				$sth->bindParam(':log_id', $log_id);

				try {
//					echo "$linecount <--> $course_id-$student_id <--> $enrollment_no - $course_code<br>";
					$sth->execute();
				} catch (PDOException $e) {
					$errorArray[] = "Problem on line no $linecount for $enrollment_no : " . $e->getMessage() . "\n";
					break;
				}

				++$linecount;
			}
			fclose($fp);

			if (count($errorArray) == 0) {
				$success = 'Grades successfully added.';

				$conn->commit();
			} else {
				$msg_err = "yyoyo";
				$conn->rollBack();
			}
		} else {
			$msg_err = "yyoyo";
		}

	}
}

$TBS->MergeBlock('exam_month', array(1,2,3,4,5,6,7,8,9,10,11,12));
$TBS->MergeBlock('errorBlk', $errorArray);
//$TBS->MergeBlock('exam_month', $exam_month);
$TBS->MergeBlock('sem_code_description, sem_code_description1', $conn, "SELECT * FROM sem_code_description, status WHERE status_name='on'"); 
$TBS->Show();

?>