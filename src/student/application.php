<?php

include_once('../../includes/include.php');
require_once ('../../includes/validator.php');
require_once ('db_helper.php');

include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
	header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$TBS = new clsTinyButStrong;

$TBS->LoadTemplate('application.html');

$tentative_enrollment_no = "";
$msg = "";
$specialError= "";
$errorArray = array();

$servername = "localhost";
$username   = "alyssandra";
$password   = "tsisibi@080044";
$dbname     = "federated_online";
$type = " ";
$showerr = false;
$showform = true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	foreach ($_POST as $k => $v) {
		if (isset($_POST[$k])) {
			$_POST[$k] = trim(filter_var($v, FILTER_SANITIZE_STRING));
		}
	}

	if (isset($_POST["registered_email"])) {
		
		$registered_email = $_POST["registered_email"];

		$student_id = ecell_get_val('student', 'comm_email', $registered_email, 'student_id');
		if ($student_id) {
			$specialError =  "Student is already enrolled.";
			$showform = false;
		}
	
		else {
			try {
				$conn1 = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
				// set the PDO error mode to exception
				$conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$conn1->exec("set names 'utf8'");
				$sql = "select * from application_details where comm_email = '" . $registered_email . "' order by registration_timestamp desc;";

				$stmt = $conn1->prepare($sql);
				$stmt->execute();

				$result1 = $stmt->fetchAll();

				$count = $stmt->rowCount();

				if ($count > 0) {
					$result = $result1[0];
				} else {
					$specialError = "Not registered on online server. Please first register on online server.";
					unset($_SESSION['emial']);
					$showform = false;
				}
			} catch (PDOException $e) {
				$specialError = $sql . "<br>" . $e->getMessage();
			}


			if (isset($result)) {
			
				$mydetails = $result;

				$type = trim($mydetails['type']);
				$_SESSION['type'] = $type;

				$jeeOptions = true;
				$ugOptions = true;
				$gateOptions = true;
				$catOptions = true;
				$pgOptions = true;

				if (strcmp($type, "B.Tech") == 0) {
					$ugOptions = false;
					$gateOptions = false;
					$catOptions = false;
					$pgOptions = false;
				} else if (strcmp($type, "M.Tech") == 0) {
					$jeeOptions = false;
					$catOptions = false;
					$pgOptions = false;
				} else if (strcmp($type, "MBA") == 0) {
					$jeeOptions = false;
					$gateOptions = false;
					$pgOptions = false;
				} else if (strcmp($type, "Ph.D") == 0) {
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
				$marsheek_10 = $mydetails['marksheet_10'];
				$cert_10 = $mydetails['cert_10'];
				$percentage_10 = $mydetails['percentage_10'];

				
				$marksheet_12 = $mydetails['marksheet_12'];
				$cert_12 = $mydetails['cert_12'];
				$percentage_12 = $mydetails['percentage_12'];

				$board_id_10 = $mydetails['board_id_10'];
				//$board_id_10 = ecell_updated_board($mydetails['board_id_10'], $_SESSION['staff_id'], $_SESSION['rank'], $conn);
				$board_id_12 = $mydetails['board_id_12'];
				//$board_id_12 = ecell_updated_board($mydetails['board_id_12'], $_SESSION['staff_id'], $_SESSION['rank'],  $conn);

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

				$university_grad_id  = $mydetails['university_grad_id'];
				$university_pg_id = $mydetails['university_pg_id'];
				//$university_grad_id = ecell_updated_university($mydetails['university_grad_id'],  $_SESSION['staff_id'], $_SESSION['rank'], $conn);
				//$university_pg_id = ecell_updated_university($mydetails['university_pg_id'],  $_SESSION['staff_id'], $_SESSION['rank'], $conn);

				$transfer_cert = $mydetails['transfer_cert'];
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

				//change it
				$state_id4 = $mydetails['local_guardian_state_id'];
				$pincode4 = $mydetails['local_guardian_pincode'];

				$year = date('Y');

				$campus_name = "Allahabad";
				$sql = "SELECT campus_id FROM campus WHERE campus_name=:campus_name";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':campus_name', $campus_name);
				$stmt->execute();

				$campus_id = $stmt->fetchAll()[0][0];	

				$campus_id = $mydetails['campus_id'];

				$sql = "SELECT enrollment_no FROM student_original WHERE year=:year AND program_id=:program_id ORDER BY enrollment_no DESC";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':program_id', $program_id);
				$stmt->bindParam(':year', $year);
				$stmt->execute();

				if ($stmt->rowCount()) {
					$tentative_enrollment_no = substr($stmt->fetchAll()[0][0], -3);
					$tentative_enrollment_no++;
				} else {
					$tentative_enrollment_no = 1;
				}

				$sql   = "SELECT * FROM program WHERE program_id=:program_id";
				$stmt  = $conn->prepare($sql);
				$stmt->bindParam(':program_id', $program_id);
				$stmt->execute();
				$result = $stmt->fetchAll();
				if ($stmt->rowCount() == 1) {
					foreach ($result as $row) {
						$program_prefix = $row['program_prefix'];
					}
				} else {
					$specialError = "Please select valid Program";
				}

				$tentative_enrollment_no = $program_prefix . date("Y") . str_pad((string) $tentative_enrollment_no, 3, "0", STR_PAD_LEFT);

			}
		}
	} 
	elseif (isset($_POST['submit'])) {

		$type = $_SESSION['type'];

		$jeeOptions = true;
		$ugOptions = true;
		$gateOptions = true;
		$catOptions = true;
		$pgOptions = true;

		if (strcmp($type, "B.Tech") == 0) {
			$ugOptions = false;
			$gateOptions = false;
			$catOptions = false;
			$pgOptions = false;
		} else if (strcmp($type, "M.Tech") == 0) {
			$jeeOptions = false;
			$catOptions = false;
			$pgOptions = false;
		} else if (strcmp($type, "MBA") == 0) {
			$jeeOptions = false;
			$gateOptions = false;
			$pgOptions = false;
		} else if (strcmp($type, "Ph.D") == 0) {
			$jeeOptions = false;
			$catOptions = false;
		}

		foreach ($_POST as $k => $v) {
			if (isset($_POST[$k]) && ($k != "hindi_name")) {
				$_POST[$k] = filter_var($v, FILTER_SANITIZE_STRING);
			}
		}
		$email = $_SESSION['email'];
		$student_id = ecell_get_val('student_original', 'comm_email', $email, 'student_id');

		if ($student_id) {
			echo "Student is already enrolled.";
			exit;
		}
		
		$conn->beginTransaction();

		$year = date('Y');
		$sem_code = $_POST["sem_code"];
		$program_id = $_POST["program_id"];
		$campus_id = $_POST["campus_id"];
		$program_name = ecell_get_program($program_id, $conn);

		$sql = "SELECT enrollment_no FROM student_original WHERE year=:year AND program_id=:program_id ORDER BY enrollment_no DESC";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':program_id', $program_id);
		$stmt->bindParam(':year', $year);
		$stmt->execute();

		if ($stmt->rowCount()) {
			$tentative_enrollment_no = substr($stmt->fetchAll()[0][0], -3);
			$tentative_enrollment_no++;
		} else {
			$tentative_enrollment_no = 1;
		}

		$sql   = "SELECT * FROM program WHERE program_id=:program_id";
		$stmt  = $conn->prepare($sql);
		$stmt->bindParam(':program_id', $program_id);
		$stmt->execute();
		$result = $stmt->fetchAll();
		if ($stmt->rowCount() == 1) {
			foreach ($result as $row) {
				$program_prefix = $row['program_prefix'];
			}
		} else {
			$specialError = "Please select valid program.";
		}


		if (!empty($_POST['enrollment_offset'])) {
			if ($tentative_enrollment_no < $_POST['enrollment_offset']) {
				$tentative_enrollment_no = $_POST['enrollment_offset'];
			}
		}
		
		$tentative_enrollment_no = $program_prefix . date("Y") . str_pad((string) $tentative_enrollment_no, 3, "0", STR_PAD_LEFT);

		$enrollment_no = $tentative_enrollment_no;
		$tentative_enrollment_no = "";

		// $sql  = "SELECT COUNT(DISTINCT enrollment_no) FROM `student_original` WHERE campus_id=:campus_id AND program_id=:program_id 
		// 	AND year=:year AND sem_code=:sem_code";
		// $stmt = $conn->prepare($sql);
		// $stmt->bindParam(':campus_id', $campus_id);
		// $stmt->bindParam(':program_id', $program_id);
		// $stmt->bindParam(':year', $year);
		// $stmt->bindParam(':sem_code', $sem_code);
		// $stmt->execute();
		// $count = $stmt->fetchAll()[0][0] + 1;
		// $sql   = "SELECT * FROM program WHERE program_id=:program_id";
		// $stmt  = $conn->prepare($sql);
		// $stmt->bindParam(':program_id', $program_id);
		// try {
		// 	$stmt->execute();
		// } catch (PDOException $e) {
		// 	$specialError = "Query unsuccessful. Please try again.";
		// }
		// $result = $stmt->fetchAll();
		// if ($stmt->rowCount() == 1) {
		// 	foreach ($result as $row) {
		// 		$program_prefix = $row['program_prefix'];
		// 	}
		// }else{
		// 	$specialError = "Please select valid Program";
		// }
		// $enrollment_no         = $program_prefix . date("Y") . str_pad((string) $count, 3, "0", STR_PAD_LEFT);
		$comm_phone_no         = $_POST["comm_phone_no"];
		$first_name            = ucwords($_POST["first_name"]);
		$middle_name           = ucwords($_POST["middle_name"]);
		$last_name             = ucwords($_POST["last_name"]);
		$hindi_name            = $_POST["hindi_name"];
		$birth_place           = ucwords($_POST["birth_place"]);
		$category_id           = $_POST["category_id"];
		$sub_category          = $_POST["sub_category"];
		$religion_id           = $_POST["religion_id"];
		$gender                = $_POST["gender"];
		$marital_status        = $_POST["marital_status"];
		$area                  = $_POST["area"];
		$blood_group           = $_POST["blood_group"];
		$nationality           = ucwords($_POST["nationality"]);
		$communication_addr    = $_POST["communication_addr"];
		$comm_city                 = ucwords($_POST["comm_city"]);
		$comm_state             = $_POST["comm_state_id"];
		$comm_pincode              = $_POST["comm_pincode"];
		$email                 = $_SESSION['email'];
		//	unset($_SESSION['email']);
		$father_first_name     = ucwords($_POST["father_first_name"]);
		$father_last_name      = ucwords($_POST["father_last_name"]);
		$father_profession     = ucwords($_POST["father_profession"]);
		$father_office_addr    = $_POST["father_office_addr"];
		$city2                 = ucwords($_POST["city2"]);
		$state_id2             = $_POST["state_id2"];
		$pincode2              = $_POST["pincode2"];
		$phone_no2             = $_POST["phone_no2"];
		$email2                = $_POST["email2"];
		$mother_first_name     = ucwords($_POST["mother_first_name"]);
		$mother_last_name      = ucwords($_POST["mother_last_name"]);
		$mother_profession     = ucwords($_POST["mother_profession"]);
		$permanent_addr        = $_POST["permanent_addr"];
		$city3                 = ucwords($_POST["city3"]);
		$state_id3             = $_POST["state_id3"];
		$pincode3              = $_POST["pincode3"];
		$phone_no3             = $_POST["phone_no3"];
		$email3                = $_POST["email3"];
		$local_guardian_name   = ucwords($_POST["local_guardian_name"]);
		$local_guardian_addr   = $_POST["loca_guardian_addr"];

		$city4                 = ucwords($_POST["city4"]);
		$phone_no4             = $_POST["phone_no4"];
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
		} else{
			$admit_card = 0;
			$jee_rank_card = 0;
			$jee_roll_no = 0;
			$jee_rank_pos = 0;
			$jee_seat_allot_letter = 0;
		}
		$marsheek_10           = $_POST["marsheek_10"];
		$cert_10               = $_POST["cert_10"];
		$percentage_10         = $_POST["percentage_10"];
		$board_id_10           = $_POST["board_id_10"];
		$marksheet_12          = $_POST["marksheet_12"];
		$cert_12               = $_POST["cert_12"];
		$percentage_12         = $_POST["percentage_12"];
		$board_id_12           = $_POST["board_id_12"];

		if($ugOptions) {
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

		if($pgOptions) {
			$marksheet_pg = $_POST["marksheet_pg"];
			$degree_pg = $_POST["degree_pg"];
			$percentage_pg = $_POST["percentage_pg"];
			$university_pg_id = $_POST["university_pg_id"];
			$net = $_POST['net'];
			if($net){
				$net_year = $_POST['net_year'];
				$net_month = $_POST['net_month'];
				$net_subject = $_POST['net_subject'];
			} else {
				$net_year = 0;
				$net_month = 0;
				$net_subject = " ";
			}

			$jrf = $_POST['jrf'];
			if($jrf){
				$jrf_year = $_POST['jrf_year'];
				$jrf_month = $_POST['jrf_month'];
				$jrf_subject = $_POST['jrf_subject'];
			}else {
				$jrf_year = 0;
				$jrf_month = 0;
				$jrf_subject = " ";
			}

			$csir = $_POST['csir'];
			if($csir){
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

		if($gateOptions) {
			$gate_score_card = $_POST["gate_score_card"];
			$gate_year = $_POST["gate_year"];
			$gate_score = $_POST["gate_score"];
		} else {
			$gate_score_card = 0;
			$gate_year = 0;
			$gate_score = 0;
		}

		if($catOptions) {
			$cat_score_card = $_POST["cat_score_card"];
			$cat_year = $_POST["cat_year"];
			$cat_score = $_POST["cat_score"];
		} else {
			$cat_score_card = 0;
			$cat_year = 0;
			$cat_score = 0;
		}


		
		$transfer_cert         = $_POST["transfer_cert"];
		$character_cert        = $_POST["character_cert"];
		$caste_cert            = $_POST["caste_cert"];
		$ph_cert               = $_POST["ph_cert"];
		$passport              = $_POST["passport"];
		$migration_cert        = $_POST["migration_cert"];
		$mcaip   	           = $_POST["mcaip"];
		$passport_no           = $_POST["passport_no"];
		$validity_period       = $_POST["validity_period"];
		$validity_period       = $validity_period.'-01-01';
		$DASA                  = $_POST["DASA"];
		$remark                = $_POST["remark"];
		$anti_rag_st           = $_POST["anti_rag_st"];
		$med_cert              = $_POST["med_cert"];
		$admission_letter      = $_POST["admission_letter"];
		$dob                   = $_POST["dob"];
		$aadhaar              = $_POST['aadhaar'];
		$dasa_country         = $_POST['dasa_country'];

		//fields added on 16 july
		// $net          = $_POST["net"];
		// $net_year     = $_POST["net_year"];
		// $net_month    = $_POST["net_month"];

		if (!isset($first_name)) {			
			$errorArray[] = "First name is required.";
		} else {
			if (!preg_match("/^[a-zA-Z ]*$/", $first_name)) {
				$errorArray[] = "Only letters and white spaces are allowed in first name.";
			}
		}
		
		if (!isset($last_name)) {
			$errorArray[] = "Last name is required.";
		} else {
			if (!preg_match("/^[a-zA-Z ]*$/", $last_name)) {
				$errorArray[] = "Only letters and white spaces are allowed in last name.";
			}
		}
		
		if (count($errorArray) == 0) {
			
			$status_id = ecell_get_status('on');

			$tdob = explode('/', $dob);
			$dob = $tdob[2] . '-' . $tdob[0] . '-' . $tdob[1];



			try {

				$sql = "INSERT INTO `student_original` (`student_id`, `program_id`, `campus_id`, `date_of_admission`, `first_name`, `middle_name`, `last_name`, `hindi_name`, `enrollment_no`, `dob`, `birth_place`, `category_id`, `sub_category`, `religion_id`, `gender`, `marital_status`, `area`, `blood_group`, `nationality`, `comm_addr`, `comm_city`, `comm_state_id`, `comm_pincode`, `comm_mobile_no`, `comm_email`, `father_first_name`, `father_last_name`, `father_profession`, `father_office_addr`, `father_city`, `father_state_id`, `father_pincode`, `father_landline`, `father_email`, `mother_first_name`, `mother_last_name`, `mother_profession`, `perm_addr`, `perm_city`, `perm_state_id`, `perm_pincode`, `perm_landline`, `perm_email`, `local_guardian_name`, `local_guardian_addr`, `local_guardian_city`, `local_guardian_landline`, `admission_category_id`, `admit_card`, `jee_rank_card`, `jee_roll_no`, `jee_rank_pos`, `jee_seat_allot_letter`, `marksheet_10`, `cert_10`, `percentage_10`, `board_id_10`, `board_10_passing_state_id`, `marksheet_12`, `cert_12`, `percentage_12`, `board_id_12`, `board_12_passing_state_id`, `marksheet_grad`, `degree_grad`, `percentage_grad`, `university_grad_id`, `marksheet_pg`, `degree_pg`, `percentage_pg`, `university_pg_id`, `gate_score_card`, `gate_year`, `gate_score`, `cat_score_card`, `cat_year`, `cat_score`, `transfer_cert`, `character_cert`, `caste_cert`, `ph_cert`, `passport`, `passport_no`, `passport_expiry`, `DASA`, `remark`, `anti_rag_st`, `med_cert`,  `admission_letter`, `status_value_id`, `log_id`, `sem_code`, `year`, `section`, `aadhaar`, `hostel_no`, `hostel_room`, `dasa_country`,`migration_cert`, `mcaip`, `net`, `net_year`, `net_month`, `net_subject`, `jrf`, `jrf_year`, `jrf_month`, `jrf_subject`, `csir`, `csir_year`, `csir_month`, `csir_subject`, `parent_mobile_no_1`, `parent_mobile_no_2`, `local_guardian_mobile_no`, `local_guardian_state_id`, `local_guardian_pincode` ) 
					VALUES 
					(:student_id,:program_id,:campus_id,:date_of_admission,:first_name,:middle_name,:last_name,:hindi_name,:enrollment_no,:dob,:birth_place,:category_id,:sub_category,:religion_id,:gender,:marital_status,:area,:blood_group,:nationality,:communication_addr,:comm_city,:comm_state_id,:comm_pincode,:comm_phone_no,:comm_email,:father_first_name,:father_last_name,:father_profession,:father_office_addr,:father_city,:father_state_id,:father_pincode,:father_phone_no,:father_email,:mother_first_name,:mother_last_name,:mother_profession,:permanent_addr,:perm_city,:perm_state_id,:perm_pincode,:perm_phone_no,:perm_email,:local_guardian_name,:local_guardian_addr,:local_guard_city,:local_guard_phone_no,:admission_category_id,:admit_card,:jee_rank_card,:jee_roll_no,:jee_rank_pos,:jee_seat_allot_letter,:marksheet_10,:cert_10,:percentage_10,:board_id_10,:board_10_passing_state,:marksheet_12,:cert_12,:percentage_12,:board_id_12,:board_12_passing_state,:marksheet_grad,:degree_grad,:percentage_grad,:university_grad_id,:marksheet_pg,:degree_pg,:percentage_pg,:university_pg_id,:gate_score_card,:gate_year,:gate_score,:cat_score_card,:cat_year,:cat_score,:transfer_cert,:character_cert,:caste_cert,:ph_cert,:passport,:passport_no,:validity_period,:DASA,:remark,:anti_rag_st,:med_cert,:admission_letter, :status_id ,:log_id, :sem_code, :year,:section,:aadhaar,:hostel_no,:hostel_room,:dasa_country, :migration_cert, :mcaip, :net, :net_year, :net_month, :net_subject, :jrf, :jrf_year, :jrf_month, :jrf_subject, :csir, :csir_year, :csir_month, :csir_subject, :mobile_no2, :mobile_no3, :mobile_no4, :state_id4, :pincode4)";

				$ac_on = "Entered a new student with enrollment_no " . $enrollment_no;
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'student_original';

				$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

				$stmt = $conn->prepare($sql);

				$student_id = NULL;
				$date_of_admission = NULL;
				$board_10_passing_state = '1';
				$board_12_passing_state = '1';
				$section = " ";
				$hostel_no = " ";
				$hostel_room = " ";

				$stmt->bindParam(':student_id', $student_id);
				$stmt->bindParam(':program_id', $program_id);
				$stmt->bindParam(':campus_id', $campus_id);
				$stmt->bindParam(':date_of_admission', $date_of_admission);
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
				$stmt->bindParam(':comm_city', $comm_city);
				$stmt->bindParam(':comm_state_id', $comm_state);
				$stmt->bindParam(':comm_pincode', $comm_pincode);
				$stmt->bindParam(':comm_phone_no', $comm_phone_no);
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
				$stmt->bindParam(':transfer_cert', $transfer_cert);
				$stmt->bindParam(':character_cert', $character_cert);
				$stmt->bindParam(':caste_cert', $caste_cert);
				$stmt->bindParam(':ph_cert', $ph_cert);
				$stmt->bindParam(':passport', $passport);
				$stmt->bindParam(':passport_no', $passport_no);
				$stmt->bindParam(':validity_period', $validity_period);
				$stmt->bindParam(':DASA', $DASA);
				$stmt->bindParam(':remark', $remark);
				$stmt->bindParam(':anti_rag_st', $anti_rag_st);
				$stmt->bindParam(':med_cert', $med_cert);
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
				$stmt->bindParam(':migration_cert', $migration_cert);
				$stmt->bindParam(':mcaip', $mcaip);

				$stmt->bindParam(':net', $net);
				$stmt->bindParam(':net_year', $net_year);
				$stmt->bindParam(':net_month', $net_month);
				$stmt->bindParam(':net_subject', $net_subject);
				$stmt->bindParam(':jrf', $jrf);
				$stmt->bindParam(':jrf_year', $jrf_year);
				$stmt->bindParam(':jrf_month', $jrf_month);
				$stmt->bindParam(':jrf_subject', $jrf_subject);
				$stmt->bindParam(':csir', $csir);
				$stmt->bindParam(':csir_year', $csir_year);
				$stmt->bindParam(':csir_month', $csir_month);
				$stmt->bindParam(':csir_subject', $csir_subject);
				$stmt->bindParam(':mobile_no2', $mobile_no2);
				$stmt->bindParam(':mobile_no3', $mobile_no3);
				$stmt->bindParam(':mobile_no4', $mobile_no4);
				$stmt->bindParam(':state_id4', $state_id4);
				$stmt->bindParam(':pincode4', $pincode4);

				$stmt->execute();

				$msg = "The roll number given is $enrollment_no.";

				$stmt2 = $conn->prepare("SELECT student_id, date_of_admission FROM student_original WHERE enrollment_no = '$enrollment_no'");
				$stmt2->execute();
				$result2 = $stmt2->fetchAll();
				
				$student_id =$result2[0][0];
				$date_of_admission = $result2[0][1];
				
				$sqlQuery = "INSERT INTO `student` (`student_id`, `program_id`, `campus_id`, `date_of_admission`, `first_name`, `middle_name`, `last_name`, `hindi_name`, `enrollment_no`, `dob`, `birth_place`, `category_id`, `sub_category`, `religion_id`, `gender`, `marital_status`, `area`, `blood_group`, `nationality`, `comm_addr`, `comm_city`, `comm_state_id`, `comm_pincode`, `comm_mobile_no`, `comm_email`, `father_first_name`, `father_last_name`, `father_profession`, `father_office_addr`, `father_city`, `father_state_id`, `father_pincode`, `father_landline`, `father_email`, `mother_first_name`, `mother_last_name`, `mother_profession`, `perm_addr`, `perm_city`, `perm_state_id`, `perm_pincode`, `perm_landline`, `perm_email`, `local_guardian_name`, `local_guardian_addr`, `local_guardian_city`, `local_guardian_landline`, `admission_category_id`, `admit_card`, `jee_rank_card`, `jee_roll_no`, `jee_rank_pos`, `jee_seat_allot_letter`, `marksheet_10`, `cert_10`, `percentage_10`, `board_id_10`, `board_10_passing_state_id`, `marksheet_12`, `cert_12`, `percentage_12`, `board_id_12`, `board_12_passing_state_id`, `marksheet_grad`, `degree_grad`, `percentage_grad`, `university_grad_id`, `marksheet_pg`, `degree_pg`, `percentage_pg`, `university_pg_id`, `gate_score_card`, `gate_year`, `gate_score`, `cat_score_card`, `cat_year`, `cat_score`, `transfer_cert`, `character_cert`, `caste_cert`, `ph_cert`, `passport`, `passport_no`, `passport_expiry`, `DASA`, `remark`, `anti_rag_st`, `med_cert`,  `admission_letter`, `status_value_id`, `log_id`, `sem_code`, `year`, `section`, `aadhaar`, `hostel_no`, `hostel_room`, `dasa_country`,`migration_cert`, `mcaip`, `net`, `net_year`, `net_month`, `net_subject`, `jrf`, `jrf_year`, `jrf_month`, `jrf_subject`, `csir`, `csir_year`, `csir_month`, `csir_subject`, `parent_mobile_no_1`, `parent_mobile_no_2`, `local_guardian_mobile_no`, `local_guardian_state_id`, `local_guardian_pincode` ) 
					VALUES 
					(:student_id,:program_id,:campus_id,:date_of_admission,:first_name,:middle_name,:last_name,:hindi_name,:enrollment_no,:dob,:birth_place,:category_id,:sub_category,:religion_id,:gender,:marital_status,:area,:blood_group,:nationality,:communication_addr,:comm_city,:comm_state_id,:comm_pincode,:comm_phone_no,:comm_email,:father_first_name,:father_last_name,:father_profession,:father_office_addr,:father_city,:father_state_id,:father_pincode,:father_phone_no,:father_email,:mother_first_name,:mother_last_name,:mother_profession,:permanent_addr,:perm_city,:perm_state_id,:perm_pincode,:perm_phone_no,:perm_email,:local_guardian_name,:local_guardian_addr,:local_guard_city,:local_guard_phone_no,:admission_category_id,:admit_card,:jee_rank_card,:jee_roll_no,:jee_rank_pos,:jee_seat_allot_letter,:marksheet_10,:cert_10,:percentage_10,:board_id_10,:board_10_passing_state,:marksheet_12,:cert_12,:percentage_12,:board_id_12,:board_12_passing_state,:marksheet_grad,:degree_grad,:percentage_grad,:university_grad_id,:marksheet_pg,:degree_pg,:percentage_pg,:university_pg_id,:gate_score_card,:gate_year,:gate_score,:cat_score_card,:cat_year,:cat_score,:transfer_cert,:character_cert,:caste_cert,:ph_cert,:passport,:passport_no,:validity_period,:DASA,:remark,:anti_rag_st,:med_cert,:admission_letter, :status_id ,:log_id, :sem_code, :year,:section,:aadhaar,:hostel_no,:hostel_room,:dasa_country, :migration_cert, :mcaip, :net, :net_year, :net_month, :net_subject, :jrf, :jrf_year, :jrf_month, :jrf_subject, :csir, :csir_year, :csir_month, :csir_subject, :mobile_no2, :mobile_no3, :mobile_no4, :state_id4, :pincode4)";

				$ac_on = "Entered a new student with enrollment_no " . $enrollment_no;
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'student';

				$log_id = ecell_log_procedure($s_i, $r, $sqlQuery, $ac_on, $conn, $tn);

				$queryStudent = $conn->prepare($sqlQuery);

				

				$board_10_passing_state = '1';
				$board_12_passing_state = '1';
				$section = " ";
				$hostel_no = " ";
				$hostel_room = " ";

				$queryStudent->bindParam(':student_id', $student_id);
				$queryStudent->bindParam(':program_id', $program_id);
				$queryStudent->bindParam(':campus_id', $campus_id);
				$queryStudent->bindParam(':date_of_admission', $date_of_admission);
				$queryStudent->bindParam(':first_name', $first_name);
				$queryStudent->bindParam(':middle_name', $middle_name);
				$queryStudent->bindParam(':last_name', $last_name);
				$queryStudent->bindParam(':hindi_name', $hindi_name);
				$queryStudent->bindParam(':enrollment_no', $enrollment_no);
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
				$queryStudent->bindParam(':communication_addr', $communication_addr);
				$queryStudent->bindParam(':comm_city', $comm_city);
				$queryStudent->bindParam(':comm_state_id', $comm_state);
				$queryStudent->bindParam(':comm_pincode', $comm_pincode);
				$queryStudent->bindParam(':comm_phone_no', $comm_phone_no);
				$queryStudent->bindParam(':comm_email', $email);
				$queryStudent->bindParam(':father_first_name', $father_first_name);
				$queryStudent->bindParam(':father_last_name', $father_last_name);
				$queryStudent->bindParam(':father_profession', $father_profession);
				$queryStudent->bindParam(':father_office_addr', $father_office_addr);
				$queryStudent->bindParam(':father_city', $city2);
				$queryStudent->bindParam(':father_state_id', $state_id2);
				$queryStudent->bindParam(':father_pincode', $pincode2);
				$queryStudent->bindParam(':father_phone_no', $phone_no2);
				$queryStudent->bindParam(':father_email', $email2);
				$queryStudent->bindParam(':mother_first_name', $mother_first_name);
				$queryStudent->bindParam(':mother_last_name', $mother_last_name);
				$queryStudent->bindParam(':mother_profession', $mother_profession);
				$queryStudent->bindParam(':permanent_addr', $permanent_addr);
				$queryStudent->bindParam(':perm_city', $city3);
				$queryStudent->bindParam(':perm_state_id', $state_id3);
				$queryStudent->bindParam(':perm_pincode', $pincode3);
				$queryStudent->bindParam(':perm_phone_no', $phone_no3);
				$queryStudent->bindParam(':perm_email', $email3);
				$queryStudent->bindParam(':local_guardian_name', $local_guardian_name);
				$queryStudent->bindParam(':local_guardian_addr', $local_guardian_addr);
				$queryStudent->bindParam(':local_guard_city', $city4);
				$queryStudent->bindParam(':local_guard_phone_no', $phone_no4);
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
				$queryStudent->bindParam(':board_10_passing_state', $board_10_passing_state);
				$queryStudent->bindParam(':marksheet_12', $marksheet_12);
				$queryStudent->bindParam(':cert_12', $cert_12);
				$queryStudent->bindParam(':percentage_12', $percentage_12);
				$queryStudent->bindParam(':board_id_12', $board_id_12);
				$queryStudent->bindParam(':board_12_passing_state', $board_12_passing_state);
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
				$queryStudent->bindParam(':transfer_cert', $transfer_cert);
				$queryStudent->bindParam(':character_cert', $character_cert);
				$queryStudent->bindParam(':caste_cert', $caste_cert);
				$queryStudent->bindParam(':ph_cert', $ph_cert);
				$queryStudent->bindParam(':passport', $passport);
				$queryStudent->bindParam(':passport_no', $passport_no);
				$queryStudent->bindParam(':validity_period', $validity_period);
				$queryStudent->bindParam(':DASA', $DASA);
				$queryStudent->bindParam(':remark', $remark);
				$queryStudent->bindParam(':anti_rag_st', $anti_rag_st);
				$queryStudent->bindParam(':med_cert', $med_cert);
				$queryStudent->bindParam(':admission_letter', $admission_letter);
				$queryStudent->bindParam(':status_id', $status_id);
				$queryStudent->bindParam(':log_id', $log_id);
				$queryStudent->bindParam(':sem_code', $sem_code);
				$queryStudent->bindParam(':year', $year);
				$queryStudent->bindParam(':section', $section);
				$queryStudent->bindParam(':aadhaar', $aadhaar);
				$queryStudent->bindParam(':hostel_no', $hostel_no);
				$queryStudent->bindParam(':hostel_room', $hostel_room);
				$queryStudent->bindParam(':dasa_country', $dasa_country);
				$queryStudent->bindParam(':migration_cert', $migration_cert);
				$queryStudent->bindParam(':mcaip', $mcaip);
				$queryStudent->bindParam(':net', $net);
				$queryStudent->bindParam(':net_year', $net_year);
				$queryStudent->bindParam(':net_month', $net_month);
				$queryStudent->bindParam(':net_subject', $net_subject);
				$queryStudent->bindParam(':jrf', $jrf);
				$queryStudent->bindParam(':jrf_year', $jrf_year);
				$queryStudent->bindParam(':jrf_month', $jrf_month);
				$queryStudent->bindParam(':jrf_subject', $jrf_subject);
				$queryStudent->bindParam(':csir', $csir);
				$queryStudent->bindParam(':csir_year', $csir_year);
				$queryStudent->bindParam(':csir_month', $csir_month);
				$queryStudent->bindParam(':csir_subject', $csir_subject);
				$queryStudent->bindParam(':mobile_no2', $mobile_no2);
				$queryStudent->bindParam(':mobile_no3', $mobile_no3);
				$queryStudent->bindParam(':mobile_no4', $mobile_no4);
				$queryStudent->bindParam(':state_id4', $state_id4);
				$queryStudent->bindParam(':pincode4', $pincode4);
				$queryStudent->execute();

				$tdob = explode('-', $dob);
				$dob = $tdob[1] . '/' . $tdob[2] . '/' . $tdob[0];

				$conn->commit();

				
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
} else if(isset($_SESSION['email']) ){

	$registered_email = $_SESSION['email'];
	$student_id = ecell_get_val('student_original', 'comm_email', $registered_email, 'student_id');

	if ($student_id) {
		$specialError =  "Student is already enrolled.";
		$showform = false;
	} else {
		try {
			$conn1 = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			// set the PDO error mode to exception
			$conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$conn1->exec("set names 'utf8'");
			$sql = "select * from application_details where comm_email = '" . $registered_email . "' order by registration_timestamp desc;";


			$stmt = $conn1->prepare($sql);
			$stmt->execute();

			$result1 = $stmt->fetchAll();

			$count = $stmt->rowCount();

			if ($count > 0) {
				$result = $result1[0];
			} else {
				$specialError = "Entry is not found in online database";
			}
		} catch (PDOException $e) {
			echo $sql . "<br>" . $e->getMessage();
		}


		$mydetails = $result;

		$type = trim($mydetails['type']);

		$jeeOptions = true;
		$ugOptions = true;
		$gateOptions = true;
		$catOptions = true;
		$pgOptions = true;

		if (strcmp($type, "B.Tech") == 0) {
			$ugOptions = false;
			$gateOptions = false;
			$catOptions = false;
			$pgOptions = false;
		} else if (strcmp($type, "M.Tech") == 0) {
			$jeeOptions = false;
			$catOptions = false;
			$pgOptions = false;
		} else if (strcmp($type, "MBA") == 0) {
			$jeeOptions = false;
			$gateOptions = false;
			$pgOptions = false;
		} else if (strcmp($type, "Ph.D") == 0) {
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
		$phone_no2 = $mydetails['father_landline'];
		$email2 = $mydetails['father_email'];
		$mother_first_name = $mydetails['mother_first_name'];
		$mother_last_name = $mydetails['mother_last_name'];
		$mother_profession = $mydetails['mother_profession'];
		$permanent_addr = $mydetails['perm_addr'];
		$city3 = $mydetails['perm_city'];
		$state_id3 = $mydetails['perm_state_id'];
		$pincode3 = $mydetails['perm_pincode'];
		$phone_no3 = $mydetails['perm_landline'];
		$email3 = $mydetails['perm_email'];
		$local_guardian_name = $mydetails['local_guardian_name'];
		$loca_guardian_addr = $mydetails['local_guardian_addr'];
		$city4 = $mydetails['local_guardian_city'];
		$phone_no4 = $mydetails['local_guardian_landline'];
		$comm_phone_no = $mydetails['comm_mobile_no'];

		$admission_category_id = $mydetails['admission_category_id'];
		$marsheek_10 = $mydetails['marksheet_10'];
		$cert_10 = $mydetails['cert_10'];
		$percentage_10 = $mydetails['percentage_10'];
		//$board_id_10 = $mydetails['board_id_10'];
		$marksheet_12 = $mydetails['marksheet_12'];
		$cert_12 = $mydetails['cert_12'];
		$percentage_12 = $mydetails['percentage_12'];
		//$board_id_12 = $mydetails['board_id_12'];
		//$board_id_10 = $mydetails['board_id_10'];
		$board_id_10 = ecell_updated_board($mydetails['board_id_10'], $_SESSION['staff_id'], $_SESSION['rank'], $conn);
		//$board_id_12 = $mydetails['board_id_12'];
		$board_id_12 = ecell_updated_board($mydetails['board_id_12'], $_SESSION['staff_id'], $_SESSION['rank'],  $conn);

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
		//$university_grad_id = $mydetails['university_grad_id'];
		//$university_pg_id = $mydetails['university_pg_id'];
		$university_grad_id = ecell_updated_university($mydetails['university_grad_id'],  $_SESSION['staff_id'], $_SESSION['rank'], $conn);
		$university_pg_id = ecell_updated_university($mydetails['university_pg_id'],  $_SESSION['staff_id'], $_SESSION['rank'], $conn);

		$transfer_cert = $mydetails['transfer_cert'];
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

		//change it
		$state_id4 = $mydetails['local_guardian_state_id'];
		$pincode4 = $mydetails['local_guardian_pincode'];

		$year = date('Y');

		$campus_name = "Allahabad";
		$sql = "SELECT campus_id FROM campus WHERE campus_name=:campus_name";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':campus_name', $campus_name);
		$stmt->execute();

		$campus_id = $stmt->fetchAll()[0][0];	

		$campus_id = $mydetails['campus_id'];

		$sql = "SELECT enrollment_no FROM student_original WHERE year=:year AND program_id=:program_id ORDER BY enrollment_no DESC";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':program_id', $program_id);
		$stmt->bindParam(':year', $year);
		$stmt->execute();

		if ($stmt->rowCount()) {
			$tentative_enrollment_no = substr($stmt->fetchAll()[0][0], -3);
			$tentative_enrollment_no++;
		} else {
			$tentative_enrollment_no = 1;
		}

		$sql   = "SELECT * FROM program WHERE program_id=:program_id";
		$stmt  = $conn->prepare($sql);
		$stmt->bindParam(':program_id', $program_id);
		$stmt->execute();
		$result = $stmt->fetchAll();
		if ($stmt->rowCount() == 1) {
			foreach ($result as $row) {
				$program_prefix = $row['program_prefix'];
			}
		} else {
			$specialError = "Please select valid Program";
		}

		$tentative_enrollment_no = $program_prefix . date("Y") . str_pad((string) $tentative_enrollment_no, 3, "0", STR_PAD_LEFT);

	}
}

$month_array  = array('1','2','3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
$blood_groups = array('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-', 'NA');

$netSubjects  = array("Chemical Sciences (Analytical Chemistry)","Chemical Sciences (Inorganic Chemistry)","Chemical Sciences (Organic Chemistry)","Chemical Sciences (Physical Chemistry)","Chemical Sciences (Others)","Earth Sciences (Geology)","Earth Sciences (Geophysics)","Earth Sciences (Meteorology)","Earth Sciences (Oceanography)","Earth Sciences (Physical Geography)","Earth Sciences (Others)","Life Sciences (Biochemistry)","Life Sciences (Biodiversity and Taxonomy)","Life Sciences (Biotechnology)","Life Sciences (Botany)","Life Sciences (Genetics)","Life Sciences (Microbiology)","Life Sciences (Physiology)","Life Sciences (Zoology)","Life Sciences (Others)","Mathematical Sciences (Applied Mathematics)","Mathematical Sciences (Pure Mathematics)","Mathematical Sciences (Statistics)","Mathematical Sciences (Others)","Physical Sciences (Atomic and Molecular Physics)","Physical Sciences (Classical Dynamics)","Physical Sciences (Condensed Matter Physics)","Physical Sciences (Electromagnetics)","Physical Sciences (Experimental Design)","Physical Sciences (Electronics)","Physical Sciences (Nuclear and Particle Physics)","Physical Sciences (Quantum Physics)","Physical Sciences (Thermodynamics)","Physical Sciences (Others)");
$csirSubjects = array("Chemical Sciences ","Earth Sciences ", "Life Sciences ", "Mathematical Sciences ", "Physical Sciences ");

$TBS->MergeBlock('netMonthBlk, jrfMonthBlk, csirMonthBlk', $month_array);
$TBS->MergeBlock('bloodgroupBlk', $blood_groups);
$TBS->MergeBlock('netSubjectBlk, jrfSubjectBlk', $netSubjects);
$TBS->MergeBlock('csirSubjectBlk', $csirSubjects);

$TBS->MergeBlock('genderBlk', array('Male', 'Female', 'Other'));

$TBS->MergeBlock('board12Blk, board10Blk', $conn, 'SELECT * from board');
$TBS->MergeBlock('program', $conn, 'SELECT * FROM program');
$TBS->MergeBlock('religion', $conn, 'SELECT * FROM religion');
$TBS->MergeBlock('category , acategoryblk', $conn, 'SELECT * FROM student_category');
$TBS->MergeBlock('campus', $conn, 'SELECT * FROM campus WHERE campus_id != 2');
$TBS->MergeBlock('universityBlk, PGUniversityBlk', $conn, 'SELECT * FROM universities');
$TBS->MergeBlock('state, state2, state3, state4', $conn, 'SELECT * FROM state');
$TBS->MergeBlock('sem_code_description', $conn, 'SELECT * FROM sem_code_description');

$TBS->Show();

?>