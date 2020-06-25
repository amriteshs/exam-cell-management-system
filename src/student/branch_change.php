<?php

	include_once('../../includes/include.php');
	
	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('branch_change.html'); 

	$success = "";
	$show_form = "y";
	$msg_err = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
		if (isset($_POST['program_id'])) {
			$program_id = $_POST['program_id'];
		}

		if (isset($_POST['student_roll_no'])) {
			$enrollment_no = $_POST['student_roll_no'];
		}
		
		if (isset($_POST['new_student_roll_no'])) {
			$new_enrollment_no = $_POST['new_student_roll_no'];
		}

		$check_new_enrollment_no = ecell_get_val('student', 'enrollment_no', $new_enrollment_no, 'student_id');
		if ($check_new_enrollment_no) {
			$msg_err .= "$new_enrollment_no already belongs to a student.";
		}

		$student_id = ecell_get_val('student', 'enrollment_no', $enrollment_no, 'student_id');
		if (!$student_id) {
			$msg_err .= "$enrollment_no doesn't exist.\n";
		}

		$old_program_id = ecell_get_val('student', 'enrollment_no', $enrollment_no, 'program_id');

		if (!$msg_err) {
			try {
				$status_value_id = ecell_get_status('on');

				$sql = "INSERT INTO `old_branch`(`branch_change_id`, `student_id`, `program_id`, `roll_no`, `status_value_id`, `log_id`) VALUES (NULL,:student_id,:program_id,:enrollment_no,:status_value_id,:log_id)";
				$ac_on = "Changed roll no of ".$enrollment_no." to ".$new_enrollment_no;
	            $s_i = $_SESSION['staff_id'];
	            $r = $_SESSION['rank'];
	           	$tn = 'branch_change_history';
	           	$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);				
	              
	  			$sql = "INSERT INTO `old_branch`(`branch_change_id`, `student_id`, `program_id`, `roll_no`, `status_value_id`, `log_id`) VALUES (NULL,:student_id,:program_id,:enrollment_no,:status_value_id,:log_id)";
	  			$stmt = $conn->prepare($sql);
	  			$stmt->bindParam(':student_id', $student_id);
	  			$stmt->bindParam(':program_id', $old_program_id);
	  			$stmt->bindParam(':enrollment_no', $enrollment_no);
	  			$stmt->bindParam(':status_value_id', $status_value_id);
	  			$stmt->bindParam(':log_id', $log_id);
			   	$stmt->execute();
				
			   	$sql = "UPDATE `student` SET `enrollment_no`=:new_enrollment_no, `program_id`=:program_id WHERE `student_id`=:student_id";
			   	$ac_on = "Changed branch for $enrollment_no. The new roll no is $new_enrollment_no.";
			   	$s_i = $_SESSION['staff_id'];
			   	$r = $_SESSION['rank'];
			   	$tn = 'student';
				$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

			   	$stmt = $conn->prepare($sql);
			   	$stmt->bindParam(':new_enrollment_no', $new_enrollment_no);
			   	$stmt->bindParam(':program_id', $program_id);
			   	$stmt->bindParam(':student_id', $student_id);

			   	$stmt->execute();

			   	$show_form = "";
				$program_name = ecell_get_val('program', 'program_id', $program_id, 'program_name');
				$success = "Successfully changed branch of $enrollment_no to $program_name. New Enrollment number is $new_enrollment_no";

			} catch (PDOException $e) {
			    //echo $sql . "<br>" . $e->getMessage();
				$msg_err = $e->getMessage();
			}
		}

	}

	$TBS->MergeBlock('program', $conn, 'SELECT * FROM program');

	$TBS->Show();

?>
