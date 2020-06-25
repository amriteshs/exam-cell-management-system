<?php

include_once('../../includes/functions.php');
include_once('../../includes/include.php');
include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
    header("Location: includes/logout.php");
}

if(!ecell_login_check($conn)) {
    header("Location: ../../includes/logout.php");
}

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('student_upload_media.html');

$upload_types = array('Photo', 'Signature', 'Misc');

if (!isset($_POST)) {
    $_POST = &$HTTP_POST_VARS;
}

$msg_err = "";
$success = "";    

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    if (isset($_POST['upload_type'])) {
        $upload_type = $upload_types[$_POST['upload_type']];
    }

    if (isset($_POST['description'])) {
        $description = $_POST['description'];
    }

    if ($_FILES["file"]["error"] > 0 && !$msg_err) {
        $msg_err .= "File not found.\n";
    } else {
        $zipArchive = new ZipArchive();
        $result = $zipArchive->open($_FILES['file']['tmp_name']);
        if ($result === TRUE) {
            $zipArchive ->extractTo("/home/ecell/apache_files/media/");
            $zipArchive ->close();
        }
    }

    foreach (glob('/home/ecell/apache_files/media/*') as $img) {
	$img_orig = $img;
        $fileTypePhoto = mime_content_type ( $img);
        $fp = fopen($img, 'r');
        $content_img = fread($fp, filesize($img));
        $content_img = base64_encode($content_img);
        $path = $img;
        $img = substr($img, strrpos($img, '/') + 1);
        $ext = substr($img, strpos($img, '.') + 1);
        $enroll = substr($img, 0, strpos($img, '.'));

        $student_id = ecell_get_val('student', 'enrollment_no', $enroll, 'student_id');

        if ($student_id > 0) {
            $sql = "INSERT INTO `student_documents`(`document_id`, `student_id`, `category`, `description`, `media`, `mime`, `status_value_id`, `log_id`) VALUES (NULL, :studentID, :category, :description, :media, :mime, :status_value_id, :log_id)";

            $ac_on = $upload_type . " uploaded for " . $enroll . ".";
            $s_i = $_SESSION['staff_id'];
            $r = $_SESSION['rank'];
            $tn = 'student_documents';
            $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

            $sql = $conn->prepare($sql);            

            $sql->bindParam(':studentID', $student_id);
            $sql->bindParam(':category', $upload_type);
            $sql->bindParam(':media', $content_img, PDO::PARAM_LOB);
            $sql->bindParam(':description', $description);
            $sql->bindParam(':mime', $fileTypePhoto);
            $status_value_id = ecell_get_status('on');
            $sql->bindParam(':status_value_id', $status_value_id);
            $sql->bindParam(':log_id', $log_id);
            $sql->execute();
            $success .= "Uploaded for $enroll succesfully.\n";
        } else {
            $success .= "Student Id not found for $enroll.\n";
        }

        unlink($img_orig);

    }
}

$TBS->MergeBlock('upload_types', $upload_types);
$TBS->Show();

?>
