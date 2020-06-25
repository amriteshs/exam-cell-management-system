<?php


include_once('../../includes/include.php');
require_once('../../includes/validator.php');
require_once('db_helper.php');
include_once('../../includes/session.php');

if (!ecell_sec_session_start()) {
    header("Location: ../../includes/logout.php");
}

if (!ecell_login_check($conn)) {
    header("Location: ../../includes/logout.php");
}

if ($_SESSION['rank'] < $all_ranks['AR']) {
    header("Location: ../home/home.php");
}

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$TBS = new clsTinyButStrong;

$TBS->LoadTemplate('modify_student_details.html');

$success = "";
$msg = "";
$specialError = "";
$errorArray = array();
$errormsg = "";
$disp_form_1 = true;
$disp_form_2 = false;
$info = "";

$type = "";
$showerr = false;
$showform = true;

$photopic = " ";
$photomime = " ";
$signmime = " ";
$signpic = " ";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    foreach ($_POST as $k => $v) {
        if (isset($_POST[$k])) {
            $_POST[$k] = trim(filter_var($v, FILTER_SANITIZE_STRING));
        }
    }

    if (isset($_POST['submit_1'])) {

        $enroll_no = strtoupper($_POST['enroll_no']);
        $_SESSION['edit_for_enroll'] = $enroll_no;
        $disp_form_1 = "";
        $disp_form_2 = true;


        try {
//                $conn1 = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            // set the PDO error mode to exception
//                $conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->exec("set names 'utf8'");
            $sql = "select * from student, program where program.program_id = student.program_id AND enrollment_no = '$enroll_no'";

            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $result1 = $stmt->fetchAll();
            $count = $stmt->rowCount();

            if ($count > 0) {
                $result = $result1[0];
            } else {
                $specialError = "Enrollment Number $enroll_no does not exist.";
                $showform = false;
            }
        } catch (PDOException $e) {
            $specialError = $sql . "<br>" . $e->getMessage();
        }


        if (isset($result)) {

            $mydetails = $result;
            $program_name = $mydetails['program_name'];
            $type = trim($mydetails['program_type']);
            $_SESSION['type'] = $type;

            $student_id = $mydetails['student_id'];
            $_SESSION['edit_for_student_id'] = $student_id;

            $offlineConnection = ecell_get_conn();

            $studentID = $mydetails['student_id'];
            $sql = $offlineConnection->prepare("SELECT media, mime from student_documents where student_id = '$student_id' and category = 'photo' ORDER BY document_id DESC ");
            $sql->execute();

            $result = $sql->fetchAll();
            if ($result) {
                $mime = $result[0][1];
                $media = $result[0][0];

                $photopic = $media;
                $photomime = $mime;
            }

            $sql = $offlineConnection->prepare("SELECT media, mime from student_documents where student_id = '$student_id' and category = 'sign' ORDER BY document_id DESC ");
            $sql->execute();

            $result = $sql->fetchAll();
            if ($result) {
                $mime = $result[0][1];
                $media = $result[0][0];

                $signpic = $media;
                $signmime = $mime;
            }

            $info = "Editing details for student $enroll_no studying $program_name.";

            $jeeOptions = true;
            $ugOptions = true;
            $gateOptions = true;
            $catOptions = true;
            $pgOptions = true;

            if (strcmp($type, "B.Tech.") == 0 || strcmp($type, "DD.5PG") == 0) {
                $ugOptions = false;
                $gateOptions = false;
                $catOptions = false;
                $pgOptions = false;
            } else if (strcmp($type, "M.Tech.") == 0 || strcmp($type, "MT.Ph.D") == 0) {
                $jeeOptions = false;
                $catOptions = false;
                $pgOptions = false;
            } else if (strcmp($type, "MBA") == 0 || strcmp($type, "MB.Ph.D") == 0) {
                $jeeOptions = false;
                $gateOptions = false;
                $pgOptions = false;
            } else if (strcmp($type, "Ph.D(AS)") == 0 || strcmp($type, "Ph.D(ECE)") == 0 || strcmp($type, "Ph.D(IT)") == 0 || strcmp($type, "Ph.D(MS)") == 0) {
                $jeeOptions = false;
                $catOptions = false;
            }

            $program_id = $mydetails['program_id'];
            $program_name = ecell_get_program($program_id, $conn);

            $aadhaar = $mydetails['aadhaar'];

            $first_name = $mydetails['first_name'];
            $middle_name = $mydetails['middle_name'];
            $last_name = $mydetails['last_name'];
            $hindi_name = $mydetails['hindi_name'];
            $birth_place = $mydetails['birth_place'];
            $category_id = $mydetails['category_id'];
            $sub_category = $mydetails['sub_category'];
            $religion_id = $mydetails['religion_id'];
            $gender = $mydetails['gender'];
            $marital_status = $mydetails['marital_status'];
            $area = $mydetails['area'];
            $blood_group = $mydetails['blood_group'];
            $nationality = $mydetails['nationality'];
            $communication_addr = $mydetails['comm_addr'];
            $comm_city = $mydetails['comm_city'];
            $comm_state_id = $mydetails['comm_state_id'];
            $comm_pincode = $mydetails['comm_pincode'];

            $email = $mydetails["comm_email"];
            $_SESSION['email'] = $email;

            $father_first_name = $mydetails['father_first_name'];
            $father_last_name = $mydetails['father_last_name'];
            $father_profession = $mydetails['father_profession'];
            $father_office_addr = $mydetails['father_office_addr'];
            $city2 = $mydetails['father_city'];
            $state_id2 = $mydetails['father_state_id'];
            $pincode2 = $mydetails['father_pincode'];
            if (empty($pincode2)) {
                $pincode2 = "";
            }
            $phone_no2 = $mydetails['father_landline'];
            $email2 = $mydetails['father_email'];
            $mother_first_name = $mydetails['mother_first_name'];
            $mother_last_name = $mydetails['mother_last_name'];
            $mother_profession = $mydetails['mother_profession'];
            $permanent_addr = $mydetails['perm_addr'];
            $city3 = $mydetails['perm_city'];
            $state_id3 = $mydetails['perm_state_id'];
            $pincode3 = $mydetails['perm_pincode'];
            if (empty($pincode3)) {
                $pincode3 = "";
            }
            $phone_no3 = $mydetails['perm_landline'];
            $email3 = $mydetails['perm_email'];
            $local_guardian_name = $mydetails['local_guardian_name'];
            $loca_guardian_addr = $mydetails['local_guardian_addr'];
            $city4 = $mydetails['local_guardian_city'];
            $phone_no4 = $mydetails['local_guardian_landline'];
            $comm_phone_no = $mydetails['comm_mobile_no'];

            $admission_category_id = $mydetails['admission_category_id'];
            $board_id_10 = $mydetails['board_id_10'];
            $marsheek_10 = $mydetails['marksheet_10'];
            $cert_10 = $mydetails['cert_10'];
            $percentage_10 = $mydetails['percentage_10'];

            $board_id_12 = $mydetails['board_id_12'];
            $marksheet_12 = $mydetails['marksheet_12'];
            $cert_12 = $mydetails['cert_12'];
            $percentage_12 = $mydetails['percentage_12'];

            $admit_card = $mydetails['admit_card'];
            $jee_rank_card = $mydetails['jee_rank_card'];
            $jee_roll_no = $mydetails['jee_roll_no'];
            $jee_rank_pos = $mydetails['jee_rank_pos'];
            $jee_seat_allot_letter = $mydetails['jee_seat_allot_letter'];
            $marksheet_grad = $mydetails['marksheet_grad'];
            $degree_grad = $mydetails['degree_grad'];
            $percentage_grad = $mydetails['percentage_grad'];


            $gate_score_card = $mydetails['gate_score_card'];
            $gate_year = $mydetails['gate_year'];
            $gate_score = $mydetails['gate_score'];
            $cat_score_card = $mydetails['cat_score_card'];
            $cat_year = $mydetails['cat_year'];
            $cat_score = $mydetails['cat_score'];
            $marksheet_pg = $mydetails['marksheet_pg'];
            $degree_pg = $mydetails['degree_pg'];
            $percentage_pg = $mydetails['percentage_pg'];


            $termination_date = $mydetails['termination_date'];
            $graduation_date = $mydetails['graduation_date'];

            $transfer_cert = $mydetails['transfer_cert'];
            $university_grad_id = $mydetails['university_grad_id'];
            $character_cert = $mydetails['character_cert'];
            $caste_cert = $mydetails['caste_cert'];
            $ph_cert = $mydetails['ph_cert'];
            $passport = $mydetails['passport'];
            $passport_no = $mydetails['passport_no'];
            $validity_period = $mydetails['passport_expiry'];
            $DASA = $mydetails['DASA'];
            $dasa_country = $mydetails['dasa_country'];
            $remark = $mydetails['remark'];
            $anti_rag_st = $mydetails['anti_rag_st'];
            $med_cert = $mydetails['med_cert'];
            $admission_letter = $mydetails['admission_letter'];
            $dob = $mydetails["dob"];
            $tdob = explode('-', $dob);
            $dob = $tdob[1] . '/' . $tdob[2] . '/' . $tdob[0];

            //fields  added
            $net = $mydetails['net'];
            $net_year = $mydetails['net_year'];
            $net_month = $mydetails['net_month'];
            $net_subject = $mydetails['net_subject'];
            $jrf = $mydetails['jrf'];
            $jrf_year = $mydetails['jrf_year'];
            $jrf_month = $mydetails['jrf_month'];
            $jrf_subject = $mydetails['jrf_subject'];
            $csir = $mydetails['csir'];
            $csir_year = $mydetails['csir_year'];
            $csir_month = $mydetails['csir_month'];
            $csir_subject = $mydetails['csir_subject'];

            $mobile_no2 = $mydetails['parent_mobile_no_1'];
            $mobile_no3 = $mydetails['parent_mobile_no_2'];

            $mobile_no4 = $mydetails['local_guardian_mobile_no'];

            $mcaip = $mydetails['mcaip'];

            //change it
            $state_id4 = $mydetails['local_guardian_state_id'];
            $pincode4 = $mydetails['local_guardian_pincode'];
            $remark = $mydetails['remark'];

            $year = date('Y');

            $campus_name = "Allahabad";
            $sql = "SELECT campus_id FROM campus WHERE campus_name=:campus_name";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':campus_name', $campus_name);
            $stmt->execute();

            $campus_id = $stmt->fetchAll()[0][0];

            $campus_id = $mydetails['campus_id'];

        }

    } 
    else if (isset($_POST['submit_2'])) {

        $type = $_SESSION['type'];

        $jeeOptions = true;
        $ugOptions = true;
        $gateOptions = true;
        $catOptions = true;
        $pgOptions = true;

        if (strcmp($type, "B.Tech.") == 0 || strcmp($type, "DD.5PG") == 0) {
            $ugOptions = false;
            $gateOptions = false;
            $catOptions = false;
            $pgOptions = false;
        } else if (strcmp($type, "M.Tech.") == 0 || strcmp($type, "MT.Ph.D") == 0) {
            $jeeOptions = false;
            $catOptions = false;
            $pgOptions = false;
        } else if (strcmp($type, "MBA") == 0 || strcmp($type, "MB.Ph.D") == 0) {
            $jeeOptions = false;
            $gateOptions = false;
            $pgOptions = false;
        } else if (strcmp($type, "Ph.D(AS)") == 0 || strcmp($type, "Ph.D(ECE)") == 0 || strcmp($type, "Ph.D(IT)") == 0 || strcmp($type, "Ph.D(MS)") == 0) {
            $jeeOptions = false;
            $catOptions = false;
        }

        foreach ($_POST as $k => $v) {
            if (isset($_POST[$k]) && ($k != "hindi_name")) {
                $_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
            }
        }

        $student_id = ecell_get_val('student', 'enrollment_no', $_SESSION['edit_for_enroll'], 'student_id');

        $conn->beginTransaction();

//        echo '<pre>';   print_r($_POST);    echo '</pre>';

        $campus_id = $_POST["campus_id"];
        $first_name = ucwords($_POST["first_name"]);
        $middle_name = ucwords($_POST["middle_name"]);
        $last_name = ucwords($_POST["last_name"]);
        $hindi_name = $_POST["hindi_name"];
        $birth_place = ucwords($_POST["birth_place"]);
        $category_id = $_POST["category_id"];
        $sub_category = $_POST["sub_category"];
        $religion_id = $_POST["religion_id"];
        $gender = $_POST["gender"];
        $marital_status = $_POST["marital_status"];
        $area = $_POST["area"];
        $blood_group = $_POST["blood_group"];
        $nationality = ucwords($_POST["nationality"]);
        $communication_addr = $_POST["communication_addr"];
        $comm_city = ucwords($_POST["comm_city"]);
        $comm_state = $_POST["comm_state_id"];
        $comm_pincode = $_POST["comm_pincode"];
        $comm_phone_no = $_POST["comm_phone_no"];
        $email = $_POST['comm_email'];
        //	unset($_SESSION['email']);
        $father_first_name = ucwords($_POST["father_first_name"]);
        $father_last_name = ucwords($_POST["father_last_name"]);
        $father_profession = ucwords($_POST["father_profession"]);
        $father_office_addr = $_POST["father_office_addr"];
        $city2 = ucwords($_POST["city2"]);
        $state_id2 = $_POST["state_id2"];
        $pincode2 = $_POST["pincode2"];
        $phone_no2 = $_POST["phone_no2"];
        $email2 = $_POST["email2"];
        $mother_first_name = ucwords($_POST["mother_first_name"]);
        $mother_last_name = ucwords($_POST["mother_last_name"]);
        $mother_profession = ucwords($_POST["mother_profession"]);
        $permanent_addr = $_POST["permanent_addr"];
        $city3 = ucwords($_POST["city3"]);
        $state_id3 = $_POST["state_id3"];
        $pincode3 = $_POST["pincode3"];
        $phone_no3 = $_POST["phone_no3"];
        $email3 = $_POST["email3"];
        $local_guardian_name = ucwords($_POST["local_guardian_name"]);
        $local_guardian_addr = $_POST["loca_guardian_addr"];
        $local_guardian_state_id = $_POST["state_id4"];

        $city4 = ucwords($_POST["city4"]);
        $phone_no4 = $_POST["phone_no4"];
        $admission_category_id = $_POST["admission_category_id"];

        //added
        $mobile_no2 = ecell_test_input($_POST["mobile_no2"]);
        $mobile_no3 = ecell_test_input($_POST["mobile_no3"]);
        $mobile_no4 = ecell_test_input($_POST["mobile_no4"]);
        $state_id4 = ecell_test_input($_POST["state_id4"]);
        $pincode4 = ecell_test_input($_POST["pincode4"]);

        if ($jeeOptions) {
            $admit_card = $_POST["admit_card"];
            $jee_rank_card = $_POST["jee_rank_card"];
            $jee_roll_no = $_POST["jee_roll_no"];
            $jee_rank_pos = $_POST["jee_rank_pos"];
            $jee_seat_allot_letter = $_POST["jee_seat_allot_letter"];
        } else {
            $admit_card = 0;
            $jee_rank_card = 0;
            $jee_roll_no = 0;
            $jee_rank_pos = 0;
            $jee_seat_allot_letter = 0;
        }
        $marsheek_10 = $_POST["marsheek_10"];
        $cert_10 = $_POST["cert_10"];
        $percentage_10 = $_POST["percentage_10"];
        $board_id_10 = $_POST["board_id_10"];
        $marksheet_12 = $_POST["marksheet_12"];
        $cert_12 = $_POST["cert_12"];
        $percentage_12 = $_POST["percentage_12"];
        $board_id_12 = $_POST["board_id_12"];

        if ($ugOptions) {
            $marksheet_grad = $_POST["marksheet_grad"];
            $degree_grad = $_POST["degree_grad"];
            $percentage_grad = $_POST["percentage_grad"];
            $university_grad_id = $_POST["university_grad_id"];
        } else {
            $marksheet_grad = 0;
            $degree_grad = 0;
            $percentage_grad = 0;
            $university_grad_id = 0;
        }

        if ($pgOptions) {
            $marksheet_pg = $_POST["marksheet_pg"];
            $degree_pg = $_POST["degree_pg"];
            $percentage_pg = $_POST["percentage_pg"];
            $university_pg_id = $_POST["university_pg_id"];
            $net = $_POST['net'];
            if ($net) {
                $net_year = $_POST['net_year'];
                $net_month = $_POST['net_month'];
                $net_subject = $_POST['net_subject'];
            } else {
                $net_year = 0;
                $net_month = 0;
                $net_subject = " ";
            }

            $jrf = $_POST['jrf'];
            if ($jrf) {
                $jrf_year = $_POST['jrf_year'];
                $jrf_month = $_POST['jrf_month'];
                $jrf_subject = $_POST['jrf_subject'];
            } else {
                $jrf_year = 0;
                $jrf_month = 0;
                $jrf_subject = " ";
            }

            $csir = $_POST['csir'];
            if ($csir) {
                $csir_year = $_POST['csir_year'];
                $csir_month = $_POST['csir_month'];
                $csir_subject = $_POST['csir_subject'];
            } else {
                $csir_year = 0;
                $csir_month = 0;
                $csir_subject = " ";
            }
        } else {
            $marksheet_pg = 0;
            $degree_pg = 0;
            $percentage_pg = 0;
            $university_pg_id = 0;
            $net = 0;
            $jrf = 0;
            $csir = 0;
            $net_year = 0;
            $net_month = 0;
            $net_subject = "NA";
            $jrf_year = 0;
            $jrf_month = 0;
            $jrf_subject = "NA";
            $csir_year = 0;
            $csir_month = 0;
            $csir_subject = "NA";
        }

        if ($gateOptions) {
            $gate_score_card = $_POST["gate_score_card"];
            $gate_year = $_POST["gate_year"];
            $gate_score = $_POST["gate_score"];
        } else {
            $gate_score_card = 0;
            $gate_year = 0;
            $gate_score = 0;
        }

        if ($catOptions) {
            $cat_score_card = $_POST["cat_score_card"];
            $cat_year = $_POST["cat_year"];
            $cat_score = $_POST["cat_score"];
        } else {
            $cat_score_card = 0;
            $cat_year = 0;
            $cat_score = 0;
        }

        $graduation_date = $_POST["graduation_date"];
        $termination_date = $_POST["termination_date"];

        $transfer_cert = $_POST["transfer_cert"];
        $character_cert = $_POST["character_cert"];
        $caste_cert = $_POST["caste_cert"];
        $ph_cert = $_POST["ph_cert"];
        $passport = $_POST["passport"];
        $migration_cert = $_POST["migration_cert"];
        $mcaip = $_POST["mcaip"];
        $passport_no = $_POST["passport_no"];
        $validity_period = $_POST["validity_period"];
        $validity_period = $validity_period . '-01-01';
        $DASA = $_POST["DASA"];
        $remark = $_POST["remark"];
        $anti_rag_st = $_POST["anti_rag_st"];
        $med_cert = $_POST["med_cert"];
        $admission_letter = $_POST["admission_letter"];
        $dob = $_POST["dob"];
        $aadhaar = $_POST['aadhaar'];
        $dasa_country = $_POST['dasa_country'];

        //fields added on 16 july
        // $net          = $_POST["net"];
        // $net_year     = $_POST["net_year"];
        // $net_month    = $_POST["net_month"];


        if (count($errorArray) == 0) {

            if ($_SESSION['rank'] < $all_ranks['AR']) {
                header("Location: ../home/home.php");
            }

            $tdob = explode('/', $dob);
            $dob = $tdob[2] . '-' . $tdob[0] . '-' . $tdob[1];

            try {

                $sql = "UPDATE `student` SET `campus_id`=:campus_id,`first_name`=:first_name,`middle_name`=:middle_name,`last_name`=:last_name,`hindi_name`=:hindi_name, `dob`=:dob,`birth_place`=:birth_place,`category_id`=:category_id,`sub_category`=:sub_category,`religion_id`=:religion_id,`gender`=:gender,`marital_status`=:marital_status,`area`=:area,`blood_group`=:blood_group,`nationality`=:nationality,`comm_addr`=:comm_addr,`comm_city`=:comm_city,`comm_state_id`=:comm_state_id,`comm_pincode`=:comm_pincode,`comm_mobile_no`=:comm_mobile_no,`comm_email`=:comm_email,`father_first_name`=:father_first_name,`father_last_name`=:father_last_name,`father_profession`=:father_profession,
`father_office_addr`=:father_office_addr,`father_city`=:father_city,`father_state_id`=:father_state_id,`father_pincode`=:father_pincode,
`father_landline`=:father_landline,`father_email`=:father_email,
`mother_first_name`=:mother_first_name,`mother_last_name`=:mother_last_name,`mother_profession`=:mother_profession,`perm_addr`=:perm_addr,`parent_mobile_no_1`=:parent_mobile_no_1,`parent_mobile_no_2`=:parent_mobile_no_2,`perm_city`=:perm_city,`perm_state_id`=:perm_state_id,`perm_pincode`=:perm_pincode,`perm_landline`=:perm_landline,`perm_email`=:perm_email,
`local_guardian_name`=:local_guardian_name,`local_guardian_addr`=:local_guardian_addr,`local_guardian_city`=:local_guardian_city,`local_guardian_state_id`=:local_guardian_state_id,`local_guardian_pincode`=:local_guardian_pincode,`local_guardian_landline`=:local_guardian_landline,`local_guardian_mobile_no`=:local_guardian_mobile_no,
`admission_category_id`=:admission_category_id,`admit_card`=:admit_card,`jee_rank_card`=:jee_rank_card,`jee_roll_no`=:jee_roll_no,`jee_rank_pos`=:jee_rank_pos,
`jee_seat_allot_letter`=:jee_seat_allot_letter,`marksheet_10`=:marksheet_10,`cert_10`=:cert_10,`percentage_10`=:percentage_10,`board_id_10`=:board_id_10,`board_10_passing_state_id`=:board_10_passing_state_id,`marksheet_12`=:marksheet_12,`cert_12`=:cert_12,`percentage_12`=:percentage_12,`board_id_12`=:board_id_12,`board_12_passing_state_id`=:board_12_passing_state_id,
`marksheet_grad`=:marksheet_grad,`degree_grad`=:degree_grad,`percentage_grad`=:percentage_grad,`university_grad_id`=:university_grad_id,`marksheet_pg`=:marksheet_pg,`degree_pg`=:degree_pg,`percentage_pg`=:percentage_pg,`university_pg_id`=:university_pg_id,`gate_score_card`=:gate_score_card,`gate_year`=:gate_year,`gate_score`=:gate_score,
`cat_score_card`=:cat_score_card,`cat_year`=:cat_year,`cat_score`=:cat_score,`csir`=:csir,`csir_month`=:csir_month,`csir_year`=:csir_year,`csir_subject`=:csir_subject,`net`=:net,`net_month`=:net_month,`net_year`=:net_year,`net_subject`=:net_subject,`jrf`=:jrf,`jrf_month`=:jrf_month,`jrf_year`=:jrf_year,`jrf_subject`=:jrf_subject,
`transfer_cert`=:transfer_cert,`migration_cert`=:migration_cert,`character_cert`=:character_cert,`caste_cert`=:caste_cert,`ph_cert`=:ph_cert,
`passport`=:passport,`passport_no`=:passport_no,`passport_expiry`=:passport_expiry,`mcaip`=:mcaip, `graduation_date`=:grad_date, `termination_date`=:ter_date,
`DASA`=:DASA,`dasa_country`=:dasa_country,`remark`=:remark,
`anti_rag_st`=:anti_rag_st, `med_cert`=:med_cert,`admission_letter`=:admission_letter, `aadhaar`=:aadhaar WHERE student_id=$student_id";

                $ac_on = "Updated details for student having enrollment number " . $_SESSION['edit_for_enroll'];
                $s_i = $_SESSION['staff_id'];
                $r = $_SESSION['rank'];
                $tn = 'student';

                $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

                $queryStudent = $conn->prepare($sql);

                $board_10_passing_state_id = 0;
                $board_12_passing_state_id = 0;

                $queryStudent->bindParam(':campus_id', $campus_id);
                $queryStudent->bindParam(':first_name', $first_name);
                $queryStudent->bindParam(':middle_name', $middle_name);
                $queryStudent->bindParam(':last_name', $last_name);
                $queryStudent->bindParam(':hindi_name', $hindi_name);
                $queryStudent->bindParam(':dob', $dob);
                $queryStudent->bindParam(':birth_place', $birth_place);
                $queryStudent->bindParam(':category_id', $category_id);
                $queryStudent->bindParam(':sub_category', $sub_category);
                $queryStudent->bindParam(':religion_id', $religion_id);
                $queryStudent->bindParam(':gender', $gender);
                $queryStudent->bindParam(':marital_status', $marital_status);
                $queryStudent->bindParam(':area', $area);
                $queryStudent->bindParam(':blood_group', $blood_group);
                $queryStudent->bindParam(':nationality', $nationality);
                $queryStudent->bindParam(':comm_addr', $communication_addr);
                $queryStudent->bindParam(':comm_city', $comm_city);
                $queryStudent->bindParam(':comm_state_id', $comm_state);
                $queryStudent->bindParam(':comm_pincode', $comm_pincode);
                $queryStudent->bindParam(':comm_mobile_no', $comm_phone_no);
                $queryStudent->bindParam(':comm_email', $email);
                $queryStudent->bindParam(':father_first_name', $father_first_name);
                $queryStudent->bindParam(':father_last_name', $father_last_name);
                $queryStudent->bindParam(':father_profession', $father_profession);
                $queryStudent->bindParam(':father_office_addr', $father_office_addr);
                $queryStudent->bindParam(':father_city', $city2);
                $queryStudent->bindParam(':father_state_id', $state_id2);
                $queryStudent->bindParam(':father_pincode', $pincode2);
                $queryStudent->bindParam(':father_landline', $phone_no2);
                $queryStudent->bindParam(':father_email', $email2);
                $queryStudent->bindParam(':mother_first_name', $mother_first_name);
                $queryStudent->bindParam(':mother_last_name', $mother_last_name);
                $queryStudent->bindParam(':mother_profession', $mother_profession);
                $queryStudent->bindParam(':perm_addr', $permanent_addr);
                $queryStudent->bindParam(':parent_mobile_no_1', $mobile_no2);
                $queryStudent->bindParam(':parent_mobile_no_2', $mobile_no3);
                $queryStudent->bindParam(':perm_city', $city3);
                $queryStudent->bindParam(':perm_state_id', $state_id3);
                $queryStudent->bindParam(':perm_pincode', $pincode3);
                $queryStudent->bindParam(':perm_landline', $phone_no3);
                $queryStudent->bindParam(':perm_email', $email3);
                $queryStudent->bindParam(':local_guardian_name', $local_guardian_name);
                $queryStudent->bindParam(':local_guardian_addr', $local_guardian_addr);
                $queryStudent->bindParam(':local_guardian_city', $city4);
                $queryStudent->bindParam(':local_guardian_state_id', $local_guardian_state_id);
                $queryStudent->bindParam(':local_guardian_pincode', $pincode4);
                $queryStudent->bindParam(':local_guardian_landline', $phone_no4);
                $queryStudent->bindParam(':local_guardian_mobile_no', $mobile_no4);

                $queryStudent->bindParam(':admission_category_id', $admission_category_id);
                $queryStudent->bindParam(':admit_card', $admit_card);
                $queryStudent->bindParam(':jee_rank_card', $jee_rank_card);
                $queryStudent->bindParam(':jee_roll_no', $jee_roll_no);
                $queryStudent->bindParam(':jee_rank_pos', $jee_rank_pos);
                $queryStudent->bindParam(':jee_seat_allot_letter', $jee_seat_allot_letter);
                $queryStudent->bindParam(':marksheet_10', $marsheek_10);
                $queryStudent->bindParam(':cert_10', $cert_10);
                $queryStudent->bindParam(':percentage_10', $percentage_10);
                $queryStudent->bindParam(':board_id_10', $board_id_10);
                $queryStudent->bindParam(':board_10_passing_state_id', $board_10_passing_state_id);
                $queryStudent->bindParam(':marksheet_12', $marksheet_12);
                $queryStudent->bindParam(':cert_12', $cert_12);
                $queryStudent->bindParam(':percentage_12', $percentage_12);
                $queryStudent->bindParam(':board_id_12', $board_id_12);
                $queryStudent->bindParam(':board_12_passing_state_id', $board_12_passing_state_id);
                $queryStudent->bindParam(':marksheet_grad', $marksheet_grad);
                $queryStudent->bindParam(':degree_grad', $degree_grad);
                $queryStudent->bindParam(':percentage_grad', $percentage_grad);
                $queryStudent->bindParam(':university_grad_id', $university_grad_id);
                $queryStudent->bindParam(':marksheet_pg', $marksheet_pg);
                $queryStudent->bindParam(':degree_pg', $degree_pg);
                $queryStudent->bindParam(':percentage_pg', $percentage_pg);
                $queryStudent->bindParam(':university_pg_id', $university_pg_id);
                $queryStudent->bindParam(':gate_score_card', $gate_score_card);
                $queryStudent->bindParam(':gate_year', $gate_year);
                $queryStudent->bindParam(':gate_score', $gate_score);
                $queryStudent->bindParam(':cat_score_card', $cat_score_card);
                $queryStudent->bindParam(':cat_year', $cat_year);
                $queryStudent->bindParam(':cat_score', $cat_score);


                $queryStudent->bindParam(':grad_date', $graduation_date);
                $queryStudent->bindParam(':ter_date', $termination_date);

                $queryStudent->bindParam(':csir', $csir);
                $queryStudent->bindParam(':csir_year', $csir_year);
                $queryStudent->bindParam(':csir_month', $csir_month);
                $queryStudent->bindParam(':csir_subject', $csir_subject);

                $queryStudent->bindParam(':net', $net);
                $queryStudent->bindParam(':net_year', $net_year);
                $queryStudent->bindParam(':net_month', $net_month);
                $queryStudent->bindParam(':net_subject', $net_subject);

                $queryStudent->bindParam(':jrf', $jrf);
                $queryStudent->bindParam(':jrf_year', $jrf_year);
                $queryStudent->bindParam(':jrf_month', $jrf_month);
                $queryStudent->bindParam(':jrf_subject', $jrf_subject);

                $queryStudent->bindParam(':transfer_cert', $transfer_cert);
                $queryStudent->bindParam(':migration_cert', $migration_cert);
                $queryStudent->bindParam(':character_cert', $character_cert);
                $queryStudent->bindParam(':caste_cert', $caste_cert);
                $queryStudent->bindParam(':ph_cert', $ph_cert);

                $queryStudent->bindParam(':passport', $passport);
                $queryStudent->bindParam(':passport_no', $passport_no);
                $queryStudent->bindParam(':passport_expiry', $validity_period);
                $queryStudent->bindParam(':mcaip', $mcaip);

                $queryStudent->bindParam(':DASA', $DASA);
                $queryStudent->bindParam(':dasa_country', $dasa_country);
                $queryStudent->bindParam(':remark', $remark);

                $queryStudent->bindParam(':anti_rag_st', $anti_rag_st);
                $queryStudent->bindParam(':med_cert', $med_cert);
                $queryStudent->bindParam(':admission_letter', $admission_letter);
                $queryStudent->bindParam(':aadhaar', $aadhaar);

                $queryStudent->execute();

                $tdob = explode('-', $dob);
                $dob = $tdob[1] . '/' . $tdob[2] . '/' . $tdob[0];

                $conn->commit();

                $success = "Details Updated for " . $_SESSION['edit_for_enroll'] . " successfully.";

            } catch (PDOException $e) {
                echo $sql . "<br>" . $e->getMessage();
                $conn->rollBack();
                //exit;
            }
        } else {
            $showerr = true;
            $TBS->MergeBlock('errBlock', $errorArray);
        }

    } 
    else if (isset($_POST['finalupload'])) {

        $enroll_no = $_SESSION['edit_for_enroll'];
        $studentID = $_SESSION['edit_for_student_id'];

        if ((!file_exists($_FILES['usersign']['tmp_name']) || !is_uploaded_file($_FILES['usersign']['tmp_name'])) && (!file_exists($_FILES['userphoto']['tmp_name']) || !is_uploaded_file($_FILES['userphoto']['tmp_name']))) {
            $errorArray[] = 'Please upload atleast one photograph.';
        }

        if ((file_exists($_FILES['userphoto']['tmp_name']) && is_uploaded_file($_FILES['userphoto']['tmp_name']))) {
            if ($_FILES["userphoto"]["error"] > 0) {
                $errorArray[] = "Error: Please upload a valid photo file for photograph";
            } else {
                $tmpNamePhoto = $_FILES['userphoto']['tmp_name'];
                $fileSizePhoto = $_FILES['userphoto']['size'];
                if ($fileSizePhoto > 0 && $fileSizePhoto < 256000) {
                    $fileTypePhoto = $_FILES['userphoto']['type'];
                    if ($fileTypePhoto == "image/jpeg" || $fileTypePhoto == "image/png") {
                        $fp = fopen($tmpNamePhoto, 'r');
                        $contentPhoto = fread($fp, filesize($tmpNamePhoto));
                        // $contentPhoto = addslashes($contentPhoto);
                        $contentPhoto = base64_encode($contentPhoto);
                        fclose($fp);
                    } else {
                        $errorArray[] = "Error in Photo : Please upload file of specified type.";
                    }
                } else {
                    $errorArray[] = "Error in Photo : Please upload file of specified size.";
                }
            }

            if (count($errorArray) == 0) {

                $sql = "SELECT COUNT(*) as count FROM student_documents WHERE student_id=:studentID AND category='photo'";
                $sql = $conn->prepare($sql);
                $sql->bindParam(':studentID', $studentID);
                $sql->execute();

                if ($sql->fetch()['count']) {
                    $sql = "UPDATE student_documents SET media=:media, mime=:mime WHERE student_id=:studentID AND category='photo'";
                    $ac_on = "Updated student photograph for student " . $enroll_no;
                    $s_i = $_SESSION['staff_id'];
                    $r = $_SESSION['rank'];
                    $tn = 'student_documents';
                    $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

                    $sql = $conn->prepare($sql);
                    $sql->bindParam(':media', $contentPhoto, PDO::PARAM_LOB);
                    $sql->bindParam(':mime', $fileTypePhoto);
                    $sql->bindParam(':studentID', $studentID);
                    $sql->execute();
                } else {
                    $sql = "INSERT INTO `student_documents`(`document_id`, `student_id`, `category`, `description`, `media`, `mime`, `status_value_id`, `log_id`) VALUES (NULL, :studentID, :category, :description, :media, :mime, :status_value_id, :log_id)";
                    $ac_on = "Updated student photograph for student " . $enroll_no;
                    $s_i = $_SESSION['staff_id'];
                    $r = $_SESSION['rank'];
                    $tn = 'student_documents';
                    $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

                    $sql = $conn->prepare($sql);
                    $sql->bindParam(':studentID', $studentID);
                    $category = 'photo';
                    $sql->bindParam(':category', $category);
                    $sql->bindParam(':media', $contentPhoto, PDO::PARAM_LOB);
                    $description = 'Photo';
                    $sql->bindParam(':description', $description);
                    $sql->bindParam(':mime', $fileTypePhoto);
                    $status_value_id = ecell_get_status('on');
                    $sql->bindParam(':status_value_id', $status_value_id);
                    $sql->bindParam(':log_id', $log_id);
                    $sql->execute();
                }
                $success = "Photograph uploaded successfully.";
            }
        }

        if ((file_exists($_FILES['usersign']['tmp_name']) && is_uploaded_file($_FILES['usersign']['tmp_name']))) {
            if ($_FILES["usersign"]["error"] > 0) {
                $errorArray[] = "Error: Please upload a valid signature file.";
            } else {
                $tmpNameSign = $_FILES['usersign']['tmp_name'];
                $fileSizeSign = $_FILES['usersign']['size'];
                if ($fileSizeSign > 0 && $fileSizeSign < 256000) {
                    $fileTypeSign = $_FILES['usersign']['type'];
                    if ($fileTypeSign == "image/jpeg" || $fileTypeSign == "image/png") {
                        $fp = fopen($tmpNameSign, 'r');
                        $contentSign = fread($fp, filesize($tmpNameSign));
                        // $contentSign = addslashes($contentSign);
                        $contentSign = base64_encode($contentSign);
                        fclose($fp);
                    } else {
                        $errorArray[] = "Error in signature : Please upload file of specified type.";
                    }
                } else {
                    $errorArray[] = "Error in signature : Please upload file of specified size.";
                }
            }

            if (count($errorArray) == 0) {

                $sql = "SELECT COUNT(*) as count FROM student_documents WHERE student_id=:studentID AND category='sign'";
                $sql = $conn->prepare($sql);
                $sql->bindParam(':studentID', $studentID);
                $sql->execute();

                if ($sql->fetch()['count']) {
                    $sql = "UPDATE student_documents SET media=:media, mime=:mime WHERE student_id=:studentID AND category='sign'";
                    $ac_on = "Updated student signatue for student " . $enroll_no;
                    $s_i = $_SESSION['staff_id'];
                    $r = $_SESSION['rank'];
                    $tn = 'student_documents';
                    $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

                    $sql = $conn->prepare($sql);
                    $sql->bindParam(':media', $contentSign, PDO::PARAM_LOB);
                    $sql->bindParam(':mime', $fileTypeSign);
                    $sql->bindParam(':studentID', $studentID);
                    $sql->execute();
                } else {
                    $sql = "INSERT INTO `student_documents`(`document_id`, `student_id`, `category`, `description`, `media`, `mime`, `status_value_id`, `log_id`) VALUES (NULL, :studentID, :category, :description, :media, :mime, :status_value_id, :log_id)";
                    $ac_on = "Updated student photograph for student " . $enroll_no;
                    $s_i = $_SESSION['staff_id'];
                    $r = $_SESSION['rank'];
                    $tn = 'student_documents';
                    $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

                    $sql = $conn->prepare($sql);
                    $sql->bindParam(':studentID', $studentID);
                    $category = 'sign';
                    $sql->bindParam(':category', $category);
                    $sql->bindParam(':media', $contentSign, PDO::PARAM_LOB);
                    $description = 'Signature';
                    $sql->bindParam(':description', $description);
                    $sql->bindParam(':mime', $fileTypeSign);
                    $status_value_id = ecell_get_status('on');
                    $sql->bindParam(':status_value_id', $status_value_id);
                    $sql->bindParam(':log_id', $log_id);
                    $sql->execute();
                }

                if ($success != '') {
                    $success .= "\n";
                }
                $success .= "Sign uploaded successfully.";

            }
        }

        if (count($errorArray) != 0) {
            $showError = true;
            foreach ($errorArray as $error) {
                $errormsg .= $error . "\n";
            }
        }

    }
}

