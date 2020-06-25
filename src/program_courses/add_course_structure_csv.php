<?php

// ini_set('display_startup_errors', 1);
// ini_set('display_errors', 1);
// error_reporting(-1);

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('add_course_structure_csv.html');

$courses = array();
$coursesname = array();
$success = "";

$sem_query = "select sem_code_id, title from sem_code_description";
$sth       = $conn->prepare($sem_query);
$sth->execute();
$semblock = $sth->fetchAll();

$sql = "SELECT * FROM status WHERE status_name='on'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll();
if ($stmt->rowCount() == 1) {
	foreach ($result as $row) {
		$status_id = $row['status_id'];
	}
}

$error = '';
$allcorrect = true;

if (isset($_POST["submit"])) {
	if (isset($_FILES['file']['name'])) {
		if ($_FILES["file"]["error"] > 0) {
			$error .= "Error: " . $_FILES["file"]["error"] . '\n';
			$allcorrect = false;
		} else {

			$year_of_joining = $_POST['year_of_joining'];
			$program_id = $_POST['course_code'];
			$sem_code_of_joining = $_POST['sem_code_of_joining'];

			$uploadedFile = $_FILES['file']['tmp_name'];
			$uploadedFP = fopen($uploadedFile, 'rb');
			$linecount  = 1;

			$year_semcode = array();

			if (!feof($uploadedFP)) {
				while ($row_in_csv = fgetcsv($uploadedFP)) {
					if ($linecount == 1) {
						$linecount++;
						continue;
					}

					$year = $row_in_csv[0];
					$sem_code = $row_in_csv[1];
					$course_code = $row_in_csv[2];
					$semester_title = $row_in_csv[3];

					if (!isset($year_semcode["$year"]["$sem_code"])) {
						$year_semcode["$year"]["$sem_code"] = array();
					}

					// array_push($year_semcode["$year"]["$sem_code"], $course_code);
					array_push($year_semcode["$year"]["$sem_code"], 
							array("course_code"=>$course_code,
								"line_num"=>$linecount,
								"semester_title"=>$semester_title));
					// print_r($year_semcode);
					$linecount++;
				}

				foreach ($year_semcode as $year => $sem_code_arr) {
					foreach ($sem_code_arr as $sem_code => $course_code_arr) {

						$sql = "SELECT * FROM `course_structure` WHERE program_id=:program_id AND year_of_joining=:year_of_joining AND sem_id_year=:year AND sem_id_sem_code=:sem_code AND sem_code_of_joining=:sem_code_of_joining";
						$query = $conn->prepare($sql);
						$query->bindParam(":program_id", $program_id);
						$query->bindParam(":year_of_joining", $year_of_joining);
						$query->bindParam(":year", $year);
						$query->bindParam(":sem_code", $sem_code);
						$query->bindParam(":sem_code_of_joining", $sem_code_of_joining);
						$query->execute();
						$count = $query->rowCount();

						if ($count != 0) {
							foreach ($course_code_arr as $key => $value) {
								$error .= "The entry at line no " . $value['line_num'] . " already exists.\n";
							}
							$allcorrect = false;
						}

					}
				}

				if ($allcorrect) {
					foreach ($year_semcode as $year => $sem_code_arr) {
						foreach ($sem_code_arr as $sem_code => $course_code_arr) {
							//$sql = "INSERT INTO `course_structure` (`semester_id`, `program_id`, `batch_year`, `year`, `sem_code`, `status_id`, `log_id`) VALUES (NULL, $program_id, $batch_year, $year, $sem_code, $status_id, 'LOG_ID')";
							try {
								$conn->beginTransaction();

								$sql = "SELECT * FROM `course_structure` WHERE `program_id`=:program_id AND `sem_title`=:semester_title AND `year_of_joining`=:year_of_joining AND `sem_code_of_joining`=:sem_code_of_joining AND `sem_id_year`=:year AND `sem_id_sem_code`=:sem_code";
								$query = $conn->prepare($sql);
								$query->bindParam(':program_id', $program_id);
								$query->bindParam(':semester_title', $course_code_arr[0]['semester_title']);
								$query->bindParam(':year_of_joining', $year_of_joining);
								$query->bindParam(':sem_code_of_joining', $sem_code_of_joining);
								$query->bindParam(':year', $year);
								$query->bindParam(':sem_code', $sem_code);
								$query->execute();

								if ($query->rowCount() == 0) {

									$sql = "INSERT INTO `course_structure` (`sem_id`, `program_id`, `sem_title`, `year_of_joining`, `sem_code_of_joining`, `sem_id_year`, `sem_id_sem_code`, `status_value_id`, `log_id`) VALUES (NULL, $program_id, '$semester_title', $year_of_joining, $sem_code_of_joining, $year, $sem_code, $status_id, 'LOG_ID')";
									$ac_on = "Added a new semester for program=$program_id AND batch_year=$year AND year=$year AND sem_code=$sem_code AND sem_code_of_joining=$sem_code_of_joining";
									$s_i = $_SESSION['staff_id'];
									$r = $_SESSION['rank'];
									$tn = 'course_structure';
									$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

									// print_r($course_code_arr);

									$sql = "INSERT INTO `course_structure` (`sem_id`, `program_id`, `sem_title`, `year_of_joining`, `sem_code_of_joining`, `sem_id_year`, `sem_id_sem_code`, `status_value_id`, `log_id`) VALUES (NULL, :program_id, :semester_title, :year_of_joining, :sem_code_of_joining, :year, :sem_code, :status_id, :log_id)";
									$query = $conn->prepare($sql);
									$query->bindParam(':program_id', $program_id);
									$query->bindParam(':semester_title', $course_code_arr[0]['semester_title']);
									$query->bindParam(':year_of_joining', $year_of_joining);
									$query->bindParam(':sem_code_of_joining', $sem_code_of_joining);
									$query->bindParam(':year', $year);
									$query->bindParam(':sem_code', $sem_code);
									$query->bindParam(':status_id', $status_id);
									$query->bindParam(':log_id', $log_id);
									$query->execute();

									$sql = "SELECT MAX(sem_id) FROM course_structure";
									$query = $conn->prepare($sql);
									$query->execute();
									$semester_id = $query->fetch()[0];
								} else {
									$semester_id = $query->fetch()['semester_id'];
								}

								//									print_r($course_code_arr); echo '<br>';

								foreach ($course_code_arr as $final_array) {
									$course_code = $final_array['course_code'];
									//										print_r($final_array); echo '<br>';

									$sql = "SELECT course_id FROM courses WHERE course_code=:course_code AND year=:year AND sem_code=:sem_code";
									$query = $conn->prepare($sql);
									$query->bindParam(':course_code', $course_code);
									$query->bindParam(':year', $year);
									$query->bindParam(':sem_code', $sem_code);
									$query->execute();

									$course_id = $query->fetch()['course_id'];

									if(!$course_id)
																			echo "$course_code $course_id<br>";

									$sql = "INSERT INTO `sem_structure` (`sem_id`, `course_id`, `status_value_id`, `log_id`) VALUES ($semester_id, '$course_id', '$status_id', '$log_id');";
									$ac_on = "Entered a course=$course_id against semester=$semester_id.";
									$s_i = $_SESSION['staff_id'];
									$r = $_SESSION['rank'];
									$tn = 'sem_structure';
									$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

									$sql = "INSERT INTO `sem_structure` (`sem_id`, `course_id`, `status_value_id`, `log_id`) VALUES (:semester_id, :course_id, :status_id, :log_id)";
									$query = $conn->prepare($sql);

									$query->bindParam(':semester_id', $semester_id);
									$query->bindParam(':course_id', $course_id);
									$query->bindParam(':status_id', $status_id);
									$query->bindParam(':log_id', $log_id);
									$query->execute();

								}
								$conn->commit();
								$success .= "Success adding courses for " .  $course_code_arr[0]['semester_title'] . ".\n";
							}
							catch (PDOException $e) {
								$conn->rollBack();
								echo $e->getMessage();
								$error .= "An error occured on adding courses for semester " .  $course_code_arr[0]['semester_title'] . ". Please check your input file.\n";
							}
						}
					}
				}

			}
		}
	} else {
		$error .= "Files don't exist.<br>";
	}
}

$TBS->MergeBlock('courseBlk', $courses);
$TBS->MergeBlock('coursesNameBlk', $coursesname);
$TBS->MergeBlock('program', $conn, 'SELECT * FROM program');
$TBS->MergeBlock('sem_code', $conn, 'SELECT * FROM sem_code_description');

$TBS->Show();

?>
