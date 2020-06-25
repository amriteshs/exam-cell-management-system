<?php

include_once('../../includes/include.php');
include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
	header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}

$show_val = "";

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('student_view_students.html'); 

$msg = "";

$TBS->MergeBlock('result', $conn, "select student_id, program.program_code as program_code, enrollment_no, first_name, middle_name, last_name, date_of_admission from student_original, program where program.program_id = student_original.program_id order by enrollment_no");

$TBS->Show();

?>