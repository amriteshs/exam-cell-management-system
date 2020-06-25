<?php

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('find_course_details.html');

$show_form = "y";
$msg_err=  "";
$show = "";

if (isset($_POST['submit'])) {

	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		foreach($_POST as $k=>$v) {
			if(isset($_POST[$k])) {
				$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
			}
		}	

		$_POST['course_code'] = strtoupper($_POST['course_code']);
		if (isset($_POST['course_code']) && preg_match('/^[A-Z0-9]{2,}$/', $_POST['course_code'])) {
			$course_code = $_POST['course_code'];
		} else {
			$msg_err .= "Course code should be a alphanumeric string(characters in CAPITAL) with no spaces in between.\n";
		}

	}

	$course_id = ecell_get_val('courses', 'course_code', $course_code, 'course_id');

	if ($course_id) {
		$show_form = "";
		$show = "y";
		$TBS->MergeBlock('result', $conn, "SELECT * FROM courses WHERE course_code='$course_code'");
	} else {
		$msg_err = "Such a course doesn't exist.";
	}


}

$TBS->MergeBlock('courses', $conn, 'SELECT * FROM courses');
$TBS->Show();

?>