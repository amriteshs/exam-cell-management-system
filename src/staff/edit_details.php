<?php
include_once('../../includes/include.php');
include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
    header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
    header("Location: ../../includes/logout.php");
}

ecell_redirect_if_below('AR', '../home/home.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('edit_details.html');

$success = "";
$msg = "";

if (!isset($_POST)) {
    $_POST = &$HTTP_POST_VARS;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['staff_id']) &&  (trim($_POST['staff_id']) != '')) {
        $staff_id = $_POST['staff_id'];
    } else {
        $msg = "Please select a staff.";
    }

    if (isset($_POST['name']) && !empty($_POST['name']) && (trim($_POST['name']) != '')) {
        $new_staff_name = $_POST['name'];
        if (!preg_match("/^[a-zA-Z ]*$/", $new_staff_name)) {
            $msg = "Name can contain only letters and spaces";
        }
    } else {
        $msg = "Please enter a name.";
    }

    if ($msg == "") {

        $sql = "SELECT username, staff_name FROM staff WHERE staff_id=:staff_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        $old_staff_name = $result['staff_name'];

        $sql = "UPDATE staff SET staff_name = :staff_name WHERE staff_id = :staff_id;";

        $ac_on = "Updating name for $old_staff_name to $new_staff_name";
        $s_i = $_SESSION['staff_id'];
        $r = $_SESSION['rank'];
        $tn = "staff";
        $log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':staff_name', $new_staff_name, PDO::PARAM_STR);
        $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_STR);
        $stmt->execute();

        $success = "Details updates successfully $new_staff_name";

    }
}

$TBS->MergeBlock('staff', $conn, 'SELECT * FROM staff WHERE rank < ' . $_SESSION['rank']);
$TBS->Show();

?>
