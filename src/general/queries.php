<?php
	include_once('../../includes/include.php');

    error_reporting(E_ALL); 
    ini_set('display_errors', 1);
    error_reporting(E_ERROR | E_PARSE);

    $TBS = new clsTinyButStrong;
    $TBS->LoadTemplate('queries.html');

    $show_main = 'y';
    $show_year = '';
    $show_program = '';
    $show_year_program = '';

    if (isset($_GET['query'])) {
        $query = $_GET['query']; 

        if ($query == 'students_category') {
            $TBS->LoadTemplate('queries_students_category.html'); 

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach ($_POST as $k => $v) {
                    if (isset($_POST[$k])) {
                        $_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
                    }   
                }

                if (isset($_POST['year_of_joining_1']) && filter_var($_POST['year_of_joining_1'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_1 = $_POST['year_of_joining_1']; 
                }

                $year_of_joining_2 = $year_of_joining_1;

                if (isset($_POST['year_of_joining_2']) && filter_var($_POST['year_of_joining_2'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_2 = $_POST['year_of_joining_2']; 
                }

                $stmt = $conn->prepare("SELECT DISTINCT program_id FROM student WHERE year >= :year_of_joining_1 AND year <= :year_of_joining_2");
                $stmt->bindParam(':year_of_joining_1', $year_of_joining_1);
                $stmt->bindParam(':year_of_joining_2', $year_of_joining_2);
                $stmt->execute();
                $program_id_list = $stmt->fetchAll();

                if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
                    $program_id_list = array('program_id' => $_POST['program_id']); 
                }

                $stmt = $conn->prepare("SELECT category_id FROM student_category");
                $stmt->execute();
                $category_id_list = $stmt->fetchAll();

                if (isset($_POST['category_id']) && filter_var($_POST['category_id'], FILTER_VALIDATE_INT)) {
                    $category_id_list = array('category_id' => $_POST['category_id']); 
                }

                if (isset($_POST['category_type'])) {
                    $category_type = $_POST['category_type']; 
                }

                $mpdf = new mPDF('');

                ecell_students_category_pdf($conn, $mpdf, $category_id_list, $program_id_list, $year_of_joining_1, $year_of_joining_2, $category_type);

                $file_name = 'Students_Category.pdf';
                $mpdf->Output($file_name, 'I');
            }
        } else if ($query == 'students_program_dropouts') {
            $TBS->LoadTemplate('queries_students_program_dropouts.html'); 

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach($_POST as $k => $v) {
                    if (isset($_POST[$k])) {
                        $_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
                    }   
                }

                if (isset($_POST['year_of_joining_1']) && filter_var($_POST['year_of_joining_1'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_1 = $_POST['year_of_joining_1']; 
                }

                $year_of_joining_2 = $year_of_joining_1;

                if (isset($_POST['year_of_joining_2']) && filter_var($_POST['year_of_joining_2'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_2 = $_POST['year_of_joining_2']; 
                }

                $stmt = $conn->prepare("SELECT DISTINCT program_id FROM student WHERE year >= :year_of_joining_1 AND year <= :year_of_joining_2");
                $stmt->bindParam(':year_of_joining_1', $year_of_joining_1);
                $stmt->bindParam(':year_of_joining_2', $year_of_joining_2);
                $stmt->execute();
                $program_id_list = $stmt->fetchAll();

                if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
                    $program_id_list = array('program_id' => $_POST['program_id']); 
                }

                $mpdf = new mPDF('');

                ecell_students_program_dropouts_pdf($conn, $mpdf, $program_id_list, $year_of_joining_1, $year_of_joining_2);

                $file_name = 'Students_Program_Dropouts.pdf';
                $mpdf->Output($file_name, 'I');
            }
        } else if ($query == 'students_active_dropout') {
            $TBS->LoadTemplate('queries_students_active_dropout.html'); 

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach ($_POST as $k => $v) {
                    if (isset($_POST[$k])) {
                        $_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
                    }   
                }

                if (isset($_POST['year_of_joining_1']) && filter_var($_POST['year_of_joining_1'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_1 = $_POST['year_of_joining_1']; 
                }

                $year_of_joining_2 = $year_of_joining_1;

                if (isset($_POST['year_of_joining_2']) && filter_var($_POST['year_of_joining_2'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_2 = $_POST['year_of_joining_2']; 
                }

                $stmt = $conn->prepare("SELECT category_id FROM student_category");
                $stmt->execute();
                $category_id_list = $stmt->fetchAll();

                if (isset($_POST['category_id']) && filter_var($_POST['category_id'], FILTER_VALIDATE_INT)) {
                    $category_id_list = array('category_id' => $_POST['category_id']); 
                }

                if (isset($_POST['category_type'])) {
                    $category_type = $_POST['category_type']; 
                }

                $mpdf = new mPDF('');

                ecell_students_active_dropout_pdf($conn, $mpdf, $category_id_list, $year_of_joining_1, $year_of_joining_2, $category_type);

                $file_name = 'Students_Active_and_Dropouts.pdf';
                $mpdf->Output($file_name, 'I');
            }
        } else if ($query == 'students_program_level') {
            $TBS->LoadTemplate('queries_students_program_level.html'); 

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach($_POST as $k => $v) {
                    if (isset($_POST[$k])) {
                        $_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
                    }   
                }
                
                if (isset($_POST['year_of_joining_1']) && filter_var($_POST['year_of_joining_1'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_1 = $_POST['year_of_joining_1']; 
                }

                $year_of_joining_2 = $year_of_joining_1;

                if (isset($_POST['year_of_joining_2']) && filter_var($_POST['year_of_joining_2'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_2 = $_POST['year_of_joining_2']; 
                }

                $mpdf = new mPDF('');

                ecell_students_program_level_pdf($conn, $mpdf, $year_of_joining_1, $year_of_joining_2);

                $file_name = 'Students_Program_Level.pdf';
                $mpdf->Output($file_name, 'I');
            }
        } else if ($query == 'students_dasa') {
            $TBS->LoadTemplate('queries_students_dasa.html');

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach ($_POST as $k => $v) {
                    if (isset($_POST[$k])) {
                        $_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
                    }   
                }

                if (isset($_POST['year_of_joining_1']) && filter_var($_POST['year_of_joining_1'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_1 = $_POST['year_of_joining_1']; 
                }

                $year_of_joining_2 = $year_of_joining_1;

                if (isset($_POST['year_of_joining_2']) && filter_var($_POST['year_of_joining_2'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_2 = $_POST['year_of_joining_2']; 
                }

                $stmt = $conn->prepare("SELECT DISTINCT program_id FROM student WHERE year >= :year_of_joining_1 AND year <= :year_of_joining_2");
                $stmt->bindParam(':year_of_joining_1', $year_of_joining_1);
                $stmt->bindParam(':year_of_joining_2', $year_of_joining_2);
                $stmt->execute();
                $program_id_list = $stmt->fetchAll();

                if (isset($_POST['program_id']) && filter_var($_POST['program_id'], FILTER_VALIDATE_INT)) {
                    $program_id_list = array('program_id' => $_POST['program_id']); 
                }

                $mpdf = new mPDF('');

                ecell_students_dasa_pdf($conn, $mpdf, $program_id_list, $year_of_joining_1, $year_of_joining_2);

                $file_name = 'Students_DASA.pdf';
                $mpdf->Output($file_name, 'I');
            }
        } else if ($query == 'students_state_board') {
            $TBS->LoadTemplate('queries_students_state_board.html'); 

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                foreach($_POST as $k => $v) {
                    if(isset($_POST[$k])) {
                        $_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
                    }   
                }

                if (isset($_POST['year_of_joining_1']) && filter_var($_POST['year_of_joining_1'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_1 = $_POST['year_of_joining_1']; 
                }

                $year_of_joining_2 = $year_of_joining_1;

                if (isset($_POST['year_of_joining_2']) && filter_var($_POST['year_of_joining_2'], FILTER_VALIDATE_INT)) {
                    $year_of_joining_2 = $_POST['year_of_joining_2']; 
                }
                
                if (isset($_POST['state'])) {
                    $state_id = $_POST['state']; 
                }

                if (isset($_POST['board_10'])) {
                    $board_10 = $_POST['board_10']; 
                }
                
                if (isset($_POST['perc_10'])) {
                    $perc_10 = $_POST['perc_10']; 
                }

                if (isset($_POST['pstate_10'])) {
                    $pstate_10_id = $_POST['pstate_10']; 
                }

                if (isset($_POST['board_12'])) {
                    $board_12 = $_POST['board_12']; 
                }
                
                if (isset($_POST['perc_12'])) {
                    $perc_12 = $_POST['perc_12']; 
                }

                if (isset($_POST['pstate_12'])) {
                    $pstate_12_id = $_POST['pstate_12']; 
                }

                if (isset($_POST['category_type'])) {
                    $category_type = $_POST['category_type']; 
                }

                $params = array();

                if (!empty($state_id)) {
                    $params['perm_state_id'] = $state_id;
                }

                if (!empty($pstate_10_id)) {
                    $params['board_10_passing_state_id'] = $pstate_10_id;
                }

                if (!empty($pstate_12_id)) {
                    $params['board_12_passing_state_id'] = $pstate_12_id;
                }

                if (!empty($board_10)) {
                    $params['board_id_10'] = $board_10;
                }

                if (!empty($board_12)) {
                    $params['board_id_12'] = $board_12;
                }

                if (!empty($perc_10)) {
                    $params['percentage_10'] = $perc_10;
                }

                if (!empty($perc_12)) {
                    $params['percentage_12'] = $perc_12;
                }
                
                $mpdf = new mPDF('');

                ecell_students_state_board_pdf($conn, $mpdf, $year_of_joining_1, $year_of_joining_2, $params, $category_type);

                $file_name = 'Students_State_and_Board.pdf';
                $mpdf->Output($file_name, 'I');
            }
        } else if ($query == 'students_toppers_list') {
            $TBS->LoadTemplate('queries_students_toppers_list.html');

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach($_POST as $k => $v) {
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

                if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
                    $year = $_POST['year']; 
                }

                if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
                    $sem_code = $_POST['sem_code']; 
                }

                if (isset($_POST['top_n']) && filter_var($_POST['top_n'], FILTER_VALIDATE_INT)) {
                    $top_n = $_POST['top_n']; 
                }

                $mpdf = new mPDF('');

                $stmt = $conn->prepare("SELECT sem_id FROM course_structure WHERE program_id = :program_id AND year_of_joining = :year_of_joining AND sem_code_of_joining = :sem_code_of_joining AND sem_id_year = :year AND sem_id_sem_code = :sem_code");
                $stmt->bindParam(':program_id', $program_id);
                $stmt->bindParam(':year_of_joining', $year_of_joining);
                $stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
                $stmt->bindParam(':year', $year);
                $stmt->bindParam(':sem_code', $sem_code);
                $stmt->execute();
                $sem_id = $stmt->fetchAll()[0]['sem_id'];

                ecell_toppers_list_pdf($conn, $mpdf, $sem_id, $program_id, $top_n);

                $program_details = ecell_program_details($conn, $program_id);
                $program_code = $program_details['program_code'];

                $sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);
                $sem_code_name = ecell_sem_code_description($conn, $sem_code);

                $file_name = $program_code.'_'.$year_of_joining.'_'.$sem_code_of_joining_name.'_'.$year.'_'.$sem_code_name.'_Toppers_List.pdf';
                $mpdf->Output($file_name, 'I');
            }
        } else if ($query == 'regular_programme_details') {
            $TBS->LoadTemplate('queries_regular_programme_details.html'); 

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach($_POST as $k => $v) {
                    if (isset($_POST[$k])) {
                        $_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
                    }   
                }

                if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
                    $year = $_POST['year']; 
                }

                $mpdf = new mPDF('');

                ecell_regular_programme_details_pdf($conn, $mpdf, $year);

                $file_name = 'Regular_Programme_Details_'.$year.'.pdf';
                $mpdf->Output($file_name, 'I');         
            }
        } else if ($query == 'semester_result_summary') {
            $TBS->LoadTemplate('queries_semester_result_summary.html'); 

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

                if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
                    $year = $_POST['year'];
                }

                if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
                    $sem_code = $_POST['sem_code'];
                }

                $mpdf = new mPDF('');

                $stmt = $conn->prepare("SELECT sem_id FROM course_structure WHERE program_id = :program_id AND year_of_joining = :year_of_joining AND sem_code_of_joining = :sem_code_of_joining AND sem_id_year = :year AND sem_id_sem_code = :sem_code");
                $stmt->bindParam(':program_id', $program_id);
                $stmt->bindParam(':year_of_joining', $year_of_joining);
                $stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
                $stmt->bindParam(':year', $year);
                $stmt->bindParam(':sem_code', $sem_code);
                $stmt->execute();
                $sem_id = $stmt->fetchAll()[0]['sem_id'];

                ecell_semester_result_summary_pdf($conn, $mpdf, $sem_id);

                $program_details = ecell_program_details($conn, $program_id);
                $program_code = $program_details['program_code'];

                $sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);
                $sem_code_name = ecell_sem_code_description($conn, $sem_code);

                $file_name = $program_code.'_'.$year_of_joining.'_'.$sem_code_of_joining_name.'_'.$year.'_'.$sem_code_name.'_Semester_Result_Summary.pdf';
                $mpdf->Output($file_name, 'I');
            }
        } else if ($query == 'foreign_student_enrollment') {
            $TBS->LoadTemplate('queries_foreign_student_enrollment.html'); 

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach ($_POST as $k => $v) {
                    if (isset($_POST[$k])) {
                        $_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
                    }   
                }
                
                if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
                    $year = $_POST['year']; 
                }

                $mpdf = new mPDF('');

                ecell_foreign_student_enrollment_pdf($conn, $mpdf, $year);

                $file_name = 'Foreign_Student_Enrollment_'.$year.'.pdf';
                $mpdf->Output($file_name, 'I');
            }
        }
    }

    $TBS->MergeBlock('program', $conn, 'SELECT * FROM program');
    $TBS->MergeBlock('category', $conn, 'SELECT * FROM student_category');
    $TBS->MergeBlock('state, pstate_10, pstate_12', $conn, 'SELECT * FROM state');
    $TBS->MergeBlock('board_10, board_12', $conn, 'SELECT * FROM board');
    $TBS->MergeBlock('sem, sem1', $conn, 'SELECT * FROM sem_code_description');
    
	$TBS->Show();
?>
