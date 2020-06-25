<?php
  
	include_once('../../includes/include.php');
	
  error_reporting(E_ALL); ini_set('display_errors', 1);
    
  $TBS = new clsTinyButStrong;
  $TBS->LoadTemplate('add_courses_csv.html');
  
  $show_form  = "y";
  $success    = "";
  $msg_err    = "";

  if (isset($_FILES['file']['name'])) {
      if ($_FILES["file"]["error"] > 0) {
          
          $errorArray[] = "Please upload the file.";
      
      } else {
          
          $uploadedFile = $_FILES['file']['tmp_name'];
          $uploadedFP = fopen($uploadedFile, 'rb');
          $linecount  = 0;
  		    $all_correct = 1;

          if (!feof($uploadedFP)) {
            while ($data = fgetcsv($uploadedFP)) {
              
              if($linecount == 0) {
                $linecount++;
                continue;
              }
              
              $course_code = filter_var($data[0], FILTER_SANITIZE_STRING);
              $year = filter_var($data[3], FILTER_SANITIZE_STRING);
              $sem_code = filter_var($data[4], FILTER_SANITIZE_STRING);

              $status_id = ecell_get_status('on');

              $sql = "SELECT * FROM courses WHERE course_code=:course_code AND year=:year AND sem_code=:sem_code";
              $stmt = $conn->prepare($sql);
              $stmt->bindParam(':course_code', $course_code);
              $stmt->bindParam(':year', $year);
              $stmt->bindParam(':sem_code', $sem_code);
              $stmt->execute();

              if ($stmt->rowCount() == 1) {
                $msg_err .= "The course at line no ".$linecount." has already been added.\n";
                $all_correct = 0;
              }
              
              $linecount++;
            
            }

          }

          $uploadedFile = $_FILES['file']['tmp_name'];
          $uploadedFP = fopen($uploadedFile, 'rb');

          $linecount = 0;

          if (!feof($uploadedFP) && $all_correct) {
		        while ($data = fgetcsv($uploadedFP)) {
              
              if($linecount == 0) {
                $linecount++;
                continue;
              }
		          
              $status_id = ecell_get_status('on');
              
              $course_code = filter_var($data[0], FILTER_SANITIZE_STRING);
              $course_name = filter_var($data[1], FILTER_SANITIZE_STRING);
              $course_type = filter_var($data[2], FILTER_SANITIZE_STRING);
              $year = filter_var($data[3], FILTER_SANITIZE_STRING);
              $sem_code = filter_var($data[4], FILTER_SANITIZE_STRING);
              $theory_credit = filter_var($data[5], FILTER_SANITIZE_STRING);
              $lab_credit = filter_var($data[6], FILTER_SANITIZE_STRING);
              $mid_sem_exam_time = filter_var($data[7], FILTER_SANITIZE_STRING);
              $end_sem_exam_time = filter_var($data[8], FILTER_SANITIZE_STRING);
              $contact_person = filter_var($data[9], FILTER_SANITIZE_STRING);


              $sql = "SELECT * FROM courses WHERE course_code=:course_code AND year=:year AND sem_code=:sem_code";
              $stmt = $conn->prepare($sql);
              $stmt->bindParam(':course_code', $course_code);
              $stmt->bindParam(':year', $year);
              $stmt->bindParam(':sem_code', $sem_code);
              $stmt->execute();
              if ($stmt->rowCount() == 1) {
                  $msg_err .= "The course $course_code on line $linecount already exists.";
                  break;
              }

            $sql = "SELECT course_type_id FROM course_type WHERE course_type_description=:course_type_description";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':course_type_description', $course_type);
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $course_type_id = $stmt->fetch()['course_type_id'];
            } else {
                $msg_err .= "The course type $course_type on line $linecount doesn't exist.";
                break;
            }


              $sql = "INSERT INTO `courses`(`course_id`, `course_code`, `course_name`, `course_type`, `year`, `sem_code`, `theory_credit`, `lab_credit`, `mid_sem_exam_time`, `end_sem_exam_time`,  `status_value_id`, `log_id`) VALUES (NULL, :course_code, :course_name, :course_type, :year, :sem_code, :theory_credit, :lab_credit, :mid_sem_exam_time, :end_sem_exam_time, :status_value_id, :log_id)";

              $ac_on = "Entered a new course ".$course_code." in year ".$year." and sem code ".$sem_code.".";
              $s_i = $_SESSION['staff_id'];
              $r = $_SESSION['rank'];
              $tn = 'courses';

              $log_id = ecell_log_procedure($s_i,$r,$sql,$ac_on,$conn,$tn);

              $sql = "INSERT INTO `courses`(`course_id`, `course_code`, `course_name`, `course_type`, `year`, `sem_code`, `theory_credit`, `lab_credit`, `mid_sem_exam_date`, `end_sem_exam_date`, `status_value_id`, `log_id`) VALUES (NULL, :course_code, :course_name, :course_type, :year, :sem_code, :theory_credit, :lab_credit, :mid_sem_exam_time, :end_sem_exam_time,  :status_value_id, :log_id)";

              $stmt = $conn->prepare($sql);
     
              $stmt->bindParam(':course_code', $course_code);
              $stmt->bindParam(':course_name', $course_name);
              $stmt->bindParam(':course_type', $course_type_id);
              $stmt->bindParam(':year', $year);
              $stmt->bindParam(':sem_code', $sem_code);
              $stmt->bindParam(':theory_credit', $theory_credit);
              $stmt->bindParam(':lab_credit', $lab_credit);
              $stmt->bindParam(':mid_sem_exam_time', $mid_sem_exam_time);
              $stmt->bindParam(':end_sem_exam_time', $end_sem_exam_time);
              
              $stmt->bindParam(':status_value_id', $status_id);
              $stmt->bindParam(':log_id', $log_id);

              $stmt->execute();
          
            }

              if ($msg_err == "") {
                  $success = "Success";
              }

          }
	   }
  }

  if (!isset($_POST['file']) && isset($_POST['submit'])) {
    $msg_err = "Please upload a file.";
  }

  $TBS->Show();

?>
