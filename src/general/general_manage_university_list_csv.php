<?php

// error_reporting(E_ALL); ini_set('display_errors', 1);

include_once('../../includes/include.php');
include_once('../../includes/session.php');
if(!ecell_sec_session_start()) {
	header("Location: includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('general_manage_university_list_csv.html');

$success = "";
$show_form = "y";
$msg_err = "";

if (isset($_FILES["file"]["error"]) && isset($_FILES['file']['tmp_name'])) {
	if ($_FILES["file"]["error"] > 0) {
		$msg_err = "File not found. Please upload the file again.";
	} else {

		$fp = fopen($_FILES['file']['tmp_name'], 'rb');

		$cnt = 0;

		while (($line = fgets($fp)) !== false) {

			if ($cnt == 0) {
				$cnt++;
				continue;
			}

			$line = str_replace('\n','',$line);
			$line = explode(';', $line);

			$university_name = (trim($line[1]));
			$state_name = (trim($line[2]));

			$sql = "INSERT INTO `universities`(`university_id`, `university_name`, `state_id`, `status_value_id`, `log_id`) VALUES (NULL, :university_name, :state_id, :status_value_id, :log_id)";

			$ac_on = "Entered new university: $university_name.";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'universities';

			$log_id = 1;		

			$sql = "INSERT INTO `universities`(`university_id`, `university_name`, `state_id`, `status_value_id`, `log_id`) VALUES (NULL, :university_name, :state_id, :status_value_id, :log_id)";

			$stmt = $conn->prepare($sql);

			$status_value_id = 1;

			$stmt->bindParam(':university_name', $university_name);

			$state_id = ecell_get_val('state', 'state_name', $state_name, 'state_id');

			$stmt->bindParam(':state_id', $state_id);
			$stmt->bindParam(':status_value_id', $status_value_id);
			$stmt->bindParam(':log_id', $log_id);

			try {        
				$stmt->execute();
			} catch (PDOException $e) {
				echo $sql . "<br>" . $e->getMessage();
			} 

			$cnt++;

		}

		$success = "Universities uploaded successfully.";

	}

} 

$TBS->Show();

?>
