<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

include_once('../../connect_db.php');

$username = "mbanewuser@gmail.com";

$onlineConnection = onlineConnect();

$sql_query = $onlineConnection->prepare("Select * from applicationform where email1 = '$username' order by registration_timestamp DESC ");
$sql_query->execute();
$data = $sql_query->fetchAll();

if(empty($data)){
    echo "Please submit your application form first.";
    exit;
}
$data = $data[0];
$fields = array("Time",
    "First Name",
    "Middle Name",
    "Last Name",
    "Full Name (Hindi)",
    "Date of Birth",
    "Place of Birth",
    "Category",
    "Sub Category ",
    "Religion",
    "Gender ",
    "Marital Status",
    "Blood Group",
    "Area",
    "Nationality",
    "Address (communication)",
    "City",
    "State",
    "Pin Code ",
    "Phone No. (communication)",
    "AADHAAR",
    "Hostel",
    "Hostel Room",
    "Email",
    "Father First Name",
    "Father Last Name",
    "Father Profession",
    "Father Office Address",
    "City",
    "State",
    "Pin Code ",
    "Phone No. (Father)",
    "Email",
    "Mother First Name ",
    "Mother Last Name",
    "Mother Profession",
    "Permanent Address ",
    "City",
    "State",
    "Pin Code ",
    "Phone No. ",
    "Email",
    "Guardian Name ",
    "Guardian Address",
    "City",
    "Phone No.",
    "Admission Category",
    "Admit Card ",
    "JEE Rank Card",
    "JEE Roll NO.",
    "JEE Rank",
    "JEE Seat Allotment Letter",
    "10th Marksheet",
    "10th Certificate",
    "10th Percentage",
    "10th Board",
    "12th Marksheet",
    "12th Certificate",
    "12th Percentage",
    "12th Board",
    "Graduation Marksheet",
    "Graduation Certificate",
    "Graduation Percentage",
    "Graduation University",
    "PG Marksheet",
    "PG Certificate",
    "PG Percentage",
    "PG University",
    "GATE Score Card",
    "GATE Year",
    "GATE Score",
    "CAT Score Card",
    "CAT Year",
    "CAT Score",
    "Transfer Certificate",
    "Character Certificate",
    "Caste Certificate",
    "PH Certificate",
    "Passport",
    "Passport Number",
    "Validity Period",
    "MCAIP",
    "DASA",
    "Country",
    "Remark",
    "Anti-Rag. Certificate",
    "Anti Ragging remark",
    "Medical Certificate",
    "Muslim Minority",
    "Other Minority",
    "Admission Letter",
    "Admission Withdrawal");

$lines = array();
for ($i = 0; $i <92; $i++){
    $lines[$i] = $i+1 . "    " . $fields[$i];
}

$offlineConnection = offlineConnect();

$categoryQuery = $offlineConnection->prepare("Select category_name from category where category_id = '$data[7]'");
$categoryQuery->execute();
$data[7] = $categoryQuery->fetchAll(PDO::FETCH_COLUMN)[0];
$categoryQuery = $offlineConnection->prepare("Select category_name from category where category_id = '$data[46]'");
$categoryQuery->execute();
//if(empty($data[]))
$data[46] = $categoryQuery->fetchAll(PDO::FETCH_COLUMN)[0];




$religionQuery = $offlineConnection->prepare("Select religion_name from religion where religion_id = '$data[9]'");
$religionQuery->execute();
$data[9] = $religionQuery->fetchAll(PDO::FETCH_COLUMN)[0];

$stateQuery = $offlineConnection->prepare("Select state_name from state where state_id = '$data[17]'");
$stateQuery->execute();
$data[17] = $stateQuery->fetchAll(PDO::FETCH_COLUMN)[0];

if($data[11] == 0){
    $data[11] = "Unmarried";
}else{
    $data[11] = "Married";
}

if($data[47] == 0){ $data[47] = "NO"; } else { $data[47] = "YES"; }
if($data[48] == 0){ $data[48] = "NO"; } else { $data[48] = "YES"; }

if($data[51] == 0){ $data[51] = "NO"; } else { $data[51] = "YES"; }
if($data[52] == 0){ $data[52] = "NO"; } else { $data[52] = "YES"; }
if($data[53] == 0){ $data[53] = "NO"; } else { $data[53] = "YES"; }
if($data[54] == 0){ $data[54] = "NO"; } else { $data[54] = "YES"; }
if($data[56] == 0){ $data[56] = "NO"; } else { $data[56] = "YES"; }
if($data[57] == 0){ $data[57] = "NO"; } else { $data[57] = "YES"; }
if($data[58] == 0){ $data[58] = "NO"; } else { $data[58] = "YES"; }




