<?php
	include_once('../../includes/include.php');
	include_once('../../includes/mpdf60/mpdf.php');



	if (!ecell_sec_session_start()) {
	  	header('Location: ../../includes/logout.php');
	}

  	if (!ecell_login_check($conn)) {
  		header('Location: ../../includes/logout.php');
  	}

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('results_transcript_all.html'); 

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

		if (isset($_POST['batch_year']) && filter_var($_POST['batch_year'], FILTER_VALIDATE_INT)) {
			$batch_year = $_POST['batch_year']; 
		}

		if (isset($_POST['batch_sem_code']) && filter_var($_POST['batch_sem_code'], FILTER_VALIDATE_INT)) {
			$batch_sem_code = $_POST['batch_sem_code']; 
		}

		function calculateGrade($conn, $grade_id) {
			$stmt = $conn->prepare("SELECT grade FROM grades WHERE grade_id = :grade_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
			$stmt->bindParam(':grade_id', $grade_id);
			$stmt->execute();
			$grade = $stmt->fetchAll()[0]['grade'];

			return $grade;
		}

		function calculateCredit($conn, $grade_id) {
			$stmt = $conn->prepare("SELECT credit FROM grades WHERE grade_id = :grade_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
			$stmt->bindParam(':grade_id', $grade_id);
			$stmt->execute();
			$credit = $stmt->fetchAll()[0]['credit'];
			
			return $credit;
		}

		$mpdf = new mPDF('');

		$stmt = $conn->prepare("SELECT * FROM student WHERE program_id = :program_id AND year = :batch_year AND sem_code = :batch_sem_code AND student_id IN (SELECT DISTINCT student_id FROM results WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')) AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
		$stmt->bindParam(':program_id', $program_id);
		$stmt->bindParam(':batch_year', $batch_year);
		$stmt->bindParam(':batch_sem_code', $batch_sem_code);
		$stmt->execute();
		$student_list = $stmt->fetchAll();

		if (count($student_list)) {
			foreach ($student_list as $student_details) {
				$student_id = $student_details['student_id'];
				$enroll = $student_details['enrollment_no'];

				$DOB = $student_details['dob'];
				$DOB = date('d/m/Y', strtotime($DOB));

				$DOA = $student_details['date_of_admission'];
				$admission_date = date('d/m/Y', strtotime($DOA));

				if ($DOA == '0000-00-00 00:00:00'){
					$admission_date = 'NA';
				}

				$first_name = $student_details['first_name'];
				$middle_name = $student_details['middle_name'];
				$last_name = $student_details['last_name'];

				if (!empty($middle_name)) {
					$student_name = $first_name.' '.$middle_name.' '.$last_name;
				} else {
					$student_name = $first_name.' '.$last_name;
				}

				$father_first_name = $student_details['father_first_name'];
				$father_last_name = $student_details['father_last_name'];
				$father_name = $father_first_name.' '.$father_last_name;	

				$stmt = $conn->prepare("SELECT * FROM program WHERE program_id = :program_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
				$stmt->bindParam(':program_id', $program_id);
				$stmt->execute();
				$program_details = $stmt->fetchAll();
				
				$program_name = $program_details[0]['program_name'];

				$program_duration = $program_details[0]['program_duration'];
	            $program_duration = preg_replace('/\D/', '', $program_duration);

	            $end_date = date('Y-m-d', strtotime(date('Y-m-d', strtotime($DOA)) . '+ '.$program_duration.' years'));
				$end_date = date('Y-m-d', strtotime(date('Y-m-d', strtotime($end_date)) . '- 1 month'));

				$time_start = strtotime($DOA);
				$month_start = date('F', $time_start);
				$year_start = date('Y', $time_start);

				$time_end = strtotime($end_date);
				$month_end = date('F', $time_end);
				$year_end = date('Y', $time_end);
				
				if ($DOA == '0000-00-00 00:00:00') {
					$month_start = 'NA';
					$month_end = 'NA';
					$year_start = '';
					$year_end = '';
				}		

				$program_period = $month_start.' '.$year_start.' - '.$month_end.' '.$year_end;

				$mpdf->AddPage();

				$html_front = "
					<html>
						<body>
							<div>
								<div style='float:left;width:10%'>
									<img src='../../fpdf/iiita.png' width='15mm'>
			                    </div>
			                    <div style='float:left;width:90%'>
			                        <p style='font-size:15;font-family:Times New Roman;text-align:center;line-height:1px;padding-top:10px'><b>Indian Institute of Information Technology, Allahabad</b></p>
			                        <p style='font-size:13;font-family:Times New Roman;text-align:center;line-height:1px'>Deoghat, Jhalwa, Allahabad-211012, U.P. India</p>
									<p style='font-size:13;font-family:Times New Roman;text-align:center;line-height:1px'>TRANSCRIPT</p>
								</div>
							</div>
							<table align='center' style='border:1px solid black;border-collapse:collapse'>
	                            <tr>
	                                <td style='font-size:11;font-family:Times New Roman;text-align:left;width:80mm;height:12px'>Enrollment No. : <b>$enroll</b></td>
	                                <td style='font-size:11;font-family:Times New Roman;text-align:left;width:100mm;height:12px;border-left:1px solid black'>Program : <b>$program_name</b></td>
	                           	</tr>
	                           	<tr>
	                                <td style='font-size:11;font-family:Times New Roman;text-align:left;width:80mm;height:12px'>Student's Name : <b>$student_name</b></td>
	                                <td style='font-size:11;font-family:Times New Roman;text-align:left;width:100mm;height:12px;border-left:1px solid black'>Date of Admission : <b>$admission_date</b></td>
	                            </tr>
	                            <tr>
	                                <td style='font-size:11;font-family:Times New Roman;text-align:left;width:80mm;height:12px'>Date of Birth : <b>$DOB</b></td>
	                                <td style='font-size:11;font-family:Times New Roman;text-align:left;width:100mm;height:12px;border-left:1px solid black'>Period of Program : <b>$program_period</b></td>
	                           	</tr>
	                           	<tr>
	                                <td style='font-size:11;font-family:Times New Roman;text-align:left;width:80mm;height:12px'>Father's Name : <b>$father_name</b></td>
	                                <td style='font-size:11;font-family:Times New Roman;text-align:left;width:100mm;height:12px;border-left:1px solid black'>Duration of Program : <b>$program_duration years</b></td>
	                           	</tr>
	                       	</table>
				";

				$stmt = $conn->prepare("SELECT DISTINCT course_structure.sem_id FROM results, course_structure WHERE results.sem_id = course_structure.sem_id AND results.student_id = :student_id AND results.status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') AND course_structure.status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY course_structure.sem_title");
	            $stmt->bindParam(':student_id', $student_id);
	            $stmt->execute();
	            $sem_id_list = $stmt->fetchAll();

				$html_front .= "
					<div style='border:1px solid black;padding:8px 3px 8px 3px'>
						<table align='left'>
				";

				$fail = 0;
				$total_sgpi = 0;
				$total_program_credits = 0;
				$creditsEarned = 0;
				$creditsAppeared = 0;
				$num_sem = $program_duration * 2;

				$course_list = array();

				foreach ($sem_id_list as $sem_list) {
					$sem_id = $sem_list['sem_id'];

					$stmt = $conn->prepare("SELECT * FROM course_structure WHERE sem_id = :sem_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
		            $stmt->bindParam(':sem_id', $sem_id);
		            $stmt->execute();
		            $sem_details = $stmt->fetchAll();

					$year = $sem_details[0]['sem_id_year'];
					$sem_code = $sem_details[0]['sem_id_sem_code'];
					$sem_title = $sem_details[0]['sem_title'];

					$stmt = $conn->prepare("SELECT * FROM sem_cancel WHERE student_id = :student_id AND year = :year AND sem_code = :sem_code");
		            $stmt->bindParam(':student_id', $student_id);
		            $stmt->bindParam(':year', $year);
		            $stmt->bindParam(':sem_code', $sem_code);
		            $stmt->execute();
		            $sem_cancel = $stmt->fetchAll();

		            if (count($sem_cancel)) {
		            	continue;
		            }

		            $stmt = $conn->prepare("SELECT title FROM sem_code_description WHERE sem_code_id = :sem_code AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
		            $stmt->bindParam(':sem_code', $sem_code);
		            $stmt->execute();
		            $title = $stmt->fetchAll()[0]['title'];

		            $session = $title.' '.$year;

					$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND sem_id = :sem_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY `timestamp` DESC");
					$stmt->bindParam(':student_id', $student_id);
					$stmt->bindParam(':sem_id', $sem_id);
					$stmt->execute();

					if ($stmt->rowCount() == 0) {
						continue;
					}

					$results = $stmt->fetchAll();

					if (preg_match('/Semester/', $sem_title)) {
						$sem_no = preg_replace('/\D/', '', $sem_title);
					}

					$flag = 0;
					$sgpi = 0;
					$total_credits = 0;
					$crs_read = array();

					if ($sem_no == $num_sem && $num_sem == 10) {
						foreach ($results as $res) {
							$course_id = $res['course_id'];
							
							$crs_repeat = 0;
							foreach ($crs_read as $crs) {
								if ($course_id == $crs) {
									$crs_repeat = 1;
									break;
								}
							}

							if ($crs_repeat == 1) {
								continue;
							}

							$crs_read[] = $course_id;

							$theory_grade_id = $res['theory_grade'];
							$lab_grade_id = $res['lab_grade'];

							$theory_grade = calculateGrade($conn, $theory_grade_id);
							$lab_grade = calculateGrade($conn, $lab_grade_id);				
							
							if ($theory_grade == 'F' || $theory_grade == 'F(Ab)' || $lab_grade == 'F' || $lab_grade == 'F(Ab)') {
								$fail = 1;
							}

							if ($fail == 1) {
								break;
							}
						}

						if ($fail == 1) {
							break;
						}

						$sem_title = $sem_title.' ('.$session.')';
						
						$html_front .= "
							<tr>
			                    <td colspan='3' style='vertical-align:top'>
			                    	<table style='border-collapse:collapse'>
			                    		<tr>
			                    			<td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;width:183mm;height:20px;border:1px solid black'><b>$sem_title</b></td>
			                            </tr>
			                            <tr>
			                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:81mm;height:20px;border:1px solid black'><b>Course Code</b></td>
			                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:51mm;height:20px;border:1px solid black'><b>Grade</b></td>
			                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:51mm;height:20px;border:1px solid black'><b>Credits</b></td>
										</tr>
						";

						$crs_read = array();

						foreach ($results as $res) {
							$course_id = $res['course_id'];
							
							$crs_repeat = 0;
							foreach ($crs_read as $crs) {
								if ($course_id == $crs) {
									$crs_repeat = 1;
									break;
								}
							}

							if ($crs_repeat == 1) {
								continue;
							}

							$crs_read[] = $course_id;
							$course_list[] = $course_id;

							$theory_grade_id = $res['theory_grade'];
							$lab_grade_id = $res['lab_grade'];

							$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :course_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
							$stmt->bindParam(':course_id', $course_id);
							$stmt->execute();
							$course_details = $stmt->fetchAll();

							$course_code = $course_details[0]['course_code'];
							$course_name = $course_details[0]['course_name'];
							$theory_course_credits = $course_details[0]['theory_credit'];
							$lab_course_credits = $course_details[0]['lab_credit'];

							$theory_grade = calculateGrade($conn, $theory_grade_id);
							$lab_grade = calculateGrade($conn, $lab_grade_id);

							$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = (SELECT exam_type_id FROM exam_type WHERE exam_type = 'Back Exam') AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY `timestamp` DESC");
							$stmt->bindParam(':student_id', $student_id);
							$stmt->bindParam(':course_id', $course_id);
							$stmt->execute();
							$back = $stmt->fetchAll();

							if (count($back)) {
								$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = (SELECT exam_type_id FROM exam_type WHERE exam_type = 'End Sem') AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY `timestamp` DESC");
								$stmt->bindParam(':student_id', $student_id);
								$stmt->bindParam(':course_id', $course_id);
								$stmt->execute();
								$back1 = $stmt->fetchAll();

								$theory_grade_back = calculateGrade($conn, $back1[0]['theory_grade']);
								$lab_grade_back = calculateGrade($conn, $back1[0]['lab_grade']);

								if ($theory_grade_back == 'F' || $theory_grade_back == 'F(Ab)') {
									$theory_grade = '<b>*</b>'.$theory_grade;
								}

								if ($lab_grade_back == 'F' || $lab_grade_back == 'F(Ab)') {
									$lab_grade = '<b>*</b>'.$lab_grade;
								}
							}
							
							$total_credits += ($theory_course_credits + $lab_course_credits);
							$creditsEarned += ($theory_course_credits + $lab_course_credits);
							
							if ($theory_course_credits == 0) {
								$theory_grade = '-';
							}

							if ($lab_course_credits == 0) {
								$lab_grade = '-';
							}

							$theory_credits = calculateCredit($conn, $theory_grade_id); 
							$lab_credits = calculateCredit($conn, $lab_grade_id);

							if ($theory_credits > 0) {
								$sgpi += ($theory_credits * $theory_course_credits);
							} 
							
							if ($lab_credits > 0) {
								$sgpi += ($lab_credits * $lab_course_credits);
							}

							$html_front .= "
								<tr>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:60px;border:1px solid black'><b>$course_code</b></td>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:60px;border:1px solid black'><b>$theory_grade</b></td>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:60px;border:1px solid black'><b>$theory_credits</b></td>
								</tr>
							";
						}

						$total_program_credits += $total_credits;
						$total_sgpi += $sgpi;
						$sgpi /= $total_credits;
						$sgpi = number_format((float)$sgpi, 2, '.', '');

						$html_front .= "
										<tr>
				                            <td style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Total Credits : $total_credits</b></td>
				                            <td colspan='2' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>SGPI = $sgpi</b></td>
				                        </tr>
				                        <tr>
				                            <td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Result: Passed and Course Completed Successfully</b></td>
										</tr>
									</table>
								</td>		
							</tr>
						";
					} else if ($sem_no == $num_sem && $num_sem == 4) {
						foreach ($results as $res) {
							$course_id = $res['course_id'];
							
							$crs_repeat = 0;
							foreach ($crs_read as $crs) {
								if ($course_id == $crs) {
									$crs_repeat = 1;
									break;
								}
							}

							if ($crs_repeat == 1) {
								continue;
							}

							$crs_read[] = $course_id;
							
							$theory_grade_id = $res['theory_grade'];
							$lab_grade_id = $res['lab_grade'];

							$theory_grade = calculateGrade($conn, $theory_grade_id);
							$lab_grade = calculateGrade($conn, $lab_grade_id);				
							
							if ($theory_grade == 'F' || $theory_grade == 'F(Ab)' || $lab_grade == 'F' || $lab_grade == 'F(Ab)') {
								$fail = 1;
							}

							if ($fail == 1) {
								break;
							}
						}

						if ($fail == 1) {
							break;
						}
					
						$sem_title = $sem_title.' ('.$session.')';

						$html_front .= "
							<tr>
			                    <td colspan='3' style='vertical-align:top'>
			                    	<table style='border-collapse:collapse'>
			                    		<tr>
			                    			<td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;width:183mm;height:20px;border:1px solid black'><b>$sem_title</b></td>
			                            </tr>
			                            <tr>
			                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:81mm;height:20px;border:1px solid black'><b>Course Code</b></td>
			                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:51mm;height:20px;border:1px solid black'><b>Grade</b></td>
			                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:51mm;height:20px;border:1px solid black'><b>Credits</b></td>
										</tr>
						";

						$crs_read = array();

						foreach ($results as $res) {
							$course_id = $res['course_id'];

							$crs_repeat = 0;
							foreach($crs_read as $crs){
								if ($course_id == $crs) {
									$crs_repeat = 1;
									break;
								}
							}

							if ($crs_repeat == 1) {
								continue;
							}

							$crs_read[] = $course_id;
							$course_list[] = $course_id;

							$theory_grade_id = $res['theory_grade'];
							$lab_grade_id = $res['lab_grade'];

							$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :course_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
							$stmt->bindParam(':course_id', $course_id);
							$stmt->execute();
							$course_details = $stmt->fetchAll();

							$course_code = $course_details[0]['course_code'];
							$course_name = $course_details[0]['course_name'];
							$theory_course_credits = $course_details[0]['theory_credit'];
							$lab_course_credits = $course_details[0]['lab_credit'];

							$theory_grade = calculateGrade($conn, $theory_grade_id);
							$lab_grade = calculateGrade($conn, $lab_grade_id);

							$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = (SELECT exam_type_id FROM exam_type WHERE exam_type = 'Back Exam') AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY `timestamp` DESC");
							$stmt->bindParam(':student_id', $student_id);
							$stmt->bindParam(':course_id', $course_id);
							$stmt->execute();
							$back = $stmt->fetchAll();

							if (count($back)) {
								$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = (SELECT exam_type_id FROM exam_type WHERE exam_type = 'End Sem') AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY `timestamp` DESC");
								$stmt->bindParam(':student_id', $student_id);
								$stmt->bindParam(':course_id', $course_id);
								$stmt->execute();
								$back1 = $stmt->fetchAll();

								$theory_grade_back = calculateGrade($conn, $back1[0]['theory_grade']);
								$lab_grade_back = calculateGrade($conn, $back1[0]['lab_grade']);

								if ($theory_grade_back == 'F' || $theory_grade_back == 'F(Ab)') {
									$theory_grade = '<b>*</b>'.$theory_grade;
								}

								if ($lab_grade_back == 'F' || $lab_grade_back == 'F(Ab)') {
									$lab_grade = '<b>*</b>'.$lab_grade;
								}
							}
						
							$total_credits += ($theory_course_credits + $lab_course_credits);
							$creditsEarned += ($theory_course_credits + $lab_course_credits);
							
							if ($theory_course_credits == 0) {
								$theory_grade = '-';
							}

							if ($lab_course_credits == 0) {
								$lab_grade = '-';
							}

							$theory_credits = calculateCredit($conn, $theory_grade_id); 
							$lab_credits = calculateCredit($conn, $lab_grade_id); 

							if ($theory_credits > 0) {
								$sgpi += ($theory_credits * $theory_course_credits);
							}

							if ($lab_credits > 0) {
								$sgpi += ($lab_credits * $lab_course_credits);
							}

							$html_front .= "
								<tr>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:60px;border:1px solid black'><b>$course_code</b></td>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:60px;border:1px solid black'><b>$theory_grade</b></td>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:60px;border:1px solid black'><b>$theory_credits</b></td>
								</tr>
							";
						}

						$total_program_credits += $total_credits;
						$total_sgpi += $sgpi;
						$sgpi /= $total_credits;
						$sgpi = number_format((float)$sgpi, 2, '.', '');

						$html_front .= "
										<tr>
				                            <td style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Total Credits : $total_credits</b></td>
				                            <td colspan='2' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>SGPI = $sgpi</b></td>
				                        </tr>
				                        <tr>
				                            <td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Result: Passed and Course Completed Successfully</b></td>
										</tr>
									</table>
								</td>		
							</tr>
						";
					} else if ($sem_no == $num_sem && $num_sem == 8) {
						foreach ($results as $res) {
							$course_id = $res['course_id'];
							
							$crs_repeat = 0;
							foreach ($crs_read as $crs) {
								if ($course_id == $crs) {
									$crs_repeat = 1;
									break;
								}
							}

							if ($crs_repeat == 1) {
								continue;
							}

							$crs_read[] = $course_id;
							
							$theory_grade_id = $res['theory_grade'];
							$lab_grade_id = $res['lab_grade'];

							$theory_grade = calculateGrade($conn, $theory_grade_id);
							$lab_grade = calculateGrade($conn, $lab_grade_id);				
							
							if ($theory_grade == 'F' || $theory_grade == 'F(Ab)' || $lab_grade == 'F' || $lab_grade == 'F(Ab)') {
								$fail = 1;
							}

							if ($fail == 1) {
								break;
							}
						}

						if ($fail == 1) {
							break;
						}

						$sem_title = $sem_title.' ('.$session.')';

						$html_front .= "
		                    <td colspan='2' style='vertical-align:top'>
		                    	<table style='border-collapse:collapse'>
		                    		<tr>
		                    			<td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;width:120mm;height:20px;border:1px solid black'><b>$sem_title</b></td>
		                            </tr>
		                            <tr>
		                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:50mm;height:20px;border:1px solid black'><b>Course Code</b></td>
		                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:35mm;height:20px;border:1px solid black'><b>Grade</b></td>
		                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:35mm;height:20px;border:1px solid black'><b>Credits</b></td>
									</tr>
						";
						
						$crs_read = array();

						foreach ($results as $res) {
							$course_id = $res['course_id'];
							
							$crs_repeat = 0;
							foreach ($crs_read as $crs) {
								if ($course_id == $crs) {
									$crs_repeat = 1;
									break;
								}
							}

							if ($crs_repeat == 1) {
								continue;
							}

							$crs_read[] = $course_id;
							$course_list[] = $course_id;

							$theory_grade_id = $res['theory_grade'];
							$lab_grade_id = $res['lab_grade'];

							$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :course_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
							$stmt->bindParam(':course_id', $course_id);
							$stmt->execute();
							$course_details = $stmt->fetchAll();

							$course_code = $course_details[0]['course_code'];
							$course_name = $course_details[0]['course_name'];
							$theory_course_credits = $course_details[0]['theory_credit'];
							$lab_course_credits = $course_details[0]['lab_credit'];

							$theory_grade = calculateGrade($conn, $theory_grade_id);
							$lab_grade = calculateGrade($conn, $lab_grade_id);	

							$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = (SELECT exam_type_id FROM exam_type WHERE exam_type = 'Back Exam') AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY `timestamp` DESC");
							$stmt->bindParam(':student_id', $student_id);
							$stmt->bindParam(':course_id', $course_id);
							$stmt->execute();
							$back = $stmt->fetchAll();

							if (count($back)) {
								$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = (SELECT exam_type_id FROM exam_type WHERE exam_type = 'End Sem') AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY `timestamp` DESC");
								$stmt->bindParam(':student_id', $student_id);
								$stmt->bindParam(':course_id', $course_id);
								$stmt->execute();
								$back1 = $stmt->fetchAll();

								$theory_grade_back = calculateGrade($conn, $back1[0]['theory_grade']);
								$lab_grade_back = calculateGrade($conn, $back1[0]['lab_grade']);

								if ($theory_grade_back == 'F' || $theory_grade_back == 'F(Ab)') {
									$theory_grade = '<b>*</b>'.$theory_grade;
								}

								if ($lab_grade_back == 'F' || $lab_grade_back == 'F(Ab)') {
									$lab_grade = '<b>*</b>'.$lab_grade;
								}
							}			

							$total_credits += ($theory_course_credits + $lab_course_credits);
							$creditsEarned += ($theory_course_credits + $lab_course_credits);

							if ($theory_course_credits == 0) {
								$theory_grade = '-';
							}

							if ($lab_course_credits == 0) {
								$lab_grade = '-';
							}

							$theory_credits = calculateCredit($conn, $theory_grade_id); 
							$lab_grade_credits = calculateCredit($conn, $lab_grade_id);

							if ($theory_credits > 0) {
								$sgpi += ($theory_credits * $theory_course_credits);
							} 

							if ($lab_credits > 0) {
								$sgpi += ($lab_credits * $lab_course_credits);
							}

							$html_front .= "
								<tr>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:40px;border:1px solid black'><b>$course_code</b></td>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:40px;border:1px solid black'><b>$theory_grade</b></td>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:40px;border:1px solid black'><b>$theory_credits</b></td>
								</tr>
							";
						}

						$total_program_credits += $total_credits;
						$total_sgpi += $sgpi;
						$sgpi /= $total_credits;
						$sgpi = number_format((float)$sgpi, 2, '.', '');

						$html_front .= "
										<tr>
				                            <td style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Total Credits : $total_credits</b></td>
				                            <td colspan='2' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>SGPI = $sgpi</b></td>
				                        </tr>
				                        <tr>
				                            <td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Result: Passed and Course Completed Successfully</b></td>
										</tr>
									</table>
								</td>		
							</tr>
						";
					} else {
						foreach ($results as $res) {
							$course_id = $res['course_id'];
							
							$crs_repeat = 0;
							foreach ($crs_read as $crs) {
								if ($course_id == $crs) {
									$crs_repeat = 1;
									break;
								}
							}

							if ($crs_repeat == 1) {
								continue;
							}

							$crs_read[] = $course_id;
							
							$theory_grade_id = $res['theory_grade'];
							$lab_grade_id = $res['lab_grade'];

							$theory_grade = calculateGrade($conn, $theory_grade_id);
							$lab_grade = calculateGrade($conn, $lab_grade_id);

							if ($lab_grade == 'F' || $lab_grade == 'F(Ab)' || $theory_grade == 'F' || $theory_grade == 'F(Ab)') {
								$fail = 1;
							}

							if ($fail == 1) {
								break;
							}
						}

						if ($fail == 1) {
							break;
						}

						if ($sem_no % 3 == 1) {
							$html_front .= "
								<tr>
							";
						}

						$crs_read = array();

						foreach ($results as $res) {
							$course_id = $res['course_id'];
							
							$crs_repeat = 0;
							foreach ($crs_read as $crs) {
								if ($course_id == $crs) {
									$crs_repeat = 1;
									break;
								}
							}

							if ($crs_repeat == 1) {
								continue;
							}

							$crs_read[] = $course_id;
							$course_list[] = $course_id;

							$theory_grade_id = $res['theory_grade'];
							$lab_grade_id = $res['lab_grade'];

							$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :course_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
							$stmt->bindParam(':course_id', $course_id);
							$stmt->execute();
							$course_details = $stmt->fetchAll();

							$course_code = $course_details[0]['course_code'];
							$course_name = $course_details[0]['course_name'];
							$theory_course_credits = $course_details[0]['theory_credit'];
							$lab_course_credits = $course_details[0]['lab_credit'];

							$theory_grade = calculateGrade($conn, $theory_grade_id);
							$lab_grade = calculateGrade($conn, $lab_grade_id);

							$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = (SELECT exam_type_id FROM exam_type WHERE exam_type = 'Back Exam') AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY `timestamp` DESC");
							$stmt->bindParam(':student_id', $student_id);
							$stmt->bindParam(':course_id', $course_id);
							$stmt->execute();
							$back = $stmt->fetchAll();

							if (count($back)) {
								$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = (SELECT exam_type_id FROM exam_type WHERE exam_type = 'End Sem') AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') ORDER BY `timestamp` DESC");
								$stmt->bindParam(':student_id', $student_id);
								$stmt->bindParam(':course_id', $course_id);
								$stmt->execute();
								$back1 = $stmt->fetchAll();

								$theory_grade_back = calculateGrade($conn, $back1[0]['theory_grade']);
								$lab_grade_back = calculateGrade($conn, $back1[0]['lab_grade']);

								if ($theory_grade_back == 'F' || $theory_grade_back == 'F(Ab)') {
									$theory_grade = '<b>*</b>'.$theory_grade;
								}

								if ($lab_grade_back == 'F' || $lab_grade_back == 'F(Ab)') {
									$lab_grade = '<b>*</b>'.$lab_grade;
								}
							}
							
							$total_credits += ($theory_course_credits + $lab_course_credits);
							$creditsEarned += ($theory_course_credits + $lab_course_credits);

							if ($lab_course_credits == 0) {
								$lab_grade = '-';
							}

							if ($theory_course_credits == 0) {
								$theory_grade = '-';
							}

							$theory_credits = calculateCredit($conn, $theory_grade_id); 
							$lab_credits = calculateCredit($conn, $lab_grade_id); 

							if ($theory_credits > 0) {
								$sgpi += ($theory_credits * $theory_course_credits);
							}

							if ($lab_credits > 0) {
								$sgpi += ($lab_credits * $lab_course_credits);
							}

							if ($flag == 0) {
								$sem_title = $sem_title.' ('.$session.')';

								$html_front .= "
	                                <td style='vertical-align:top'>
	                                	<table style='border-collapse:collapse'>
	                                		<tr>
	                                			<td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;width:60mm;height:20px;border:1px solid black'><b>$sem_title</b></td>
			                                </tr>
				                            <tr>
				                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:20mm;height:20px;border:1px solid black'><b>Course Code</b></td>
				                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:20mm;height:20px;border:1px solid black'><b>Theory Grade</b></td>
				                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:20mm;height:20px;border:1px solid black'><b>Lab Grade</b></td>
											</tr>
								";

								$flag = 1;
							}

							$html_front .= "
								<tr>
		                            <td style='font-size:9;font-family:Courier;text-align:left;height:20px;border:1px solid black'>$course_code</td>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:20px;border:1px solid black'>$theory_grade</td>
		                            <td style='font-size:9;font-family:Courier;text-align:center;height:20px;border:1px solid black'>$lab_grade</td>
								</tr>
							";
						}

						if ($flag) {
							$total_program_credits += $total_credits;
							$total_sgpi += $sgpi;
							$sgpi /= $total_credits;
							$sgpi = number_format((float)$sgpi, 2, '.', '');

							$html_front .= "
										<tr>
				                            <td colspan='2' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Total Credits : $total_credits</b></td>
				                            <td style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>SGPI = $sgpi</b></td>
				                        </tr>
				                        <tr>
				                            <td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'>Result: Passed and Promoted to Next Semester</td>
										</tr>
									</table>
								</td>		
							";
						}

						if ($sem_no % 3 == 0) {
							$html_front .= "
								</tr>
							";
						}
					}

					if ($fail == 1) {
						break;
					}
				}

				$html_front .= "
					</table>
				";

				$cgpi = $total_sgpi / $total_program_credits;
				$cgpi = number_format((float)$cgpi, 2, '.', '');

				$stmt = $conn->prepare("SELECT DISTINCT courses.course_id, courses.theory_credit, courses.lab_credit FROM results, courses WHERE results.course_id = courses.course_id AND results.student_id = :student_id");
				$stmt->bindParam(':student_id', $student_id);
				$stmt->execute();
				$credits = $stmt->fetchAll();

				$creditsAppeared = 0;
				foreach ($credits as $crd) {
					$creditsAppeared += ($crd['theory_credit'] + $crd['lab_credit']); 
				}

				$html_front .= "
								<table align='left'>
									<tr>
										<td style='font-size:11;font-family:Times New Roman;text-align:left;line-height:2px;padding-top:10px;padding-right:25px'><b>CGPI : $cgpi</b></td>
										<td style='font-size:11;font-family:Times New Roman;text-align:left;line-height:2px;padding-top:10px;padding-right:25px'><b>Credits Appeared : $creditsAppeared</b></td>
										<td style='font-size:11;font-family:Times New Roman;text-align:left;line-height:2px;padding-top:10px;padding-right:25px'><b>Credits Earned : $creditsEarned</b></td>
									</tr>
								</table>
								<table align='center'>
									<tr>
										<td style='font-size:11;font-family:Times New Roman;text-align:center;line-height:2px;width:60mm;padding-top:70px;padding-bottom:20px'><b>Prepared By</b></td>
										<td style='font-size:11;font-family:Times New Roman;text-align:center;line-height:2px;width:60mm;padding-top:70px;padding-bottom:20px'><b>Checked By</b></td>
										<td style='font-size:11;font-family:Times New Roman;text-align:center;line-height:2px;width:60mm;padding-top:70px;padding-bottom:20px'><b>Assistant Registrar (Exam)</b></td>
									</tr>
								</table>
							</div>
						</body>
					</html>
				";

				$mpdf->WriteHTML($html_front);

				// Back side of page

	            $mpdf->AddPage();

	            $html_back = "
	                <html>
	                    <body>
	                        <div style='padding:8px'>
	                            <p style='font-size:11;font-family:Times New Roman;text-align:center'><b><u>Award System and Other Relevant Information</u></b></p>
	                            <ul style='font-size:10;font-family:Times New Roman;text-align:left;line-height:15px'>
	                                <li>Medium of Instruction: English.</li>
	                                <li>Whole program consists of $num_sem semesters of 6 months each.</li>
	                                <li>Each semester has a minimum credit requirement of 20 credits. Students have two sets of courses for semester fifth onwards - Core & Elective. Students have to select courses from elective course baskets to meet their minimum credit requirement for the corresponding semester.</li>
	                                <li>A theory credit denotes 1 hour of lecture per week and one practical credit denotes 1 and 1/2 hours of laboratory work per week.</li>
	                                <li>The coding of subjects is as per fig.-1.</li>
	                                <li>Duration of each semester is a minimum of 18 instructional weeks.</li>
	                                <li>Senate of Indian Institute of Information Technology, Allahabad, prescribes course work.</li>
	                                <li>No equivalent PERCENTAGE of marks is awarded as Institute follows a relative grading system.</li>
	                                <li>CGPI 7 will be considered as first division/class.</li>
	                                <li>* Marks on a Course indicates that Paper Cleared Through Back Paper Exam.</li>
	                                <li>Grades are awarded at the end of each semester as per the criteria given in table-1.</li>
	                                <li><b>SGPI</b> (Semester Grade Point Index) is weighted mean of grade value with the credit value of each course serving as the weight.<br><b>CGPI</b> (Cumulative Grade Point Index) is weighted mean of SGPI value of all completed semesters with respective total credits appeared serving as the weights.</li>
	                            </ul>
	                        </div>
	                       	<div style='border:1px solid black'>
		                        <div style='padding:5px;float:left;width:30%'>
		                            <table align='center' style='border-collapse:collapse'>
		                                <tr>
		                                	<th colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center'><b>Grade value in Table-1</b></th>
		                                </tr>
		                                <tr>
		                                    <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:10mm;border:1px solid black'><b>Grade</b></th>
		                                    <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:20mm;border:1px solid black'><b>Meaning</b></th>
		                                    <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:10mm;border:1px solid black'><b>Grade Value</b></th>
		                                </tr>
	            ";

	            $stmt = $conn->prepare("SELECT * FROM grades WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
	            $stmt->execute();
	            $allowedGrades = $stmt->fetchAll();

	            foreach ($allowedGrades as $value) {
	                $grade_name = $value['grade'];
	                $grade_meaning = $value['description'];
	                $grade_credit = $value['credit'];

	                if ($grade_credit > 0 || $grade_name == 'F') {
	                    $html_back .= "
	                        <tr>
	                            <td style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;border:1px solid black'>$grade_name</td>
	                            <td style='font-size:10;font-family:Times New Roman;text-align:left;height:13px;border:1px solid black'>$grade_meaning</td>
	                            <td style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;border:1px solid black'>$grade_credit</td>
	                        </tr>
	                    ";
	                }  
	            }

	            $html_back .= "
	                        </table>
	                    </div>
	                    <div style='border-left:1px solid black;padding:5px;float:right;width:65%'>
	                        <table align='center' style='border-collapse:collapse'>
	                            <tr>
	                            	<th colspan='5' style='font-size:10;font-family:Times New Roman;text-align:center'><b>Fig.-1</b></th>
	                            </tr>
	                            <tr>
	                                <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:20%;border:1px solid black'><b>Individual department offering the course</b></th>
	                                <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:20%;border:1px solid black'><b>Course Abbreviation</b></th>
	                                <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:20%;border:1px solid black'><b>Semester in which the course is normally offered</b></th>
	                                <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:20%;border:1px solid black'><b>Theory Credit</b></th>
	                                <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:20%;border:1px solid black'><b>Lab Credit</b></th>
	                                <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:20%;border:1px solid black'><b>Course Type</b></th>
	                            </tr>
	                            <tr>
	                                <td style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;border:1px solid black'>A, I, E, M, S<br>(One Character)</td>
	                                <td style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;border:1px solid black'>(Three Characters)</td>
	                                <td style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;border:1px solid black'>UG = (1 - 8)<br>PG = (1 - 4)<br>Integrated = (1 - X)<br>(One Digit)</td>
	                                <td style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;border:1px solid black'>(One Digit)</td>
	                                <td style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;border:1px solid black'>(One Digit)</td>
	                                <td style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;border:1px solid black'>Flexible = F<br>Elective = E<br>Compulsory = C<br>Project = P<br>Web = W<br>(One Character)</td>
	                            </tr>
	                        </table>
	                    </div>
	                </div>
	            ";

	            $course_list_length = count($course_list);

		        if ($course_list_length % 2 == 0) {
		        	$course_list_1 = array_slice($course_list, 0, $course_list_length / 2);
					$course_list_2 = array_slice($course_list, $course_list_length / 2);
		        } else {
		        	$course_list_1 = array_slice($course_list, 0, (ceil)($course_list_length / 2));
					$course_list_2 = array_slice($course_list, (ceil)($course_list_length / 2));
		        }

	            $html_back .= "
	                <div style='border:1px solid black;padding:5px'>
	                	<div style='float:left;width:50%'>
		                	<table align='center' style='border-collapse:collapse'>
		                        <tr>
		                            <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:25mm;border:1px solid black'><b>Course Code</b></th>
		                            <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:60mm;border:1px solid black'><b>Course Name</b></th>
		                        </tr>
		        ";

		        foreach ($course_list_1 as $crs) {
	        		$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :crs AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
		            $stmt->bindParam(':crs', $crs);
		            $stmt->execute();
		            $courses = $stmt->fetchAll();

		            $course_code = $courses[0]['course_code'];
		            $course_name = $courses[0]['course_name'];

		        	$html_back .= "
		        		<tr>
	                        <td style='font-size:10;font-family:Courier;text-align:left;height:13px;border:1px solid black'>$course_code</td>
	                        <td style='font-size:10;font-family:Times New Roman;text-align:left;height:13px;border:1px solid black'>$course_name</td>
	                    </tr>
		        	";
		        }

		        $html_back .= "
		        		</table>
		        	</div>
		        	<div style='float:right;width:50%'>
		            	<table align='center' style='border-collapse:collapse'>
		                    <tr>
		                        <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:25mm;border:1px solid black'><b>Course Code</b></th>
		                        <th style='font-size:10;font-family:Times New Roman;text-align:center;height:13px;width:60mm;border:1px solid black'><b>Course Name</b></th>
		                    </tr>
		        ";

		        foreach ($course_list_2 as $crs) {
	        		$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :crs AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
		            $stmt->bindParam(':crs', $crs);
		            $stmt->execute();
		            $courses = $stmt->fetchAll();

		            $course_code = $courses[0]['course_code'];
		            $course_name = $courses[0]['course_name'];

		        	$html_back .= "
		        		<tr>
	                        <td style='font-size:10;font-family:Courier;text-align:left;height:13px;border:1px solid black'>$course_code</td>
	                        <td style='font-size:10;font-family:Times New Roman;text-align:left;height:13px;border:1px solid black'>$course_name</td>
	                    </tr>
		        	";
		        }

		        $html_back .= "
		        					</table>
		        				</div>
		                    </div>
	                    </body>
	                </html>
	            ";

	            $mpdf->WriteHTML($html_back);
	        }

			$file = $program_id."_Transcript.pdf";
			$mpdf->Output($file, 'I');
		} else {
			$msg_err = "Year and semester code of joining is invalid.";
		}
	}

	$TBS->MergeBlock('program', $conn, "SELECT * FROM program WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
	$TBS->MergeBlock('sem', $conn, "SELECT * FROM sem_code_description WHERE status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on')");
	$TBS->Show();
?>