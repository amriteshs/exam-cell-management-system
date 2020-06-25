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
$TBS->LoadTemplate('reset_passwd.html'); 

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

	if (isset($_POST['password']) && isset($_POST['confirm_password']) && (trim($_POST['password']) != '') ) {
		if ($_POST['password'] == $_POST['confirm_password'] ) {
			$password = $_POST['password'];
			$password = crypt($password);
		} else {
			$msg = "Passwords do not match.";
		}
	} else {
		$msg = "Please enter valid password";
	}

	if ($msg == "") {

		$sql = "SELECT username, staff_name FROM staff WHERE staff_id=:staff_id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		$username = $result['username'];
		$staff_name = $result['staff_name'];

		$msghash = md5($_POST['password'], $username);

		if(isset($_SESSION['msghash']) && ($_SESSION['msghash'] == $msghash)){
			$msg = "Password already reseted for $username";
		} else {
			$sql = "UPDATE staff SET password = :password WHERE staff_id = :staff_id;";
			$ac_on = "Resetting password for $staff_name";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = "staff";
			$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

			$stmt = $conn->prepare($sql);

			$stmt->bindParam(':password', $password, PDO::PARAM_STR);       
			$stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_STR);
			$stmt->execute();

			$_SESSION['msghash'] = $msghash;
			$success = "Password successfully reset for $staff_name";
		}
	}
}

$TBS->MergeBlock('staff', $conn, 'SELECT * FROM staff WHERE rank < ' . $_SESSION['rank']);
$TBS->Show();

?>
