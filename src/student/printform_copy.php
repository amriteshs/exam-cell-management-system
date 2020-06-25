<?php
include_once('../../includes/connect.php');
include_once('db_helper.php');

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
		$sql = "select * from student where comm_email = '" . $registered_email . "' order by date_of_admission desc;";

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
			$communication_addr = $mydetails['communication_addr'];
			$comm_city = $mydetails['comm_city'];
			$comm_state_id = ecell_get_state($mydetails['comm_state_id'], $offlineConnection);
			$comm_pincode = $mydetails['comm_pincode'];

			$comm_phone_no = $mydetails['comm_phone_no'];
			$email = $mydetails["comm_email"];

			$father_first_name = $mydetails['father_first_name'];
			$father_last_name = $mydetails['father_last_name'];
			$father_profession = $mydetails['father_profession'];
			$father_office_addr = $mydetails['father_office_addr'];
			$city2 = $mydetails['father_city'];
			$state_id2 = ecell_get_state($mydetails['father_state_id'], $offlineConnection);
			$pincode2 = $mydetails['father_pincode'];
			$phone_no2 = $mydetails['father_phone_no'];
			$email2 = $mydetails['father_email'];
			$mother_first_name = $mydetails['mother_first_name'];
			$mother_last_name = $mydetails['mother_last_name'];
			$mother_profession = $mydetails['mother_profession'];
			$permanent_addr = $mydetails['permanent_addr'];
			$city3 = $mydetails['perm_city'];
			$state_id3 = ecell_get_state($mydetails['perm_state_id'], $offlineConnection);
			$pincode3 = $mydetails['perm_pincode'];
			$phone_no3 = $mydetails['perm_phone_no'];
			$email3 = $mydetails['perm_email'];
			$local_guardian_name = $mydetails['local_guardian_name'];
			$loca_guardian_addr = $mydetails['local_guardian_addr'];
			$city4 = $mydetails['local_guard_city'];
			$phone_no4 = $mydetails['local_guard_phone_no'];

			$admission_category_id = ecell_get_category($mydetails['admission_category_id'], $offlineConnection);
			$marsheek_10 = ecell_yesno($mydetails['marksheet_10']);
			$cert_10 = ecell_yesno($mydetails['cert_10']);
			$percentage_10 = $mydetails['percentage_10'];
			$board_id_10 = $mydetails['board_id_10'];
			$marksheet_12 = ecell_yesno($mydetails['marksheet_12']);
			$cert_12 = ecell_yesno($mydetails['cert_12']);
			$percentage_12 = ecell_yesno($mydetails['percentage_12']);
			$board_id_12 = $mydetails['board_id_12'];
			$admit_card = ecell_yesno($mydetails['admit_card']);
			$jee_rank_card = ecell_yesno($mydetails['jee_rank_card']);
			$jee_roll_no = $mydetails['jee_roll_no'];
			$jee_rank_pos = $mydetails['jee_rank_pos'];
			$jee_seat_allot_letter = ecell_yesno($mydetails['jee_seat_allot_letter']);
			$marksheet_grad = ecell_yesno($mydetails['marksheet_grad']);
			$degree_grad = ecell_yesno($mydetails['degree_grad']);
			$percentage_grad = $mydetails['percentage_grad'];
			$university_grad_id = $mydetails['university_grad_id'];
			$gate_score_card = ecell_yesno($mydetails['gate_score_card']);
			$gate_year = $mydetails['gate_year'];
			$gate_score = $mydetails['gate_score'];
			$cat_score_card = ecell_yesno($mydetails['cat_score_card']);
			$cat_year = $mydetails['cat_year'];
			$cat_score = $mydetails['cat_score'];
			$marksheet_pg = ecell_yesno($mydetails['marksheet_pg']);
			$degree_pg = $mydetails['degree_pg'];
			$percentage_pg = $mydetails['percentage_pg'];
			$university_pg_id = $mydetails['university_pg_id'];
			$tc = ecell_yesno($mydetails['tc']);
			$character_cert = ecell_yesno($mydetails['character_cert']);
			$caste_cert = ecell_yesno($mydetails['caste_cert']);
			$ph_cert = ecell_yesno($mydetails['ph_cert']);
			$passport = ecell_yesno($mydetails['passport']);
			$passport_no = $mydetails['passport_no'];
			$validity_period = $mydetails['validity_period'];
			$DASA = ecell_get_DASA($mydetails['DASA']);
			$remark = $mydetails['remark'];
			$anti_rag_st = ecell_yesno($mydetails['anti_rag_st']);
			$anti_rag_pr = $mydetails['anti_rag_pr'];
			$med_cert = ecell_yesno($mydetails['med_cert']);
			$muslim_minority = $mydetails['muslim_minority'];
			$other_minority = $mydetails['other_minority'];
			$admission_letter = $mydetails['admission_letter'];
			$dob = $mydetails["dob"];

			$dasa_country = $mydetails['dasa_country'];
			$mcaip = ecell_yesno($mydetails['mcaip']);
			$admision_withdrawal = $mydetails['admission_withdrawal'];
			$aadhaar = $mydetails['aadhaar'];
			$hostel = $mydetails['hostel_no'];
			$hostel_room = $mydetails['hostel_room'];


			$migration_cert = ecell_yesno($mydetails['migration_cert']);

			$printdetails = array();
			$printdetails[] = array('key' => 'Time', 'value' => $time);
			$printdetails[] = array('key' => 'Program', 'value' => $program_id);
			$printdetails[] = array('key' => 'First Name', 'value' => ucwords($first_name));
			$printdetails[] = array('key' => 'Middle Name', 'value' => ucwords($middle_name));
			$printdetails[] = array('key' => 'Last Name', 'value' => ucwords($last_name));
			$printdetails[] = array('key' => 'Full Name (Hindi)', 'value' => $hindi_name);
			$printdetails[] = array('key' => 'Date of Birth', 'value' => $dob);
			$printdetails[] = array('key' => 'Place of Birth', 'value' => $birth_place);
			$printdetails[] = array('key' => 'Category', 'value' => $category_id);
			$printdetails[] = array('key' => 'Sub Category ', 'value' => ucwords($sub_category));
			$printdetails[] = array('key' => 'Admission Category', 'value' => $admission_category_id);
			$printdetails[] = array('key' => 'Religion', 'value' => $religion_id);
			$printdetails[] = array('key' => 'Gender ', 'value' => $gender);
			$printdetails[] = array('key' => 'Marital Status', 'value' => $marital_status);
			$printdetails[] = array('key' => 'Blood Group', 'value' => $blood_group);
			$printdetails[] = array('key' => 'Area', 'value' => $area);
			$printdetails[] = array('key' => 'Nationality', 'value' => ucwords($nationality));
			$printdetails[] = array('key' => 'Address (communication)', 'value' => $communication_addr);
			$printdetails[] = array('key' => 'City', 'value' => $comm_city);
			$printdetails[] = array('key' => 'State', 'value' => $comm_state_id);
			$printdetails[] = array('key' => 'Pin Code ', 'value' => $comm_pincode);
			$printdetails[] = array('key' => 'Phone No. (communication)', 'value' => $comm_phone_no);
			$printdetails[] = array('key' => 'AADHAAR No.', 'value' => $aadhaar);
			$printdetails[] = array('key' => 'Communication Email', 'value' => $email);
			$printdetails[] = array('key' => 'Father\'s First Name', 'value' => ucwords($father_first_name));
			$printdetails[] = array('key' => 'Father\'s Last Name', 'value' => ucwords($father_last_name));
			$printdetails[] = array('key' => 'Father\'s Profession', 'value' => $father_profession);
			$printdetails[] = array('key' => 'Father\'s Office Address', 'value' => $father_office_addr);
			$printdetails[] = array('key' => 'City', 'value' => $city2);
			$printdetails[] = array('key' => 'State', 'value' => $state_id2);
			$printdetails[] = array('key' => 'Pin Code ', 'value' => $pincode2);
			$printdetails[] = array('key' => 'Phone No. (Father)', 'value' => $phone_no2);
			$printdetails[] = array('key' => 'Father\'s Email', 'value' => $email2);
			$printdetails[] = array('key' => 'Mother\'s First Name ', 'value' => ucwords($mother_first_name));
			$printdetails[] = array('key' => 'Mother\'s Last Name', 'value' => ucwords($mother_last_name));
			$printdetails[] = array('key' => 'Mother\'s Profession', 'value' => $mother_profession);
			$printdetails[] = array('key' => 'Permanent Address ', 'value' => $permanent_addr);
			$printdetails[] = array('key' => 'City', 'value' => $city3);
			$printdetails[] = array('key' => 'State', 'value' => $state_id3);
			$printdetails[] = array('key' => 'Pin Code ', 'value' => $pincode3);
			$printdetails[] = array('key' => 'Phone No. ', 'value' => $phone_no3);
			$printdetails[] = array('key' => 'Email', 'value' => $email3);
			$printdetails[] = array('key' => 'Guardian Name ', 'value' => ucwords($local_guardian_name));
			$printdetails[] = array('key' => 'Guardian Address', 'value' => $loca_guardian_addr);
			$printdetails[] = array('key' => 'City', 'value' => $city4);
			$printdetails[] = array('key' => 'Phone No.', 'value' => $phone_no4);

			$printdetails[] = array('key' => '10th Marksheet', 'value' => $marsheek_10);
			$printdetails[] = array('key' => '10th Certificate', 'value' => $cert_10);
			$printdetails[] = array('key' => '10th Percentage', 'value' => $percentage_10);
			$printdetails[] = array('key' => '10th Board', 'value' => $board_id_10);
			$printdetails[] = array('key' => '12th Marksheet', 'value' => $marksheet_12);
			$printdetails[] = array('key' => '12th Certificate', 'value' => $cert_12);
			$printdetails[] = array('key' => '12th Percentage', 'value' => $percentage_12);
			$printdetails[] = array('key' => '12th Board', 'value' => $board_id_12);

			$printdetails[] = array('key' => 'Caste Certificate', 'value' => $caste_cert);
			$printdetails[] = array('key' => 'PH Certificate', 'value' => $ph_cert);
			$printdetails[] = array('key' => 'Transfer Certificate', 'value' => $tc);
			$printdetails[] = array('key' => 'Character Certificate', 'value' => $character_cert);
			$printdetails[] = array('key' => 'Migration Certificate', 'value' => $migration_cert);

			if ($jeeOptions) {
				$printdetails[] = array('key' => 'Provisional Seat Allotment Letter', 'value' => $jee_seat_allot_letter);
				$printdetails[] = array('key' => 'JEE Admit Card ', 'value' => $admit_card);
				$printdetails[] = array('key' => 'JEE Rank Card', 'value' => $jee_rank_card);
				$printdetails[] = array('key' => 'JEE Roll No.', 'value' => $jee_roll_no);
				$printdetails[] = array('key' => 'JEE Rank', 'value' => $jee_rank_pos);
			}

			if ($ugOptions) {
				$printdetails[] = array('key' => 'Graduation Marksheet', 'value' => $marksheet_grad);
				$printdetails[] = array('key' => 'Graduation Certificate', 'value' => $degree_grad);
				$printdetails[] = array('key' => 'Graduation Percentage', 'value' => $percentage_grad);
				$printdetails[] = array('key' => 'Graduation University', 'value' => $university_grad_id);
			}

			if ($pgOptions) {
				$printdetails[] = array('key' => 'PG Marksheet', 'value' => $marksheet_pg);
				$printdetails[] = array('key' => 'PG Certificate', 'value' => $degree_pg);
				$printdetails[] = array('key' => 'PG Percentage', 'value' => $percentage_pg);
				$printdetails[] = array('key' => 'PG University', 'value' => $university_pg_id);
			}

			if ($gateOptions) {
				$printdetails[] = array('key' => 'GATE Score Card', 'value' => $gate_score_card);
				$printdetails[] = array('key' => 'GATE Year', 'value' => $gate_year);
				$printdetails[] = array('key' => 'GATE Score', 'value' => $gate_score);
			}

			if ($catOptions) {
				$printdetails[] = array('key' => 'CAT Score Card', 'value' => $cat_score_card);
				$printdetails[] = array('key' => 'CAT Year', 'value' => $cat_year);
				$printdetails[] = array('key' => 'CAT Score', 'value' => $cat_score);
			}

			$printdetails[] = array('key' => 'Passport', 'value' => $passport);
			if (!($passport == "NO")) {
				$printdetails[] = array('key' => 'Passport Number', 'value' => $passport_no);
				$printdetails[] = array('key' => 'Validity Period', 'value' => $validity_period);
			}

			$printdetails[] =  array('key' => 'MCAIP', 'value' => $mcaip);
			$printdetails[] = array('key' => 'DASA', 'value' => $DASA);
			if (!($DASA == "NO")) {
				$printdetails[] = array('key' => 'Country', 'value' => $dasa_country);
			}
			$printdetails[] = array('key' => 'Anti-Ragging Certificate', 'value' => $anti_rag_st);
			//$printdetails[] =  array('key' => 'Anti Ragging remark', 'value' => $anti_rag_pr);
			$printdetails[] = array('key' => 'Medical Certificate', 'value' => $med_cert);
			//$printdetails[] = array('key' => 'Muslim Minority', 'value' => $muslim_minority);
			//$printdetails[] = array('key' => 'Other Minority', 'value' => $other_minority);
			//$printdetails[] =  array('key' => 'Remark', 'value' => $remark);

			$mycollege = "Indian Institue of Information Technology, Allahabad";
			$address = "Deoghat - Jhalwa, Allahabad - 2011012";
			$registrationform = "Registration Form";
			$academicsession = "Academic Session: 2016-2020";
			$nameofprogram = "NAME OF PROGRAM ";
			$enrollmentno = "Enrolment No. (for office use only) ";
			$detailsenroll = "Details of the Student to be Enrolled";
			$declaration = "I do herby declare that the above information given by me is true to the best of my knowledge and belief.";
			$declaration2 = "If at later date at any stage, it is found to be false, my candidature for admission shall stand cancelled.";
			$place = "Place:..........................";
			$date = "Date:...........................";
			$signCandidate = "Signature of candidate.............................................";
			$signFMG = "Signature of Father/Mother/Guardian.....................";

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
				<tr style='height: 40px;'><td>NAME OF PROGRAM</td><td style='width:410px; height: 35px; border: 2px solid black; position: relative;'></td></tr>
				<tr style='height: 40px;'><td style='font-size: 14px;'>Enrollment No.<br>(For office use only)</td><td style='width:260px; height: 35px; border: 2px solid black;'></td></tr>
				</table>
				<br>
				<div style='position:absolute; font-size: 14px; right: 15px; top:105px; border: 2px solid black; height: 160px; width: 130px;'>Paste your recent passport size photograph<br>(Do not clip or staple)</div></div>
				<p style='position: absolute; font-size: 14px; top:265px; right: 160px;;'>Signature</p>
				<div style='height: 30px; border: 2px solid black; position: absolute; width: 130px; top:275px; right: 15px;;'></div>
				<p style='text-align: center; font-size: 15px; position: relative;margin-top: 24px;'>Details of Student to be Enrolled</p> 
				<table style='font-size: 13px; margin-bottom: 40px'>                            
				";

			$i = 1;
			foreach ($printdetails as $f) {
				$html .= "<tr><td>$i .   </td><td style='width: 220px;'>" . $f['key'] . "</td><td style='width: 62%;border-bottom: 1px solid black;'>" . $f['value'] . "</td></tr>";
				$i++;
			}

			$html .= "</table>
				<p style='font: 13px italic; position: relative;'>$declaration $declaration2</p>
				<table style='font-size: 15px'><tr><td width='50%'>$place</td><td>$signCandidate</td></tr><tr><td>$date</td><td>$signFMG</td></tr></table>";

			//==============================================================
			//==============================================================
			//==============================================================
			include("../../includes/mpdf60/mpdf.php");

			$mpdf = new mPDF('');

			$mpdf->WriteHTML($html);

			$mpdf->Output();
			exit;
			//==============================================================
			//==============================================================
		}
	}
}
?>
