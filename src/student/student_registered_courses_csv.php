<?php

include_once('../../includes/include.php');

error_reporting(E_ALL); ini_set('display_errors', 1);

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('student_registered_courses_csv.html');

$TBS->MergeBlock('program', $conn, 'SELECT * FROM program');
$TBS->MergeBlock('sem_code_description', $conn, "SELECT * FROM sem_code_description");
$TBS->MergeBlock('sem_code_description1', $conn, "SELECT * FROM sem_code_description");

$show_up  = "y";
$show_down = "";
$success    = "";
$msg_err    = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach($_POST as $k=>$v) {
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v,FILTER_SANITIZE_STRING);
		}			
	}

	if (isset($_POST['course_code'])) {
		$course_id = $_POST['course_code'];
	}

}


if (isset($_POST['submit_up'])) {

	if (isset($_POST['program_id'])) {
		$program_id = $_POST['program_id'];
	}

	if (isset($_POST['year_of_joining']) && filter_var($_POST['year_of_joining'], FILTER_VALIDATE_INT) && preg_match('/^[12]{1}[0-9]{3}$/', $_POST['year_of_joining'])) {
		$year_of_joining = $_POST['year_of_joining'];
	} else {
		$msg_err .= "Please enter proper year of joining.\n";
	}

	if (isset($_POST['sem_code_of_joining']) && filter_var($_POST['sem_code_of_joining'], FILTER_VALIDATE_INT)) {
		$sem_code_of_joining = $_POST['sem_code_of_joining'];
	}	

	if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT) && preg_match('/^[12]{1}[0-9]{3}$/', $_POST['year'])) {
		$year = $_POST['year'];
	} else {
		$msg_err .= "Please enter proper year.\n";
	}

	if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
		$sem_code = $_POST['sem_code'];
	}

	if (isset($_POST['year']) && isset($_POST['year_of_joining'])) {
		if (intval($_POST['year']) < intval($_POST['year_of_joining'])) {
			$msg_err .= "Year should be greater than or equal to year of joining.\n";
		}
	}

	if (isset($_POST['elective'])) {

		$sql = "SELECT * FROM courses WHERE year=:year AND sem_code=:sem_code AND course_type='E'";

		$stmt = $conn->prepare($sql);

		$stmt->bindParam(':year', $year);
		$stmt->bindParam(':sem_code', $sem_code);

		$stmt->execute();

		if ($stmt->rowCount() == 0) {
			$msg_err .= "No courses available for this given year and semester code.";
		}

		$TBS->MergeBlock('courses', $conn, "SELECT * FROM courses WHERE year='$year' AND sem_code='$sem_code' AND course_type='E'");

	} else {

		$sql = "SELECT courses.course_id, courses.course_code FROM course_structure, sem_structure, courses WHERE course_structure.semester_id = sem_structure.semester_id AND sem_structure.course_id = courses.course_id AND course_structure.program_id = :program_id AND course_structure.year_of_joining=:year_of_joining AND course_structure.sem_code_of_joining=:sem_code_of_joining AND course_structure.year = :year AND course_structure.sem_code=:sem_code";

		$stmt = $conn->prepare($sql);

		$stmt->bindParam(':program_id', $program_id);
		$stmt->bindParam(':year_of_joining', $year_of_joining);
		$stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
		$stmt->bindParam(':year', $year);
		$stmt->bindParam(':sem_code', $sem_code);

		$stmt->execute();

		if ($stmt->rowCount() == 0) {
			$msg_err .= "No electives available for this program in the given year and semester code.";
		}

		$TBS->MergeBlock('courses', $conn, "SELECT courses.course_id, courses.course_code FROM course_structure, sem_structure, courses WHERE course_structure.semester_id = sem_structure.semester_id AND sem_structure.course_id = courses.course_id AND course_structure.program_id = '$program_id' AND course_structure.year_of_joining='$year_of_joining' AND course_structure.sem_code_of_joining='$sem_code_of_joining' AND course_structure.year = '$year' AND course_structure.sem_code='$sem_code'");
	
	}

	$_SESSION['program_id'] = $program_id;
	$_SESSION['year_of_joining'] = $year_of_joining;
	$_SESSION['sem_code_of_joining'] = $sem_code_of_joining;
	$_SESSION['year'] = $year;
	$_SESSION['sem_code'] = $sem_code;

	if (!$msg_err) {
		$show_up = "";
		$show_down = "y";
	}

}

