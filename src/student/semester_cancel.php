<?php

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('semester_cancel.html');

if ($_SESSION['rank'] < $all_ranks['AR']) {
    header("Location: ../home/home.php");
}

$success = "";
$show_form = "y";
$msg_err = "";
$name = "";
$enroll = "";
$year = "";
$first = true;
$second = false;
$sem_title = "";

if (isset($_POST['sub1'])) {
    $enroll = $_POST['enroll'];
    $year = $_POST['year'];
    $sem_code = $_POST['sem_code'];

    $sql = "SELECT title FROM sem_code_description WHERE sem_code_id=:sem_code";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':sem_code', $sem_code);
    $stmt->execute();
    $sem_title = $stmt->fetch()['title'];

    $sql = "SELECT student_id, first_name, last_name FROM student WHERE enrollment_no=:enroll";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':enroll', $enroll);
    $stmt->execute();
    $res = $stmt->fetch();

    $student_id = $res['student_id'];
    $name = $res['first_name'] . ' ' . $res['last_name'];

    $_SESSION['sem_cancel'] = array(
        'enroll' => $enroll,
        'student_id' => $student_id,
        'year' => $year,
        'sem_code' => $sem_code,
        'sem_title' => $sem_title
    );

    $first = false;
    $second = true;
}

if (isset($_POST['sub_yes'])) {

    if (isset($_POST['password'])) {

        $sql = "SELECT password FROM staff WHERE username=:usernane";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usernane', $_SESSION['username']);
        $stmt->execute();
        $check_password = $stmt->fetch()['password'];

        if (strcmp($check_password, crypt($_POST['password'], $check_password)) == 0) {

            $sql = "INSERT INTO `sem_cancel`(`student_id`, `year`, `sem_code`, `status_value_id`, `log_id`) VALUES (:student_id, :year, :sem_code, :status_value_id, :log_id)";

            $ac_on = "Cancelling sem " . $_SESSION['sem_cancel']['year'] . " " . $_SESSION['sem_cancel']['sem_title'] . " for student " . $_SESSION['sem_cancel']['enroll'];
            $s_i = $_SESSION['staff_id'];
            $r = $_SESSION['rank'];
            $tn = 'sem_cancel';
            $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

            $status_value_id = ecell_get_status('on');

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':student_id', $_SESSION['sem_cancel']['student_id']);
            $stmt->bindParam(':year', $_SESSION['sem_cancel']['year']);
            $stmt->bindParam(':sem_code', $_SESSION['sem_cancel']['sem_code']);
            $stmt->bindParam(':status_value_id', $status_value_id);
            $stmt->bindParam(':log_id', $log_id);
            $stmt->execute();

            $success = "Successfully cancelled sem " . $_SESSION['sem_cancel']['sem_title'] . "-" . $_SESSION['sem_cancel']['year'] . " for student " . $_SESSION['sem_cancel']['enroll'];
        } else {
            $msg_err = "You entered wrong password.";
        }
    } else {
        $msg_err = "Please enter your password.";
    }
}

$TBS->MergeBlock('sem_code_description1', $conn, "SELECT * FROM sem_code_description, status WHERE status_name='on'");
$TBS->Show();

?>
