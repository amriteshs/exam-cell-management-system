<?php
require_once('../../tbs/tbs_class.php');
require_once('../../tbs/tbs_plugin_html.php');
include_once('../../includes/include.php');

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('application_media.html');

$conn = ecell_get_conn();

$studentID = 0;

$errorArray = array();
$success = false;
$showError = false;
$showPre = true;
$signpic = " ";
$signmime = " ";

$photopic = " ";
$photomime = " ";

$email = "";
$showme = true;

if (isset($_POST['media'])) {

	foreach ($_POST as $k => $v) {
		if (isset($_POST[$k])) {
			$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
		}
	}

	if (isset($_POST["registered_email"])) {
		$registered_email = $_POST["registered_email"];
		if (!filter_var($registered_email, FILTER_VALIDATE_EMAIL)) {
			header("Location: offline_registration_error.php");
		}

		$sql = "select student_id from student_original where comm_email = '" . $registered_email . "' order by date_of_admission desc;";

		$stmt = $conn->prepare($sql);
		$stmt->execute();

		$result1 = $stmt->fetchAll();

		$count = $stmt->rowCount();

		if ($count > 0) {
			$studentID = $result1[0][0];
		} else {
			echo "Not registered. Please enter a registered Email.";
			exit;
		}

		$email = $registered_email;

		if($studentID) {


			$onlineConnection = ecell_get_conn_fedratecd();
			$sql = $onlineConnection->prepare("SELECT media, mime from student_documents where username = '$email' and category = 'photo' ORDER BY document_id DESC ");
			$sql->execute();

			$result = $sql->fetchAll();
			if($result) {
				$mime = $result[0][1];
				$media = $result[0][0];

				$photopic = base64_encode($media);
				$photomime = $mime;
			}else{
				$showme = false;
			}

			$sql = $onlineConnection->prepare("SELECT media, mime from student_documents where username = '$email' and category = 'sign' ORDER BY document_id DESC ");
			$sql->execute();

			$result = $sql->fetchAll();
			if($result) {
				$mime = $result[0][1];
				$media = $result[0][0];

				$signpic = base64_encode($media);
				$signmime = $mime;
			}

		}
		$_SESSION['reg_email'] = $email;
		$_SESSION['studentID'] = $studentID;

	}
} else if (isset($_POST['finalupload'])) {

	$email = $_POST['reg_email'];
	$studentID = $_POST['studentID'];

	if ($_FILES["userphoto"]["error"] > 0) {
		$errorArray[] = "Error: Please upload a valid photo file.";
	} else {
		$tmpNamePhoto = $_FILES['userphoto']['tmp_name'];
		$fileSizePhoto = $_FILES['userphoto']['size'];
		if ($fileSizePhoto > 0 && $fileSizePhoto < 256000) {
			$fileTypePhoto = $_FILES['userphoto']['type'];
			if ($fileTypePhoto == "image/jpeg" || $fileTypePhoto == "image/png") {
				$fp = fopen($tmpNamePhoto, 'r');
				$contentPhoto = fread($fp, filesize($tmpNamePhoto));
				// $contentPhoto = addslashes($contentPhoto);
				$contentPhoto = base64_encode($contentPhoto);
				fclose($fp);
			} else {
				$errorArray[] = "Error in Photo : Please upload file of specified type.";
			}
		} else {
			$errorArray[] = "Error in Photo : Please upload file of specified size.";
		}
	}
	if ($_FILES["usersign"]["error"] > 0) {
		$errorArray[] = "Error: Please upload a valid signature file.";
	} else {
		$tmpNameSign = $_FILES['usersign']['tmp_name'];
		$fileSizeSign = $_FILES['usersign']['size'];
		if ($fileSizeSign > 0 && $fileSizeSign < 256000) {
			$fileTypeSign = $_FILES['usersign']['type'];
			if ($fileTypeSign == "image/jpeg" || $fileTypeSign == "image/png") {
				$fp = fopen($tmpNameSign, 'r');
				$contentSign = fread($fp, filesize($tmpNameSign));
				// $contentSign = addslashes($contentSign);
				$contentSign = base64_encode($contentSign);
				fclose($fp);
			} else {
				$errorArray[] = "Error in signature : Please upload file of specified type.";
			}
		} else {
			$errorArray[] = "Error in signature : Please upload file of specified size.";
		}
	}


	if (count($errorArray) == 0) {

		$sql = $conn->prepare("INSERT INTO student_documents (document_id,student_id,category,description,media,mime,status_value_id,log_id) VALUES ('', :studentID,'photo','Photo', :media, :mime, 1,1)");
		$sql->bindParam(':media', $contentPhoto, PDO::PARAM_LOB);
		$sql->bindParam(':mime', $fileTypePhoto);
		$sql->bindParam(':studentID', $studentID);
		$sql->execute();

		$sql = $conn->prepare("INSERT INTO student_documents (document_id,student_id,category,description,media,mime,status_value_id,log_id) VALUES ('', :studentID,'sign','Signature', :media, :mime, 1,1)");
		$sql->bindParam(':media', $contentSign, PDO::PARAM_LOB);
		$sql->bindParam(':mime', $fileTypeSign);
		$sql->bindParam(':studentID', $studentID);
		$sql->execute();

		$photopic = base64_encode($contentPhoto);
		$photomime = $fileTypePhoto;

		$signpic = base64_encode($contentSign);
		$signmime = $fileTypeSign;

		$success = "Photo and Signature files are uploaded.";
	} else {
		$showError = true;
	}
}else if (isset($_POST['accept'])) {

	$email = $_POST['reg_email'];
	$studentID = $_POST['studentID'];

	$onlineConnection = ecell_get_conn_fedratecd();
	$sql = $onlineConnection->prepare("SELECT media, mime from student_documents where username = '$email' and category = 'photo' ORDER BY document_id DESC ");
	$sql->execute();

	$result = $sql->fetchAll();
	if($result) {
		$mime = $result[0][1];
		$media = $result[0][0];

		$photopic = base64_encode($media);
		$photomime = $mime;

		$sql = $conn->prepare("INSERT INTO student_documents (document_id,student_id,category,description,media,mime,status_value_id,log_id) VALUES ('', :studentID,'photo','Photo', :media, :mime, 1,1)");
		$sql->bindParam(':media', $photopic, PDO::PARAM_LOB);
		$sql->bindParam(':mime', $photomime);
		$sql->bindParam(':studentID', $studentID);
		$sql->execute();

		$sql = $onlineConnection->prepare("SELECT media, mime from student_documents where username = '$email' and category = 'sign' ORDER BY document_id DESC ");
		$sql->execute();

		$result = $sql->fetchAll()[0];
		$mime = $result[1];
		$media = $result[0];

		$signpic = base64_encode($media);
		$signmime = $mime;

		$sql = $conn->prepare("INSERT INTO student_documents (document_id,student_id,category,description,media,mime,status_value_id,log_id) VALUES ('', :studentID,'sign','Signature', :media, :mime, 1,1)");
		$sql->bindParam(':media', $signpic, PDO::PARAM_LOB);
		$sql->bindParam(':mime', $signmime);
		$sql->bindParam(':studentID', $studentID);
		$sql->execute();

		$success = "Photo and Signature files are uploaded.";
	}
}else{

	$email = $_SESSION['reg_email'] ;
	$studentID = $_SESSION['studentID'];

	$onlineConnection = ecell_get_conn_fedratecd();
	$sql = $onlineConnection->prepare("SELECT media, mime from student_documents where username = '$email' and category = 'photo' ORDER BY document_id DESC ");
	$sql->execute();

	$result = $sql->fetchAll();
	if($result) {
		$mime = $result[0][1];
		$media = $result[0][0];
		$photopic = base64_encode($media);
		$photomime = $mime;
	}else{
		$showme = false;
	}
	$sql = $onlineConnection->prepare("SELECT media, mime from student_documents where username = '$email' and category = 'sign' ORDER BY document_id DESC ");
	$sql->execute();

	$result = $sql->fetchAll();
	if($result) {
		$mime = $result[0][1];
		$media = $result[0][0];
		$signpic = base64_encode($media);
		$signmime = $mime;
	}
}

$TBS->MergeBlock('errorBlock', $errorArray);
$TBS->Show();

?>
