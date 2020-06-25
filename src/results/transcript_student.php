
<?php
	include_once('../../includes/include.php');

	error_reporting(E_ALL); 
	ini_set('display_errors', 1);
	error_reporting(E_ERROR | E_PARSE);

	if (!ecell_sec_session_Start()) {
	  	header('Location: ../../includes/logout.php');
	}

  	if (!ecell_login_Check($conn)) {
  		header('Location: ../../includes/logout.php');
  	}

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('transcript_student.html'); 

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

		if (isset($_POST['enrollment']) && (ecell_is_alpha_num($_POST['enrollment']))) {
			$enrollment_no = strtoupper($_POST['enrollment']); 
		}

		if (isset($_POST['output_format'])) {
			$output_format = $_POST['output_format']; 
		}

		$student_details = ecell_student_details($conn, $enrollment_no);
        $student_id = $student_details['student_id'];
        
        if (is_null($student_id)) {
            $msg_err = 'Enrollment number does not exist.';
        } else {
        	if ($output_format == 'pdf') {
				$mpdf = new mPDF('');

				ecell_generate_transcript_pdf($conn, $mpdf, $enrollment_no);

				$file = $enrollment_no.'_Transcript.pdf';
				$mpdf->Output($file, 'I');
			} else if ($output_format == 'tex') {
				mkdir('/home/ecell/apache_files/cert/'.$enrollment_no.'/', 0777, true);         

				$tex_file_name = $enrollment_no.'_Transcript.tex';
				$pdf_file_name = $enrollment_no.'_Transcript.pdf';

				$output = fopen('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$tex_file_name, 'wb');
				
				$tex = "\\documentclass[10pt,a4paper,oneside]{article}\n\\usepackage[a4paper,left=0.85in,right=0.9in,top=1in,bottom=1in]{geometry}\n\\usepackage{graphicx}\n\\usepackage{longtable}\n\\usepackage{array}\n\\usepackage{float}\n\\usepackage{enumitem}\n\\usepackage{eso-pic}\n\n\\renewcommand{\\familydefault}{ppl}\n\\renewcommand{\\familydefault}{\\sfdefault}\n\n\\newcommand\\FrontsidePic{\n\t\\put(-4,0){\n\t\t\\parbox[b][\\paperheight]{\\paperwidth}{%\n\t\t\t\\vfill\n\t\t\t\\centering\n\t\t\t\\includegraphics[width=\\paperwidth,height=\\paperheight]{/home/ecell/apache_files/cert/orig/frame-ts.pdf}\n\t\t\t\\vfill\n\t\t\t}}}\n\n\\begin{document}\n\t".ecell_generate_transcript_tex($conn, $enrollment_no)."\n\n\t\\end{document}";

				fwrite($output, $tex);
		        fclose($output);

                shell_exec("cd /home/ecell/apache_files/cert/$enrollment_no ; /bin/pdflatex $tex_file_name");

                header('Content-Type:application/pdf');
                header('Content-disposition:attachment;filename='.$pdf_file_name);
                header('Content-Length:'.filesize('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$pdf_file_name));
                readfile('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$pdf_file_name);

                unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_Transcript.aux");
                unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_Transcript.log");
                unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_Transcript.pdf");
                unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_Transcript.tex");

                rmdir('/home/ecell/apache_files/cert/'.$enrollment_no.'/');

				die();
			}
		}
	}

	$TBS->Show();
?>