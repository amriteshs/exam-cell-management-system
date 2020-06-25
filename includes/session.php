<?php

function ecell_sec_session_start() {

	$session_name = 'sec_session_id';

	session_set_cookie_params(0,'/',null,false,true);

	session_name($session_name);
	if (!isset($_SESSION)) { 
		session_start(); 
	}           
	session_regenerate_id(true);

	if(!isset($_SESSION['page_visit'])){
		$_SESSION['page_visit']= time();
		$_SESSION['last_time']= time();
	}
	else{
		$_SESSION['page_visit']= $_SESSION['last_time'];
		$_SESSION['last_time']= time();
	}
	if(ecell_validate_session()) {

		if(!ecell_prevent_hijacking()) {
			ecell_regenerate_session();
		}

	} else {

		$_SESSION = array();
		//header('Location: logout.php');
		return false;
	}
	return true;
}


function ecell_prevent_hijacking()
{
	if(!isset($_SESSION['IPaddress']) || !isset($_SESSION['userAgent']))
		return false;

	if ($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR'])
		return false;

	if( $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT'])
		return false;

	return true;
}

function ecell_regenerate_session()
{
	session_regenerate_id();
}

function ecell_validate_session()
{
	if(!isset($_SESSION['last_time']) || !isset($_SESSION['page_visit']))
		return false;

	if(isset($_SESSION['page_visit']) && time() - $_SESSION['page_visit'] > 100000)
		return false;

	return true;
}

function ecell_set_session_parameter($staff_id, $username, $password, $rank, $staff_name)
{
	$_SESSION['staff_id'] = $staff_id;       
	$_SESSION['username'] = $username;
	$_SESSION['staff_name'] = $staff_name;
	$_SESSION['login_string'] = hash('sha512',trim($password).$_SERVER['HTTP_USER_AGENT']);
	$_SESSION['last_time'] = time();
	$_SESSION['rank'] = $rank;
	$_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
	$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
}

function ecell_login_check($conn) {

	if (isset($_SESSION['staff_id'], $_SESSION['username'], $_SESSION['login_string'])) {
		$user_id = $_SESSION['staff_id'];
		$login_string = $_SESSION['login_string'];
		$username = $_SESSION['username'];
		$user_browser = $_SERVER['HTTP_USER_AGENT'];

		$stmt = $conn->prepare("SELECT password FROM staff WHERE username=:uname");
		//	$stmt->bindparam(":st",$user_id, PDO::PARAM_INT);
		$stmt->bindparam(":uname",$username, PDO::PARAM_STR);
		try {
			$stmt->execute();
		} catch (PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
		}
		$password= $stmt->fetchColumn();

		$login_check = hash('sha512', trim($password).$user_browser);

		if (strcmp($login_check, $login_string)==0){

			$_SESSION['last_time'] = time();
			return true;

		} else {
			return false;
		}

	} else {
		return false;
	}
}
?>