$month_array = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
$blood_groups = array('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-', 'NA');

$netSubjects = array("Chemical Sciences (Analytical Chemistry)", "Chemical Sciences (Inorganic Chemistry)", "Chemical Sciences (Organic Chemistry)", "Chemical Sciences (Physical Chemistry)", "Chemical Sciences (Others)", "Earth Sciences (Geology)", "Earth Sciences (Geophysics)", "Earth Sciences (Meteorology)", "Earth Sciences (Oceanography)", "Earth Sciences (Physical Geography)", "Earth Sciences (Others)", "Life Sciences (Biochemistry)", "Life Sciences (Biodiversity and Taxonomy)", "Life Sciences (Biotechnology)", "Life Sciences (Botany)", "Life Sciences (Genetics)", "Life Sciences (Microbiology)", "Life Sciences (Physiology)", "Life Sciences (Zoology)", "Life Sciences (Others)", "Mathematical Sciences (Applied Mathematics)", "Mathematical Sciences (Pure Mathematics)", "Mathematical Sciences (Statistics)", "Mathematical Sciences (Others)", "Physical Sciences (Atomic and Molecular Physics)", "Physical Sciences (Classical Dynamics)", "Physical Sciences (Condensed Matter Physics)", "Physical Sciences (Electromagnetics)", "Physical Sciences (Experimental Design)", "Physical Sciences (Electronics)", "Physical Sciences (Nuclear and Particle Physics)", "Physical Sciences (Quantum Physics)", "Physical Sciences (Thermodynamics)", "Physical Sciences (Others)");
$csirSubjects = array("Chemical Sciences ", "Earth Sciences ", "Life Sciences ", "Mathematical Sciences ", "Physical Sciences ");

