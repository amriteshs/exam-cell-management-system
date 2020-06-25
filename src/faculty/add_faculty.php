<?php

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('add_faculty.html');

$show_form = "y";
$success = "";
$msg_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach($_POST as $k=>$v) {
		if(isset($_POST[$k])) {
			$_POST[$k] = filter_var($v,FILTER_SANITIZE_STRING);
		}			
	}

	if (isset($_POST['faculty_name']) && preg_match('/^[A-Za-z., ]{2,}$/', $_POST['faculty_name'])) {
		$faculty_name = $_POST['faculty_name'];
	} else {
		$msg_err .= "Faculty name should contain only alphabets and spaces and '.' and ','.\n";
	}

	if (isset($_POST['faculty_abbr'])) {
		$_POST['faculty_abbr'] = strtoupper($_POST['faculty_abbr']);
	}

	if (isset($_POST['faculty_abbr']) && preg_match('/^[A-Z]{2,}$/', $_POST['faculty_abbr'])) {
		$faculty_abbr = $_POST['faculty_abbr'];
	} else {
		$msg_err .= "Faculty abbreviation should contain at least 2 capital alphabets and no spaces.\n";
	}

	$sql = "SELECT * FROM faculty WHERE abbreviation =:abbr";

	$stmt = $conn->prepare($sql);

	$stmt->bindParam(':abbr', $faculty_abbr);

	$stmt->execute();

	if ($stmt->rowCount() == 1) {
		$msg_err .= "Faculty has already been added.\n";
	}

	if (!$msg_err) {

		$status_id = ecell_get_status('on');

		try {

			$sql = "INSERT INTO `faculty` (`faculty_id`, `faculty_name`, `abbreviation`, `status_value_id`, `log_id`) VALUES (NULL, :faculty_name,:faculty_abbr,:status_id,:log_id)";

			$ac_on = "Registered new faculty ".$faculty_name." with abbreviation ".$faculty_abbr;
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'faculty';

			$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

			$sql = "INSERT INTO `faculty` (`faculty_id`, `faculty_name`, `abbreviation`, `status_value_id`, `log_id`) VALUES (NULL, :faculty_name,:faculty_abbr,:status_id,:log_id)";

			$stmt = $conn->prepare($sql);

			$stmt->bindParam(':faculty_name', $faculty_name);
			$stmt->bindParam(':faculty_abbr', $faculty_abbr);
			$stmt->bindParam(':status_id', $status_id);
			$stmt->bindParam(':log_id', $log_id);
			$stmt->execute();

			$show_form = "";
			$success = "Faculty successfully added.";

		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}
	}

}

$TBS->Show();

?>