if (isset($_POST['submit_down'])) {
	if (isset($_FILES['file']['name'])) {

		if ($_FILES["file"]["error"] > 0) {

			$msg_err .= "Please upload the file again.";

		} else {

			$program_id = $_SESSION['program_id'];
			$year_of_joining = $_SESSION['year_of_joining'];
			$sem_code_of_joining = $_SESSION['sem_code_of_joining'];
			$year = $_SESSION['year'];
			$sem_code = $_SESSION['sem_code'];

			unset($_SESSION['program_id']);
			unset($_SESSION['year_of_joining']);
			unset($_SESSION['sem_code_of_joining']);
			unset($_SESSION['year']);
			unset($_SESSION['sem_code']);

			$uploadedFile = $_FILES['file']['tmp_name'];
			$uploadedFP = fopen($uploadedFile, 'rb');

			$linecount  = 0;

			if (!feof($uploadedFP)) {

				while ($data = fgetcsv($uploadedFP)) {

					if (count($data) != 1) {
						$msg_err .= "Please upload correct CSV file.\n";
						break;
					}

				}

			}

			$uploadedFile = $_FILES['file']['tmp_name'];
			$uploadedFP = fopen($uploadedFile, 'rb');

			$linecount  = 0;

			if (!$msg_err) {

				if (!feof($uploadedFP)) {

					while ($data = fgetcsv($uploadedFP)) {

						$linecount++;

						$enrollment_no = test_input($data[0]);

						$student_id = get_val('student', 'enrollment_no', $enrollment_no, 'student_id');

						if (!$student_id) {
							$student_id = get_val('branch_change_history', 'roll_no', $enrollment_no, 'student_id');
							if (!$student_id) {
								$msg_err .= "The student at line no ".$linecount." doesn't exist.";
							}
							continue;
						}

						$sql = "SELECT * FROM course_registration WHERE student_id = :student_id AND course_id = :course_id";
						$stmt = $conn->prepare($sql);
						$stmt->bindParam(':student_id', $student_id);
						$stmt->bindParam(':course_id', $course_id);
						$stmt->execute();
						if ($stmt->rowCount() == 1) {
							$msg_err .= "The student at line no $linecount has already been registered to the course.";
						}

					}

				}

			}

			$linecount = 0;

			if (!$msg_err) {

				$uploadedFile = $_FILES['file']['tmp_name'];
				$uploadedFP = fopen($uploadedFile, 'rb');

				$conn->beginTransaction();

				if (!feof($uploadedFP)) {

					while ($data = fgetcsv($uploadedFP)) {

						$linecount++;

						$status_id = get_status('on');

						$enrollment_no = test_input($data[0]);

						$student_id = get_val('student', 'enrollment_no', $enrollment_no, 'student_id');

						if (!$student_id) {
							$student_id = get_val('branch_change_history', 'roll_no', $enrollment_no, 'student_id');
						}

						$sql = "SELECT * FROM student WHERE student_id=':student_id'";
						$sth = $conn->prepare($sql);
						$sth->bindParam(':student_id', $student_id);

						$sth->execute();

						$res = $sth->fetchAll();

						$student_name = "";

						if ($sth->rowCount() == 1) { 
							$first_name = $res['first_name'];
							$middle_name = $last_name = '';
							if (strlen($res['middle_name'])) $middle_name = $res['middle_name'];
							if (strlen($res['last_name'])) $last_name = $res['last_name'];
							$student_name = $first_name;
							if (strlen($middle_name)) $student_name .= " ".$middlename;
							if (strlen($last_name)) $student_name .= " ".$last_name;
						}

						$sql = "INSERT INTO `course_registration`(`course_id`, `program_id`, `student_id`, `student_name`, `roll_no`, `year_of_joining`, `sem_code_of_joining`, `year`, `sem_code`, `status_id`, `log_id`) VALUES (:course_id, :program_id, :student_id, :student_name, :roll_no, :year_of_joining, :sem_code_of_joining, :year, :sem_code, :status_id, :log_id)";

						$ac_on = "Registered ".$enrollment_no." in the course id ".$course_id.".";
						$s_i = $_SESSION['staff_id'];
						$r = $_SESSION['rank'];
						$tn = 'course_registration';

						$log_id = log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

						$sql = "INSERT INTO `course_registration`(`course_id`, `program_id`, `student_id`, `student_name`, `roll_no`, `year_of_joining`, `sem_code_of_joining`, `year`, `sem_code`, `status_id`, `log_id`) VALUES (:course_id, :program_id, :student_id, :student_name, :roll_no, :year_of_joining, :sem_code_of_joining, :year, :sem_code, :status_id, :log_id)";

						$stmt = $conn->prepare($sql);

						$stmt->bindParam(':course_id', $course_id);
						$stmt->bindParam(':program_id', $program_id);
						$stmt->bindParam(':student_id', $student_id);
						$stmt->bindParam(':student_name', $student_name);
						$stmt->bindParam(':roll_no', $enrollment_no);
						$stmt->bindParam(':year_of_joining', $year_of_joining);
						$stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
						$stmt->bindParam(':year', $year);
						$stmt->bindParam(':sem_code', $sem_code);
						$stmt->bindParam(':status_id', $status_id);
						$stmt->bindParam(':log_id', $log_id);

						try {
							$stmt->execute();
						} catch (PDOException $e) {
							$msg_err .= "Problem on line no. ".$linecount.": ".$e->getMessage()."\n";
						}

					}

				}	

				$conn->rollBack();

			}

			if (!$msg_err) {

				$uploadedFile = $_FILES['file']['tmp_name'];
				$uploadedFP = fopen($uploadedFile, 'rb');

				if (!feof($uploadedFP)) {

					while ($data = fgetcsv($uploadedFP)) {

						$status_id = get_status('on');

						$enrollment_no = test_input($data[0]);

						$student_id = get_val('student', 'enrollment_no', $enrollment_no, 'student_id');

						if (!$student_id) {
							$student_id = get_val('branch_change_history', 'roll_no', $enrollment_no, 'student_id');
						}

						$sql = "SELECT * FROM student WHERE student_id=':student_id'";
						$sth = $conn->prepare($sql);
						$sth->bindParam(':student_id', $student_id);

						$sth->execute();

						$res = $sth->fetchAll();

						$student_name = "";

						if ($sth->rowCount() == 1) { 
							$first_name = $res['first_name'];
							$middle_name = $last_name = '';
							if (strlen($res['middle_name'])) $middle_name = $res['middle_name'];
							if (strlen($res['last_name'])) $last_name = $res['last_name'];
							$student_name = $first_name;
							if (strlen($middle_name)) $student_name .= " ".$middlename;
							if (strlen($last_name)) $student_name .= " ".$last_name;
						}

						$sql = "INSERT INTO `course_registration`(`course_id`, `program_id`, `student_id`, `student_name`, `roll_no`, `year_of_joining`, `sem_code_of_joining`, `year`, `sem_code`, `status_id`, `log_id`) VALUES (:course_id, :program_id, :student_id, :student_name, :roll_no, :year_of_joining, :sem_code_of_joining, :year, :sem_code, :status_id, :log_id)";

						$ac_on = "Registered ".$enrollment_no." in the course ".$course_id.".";
						$s_i = $_SESSION['staff_id'];
						$r = $_SESSION['rank'];
						$tn = 'course_registration';

						$log_id = log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

						$sql = "INSERT INTO `course_registration`(`course_id`, `program_id`, `student_id`, `student_name`, `roll_no`, `year_of_joining`, `sem_code_of_joining`, `year`, `sem_code`, `status_id`, `log_id`) VALUES (:course_id, :program_id, :student_id, :student_name, :roll_no, :year_of_joining, :sem_code_of_joining, :year, :sem_code, :status_id, :log_id)";

						$stmt = $conn->prepare($sql);

						$stmt->bindParam(':course_id', $course_id);
						$stmt->bindParam(':program_id', $program_id);
						$stmt->bindParam(':student_id', $student_id);
						$stmt->bindParam(':student_name', $student_name);
						$stmt->bindParam(':roll_no', $enrollment_no);
						$stmt->bindParam(':year_of_joining', $year_of_joining);
						$stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
						$stmt->bindParam(':year', $year);
						$stmt->bindParam(':sem_code', $sem_code);
						$stmt->bindParam(':status_id', $status_id);
						$stmt->bindParam(':log_id', $log_id);

						$stmt->execute();

					}


					$success = "Success";

				}
			}

		}

	}
}

$TBS->Show();

?>
