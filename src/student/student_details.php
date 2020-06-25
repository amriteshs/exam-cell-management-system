<?php
require_once('../../tbs/tbs_class.php');
require_once('../../tbs/tbs_plugin_html.php');
include_once('../../includes/include.php');


$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('student_details.html');


$errorArray = array();
$success = false;
$showError = false;
$showPre = true;
$signpic = " ";
$signmime = " ";

$photopic = " ";
$photomime = " ";

$email = "";
$showme = false;
$showform = true;
$enroll = " ";


$TBS->MergeBlock('errorBlock', $errorArray);
$TBS->Show();

?>
