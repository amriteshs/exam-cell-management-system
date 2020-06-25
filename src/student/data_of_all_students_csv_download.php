<?php

include_once('../../includes/include.php');
include_once('../../includes/session.php');

if(!ecell_sec_session_start()) {
    header("Location: ../../includes/logout.php");
}

if(!ecell_login_check($conn)) {
    header("Location: ../../includes/logout.php");
}

$sql = "SELECT * FROM state";
$stmt = $conn->prepare($sql);
$stmt->execute();
$state_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
$state = array();
foreach ($state_arr as $some_state) {
    $state[$some_state['state_id']] = $some_state['state_name'];
}

$sql = "SELECT * FROM student_category";
$stmt = $conn->prepare($sql);
$stmt->execute();
$category_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
$category = array();
foreach ($category_arr as $some_category) {
    $category[$some_category['category_id']] = $some_category['category_name'];
}

$sql = "SELECT * FROM board";
$stmt = $conn->prepare($sql);
$stmt->execute();
$board_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
$board = array();
foreach ($board_arr as $some_board) {
    $board[$some_board['board_id']] = $some_board['board_name'];
}

$sql = "SELECT * FROM universities";
$stmt = $conn->prepare($sql);
$stmt->execute();
$univ_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
$univ = array();
foreach ($univ_arr as $some_univ) {
    $univ[$some_univ['university_id']] = $some_univ['university_name'];
}

$sql = "SELECT program.program_name as program_name, enrollment_no, first_name, middle_name, last_name, comm_mobile_no, comm_email from student, program where program.program_id = student.program_id order by enrollment_no;";
$sql = "SELECT student_id, program.program_name, campus.campus_name, date_of_admission, first_name, middle_name, last_name, hindi_name, enrollment_no, dob, birth_place, category_id, sub_category, religion.religion_name, gender, marital_status, area, blood_group, nationality, comm_addr, comm_city, comm_state_id, comm_pincode, comm_mobile_no, comm_email, father_first_name, father_last_name, father_profession, father_office_addr, father_city, father_state_id, father_pincode, father_landline, father_email, mother_first_name, mother_last_name, mother_profession, perm_addr, parent_mobile_no_1, parent_mobile_no_2, perm_city, perm_state_id, perm_pincode, perm_landline, perm_email, local_guardian_name, local_guardian_addr, local_guardian_city, local_guardian_state_id, local_guardian_pincode, local_guardian_landline, local_guardian_mobile_no, admission_category_id, admit_card, jee_rank_card, jee_roll_no, jee_rank_pos, jee_seat_allot_letter, marksheet_10, cert_10, percentage_10, board_id_10, board_10_passing_state_id, marksheet_12, cert_12, percentage_12, board_id_12, board_12_passing_state_id, marksheet_grad, degree_grad, percentage_grad, university_grad_id, marksheet_pg, degree_pg, percentage_pg, university_pg_id, gate_score_card, gate_year, gate_score, cat_score_card, cat_year, cat_score, csir, csir_month, csir_year, csir_subject, net, net_month, net_year, net_subject, jrf, jrf_month, jrf_year, jrf_subject, transfer_cert, migration_cert, character_cert, caste_cert, ph_cert, passport, passport_no, passport_expiry, mcaip, DASA, dasa_country, remark, anti_rag_st, anti_rag_pr, med_cert, muslim_minority, other_minority, admission_letter, year, sem_code, section, aadhaar, hostel_no, hostel_room, id_card_validity, termination_date, graduation_date FROM student, program, campus, religion WHERE student.program_id=program.program_id AND student.campus_id=campus.campus_id AND student.religion_id=religion.religion_id ORDER BY enrollment_no;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$student_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

$student_mod = array();

