<?php

$servername = "localhost";
$usernameforserver = "alyssandra";
$passwordforserver = "tsisibi@080044";
$dbname = "offline";
$conn = null;

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $usernameforserver, $passwordforserver);
	$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$conn->exec("set names utf8");
} catch (PDOException $e) {
	echo "Connection failed: " . $e->getMessage();
}

function ecell_get_conn() {	

	$servername = "localhost";
	$usernameforserver = "alyssandra";
	$passwordforserver = "tsisibi@080044";
	$dbname = "offline";
	$conn = null;

	try {
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $usernameforserver, $passwordforserver);
		$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->exec("set names utf8");
	} catch (PDOException $e) {
		echo "Connection failed: " . $e->getMessage();
	}

	return $conn;

}

function ecell_get_conn_fedratecd() {

	$servername = "localhost";
	$usernameforserver = "alyssandra";
	$passwordforserver = "tsisibi@080044";
	$dbname = "federated_online";
	$conn = null;

	try {
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $usernameforserver, $passwordforserver);
		$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->exec("set names utf8");
	} catch (PDOException $e) {
		return false;
	}

	return $conn;

}

?>
