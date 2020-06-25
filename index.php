<?php

	include_once('./includes/include.php');
	include_once ('./includes/functions.php');
	include_once('./includes/session.php');
	include_once('./includes/connect.php');
	include_once('tbs/tbs_class.php');
	include_once('tbs/tbs_plugin_html.php');

	date_default_timezone_set('Asia/Calcutta');

	if(!ecell_sec_session_start()) {
		header("Location: includes/logout.php");
	}
	
	if(ecell_login_check($conn)) {
		header('Location: src/index.php');
		
	}
	
	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('index.tpl'); 
	$check_password="";
	$msg = "";
	$pc_mag = "";

	if (isset($_GET['pc'])) {
		$pc_mag = "Password changed successfully.";
	}
	
	if (!isset($_POST)) {
		$_POST = &$HTTP_POST_VARS;
	}

	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		if (isset($_POST['username']) &&  (trim($_POST['username']) != '')) {
			$username = $_POST['username'];
		} else {
			$msg = "Please Enter Valid Username";
		}
		if (isset($_POST['password']) ) {
			$password = $_POST['password'];
		} else {
			$msg = "Please Enter Password";
		}
		
		if(!$msg) {
			
			$sql = "SELECT staff.password,staff.rank,status.status_name,staff.staff_id, staff_name from staff,status_value,status WHERE staff.status_value_id=status_value.status_value_id AND status_value.status_id=status.status_id AND username = :username;";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->execute();

			if($stmt->rowCount() == 1) {
				$row = $stmt->fetchObject();
				$check_password = $row->password;
				$rank = $row->rank;
				$status = $row->status_name;
				$staff_id = $row->staff_id;
				$staff_name = $row->staff_name;
			}	

// bss edit
			if ($username == "abhi") {
			///	$check_password = "wrong";
			}

			if (strcmp($check_password,crypt($password, $check_password))==0) {
				if( $status == 'on' ) { 
					ecell_set_session_parameter($staff_id,$username,$check_password,$rank,$staff_name);
//					login_user($rank);

					$sql = "LOGIN";
					$ac_on = "Login by staff menber $staff_name from IP address " . $_SERVER['HTTP_X_FORWARDED_FOR'];
					$s_i = $_SESSION['staff_id'];
					$r = $_SESSION['rank'];
					$t_n="login";
					$log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$t_n);

					header("Location: src/home/home.php");

				} else {
					$msg="Sorry, you are not ACTIVE user.";
				}	
			} else {
				$msg = "You've Entered Wrong Credentials.";
			}
		
		}	
	}

	$TBS->Show();

	function login_user($rank) {
		if($rank < 50 && $rank >= 40) {
			header("Location: src/home/home.php");
		} else if($rank < 40){
			header("Location: src/home/home.php");
		} else {
			header("Location: src/home/home.php");
		}
	}

?>
