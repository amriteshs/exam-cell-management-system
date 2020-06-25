<?php
    include_once('../../includes/include.php');

    set_time_limit(120);

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
    $TBS->LoadTemplate('grade_card_batch.html');

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
       
        if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
            $program_id = $_POST['program_id'];
        }

        if (isset($_POST['year_of_joining']) && filter_var($_POST['year_of_joining'], FILTER_VALIDATE_INT)) {
            $year_of_joining = $_POST['year_of_joining'];
        }

        if (isset($_POST['sem_code_of_joining']) && filter_var($_POST['sem_code_of_joining'], FILTER_VALIDATE_INT)) {
            $sem_code_of_joining = $_POST['sem_code_of_joining'];
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
            $stmt = $conn->prepare("SELECT DISTINCT results.student_id FROM results, course_structure WHERE results.sem_id = course_structure.sem_id AND course_structure.program_id = :program_id AND course_structure.year_of_joining = :year_of_joining AND course_structure.sem_code_of_joining = :sem_code_of_joining AND course_structure.sem_id_year = :year AND course_structure.sem_id_sem_code = :sem_code");
            $stmt->bindParam(':program_id', $program_id);
            $stmt->bindParam(':year_of_joining', $year_of_joining);
            $stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':sem_code', $sem_code);
            $stmt->execute();
            $student_id_list = $stmt->fetchAll();

            if (empty($student_id_list)) {
                $msg_err = 'Results not available for given details.';
            } else {
                if ($output_format == 'pdf') {
                    $mpdf = new mPDF('');

                    foreach ($student_id_list as $stud) {
                        $student_id = $stud['student_id'];

                        $stmt = $conn->prepare("SELECT enrollment_no FROM student WHERE student_id = :student_id");
                        $stmt->bindParam(':student_id', $student_id);
                        $stmt->execute();
                        $enrollment_no = $stmt->fetchAll()[0]['enrollment_no'];

                        ecell_generate_grade_card_pdf($conn, $mpdf, $enrollment_no, $year, $sem_code);
                    }

                    $program_details = ecell_program_details($conn, $program_id);
                    $program_code = $program_details['program_code'];

                    $sem_code_name = ecell_sem_code_description($conn, $sem_code);
                    $sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);

                    $file = $program_code.'_'.$year_of_joining.'_'.$sem_code_of_joining_name.'_'.$year.'_'.$sem_code_name.'_Grade_Card.pdf';

                    $mpdf->Output($file, 'I');
                } else if ($output_format == 'tex') {
                    $program_details = ecell_program_details($conn, $program_id);
                    $program_code = $program_details['program_code'];

                    $sem_code_name = ecell_sem_code_description($conn, $sem_code);
                    $sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);

                    $file_name = $program_code.'_'.$year_of_joining.'_'.$sem_code_of_joining_name.'_'.$year.'_'.$sem_code_name.'_Grade_Card.tex';

                    $output = fopen("php://output",'w') or die("Can't open php://output");
                    header("Content-Disposition:attachment;filename=$file_name");

                    $tex = "\\documentclass[10pt,a4paper,oneside]{article}\n\\usepackage[a4paper,left=0.9in,right=0.9in,top=1in,bottom=1in]{geometry}\n\\usepackage{graphicx}\n\\usepackage{longtable}\n\\usepackage{array}\n\\usepackage{float}\n\\usepackage{enumitem}\n\\usepackage{eso-pic}\n\\usepackage{booktabs}\n\n\\renewcommand{\\familydefault}{ppl}\n\n\\newcommand\\FrontsidePic{\n\t\\put(-4,0){\n\t\t\\parbox[b][\\paperheight]{\\paperwidth}{%\n\t\t\t\\vfill\n\t\t\t\\centering\n\t\t\t\\includegraphics[width=\\paperwidth,height=\\paperheight]{/home/ecell/apache_files/cert/orig/frame-gs.pdf}\n\t\t\t\\vfill\n}}}\n\n\\begin{document}\n\t% ----------- Header -------------\n\t\\pagestyle{empty}\n\n\t\n\n";

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

                        $tex .= ecell_generate_grade_card_tex($conn, $enrollment_no, $year, $sem_code);

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
        } else if (isset($_POST['submit_back_exam'])) {
            $date_of_exam = '%'.$year.'-'.$month.'-%';

            $stmt = $conn->prepare("SELECT DISTINCT results.student_id FROM results, course_structure WHERE results.sem_id = course_structure.sem_id AND course_structure.program_id = :program_id AND course_structure.year_of_joining = :year_of_joining AND course_structure.sem_code_of_joining = :sem_code_of_joining AND results.date_of_exam LIKE :date_of_exam");
            $stmt->bindParam(':program_id', $program_id);
            $stmt->bindParam(':year_of_joining', $year_of_joining);
            $stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
            $stmt->bindParam(':date_of_exam', $date_of_exam);
            $stmt->execute();
            $student_id_list = $stmt->fetchAll();

            if (empty($student_id_list)) {
                $msg_err = 'Back exam results not available for given details.';
            } else {
                if ($output_format == 'pdf') {
                    $mpdf = new mPDF('');

                    foreach ($student_id_list as $stud) {
                        $student_id = $stud['student_id'];

                        $stmt = $conn->prepare("SELECT enrollment_no FROM student WHERE student_id = :student_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
                        $stmt->bindParam(':student_id', $student_id);
                        $stmt->execute();
                        $enrollment_no = $stmt->fetchAll()[0]['enrollment_no'];

                        ecell_generate_back_grade_card_pdf($conn, $mpdf, $enrollment_no, $year, $month);
                    }

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

                    $program_details = ecell_program_details($conn, $program_id);
                    $program_code = $program_details['program_code'];

                    $sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);

                    $file = $program_code.' '.$year_of_joining.' '.$sem_code_of_joining_name.' '.$year.' '.$month_name.'_Back_Grade_Card.pdf';
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

                    $program_details = ecell_program_details($conn, $program_id);
                    $program_code = $program_details['program_code'];

                    $sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);

                    $file_name = $program_code.' '.$year_of_joining.' '.$sem_code_of_joining_name.' '.$year.' '.$month_name.'_Back_Grade_Card.pdf';
                    
                    $output = fopen("php://output",'w') or die("Can't open php://output");
                    header("Content-Disposition:attachment;filename=$file_name");

                    $tex = "\\documentclass[10pt,a4paper,oneside]{article}\n\\usepackage[a4paper,left=0.9in,right=0.9in,top=1in,bottom=1in]{geometry}\n\\usepackage{graphicx}\n\\usepackage{longtable}\n\\usepackage{array}\n\\usepackage{float}\n\\usepackage{enumitem}\n\\usepackage{eso-pic}\n\\usepackage{booktabs}\n\n\\renewcommand{\\familydefault}{ppl}\n\n\\newcommand\\FrontsidePic{\n\t\\put(-4,0){\n\t\t\\parbox[b][\\paperheight]{\\paperwidth}{%\n\t\t\t\\vfill\n\t\t\t\\centering\n\t\t\t\\includegraphics[width=\\paperwidth,height=\\paperheight]{/home/ecell/apache_files/cert/orig/frame-gs.pdf}\n\t\t\t\\vfill\n}}}\n\n\\begin{document}\n\t% ----------- Header -------------\n\t\\pagestyle{empty}\n\n\t\n\n";

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

                        $tex .= ecell_generate_back_grade_card_tex($conn, $enrollment_no, $year, $month);

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
    }

    $TBS->MergeBlock('exam_type', $conn, "SELECT * FROM exam_type WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY exam_type");
    $TBS->MergeBlock('program1, program2', $conn, "SELECT * FROM program WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
    $TBS->MergeBlock('sem1, sem2, sem3', $conn, "SELECT * FROM sem_code_description WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
    $TBS->Show();
?>