<?php

include_once('../../includes/include.php');
include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
	header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('enroll_student.html'); 

$TBS->Show();

?>
