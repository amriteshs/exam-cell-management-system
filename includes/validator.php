<?php
function ecell_test_input($data)
{
	$data = htmlspecialchars($data);
	return $data;
}

function  ecell_validate_string($data){
	if (!isset($data)) {
		return " is required";
	} else {
		if (!preg_match("/^[a-zA-Z ]*$/", $data)) {
			return " : Only letters and white space allowed";
		}
	}
	return null;
}

function ecell_validate($data){
	if (!isset($data)) {
		return" is required";
	}
	return null;
}
function ecell_validate_pin($data){
	if (!isset($data)) {
		return " is required";
	} else {
		if (!preg_match("/^[0-9]{6}$/", $data)) {
			return " : Only 6 digits are allowed";
		}
	}
	return null;
}
function ecell_validate_phone($data){
	if (!isset($data)) {
		return" is required";
	} else {
		if (!preg_match("/^[0-9]{10,15}$/", $data)) {
			return " : Only 6 digits are allowed";
		}
	}
	return null;
}
?>
