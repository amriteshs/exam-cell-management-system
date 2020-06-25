<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('copied_semester.html');

$form1 = true;
$form2 = false;
$form3 = false;

$success = "";
$error = '';
$table_heading = '';

if (isset($_POST["submit_1"])) {

    $_SESSION['copied_semester'] = [];

    $from_program_id = $_POST['from_program_id'];
    $year_of_joining = $_POST['year_of_joining'];
    $sem_code_of_joining = $_POST['sem_code_of_joining'];
    $to_program_id = $_POST['to_program_id'];

    $from_program_name = ecell_get_val('program', 'program_id', $from_program_id, 'program_name');
    $sem_of_joining = ecell_get_val('sem_code_description', 'sem_code_id', $sem_code_of_joining, 'title');

    $sql = "SELECT sem_id, sem_title FROM course_structure WHERE program_id = :program_id AND year_of_joining = :year_of_joining AND sem_code_of_joining = :sem_code_of_joining";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':program_id', $from_program_id);
    $stmt->bindParam(':year_of_joining', $year_of_joining);
    $stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
    $stmt->execute();

    if ($stmt->rowCount()) {
        $form1 = false;
        $form2 = true;
        $form3 = false;

        $semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $TBS->MergeBlock('semesters', $semesters);

        $_SESSION['copied_semester']['from_program_id'] = $from_program_id;
        $_SESSION['copied_semester']['to_program_id'] = $to_program_id;
        $_SESSION['copied_semester']['year_of_joining'] = $year_of_joining;
        $_SESSION['copied_semester']['sem_code_of_joining'] = $sem_code_of_joining;
    } else {
        $error = "$from_program_name has no semesters.";
    }

} else if (isset($_POST["submit_2"])) {

    $source_sem_id = $_POST['source_sem_id'];
    $sem_title = ecell_get_val('course_structure', 'sem_id', $source_sem_id, 'sem_title');

    $_SESSION['copied_semester']['source_sem_id'] = $source_sem_id;

    $sql = "SELECT course_code, course_name FROM sem_structure, courses WHERE sem_id = :sem_id AND sem_structure.course_id = courses.course_id ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':sem_id', $source_sem_id);
    $stmt->execute();

    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($stmt->rowCount()) {

        $table_heading = 'Following courses will be copied from program ' . ecell_get_val('program', 'program_id', $_SESSION['copied_semester']['from_program_id'], 'program_name') . ' to program ' . ecell_get_val('program', 'program_id', $_SESSION['copied_semester']['to_program_id'], 'program_name') . ' of ' . $_SESSION['copied_semester']['year_of_joining'] . ' batch as ' . $sem_title . '.';

        $form1 = false;
        $form2 = false;
        $form3 = true;

        $TBS->MergeBlock('courses', $courses);
    } else {
        $error = "This semester has no courses.";
    }

} else if (isset($_POST['submit_3'])) {


    $sql = "SELECT * FROM course_structure WHERE sem_id = :sem_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':sem_id', $_SESSION['copied_semester']['source_sem_id']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM `course_structure` WHERE `program_id` = :program_id AND `year_of_joining` = :year_of_joining AND `sem_code_of_joining` = :sem_code_of_joining AND `sem_id_year` = :sem_id_year AND `sem_id_sem_code` = :sem_id_sem_code";
    $stmt1 = $conn->prepare($sql);
    $stmt1->bindParam(':program_id', $_SESSION['copied_semester']['to_program_id']);
    $stmt1->bindParam(':year_of_joining', $_SESSION['copied_semester']['year_of_joining']);
    $stmt1->bindParam(':sem_code_of_joining', $_SESSION['copied_semester']['sem_code_of_joining']);
    $stmt1->bindParam(':sem_id_year', $result['sem_id_year']);
    $stmt1->bindParam(':sem_id_sem_code', $result['sem_id_sem_code']);
    $stmt1->execute();

//    echo "SELECT * FROM `course_structure` WHERE `program_id` = " . $_SESSION['copied_semester']['to_program_id'] .  " AND `year_of_joining` = " . $_SESSION['copied_semester']['year_of_joining'] ." AND `sem_code_of_joining` = " . $_SESSION['copied_semester']['sem_code_of_joining'] ." AND `sem_id_year` = " . $result['sem_id_year'] . " AND `sem_id_sem_code` = " . $result['sem_id_sem_code'];
    if ($stmt1->rowCount() == 0) {

        $sql = "INSERT INTO `course_structure` (`sem_id`, `program_id`, `sem_title`, `year_of_joining`, `sem_code_of_joining`, `sem_id_year`, `sem_id_sem_code`, `status_value_id`, `log_id`) VALUES (NULL, :program_id, :semester_title, :year_of_joining, :sem_code_of_joining, :year, :sem_code, :status_id, :log_id)";
        $ac_on = "Added a new semester for program=" . $_SESSION['copied_semester']['to_program_id'] . " AND batch_year=" . $_SESSION['copied_semester']['year_of_joining'] . " AND year=" . $result['sem_id_year'] . " AND sem_code=" . $result['sem_id_sem_code'] . " AND sem_code_of_joining=" . $_SESSION['copied_semester']['sem_code_of_joining'];
        $s_i = $_SESSION['staff_id'];
        $r = $_SESSION['rank'];
        $tn = 'course_structure';
        $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

        $status_id = ecell_get_status('on');

        $query = $conn->prepare($sql);
        $query->bindParam(':program_id', $_SESSION['copied_semester']['to_program_id']);
        $query->bindParam(':semester_title', $result['sem_title']);
        $query->bindParam(':year_of_joining', $_SESSION['copied_semester']['year_of_joining']);
        $query->bindParam(':sem_code_of_joining', $_SESSION['copied_semester']['sem_code_of_joining']);
        $query->bindParam(':year', $result['sem_id_year']);
        $query->bindParam(':sem_code', $result['sem_id_sem_code']);
        $query->bindParam(':status_id', $status_id);
        $query->bindParam(':log_id', $log_id);
        $query->execute();

        $sql = "SELECT MAX(sem_id) FROM course_structure";
        $query = $conn->prepare($sql);
        $query->execute();
        $des_sem_id = $query->fetch()[0];

        $src_sem_id = $_SESSION['copied_semester']['source_sem_id'];

        $sql = "SELECT course_id FROM sem_structure WHERE sem_id = :sem_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':sem_id', $src_sem_id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $status_id = ecell_get_status('on');

        foreach ($result as $r) {
            $course_id = $r['course_id'];

            $sql = "INSERT INTO `sem_structure` (`sem_id`, `course_id`, `status_value_id`, `log_id`) VALUES ($src_sem_id, '$course_id', '$status_id', '$log_id');";
            $ac_on = "Entered a course=$course_id against semester=$src_sem_id.";
            $s_i = $_SESSION['staff_id'];
            $r = $_SESSION['rank'];
            $tn = 'sem_structure';
            $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

            $sql = "INSERT INTO `sem_structure` (`sem_id`, `course_id`, `status_value_id`, `log_id`) VALUES (:semester_id, :course_id, :status_id, :log_id)";
            $query = $conn->prepare($sql);


            $query->bindParam(':semester_id', $des_sem_id);
            $query->bindParam(':course_id', $course_id);
            $query->bindParam(':status_id', $status_id);
            $query->bindParam(':log_id', $log_id);
            $query->execute();

        }

        $sql = "INSERT INTO `copied_semesters`(`original_sem_id`, `copy_sem_id`, `status_value_id`, `log_id`) VALUES ( :original_sem_id , :copy_sem_id , :status_value_id , :log_id )";
        $ac_on = "Copied a semester=$src_sem_id to semester=$des_sem_id.";
        $s_i = $_SESSION['staff_id'];
        $r = $_SESSION['rank'];
        $tn = 'copied_semesters';
        $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':original_sem_id', $src_sem_id);
        $stmt->bindParam(':copy_sem_id', $des_sem_id);
        $stmt->bindParam(':status_value_id', $status_id);
        $stmt->bindParam(':log_id', $log_id);
        $stmt->execute();

        $_SESSION['copied_semester'] = [];

        $success = "Semesters copied successfully.";
    } else {
        $error = "Cannot copy semester. Please use the CSV interface.";
    }
}


//$TBS->MergeBlock('courseBlk', $courses);
//$TBS->MergeBlock('coursesNameBlk', $coursesname);
$TBS->MergeBlock('program, programt', $conn, 'SELECT * FROM program');
$TBS->MergeBlock('sem_code', $conn, 'SELECT * FROM sem_code_description');

$TBS->Show();

?>
