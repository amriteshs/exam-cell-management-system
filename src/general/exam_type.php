<?php

include_once('../../includes/include.php');
include_once('../../includes/session.php');
if(!ecell_sec_session_start()) {
    header("Location: includes/logout.php");
}

if(!ecell_login_check($conn)) {
    header("Location: ../../includes/logout.php");
}
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('exam_type.html');

$success = "";
$msg_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach($_POST as $k=>$v) {
        if(isset($_POST[$k])) {
            $_POST[$k] = filter_var($v,FILTER_SANITIZE_STRING);
        }
    }

    if (isset($_POST['etype']) && !empty($_POST['etype']) && trim($_POST['etype'])) {
        $etype = ucwords(trim($_POST['etype']));
    } else {
        $msg_err .= "Please enter an exam type.\n";
    }


    $sql = "SELECT * FROM exam_type WHERE exam_type=:etype";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':etype', $etype);
    $stmt->execute();
    $result = $stmt->fetchAll();
    if ($stmt->rowCount() == 1) {
        $msg_err = "Exam Type: $etype already exists.";
    }

    if ($msg_err == "") {
        try {
            $status_id = ecell_get_status('on');
            $sql = "INSERT INTO `exam_type` (`exam_type_id`, `exam_type`, `status_value_id`, `log_id`) VALUES (NULL, :etype, :status_id, :log_id)";
            $ac_on = "Entered a exam type: $etype.";
            $s_i = $_SESSION['staff_id'];
            $r = $_SESSION['rank'];
            $tn = 'course_type';
            $log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':etype', $etype);
            $stmt->bindParam(':status_id', $status_id);
            $stmt->bindParam(':log_id', $log_id);

            $stmt->execute();

            $success = "Success";
        } catch (PDOException $e) {
            echo "<br>" . $e->getMessage();
        }
    }

}

$TBS->Show();

?>