$stateQuery = $offlineConnection->prepare("Select state_name from state where state_id = '$data[29]'");
$stateQuery->execute();
$data[29] = $stateQuery->fetchAll(PDO::FETCH_COLUMN)[0];

$stateQuery = $offlineConnection->prepare("Select state_name from state where state_id = '$data[38]'");
$stateQuery->execute();
$data[38] = $stateQuery->fetchAll(PDO::FETCH_COLUMN)[0];


if($data[60] == 0){ $data[60] = "NO"; } else { $data[60] = "YES"; }
if($data[61] == 0){ $data[61] = "NO"; } else { $data[61] = "YES"; }
if($data[62] == 0){ $data[62] = "NO"; } else { $data[62] = "YES"; }
if($data[64] == 0){ $data[64] = "NO"; } else { $data[64] = "YES"; }
if($data[65] == 0){ $data[65] = "NO"; } else { $data[65] = "YES"; }
if($data[66] == 0){ $data[66] = "NO"; } else { $data[66] = "YES"; }
if($data[68] == 0){ $data[68] = "NO"; } else { $data[68] = "YES"; }

if($data[71] == 0){ $data[71] = "NO"; } else { $data[71] = "YES"; }

if($data[74] == 0){ $data[74] = "NO"; } else { $data[74] = "YES"; }
if($data[75] == 0){ $data[75] = "NO"; } else { $data[75] = "YES"; }
if($data[76] == 0){ $data[76] = "NO"; } else { $data[76] = "YES"; }
if($data[77] == 0){ $data[77] = "NO"; } else { $data[77] = "YES"; }
if($data[78] == 0){ $data[78] = "NO"; } else { $data[78] = "YES"; }
if($data[81] == 0){ $data[81] = "NO"; } else { $data[81] = "YES"; }

$mycollege = "Indian Institue Of Information Technology, Allahabad";
$auniversity = "(A University)";
$address = "Deoghat - Jhalwa, Allahabad - 2011012";
$registrationform = "Registration Form";
$academicsession = "Academic Session: 2016-2020";

$nameofprogram = "NAME OF PROGRAM ";
$enrollmentno = "Enrolment No. (for office use only) ";

$detailsenroll ="Details of the Student to be Enrolled";

$declaration = "I do herby declare that the above information given by me is true to the best of my knowledge and belief.";
$declaration2 = "If at later date at any stage, it is found to be false, my candidature for admission shall stand cancelled.";
$place = "Place:..........................";
$date =  "Date:...........................";
$signCandidate = "Signature of candidate.............................................";
$signFMG =       "Signature of Father/Mother/Guardian.....................";

$html = "<html>

<body>
<style>
body, p, div { font-size: 14pt; font-family: freeserif;}
h3 { font-size: 15pt; margin-bottom:0; font-family: sans-serif; }
h4 {line-height: 8px; font-size: 16px;}
h2 { line-height: 16px;}
</style>
<h2 style='text-align: center;' >$mycollege</h2>
<h4 style='text-align: center; font-style: italic; font-weight: normal; ' >$auniversity</h4>
<h4 style='text-align: center;' >$address</h4>
<h4 style='text-align: center;' >$registrationform</h4>
<h4 style='text-align: center; font-weight: normal; font-style: italic;' >$academicsession</h4>

<div style='position: relative;'></div>
<table style='font-size: 16px;'>
    <tr style='height: 40px;'><td>NAME OF PROGRAM</td><td style='width:260px; height: 40px; border: 2px solid black;'></td></tr>
    <tr style='height: 40px;'><td>Enrollment No. (For Office use only)</td><td style='width:260px; height: 40px; border: 2px solid black;'></td></tr>
    <tr style='height: 40px;'><td>Signature</td><td style='width:260px; height: 40px; border: 2px solid black;'></td></tr>
</table>

<div style='position:absolute; font-size: 14px; right: 15px; top: 180px; border: 2px solid black; height: 160px; width: 130px;'>Please paste your latest photograph <br>(Do not clip or staple)</div>
</div>

<p style='text-align: center; font-size: 14px; position: relative;margin-top: 24px;'>Details of Student to be Enrolled</p>
                
<table style='font-size: 14px; margin-bottom: 50px'>                            
";

$i = 1;
foreach ($fields as $field) {
    $html .= "<tr><td>$i </td><td style='width: 240px;'>$field</td><td style='width: 60%;border-bottom: 1px solid black;'>" . $data[$i - 1] . "</td></tr>";
    $i++;
}

$html .= "</table>
<p style='font: 15px italic; margin-top: 14px; position: relative;'>$declaration $declaration2</p>


<table style='font-size: 16px'><tr><td width='50%'>$place</td><td>$signCandidate</td></tr><tr><td>$date</td><td>$signFMG</td></tr></table>

";

//==============================================================
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF(''); 

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;
//==============================================================
//==============================================================


?>