foreach ($student_array as $student) {
    $student['category_id'] = $category[$student['category_id']];
    $student['admission_category_id'] = $category[$student['admission_category_id']];

    $student['comm_state_id'] = $state[$student['comm_state_id']];
    $student['father_state_id'] = $state[$student['father_state_id']];
    $student['perm_state_id'] = $state[$student['perm_state_id']];
    $student['local_guardian_state_id'] = $state[$student['local_guardian_state_id']];
    $student['board_10_passing_state_id'] = $state[$student['board_10_passing_state_id']];
    $student['board_12_passing_state_id'] = $state[$student['board_12_passing_state_id']];

    $student['board_id_10'] = $board[$student['board_id_10']];
    $student['board_id_12'] = $board[$student['board_id_12']];

    $student['university_grad_id'] = $univ[$student['university_grad_id']];
    $student['university_pg_id'] = $univ[$student['university_pg_id']];

    $student['admit_card'] = $student['admit_card'] ? 'Yes' : 'No';
    $student['jee_rank_card'] = $student['jee_rank_card'] ? 'Yes' : 'No';
    $student['jee_seat_allot_letter'] = $student['jee_seat_allot_letter'] ? 'Yes' : 'No';
    $student['marksheet_10'] = $student['marksheet_10'] ? 'Yes' : 'No';
    $student['cert_10'] = $student['cert_10'] ? 'Yes' : 'No';
    $student['marksheet_12'] = $student['marksheet_12'] ? 'Yes' : 'No';
    $student['cert_12'] = $student['cert_12'] ? 'Yes' : 'No';
    $student['marksheet_grad'] = $student['marksheet_grad'] ? 'Yes' : 'No';
    $student['degree_grad'] = $student['degree_grad'] ? 'Yes' : 'No';
    $student['marksheet_pg'] = $student['marksheet_pg'] ? 'Yes' : 'No';
    $student['degree_pg'] = $student['degree_pg'] ? 'Yes' : 'No';
    $student['gate_score_card'] = $student['gate_score_card'] ? 'Yes' : 'No';
    $student['csir'] = $student['csir'] ? 'Yes' : 'No';
    $student['net'] = $student['net'] ? 'Yes' : 'No';
    $student['jrf'] = $student['jrf'] ? 'Yes' : 'No';
    $student['transfer_cert'] = $student['transfer_cert'] ? 'Yes' : 'No';
    $student['migration_cert'] = $student['migration_cert'] ? 'Yes' : 'No';
    $student['character_cert'] = $student['character_cert'] ? 'Yes' : 'No';
    $student['caste_cert'] = $student['caste_cert'] ? 'Yes' : 'No';
    $student['ph_cert'] = $student['ph_cert'] ? 'Yes' : 'No';
    $student['mcaip'] = $student['mcaip'] ? 'Yes' : 'No';
    $student['DASA'] = $student['DASA'] ? 'Yes' : 'No';
    $student['anti_rag_st'] = $student['anti_rag_st'] ? 'Yes' : 'No';
    $student['med_cert'] = $student['med_cert'] ? 'Yes' : 'No';
    $student['passport'] = $student['passport'] ? 'Yes' : 'No';
    $student['marital_status'] = $student['marital_status'] ? 'Yes' : 'No';

    $dob = explode('-', $student['dob']);

    $student['dob'] = $dob[2] . '/' . $dob[1] . '/' . $dob[0];

    $student_mod[] = $student;
}

$output = fopen("php://output",'w') or die("Can't open php://output");
header("Content-Type:application/csv");
header("Content-Disposition:attachment;filename=students.csv");

fputcsv($output, array('Student ID', 'Program Name', 'Campus', 'Date of Admission', 'First Name', 'Middle Name', 'Last Name', 'Hindi Name', 'Enrollment No', 'DOB', 'Birth Place', 'Category', 'Sub Category', 'Religion', 'Gender', 'Marital Status', 'Area', 'Blood Group', ' Nationality', 'Comm Address', 'Comm City', 'Comm State', 'Comm Pincode', 'Mobile', 'Email', 'Father\'s First Name', 'Father\'s Last Name', 'Father\'s Profession', 'Father\'s Office Address', 'Father\'s City', 'Father\'s State', 'Father\'s Pincode', 'Father\'s Landline', 'Father\'s Email', 'Mother\'s First Name', 'Mother\'s Last Name', 'Mother\'s Profession', 'Permanent Address', 'Parent\'s Mobile 1', 'Parent\'s Mobile 2', 'Permanent City', 'Permanent State', 'Permanent Pincode', 'Permanent Landline', 'Permanent Email', 'Local Guardian\'s Name', 'Local Guardian\'s Address', 'Local Guardian\'s City', 'Local Guradian\'s State', 'Local Guardian\'s Pincode', 'Local Guardian\'s Landline', 'Local Guardian\'s Mobile', 'Admission Category', 'Admit Card', ' JEE Rank Card', 'JEE Roll No', 'JEE Rank', 'JEE Seat Allotment Letter', '10th Marksheet', '10th Certificate', '10th Percentage', '10th Board', '10th Passing State', '12th Marksheet', '12th Certificate', '12th Percentage', '12th Board', '12th Passing State', 'UG Marksheet', 'UG Degree', 'UG Percentage', 'UG University', 'PG Marksheet', 'PG Degree', 'PG Percentage', 'PG University', 'GATE Score Card', 'GATE Year', 'GATE Score', 'CAT Score Card', 'CAT Year', 'CAT Score', 'CSIR', 'CSIR Month', 'CSIR Year', 'CSIR Subject', 'NET', 'NET Month', 'NET Year', 'NET Subject', 'JRF', 'JRF Month', 'JRF Year', 'JRF Subject', 'Transfer Certificate', 'Migration Certificate', 'Character Certificate', 'Caste Certificate', 'PH Certificate', 'Passport', 'Passport No', 'Passport Expiry', 'MCAIP', 'DASA', 'DASA Country', 'Remark', 'Anti Ragging St', 'Anti Ragging Pr', 'Medical Certificate', 'Muslim Minority', 'Other Minority', 'Admission Letter', 'Year', 'Semester Code', 'Section', 'AADHAAR', 'Hostel No', 'Hostel Room', 'ID Card Validity', 'Termination Date', 'Graduation Date'));
foreach ($student_mod as $list) {
    fputcsv($output, $list);
}

fclose($output);

?>


