<?php

include_once('../../includes/include.php');

error_reporting(E_ALL); ini_set('display_errors', 1);

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('declare_results.html'); 

$success = "";
$show_form = "";
$msg_err = "";

$sql = "SELECT temp_results.temp_results_id as id, program.program_name as program_name, course_structure.year_of_joining as year_of_joining, sem_code_description.title as sem_code_of_joining, course_structure.sem_id_year as year, another_sem_code.title as sem_code FROM temp_results, program, course_structure, sem_code_description, sem_code_description as another_sem_code, status_value, status WHERE sem_code_description.sem_code_id=course_structure.sem_id_sem_code AND another_sem_code.sem_code_id=course_structure.sem_code_of_joining AND temp_results.sem_id=course_structure.sem_id AND course_structure.program_id=program.program_id AND temp_results.status_value_id=status_value.status_value_id AND status_value.status_id=status.status_id AND status.status_name='on'";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':status_value_id', $status_value_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
	$success = "No new results have been uploaded.";
} else {
	$show_form = "y";
}

$TBS->MergeBlock('blk', $conn, "SELECT temp_results.temp_results_id as id, program.program_name as program_name, course_structure.year_of_joining as year_of_joining, sem_code_description.title as sem_code_of_joining, course_structure.sem_id_year as year, another_sem_code.title as sem_code FROM temp_results, program, course_structure, sem_code_description, sem_code_description as another_sem_code, status_value, status WHERE sem_code_description.sem_code_id=course_structure.sem_id_sem_code AND another_sem_code.sem_code_id=course_structure.sem_code_of_joining AND temp_results.sem_id=course_structure.sem_id AND course_structure.program_id=program.program_id AND temp_results.status_value_id=status_value.status_value_id AND status_value.status_id=status.status_id AND status.status_name='on'");

$TBS->Show();

?>
