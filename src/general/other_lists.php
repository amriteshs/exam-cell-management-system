<?php

	include_once('../../includes/include.php');
	include_once('../../includes/session.php');
if(!ecell_sec_session_start()) {
	header("Location: includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('other_lists.html'); 

	$success = "";
	$msg_err = "";

	if (!isset($_POST)) {
		$_POST = &$HTTP_POST_VARS;
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		foreach($_POST as $k=>$v) {
		
			if(isset($_POST[$k])) {
				$_POST[$k] = filter_var($v,FILTER_SANITIZE_STRING);
			}
		}

		if (isset($_POST['table_name']) && isset($_POST['name'])) {

			if (empty($_POST['name'])) {

				$msg_err = "Name should not be empty.";

			} else {

				try {
					$info = explode(",",$_POST['table_name']);
					$table_name = $info[0];
					$column_name = $info[1];
					$name = $_POST['name'];
					if(ecell_get_val($table_name, $column_name, $name, 'log_id') == 0){
					if ($table_name == 'status') {
						
						$status_id = ecell_get_status('on');
						
						$sql = "INSERT INTO $table_name VALUES (NULL, '$name', 'LOG_ID')";
			  			
						$ac_on = "Inserted a new ".$table_name;
			            $s_i = $_SESSION['staff_id'];
			            $r = $_SESSION['rank'];
			           	$tn = $table_name;

			           	$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

						$sql = "INSERT INTO $table_name VALUES (NULL, '$name', '$log_id')";
						$stmt = $conn->prepare($sql);
						$stmt->bindParam(':name', $name);
            			$stmt->bindParam(':log_id', $log_id);
            			$stmt->bindParam(':status_id', $status_id);
						
					} else {
						
						$status_id = ecell_get_status('on');

						$sql = "INSERT INTO $table_name VALUES (NULL, '$name', '$status_id', 'LOG_ID')";
			  			
						$ac_on = "Inserted a new ".$table_name;
			            $s_i = $_SESSION['staff_id'];
			            $r = $_SESSION['rank'];
			           	$tn = $table_name;

			           	$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

						$sql = "INSERT INTO $table_name VALUES (NULL, '$name', '$status_id', '$log_id')";
						$stmt = $conn->prepare($sql);
						$stmt->bindParam(':table_name', $table_name);
            			$stmt->bindParam(':name', $name);
            			$stmt->bindParam(':log_id', $log_id);
			
					}

					$stmt->execute();

					$success = "Success";
					} else {
					$msg_err = "Duplicate entry.";
					}
				} catch(PDOException $e) {
					echo $sql . "<br>" . $e->getMessage();	
				}				
			}
		}
	}

	$TBS->Show();

?>