$TBS->MergeBlock('netMonthBlk, jrfMonthBlk, csirMonthBlk', $month_array);
$TBS->MergeBlock('bloodgroupBlk', $blood_groups);
$TBS->MergeBlock('netSubjectBlk, jrfSubjectBlk', $netSubjects);
$TBS->MergeBlock('csirSubjectBlk', $csirSubjects);

$TBS->MergeBlock('genderBlk', array('Male', 'Female', 'Other'));

$TBS->MergeBlock('board12Blk', $conn, 'SELECT * from board');
$TBS->MergeBlock('board10Blk', $conn, 'SELECT * from board');
$TBS->MergeBlock('program', $conn, 'SELECT * FROM program');
$TBS->MergeBlock('religion', $conn, 'SELECT * FROM religion');
$TBS->MergeBlock('acategoryblk', $conn, 'SELECT * FROM student_category');
$TBS->MergeBlock('category', $conn, 'SELECT * FROM student_category');
$TBS->MergeBlock('campus', $conn, 'SELECT * FROM campus WHERE campus_id != 2');
$TBS->MergeBlock('universityBlk, PGUniversityBlk', $conn, 'SELECT * FROM universities');
$TBS->MergeBlock('state, state2, state3, state4', $conn, 'SELECT * FROM state');
$TBS->MergeBlock('sem_code_description', $conn, 'SELECT * FROM sem_code_description');

$TBS->MergeBlock('errBlock', $errorArray);

$TBS->Show();

?>