<?php

include_once('../../includes/include.php');
include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
	header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}

$sql = "SELECT program.program_name as program_name, enrollment_no, first_name, middle_name, last_name, comm_mobile_no, comm_email, dob from student, program where program.program_id = student.program_id order by enrollment_no;";

$stmt = $conn->prepare($sql);
$stmt->execute();
$student_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

$output = fopen("php://output",'w') or die("Can't open php://output");
header("Content-Type:application/csv"); 
header("Content-Disposition:attachment;filename=students.csv"); 
//fputcsv($output, array('id','name','description'));


foreach ($student_array as $list) {
	fputcsv($output, $list);
}

fclose($output);

?>
