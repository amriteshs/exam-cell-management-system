<?php

include_once('../../includes/include.php');

function test_input($str) {
	$str = trim($str);
	$str = htmlspecialchars($str);
	return $str;
}

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('enroll_students_csv.html');

$show_form  = "y";
$success    = "";
$msg_err = "";

if (isset($_POST['submit'])) {

	if ($_FILES["file"]["error"] > 0) {

		$msg_err .= "File not found.\n";

	} else {

		$uploadedFile = $_FILES['file']['tmp_name'];
		$uploadedFP = fopen($uploadedFile, 'rb');

		if (!feof($uploadedFP)) {

			$len = -1;

			while ($data = fgetcsv($uploadedFP)) {

				if ($len == -1) {
					$len = count($data);
				}
				if ($len != count($data)) {
					$msg_err .= "Please upload correct CSV file.\n";
					break;
				}

			}

		}

	}
}

if (isset($_FILES['file']['name']) && !$msg_err) {
	if (isset($_POST['year_of_joining'])) {
		$year_of_joining = $_POST['year_of_joining'];
	}
	if (isset($_POST['sem_code_of_joining'])) {
		$sem_code_of_joining = $_POST['sem_code_of_joining'];
	}
	if (isset($_POST['program'])) {
		$program_id = $_POST['program'];
	}

	if ($_FILES["file"]["error"] > 0) {
		$msg_err .= "File not found.";
	} else {
		$uploadedFile = $_FILES['file']['tmp_name'];
		$uploadedFileCount = count(file($uploadedFile));
		$uploadedFP = fopen($uploadedFile, 'rb');
		$linecount  = 0;
		if (!feof($uploadedFP)) {
			while ($data2 = fgetcsv($uploadedFP)) {
				if($linecount == 0) {
					$linecount++;
					continue;
				}

				$linecount++;

				$r_number =  test_input($data2[4]);

				$sql = "SELECT * FROM student_original WHERE enrollment_no=:roll_no";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':roll_no', $r_number);

				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$msg_err .= "Student at line no. $linecount has already been added.\n";
				}

			}
		}
	}

	$linecount = 0;
	if ($msg_err == "") {
		$uploadedFile = $_FILES['file']['tmp_name'];
		$uploadedFileCount = count(file($uploadedFile));
		$uploadedFP = fopen($uploadedFile, 'rb');
		$linecount  = 0;
		$conn->beginTransaction();
		if (!feof($uploadedFP)) {
			while ($data2 = fgetcsv($uploadedFP)) {
				if($linecount == 0) {
					$linecount++;
					continue;
				}

				$course_name =  test_input($data2[0]);
				$year =  test_input($data2[1]);
				$name_eng =  test_input($data2[2]);
				$name_hindi =  test_input($data2[3]);
				$r_number =  test_input($data2[4]);
				$dob =  trim($data2[5]);
				$category =  test_input($data2[6]);
				$religion =  test_input($data2[7]);
				$Gender =  test_input($data2[8]);
				$area =  test_input($data2[9]);
				$communication_address_1 =  test_input($data2[10]);
				$communication_address_city =  test_input($data2[11]);
				$communication_address_state =  test_input($data2[12]);
				$communication_address_pin =  test_input($data2[13]);
				$c_ph_no_stdcode =  test_input($data2[14]);
				$c_ph_no =  test_input($data2[15]);
				$c_email =  test_input($data2[16]);
				$fathers_name =  test_input($data2[17]);
				$fathers_profession =  test_input($data2[18]);
				$fathers_address =  test_input($data2[19]);
				$fathers_pincode =  test_input($data2[20]);
				$fathers_ph_no_stdcode =  test_input($data2[21]);
				$fathers_ph_no =  test_input($data2[22]);
				$mothers_name =  test_input($data2[23]);
				$mothers_profession =  test_input($data2[24]);
				$mothers_address =  test_input($data2[25]);
				$mothers_city =  test_input($data2[26]);
				$mothers_state =  test_input($data2[27]);
				$m_pincode =  test_input($data2[28]);
				$mothers_ph_no_stdcode =  test_input($data2[29]);
				$mothers_ph_no =  test_input($data2[30]);
				$m_city =  test_input($data2[31]);
				$lg_name =  test_input($data2[32]);
				$lg_address =  test_input($data2[33]);
				$lg_ph_no =  test_input($data2[34]);
				$admit_card =  test_input($data2[35]);
				$x_marksheet =  test_input($data2[36]);
				$x_certificate =  test_input($data2[37]);
				$xii_marksheet =  test_input($data2[38]);
				$xii_certificate =  test_input($data2[39]);
				$graduation_marksheet =  test_input($data2[40]);
				$graduation_certificate =  test_input($data2[41]);
				$pg_marksheet =  test_input($data2[42]);
				$pg_certificate =  test_input($data2[43]);
				$tc =  test_input($data2[44]);
				$character =  test_input($data2[45]);
				$aieee =  test_input($data2[46]);
				$aieee_rank =  test_input($data2[47]);
				$aieee_seat_allot_letter =  test_input($data2[48]);
				$gate =  test_input($data2[49]);
				$gate_score =  test_input($data2[50]);
				$cat =  test_input($data2[51]);
				$cat_score =  test_input($data2[52]);
				$d_registration =  trim($data2[53]);
				$data_entered_by =  test_input($data2[54]);
				$campus =  test_input($data2[55]);
				$caste_certificate =  test_input($data2[56]);
				$cat_year =  test_input($data2[57]);
				$gate_year =  test_input($data2[58]);
				$remarks =  test_input($data2[59]);
				$percentage_12 =  test_input($data2[60]);
				$edcil_letter =  test_input($data2[61]);
				$passport =  test_input($data2[62]);
				$board_12 =  test_input($data2[63]);
				$bg =  test_input($data2[64]);
				$validity_period =  test_input($data2[65]);
				$A_Category =  test_input($data2[66]);
				$AIEEE_Roll_No =  test_input($data2[67]);
				$Insurance = test_input($data2[68]);

				$campus_id = ecell_get_val('campus', 'campus_name', $campus, 'campus_id');
				$category_id = ecell_get_val('student_category', 'category_name', $category, 'category_id');
				$religion_id = ecell_get_val('religion', 'religion_name', $religion, 'religion_id');
				$state_id = ecell_get_val('state', 'state_name', $communication_address_state, 'state_id');
				$A_Category = ecell_get_val('student_category', 'category_name', $A_Category, 'category_id');

				if (!$campus_id) {
					$campus_id = 1;
				}

				if (!$category_id) {
					$category_id = 1;
				}

				if ($religion_id) {
					$category_id = 1;
				}

				if ($religion_id == 0) {
					$religion_id = 1;
				}

				if (!$state_id) {
					$state_id = 1;
				}


				$board_12 = ecell_get_val('board', 'board_name', $board_12, 'board_id');

				$dob = (string) $dob;
				$m_first = 0;
				if (strpos($dob, '-', 0)) {
					$dob = explode('-', $dob);
				} else {
					$m_first = 1;
					$dob = explode('/', $dob);
				}

				if ($m_first) {
					$day = str_pad($dob[1], 2, '0', STR_PAD_LEFT);
					$month = str_pad($dob[0], 2, '0', STR_PAD_LEFT);
					$year = $dob[2];
				} else {
					$day = str_pad($dob[0], 2, '0', STR_PAD_LEFT);
					$month = str_pad($dob[1], 2, '0', STR_PAD_LEFT);
					$year = $dob[2];
				}

				$dob = $year.'-'.$month.'-'.$day; 

				$dob1 = (string) $d_registration;
				$m_first = 0;
				if (strpos($dob1, '-', 0)) 
					$dob1 = explode('-', $dob1);
				else {
					$m_first = 1;
					$dob1 = explode('/', $dob1);
				}

				if ($m_first) {
					$day = str_pad($dob1[1], 2, '0', STR_PAD_LEFT);
					$month = str_pad($dob1[0], 2, '0', STR_PAD_LEFT);
					$year = $dob1[2];
				} else {
					$day = str_pad($dob1[0], 2, '0', STR_PAD_LEFT);
					$month = str_pad($dob1[1], 2, '0', STR_PAD_LEFT);
					$year = $dob1[2];
				}

				$d_registration = $year.'-'.$month.'-'.$day; 

				$status_id = ecell_get_status('on');



				$submit_query = "INSERT INTO `student_original` (`student_id`, `program_id`, `campus_id`, `date_of_admission`, 

`first_name`, `middle_name`, `last_name`, `hindi_name`, `enrollment_no`, `dob`, `birth_place`, `category_id`, `sub_category`, `religion_id`, `gender`, `marital_status`, `area`, `blood_group`, `nationality`, `comm_addr`, `comm_city`, `comm_state_id`, `comm_pincode`, `comm_mobile_no`, `comm_email`, `father_first_name`, `father_last_name`, `father_profession`, `father_office_addr`, `father_city`, `father_state_id`, `father_pincode`, `father_landline`, `father_email`, `mother_first_name`, `mother_last_name`, `mother_profession`, `perm_addr`, `perm_city`, `perm_state_id`, `perm_pincode`, `perm_landline`, `perm_email`, `local_guardian_name`, `local_guardian_addr`, `local_guardian_city`, `local_guardian_mobile_no`, `admission_category_id`, `admit_card`, `jee_rank_card`, `jee_roll_no`, `jee_rank_pos`, `jee_seat_allot_letter`, `marksheet_10`, `cert_10`, `percentage_10`, `board_id_10`, `board_10_passing_state_id`, 

 `marksheet_12`, `cert_12`, `percentage_12`, `board_id_12`, `board_12_passing_state_id`, `marksheet_grad`, `degree_grad`, `percentage_grad`, `university_grad_id`, `marksheet_pg`, `degree_pg`, `percentage_pg`, `university_pg_id`, `gate_score_card`, `gate_year`, `gate_score`, `cat_score_card`, `cat_year`, `cat_score`, 
 
 `transfer_cert`, `character_cert`, `caste_cert`, `ph_cert`, `passport`, `passport_no`, `passport_expiry`, `mcaip`, `DASA`, `remark`, `anti_rag_st`, `anti_rag_pr`, `med_cert`, `muslim_minority`, `other_minority`, `admission_letter`, `status_value_id`, `log_id`, `sem_code`, `year`, `section`, `aadhaar`, `hostel_no`, `hostel_room`, `dasa_country`) VALUES (NULL, :program_id, :campus_id, :date_of_admission, 
 :first_name, :middle_name, :last_name, :hindi_name, :enrollment_no, :dob, :birth_place, :category_id, :sub_category, :religion_id, :gender, :marital_status, :area, :blood_group, :nationality, :comm_addr, :comm_city, :comm_state_id, :comm_pincode, :comm_phone_no, :comm_email, :father_first_name, :father_last_name, :father_profession, :father_office_addr, :father_city, :father_state_id, :father_pincode, :father_phone_no, :father_email, :mother_first_name, :mother_last_name, :mother_profession, :permanent_addr, :perm_city, :perm_state_id, :perm_pincode, :perm_phone_no, :perm_email, :local_guardian_name, :local_guardian_addr, :local_guard_city, :local_guard_phone_no, :admission_category_id, :admit_card, :jee_rank_card, :jee_roll_no, :jee_rank_pos, :jee_seat_allot_letter, :marksheet_10, :cert_10, :percentage_10, :board_id_10, :board_10_passing_state, :marksheet_12, :cert_12, :percentage_12, :board_id_12, :board_12_passing_state, :marksheet_grad, :degree_grad, :percentage_grad, :university_grad_id, :marksheet_pg, :degree_pg, :percentage_pg, :university_pg_id, :gate_score_card, :gate_year, :gate_score, :cat_score_card, :cat_year, :cat_score, :tc, :character_cert, :caste_cert, :ph_cert, :passport, :passport_no, :validity_period, :mcaip, :DASA, :remark, :anti_rag_st, :anti_rag_pr, :med_cert, :muslim_minority, :other_minority, :admission_letter, :status_id, :log_id, :sem_code, :year, :section, :aadhaar, :hostel_no, :hostel_room, :dasa_country)";

				$ac_on = "Entered a new student ".$r_number;
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'student_original';
				$log_id = ecell_log_procedure($s_i,$r,$submit_query,$ac_on,$conn,$tn);

				$stmt = $conn->prepare($submit_query);

				$date_of_admission = $d_registration;
				$photo = 0;
				$mime_photo = 0;
				$briefcase = 0;
				$mime_briefcase = 0;
				$first_name = $name_eng;
				$middle_name = ' ';
				$last_name = ' ';
				$hindi_name = $name_hindi;
				$enrollment_no = $r_number;
				$birth_place = ' ';
				$sub_category = ' ';
				$gender = $Gender;
				$marital_status = ' ';
				$blood_group = $bg;
				$nationality = ' ';
				$communication_addr = $communication_address_1;
				$comm_city = $communication_address_city;
				$comm_state_id = $state_id;
				$comm_pincode = $communication_address_pin;
				$comm_phone_no = $c_ph_no;
				$comm_email = $c_email;
				$father_first_name = $fathers_name;
				$father_last_name = ' ';
				$father_profession = $fathers_profession;
				$father_office_addr = $fathers_address;
				$father_city = ' ';
				$father_state_id = 0;
				$father_pincode = $fathers_pincode;
				$father_phone_no = $fathers_ph_no;
				$father_email = ' ';
				$mother_first_name = $mothers_name;
				$mother_last_name = ' ';
				$mother_profession = $mothers_profession;
				$permanent_addr = ' ';
				$perm_city = ' ';
				$perm_state_id = 0;
				$perm_pincode = ' ';
				$perm_phone_no = ' ';
				$perm_email = ' ';
				$local_guardian_name = $lg_name;
				$local_guardian_addr = $lg_address;
				$local_guard_city = ' ';
				$local_guard_phone_no = $lg_ph_no;
				$admission_category_id = $A_Category == 0 ? 1: $A_Category;
				$jee_rank_card = $aieee;
				$jee_roll_no = $AIEEE_Roll_No;
				$jee_rank_pos = $aieee_rank;
				$jee_seat_allot_letter = $aieee_seat_allot_letter;
				$marksheet_10 = $x_marksheet;
				$cert_10 = $x_certificate;
				$percentage_10 = ' ';
				$board_id_10 = 0;
				$board_10_passing_state = 0;
				$marksheet_12 = $xii_marksheet;
				$cert_12 = $xii_certificate;
				$board_id_12 = $board_12;
				$board_12_passing_state = 0;
				$marksheet_grad = $graduation_marksheet;
				$degree_grad = $graduation_certificate;
				$percentage_grad = ' ';
				$university_grad_id = 0;
				$marksheet_pg = $pg_marksheet;
				$degree_pg = $pg_certificate;
				$percentage_pg = ' ';
				$university_pg_id = 0;
				$gate_score_card = $gate;
				$gate_year = '0000-00-00';
				$cat_score_card = $cat;
				$cat_year = '0000';
				$character_cert = $character;
				$caste_cert = ' ';
				$ph_cert = ' ';
				$passport_no = ' ';
				$mcaip = ' ';
				$DASA = ' ';
				$remark = $remarks;
				$anti_rag_st = ' ';
				$anti_rag_pr = ' ';
				$med_cert = ' ';
				$muslim_minority = ' ';
				$other_minority = ' ';
				$admission_letter = ' ';
				$section = 'A';
				$aadhaar = '000000000000';
				$hostel_no = ' ';
				$hostel_room = ' ';
				$dasa_country = ' ';

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
				$stmt->bindParam(':comm_addr', $communication_addr);
				$stmt->bindParam(':comm_city', $comm_city);
				$stmt->bindParam(':comm_state_id', $comm_state_id);
				$stmt->bindParam(':comm_pincode', $comm_pincode);
				$stmt->bindParam(':comm_phone_no', $comm_phone_no);
				$stmt->bindParam(':comm_email', $comm_email);
				$stmt->bindParam(':father_first_name', $father_first_name);
				$stmt->bindParam(':father_last_name', $father_last_name);
				$stmt->bindParam(':father_profession', $father_profession);
				$stmt->bindParam(':father_office_addr', $father_office_addr);
				$stmt->bindParam(':father_city', $father_city);
				$stmt->bindParam(':father_state_id', $father_state_id);
				$stmt->bindParam(':father_pincode', $father_pincode);
				$stmt->bindParam(':father_phone_no', $father_phone_no);
				$stmt->bindParam(':father_email', $father_email);
				$stmt->bindParam(':mother_first_name', $mother_first_name);
				$stmt->bindParam(':mother_last_name', $mother_last_name);
				$stmt->bindParam(':mother_profession', $mother_profession);
				$stmt->bindParam(':permanent_addr', $permanent_addr);
				$stmt->bindParam(':perm_city', $perm_city);
				$stmt->bindParam(':perm_state_id', $perm_state_id);
				$stmt->bindParam(':perm_pincode', $perm_pincode);
				$stmt->bindParam(':perm_phone_no', $perm_phone_no);
				$stmt->bindParam(':perm_email', $perm_email);
				$stmt->bindParam(':local_guardian_name', $local_guardian_name);
				$stmt->bindParam(':local_guardian_addr', $local_guardian_addr);
				$stmt->bindParam(':local_guard_city', $local_guard_city);
				$stmt->bindParam(':local_guard_phone_no', $local_guard_phone_no);
				$stmt->bindParam(':admission_category_id', $admission_category_id);
				$stmt->bindParam(':admit_card', $admit_card);
				$stmt->bindParam(':jee_rank_card', $jee_rank_card);
				$stmt->bindParam(':jee_roll_no', $jee_roll_no);
				$stmt->bindParam(':jee_rank_pos', $jee_rank_pos);
				$stmt->bindParam(':jee_seat_allot_letter', $jee_seat_allot_letter);
				$stmt->bindParam(':marksheet_10', $marksheet_10);
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
				$stmt->bindParam(':sem_code', $sem_code_of_joining);
				$stmt->bindParam(':year', $year_of_joining);
				$stmt->bindParam(':section', $section);
				$stmt->bindParam(':aadhaar', $aadhaar);
				$stmt->bindParam(':hostel_no', $hostel_no);
				$stmt->bindParam(':hostel_room', $hostel_room);
				$stmt->bindParam(':dasa_country', $dasa_country);

				try {
					$stmt->execute();
				} catch (PDOException $e) {
					$msg_err .= "Problem on line no. ".($linecount + 1).": ".$e->getMessage()."\n\n";
				}

				$sql = "SELECT student_id FROM student_original WHERE enrollment_no = :enrollment_no";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(':enrollment_no', $enrollment_no);
				$stmt->execute();
				$student_id = $stmt->fetch()['student_id'];

				$submit_query = "INSERT INTO `student` (`student_id`, `program_id`, `campus_id`, `date_of_admission`, 

`first_name`, `middle_name`, `last_name`, `hindi_name`, `enrollment_no`, `dob`, `birth_place`, `category_id`, `sub_category`, `religion_id`, `gender`, `marital_status`, `area`, `blood_group`, `nationality`, `comm_addr`, `comm_city`, `comm_state_id`, `comm_pincode`, `comm_mobile_no`, `comm_email`, `father_first_name`, `father_last_name`, `father_profession`, `father_office_addr`, `father_city`, `father_state_id`, `father_pincode`, `father_landline`, `father_email`, `mother_first_name`, `mother_last_name`, `mother_profession`, `perm_addr`, `perm_city`, `perm_state_id`, `perm_pincode`, `perm_landline`, `perm_email`, `local_guardian_name`, `local_guardian_addr`, `local_guardian_city`, `local_guardian_mobile_no`, `admission_category_id`, `admit_card`, `jee_rank_card`, `jee_roll_no`, `jee_rank_pos`, `jee_seat_allot_letter`, `marksheet_10`, `cert_10`, `percentage_10`, `board_id_10`, `board_10_passing_state_id`, 

 `marksheet_12`, `cert_12`, `percentage_12`, `board_id_12`, `board_12_passing_state_id`, `marksheet_grad`, `degree_grad`, `percentage_grad`, `university_grad_id`, `marksheet_pg`, `degree_pg`, `percentage_pg`, `university_pg_id`, `gate_score_card`, `gate_year`, `gate_score`, `cat_score_card`, `cat_year`, `cat_score`, 
 
 `transfer_cert`, `character_cert`, `caste_cert`, `ph_cert`, `passport`, `passport_no`, `passport_expiry`, `mcaip`, `DASA`, `remark`, `anti_rag_st`, `anti_rag_pr`, `med_cert`, `muslim_minority`, `other_minority`, `admission_letter`, `status_value_id`, `log_id`, `sem_code`, `year`, `section`, `aadhaar`, `hostel_no`, `hostel_room`, `dasa_country`) VALUES (:student_id, :program_id, :campus_id, :date_of_admission, 
 :first_name, :middle_name, :last_name, :hindi_name, :enrollment_no, :dob, :birth_place, :category_id, :sub_category, :religion_id, :gender, :marital_status, :area, :blood_group, :nationality, :comm_addr, :comm_city, :comm_state_id, :comm_pincode, :comm_phone_no, :comm_email, :father_first_name, :father_last_name, :father_profession, :father_office_addr, :father_city, :father_state_id, :father_pincode, :father_phone_no, :father_email, :mother_first_name, :mother_last_name, :mother_profession, :permanent_addr, :perm_city, :perm_state_id, :perm_pincode, :perm_phone_no, :perm_email, :local_guardian_name, :local_guardian_addr, :local_guard_city, :local_guard_phone_no, :admission_category_id, :admit_card, :jee_rank_card, :jee_roll_no, :jee_rank_pos, :jee_seat_allot_letter, :marksheet_10, :cert_10, :percentage_10, :board_id_10, :board_10_passing_state, :marksheet_12, :cert_12, :percentage_12, :board_id_12, :board_12_passing_state, :marksheet_grad, :degree_grad, :percentage_grad, :university_grad_id, :marksheet_pg, :degree_pg, :percentage_pg, :university_pg_id, :gate_score_card, :gate_year, :gate_score, :cat_score_card, :cat_year, :cat_score, :tc, :character_cert, :caste_cert, :ph_cert, :passport, :passport_no, :validity_period, :mcaip, :DASA, :remark, :anti_rag_st, :anti_rag_pr, :med_cert, :muslim_minority, :other_minority, :admission_letter, :status_id, :log_id, :sem_code, :year, :section, :aadhaar, :hostel_no, :hostel_room, :dasa_country)";

				$ac_on = "Entered a new student ".$r_number;
				$s_i = $_SESSION['staff_id'];
				$r = $_SESSION['rank'];
				$tn = 'student';
				$log_id = ecell_log_procedure($s_i,$r,$submit_query,$ac_on,$conn,$tn);

				$stmt = $conn->prepare($submit_query);

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
				$stmt->bindParam(':comm_addr', $communication_addr);
				$stmt->bindParam(':comm_city', $comm_city);
				$stmt->bindParam(':comm_state_id', $comm_state_id);
				$stmt->bindParam(':comm_pincode', $comm_pincode);
				$stmt->bindParam(':comm_phone_no', $comm_phone_no);
				$stmt->bindParam(':comm_email', $comm_email);
				$stmt->bindParam(':father_first_name', $father_first_name);
				$stmt->bindParam(':father_last_name', $father_last_name);
				$stmt->bindParam(':father_profession', $father_profession);
				$stmt->bindParam(':father_office_addr', $father_office_addr);
				$stmt->bindParam(':father_city', $father_city);
				$stmt->bindParam(':father_state_id', $father_state_id);
				$stmt->bindParam(':father_pincode', $father_pincode);
				$stmt->bindParam(':father_phone_no', $father_phone_no);
				$stmt->bindParam(':father_email', $father_email);
				$stmt->bindParam(':mother_first_name', $mother_first_name);
				$stmt->bindParam(':mother_last_name', $mother_last_name);
				$stmt->bindParam(':mother_profession', $mother_profession);
				$stmt->bindParam(':permanent_addr', $permanent_addr);
				$stmt->bindParam(':perm_city', $perm_city);
				$stmt->bindParam(':perm_state_id', $perm_state_id);
				$stmt->bindParam(':perm_pincode', $perm_pincode);
				$stmt->bindParam(':perm_phone_no', $perm_phone_no);
				$stmt->bindParam(':perm_email', $perm_email);
				$stmt->bindParam(':local_guardian_name', $local_guardian_name);
				$stmt->bindParam(':local_guardian_addr', $local_guardian_addr);
				$stmt->bindParam(':local_guard_city', $local_guard_city);
				$stmt->bindParam(':local_guard_phone_no', $local_guard_phone_no);
				$stmt->bindParam(':admission_category_id', $admission_category_id);
				$stmt->bindParam(':admit_card', $admit_card);
				$stmt->bindParam(':jee_rank_card', $jee_rank_card);
				$stmt->bindParam(':jee_roll_no', $jee_roll_no);
				$stmt->bindParam(':jee_rank_pos', $jee_rank_pos);
				$stmt->bindParam(':jee_seat_allot_letter', $jee_seat_allot_letter);
				$stmt->bindParam(':marksheet_10', $marksheet_10);
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
				$stmt->bindParam(':sem_code', $sem_code_of_joining);
				$stmt->bindParam(':year', $year_of_joining);
				$stmt->bindParam(':section', $section);
				$stmt->bindParam(':aadhaar', $aadhaar);
				$stmt->bindParam(':hostel_no', $hostel_no);
				$stmt->bindParam(':hostel_room', $hostel_room);
				$stmt->bindParam(':dasa_country', $dasa_country);

				try {
					$stmt->execute();
				} catch (PDOException $e) {
					$msg_err .= "Problem on line no. ".($linecount + 1).": ".$e->getMessage()."\n\n";
				}

				$linecount++;
			}
		}
		if ($msg_err) { 
			$conn->rollBack();
		} else {
			$success = "Success";
			$conn->commit();
		}
	}
	// $linecount = 0;
	// if (!$msg_err) {
	// 	$uploadedFile = $_FILES['file']['tmp_name'];
	// 	$uploadedFileCount = count(file($uploadedFile));
	// 	$uploadedFP = fopen($uploadedFile, 'rb');
	// 	$linecount  = 0;
	// 	if (!feof($uploadedFP)) {
	// 		while ($data2 = fgetcsv($uploadedFP)) {
	// 			if($linecount == 0) {
	// 				$linecount++;
	// 				continue;
	// 			}

	// 			$course_name =  test_input($data2[0]);
	// 			$year =  test_input($data2[1]);
	// 			$name_eng =  test_input($data2[2]);
	// 			$name_hindi =  test_input($data2[3]);
	// 			$r_number =  test_input($data2[4]);
	// 			$dob =  trim($data2[5]);
	// 			$category =  test_input($data2[6]);
	// 			$religion =  test_input($data2[7]);
	// 			$Gender =  test_input($data2[8]);
	// 			$area =  test_input($data2[9]);
	// 			$communication_address_1 =  test_input($data2[10]);
	// 			$communication_address_city =  test_input($data2[11]);
	// 			$communication_address_state =  test_input($data2[12]);
	// 			$communication_address_pin =  test_input($data2[13]);
	// 			$c_ph_no_stdcode =  test_input($data2[14]);
	// 			$c_ph_no =  test_input($data2[15]);
	// 			$c_email =  test_input($data2[16]);
	// 			$fathers_name =  test_input($data2[17]);
	// 			$fathers_profession =  test_input($data2[18]);
	// 			$fathers_address =  test_input($data2[19]);
	// 			$fathers_pincode =  test_input($data2[20]);
	// 			$fathers_ph_no_stdcode =  test_input($data2[21]);
	// 			$fathers_ph_no =  test_input($data2[22]);
	// 			$mothers_name =  test_input($data2[23]);
	// 			$mothers_profession =  test_input($data2[24]);
	// 			$mothers_address =  test_input($data2[25]);
	// 			$mothers_city =  test_input($data2[26]);
	// 			$mothers_state =  test_input($data2[27]);
	// 			$m_pincode =  test_input($data2[28]);
	// 			$mothers_ph_no_stdcode =  test_input($data2[29]);
	// 			$mothers_ph_no =  test_input($data2[30]);
	// 			$m_city =  test_input($data2[31]);
	// 			$lg_name =  test_input($data2[32]);
	// 			$lg_address =  test_input($data2[33]);
	// 			$lg_ph_no =  test_input($data2[34]);
	// 			$admit_card =  test_input($data2[35]);
	// 			$x_marksheet =  test_input($data2[36]);
	// 			$x_certificate =  test_input($data2[37]);
	// 			$xii_marksheet =  test_input($data2[38]);
	// 			$xii_certificate =  test_input($data2[39]);
	// 			$graduation_marksheet =  test_input($data2[40]);
	// 			$graduation_certificate =  test_input($data2[41]);
	// 			$pg_marksheet =  test_input($data2[42]);
	// 			$pg_certificate =  test_input($data2[43]);
	// 			$tc =  test_input($data2[44]);
	// 			$character =  test_input($data2[45]);
	// 			$aieee =  test_input($data2[46]);
	// 			$aieee_rank =  test_input($data2[47]);
	// 			$aieee_seat_allot_letter =  test_input($data2[48]);
	// 			$gate =  test_input($data2[49]);
	// 			$gate_score =  test_input($data2[50]);
	// 			$cat =  test_input($data2[51]);
	// 			$cat_score =  test_input($data2[52]);
	// 			$d_registration =  trim($data2[53]);
	// 			$data_entered_by =  test_input($data2[54]);
	// 			$campus =  test_input($data2[55]);
	// 			$caste_certificate =  test_input($data2[56]);
	// 			$cat_year =  test_input($data2[57]);
	// 			$gate_year =  test_input($data2[58]);
	// 			$remarks =  test_input($data2[59]);
	// 			$percentage_12 =  test_input($data2[60]);
	// 			$edcil_letter =  test_input($data2[61]);
	// 			$passport =  test_input($data2[62]);
	// 			$board_12 =  test_input($data2[63]);
	// 			$bg =  test_input($data2[64]);
	// 			$validity_period =  test_input($data2[65]);
	// 			$A_Category =  test_input($data2[66]);
	// 			$AIEEE_Roll_No =  test_input($data2[67]);
	// 			$Insurance = test_input($data2[68]);

	// 			$campus_id = get_val('campus', 'campus_name', $campus, 'campus_id');
	// 			$category_id = get_val('category', 'category_name', $category, 'category_id');
	// 			$religion_id = get_val('religion', 'religion_name', $religion, 'religion_id');
	// 			$state_id = get_val('state', 'state_name', $communication_address_state, 'state_id');
	// 			$A_Category = get_val('category', 'category_name', $A_Category, 'category_id');

	// 			if (strlen($board_12)) {
	// 				$board_12 = get_val('board', 'board_name', $board_12, 'board_name'); 
	// 			} else {
	// 				$board_12 = 'NA';
	// 			}

				// $dob = (string) $dob;

				// if (!strpos($dob, '/', 0)) 
				// 	$dob = explode('-', $dob);
				// else 
				// 	$dob = explode('/', $dob);

				// $day = str_pad($dob[0], 2, '0', STR_PAD_LEFT);
				// $month = str_pad($dob[1], 2, '0', STR_PAD_LEFT);
				// $year = $dob[2];

				// $dob = $year.'-'.$month.'-'.$day; 

				// $dob1 = (string) $d_registration;

				// if (!strpos($dob1, '/', 0)) 
				// 	$dob1 = explode('-', $dob1);
				// else 
				// 	$dob1 = explode('/', $dob1);

				// $day = str_pad($dob1[0], 2, '0', STR_PAD_LEFT);
				// $month = str_pad($dob1[1], 2, '0', STR_PAD_LEFT);
				// $year = $dob1[2];

				// $d_registration = $year.'-'.$month.'-'.$day; 

	// 			$status_id = get_status('on');

	// 			$submit_query = "INSERT INTO `student`(`student_id`, `program_id`, `campus_id`, `date_of_admission`, `photo`, `mime_photo`, `briefcase`, `mime_briefcase`, `first_name`, `middle_name`, `last_name`, `hindi_name`, `enrollment_no`, `dob`, `birth_place`, `category_id`, `sub_category`, `religion_id`, `gender`, `marital_status`, `area`, `blood_group`, `nationality`, `communication_addr`, `comm_city`, `comm_state_id`, `comm_pincode`, `comm_phone_no`, `comm_email`, `father_first_name`, `father_last_name`, `father_profession`, `father_office_addr`, `father_city`, `father_state_id`, `father_pincode`, `father_phone_no`, `father_email`, `mother_first_name`, `mother_last_name`, `mother_profession`, `permanent_addr`, `perm_city`, `perm_state_id`, `perm_pincode`, `perm_phone_no`, `perm_email`, `local_guardian_name`, `local_guardian_addr`, `local_guard_city`, `local_guard_phone_no`, `admission_category_id`, `admit_card`, `jee_rank_card`, `jee_roll_no`, `jee_rank_pos`, `jee_seat_allot_letter`, `marksheet_10`, `cert_10`, `percentage_10`, `board_id_10`, `board_10_passing_state`, `marksheet_12`, `cert_12`, `percentage_12`, `board_id_12`, `board_12_passing_state`, `marksheet_grad`, `degree_grad`, `percentage_grad`, `university_grad_id`, `marksheet_pg`, `degree_pg`, `percentage_pg`, `university_pg_id`, `gate_score_card`, `gate_year`, `gate_score`, `cat_score_card`, `cat_year`, `cat_score`, `tc`, `character_cert`, `caste_cert`, `ph_cert`, `passport`, `passport_no`, `validity_period`, `mcaip`, `DASA`, `remark`, `anti_rag_st`, `anti_rag_pr`, `med_cert`, `muslim_minority`, `other_minority`, `admission_letter`, `status_id`, `log_id`, `sem_code`, `year`, `section`, `aadhaar`, `hostel_no`, `hostel_room`, `dasa_country`) VALUES (:student_id, :program_id, :campus_id, :date_of_admission, :photo, :mime_photo, :briefcase, :mime_briefcase, :first_name, :middle_name, :last_name, :hindi_name, :enrollment_no, :dob, :birth_place, :category_id, :sub_category, :religion_id, :gender, :marital_status, :area, :blood_group, :nationality, :communication_addr, :comm_city, :comm_state_id, :comm_pincode, :comm_phone_no, :comm_email, :father_first_name, :father_last_name, :father_profession, :father_office_addr, :father_city, :father_state_id, :father_pincode, :father_phone_no, :father_email, :mother_first_name, :mother_last_name, :mother_profession, :permanent_addr, :perm_city, :perm_state_id, :perm_pincode, :perm_phone_no, :perm_email, :local_guardian_name, :local_guardian_addr, :local_guard_city, :local_guard_phone_no, :admission_category_id, :admit_card, :jee_rank_card, :jee_roll_no, :jee_rank_pos, :jee_seat_allot_letter, :marksheet_10, :cert_10, :percentage_10, :board_id_10, :board_10_passing_state, :marksheet_12, :cert_12, :percentage_12, :board_id_12, :board_12_passing_state, :marksheet_grad, :degree_grad, :percentage_grad, :university_grad_id, :marksheet_pg, :degree_pg, :percentage_pg, :university_pg_id, :gate_score_card, :gate_year, :gate_score, :cat_score_card, :cat_year, :cat_score, :tc, :character_cert, :caste_cert, :ph_cert, :passport, :passport_no, :validity_period, :mcaip, :DASA, :remark, :anti_rag_st, :anti_rag_pr, :med_cert, :muslim_minority, :other_minority, :admission_letter, :status_id, :log_id, :sem_code, :year, :section, :aadhaar, :hostel_no, :hostel_room, :dasa_country)";

	// 			$ac_on = "Entered a new student ".$r_number;
	// 			$s_i = $_SESSION['staff_id'];
	// 			$r = $_SESSION['rank'];
	// 			$tn = 'student';

	// 			$log_id = log_procedure($s_i,$r,$submit_query,$ac_on,$conn,$tn);

	// 			$stmt = $conn->prepare($submit_query);

	// 			$student_id = NULL;
	// 			$date_of_admission = $d_registration;
	// 			$photo = 0;
	// 			$mime_photo = 0;
	// 			$briefcase = 0;
	// 			$mime_briefcase = 0;
	// 			$first_name = $name_eng;
	// 			$middle_name = ' ';
	// 			$last_name = ' ';
	// 			$hindi_name = $name_hindi;
	// 			$enrollment_no = $r_number;
	// 			$birth_place = ' ';
	// 			$sub_category = ' ';
	// 			$gender = $Gender;
	// 			$marital_status = ' ';
	// 			$blood_group = $bg;
	// 			$nationality = ' ';
	// 			$communication_addr = $communication_address_1;
	// 			$comm_city = $communication_address_city;
	// 			$comm_state_id = $state_id;
	// 			$comm_pincode = $communication_address_pin;
	// 			$comm_phone_no = $c_ph_no;
	// 			$comm_email = $c_email;
	// 			$father_first_name = $fathers_name;
	// 			$father_last_name = ' ';
	// 			$father_profession = $fathers_profession;
	// 			$father_office_addr = $fathers_address;
	// 			$father_city = ' ';
	// 			$father_state_id = 1;
	// 			$father_pincode = $fathers_pincode;
	// 			$father_phone_no = $fathers_ph_no;
	// 			$father_email = ' ';
	// 			$mother_first_name = $mothers_name;
	// 			$mother_last_name = ' ';
	// 			$mother_profession = $mothers_profession;
	// 			$permanent_addr = ' ';
	// 			$perm_city = ' ';
	// 			$perm_state_id = 1;
	// 			$perm_pincode = ' ';
	// 			$perm_phone_no = ' ';
	// 			$perm_email = ' ';
	// 			$local_guardian_name = $lg_name;
	// 			$local_guardian_addr = $lg_address;
	// 			$local_guard_city = ' ';
	// 			$local_guard_phone_no = $lg_ph_no;
	// 			$admission_category_id = $A_Category;
	// 			$jee_rank_card = $aieee;
	// 			$jee_roll_no = $AIEEE_Roll_No;
	// 			$jee_rank_pos = $aieee_rank;
	// 			$jee_seat_allot_letter = $aieee_seat_allot_letter;
	// 			$marksheet_10 = $x_marksheet;
	// 			$cert_10 = $x_certificate;
	// 			$percentage_10 = ' ';
	// 			$board_id_10 = 'NA';
	// 			$board_10_passing_state = 1;
	// 			$marksheet_12 = $xii_marksheet;
	// 			$cert_12 = $xii_certificate;
	// 			$board_id_12 = $board_12;
	// 			$board_12_passing_state = 1;
	// 			$marksheet_grad = $graduation_marksheet;
	// 			$degree_grad = $graduation_certificate;
	// 			$percentage_grad = ' ';
	// 			$university_grad_id = 1;
	// 			$marksheet_pg = $pg_marksheet;
	// 			$degree_pg = $pg_certificate;
	// 			$percentage_pg = ' ';
	// 			$university_pg_id = 1;
	// 			$gate_score_card = $gate;
	// 			$gate_year = '0000-00-00';
	// 			$cat_score_card = $cat;
	// 			$cat_year = '0000';
	// 			$character_cert = $character;
	// 			$caste_cert = ' ';
	// 			$ph_cert = ' ';
	// 			$passport_no = ' ';
	// 			$mcaip = ' ';
	// 			$DASA = ' ';
	// 			$remark = $remarks;
	// 			$anti_rag_st = ' ';
	// 			$anti_rag_pr = ' ';
	// 			$med_cert = ' ';
	// 			$muslim_minority = ' ';
	// 			$other_minority = ' ';
	// 			$admission_letter = ' ';
	// 			$section = 'A';
	// 			$aadhaar = '000000000000';
	// 			$hostel_no = ' ';
	// 			$hostel_room = ' ';
	// 			$dasa_country = ' ';

	// 			$stmt->bindParam(':student_id', $student_id);
	// 			$stmt->bindParam(':program_id', $program_id);
	// 			$stmt->bindParam(':campus_id', $campus_id);
	// 			$stmt->bindParam(':date_of_admission', $date_of_admission);
	// 			$stmt->bindParam(':photo', $photo);
	// 			$stmt->bindParam(':mime_photo', $mime_photo);
	// 			$stmt->bindParam(':briefcase', $briefcase);
	// 			$stmt->bindParam(':mime_briefcase', $mime_briefcase);
	// 			$stmt->bindParam(':first_name', $first_name);
	// 			$stmt->bindParam(':middle_name', $middle_name);
	// 			$stmt->bindParam(':last_name', $last_name);
	// 			$stmt->bindParam(':hindi_name', $hindi_name);
	// 			$stmt->bindParam(':enrollment_no', $enrollment_no);
	// 			$stmt->bindParam(':dob', $dob);
	// 			$stmt->bindParam(':birth_place', $birth_place);
	// 			$stmt->bindParam(':category_id', $category_id);
	// 			$stmt->bindParam(':sub_category', $sub_category);
	// 			$stmt->bindParam(':religion_id', $religion_id);
	// 			$stmt->bindParam(':gender', $gender);
	// 			$stmt->bindParam(':marital_status', $marital_status);
	// 			$stmt->bindParam(':area', $area);
	// 			$stmt->bindParam(':blood_group', $blood_group);
	// 			$stmt->bindParam(':nationality', $nationality);
	// 			$stmt->bindParam(':communication_addr', $communication_addr);
	// 			$stmt->bindParam(':comm_city', $comm_city);
	// 			$stmt->bindParam(':comm_state_id', $comm_state_id);
	// 			$stmt->bindParam(':comm_pincode', $comm_pincode);
	// 			$stmt->bindParam(':comm_phone_no', $comm_phone_no);
	// 			$stmt->bindParam(':comm_email', $comm_email);
	// 			$stmt->bindParam(':father_first_name', $father_first_name);
	// 			$stmt->bindParam(':father_last_name', $father_last_name);
	// 			$stmt->bindParam(':father_profession', $father_profession);
	// 			$stmt->bindParam(':father_office_addr', $father_office_addr);
	// 			$stmt->bindParam(':father_city', $father_city);
	// 			$stmt->bindParam(':father_state_id', $father_state_id);
	// 			$stmt->bindParam(':father_pincode', $father_pincode);
	// 			$stmt->bindParam(':father_phone_no', $father_phone_no);
	// 			$stmt->bindParam(':father_email', $father_email);
	// 			$stmt->bindParam(':mother_first_name', $mother_first_name);
	// 			$stmt->bindParam(':mother_last_name', $mother_last_name);
	// 			$stmt->bindParam(':mother_profession', $mother_profession);
	// 			$stmt->bindParam(':permanent_addr', $permanent_addr);
	// 			$stmt->bindParam(':perm_city', $perm_city);
	// 			$stmt->bindParam(':perm_state_id', $perm_state_id);
	// 			$stmt->bindParam(':perm_pincode', $perm_pincode);
	// 			$stmt->bindParam(':perm_phone_no', $perm_phone_no);
	// 			$stmt->bindParam(':perm_email', $perm_email);
	// 			$stmt->bindParam(':local_guardian_name', $local_guardian_name);
	// 			$stmt->bindParam(':local_guardian_addr', $local_guardian_addr);
	// 			$stmt->bindParam(':local_guard_city', $local_guard_city);
	// 			$stmt->bindParam(':local_guard_phone_no', $local_guard_phone_no);
	// 			$stmt->bindParam(':admission_category_id', $admission_category_id);
	// 			$stmt->bindParam(':admit_card', $admit_card);
	// 			$stmt->bindParam(':jee_rank_card', $jee_rank_card);
	// 			$stmt->bindParam(':jee_roll_no', $jee_roll_no);
	// 			$stmt->bindParam(':jee_rank_pos', $jee_rank_pos);
	// 			$stmt->bindParam(':jee_seat_allot_letter', $jee_seat_allot_letter);
	// 			$stmt->bindParam(':marksheet_10', $marksheet_10);
	// 			$stmt->bindParam(':cert_10', $cert_10);
	// 			$stmt->bindParam(':percentage_10', $percentage_10);
	// 			$stmt->bindParam(':board_id_10', $board_id_10);
	// 			$stmt->bindParam(':board_10_passing_state', $board_10_passing_state);
	// 			$stmt->bindParam(':marksheet_12', $marksheet_12);
	// 			$stmt->bindParam(':cert_12', $cert_12);
	// 			$stmt->bindParam(':percentage_12', $percentage_12);
	// 			$stmt->bindParam(':board_id_12', $board_id_12);
	// 			$stmt->bindParam(':board_12_passing_state', $board_12_passing_state);
	// 			$stmt->bindParam(':marksheet_grad', $marksheet_grad);
	// 			$stmt->bindParam(':degree_grad', $degree_grad);
	// 			$stmt->bindParam(':percentage_grad', $percentage_grad);
	// 			$stmt->bindParam(':university_grad_id', $university_grad_id);
	// 			$stmt->bindParam(':marksheet_pg', $marksheet_pg);
	// 			$stmt->bindParam(':degree_pg', $degree_pg);
	// 			$stmt->bindParam(':percentage_pg', $percentage_pg);
	// 			$stmt->bindParam(':university_pg_id', $university_pg_id);
	// 			$stmt->bindParam(':gate_score_card', $gate_score_card);
	// 			$stmt->bindParam(':gate_year', $gate_year);
	// 			$stmt->bindParam(':gate_score', $gate_score);
	// 			$stmt->bindParam(':cat_score_card', $cat_score_card);
	// 			$stmt->bindParam(':cat_year', $cat_year);
	// 			$stmt->bindParam(':cat_score', $cat_score);
	// 			$stmt->bindParam(':tc', $tc);
	// 			$stmt->bindParam(':character_cert', $character_cert);
	// 			$stmt->bindParam(':caste_cert', $caste_cert);
	// 			$stmt->bindParam(':ph_cert', $ph_cert);
	// 			$stmt->bindParam(':passport', $passport);
	// 			$stmt->bindParam(':passport_no', $passport_no);
	// 			$stmt->bindParam(':validity_period', $validity_period);
	// 			$stmt->bindParam(':mcaip', $mcaip);
	// 			$stmt->bindParam(':DASA', $DASA);
	// 			$stmt->bindParam(':remark', $remark);
	// 			$stmt->bindParam(':anti_rag_st', $anti_rag_st);
	// 			$stmt->bindParam(':anti_rag_pr', $anti_rag_pr);
	// 			$stmt->bindParam(':med_cert', $med_cert);
	// 			$stmt->bindParam(':muslim_minority', $muslim_minority);
	// 			$stmt->bindParam(':other_minority', $other_minority);
	// 			$stmt->bindParam(':admission_letter', $admission_letter);
	// 			$stmt->bindParam(':status_id', $status_id);
	// 			$stmt->bindParam(':log_id', $log_id);
	// 			$stmt->bindParam(':sem_code', $sem_code_of_joining);
	// 			$stmt->bindParam(':year', $year_of_joining);
	// 			$stmt->bindParam(':section', $section);
	// 			$stmt->bindParam(':aadhaar', $aadhaar);
	// 			$stmt->bindParam(':hostel_no', $hostel_no);
	// 			$stmt->bindParam(':hostel_room', $hostel_room);
	// 			$stmt->bindParam(':dasa_country', $dasa_country);
				
	// 			$stmt->execute();

	// 			$linecount++;

	// 		}

	// 		$success = "Success";
	// 	}
	// }
}

$TBS->MergeBlock('program', $conn, "SELECT * FROM program, status WHERE status_name='on'");
$TBS->MergeBlock('sem_code_description', $conn, "SELECT * FROM sem_code_description, status WHERE status_name='on'");

$TBS->Show();

?>
