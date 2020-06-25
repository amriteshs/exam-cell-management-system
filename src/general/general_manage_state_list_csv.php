<?php

error_reporting(E_ALL); ini_set('display_errors', 1);
include_once('../../includes/session.php');
if(!ecell_sec_session_start()) {
	header("Location: includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}
include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('general_manage_state_list_csv.html');

$success = "";
$show_form = "y";
$msg_err = "";

if (isset($_FILES["file"]["error"]) && isset($_FILES['file']['tmp_name'])) {
	if ($_FILES["file"]["error"] > 0) {
		$msg_err = "File not found. Please upload the file again.";
	} else {

		$fp = fopen($_FILES['file']['tmp_name'], 'rb');

		while (($line = fgets($fp)) !== false) {

			$line = str_replace('\n','',$line);
			$line = explode(',', $line);

			$state_name = (trim($line[0]));
			$zone_name = trim($line[1]);

			$sql = "INSERT INTO `state`(`state_id`, `state_name`, `zone`, `status_value_id`, `log_id`) VALUES (NULL,$state_name,$zone,$status_value_id,$zone,$status_value_id,:log_id)";

			$ac_on = "Entered new state: ".$state_name;
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'state';

			// $log_id = log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);		

			$sql = "INSERT INTO `state`(`state_id`, `state_name`, `zone`, `status_value_id`, `log_id`) VALUES (NULL,:state_name,:zone,:status_value_id,:log_id)";

			$stmt = $conn->prepare($sql);

			// $status_value_id = get_status('on');

			$log_id = 1;
			$status_value_id = 1;

			$stmt->bindParam(':state_name', $state_name);
			$stmt->bindParam(':zone', $zone_name);
			$stmt->bindParam(':status_value_id', $status_value_id);
			$stmt->bindParam(':log_id', $log_id);

			try {        
				$stmt->execute();
			} catch (PDOException $e) {
				echo $sql . "<br>" . $e->getMessage();
			} 
		}

		$success = "States uploaded successfully.";

	}

} 

$TBS->Show();

?>
