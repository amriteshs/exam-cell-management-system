<?php
    include_once('../../includes/include.php');

    set_time_limit(150);

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
    $TBS->LoadTemplate('result_sheet_generation.html');

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

        if (isset($_POST['year']) && filter_var($_POST['year'], FILTER_VALIDATE_INT)) {
            $year = $_POST['year'];
        }

        if (isset($_POST['sem_code']) && filter_var($_POST['sem_code'], FILTER_VALIDATE_INT)) {
            $sem_code = $_POST['sem_code'];
        }

        if (isset($_POST['output_format'])) {
            $output_format = $_POST['output_format'];
        }

        $stmt = $conn->prepare("SELECT sem_id FROM course_structure WHERE program_id = :program_id AND year_of_joining = :year_of_joining AND sem_code_of_joining = :sem_code_of_joining AND sem_id_year = :year AND sem_id_sem_code = :sem_code");
        $stmt->bindParam(':program_id', $program_id);
        $stmt->bindParam(':year_of_joining', $year_of_joining);
        $stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':sem_code', $sem_code);
        $stmt->execute();
        $sem_id = $stmt->fetchAll()[0]['sem_id'];

        if (is_null($sem_id)) {
            $msg_err = 'Results not available for given details.';
        } else {
            if ($output_format == 'pdf') {
                $mpdf = new mPDF('c', 'A4-L');

                ecell_generate_result_sheet_pdf($conn, $mpdf, $sem_id);

                $sem_details = ecell_sem_details($conn, $sem_id);

                $program_id = $sem_details['program_id'];
                $year_of_joining = $sem_details['year_of_joining'];
                $sem_code_of_joining = $sem_details['sem_code_of_joining'];
                $year = $sem_details['year'];
                $sem_code = $sem_details['sem_code'];

                $program_details = ecell_program_details($conn, $program_id);
                $program_code = $program_details['program_code'];

                $sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);
                $sem_code_name = ecell_sem_code_description($conn, $sem_code);

                $file = $program_code.'_'.$year_of_joining.'_'.$sem_code_of_joining_name.'_'.$year.'_'.$sem_code_name.'_Result_Sheet.pdf';
                $mpdf->Output($file, 'I');
            } else if ($output_format == 'csv') {
                ecell_generate_result_sheet_csv($conn, $sem_id);
                die();
            }
        }
    }

    $TBS->MergeBlock('program', $conn, "SELECT * FROM program WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
    $TBS->MergeBlock('sem1, sem2', $conn, "SELECT * FROM sem_code_description WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
    $TBS->Show();
?>