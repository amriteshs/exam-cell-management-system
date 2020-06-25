<?php
include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('course_registration_csv.html');

$form1 = true;
$form2 = false;
$enrollment_no = '';
$year = '';
$disabled = '';
$allcorrect = 'true';

//error_reporting(E_ALL); ini_set('display_errors', 1);

$success="";
$msg_err="";
$on_id = ecell_check_status('on');

if (isset($_POST["submit"])) {
    if (isset($_FILES['file']['name'])) {
        if ($_FILES["file"]["error"] > 0) {
            $error .= "Error: " . $_FILES["file"]["error"] . '\n';
            $allcorrect = false;
        } else {

            $program = $_POST['program'];
            $year = $_POST['year'];
            $sem_code = $_POST['sem_code'];

            $uploadedFile = $_FILES['file']['tmp_name'];
            $uploadedFP = fopen($uploadedFile, 'rb');
            $linecount  = 1;

            if (!feof($uploadedFP)) {
                while ($row_in_csv = fgetcsv($uploadedFP)) {
                    if ($linecount == 1) {
                        $linecount++;
                        $courses = $row_in_csv;
                        $course_names = $row_in_csv;
                        for ($i = 1; $i != count($courses); $i++) {
                            $course_code = $courses[$i];
                            $sql = "SELECT course_id FROM courses WHERE course_code = :course_code AND year = :year AND sem_code = :sem_code";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':course_code', $course_code);
                            $stmt->bindParam(':year', $year);
                            $stmt->bindParam(':sem_code', $sem_code);
                            $stmt->execute();
                            if ($stmt->rowCount()) {
                                $courses[$i] = $stmt->fetch()['course_id'];
                            } else {
                                $msg_err .= "$course_code doesn't exist (Line $linecount).\n";
                                $allcorrect = false;
                            }
                        }
                        continue;
                    }

                    $student_id = ecell_get_val('student', 'enrollment_no', $row_in_csv[0], 'student_id');
                    if ($student_id == 0) {
                        $allcorrect = false;
                        $msg_err .= "$row_in_csv[0] doesn't exist (Line $linecount).\n";
                    }

                    for ($i = 1; $i != count($courses); $i++) {
                        if ($row_in_csv[$i] != 'N' && $row_in_csv[$i] != 'Y') {
                            $msg_err .= "Data can only be 'Y' or 'N' (Line $linecount).\n";
                            $allcorrect = false;
                        } else {
                            $sql = "SELECT * FROM course_registration WHERE course_id =:course AND student_id=:st_id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':course', $courses[$i]);
                            $stmt->bindParam(':st_id', $student_id);
                            $stmt->execute();
                            if ($stmt->rowCount() == 1) {
                                $msg_err .= "$row_in_csv[0] is already registered in " . $course_names[$i] . "(Line $linecount).\n";
                                $allcorrect = false;
                            }
                        }
                    }

                    $linecount++;
                }
                if ($allcorrect) {
                    $uploadedFP = fopen($uploadedFile, 'rb');
                    $linecount = 1;
                    while ($row_in_csv = fgetcsv($uploadedFP)) {
                        if ($linecount == 1) {
                            ++$linecount;
                            continue;
                        }
                        $student_id = ecell_get_val('student', 'enrollment_no', $row_in_csv[0], 'student_id');
                        for ($i = 1; $i != count($courses); $i++) {
                            if ($row_in_csv[$i] == 'Y') {

                                $sql = "INSERT INTO course_registration(course_id, student_id, grade_card_year, grade_card_sem_code, status_value_id, log_id) VALUES (:course, :st_id, :year, :sem_code_id, :status_id, :log_id)";
                                $ac_on = "Registered a student with $enrollment_no in " . $courses[$i] . " in sem-code $sem_code of $year.";
                                $s_i = $_SESSION['staff_id'];
                                $r = $_SESSION['rank'];
                                $tn = 'course_registration';
                                $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

                                $status_id = ecell_get_status('on');
//                                echo $courses[$i] . "-$student_id-$year-$sem_code-$status_id-$log_id<br>";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':course', $courses[$i]);
                                $stmt->bindParam(':st_id', $student_id);
                                $stmt->bindParam(':year', $year);
                                $stmt->bindParam(':sem_code_id', $sem_code);
                                $stmt->bindParam(':status_id', $status_id);
                                $stmt->bindParam(':log_id', $log_id);
                                $stmt->execute();
                            }
                        }
                        $success = 'Success';
                    }
                }
            }
        }
    } else {
        $error .= "Files don't exist.<br>";
    }
}


$TBS->MergeBlock('program', $conn, "SELECT * FROM program");
$TBS->MergeBlock('sem_code_description', $conn, "SELECT * FROM sem_code_description, status_value WHERE sem_code_description.status_value_id = status_value.status_value_id AND status_value.status_id = $on_id");

$TBS->show();

?>