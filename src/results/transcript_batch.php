<?php
    include_once('../../includes/include.php');

    set_time_limit(500);

	error_reporting(E_ALL); 
	ini_set('display_errors', 1);
	error_reporting(E_ERROR | E_PARSE);

	if (!ecell_sec_session_start()) {
	  	header('Location: ../../includes/logout.php');
	}

  	if (!ecell_login_check($conn)) {
  		header('Location: ../../includes/logout.php');
  	}

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('transcript_batch.html'); 

	$success = '';
	$show_form = 'y';
	$msg_err = '';

	if (!isset($_POST)) {
		$_POST = &$HTTP_POST_VARS;
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		foreach ($_POST as $k => $v) {
			if (isset($_POST[$k])) {
				$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
			}	
		}

		if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
			$program_id = $_POST['program_id']; 
		}

		if (isset($_POST['year_of_joining']) && filter_var($_POST['year_of_joining'], FILTER_VALIDATE_INT)) {
			$year_of_joining = $_POST['year_of_joining']; 
		}

		if (isset($_POST['sem_code_of_joining']) && filter_var($_POST['sem_code_of_joining'], FILTER_VALIDATE_INT)) {
			$sem_code_of_joining = $_POST['sem_code_of_joining']; 
		}

		if (isset($_POST['output_format'])) {
			$output_format = $_POST['output_format']; 
		}

		$stmt = $conn->prepare("SELECT student_id FROM student WHERE program_id = :program_id AND year = :year_of_joining AND sem_code = :sem_code_of_joining");
		$stmt->bindParam(':program_id', $program_id);
		$stmt->bindParam(':year_of_joining', $year_of_joining);
		$stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
		$stmt->execute();
		$student_id_list = $stmt->fetchAll();

		if (empty($student_id_list)) {
			$msg_err = 'No student enrolled in given year and semester code of joining.';
		} else {
			if ($output_format == 'pdf') {
				$mpdf = new mPDF('');

				foreach ($student_id_list as $stud) {
					$student_id = $stud['student_id'];

					$stmt = $conn->prepare("SELECT enrollment_no FROM student WHERE student_id = :student_id");
					$stmt->bindParam(':student_id', $student_id);
					$stmt->execute();
					$enrollment_no = $stmt->fetchAll()[0]['enrollment_no'];

					ecell_generate_transcript_pdf($conn, $mpdf, $enrollment_no);
				}

				$program_details = ecell_program_details($conn, $program_id);
				$program_code = $program_details['program_code'];

				$sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);

				$file = $program_code.'_'.$year_of_joining.'_'.$sem_code_of_joining_name.'_Transcript.pdf';
				$mpdf->Output($file, 'I');
			} else if ($output_format == 'tex') {
				$program_details = ecell_program_details($conn, $program_id);
				$program_code = $program_details['program_code'];

				$sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);
				
				$file_name = $program_code.'_'.$year_of_joining.'_'.$sem_code_of_joining_name.'_Transcript.tex';

				$output = fopen("php://output",'w') or die("Can't open php://output");
		        header("Content-Disposition:attachment;filename=$file_name");

				$tex = "\\documentclass[10pt,a4paper,oneside]{article}\n\\usepackage[a4paper,left=0.85in,right=0.9in,top=1in,bottom=1in]{geometry}\n\\usepackage{graphicx}\n\\usepackage{longtable}\n\\usepackage{array}\n\\usepackage{float}\n\\usepackage{enumitem}\n\\usepackage{eso-pic}\n\n\\renewcommand{\\familydefault}{ppl}\n\\renewcommand{\\familydefault}{\\sfdefault}\n\n\\newcommand\\FrontsidePic{\n\t\\put(-4,0){\n\t\t\\parbox[b][\\paperheight]{\\paperwidth}{%\n\t\t\t\\vfill\n\t\t\t\\centering\n\t\t\t\\includegraphics[width=\\paperwidth,height=\\paperheight]{/home/ecell/apache_files/cert/orig/frame-ts.pdf}\n\t\t\t\\vfill\n\t\t\t}}}\n\n\\begin{document}\n
        		";

        		$ctr = 0;
        		$len = count($student_id_list);

				foreach ($student_id_list as $stud) {
					$student_id = $stud['student_id'];

					$stmt = $conn->prepare("SELECT enrollment_no FROM student WHERE student_id = :student_id");
					$stmt->bindParam(':student_id', $student_id);
					$stmt->execute();
					$enrollment_no = $stmt->fetchAll()[0]['enrollment_no'];

					if ($ctr != 0) {
						$tex .= "\n\n\\newpage\n\n";
					}

					$tex .= ecell_generate_transcript_tex($conn, $enrollment_no);

					if ($ctr == $len - 1) {
						$tex .= "\n\n\t\\end{document}";
					}

					$ctr++;
				}

				fwrite($output, $tex);
		        fclose($output);

				die();
			}
		}
	}

	$TBS->MergeBlock('program', $conn, "SELECT * FROM program WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
	$TBS->MergeBlock('sem', $conn, "SELECT * FROM sem_code_description WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
	$TBS->Show();
?>