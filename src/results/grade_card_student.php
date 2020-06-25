<?php
    include_once('../../includes/include.php');

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
    $TBS->LoadTemplate('grade_card_student.html');

    $success = '';
    $show_exam_type = 'y';
    $show_end_sem = '';
    $show_back_exam = '';
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

        if (isset($_POST['exam_type'])) {
            $exam_type = $_POST['exam_type'];
        }
       
        if (isset($_POST['enrollment_no']) && (ecell_is_alpha_num($_POST['enrollment_no']))) {
            $enrollment_no = strtoupper($_POST['enrollment_no']);
        }

        if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
            $year = $_POST['year'];
        }

        if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
            $sem_code = $_POST['sem_code'];
        }

        if (isset($_POST['month'])) {
            $month = $_POST['month'];
        }

        if (isset($_POST['output_format'])) {
            $output_format = $_POST['output_format']; 
        }

        if (isset($_POST['submit_exam_type'])) {
            $_SESSION['temp_exam_type'] = $exam_type;

            if ($exam_type == 'END') {
                $show_exam_type = '';
                $show_end_sem = 'y';
                $show_back_exam = '';
            } else if ($exam_type == 'BACK') {
                $show_exam_type = '';
                $show_end_sem = '';
                $show_back_exam = 'y';
            }
        }

        if (isset($_POST['submit_end_sem'])) {
            $student_details = ecell_student_details($conn, $enrollment_no);
            $student_id = $student_details['student_id'];
            
            if (is_null($student_id)) {
                $msg_err = 'Enrollment number does not exist.';
            } else {
                $stmt = $conn->prepare("SELECT * FROM results, course_structure WHERE results.sem_id = course_structure.sem_id AND results.student_id = :student_id AND course_structure.sem_id_year = :year AND course_structure.sem_id_sem_code = :sem_code AND exam_type = 'END'");
                $stmt->bindParam(':student_id', $student_id);
                $stmt->bindParam(':year', $year);
                $stmt->bindParam(':sem_code', $sem_code);
                $stmt->execute();
                $result_details = $stmt->fetchAll();

                if (empty($result_details)) {
                    $msg_err = 'Result not available for student in given year and semester code.';
                } else {
                    if ($output_format == 'pdf') {
                        $mpdf = new mPDF('');

                        ecell_generate_grade_card_pdf($conn, $mpdf, $enrollment_no, $year, $sem_code);

                        $sem_code_name = ecell_sem_code_description($conn, $sem_code);

                        $file = $enrollment_no.'_'.$year.'_'.$sem_code_name.'_Grade_Card.pdf';
                        $mpdf->Output($file, 'I');
                    } else if ($output_format == 'tex') {
                        mkdir('/home/ecell/apache_files/cert/'.$enrollment_no.'/', 0777, true);

                        $student_photo = ecell_student_photo($conn, $enrollment_no);
                        $image = $student_photo['image'];
                        $mime = $student_photo['mime'];
						
						$ex = explode("/", $mime);
						$img_ext = $ex[1];

                        $image = base64_decode($image);

                        $output1 = fopen('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$enrollment_no.'.'.$img_ext, 'wb');
                        fwrite($output1, $image);
                        fclose($output1);              

                        $sem_code_name = ecell_sem_code_description($conn, $sem_code);

                        $tex_file_name = $enrollment_no.'_'.$year.'_'.$sem_code_name.'_Grade_Card.tex';
                        $pdf_file_name = $enrollment_no.'_'.$year.'_'.$sem_code_name.'_Grade_Card.pdf';
                        
                        $output2 = fopen('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$tex_file_name, 'wb');

                        $tex = "\\documentclass[10pt,a4paper,oneside]{article}\n\\usepackage[a4paper,left=0.9in,right=0.9in,top=1in,bottom=1in]{geometry}\n\\usepackage{graphicx}\n\\usepackage{longtable}\n\\usepackage{array}\n\\usepackage{float}\n\\usepackage{enumitem}\n\\usepackage{eso-pic}\n\\usepackage{booktabs}\n\n\\renewcommand{\\familydefault}{ppl}\n\n\\newcommand\\FrontsidePic{\n\t\\put(-4,0){\n\t\t\\parbox[b][\\paperheight]{\\paperwidth}{%\n\t\t\t\\vfill\n\t\t\t\\centering\n\t\t\t\\includegraphics[width=\\paperwidth,height=\\paperheight]{/home/ecell/apache_files/cert/orig/frame-gs.pdf}\n\t\t\t\\vfill\n}}}\n\n\\begin{document}\n\t% ----------- Header -------------\n\t\\pagestyle{empty}\n\n\t\n\n".ecell_generate_grade_card_tex($conn, $enrollment_no, $year, $sem_code)."\n\n\\end{document}";

                        fwrite($output2, $tex);
                        fclose($output2);

                        shell_exec("cd /home/ecell/apache_files/cert/$enrollment_no ; /bin/pdflatex $tex_file_name");
    
                        header('Content-Type:application/pdf');
                        header('Content-disposition:attachment;filename='.$pdf_file_name);
                        header('Content-Length:'.filesize('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$pdf_file_name));
                        readfile('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$pdf_file_name);

                        unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no.".".$img_ext);
                        unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_".$year."_".$sem_code_name."_Grade_Card.aux");
                        unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_".$year."_".$sem_code_name."_Grade_Card.log");
                        unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_".$year."_".$sem_code_name."_Grade_Card.pdf");
                        unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_".$year."_".$sem_code_name."_Grade_Card.tex");

                        rmdir('/home/ecell/apache_files/cert/'.$enrollment_no.'/');

                        die();
                    }
                }
            }
        } else if (isset($_POST['submit_back_exam'])) {
            $student_details = ecell_student_details($conn, $enrollment_no);
            $student_id = $student_details['student_id'];
            
            if (is_null($student_id)) {
                $msg_err = 'Enrollment number does not exist.';
            } else {
                $date_of_exam = '%'.$year.'-'.$month.'-%';

                $stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND date_of_exam LIKE :date_of_exam AND exam_type = 'BACK'");
                $stmt->bindParam(':student_id', $student_id);
                $stmt->bindParam(':date_of_exam', $date_of_exam);
                $stmt->execute();
                $result_details = $stmt->fetchAll();

                if (empty($result_details)) {
                    $msg_err = 'Back exam details not available for student in given year and month.';
                } else {
                    if ($output_format == 'pdf') {
                        $mpdf = new mPDF('');

                        ecell_generate_back_grade_card_pdf($conn, $mpdf, $enrollment_no, $year, $month);

                        if ($month == '01') {
                            $month_name = 'January';
                        } else if ($month == '02') {
                            $month_name = 'February';
                        } else if ($month == '03') {
                            $month_name = 'March';
                        } else if ($month == '04') {
                            $month_name = 'April';
                        } else if ($month == '05') {
                            $month_name = 'May';
                        } else if ($month == '06') {
                            $month_name = 'June';
                        } else if ($month == '07') {
                            $month_name = 'July';
                        } else if ($month == '08') {
                            $month_name = 'August';
                        } else if ($month == '09') {
                            $month_name = 'September';
                        } else if ($month == '10') {
                            $month_name = 'October';
                        } else if ($month == '11') {
                            $month_name = 'November';
                        } else if ($month == '12') {
                            $month_name = 'December';
                        } 

                        $file = $enrollment_no.'_'.$year.'_'.$month_name.'_Back_Grade_Card.pdf';
                        $mpdf->Output($file, 'I');
                    } else if ($output_format == 'tex') {
                        if ($month == '01') {
                            $month_name = 'January';
                        } else if ($month == '02') {
                            $month_name = 'February';
                        } else if ($month == '03') {
                            $month_name = 'March';
                        } else if ($month == '04') {
                            $month_name = 'April';
                        } else if ($month == '05') {
                            $month_name = 'May';
                        } else if ($month == '06') {
                            $month_name = 'June';
                        } else if ($month == '07') {
                            $month_name = 'July';
                        } else if ($month == '08') {
                            $month_name = 'August';
                        } else if ($month == '09') {
                            $month_name = 'September';
                        } else if ($month == '10') {
                            $month_name = 'October';
                        } else if ($month == '11') {
                            $month_name = 'November';
                        } else if ($month == '12') {
                            $month_name = 'December';
                        } 

                        mkdir('/home/ecell/apache_files/cert/'.$enrollment_no.'/', 0777, true);

                        $student_photo = ecell_student_photo($conn, $enrollment_no);
                        $image = $student_photo['image'];
                        $mime = $student_photo['mime'];
                        
                        $ex = explode("/", $mime);
                        $img_ext = $ex[1];

                        $image = base64_decode($image);

                        $output1 = fopen('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$enrollment_no.'.'.$img_ext, 'wb');
                        fwrite($output1, $image);
                        fclose($output1);

                        $tex_file_name = $enrollment_no.'_'.$year.'_'.$month_name.'_Back_Grade_Card.tex';
                        $pdf_file_name = $enrollment_no.'_'.$year.'_'.$month_name.'_Back_Grade_Card.pdf';

                        $output2 = fopen('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$tex_file_name, 'wb');

                        $tex = "\\documentclass[10pt,a4paper,oneside]{article}\n\\usepackage[a4paper,left=0.9in,right=0.9in,top=1in,bottom=1in]{geometry}\n\\usepackage{graphicx}\n\\usepackage{longtable}\n\\usepackage{array}\n\\usepackage{float}\n\\usepackage{enumitem}\n\\usepackage{eso-pic}\n\\usepackage{booktabs}\n\n\\renewcommand{\\familydefault}{ppl}\n\n\\newcommand\\FrontsidePic{\n\t\\put(-4,0){\n\t\t\\parbox[b][\\paperheight]{\\paperwidth}{%\n\t\t\t\\vfill\n\t\t\t\\centering\n\t\t\t\\includegraphics[width=\\paperwidth,height=\\paperheight]{/home/ecell/apache_files/cert/orig/frame-gs.pdf}\n\t\t\t\\vfill\n}}}\n\n\\begin{document}\n\t% ----------- Header -------------\n\t\\pagestyle{empty}\n\n\t\n\n".ecell_generate_back_grade_card_tex($conn, $enrollment_no, $year, $month)."\n\n\\end{document}";

                        fwrite($output2, $tex);
                        fclose($output2);

                        shell_exec("cd /home/ecell/apache_files/cert/$enrollment_no ; /bin/pdflatex $tex_file_name");
    
                        header('Content-Type:application/pdf');
                        header('Content-disposition:attachment;filename='.$pdf_file_name);
                        header('Content-Length:'.filesize('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$pdf_file_name));
                        readfile('/home/ecell/apache_files/cert/'.$enrollment_no.'/'.$pdf_file_name);

                        // unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no.".".$img_ext);
                        // unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_".$year."_".$month_name."_Back_Grade_Card.aux");
                        // unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_".$year."_".$month_name."_Back_Grade_Card.log");
                        // unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_".$year."_".$month_name."_Back_Grade_Card.pdf");
                        // unlink("/home/ecell/apache_files/cert/".$enrollment_no."/".$enrollment_no."_".$year."_".$month_name."_Back_Grade_Card.tex");

                        // rmdir('/home/ecell/apache_files/cert/'.$enrollment_no.'/');
                        
                        die();
                    }
                }
            }
        }
    }

    $TBS->MergeBlock('exam_type', $conn, "SELECT * FROM exam_type WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY exam_type");
    $TBS->MergeBlock('sem', $conn, "SELECT * FROM sem_code_description WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY sem_code_id");
    $TBS->Show();
?>
