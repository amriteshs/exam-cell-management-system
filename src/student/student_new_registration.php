<?php

include_once('../../includes/include.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('student_application.html');

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$msg = "";

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "federated_online";

$first_name = "";
$middle_name = "";
$last_name = "";
$hindi_name = "";
$birth_place = "";
$category_id = "";
$sub_category = "";
$religion_id = "";
$gender = "";
$marital_status = "";
$area = "";
$blood_group = "";
$nationality = "";
$communication_addr = "";
$comm_city = "";
$comm_state_id = "";
$comm_pincode = "";
$email = "";
$father_first_name = "";
$father_last_name = "";
$father_profession = "";
$father_office_addr = "";
$city2 = "";
$state_id2 = "";
$pincode2 = "";
$phone_no2 = "";
$email2 = "";
$mother_first_name = "";
$mother_last_name = "";
$mother_profession = "";
$permanent_addr = "";
$city3 = "";
$state_id3 = "";
$pincode3 = "";
$phone_no3 = "";
$email3 = "";
$local_guardian_name = "";
$loca_guardian_addr = "";
$city4 = "";
$phone_no4 = "";
$comm_phone_no = "";
$admission_category_id = "";
$marsheek_10 = "";
$cert_10 = "";
$percentage_10 = "";
$board_id_10 = "";
$marksheet_12 = "";
$cert_12 = "";
$percentage_12 = "";
$board_id_12 = "";
$admit_card = "";
$jee_rank_card = "";
$jee_roll_no = "";
$jee_rank_pos = "";
$jee_seat_allot_letter = "";
$marksheet_grad = "";
$degree_grad = "";
$percentage_grad = "";
$university_grad_id = "";
$gate_score_card = "";
$gate_year = "";
$gate_score = "";
$cat_score_card = "";
$cat_year = "";
$cat_score = "";
$marksheet_pg = "";
$degree_pg = "";
$percentage_pg = "";
$university_pg_id = "";
$tc = "";
$character_cert = "";
$caste_cert = "";
$ph_cert = "";
$passport = "";
$passport_no = "";
$validity_period = "";
$DASA = "";
$remark = "";
$anti_rag_st = "";
$med_cert = "";
$muslim_minority = "";
$other_minority = "";
$admission_letter = "";
$dob = "";
$aadhaar = "";
$hostel_no = "";
$hostel_room = "";
$dasa_country = "";
$admission_withdrawal = "";
$dob = "";

$jee_rank_card_yes = "";
$jee_rank_card_no  = "";
$jee_rank_card_yes = "";
$jee_rank_card_no  = "";        
$jee_seat_allot_letter_yes = "";
$jee_seat_allot_letter_no  = "";       
$jee_seat_allot_letter_yes = "";
$jee_seat_allot_letter_no  = "";        
$marsheek_10_yes = "";
$marsheek_10_no  = "";
$marsheek_10_yes = "";
$marsheek_10_no  = "";
$cert_10_yes = "";
$cert_10_no  = "";       
$cert_10_yes = "";
$cert_10_no  = "";       
$marksheet_12_yes = "";
$marksheet_12_no  = "";       
$marksheet_12_yes = "";
$marksheet_12_no  = "";
$cert_12_yes = "";
$cert_12_no  = "";
$cert_12_yes = "";
$cert_12_no  = "";
$marksheet_grad_yes = "";
$marksheet_grad_no  = "";
$marksheet_grad_yes = "";
$marksheet_grad_no  = "";
$degree_grad_yes = "";
$degree_grad_no  = "";
$degree_grad_yes = "";
$degree_grad_no  = "";
$marksheet_pg_yes = "";
$marksheet_pg_no  = "";
$marksheet_pg_yes = "";
$marksheet_pg_no  = "";        
$degree_pg_yes = "";
$degree_pg_no  = "";
$degree_pg_yes = "";
$degree_pg_no  = "";       
$gate_score_card_yes = "";
$gate_score_card_no  = "";       
$gate_score_card_yes = "";
$gate_score_card_no  = "";      
$cat_score_card_yes = "";
$cat_score_card_no  = "";      
$cat_score_card_yes = "";
$cat_score_card_no  = "";      
$tc_yes = "";
$tc_no  = "";        
$tc_yes = "";
$tc_no  = "";       
$character_cert_yes = "";
$character_cert_no  = "";       
$character_cert_yes = "";
$character_cert_no  = "";       
$caste_cert_yes = "";
$caste_cert_no  = "";      
$caste_cert_yes = "";
$caste_cert_no  = "";      
$passport_yes = "";
$passport_no  = "";
$passport_yes = "";
$passport_no  = "";      
$DASA_yes = "";
$DASA_no  = "";       
$DASA_yes = "";
$DASA_no  = "";
$anti_rag_st_yes = "";
$anti_rag_st_no  = "";      
$anti_rag_st_yes = "";
$anti_rag_st_no  = "";

$marital_status_yes = "";
$marital_status_no  = "";
$marital_status_yes = "";
$marital_status_no  = "";
$admit_card_yes = "";
$admit_card_no  = "";
$admit_card_yes = "";
$admit_card_no  = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach ($_POST as $k => $v) {
		if (isset($_POST[$k])) {
			$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
		}
	}

	if (isset($_POST["registered_email"])) {
		$registered_email = $_POST["registered_email"];
		if (!filter_var($registered_email, FILTER_VALIDATE_EMAIL)) {
			header("Location: offline_registration_error.php");
		}
		try {
			$conn1 = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			// set the PDO error mode to exception
			$conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql  = "select * from applicationform where email1 = '".$registered_email."' order by registration_timestamp desc;";

			$stmt = $conn1->prepare($sql);
			$stmt->execute();
			$count   = $stmt->rowCount();
			$result1 = $stmt->fetchAll();

			if ($count > 0) {
				$result = $result1[0];
			} else {
				echo "Entry is not found in database";
				exit;
			}
		}
		catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}


		$row = $result;

		$program_id     = $row["program_id"];
		$type           = $row["type"];
		$first_name     = $row["first_name"];
		$middle_name    = $row["middle_name"];
		$last_name      = $row["last_name"];
		$hindi_name     = $row["hindi_name"];
		$birth_place    = $row["birth_place"];
		$category_id    = $row["category_id"];
		$sub_category   = $row["sub_category"];
		$religion_id    = $row["religion_id"];
		$gender         = $row["gender"];
		$marital_status = $row["marital_status"];
		if ($marital_status == 0) {
			$marital_status_yes = "checked";
			$marital_status_no  = "";
		} else {
			$marital_status_yes = "";
			$marital_status_no  = "checked";
		}
		$area                  = $row["area"];
		$blood_group           = $row["blood_group"];
		$nationality           = $row["nationality"];
		$communication_addr    = $row["communication_addr"];
		$comm_city             = $row["city1"];
		$comm_state_id         = $row["state_id1"];
		$comm_pincode          = $row["pincode1"];
		$email                 = $row["email1"];
		$_SESSION['email'] = $email;
		$father_first_name     = $row["father_first_name"];
		$father_last_name      = $row["father_last_name"];
		$father_profession     = $row["father_profession"];
		$father_office_addr    = $row["father_office_addr"];
		$city2                 = $row["city2"];
		$state_id2             = $row["state_id2"];
		$pincode2              = $row["pincode2"];
		$phone_no2             = $row["phone_no2"];
		$email2                = $row["email2"];
		$mother_first_name     = $row["mother_first_name"];
		$mother_last_name      = $row["mother_last_name"];
		$mother_profession     = $row["mother_profession"];
		$permanent_addr        = $row["permanent_addr"];
		$city3                 = $row["city3"];
		$state_id3             = $row["state_id3"];
		$pincode3              = $row["pincode3"];
		$phone_no3             = $row["phone_no3"];
		$email3                = $row["email3"];
		$local_guardian_name   = $row["local_guardian_name"];
		$loca_guardian_addr    = $row["loca_guardian_addr"];
		$city4                 = $row["city4"];
		$phone_no4             = $row["phone_no4"];
		$comm_phone_no         = $row["phone_no1"];
		$admission_category_id = $row["admission_category_id"];
		$marsheek_10           = $row["marsheek_10"];
		$cert_10               = $row["cert_10"];
		$percentage_10         = $row["percentage_10"];
		$board_id_10           = $row["board_id_10"];
		$marksheet_12          = $row["marksheet_12"];
		$cert_12               = $row["cert_12"];
		$percentage_12         = $row["percentage_12"];
		$board_id_12           = $row["board_id_12"];
		$admit_card            = $row["admit_card"];
		if ($admit_card == 0) {
			$admit_card_yes = "checked";
			$admit_card_no  = "";
		} else {
			$admit_card_yes = "";
			$admit_card_no  = "checked";
		}
		$jee_rank_card         = $row["jee_rank_card"];
		$jee_roll_no           = $row["jee_roll_no"];
		$jee_rank_pos          = $row["jee_rank_pos"];
		$jee_seat_allot_letter = $row["jee_seat_allot_letter"];
		$marksheet_grad        = $row["marksheet_grad"];
		$degree_grad           = $row["degree_grad"];
		$percentage_grad       = $row["percentage_grad"];
		$university_grad_id    = $row["university_grad_id"];
		$gate_score_card       = $row["gate_score_card"];
		$gate_year             = $row["gate_year"];
		$gate_score            = $row["gate_score"];
		$cat_score_card        = $row["cat_score_card"];
		$cat_year              = $row["cat_year"];
		$cat_score             = $row["cat_score"];
		$marksheet_pg          = $row["marksheet_pg"];
		$degree_pg             = $row["degree_pg"];
		$percentage_pg         = $row["percentage_pg"];
		$university_pg_id      = $row["university_pg_id"];
		$tc                    = $row["tc"];
		$character_cert        = $row["character_cert"];
		$caste_cert            = $row["caste_cert"];
		$ph_cert               = $row["ph_cert"];
		$passport              = $row["passport"];
		$passport_no           = $row["passport_no"];
		$validity_period       = $row["validity_period"];
		$DASA                  = $row["DASA"];
		$remark                = $row["remark"];
		$anti_rag_st           = $row["anti_rag_st"];
		$med_cert              = $row["med_cert"];
		$muslim_minority       = $row["muslim_minority"];
		$other_minority        = $row["other_minority"];
		$admission_letter      = $row["admission_letter"];
		$dob                   = $row["dob"];
		$aadhaar               = $row["aadhaar"];
		$hostel_no             = $row["hostel_no"];
		$hostel_room           = $row["hostel_room"];
		$dasa_country          = $row["dasa_country"];
		$admission_withdrawal  = $row["admission_withdrawal"];

		if ($jee_rank_card == 0) {
			$jee_rank_card_yes = "checked";
			$jee_rank_card_no  = "";
		} else {
			$jee_rank_card_yes = "";
			$jee_rank_card_no  = "checked";
		}
		if ($jee_seat_allot_letter == 0) {
			$jee_seat_allot_letter_yes = "checked";
			$jee_seat_allot_letter_no  = "";
		} else {
			$jee_seat_allot_letter_yes = "";
			$jee_seat_allot_letter_no  = "checked";
		}
		if ($marsheek_10 == 0) {
			$marsheek_10_yes = "checked";
			$marsheek_10_no  = "";
		} else {
			$marsheek_10_yes = "";
			$marsheek_10_no  = "checked";
		}
		if ($cert_10 == 0) {
			$cert_10_yes = "checked";
			$cert_10_no  = "";
		} else {
			$cert_10_yes = "";
			$cert_10_no  = "checked";
		}
		if ($marksheet_12 == 0) {
			$marksheet_12_yes = "checked";
			$marksheet_12_no  = "";
		} else {
			$marksheet_12_yes = "";
			$marksheet_12_no  = "checked";
		}
		if ($cert_12 == 0) {
			$cert_12_yes = "checked";
			$cert_12_no  = "";
		} else {
			$cert_12_yes = "";
			$cert_12_no  = "checked";
		}
		if ($marksheet_grad == 0) {
			$marksheet_grad_yes = "checked";
			$marksheet_grad_no  = "";
		} else {
			$marksheet_grad_yes = "";
			$marksheet_grad_no  = "checked";
		}
		if ($degree_grad == 0) {
			$degree_grad_yes = "checked";
			$degree_grad_no  = "";
		} else {
			$degree_grad_yes = "";
			$degree_grad_no  = "checked";
		}
		if ($marksheet_pg == 0) {
			$marksheet_pg_yes = "checked";
			$marksheet_pg_no  = "";
		} else {
			$marksheet_pg_yes = "";
			$marksheet_pg_no  = "checked";
		}
		if ($degree_pg == 0) {
			$degree_pg_yes = "checked";
			$degree_pg_no  = "";
		} else {
			$degree_pg_yes = "";
			$degree_pg_no  = "checked";
		}
		if ($gate_score_card == 0) {
			$gate_score_card_yes = "checked";
			$gate_score_card_no  = "";
		} else {
			$gate_score_card_yes = "";
			$gate_score_card_no  = "checked";
		}
		if ($cat_score_card == 0) {
			$cat_score_card_yes = "checked";
			$cat_score_card_no  = "";
		} else {
			$cat_score_card_yes = "";
			$cat_score_card_no  = "checked";
		}
		if ($tc == 0) {
			$tc_yes = "checked";
			$tc_no  = "";
		} else {
			$tc_yes = "";
			$tc_no  = "checked";
		}
		if ($character_cert == 0) {
			$character_cert_yes = "checked";
			$character_cert_no  = "";
		} else {
			$character_cert_yes = "";
			$character_cert_no  = "checked";
		}
		if ($caste_cert == 0) {
			$caste_cert_yes = "checked";
			$caste_cert_no  = "";
		} else {
			$caste_cert_yes = "";
			$caste_cert_no  = "checked";
		}
		if ($passport == 0) {
			$passport_yes = "checked";
			$passport_no  = "";
		} else {
			$passport_yes = "";
			$passport_no  = "checked";
		}
		if ($DASA == 0) {
			$DASA_yes = "checked";
			$DASA_no  = "";
		} else {
			$DASA_yes = "";
			$DASA_no  = "checked";
		}
		if ($anti_rag_st == 0) {
			$anti_rag_st_yes = "checked";
			$anti_rag_st_no  = "";
		} else {
			$anti_rag_st_yes = "";
			$anti_rag_st_no  = "checked";
		}
		$dob = $row['dob'];
	} elseif (isset($_POST['submit'])) {
		$program_id = $_POST['program_id'];
		$campus_id = $_POST['campus_id'];
		foreach ($_POST as $k => $v) {
			if (isset($_POST[$k]) && ($k != "hindi_name")) {
				$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
			}
		}
		$sql  = "SELECT COUNT(DISTINCT enrollment_no) FROM `student` WHERE campus_id=:campus_id AND program_id=:program_id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':campus_id', $campus_id);
		$stmt->bindParam(':program_id', $program_id);
		$stmt->execute();
		$count = $stmt->fetchAll()[0][0] + 1;
		$sql   = "SELECT * FROM program WHERE program_id=:program_id";
		$stmt  = $conn->prepare($sql);
		$stmt->bindParam(':program_id', $program_id);
		$stmt->execute();
		$result = $stmt->fetchAll();
		if ($stmt->rowCount() == 1) {
			foreach ($result as $row) {
				$program_prefix = $row['program_prefix'];
			}
		}
		$enrollment_no         = $program_prefix . date("Y") . str_pad((string) $count, 3, "0", STR_PAD_LEFT);
		//$sem_code = $_POST['sem_code'];
		$comm_phone            = $_POST["comm_phone_no"];
		$sem_code              = $_POST["sem_code"];
		$program_id            = $_POST["program_id"];
		$campus_id             = $_POST["campus_id"];
		$first_name            = $_POST["first_name"];
		$middle_name           = $_POST["middle_name"];
		$last_name             = $_POST["last_name"];
		$hindi_name            = $_POST["hindi_name"];
		$birth_place           = $_POST["birth_place"];
		$category_id           = $_POST["category_id"];
		$sub_category          = $_POST["sub_category"];
		$religion_id           = $_POST["religion_id"];
		$gender                = $_POST["gender"];
		$marital_status        = $_POST["marital_status"];
		$area                  = $_POST["area"];
		$blood_group           = $_POST["blood_group"];
		$nationality           = $_POST["nationality"];
		$communication_addr    = $_POST["communication_addr"];
		$city1                 = $_POST["comm_city"];
		$state_id1             = $_POST["comm_state_id"];
		$pincode1              = $_POST["comm_pincode"];
		$email                 = $_SESSION['email'];
		unset($_SESSION['emial']);
		$father_first_name     = $_POST["father_first_name"];
		$father_last_name      = $_POST["father_last_name"];
		$father_profession     = $_POST["father_profession"];
		$father_office_addr    = $_POST["father_office_addr"];
		$city2                 = $_POST["city2"];
		$state_id2             = $_POST["state_id2"];
		$pincode2              = $_POST["pincode2"];
		$phone_no2             = $_POST["phone_no2"];
		$email2                = $_POST["email2"];
		$mother_first_name     = $_POST["mother_first_name"];
		$mother_last_name      = $_POST["mother_last_name"];
		$mother_profession     = $_POST["mother_profession"];
		$permanent_addr        = $_POST["permanent_addr"];
		$city3                 = $_POST["city3"];
		$state_id3             = $_POST["state_id3"];
		$pincode3              = $_POST["pincode3"];
		$phone_no3             = $_POST["phone_no3"];
		$email3                = $_POST["email3"];
		$local_guardian_name   = $_POST["local_guardian_name"];
		$local_guardian_addr   = $_POST["loca_guardian_addr"];
		$city4                 = $_POST["city4"];
		$phone_no4             = $_POST["phone_no4"];
		$admission_category_id = $_POST["admission_category_id"];
		$admit_card            = $_POST["admit_card"];
		$jee_rank_card         = $_POST["jee_rank_card"];
		$jee_roll_no           = $_POST["jee_roll_no"];
		$jee_rank_pos          = $_POST["jee_rank_pos"];
		$jee_seat_allot_letter = $_POST["jee_seat_allot_letter"];
		$marsheek_10           = $_POST["marsheek_10"];
		$cert_10               = $_POST["cert_10"];
		$percentage_10         = $_POST["percentage_10"];
		$board_id_10           = $_POST["board_id_10"];
		$marksheet_12          = $_POST["marksheet_12"];
		$cert_12               = $_POST["cert_12"];
		$percentage_12         = $_POST["percentage_12"];
		$board_id_12           = $_POST["board_id_12"];
		$marksheet_grad        = $_POST["marksheet_grad"];
		$degree_grad           = $_POST["degree_grad"];
		$percentage_grad       = $_POST["percentage_grad"];
		$university_grad_id    = $_POST["university_grad_id"];
		$marksheet_pg          = $_POST["marksheet_pg"];
		$degree_pg             = $_POST["degree_pg"];
		$percentage_pg         = $_POST["percentage_pg"];
		$university_pg_id      = $_POST["university_pg_id"];
		$gate_score_card       = $_POST["gate_score_card"];
		$gate_year             = $_POST["gate_year"];
		$gate_score            = $_POST["gate_score"];
		$cat_score_card        = $_POST["cat_score_card"];
		$cat_year              = $_POST["cat_year"];
		$cat_score             = $_POST["cat_score"];
		$tc                    = $_POST["tc"];
		$character_cert        = $_POST["character_cert"];
		$caste_cert            = $_POST["caste_cert"];
		$ph_cert               = $_POST["ph_cert"];
		$passport              = $_POST["passport"];
		$passport_no           = $_POST["passport_no"];
		$validity_period       = $_POST["validity_period"];
		$mcaip                 = $_POST["mcaip"];
		$DASA                  = $_POST["DASA"];
		$remark                = $_POST["remark"];
		$anti_rag_st           = $_POST["anti_rag_st"];
		$anti_rag_pr           = $_POST["anti_rag_pr"];
		$med_cert              = $_POST["med_cert"];
		$muslim_minority       = $_POST["muslim_minority"];
		$other_minority        = $_POST["other_minority"];
		$admission_letter      = $_POST["admission_letter"];
		$dob                   = $_POST["dob"];
		if ($marital_status == 0) {
			$marital_status_yes = "checked";
			$marital_status_no  = "";
		} else {
			$marital_status_yes = "";
			$marital_status_no  = "checked";
		}
		if ($admit_card == 0) {
			$admit_card_yes = "checked";
			$admit_card_no  = "";
		} else {
			$admit_card_yes = "";
			$admit_card_no  = "checked";
		}
		if ($jee_rank_card == 0) {
			$jee_rank_card_yes = "checked";
			$jee_rank_card_no  = "";
		} else {
			$jee_rank_card_yes = "";
			$jee_rank_card_no  = "checked";
		}
		if ($jee_seat_allot_letter == 0) {
			$jee_seat_allot_letter_yes = "checked";
			$jee_seat_allot_letter_no  = "";
		} else {
			$jee_seat_allot_letter_yes = "";
			$jee_seat_allot_letter_no  = "checked";
		}
		if ($marsheek_10 == 0) {
			$marsheek_10_yes = "checked";
			$marsheek_10_no  = "";
		} else {
			$marsheek_10_yes = "";
			$marsheek_10_no  = "checked";
		}
		if ($cert_10 == 0) {
			$cert_10_yes = "checked";
			$cert_10_no  = "";
		} else {
			$cert_10_yes = "";
			$cert_10_no  = "checked";
		}
		if ($marksheet_12 == 0) {
			$marksheet_12_yes = "checked";
			$marksheet_12_no  = "";
		} else {
			$marksheet_12_yes = "";
			$marksheet_12_no  = "checked";
		}
		if ($cert_12 == 0) {
			$cert_12_yes = "checked";
			$cert_12_no  = "";
		} else {
			$cert_12_yes = "";
			$cert_12_no  = "checked";
		}
		if ($marksheet_grad == 0) {
			$marksheet_grad_yes = "checked";
			$marksheet_grad_no  = "";
		} else {
			$marksheet_grad_yes = "";
			$marksheet_grad_no  = "checked";
		}
		if ($degree_grad == 0) {
			$degree_grad_yes = "checked";
			$degree_grad_no  = "";
		} else {
			$degree_grad_yes = "";
			$degree_grad_no  = "checked";
		}
		if ($marksheet_pg == 0) {
			$marksheet_pg_yes = "checked";
			$marksheet_pg_no  = "";
		} else {
			$marksheet_pg_yes = "";
			$marksheet_pg_no  = "checked";
		}
		if ($degree_pg == 0) {
			$degree_pg_yes = "checked";
			$degree_pg_no  = "";
		} else {
			$degree_pg_yes = "";
			$degree_pg_no  = "checked";
		}
		if ($gate_score_card == 0) {
			$gate_score_card_yes = "checked";
			$gate_score_card_no  = "";
		} else {
			$gate_score_card_yes = "";
			$gate_score_card_no  = "checked";
		}
		if ($cat_score_card == 0) {
			$cat_score_card_yes = "checked";
			$cat_score_card_no  = "";
		} else {
			$cat_score_card_yes = "";
			$cat_score_card_no  = "checked";
		}
		if ($tc == 0) {
			$tc_yes = "checked";
			$tc_no  = "";
		} else {
			$tc_yes = "";
			$tc_no  = "checked";
		}
		if ($character_cert == 0) {
			$character_cert_yes = "checked";
			$character_cert_no  = "";
		} else {
			$character_cert_yes = "";
			$character_cert_no  = "checked";
		}
		if ($caste_cert == 0) {
			$caste_cert_yes = "checked";
			$caste_cert_no  = "";
		} else {
			$caste_cert_yes = "";
			$caste_cert_no  = "checked";
		}
		if ($passport == 0) {
			$passport_yes = "checked";
			$passport_no  = "";
		} else {
			$passport_yes = "";
			$passport_no  = "checked";
		}
		if ($DASA == 0) {
			$DASA_yes = "checked";
			$DASA_no  = "";
		} else {
			$DASA_yes = "";
			$DASA_no  = "checked";
		}
		if ($anti_rag_st == 0) {
			$anti_rag_st_yes = "checked";
			$anti_rag_st_no  = "";
		} else {
			$anti_rag_st_yes = "";
			$anti_rag_st_no  = "checked";
		}
		$aadhaar              = $_POST['aadhaar'];
		$dasa_country         = $_POST['dasa_country'];
		$admission_withdrawal = " ";

		$status_id = ecell_get_status('on');

		try {
			$year = DATE("Y");

			$sql = "INSERT INTO `student` (`student_id`, `program_id`, `campus_id`, `date_of_admission`, `photo`, `mime_photo`, `briefcase`, `mime_briefcase`, `first_name`, `middle_name`, `last_name`, `hindi_name`, `enrollment_no`, `dob`, `birth_place`, `category_id`, `sub_category`, `religion_id`, `gender`, `marital_status`, `area`, `blood_group`, `nationality`, `communication_addr`, `comm_city`, `comm_state_id`, `comm_pincode`, `comm_phone_no`, `comm_email`, `father_first_name`, `father_last_name`, `father_profession`, `father_office_addr`, `father_city`, `father_state_id`, `father_pincode`, `father_phone_no`, `father_email`, `mother_first_name`, `mother_last_name`, `mother_profession`, `permanent_addr`, `perm_city`, `perm_state_id`, `perm_pincode`, `perm_phone_no`, `perm_email`, `local_guardian_name`, `local_guardian_addr`, `local_guard_city`, `local_guard_phone_no`, `admission_category_id`, `admit_card`, `jee_rank_card`, `jee_roll_no`, `jee_rank_pos`, `jee_seat_allot_letter`, `marksheet_10`, `cert_10`, `percentage_10`, `board_id_10`, `board_10_passing_state`, `marksheet_12`, `cert_12`, `percentage_12`, `board_id_12`, `board_12_passing_state`, `marksheet_grad`, `degree_grad`, `percentage_grad`, `university_grad_id`, `marksheet_pg`, `degree_pg`, `percentage_pg`, `university_pg_id`, `gate_score_card`, `gate_year`, `gate_score`, `cat_score_card`, `cat_year`, `cat_score`, `tc`, `character_cert`, `caste_cert`, `ph_cert`, `passport`, `passport_no`, `validity_period`, `mcaip`, `DASA`, `remark`, `anti_rag_st`, `anti_rag_pr`, `med_cert`, `muslim_minority`, `other_minority`, `admission_letter`, `status_id`, `log_id`, `sem_code`, `year`, `section`, `aadhaar`, `hostel_no`, `hostel_room`, `dasa_country`, `admission_withdrawal`) 
				VALUES 
				(:student_id,:program_id,:campus_id,:date_of_admission,:photo,:mime_photo,:briefcase,:mime_briefcase,:first_name,:middle_name,:last_name,:hindi_name,:enrollment_no,:dob,:birth_place,:category_id,:sub_category,:religion_id,:gender,:marital_status,:area,:blood_group,:nationality,:communication_addr,:comm_city,:comm_state_id,:comm_pincode,:comm_phone_no,:comm_email,:father_first_name,:father_last_name,:father_profession,:father_office_addr,:father_city,:father_state_id,:father_pincode,:father_phone_no,:father_email,:mother_first_name,:mother_last_name,:mother_profession,:permanent_addr,:perm_city,:perm_state_id,:perm_pincode,:perm_phone_no,:perm_email,:local_guardian_name,:local_guardian_addr,:local_guard_city,:local_guard_phone_no,:admission_category_id,:admit_card,:jee_rank_card,:jee_roll_no,:jee_rank_pos,:jee_seat_allot_letter,:marksheet_10,:cert_10,:percentage_10,:board_id_10,:board_10_passing_state,:marksheet_12,:cert_12,:percentage_12,:board_id_12,:board_12_passing_state,:marksheet_grad,:degree_grad,:percentage_grad,:university_grad_id,:marksheet_pg,:degree_pg,:percentage_pg,:university_pg_id,:gate_score_card,:gate_year,:gate_score,:cat_score_card,:cat_year,:cat_score,:tc,:character_cert,:caste_cert,:ph_cert,:passport,:passport_no,:validity_period,:mcaip,:DASA,:remark,:anti_rag_st,:anti_rag_pr,:med_cert,:muslim_minority,:other_minority,:admission_letter,:status_id,:log_id,:sem_code,:year,:section,:aadhaar,:hostel_no,:hostel_room,:dasa_country,:admission_withdrawal)";

			$ac_on = "Entered a new student with enrollment_no " . $enrollment_no;
			$s_i   = $_SESSION['staff_id'];
			$r     = $_SESSION['rank'];
			$tn    = 'student';

			$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

			$sql = "INSERT INTO `student` (`student_id`, `program_id`, `campus_id`, `date_of_admission`, `photo`, `mime_photo`, `briefcase`, `mime_briefcase`, `first_name`, `middle_name`, `last_name`, `hindi_name`, `enrollment_no`, `dob`, `birth_place`, `category_id`, `sub_category`, `religion_id`, `gender`, `marital_status`, `area`, `blood_group`, `nationality`, `communication_addr`, `comm_city`, `comm_state_id`, `comm_pincode`, `comm_phone_no`, `comm_email`, `father_first_name`, `father_last_name`, `father_profession`, `father_office_addr`, `father_city`, `father_state_id`, `father_pincode`, `father_phone_no`, `father_email`, `mother_first_name`, `mother_last_name`, `mother_profession`, `permanent_addr`, `perm_city`, `perm_state_id`, `perm_pincode`, `perm_phone_no`, `perm_email`, `local_guardian_name`, `local_guardian_addr`, `local_guard_city`, `local_guard_phone_no`, `admission_category_id`, `admit_card`, `jee_rank_card`, `jee_roll_no`, `jee_rank_pos`, `jee_seat_allot_letter`, `marksheet_10`, `cert_10`, `percentage_10`, `board_id_10`, `board_10_passing_state`, `marksheet_12`, `cert_12`, `percentage_12`, `board_id_12`, `board_12_passing_state`, `marksheet_grad`, `degree_grad`, `percentage_grad`, `university_grad_id`, `marksheet_pg`, `degree_pg`, `percentage_pg`, `university_pg_id`, `gate_score_card`, `gate_year`, `gate_score`, `cat_score_card`, `cat_year`, `cat_score`, `tc`, `character_cert`, `caste_cert`, `ph_cert`, `passport`, `passport_no`, `validity_period`, `mcaip`, `DASA`, `remark`, `anti_rag_st`, `anti_rag_pr`, `med_cert`, `muslim_minority`, `other_minority`, `admission_letter`, `status_id`, `log_id`, `sem_code`, `year`, `section`, `aadhaar`, `hostel_no`, `hostel_room`, `dasa_country`, `admission_withdrawal`) 
				VALUES 
				(:student_id,:program_id,:campus_id,:date_of_admission,:photo,:mime_photo,:briefcase,:mime_briefcase,:first_name,:middle_name,:last_name,:hindi_name,:enrollment_no,:dob,:birth_place,:category_id,:sub_category,:religion_id,:gender,:marital_status,:area,:blood_group,:nationality,:communication_addr,:comm_city,:comm_state_id,:comm_pincode,:comm_phone_no,:comm_email,:father_first_name,:father_last_name,:father_profession,:father_office_addr,:father_city,:father_state_id,:father_pincode,:father_phone_no,:father_email,:mother_first_name,:mother_last_name,:mother_profession,:permanent_addr,:perm_city,:perm_state_id,:perm_pincode,:perm_phone_no,:perm_email,:local_guardian_name,:local_guardian_addr,:local_guard_city,:local_guard_phone_no,:admission_category_id,:admit_card,:jee_rank_card,:jee_roll_no,:jee_rank_pos,:jee_seat_allot_letter,:marksheet_10,:cert_10,:percentage_10,:board_id_10,:board_10_passing_state,:marksheet_12,:cert_12,:percentage_12,:board_id_12,:board_12_passing_state,:marksheet_grad,:degree_grad,:percentage_grad,:university_grad_id,:marksheet_pg,:degree_pg,:percentage_pg,:university_pg_id,:gate_score_card,:gate_year,:gate_score,:cat_score_card,:cat_year,:cat_score,:tc,:character_cert,:caste_cert,:ph_cert,:passport,:passport_no,:validity_period,:mcaip,:DASA,:remark,:anti_rag_st,:anti_rag_pr,:med_cert,:muslim_minority,:other_minority,:admission_letter,:status_id,:log_id,:sem_code,:year,:section,:aadhaar,:hostel_no,:hostel_room,:dasa_country,:admission_withdrawal)";

			$stmt = $conn->prepare($sql);

			$student_id = NULL;
			$date_of_admission = NULL;
			$photo = NULL;
			$mime_photo = 'MIME';
			$briefcase = NULL;
			$mime_briefcase = 'MIME';
			$board_10_passing_state = '1';
			$board_12_passing_state = '1';
			$section = " ";
			$hostel_no = " ";
			$hostel_room = " ";

			$stmt->bindParam(':student_id', $student_id);
			$stmt->bindParam(':program_id', $program_id);
			$stmt->bindParam(':campus_id', $campus_id);
			$stmt->bindParam(':date_of_admission', $date_of_admission);
			$stmt->bindParam(':photo', $photo); 
			$stmt->bindParam(':mime_photo', $mime_photo);
			$stmt->bindParam(':briefcase', $briefcase);
			$stmt->bindParam(':mime_briefcase', $mime_briefcase);
			$stmt->bindParam(':first_name', $first_name);
			$stmt->bindParam(':middle_name', $middle_name);
			$stmt->bindParam(':last_name', $last_name); 
			$stmt->bindParam(':hindi_name', $hindi_name);
			$stmt->bindParam(':enrollment_no', $enrollment_no); 
			$stmt->bindParam(':dob', $dob);
			$stmt->bindParam(':birth_place', $birth_place); 
			$stmt->bindParam(':category_id', $category_id);
			$stmt->bindParam(':sub_category', $sub_category); 
			$stmt->bindParam(':religion_id', $religion_id);
			$stmt->bindParam(':gender', $gender); 
			$stmt->bindParam(':marital_status', $marital_status);
			$stmt->bindParam(':area', $area); 
			$stmt->bindParam(':blood_group', $blood_group);
			$stmt->bindParam(':nationality', $nationality); 
			$stmt->bindParam(':communication_addr', $communication_addr);
			$stmt->bindParam(':comm_city', $city1); 
			$stmt->bindParam(':comm_state_id', $state_id1);
			$stmt->bindParam(':comm_pincode', $pincode1); 
			$stmt->bindParam(':comm_phone_no', $comm_phone);
			$stmt->bindParam(':comm_email', $email); 
			$stmt->bindParam(':father_first_name', $father_first_name);
			$stmt->bindParam(':father_last_name', $father_last_name); 
			$stmt->bindParam(':father_profession', $father_profession);
			$stmt->bindParam(':father_office_addr', $father_office_addr); 
			$stmt->bindParam(':father_city', $city2); 
			$stmt->bindParam(':father_state_id', $state_id2); 
			$stmt->bindParam(':father_pincode', $pincode2); 
			$stmt->bindParam(':father_phone_no', $phone_no2); 
			$stmt->bindParam(':father_email', $email2);
			$stmt->bindParam(':mother_first_name', $mother_first_name); 
			$stmt->bindParam(':mother_last_name', $mother_last_name);
			$stmt->bindParam(':mother_profession', $mother_profession); 
			$stmt->bindParam(':permanent_addr', $permanent_addr);
			$stmt->bindParam(':perm_city', $city3); 
			$stmt->bindParam(':perm_state_id', $state_id3); 
			$stmt->bindParam(':perm_pincode', $pincode3); 
			$stmt->bindParam(':perm_phone_no', $phone_no3); 
			$stmt->bindParam(':perm_email', $email3); 
			$stmt->bindParam(':local_guardian_name', $local_guardian_name);
			$stmt->bindParam(':local_guardian_addr', $local_guardian_addr); 
			$stmt->bindParam(':local_guard_city', $city4);
			$stmt->bindParam(':local_guard_phone_no', $phone_no4); 
			$stmt->bindParam(':admission_category_id', $admission_category_id);
			$stmt->bindParam(':admit_card', $admit_card);
			$stmt->bindParam(':jee_rank_card', $jee_rank_card);
			$stmt->bindParam(':jee_roll_no', $jee_roll_no); 
			$stmt->bindParam(':jee_rank_pos', $jee_rank_pos);
			$stmt->bindParam(':jee_seat_allot_letter', $jee_seat_allot_letter); 
			$stmt->bindParam(':marksheet_10', $marsheek_10);
			$stmt->bindParam(':cert_10', $cert_10); 
			$stmt->bindParam(':percentage_10', $percentage_10); 
			$stmt->bindParam(':board_id_10', $board_id_10); 
			$stmt->bindParam(':board_10_passing_state', $board_10_passing_state);
			$stmt->bindParam(':marksheet_12', $marksheet_12); 
			$stmt->bindParam(':cert_12', $cert_12); 
			$stmt->bindParam(':percentage_12', $percentage_12); 
			$stmt->bindParam(':board_id_12', $board_id_12);
			$stmt->bindParam(':board_12_passing_state', $board_12_passing_state); 
			$stmt->bindParam(':marksheet_grad', $marksheet_grad);
			$stmt->bindParam(':degree_grad', $degree_grad); 
			$stmt->bindParam(':percentage_grad', $percentage_grad);
			$stmt->bindParam(':university_grad_id', $university_grad_id); 
			$stmt->bindParam(':marksheet_pg', $marksheet_pg);
			$stmt->bindParam(':degree_pg', $degree_pg); 
			$stmt->bindParam(':percentage_pg', $percentage_pg); 
			$stmt->bindParam(':university_pg_id', $university_pg_id); 
			$stmt->bindParam(':gate_score_card', $gate_score_card);
			$stmt->bindParam(':gate_year', $gate_year); 
			$stmt->bindParam(':gate_score', $gate_score);
			$stmt->bindParam(':cat_score_card', $cat_score_card); 
			$stmt->bindParam(':cat_year', $cat_year);
			$stmt->bindParam(':cat_score', $cat_score); 
			$stmt->bindParam(':tc', $tc);
			$stmt->bindParam(':character_cert', $character_cert); 
			$stmt->bindParam(':caste_cert', $caste_cert);
			$stmt->bindParam(':ph_cert', $ph_cert); 
			$stmt->bindParam(':passport', $passport); 
			$stmt->bindParam(':passport_no', $passport_no); 
			$stmt->bindParam(':validity_period', $validity_period);
			$stmt->bindParam(':mcaip', $mcaip); 
			$stmt->bindParam(':DASA', $DASA); 
			$stmt->bindParam(':remark', $remark);
			$stmt->bindParam(':anti_rag_st', $anti_rag_st); 
			$stmt->bindParam(':anti_rag_pr', $anti_rag_pr);
			$stmt->bindParam(':med_cert', $med_cert); 
			$stmt->bindParam(':muslim_minority', $muslim_minority); 
			$stmt->bindParam(':other_minority', $other_minority); 
			$stmt->bindParam(':admission_letter', $admission_letter);
			$stmt->bindParam(':status_id', $status_id); 
			$stmt->bindParam(':log_id', $log_id);
			$stmt->bindParam(':sem_code', $sem_code); 
			$stmt->bindParam(':year', $year); 
			$stmt->bindParam(':section', $section); 
			$stmt->bindParam(':aadhaar', $aadhaar);
			$stmt->bindParam(':hostel_no', $hostel_no); 
			$stmt->bindParam(':hostel_room', $hostel_room);
			$stmt->bindParam(':dasa_country', $dasa_country); 
			$stmt->bindParam(':admission_withdrawal', $admission_withdrawal);

			$stmt->execute();

			$msg = "The roll number given is $enrollment_no.";
		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}

	}
}

$TBS->MergeBlock('program', $conn, 'SELECT * FROM program');
$TBS->MergeBlock('campus', $conn, 'SELECT * FROM campus');
$TBS->MergeBlock('sem_code_description', $conn, 'SELECT * FROM sem_code_description');
$TBS->MergeBlock('religion', $conn, 'SELECT * FROM religion');
$TBS->MergeBlock('category, acategoryblk', $conn, 'SELECT * FROM category');
$TBS->MergeBlock('universityBlk, PGUniversityBlk', $conn, 'SELECT * FROM universities');
$TBS->MergeBlock('state, state2, state3', $conn, 'SELECT * FROM state');
$TBS->MergeBlock('board_1, board_2', $conn, 'SELECT * FROM board');

$TBS->Show();

?>
