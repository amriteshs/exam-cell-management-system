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

		$student_id = ecell_get_val('student', 'enrollment_no', $enrollment_no, 'student_id');
		if (!$student_id) {
			$msg_err .= "The student doesn't exist.\n";
		}

		if (!$msg_err) {
			$check_program = ecell_get_val('student', 'enrollment_no', $enrollment_no, 'program_id');
			if ($check_program != $program_id) {
				$msg_err .= "The student doesn't belong to the given program.\n";
			}		
		}

		if (!$msg_err) {
			try {
			
				$sql = "INSERT INTO `branch_change_history`
	  			(`branch_change_history_id`, `log_id`, `program_id`, `roll_no`, `student_id`) 
	  			VALUES (NULL,:log_id, :program_id, :new_enrollment_no, :student_id)";

				$ac_on = "Changed roll no of ".$enrollment_no." to ".$new_enrollment_no;
	            $s_i = $_SESSION['staff_id'];
	            $r = $_SESSION['rank'];
	           	$tn = 'branch_change_history';

	           	$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);				
	              
	  			$sql = "INSERT INTO `branch_change_history`
	  			(`branch_change_history_id`, `log_id`, `program_id`, `roll_no`, `student_id`) 
	  			VALUES (NULL,:log_id, :program_id, :new_enrollment_no, :student_id)";
			    
	  			$stmt = $conn->prepare($sql);

	  			$stmt->bindParam(':log_id', $log_id);
	  			$stmt->bindParam(':program_id', $program_id);
	  			$stmt->bindParam(':new_enrollment_no', $new_enrollment_no);
	  			$stmt->bindParam(':student_id', $student_id);

			   	$stmt->execute();
				
			   	$sql = "UPDATE `student` SET `enrollment_no`=:new_enrollment_no, `program_id`=:program_id WHERE `student_id`=:student_id";

			   	$ac_no = "Changed branch for $enrollment_no. The new roll no is $new_enrollment_no.";
			   	$s_i = $_SESSION['staff_id'];
			   	$r = $_SESSION['rank'];
			   	$tn = 'student';

			   	$stmt = $conn->prepare($sql);

			   	$stmt->bindParam(':new_enrollment_no', $new_enrollment_no);
			   	$stmt->bindParam(':program_id', $program_id);
			   	$stmt->bindParam(':student_id', $student_id);

			   	$stmt->execute();

			   	$show_form = "";
				$success = "Success";

			} catch (PDOException $e) {
			    echo $sql . "<br>" . $e->getMessage();
			}
		}

	}

	$TBS->MergeBlock('program', $conn, 'SELECT * FROM program');

	$TBS->Show();

?>
