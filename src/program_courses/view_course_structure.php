<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('view_course_structure.html');

$table = false;

if (isset($_POST["view"])) {

    $program_id = $_POST['program_id'];
    $year_of_joining = $_POST['year_of_joining'];
    $sem_code_of_joining = $_POST['sem_code_of_joining'];

    $sql = "SELECT sem_id_year, title, sem_title, course_code, course_name FROM course_structure, sem_structure, courses, sem_code_description WHERE course_structure.sem_id = sem_structure.sem_id AND courses.course_id = sem_structure.course_id AND sem_code_description.sem_code_id = course_structure.sem_id_sem_code AND program_id = :program_id AND year_of_joining=:year_of_joining AND sem_code_of_joining=:sem_code_of_joining  ORDER BY sem_id_year ASC, sem_id_sem_code ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':program_id', $program_id);
    $stmt->bindParam(':year_of_joining', $year_of_joining);
    $stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
    $result = $stmt->execute();

    $courses = $stmt->fetchAll();

    $TBS->MergeBlock('courses', $courses);
    $table = true;
    
}

$TBS->MergeBlock('prg', $conn, 'SELECT * FROM program');
$TBS->MergeBlock('semBlk, semBlk1', $conn, 'SELECT * FROM sem_code_description');

$TBS->Show();

?>
