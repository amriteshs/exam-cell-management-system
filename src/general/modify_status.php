<?php
	include_once('../../includes/include.php');

	error_reporting(E_ALL); ini_set('display_errors', 1);
	error_reporting(E_ERROR | E_PARSE);

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('modify_status.html');

	$success = '';
	$show_up = 'y';
	$show_down = '';
	$msg_err = '';

	if (isset($_GET['stat'])) {
		$status = $_GET['stat'];

		if ($status == 'board_status') {
			$TBS->LoadTemplate('modify_status_board.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['board_id']) && filter_var($_POST['board_id'], FILTER_VALIDATE_INT)) {
					$board_id = $_POST['board_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'board';

				$attribute_list = array(
						'board_id' => $board_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'campus_status') {
			$TBS->LoadTemplate('modify_status_campus.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['campus_id']) && filter_var($_POST['campus_id'], FILTER_VALIDATE_INT)) {
					$campus_id = $_POST['campus_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'campus';

				$attribute_list = array(
						'campus_id' => $campus_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'copied_semesters_status') {
			$TBS->LoadTemplate('modify_status_copied_semesters.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
					$program_id = $_POST['program_id'];
				}

				if (isset($_POST['year_of_joining']) && filter_var($_POST['year_of_joining'], FILTER_VALIDATE_INT)) {
					$year_of_joining = $_POST['year_of_joining'];
				}

				if (isset($_POST['sem_code_of_joining']) && filter_var($_POST['sem_code_of_joining'], FILTER_VALIDATE_INT)) {
					$sem_code_of_joining = $_POST['sem_code_of_joining'];
				}

				if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
					$year = $_POST['year'];
				}

				if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
					$sem_code = $_POST['sem_code'];
				}

				if (isset($_POST['program_id_1']) && filter_var($_POST['program_id_1'], FILTER_VALIDATE_INT)) {
					$program_id_1 = $_POST['program_id_1'];
				}

				if (isset($_POST['year_of_joining_1']) && filter_var($_POST['year_of_joining_1'], FILTER_VALIDATE_INT)) {
					$year_of_joining_1 = $_POST['year_of_joining_1'];
				}

				if (isset($_POST['sem_code_of_joining_1']) && filter_var($_POST['sem_code_of_joining_1'], FILTER_VALIDATE_INT)) {
					$sem_code_of_joining_1 = $_POST['sem_code_of_joining_1'];
				}

				if (isset($_POST['year_1']) && filter_var($_POST['year_1'], FILTER_VALIDATE_INT)) {
					$year_1 = $_POST['year_1'];
				}

				if (isset($_POST['sem_code_1']) && filter_var($_POST['sem_code_1'], FILTER_VALIDATE_INT)) {
					$sem_code_1 = $_POST['sem_code_1'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$sql = "SELECT sem_id FROM course_structure WHERE program_id = :program_id AND year_of_joining = :year_of_joining AND sem_code_of_joining = :sem_code_of_joining AND sem_id_year = :year AND sem_id_sem_code = :sem_code";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':program_id', $program_id);
				$stmt->bindParam(':year_of_joining', $year_of_joining);
				$stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
				$stmt->bindParam(':year', $year);
				$stmt->bindParam(':sem_code', $sem_code);
				$stmt->execute();
				$original_sem_id = $stmt->fetchAll()[0]['sem_id'];

				$sql = "SELECT sem_id FROM course_structure WHERE program_id = :program_id_1 AND year_of_joining = :year_of_joining_1 AND sem_code_of_joining = :sem_code_of_joining_1 AND sem_id_year = :year_1 AND sem_id_sem_code = :sem_code_1";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':program_id_1', $program_id_1);
				$stmt->bindParam(':year_of_joining_1', $year_of_joining_1);
				$stmt->bindParam(':sem_code_of_joining_1', $sem_code_of_joining_1);
				$stmt->bindParam(':year_1', $year_1);
				$stmt->bindParam(':sem_code_1', $sem_code_1);
				$stmt->execute();
				$copy_sem_id = $stmt->fetchAll()[0]['sem_id'];

				$table_name = 'copied_semesters';

				$attribute_list = array(
						'original_sem_id' => $original_sem_id,
						'copy_sem_id' => $copy_sem_id
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'courses_status') {
			$TBS->LoadTemplate('modify_status_courses.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['course_code'])) {
					$course_code = $_POST['course_code'];
				}

				if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
					$year = $_POST['year'];
				}

				if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
					$sem_code = $_POST['sem_code'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				if (isset($_POST['submit_up'])) {
					$_SESSION['temp_year'] = $year;
					$_SESSION['temp_sem_code'] = $sem_code;

					$sql = "SELECT * FROM courses WHERE sem_code = :sem_code AND year = :year";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->bindParam(':year', $year);
					$stmt->execute();
					$course_list = $stmt->fetchAll();

					if (empty($course_list)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
						$show_up = 'y';
						$show_down = '';
					} else {
						$TBS->MergeBlock('course', $conn, "SELECT * FROM courses WHERE sem_code = '$sem_code' AND year = '$year' ORDER BY course_code");
						$show_up = '';
						$show_down = 'y';
					}
				}

				if (isset($_POST['submit_down'])) {
					$year = $_SESSION['temp_year'];
					$sem_code = $_SESSION['temp_sem_code'];

					$table_name = 'courses';

					$attribute_list = array(
							'course_code' => $course_code,
							'year' => $year,
							'sem_code' => $sem_code
						);

					$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

					if (is_null($status_value_id)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
					} else {
						$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
						$stmt = $conn->prepare($sql);
						$stmt->bindParam(':status_value_id', $status_value_id);
						$stmt->execute();
						$old_status_id = $stmt->fetchAll()[0]['status_id'];

						if ($old_status_id == $new_status_id) {
							$msg_err = 'New status is equal to old status. Please enter a valid status.';
						} else {
							ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

							$success = 'Status updated successfully.';
						}
					}
				}
			}
		} else if ($status == 'course_registration_status') {
			$TBS->LoadTemplate('modify_status_course_registration.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['enrollment_no']) && ecell_is_alpha_num($_POST['enrollment_no'])) {
					$enrollment_no = strtoupper($_POST['enrollment_no']);
				}

				if (isset($_POST['course_code'])) {
					$course_code = $_POST['course_code'];
				}

				if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
					$year = $_POST['year'];
				}

				if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
					$sem_code = $_POST['sem_code'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				if (isset($_POST['submit_up'])) {
					$_SESSION['temp_year'] = $year;
					$_SESSION['temp_sem_code'] = $sem_code;

					$sql = "SELECT * FROM courses WHERE sem_code = :sem_code AND year = :year";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->bindParam(':year', $year);
					$stmt->execute();
					$course_list = $stmt->fetchAll();

					if (empty($course_list)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
						$show_up = 'y';
						$show_down = '';
					} else {
						$TBS->MergeBlock('course', $conn, "SELECT * FROM courses WHERE sem_code = '$sem_code' AND year = '$year' ORDER BY course_code");
						$show_up = '';
						$show_down = 'y';
					}
				}

				if (isset($_POST['submit_down'])) {
					$year = $_SESSION['temp_year'];
					$sem_code = $_SESSION['temp_sem_code'];

					$student_details = ecell_student_details($conn, $enrollment_no);
					$student_id = $student_details['student_id'];

					$sql = "SELECT course_id FROM courses WHERE course_code = :course_code AND year = :year AND sem_code = :sem_code";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':course_code', $course_code);
					$stmt->bindParam(':year', $year);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->execute();
					$course_id = $stmt->fetchAll()[0]['course_id'];

					$table_name = 'course_registration';

					$attribute_list = array(
							'student_id' => $student_id,
							'course_id' => $course_id
						);

					$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

					if (is_null($status_value_id)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
					} else {
						$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
						$stmt = $conn->prepare($sql);
						$stmt->bindParam(':status_value_id', $status_value_id);
						$stmt->execute();
						$old_status_id = $stmt->fetchAll()[0]['status_id'];

						if ($old_status_id == $new_status_id) {
							$msg_err = 'New status is equal to old status. Please enter a valid status.';
						} else {
							ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

							$success = 'Status updated successfully.';
						}
					}
				}
			}
		} else if ($status == 'course_structure_status') {
			$TBS->LoadTemplate('modify_status_course_structure.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
					$program_id = $_POST['program_id'];
				}

				if (isset($_POST['year_of_joining']) && filter_var($_POST['year_of_joining'], FILTER_VALIDATE_INT)) {
					$year_of_joining = $_POST['year_of_joining'];
				}

				if (isset($_POST['sem_code_of_joining']) && filter_var($_POST['sem_code_of_joining'], FILTER_VALIDATE_INT)) {
					$sem_code_of_joining = $_POST['sem_code_of_joining'];
				}

				if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
					$year = $_POST['year'];
				}

				if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
					$sem_code = $_POST['sem_code'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'course_structure';

				$attribute_list = array(
						'program_id' => $program_id,
						'year_of_joining' => $year_of_joining,
						'sem_code_of_joining' => $sem_code_of_joining,
						'sem_id_year' => $year,
						'sem_id_sem_code' => $sem_code
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'course_type_status') {
			$TBS->LoadTemplate('modify_status_course_type.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['course_type_id']) && filter_var($_POST['course_type_id'], FILTER_VALIDATE_INT)) {
					$course_type_id = $_POST['course_type_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'course_type';

				$attribute_list = array(
						'course_type_id' => $course_type_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'exam_type_status') {
			$TBS->LoadTemplate('modify_status_exam_type.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['exam_type_id']) && filter_var($_POST['exam_type_id'], FILTER_VALIDATE_INT)) {
					$exam_type_id = $_POST['exam_type_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'exam_type';

				$attribute_list = array(
						'exam_type_id' => $exam_type_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'faculty_status') {
			$TBS->LoadTemplate('modify_status_faculty.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['faculty_id']) && filter_var($_POST['faculty_id'], FILTER_VALIDATE_INT)) {
					$faculty_id = $_POST['faculty_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'faculty';

				$attribute_list = array(
						'faculty_id' => $faculty_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'faculty_course_status') {
			$TBS->LoadTemplate('modify_status_faculty_course.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['faculty_id']) && filter_var($_POST['faculty_id'], FILTER_VALIDATE_INT)) {
					$faculty_id = $_POST['faculty_id'];
				}

				if (isset($_POST['course_code'])) {
					$course_code = $_POST['course_code'];
				}

				if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
					$year = $_POST['year'];
				}

				if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
					$sem_code = $_POST['sem_code'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				if (isset($_POST['submit_up'])) {
					$_SESSION['temp_year'] = $year;
					$_SESSION['temp_sem_code'] = $sem_code;

					$sql = "SELECT * FROM courses WHERE sem_code = :sem_code AND year = :year";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->bindParam(':year', $year);
					$stmt->execute();
					$course_list = $stmt->fetchAll();

					if (empty($course_list)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
						$show_up = 'y';
						$show_down = '';
					} else {
						$TBS->MergeBlock('course', $conn, "SELECT * FROM courses WHERE sem_code = '$sem_code' AND year = '$year' ORDER BY course_code");
						$show_up = '';
						$show_down = 'y';
					}
				}

				if (isset($_POST['submit_down'])) {
					$year = $_SESSION['temp_year'];
					$sem_code = $_SESSION['temp_sem_code'];

					$sql = "SELECT course_id FROM courses WHERE course_code = :course_code AND year = :year AND sem_code = :sem_code";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':course_code', $course_code);
					$stmt->bindParam(':year', $year);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->execute();
					$course_id = $stmt->fetchAll()[0]['course_id'];

					$table_name = 'faculty_course';

					$attribute_list = array(
							'faculty_id' => $faculty_id,
							'course_id' => $course_id
						);

					$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

					if (is_null($status_value_id)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
					} else {
						$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
						$stmt = $conn->prepare($sql);
						$stmt->bindParam(':status_value_id', $status_value_id);
						$stmt->execute();
						$old_status_id = $stmt->fetchAll()[0]['status_id'];

						if ($old_status_id == $new_status_id) {
							$msg_err = 'New status is equal to old status. Please enter a valid status.';
						} else {
							ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

							$success = 'Status updated successfully.';
						}
					}
				}
			}
		} else if ($status == 'grades_status') {
			$TBS->LoadTemplate('modify_status_grades.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['version_no']) && filter_var($_POST['version_no'], FILTER_VALIDATE_INT)) {
					$version_no = $_POST['version_no'];
				}

				if (isset($_POST['grade'])) {
					$grade = $_POST['grade'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				if (isset($_POST['submit_up'])) {
					$_SESSION['temp_version_no'] = $version_no;

					$sql = "SELECT * FROM grades WHERE version_no = :version_no";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':version_no', $version_no);
					$stmt->execute();
					$grade_list = $stmt->fetchAll();

					if (empty($grade_list)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
						$show_up = 'y';
						$show_down = '';
					} else {
						$TBS->MergeBlock('grade', $conn, "SELECT * FROM grades WHERE version_no = '$version_no' ORDER BY credit DESC");
						$show_up = '';
						$show_down = 'y';
					}
				}

				if (isset($_POST['submit_down'])) {
					$version_no = $_SESSION['temp_version_no'];

					$table_name = 'grades';

					$attribute_list = array(
							'grade' => $grade,
							'version_no' => $version_no
						);

					$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

					if (is_null($status_value_id)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
					} else {
						$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
						$stmt = $conn->prepare($sql);
						$stmt->bindParam(':status_value_id', $status_value_id);
						$stmt->execute();
						$old_status_id = $stmt->fetchAll()[0]['status_id'];

						if ($old_status_id == $new_status_id) {
							$msg_err = 'New status is equal to old status. Please enter a valid status.';
						} else {
							ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

							$success = 'Status updated successfully.';
						}
					}
				}
			}
		} else if ($status == 'old_branch_status') {
			$TBS->LoadTemplate('modify_status_old_branch.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['enrollment_no']) && ecell_is_alpha_num($_POST['enrollment_no'])) {
					$enrollment_no = strtoupper($_POST['enrollment_no']);
				}

				if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
					$program_id = $_POST['program_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$student_details = ecell_student_details($conn, $enrollment_no);
				$student_id = $student_details['student_id'];

				$table_name = 'old_branch';

				$attribute_list = array(
						'student_id' => $student_id,
						'program_id' => $program_id
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'program_status') {
			$TBS->LoadTemplate('modify_status_program.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
					$program_id = $_POST['program_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'program';

				$attribute_list = array(
						'program_id' => $program_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'religion_status') {
			$TBS->LoadTemplate('modify_status_religion.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['religion_id']) && filter_var($_POST['religion_id'], FILTER_VALIDATE_INT)) {
					$religion_id = $_POST['religion_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'religion';

				$attribute_list = array(
						'religion_id' => $religion_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'results_status') {
			$TBS->LoadTemplate('modify_status_results.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['enrollment_no']) && ecell_is_alpha_num($_POST['enrollment_no'])) {
					$enrollment_no = strtoupper($_POST['enrollment_no']);
				}

				if (isset($_POST['course_code'])) {
					$course_code = $_POST['course_code'];
				}

				if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
					$year = $_POST['year'];
				}

				if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
					$sem_code = $_POST['sem_code'];
				}

				if (isset($_POST['exam_type'])) {
					$exam_type = $_POST['exam_type'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				if (isset($_POST['submit_up'])) {
					$_SESSION['temp_year'] = $year;
					$_SESSION['temp_sem_code'] = $sem_code;

					$sql = "SELECT * FROM courses WHERE sem_code = :sem_code AND year = :year";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->bindParam(':year', $year);
					$stmt->execute();
					$course_list = $stmt->fetchAll();

					if (empty($course_list)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
						$show_up = 'y';
						$show_down = '';
					} else {
						$TBS->MergeBlock('course', $conn, "SELECT * FROM courses WHERE sem_code = '$sem_code' AND year = '$year' ORDER BY course_code");
						$show_up = '';
						$show_down = 'y';
					}
				}

				if (isset($_POST['submit_down'])) {
					$year = $_SESSION['temp_year'];
					$sem_code = $_SESSION['temp_sem_code'];

					$sql = "SELECT course_id FROM courses WHERE course_code = :course_code AND sem_code = :sem_code AND year = :year";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':course_code', $course_code);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->bindParam(':year', $year);
					$stmt->execute();
					$course_id = $stmt->fetchAll()[0]['course_id'];

					$sql = "SELECT `timestamp` FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = :exam_type ORDER BY `timestamp` DESC LIMIT 1";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':student_id', $student_id);
					$stmt->bindParam(':course_id', $course_id);
					$stmt->bindParam(':exam_type', $exam_type);
					$stmt->execute();
					$timestamp = $stmt->fetchAll()[0]['timestamp'];

					$table_name = 'results';

					$attribute_list = array(
							'student_id' => $student_id,
							'course_id' => $course_id,
							'exam_type' => $exam_type,
							'timestamp' => $timestamp
						);

					$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

					if (is_null($status_value_id)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
					} else {
						$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
						$stmt = $conn->prepare($sql);
						$stmt->bindParam(':status_value_id', $status_value_id);
						$stmt->execute();
						$old_status_id = $stmt->fetchAll()[0]['status_id'];

						if ($old_status_id == $new_status_id) {
							$msg_err = 'New status is equal to old status. Please enter a valid status.';
						} else {
							ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

							$success = 'Status updated successfully.';
						}
					}
				}
			}
		} else if ($status == 'sem_code_description_status') {
			$TBS->LoadTemplate('modify_status_sem_code_description.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['sem_code_id']) && filter_var($_POST['sem_code_id'], FILTER_VALIDATE_INT)) {
					$sem_code_id = $_POST['sem_code_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'sem_code_description';

				$attribute_list = array(
						'sem_code_id' => $sem_code_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'sem_structure_status') {
			$TBS->LoadTemplate('modify_status_sem_structure.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
					$program_id = $_POST['program_id'];
				}

				if (isset($_POST['course_code'])) {
					$course_code = $_POST['course_code'];
				}

				if (isset($_POST['year_of_joining']) && filter_var($_POST['year_of_joining'], FILTER_VALIDATE_INT)) {
					$year_of_joining = $_POST['year_of_joining'];
				}

				if (isset($_POST['sem_code_of_joining']) && filter_var($_POST['sem_code_of_joining'], FILTER_VALIDATE_INT)) {
					$sem_code_of_joining = $_POST['sem_code_of_joining'];
				}

				if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
					$year = $_POST['year'];
				}

				if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
					$sem_code = $_POST['sem_code'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				if (isset($_POST['submit_up'])) {
					$_SESSION['temp_year'] = $year;
					$_SESSION['temp_sem_code'] = $sem_code;

					$sql = "SELECT * FROM courses WHERE sem_code = :sem_code AND year = :year";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->bindParam(':year', $year);
					$stmt->execute();
					$course_list = $stmt->fetchAll();

					if (empty($course_list)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
						$show_up = 'y';
						$show_down = '';
					} else {
						$TBS->MergeBlock('course', $conn, "SELECT * FROM courses WHERE sem_code = '$sem_code' AND year = '$year' ORDER BY course_code");
						$show_up = '';
						$show_down = 'y';
					}
				}

				if (isset($_POST['submit_down'])) {
					$year = $_SESSION['temp_year'];
					$sem_code = $_SESSION['temp_sem_code'];

					$sql = "SELECT sem_id FROM course_structure WHERE program_id = :program_id AND sem_code_of_joining = :sem_code_of_joining AND year_of_joining = :year_of_joining AND sem_id_sem_code = :sem_code AND sem_id_year = :year";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':program_id', $program_id);
					$stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
					$stmt->bindParam(':year_of_joining', $year_of_joining);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->bindParam(':year', $year);
					$stmt->execute();
					$sem_id = $stmt->fetchAll()[0]['sem_id'];

					$sql = "SELECT course_id FROM courses WHERE course_code = :course_code AND sem_code = :sem_code AND year = :year";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':course_code', $course_code);
					$stmt->bindParam(':sem_code', $sem_code);
					$stmt->bindParam(':year', $year);
					$stmt->execute();
					$course_id = $stmt->fetchAll()[0]['course_id'];

					$table_name = 'sem_structure';

					$attribute_list = array(
							'sem_id' => $sem_id,
							'course_id' => $course_id
						);

					$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

					if (is_null($status_value_id)) {
						$msg_err = 'Data does not exist. Please enter valid data.';
					} else {
						$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
						$stmt = $conn->prepare($sql);
						$stmt->bindParam(':status_value_id', $status_value_id);
						$stmt->execute();
						$old_status_id = $stmt->fetchAll()[0]['status_id'];

						if ($old_status_id == $new_status_id) {
							$msg_err = 'New status is equal to old status. Please enter a valid status.';
						} else {
							ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

							$success = 'Status updated successfully.';
						}
					}
				}
			}
		} else if ($status == 'staff_status') {
			$TBS->LoadTemplate('modify_status_staff.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['username'])) {
					$username = $_POST['username'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'staff';

				$attribute_list = array(
						'username' => $username,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'staff_designation_status') {
			$TBS->LoadTemplate('modify_status_staff_designation.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['designation_id']) && filter_var($_POST['designation_id'], FILTER_VALIDATE_INT)) {
					$designation_id = $_POST['designation_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'staff_designation';

				$attribute_list = array(
						'designation_id' => $designation_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'staff_documents_status') {
			$TBS->LoadTemplate('modify_status_staff_documents.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['username'])) {
					$username = $_POST['username'];
				}

				if (isset($_POST['document_type'])) {
					$document_type = $_POST['document_type'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$sql = "SELECT staff_id FROM staff WHERE username = :username";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':username', $username);
				$stmt->execute();
				$staff_id = $stmt->fetchAll()[0]['staff_id'];

				$table_name = 'staff_documents';

				$attribute_list = array(
						'staff_id' => $staff_id,
						'category' => $document_type
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'state_status') {
			$TBS->LoadTemplate('modify_status_state.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['state_id']) && filter_var($_POST['state_id'], FILTER_VALIDATE_INT)) {
					$state_id = $_POST['state_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'state';

				$attribute_list = array(
						'state_id' => $state_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'student_status') {
			$TBS->LoadTemplate('modify_status_student.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['enrollment_no']) && ecell_is_alpha_num($_POST['enrollment_no'])) {
					$enrollment_no = strtoupper($_POST['enrollment_no']);
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'student';

				$attribute_list = array(
						'enrollment_no' => $enrollment_no,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'student_category_status') {
			$TBS->LoadTemplate('modify_status_student_category.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['category_id']) && filter_var($_POST['category_id'], FILTER_VALIDATE_INT)) {
					$category_id = $_POST['category_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'student_category';

				$attribute_list = array(
						'category_id' => $category_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'student_documents_status') {
			$TBS->LoadTemplate('modify_status_student_documents.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['enrollment_no']) && ecell_is_alpha_num($_POST['enrollment_no'])) {
					$enrollment_no = strtoupper($_POST['enrollment_no']);
				}

				if (isset($_POST['document_type'])) {
					$document_type = $_POST['document_type'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$student_details = ecell_student_details($conn, $enrollment_no);
				$student_id = $student_details['student_id'];

				$table_name = 'student_documents';

				$attribute_list = array(
						'student_id' => $student_id,
						'category' => $document_type
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'temp_results_status') {
			$TBS->LoadTemplate('modify_status_temp_results.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['temp_results_id']) && filter_var($_POST['temp_results_id'], FILTER_VALIDATE_INT)) {
					$temp_results_id = $_POST['temp_results_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'temp_results';

				$attribute_list = array(
						'temp_results_id' => $temp_results_id
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		} else if ($status == 'universities_status') {
			$TBS->LoadTemplate('modify_status_universities.html');

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach ($_POST as $k => $v) {      
					if (isset($_POST[$k])) {
						$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
					}   
				}

				if (isset($_POST['university_id']) && filter_var($_POST['university_id'], FILTER_VALIDATE_INT)) {
					$university_id = $_POST['university_id'];
				}

				if (isset($_POST['new_status_id']) && filter_var($_POST['new_status_id'], FILTER_VALIDATE_INT)) {
					$new_status_id = $_POST['new_status_id'];
				}

				$table_name = 'universities';

				$attribute_list = array(
						'university_id' => $university_id,
					);

				$status_value_id = ecell_get_status_value_id($conn, $table_name, $attribute_list);

				if (is_null($status_value_id)) {
					$msg_err = 'Data does not exist. Please enter valid data.';
				} else {
					$sql = "SELECT status_id FROM status_value WHERE status_value_id = :status_value_id";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':status_value_id', $status_value_id);
					$stmt->execute();
					$old_status_id = $stmt->fetchAll()[0]['status_id'];

					if ($old_status_id == $new_status_id) {
						$msg_err = 'New status is equal to old status. Please enter a valid status.';
					} else {
						ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id);

						$success = 'Status updated successfully.';
					}
				}
			}
		}
	}

	$TBS->MergeBlock('status', $conn, 'SELECT * FROM status ORDER BY status_name');
	$TBS->MergeBlock('board', $conn, 'SELECT * FROM board ORDER BY board_name');
	$TBS->MergeBlock('campus', $conn, 'SELECT * FROM campus ORDER BY campus_name');
	$TBS->MergeBlock('course_type', $conn, 'SELECT * FROM course_type ORDER BY course_type_description');
	$TBS->MergeBlock('exam_type', $conn, 'SELECT * FROM exam_type ORDER BY exam_type');
	$TBS->MergeBlock('faculty', $conn, 'SELECT * FROM faculty ORDER BY abbreviation');
	$TBS->MergeBlock('version', $conn, 'SELECT DISTINCT version_no FROM grades ORDER BY version_no DESC');
	$TBS->MergeBlock('program, program_1', $conn, 'SELECT * FROM program ORDER BY program_name');
	$TBS->MergeBlock('religion', $conn, 'SELECT * FROM religion ORDER BY religion_name');
	$TBS->MergeBlock('designation', $conn, 'SELECT * FROM staff_designation ORDER BY designation');
	$TBS->MergeBlock('sem_code, sem_code_1, sem_code_2, sem_code_3', $conn, 'SELECT * FROM sem_code_description ORDER BY title');
	$TBS->MergeBlock('staff_documents', $conn, 'SELECT DISTINCT category FROM staff_documents ORDER BY category');
	$TBS->MergeBlock('state', $conn, 'SELECT * FROM state ORDER BY state_name');
	$TBS->MergeBlock('category', $conn, 'SELECT * FROM student_category ORDER BY category_name');
	$TBS->MergeBlock('student_documents', $conn, 'SELECT DISTINCT category FROM student_documents ORDER BY category');
	$TBS->MergeBlock('university', $conn, 'SELECT * FROM universities ORDER BY university_name');

	$TBS->Show();
?>