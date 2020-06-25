<?php
/**
 * Created by PhpStorm.
 * User: ecell
 * Date: 8/3/16
 * Time: 4:27 PM
 */


error_reporting(E_ALL); ini_set('display_errors', 1);

include_once('../../includes/include.php');

$sql = "SELECT * from student_documents, student WHERE student.student_id=student_documents.student_id";
$sql = $conn->prepare($sql);
$sql->execute();
$res = $sql->fetchAll(PDO::FETCH_ASSOC);

foreach ($res as $doc) {

    if ($doc['category'] == 'sign') {
        $filename = "/var/www/html/version3/sign/" . $doc['enrollment_no'] . "S";
    } else {
        $filename = "/var/www/html/version3/photo/" . $doc['enrollment_no'];
    }

    $mime = $doc['mime'];
    if ($mime == 'image/png') {
        $myfile = fopen("$filename.png", "w") or die("Unable to open file!");
        fwrite($myfile, base64_decode($doc['media']));
        fclose($myfile);
    } else if ($mime == 'image/jpeg') {
        $myfile = fopen("$filename.jpeg", "w") or die("Unable to open file!");
        fwrite($myfile, base64_decode($doc['media']));
        fclose($myfile);
    }

}

?>