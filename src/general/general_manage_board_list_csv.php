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
$TBS->LoadTemplate('general_manage_board_list_csv.html');

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

			$board_name = (trim($line[0]));
			$state_name = trim($line[1]);

			$state_id = ecell_get_val('state', 'state_name', $state_name, 'state_id');

			$sql = "INSERT INTO `board`(`board_id`, `board_name`, `state_id`, `status_value_id`, `log_id`) VALUES (NULL, :board_name, :state_id, :status_value_id, :log_id)";

			$ac_on = "Entered new board: $board_name in $state_name.";
			$s_i = $_SESSION['staff_id'];
			$r = $_SESSION['rank'];
			$tn = 'state';

			$log_id = 1;		

			$sql = "INSERT INTO `board`(`board_id`, `board_name`, `state_id`, `status_value_id`, `log_id`) VALUES (NULL, :board_name, :state_id, :status_value_id, :log_id)";

			$stmt = $conn->prepare($sql);

			$status_value_id = 1;

			$stmt->bindParam(':board_name', $board_name);
			$stmt->bindParam(':state_id', $state_id);
			$stmt->bindParam(':status_value_id', $status_value_id);
			$stmt->bindParam(':log_id', $log_id);

			try {        
				$stmt->execute();
			} catch (PDOException $e) {
				echo $sql . "<br>" . $e->getMessage();
			} 

		}

		$success = "Boards uploaded successfully.";

	}

} 

$TBS->Show();

?>
