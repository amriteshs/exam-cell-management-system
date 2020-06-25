<?php
include_once('../../includes/connect.php');
include_once('db_helper.php');


if(!ecell_sec_session_start()) {
	header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
	header("Location: ../../includes/logout.php");
}


//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$offlineConnection = ecell_get_conn();

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
		$sql = "select * from student_original where comm_email = '" . $registered_email . "' order by date_of_admission desc;";

		$stmt = $offlineConnection->prepare($sql);
		$stmt->execute();

		$result1 = $stmt->fetchAll();

		$count = $stmt->rowCount();

		if ($count > 0) {
			$result = $result1[0];
		} else {
			echo "Not registered. Please enter a registered Email.";
		}


		if (isset($result)) {

			$mydetails = $result;

			$enroll = $mydetails['enrollment_no'];
			$program_id = ecell_get_program($mydetails['program_id'], $offlineConnection);
			$campus_id = ecell_get_campus($mydetails['campus_id'], $offlineConnection);
			$type = trim(ecell_get_program_type($mydetails['program_id'], $offlineConnection));

			$jeeOptions = true;
			$ugOptions = true;
			$gateOptions = true;
			$catOptions = true;
			$pgOptions = true;

			$showerr = false;

			if ((strcmp($type, "B.Tech.") == 0) || (strcmp($type, "DD.5PG") == 0)){
				$ugOptions = false;
				$gateOptions = false;
				$catOptions = false;
				$pgOptions = false;
			} else if ((strcmp($type, "M.Tech.") == 0) || (strcmp($type, "MT.Ph.D") == 0)){
				$jeeOptions = false;
				$catOptions = false;
				$pgOptions = false;
			} else if ((strcmp($type, "MBA") == 0) || (strcmp($type, "MB.Ph.D") == 0)){
				$jeeOptions = false;
				$gateOptions = false;
				$pgOptions = false;
			} else if ((strcmp($type, "Ph.D(AS)") == 0) || (strcmp($type, "Ph.D(ECE)") == 0) || (strcmp($type, "Ph.D(IT)") == 0) || (strcmp($type, "Ph.D(MS)") == 0)) {
				$jeeOptions = false;
				$catOptions = false;
			}


			$aadhaar = $mydetails['aadhaar'];
			$time = $mydetails['date_of_admission'];
			$first_name = $mydetails['first_name'];
			$middle_name = $mydetails['middle_name'];
			$last_name = $mydetails['last_name'];
			$hindi_name = $mydetails['hindi_name'];
			$birth_place = $mydetails['birth_place'];
			$category_id = ecell_get_category($mydetails['category_id'], $offlineConnection);
			$sub_category = $mydetails['sub_category'];
			$religion_id = ecell_get_relegion($mydetails['religion_id'], $offlineConnection);
			$gender = $mydetails['gender'];
			$marital_status = ecell_get_marital($mydetails['marital_status']);
			$area = $mydetails['area'];
			$blood_group = $mydetails['blood_group'];
			$nationality = $mydetails['nationality'];
			$communication_addr = $mydetails['comm_addr'];
			$comm_city = $mydetails['comm_city'];
			$comm_state_id = ecell_get_state($mydetails['comm_state_id'], $offlineConnection);
			$comm_pincode = $mydetails['comm_pincode'];
			$comm_phone_no = $mydetails['comm_mobile_no'];
			$email = $mydetails["comm_email"];
			$father_first_name = $mydetails['father_first_name'];
			$father_last_name = $mydetails['father_last_name'];
			$father_profession = $mydetails['father_profession'];
			$father_office_addr = $mydetails['father_office_addr'];
			$city2 = $mydetails['father_city'];
			$state_id2 = ecell_get_state($mydetails['father_state_id'], $offlineConnection);
			$pincode2 = $mydetails['father_pincode'];
			$phone_no2 = $mydetails['father_landline'];
			$email2 = $mydetails['father_email'];
			$mother_first_name = $mydetails['mother_first_name'];
			$mother_last_name = $mydetails['mother_last_name'];
			$mother_profession = $mydetails['mother_profession'];
			$permanent_addr = $mydetails['perm_addr'];
			$city3 = $mydetails['perm_city'];
			$state_id3 = ecell_get_state($mydetails['perm_state_id'], $offlineConnection);
			$pincode3 = $mydetails['perm_pincode'];
			$phone_no3 = $mydetails['perm_landline'];
			$email3 = $mydetails['perm_email'];
			$local_guardian_name = $mydetails['local_guardian_name'];
			$local_guardian_addr = $mydetails['local_guardian_addr'];
			$city4 = $mydetails['local_guardian_city'];
			$phone_no4 = $mydetails['local_guardian_landline'];
			$admission_category_id = ecell_get_category($mydetails['admission_category_id'], $offlineConnection);

			$marsheek_10 = ecell_yesno($mydetails['marksheet_10']);

			if (!($marsheek_10 == "No")) {
				$cert_10 = ecell_yesno($mydetails['cert_10']);
				$percentage_10 = $mydetails['percentage_10'];
				$board_id_10 = ecell_get_board($mydetails['board_id_10'],$offlineConnection);
			} else {
				$cert_10 = '--';
				$percentage_10 = '--';
				$board_id_10 = '--';
			}

			$marksheet_12 = ecell_yesno($mydetails['marksheet_12']);

			if (!($marksheet_12 == "No")) {
				$cert_12 = ecell_yesno($mydetails['cert_12']);
				$percentage_12 = $mydetails['percentage_12'];
				$board_id_12 = ecell_get_board($mydetails['board_id_12'],$offlineConnection);
			} else {
				$cert_12 = '--';
				$percentage_12 = '--';
				$board_id_12 = '--';
			}
			$admit_card = ecell_yesno($mydetails['admit_card']);
			$jee_rank_card = ecell_yesno($mydetails['jee_rank_card']);
			$jee_roll_no = $mydetails['jee_roll_no'];
			$jee_rank_pos = $mydetails['jee_rank_pos'];
			$jee_seat_allot_letter = ecell_yesno($mydetails['jee_seat_allot_letter']);

			$marksheet_grad = ecell_yesno($mydetails['marksheet_grad']);
			if (!($marksheet_grad == "No")) {
				$degree_grad = ecell_yesno($mydetails['degree_grad']);
				$percentage_grad = $mydetails['percentage_grad'];
				$university_grad_id = ecell_get_university($mydetails['university_grad_id'], $offlineConnection);
			}else {
				$degree_grad = '--';
				$percentage_grad = '--';
				$university_grad_id = '--';
			}

			$gate_score_card = ecell_yesno($mydetails['gate_score_card']);
			if (!($gate_score_card == "No")) {
				$gate_year = $mydetails['gate_year'];
				$gate_score = $mydetails['gate_score'];
			}else {
				$gate_year = '--';
				$gate_score = '--';
			}

			$cat_score_card = ecell_yesno($mydetails['cat_score_card']);
			if (!($cat_score_card == "No")) {
				$cat_year = $mydetails['cat_year'];
				$cat_score = $mydetails['cat_score'];
			} else {
				$cat_year = '--';
				$cat_score = '--';
			}

			$marksheet_pg = ecell_yesno($mydetails['marksheet_pg']);
			if (!($marksheet_pg == "No")) {
				$degree_pg = ecell_yesno($mydetails['degree_pg']);
				$percentage_pg = $mydetails['percentage_pg'];
				$university_pg_id = ecell_get_university($mydetails['university_pg_id'], $offlineConnection);
			} else {
				$degree_pg = '--';
				$percentage_pg = '--';
				$university_pg_id = '--';
			}
			$tc = ecell_yesno($mydetails['transfer_cert']);
			$character_cert = ecell_yesno($mydetails['character_cert']);
			$caste_cert = ecell_yesno($mydetails['caste_cert']);
			$ph_cert = ecell_yesno($mydetails['ph_cert']);
			$passport = ecell_yesno($mydetails['passport']);
			$passport_no = $mydetails['passport_no'];
			$validity_period = $mydetails['passport_expiry'];
			$DASA = ecell_get_DASA($mydetails['DASA']);
			$remark = $mydetails['remark'];
			$anti_rag_st = ecell_yesno($mydetails['anti_rag_st']);
			$anti_rag_pr = $mydetails['anti_rag_pr'];
			$med_cert = ecell_yesno($mydetails['med_cert']);
			$muslim_minority = $mydetails['muslim_minority'];
			$other_minority = $mydetails['other_minority'];
			$admission_letter = $mydetails['admission_letter'];
			$dob = $mydetails["dob"];
			$tdob = explode('-', $dob);
			$dob = $tdob[2] . ' ' . ecell_get_month($tdob[1]) . ' ' . $tdob[0];
			$dasa_country = $mydetails['dasa_country'];
			$mcaip = ecell_yesno($mydetails['mcaip']);
			$aadhaar = $mydetails['aadhaar'];
			$hostel = $mydetails['hostel_no'];
			$hostel_room = $mydetails['hostel_room'];
			$migration_cert = ecell_yesno($mydetails['migration_cert']);

			$net = ecell_yesno($mydetails['net']);
			if (!($net == "No")) {
				$net_year = $mydetails['net_year'];
				$net_month = $mydetails['net_month'];
				$net_subject = $mydetails['net_subject'];
			} else {
				$net_year = "--";
				$net_month = "--";
				$net_subject = "--";
			}
			$jrf = ecell_yesno($mydetails['jrf']);
			if (!($jrf == "No")) {
				$jrf_year = $mydetails['jrf_year'];
				$jrf_month = $mydetails['jrf_month'];
				$jrf_subject = $mydetails['jrf_subject'];
			} else {
				$jrf_year = "--";
				$jrf_month = "--";
				$jrf_subject = "--";
			}
			
			$csir = ecell_yesno($mydetails['csir']);
			if (!($csir == "No")) {
				$csir_year = $mydetails['csir_year'];
				$csir_month = $mydetails['csir_month'];
				$csir_subject = $mydetails['csir_subject'];
			} else {
				$csir_year = "--";
				$csir_month = "--";
				$csir_subject = "--";
			}
			

			$mobile_no2 = $mydetails['parent_mobile_no_1'];
			$mobile_no3 = $mydetails['parent_mobile_no_2'];

			$mobile_no4 = $mydetails['local_guardian_mobile_no'];
			$state_id4 = ecell_get_state($mydetails['local_guardian_state_id'], $offlineConnection);
			$pincode4 = $mydetails['local_guardian_pincode'];

			$studentID = $mydetails['student_id'];
			$sql = $offlineConnection->prepare("SELECT media, mime from student_documents where student_id = '$studentID' and category = 'photo' ORDER BY document_id DESC ");
			$sql->execute();

			$result = $sql->fetchAll();
			if($result) {
				$mime = $result[0][1];
				$media = $result[0][0];

				$photopic = $media;
				$photomime = $mime;
			}

			$sql = $offlineConnection->prepare("SELECT media, mime from student_documents where student_id = '$studentID' and category = 'sign' ORDER BY document_id DESC ");
			$sql->execute();

			$result = $sql->fetchAll();
			if($result) {
				$mime = $result[0][1];
				$media = $result[0][0];

				$signpic = $media;
				$signmime = $mime;
			}

			$print_details_personal = array();
			
			//$print_details_personal[] = array('key' => 'Time', 'value' => $time);
			$print_details_personal[] = array('key' => 'Programme', 'value' => $program_id);
			$print_details_personal[] = array('key' => 'Campus', 'value' => $campus_id);
			$print_details_personal[] = array('key' => 'First Name', 'value' => $first_name);
			$print_details_personal[] = array('key' => 'Middle Name', 'value' => $middle_name);
			$print_details_personal[] = array('key' => 'Last Name', 'value' => $last_name);
			$print_details_personal[] = array('key' => 'Full Name (Hindi)', 'value' => $hindi_name);
			$print_details_personal[] = array('key' => 'Date of Birth', 'value' => $dob);
			$print_details_personal[] = array('key' => 'Place of Birth', 'value' => $birth_place);
			$print_details_personal[] = array('key' => 'Category', 'value' => $category_id);
			$print_details_personal[] = array('key' => 'Sub Category ', 'value' => $sub_category);
			$print_details_personal[] = array('key' => 'Admission Category', 'value' => $admission_category_id);
			$print_details_personal[] = array('key' => 'Religion', 'value' => $religion_id);
			$print_details_personal[] = array('key' => 'Gender ', 'value' => $gender);
			$print_details_personal[] = array('key' => 'Marital Status', 'value' => $marital_status);
			$print_details_personal[] = array('key' => 'Blood Group', 'value' => $blood_group);
			$print_details_personal[] = array('key' => 'Area', 'value' => $area);
			$print_details_personal[] = array('key' => 'Nationality', 'value' => $nationality);
			$print_details_personal[] = array('key' => 'Address (communication)', 'value' => $communication_addr);
			$print_details_personal[] = array('key' => 'City', 'value' => $comm_city);
			$print_details_personal[] = array('key' => 'State', 'value' => $comm_state_id);
			$print_details_personal[] = array('key' => 'PIN Code ', 'value' => $comm_pincode);
			$print_details_personal[] = array('key' => 'Mobile No. (communication)', 'value' => $comm_phone_no);
			$print_details_personal[] = array('key' => 'Aadhaar No.', 'value' => $aadhaar);
			$print_details_personal[] = array('key' => 'Communication Email', 'value' => $email);

			$print_details_parents = array();

			$print_details_parents[] = array('key' => 'Father\'s First Name', 'value' => $father_first_name);
			$print_details_parents[] = array('key' => 'Father\'s Last Name', 'value' => $father_last_name);
			$print_details_parents[] = array('key' => 'Father\'s Profession', 'value' => $father_profession);
			
			$print_details_parents[] = array('key' => 'Mother\'s First Name ', 'value' => $mother_first_name);
			$print_details_parents[] = array('key' => 'Mother\'s Last Name', 'value' => $mother_last_name);
			$print_details_parents[] = array('key' => 'Mother\'s Profession', 'value' => $mother_profession);
			$print_details_parents[] = array('key' => 'Permanent Address ', 'value' => $permanent_addr);
			$print_details_parents[] = array('key' => 'City', 'value' => $city3);
			$print_details_parents[] = array('key' => 'State', 'value' => $state_id3);
			$print_details_parents[] = array('key' => 'PIN Code ', 'value' => $pincode3);
			$print_details_parents[] = array('key' => 'Landline No. ', 'value' => $phone_no3);
			$print_details_parents[] = array('key' => 'Parent Mobile 1 ', 'value' => $mobile_no2);
			$print_details_parents[] = array('key' => 'Parent Mobile 2 ', 'value' => $mobile_no3);
			$print_details_parents[] = array('key' => 'Email', 'value' => $email3);
			$print_details_parents[] = array('key' => 'Father\'s Office Address', 'value' => $father_office_addr);
			$print_details_parents[] = array('key' => 'City', 'value' => $city2);
			$print_details_parents[] = array('key' => 'State', 'value' => $state_id2);
			$print_details_parents[] = array('key' => 'PIN Code ', 'value' => $pincode2);
			$print_details_parents[] = array('key' => 'Landline (Father)', 'value' => $phone_no2);
			$print_details_parents[] = array('key' => 'Father\'s Email', 'value' => $email2);
			$print_details_parents[] = array('key' => 'Guardian Name ', 'value' => $local_guardian_name);
			$print_details_parents[] = array('key' => 'Guardian Address', 'value' => $local_guardian_addr);
			$print_details_parents[] = array('key' => 'Guardian City', 'value' => $city4);
			$print_details_parents[] = array('key' => 'Guardian State ', 'value' => $state_id4);
			$print_details_parents[] = array('key' => 'Guardian PIN Code', 'value' => $pincode4);
			$print_details_parents[] = array('key' => 'Landline', 'value' => $phone_no4);
			$print_details_parents[] = array('key' => 'Mobile No.', 'value' => $mobile_no4);

			$print_details_qualification = array();

			$print_details_qualification[] = array('key' => '10th Marksheet', 'value' => $marsheek_10);
			$print_details_qualification[] = array('key' => '10th Certificate', 'value' => $cert_10);
			$print_details_qualification[] = array('key' => '10th Percentage', 'value' => $percentage_10);
			$print_details_qualification[] = array('key' => '10th Board', 'value' => $board_id_10);
			$print_details_qualification[] = array('key' => '12th Marksheet', 'value' => $marksheet_12);
			$print_details_qualification[] = array('key' => '12th Certificate', 'value' => $cert_12);
			$print_details_qualification[] = array('key' => '12th Percentage', 'value' => $percentage_12);
			$print_details_qualification[] = array('key' => '12th Board', 'value' => $board_id_12);
			$print_details_qualification[] = array('key' => 'Caste Certificate', 'value' => $caste_cert);
			$print_details_qualification[] = array('key' => 'PH Certificate', 'value' => $ph_cert);
			$print_details_qualification[] = array('key' => 'Transfer Certificate', 'value' => $tc);
			$print_details_qualification[] = array('key' => 'Character Certificate', 'value' => $character_cert);
			$print_details_qualification[] = array('key' => 'Migration Certificate', 'value' => $migration_cert);
			$print_details_qualification[] = array('key' => 'MCAIP', 'value' => $mcaip);

			if ($jeeOptions) {
				$print_details_qualification[] = array('key' => 'Provisional Seat Allotment Letter', 'value' => $jee_seat_allot_letter);
				$print_details_qualification[] = array('key' => 'JEE Admit Card ', 'value' => $admit_card);
				$print_details_qualification[] = array('key' => 'JEE Rank Card', 'value' => $jee_rank_card);
				$print_details_qualification[] = array('key' => 'JEE Roll No.', 'value' => $jee_roll_no);
				$print_details_qualification[] = array('key' => 'JEE Rank', 'value' => $jee_rank_pos);
			}

			if ($ugOptions) {
				$print_details_qualification[] = array('key' => 'Graduation Marksheet', 'value' => $marksheet_grad);
				$print_details_qualification[] = array('key' => 'Graduation Certificate', 'value' => $degree_grad);
				$print_details_qualification[] = array('key' => 'Graduation Percentage', 'value' => $percentage_grad);
				$print_details_qualification[] = array('key' => 'Graduation University', 'value' => $university_grad_id);
			}

			if ($pgOptions) {
				$print_details_qualification[] = array('key' => 'PG Marksheet', 'value' => $marksheet_pg);
				$print_details_qualification[] = array('key' => 'PG Certificate', 'value' => $degree_pg);
				$print_details_qualification[] = array('key' => 'PG Percentage', 'value' => $percentage_pg);
				$print_details_qualification[] = array('key' => 'PG University', 'value' => $university_pg_id);

				$print_details_qualification[] = array('key' => 'NET', 'value' => $net);
				$print_details_qualification[] = array('key' => 'NET Year', 'value' => $net_year);
				$print_details_qualification[] = array('key' => 'NET Month', 'value' => $net_month);
				$print_details_qualification[] = array('key' => 'NET Subject', 'value' => $net_subject);

				$print_details_qualification[] = array('key' => 'JRF', 'value' => $jrf);

				$print_details_qualification[] = array('key' => 'JRF Year', 'value' => $jrf_year);
				$print_details_qualification[] = array('key' => 'JRF Month', 'value' => $jrf_month);
				$print_details_qualification[] = array('key' => 'JRF Subject', 'value' => $jrf_subject);

				$print_details_qualification[] = array('key' => 'CSIR', 'value' => $csir);
				$print_details_qualification[] = array('key' => 'CSIR Year', 'value' => $csir_year);
				$print_details_qualification[] = array('key' => 'CSIR Month', 'value' => $csir_month);
				$print_details_qualification[] = array('key' => 'CSIR Subject', 'value' => $csir_subject);
				
			}

			if ($gateOptions) {
				$print_details_qualification[] = array('key' => 'GATE Score Card', 'value' => $gate_score_card);
				$print_details_qualification[] = array('key' => 'GATE Year', 'value' => $gate_year);
				$print_details_qualification[] = array('key' => 'GATE Score', 'value' => $gate_score);
			}

			if ($catOptions) {
				$print_details_qualification[] = array('key' => 'CAT Score Card', 'value' => $cat_score_card);
				$print_details_qualification[] = array('key' => 'CAT Year', 'value' => $cat_year);
				$print_details_qualification[] = array('key' => 'CAT Score', 'value' => $cat_score);
			}

			$print_details_qualification[] = array('key' => 'Passport', 'value' => $passport);
			if (!($passport == "No")) {
				$print_details_qualification[] = array('key' => 'Passport Number', 'value' => $passport_no);
				$print_details_qualification[] = array('key' => 'Passport Expiry', 'value' => $validity_period);
			}

			
			$print_details_qualification[] = array('key' => 'DASA', 'value' => $DASA);
			if (!($DASA == "No")) {
				$print_details_qualification[] = array('key' => 'Country', 'value' => $dasa_country);
			}
			$print_details_qualification[] = array('key' => 'Anti-Ragging Certificate', 'value' => $anti_rag_st);
			$print_details_qualification[] = array('key' => 'Medical Certificate', 'value' => $med_cert);
			$print_details_qualification[] =  array('key' => 'Remarks (if any)', 'value' => $remark);



			$mycollege = "Indian Institute of Information Technology, Allahabad";
			$address = "Deoghat - Jhalwa, Allahabad - 2011012";
			$registrationform = "Registration Form";
			$nameofprogram = "NAME OF PROGRAMME ";
			$enrollmentno = "Enrollment No. (for office use only) ";
			$detailsenroll = "Details of the Student to be Enrolled";
			$declaration = "I do hereby declare that the above information given by me is true to the best of my knowledge and belief.";
			$declaration2 = "If at later date at any stage, it is found to be false, my candidature for admission shall stand cancelled.";
			$place = "Place:......................................";
			$date =  "Date:.......................................";
			$signCandidate = "Signature of Candidate......................................................";
			$signFMG =       "Signature of Father/Mother/Guardian..............................";

			$html = "<html>
				<body>
				<style>
				body, p, div { font-size: 14pt; font-family: freeserif;}
			h3 { font-size: 15pt; margin-bottom:0; font-family: monospace; }
			h4 {line-height: 8px; font-size: 14px;}
			h2 { line-height: 16px;}
			</style>
				<h2 style='text-align: center;' >$mycollege</h2>
				<h4 style='text-align: center;' >$address</h4>
				<h4 style='text-align: center;' >$registrationform</h4><br>
				<div style='position: relative;'></div>
				<table style='font-size: 16px;'>
				<tr style='height: 40px;'><td>PROGRAMME NAME</td><td style='width:410px; height: 35px; border: 2px solid black; position: relative;'>$program_id</td></tr>
				<tr style='height: 40px;'><td style='font-size: 14px;'>Enrollment No.<br>(For office use only)</td><td style='width:260px; height: 35px; border: 2px solid black;'>$enroll</td></tr>
				</table>
				<br>
				<div style='position:absolute; font-size: 14px; right: 15px; top:105px; border: 2px solid black; height: 160px; width: 130px;'>
				<img src='data: $photomime;base64,$photopic'  width='130' height='160' alt='Please paste pasport size photo'>
				</div>
				<p style='position: absolute; font-size: 14px; top:265px; right: 160px;;'>Signature</p>
				<div style='height: 30px; border: 2px solid black; position: absolute; width: 130px; top:275px; right: 15px;;'>
				<img src='data: $signmime;base64,$signpic'  width='130' height='30' alt=' '>
				</div>
				<p style='text-align: center; font-size: 16px; position: relative;margin-top: 100px; margin-bottom: 40px'>
					<u><b>STUDENT DETAILS</b></u>
				</p> 
				<table style='font-size: 14px; margin-bottom: 15px'>                            
				";

			$i = 1;
			foreach ($print_details_personal as $f) {
				$html .= "<tr><td>$i .    </td><td style='width: 220px;'>" . $f['key'] . "</td><td style='width: 62%;border-bottom: 1px dotted black;'>" . $f['value'] . "</td></tr>";
				$i++;
			}
			
			$html .= "<tr><td  height='80px'></td><td></td></tr>";
			$parent_title = "<u><b>PARENTS DETAILS</b><u>";
			$html .= "<tr><td  height='30px' ></td><td>$parent_title</td></tr>";
			foreach ($print_details_parents as $f) {
				$html .= "<tr><td>$i .   </td><td style='width: 220px;'>" . $f['key'] . "</td><td style='width: 62%;border-bottom: 1px dotted black;'>" . $f['value'] . "</td></tr>";
				$i++;
			}
			$html .= "<tr><td  height='340px'></td><td></td></tr>";
			$parent_title = "<u><b>QUALIFICATION DETAILS</b></u>";
			$html .= "<tr><td  height='30px'></td><td>$parent_title</td></tr>";
			foreach ($print_details_qualification as $f) {
				$html .= "<tr><td>$i .   </td><td style='width: 220px;'>" . $f['key'] . "</td><td style='width: 62%;border-bottom: 1px dotted black;'>" . $f['value'] . "</td></tr>";
				$i++;
			}
			$timestamp = "Registration Time:   ";
			$sql = "SELECT * FROM staff WHERE username=:username";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':username', $_SESSION['username']);
			$stmt->execute();
			$staff_name = $stmt->fetchAll()[0]['staff_name'];
			$staff_username = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name of staff:   " .$staff_name;
			$staff_sign = "(Signature)  ";
			$html .= "</table>
			<div>
				<div>
				 	<p style='font: 15px italic; position: relative;'>$declaration $declaration2</p>
					<table style='font-size: 14px'>
						<tr><td width='50%' height='40px'>$place</td><td>$signCandidate</td></tr>
					   	<tr><td  height='40px' >$date</td><td>$signFMG</td></tr>
					</table>
				</div>
				<div>
					<p style='text-align: center; font-size: 15px; position: relative;margin-top: 10px;'><u><b>EXAMCELL IIIT ALLAHABAD</b></u></p> 
					<p style='text-align: center; font-size: 14px; position: relative;margin-top: 10px;'>$timestamp $time $staff_username</p>
					<p style='text-align: center; font-size: 14px; position: relative;margin-top: 25px;'><u> $staff_sign</u></p>
				</div>
			</div>
				";

			//==============================================================
			//==============================================================
			//==============================================================


			$mpdf = new mPDF('');
			$mpdf->setFooter('{PAGENO}');
			$mpdf->WriteHTML($html);

			$mpdf->Output();
			exit;
			//==============================================================
			//==============================================================
		}
	}
}
?>
