<?php
    include_once('../../includes/include.php');

    set_time_limit(600);

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
    $TBS->LoadTemplate('cumulative_sgpi_csv.html');

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

        ecell_generate_cumulative_sgpi_csv($conn, $program_id, $year_of_joining, $sem_code_of_joining);
        die();
    }

    $TBS->MergeBlock('program', $conn, "SELECT * FROM program WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
    $TBS->MergeBlock('sem', $conn, "SELECT * FROM sem_code_description WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
    $TBS->Show();
?>