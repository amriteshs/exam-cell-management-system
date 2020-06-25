<?php

include_once('connect.php');
include_once('session.php');

$hashstr = ecell_grade_hash();

function ecell_convert_date($date)
{
    $date = explode('/', $date);

    $day = str_pad($date[1], 2, '0', STR_PAD_LEFT);
    $month = str_pad($date[0], 2, '0', STR_PAD_LEFT);
    $year = $date[2];

    $date = $year . '-' . $month . '-' . $day;

    return $date;
}

function ecell_grade_hash()
{

    $conn = ecell_get_conn();

    $sql = "SELECT * FROM results";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount()) {
        $sql = "CHECKSUM TABLE results";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $checksum = $stmt->fetchAll()[0][1];

        $sql = "SELECT staff.staff_name, log.time FROM log, staff WHERE log.table_name='results' AND staff.staff_id=log.staff_id ORDER BY log.time DESC LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();


        if ($stmt->rowCount()) {
            $staff_name = $res[0]['staff_name'];
            $time = $res[0]['time'];

            return "Grade table checksum is $checksum. Last modified by $staff_name at $time.";
        }
        return "Grade table checksum is $checksum. Last modified info is not available. Contact Examcell Team.";
    } else {
        return "No grades have been added yet.";
    }
}

function ecell_check_status($str)
{
	$conn = ecell_get_conn();
	$sql = "SELECT status_id FROM status WHERE status_name = :status_name";

	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':status_name', $str);
	$stmt->execute();

	$res = $stmt->fetchAll()[0][0];
	return $res;
}

function ecell_get_status($str)
{

    $conn = ecell_get_conn();

    $sql = "SELECT status_id FROM status WHERE status_name = :status_name";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status_name', $str);
    $stmt->execute();

    $res = $stmt->fetchAll()[0][0];

    $sql = "INSERT INTO `status_value`(`status_value_id`, `status_id`, `log_id`) VALUES (NULL, $res, :log_id)";

    $ac_on = "Entered new status value for the status $str";
    $s_i = $_SESSION['staff_id'];
    $r = $_SESSION['rank'];
    $tn = 'status_value';

    $log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

    $sql = "INSERT INTO `status_value`(`status_value_id`, `status_id`, `log_id`) VALUES (NULL, :status_id, :log_id)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status_id', $res);
    $stmt->bindParam(':log_id', $log_id);
    $stmt->execute();

    $sql = "SELECT MAX(status_value_id) FROM status_value";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $res = $stmt->fetchAll()[0][0];

    return $res;

}

function ecell_get_val($table_name, $column_name, $value, $req)
{
    try {

        $conn = new PDO("mysql:host=localhost;dbname=offline", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT " . $req . " FROM " . $table_name . " WHERE " . $column_name . " = '" . $value . "'";

        $sth = $conn->prepare($sql);
        $sth->execute();
        $return_val = $sth->fetchAll(PDO::FETCH_COLUMN);

        if ($sth->rowCount() == 0) {
            return 0;
        } else {
            return $return_val[0];
        }

    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }
}

function ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn)
{

    try {

        $gid = $conn->prepare("SELECT MAX(log_id) as maxGroup FROM log");
        $gid->execute();
        $test = $gid->fetch(PDO::FETCH_ASSOC);
        $l_id = $test['maxGroup'];


        $pre_hash = ecell_get_val("log", "log_id", $l_id, "curr_hash");
        $today = date("Y-m-d H:i:s T D");


        $c = $s_i . $r . $today . $tn . $sql . $pre_hash;

        $new_hash = md5($c);


        $q = "INSERT INTO `log`(`rank`,`staff_id`,`prev_hash`,`executed_query`, `description`,`table_name`,`curr_hash`,`time`) VALUES 	(:irank,:istaff_id,:ph,:isql,:iact_on,:itable_name,:nh,:td)";
        $stmt = $conn->prepare($q);
        $stmt->bindParam(':irank', $r);
        $stmt->bindParam(':istaff_id', $s_i);
        $stmt->bindParam(':ph', $pre_hash);
        $stmt->bindParam(':isql', $sql);
        $stmt->bindParam(':iact_on', $ac_on);
        $stmt->bindParam(':itable_name', $tn);
        $stmt->bindParam(':nh', $new_hash);
        $stmt->bindParam(':td', $today);

        $stmt->execute();


        $gid = $conn->prepare("SELECT MAX(log_id) as maxGroup FROM log");
        $gid->execute();
        $test = $gid->fetch(PDO::FETCH_ASSOC);
        $log_id = $test['maxGroup'];


    } catch (PDOException $pe) {
        die("Error occurred:" . $pe->getMessage());
    }

    if ($tn == 'grade') {
        $hashstr = ecell_grade_hash();
    }

    return $log_id;

}

/*function session_handler() {

	if(!Sec_Session_Start()) {
		header("Location: logout.php");
	}

	if(!Login_Check($conn)) {
		header("Location: logout.php");
	}

	if (!isset($_POST)) {
		$_POST = &$HTTP_POST_VARS;
	}

}*/

function ecell_is_string_valid($str)
{

    $str = trim($str);
    $aValid = array(' ');

    if (ctype_alpha(str_replace($aValid, '', $str))) {
        return true;
    } else {
        return false;
    }

}

function ecell_is_alpha_num($str)
{

    $str = trim($str);
    if (ctype_alnum($str)) {
        return true;
    } else {
        return false;
    }
}

function ecell_get_staff_ranks()
{

    $conn = ecell_get_conn();
    $sql = "SELECT * FROM staff_designation";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $staff_designation_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rank = array();

    foreach ($staff_designation_arr as $des) {
        $rank[$des['designation']] = $des['rank'];
    }

    return $rank;
}

function ecell_redirect_if_below($designation, $to)
{
    $all_ranks = ecell_get_staff_ranks();
    if ($_SESSION['rank'] < $all_ranks[$designation]) {
        header("Location: $to");
    }
}

$all_ranks = ecell_get_staff_ranks();
header('Cache-Control: max-age=100');

	function ecell_grade_id_to_name($conn, $grade_id) {
		$stmt = $conn->prepare("SELECT grade FROM grades WHERE grade_id = :grade_id");
		$stmt->bindParam(':grade_id', $grade_id);
		$stmt->execute();
		$grade_name = $stmt->fetchAll()[0]['grade'];

		return $grade_name;
	}

	function ecell_grade_id_to_credit($conn, $grade_id) {
		$stmt = $conn->prepare("SELECT credit FROM grades WHERE grade_id = :grade_id");
		$stmt->bindParam(':grade_id', $grade_id);
		$stmt->execute();
		$grade_credit = $stmt->fetchAll()[0]['credit'];
		
		return $grade_credit;
	}

	function ecell_student_details($conn, $enrollment_no) {
		$stmt = $conn->prepare("SELECT * FROM student WHERE enrollment_no = :enrollment_no");
        $stmt->bindParam(':enrollment_no', $enrollment_no);
        $stmt->execute();
        $student_details = $stmt->fetchAll();

		$student_id = $student_details[0]['student_id'];
        $program_id = $student_details[0]['program_id'];
        $year_of_joining = $student_details[0]['year'];
        $sem_code_of_joining = $student_details[0]['sem_code'];

		$DOB = $student_details[0]['dob'];
		$DOB = date('d/m/Y', strtotime($DOB));

		$DOA = $student_details[0]['date_of_admission'];
		$admission_date = date('Y-m-d', strtotime($DOA));

		if ($DOA == '0000-00-00 00:00:00'){
			$admission_date = 'NA';
		}

		$first_name = $student_details[0]['first_name'];
		$middle_name = $student_details[0]['middle_name'];
		$last_name = $student_details[0]['last_name'];

		if (!empty($middle_name)) {
			$student_name = $first_name.' '.$middle_name.' '.$last_name;
		} else {
			$student_name = $first_name.' '.$last_name;
		}

		$father_first_name = $student_details[0]['father_first_name'];
		$father_last_name = $student_details[0]['father_last_name'];
		$father_name = $father_first_name.' '.$father_last_name;

		$student_array = array(
				'student_id' => $student_id,
				'program_id' => $program_id,
				'year_of_joining' => $year_of_joining,
				'sem_code_of_joining' => $sem_code_of_joining,
				'dob' => $DOB,
				'admission_date' => $admission_date,
				'student_name' => $student_name,
				'father_name' => $father_name
			);

		return $student_array;
	}

	function ecell_program_details($conn, $program_id) {
		$stmt = $conn->prepare("SELECT * FROM program WHERE program_id = :program_id");
		$stmt->bindParam(':program_id', $program_id);
		$stmt->execute();
		$program_details = $stmt->fetchAll();
		
		$program_code = $program_details[0]['program_code'];
		$program_name = $program_details[0]['program_name'];

		$program_duration = $program_details[0]['program_duration'];
	    $program_duration = preg_replace('/\D/', '', $program_duration);

		$program_array = array(
				'program_code' => $program_code,
				'program_name' => $program_name,
				'program_duration' => $program_duration
			);

		return $program_array;
	}

	function ecell_branch_change_roll_no($conn, $old_enrollment_no) {
		$stmt = $conn->prepare("SELECT * FROM old_branch WHERE roll_no = :enrollment_no");
        $stmt->bindParam(':enrollment_no', $old_enrollment_no);
        $stmt->execute();
        $branch_change = $stmt->fetchAll();

        if (count($branch_change)) {
        	$flag = true;

            $student_id = $branch_change[0]['student_id'];
            
            $stmt = $conn->prepare("SELECT enrollment_no FROM student WHERE student_id = :student_id");
            $stmt->bindParam(':student_id', $student_id);
            $stmt->execute();
            $new_enrollment_no = $stmt->fetchAll()[0]['enrollment_no'];
        } else {
        	$new_enrollment_no = 'NA';
        }

        return $new_enrollment_no;
	}

	function ecell_student_photo($conn, $enrollment_no) {
		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$stmt = $conn->prepare("SELECT media, mime FROM student_documents WHERE student_id = :student_id AND category = 'photo'");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $docs = $stmt->fetchAll();
        $image = $docs[0]['media'];
        $mime = $docs[0]['mime'];

        $photo_array = array(
        		'image' => $image,
        		'mime' => $mime
        	);

        return $photo_array;
	}

	function ecell_sem_cancel($conn, $enrollment_no, $year, $sem_code) {
		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$stmt = $conn->prepare("SELECT * FROM sem_cancel WHERE student_id = :student_id AND year = :year AND sem_code = :sem_code");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':sem_code', $sem_code);
        $stmt->execute();
        $sem_cancel = $stmt->fetchAll();

        if (count($sem_cancel)) {
            return true;
        } else {
        	return false;
        }
	}

	function ecell_sem_code_description($conn, $sem_code) {
		$stmt = $conn->prepare("SELECT title FROM sem_code_description WHERE sem_code_id = :sem_code");
        $stmt->bindParam(':sem_code', $sem_code);
        $stmt->execute();
        $sem_code_name = $stmt->fetchAll()[0]['title'];

        return $sem_code_name;
	}

	function ecell_course_details($conn, $course_id) {
		$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        $course_details = $stmt->fetchAll();

        $course_code = $course_details[0]['course_code'];
        $course_name = $course_details[0]['course_name'];
        $course_theory_credits = $course_details[0]['theory_credit'];
        $course_lab_credits = $course_details[0]['lab_credit'];

        $course_array = array(
        		'course_code' => $course_code,
        		'course_name' => $course_name,
        		'course_theory_credits' => $course_theory_credits,
        		'course_lab_credits' => $course_lab_credits
        	);

        return $course_array;
	}

	function ecell_sem_details($conn, $sem_id) {
		$stmt = $conn->prepare("SELECT * FROM course_structure WHERE sem_id = :sem_id");
        $stmt->bindParam(':sem_id', $sem_id);
        $stmt->execute();
        $sem_details = $stmt->fetchAll();

        $sem_title = $sem_details[0]['sem_title'];
        $program_id = $sem_details[0]['program_id'];
        $year_of_joining = $sem_details[0]['year_of_joining'];
        $sem_code_of_joining = $sem_details[0]['sem_code_of_joining'];
		$year = $sem_details[0]['sem_id_year'];
		$sem_code = $sem_details[0]['sem_id_sem_code'];

		$sem_array = array(
				'sem_title' => $sem_title,
		        'program_id' => $program_id,
		        'year_of_joining' => $year_of_joining,
		        'sem_code_of_joining' => $sem_code_of_joining,
				'year' => $year,
				'sem_code' => $sem_code
			);

		return $sem_array;
	}

	function ecell_sem_id_list($conn, $enrollment_no) {
		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

        $stmt = $conn->prepare("SELECT DISTINCT course_structure.sem_id FROM results, course_structure WHERE results.sem_id = course_structure.sem_id AND results.student_id = :student_id ORDER BY course_structure.sem_title");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $sem_id_list = $stmt->fetchAll();

        foreach ($sem_id_list as $sem_list) {
			$sem_id = $sem_list['sem_id'];

			$sem_details = ecell_sem_details($conn, $sem_id);

			$year = $sem_details['year'];
			$sem_code = $sem_details['sem_code'];

            if (!ecell_sem_cancel($conn, $enrollment_no, $year, $sem_code)) {
            	$sem_id_array[] = $sem_id;
            }
        }

        return $sem_id_array;
	}

	function ecell_has_back_paper($conn, $enrollment_no, $sem_id) {
		$flag = false;

		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$stmt = $conn->prepare("SELECT * FROM results, (SELECT course_id, MAX(`timestamp`) AS ts FROM results WHERE student_id = :student_id_1 AND sem_id = :sem_id_1 AND exam_type = 'END' AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'on') GROUP BY course_id) max_ts WHERE results.course_id = max_ts.course_id AND results.timestamp = max_ts.ts AND student_id = :student_id_2 AND sem_id = :sem_id_2 AND exam_type = 'END'");
		$stmt->bindParam(':student_id_1', $student_id);
		$stmt->bindParam(':sem_id_1', $sem_id);
		$stmt->bindParam(':student_id_2', $student_id);
		$stmt->bindParam(':sem_id_2', $sem_id);
		$stmt->execute();
		$results = $stmt->fetchAll();

		foreach ($results as $res) {
			$course_id = $res['course_id'];
			$theory_grade_id = $res['theory_grade'];
			$lab_grade_id = $res['lab_grade'];

			$theory_grade_name = ecell_grade_id_to_name($conn, $theory_grade_id);
			$lab_grade_name = ecell_grade_id_to_name($conn, $lab_grade_id);				
			
			if ($theory_grade_name == 'F' || $theory_grade_name == 'F(Ab)' || $lab_grade_name == 'F' || $lab_grade_name == 'F(Ab)') {
				$flag = true;
				break;
			}
		}

		return $flag;
	}

	function ecell_student_backs_in_sem($conn, $enrollment_no, $sem_id) {
		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$stmt = $conn->prepare("SELECT COUNT(student_id) FROM results WHERE student_id = :student_id AND sem_id = :sem_id AND exam_type = 'BACK'");
		$stmt->bindParam(':student_id', $student_id);
		$stmt->bindParam(':sem_id', $sem_id);
		$stmt->execute();
		$no_of_backs = $stmt->fetchAll()[0]['COUNT(student_id)'];

		return $no_of_backs;
	}

	function ecell_student_total_backs($conn, $enrollment_no) {
		$total_backs = 0;

		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$sem_id_list = ecell_sem_id_list($conn, $enrollment_no);

		foreach ($sem_id_list as $sem_id) {
			$backs_in_sem = ecell_student_backs_in_sem($sonn, $enrollment_no, $sem_id);

			$total_backs += $backs_in_sem;
		}

		return $total_backs;
	}

	function ecell_total_sem_credits($conn, $enrollment_no, $sem_id) {
		$total_sem_credits = 0;

		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$stmt = $conn->prepare("SELECT * FROM results, (SELECT course_id, MAX(`timestamp`) AS ts FROM results WHERE student_id = :student_id_1 AND sem_id = :sem_id_1 GROUP BY course_id) max_ts WHERE results.course_id = max_ts.course_id AND results.timestamp = max_ts.ts AND student_id = :student_id_2 AND sem_id = :sem_id_2");
		$stmt->bindParam(':student_id_1', $student_id);
		$stmt->bindParam(':sem_id_1', $sem_id);
		$stmt->bindParam(':student_id_2', $student_id);
		$stmt->bindParam(':sem_id_2', $sem_id);
		$stmt->execute();
		$results = $stmt->fetchAll();

		foreach ($results as $res) {
			$course_id = $res['course_id'];

			$course_details = ecell_course_details($conn, $course_id);

			$course_theory_credits = $course_details['course_theory_credits'];
			$course_lab_credits = $course_details['course_lab_credits'];

			$total_sem_credits += ($course_theory_credits + $course_lab_credits);
		}

		return $total_sem_credits;
	}

	function ecell_sgpi($conn, $enrollment_no, $sem_id) {
		$sgpi = 0;

		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$stmt = $conn->prepare("SELECT * FROM results, (SELECT course_id, MAX(`timestamp`) AS ts FROM results WHERE student_id = :student_id_1 AND sem_id = :sem_id_1 GROUP BY course_id) max_ts WHERE results.course_id = max_ts.course_id AND results.timestamp = max_ts.ts AND student_id = :student_id_2 AND sem_id = :sem_id_2");
		$stmt->bindParam(':student_id_1', $student_id);
		$stmt->bindParam(':sem_id_1', $sem_id);
		$stmt->bindParam(':student_id_2', $student_id);
		$stmt->bindParam(':sem_id_2', $sem_id);
		$stmt->execute();
		$results = $stmt->fetchAll();

		foreach ($results as $res) {
			$course_id = $res['course_id'];
			$theory_grade_id = $res['theory_grade'];
			$lab_grade_id = $res['lab_grade'];

			$theory_grade_credits = ecell_grade_id_to_credit($conn, $theory_grade_id); 
			$lab_grade_credits = ecell_grade_id_to_credit($conn, $lab_grade_id);

			$course_details = ecell_course_details($conn, $course_id);

			$course_theory_credits = $course_details['course_theory_credits'];
			$course_lab_credits = $course_details['course_lab_credits'];

			if ($course_theory_credits > 0 && $theory_grade_credits >= 0) {
				$sgpi += ($theory_grade_credits * $course_theory_credits);
			} 
			
			if ($course_lab_credits > 0 && $lab_grade_credits >= 0) {
				$sgpi += ($lab_grade_credits * $course_lab_credits);
			}
		}

		$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);

		$sgpi /= $total_sem_credits;
        $sgpi = number_format((float)$sgpi, 2, '.', '');

		return $sgpi;
	}

	function ecell_total_credits_earned($conn, $enrollment_no) {
		$flag = 0;
		$total_credits_earned = 0;

		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$sem_id_list = ecell_sem_id_list($conn, $enrollment_no);

		foreach ($sem_id_list as $sem_id) {
			$stmt = $conn->prepare("SELECT * FROM results, (SELECT course_id, MAX(`timestamp`) AS ts FROM results WHERE student_id = :student_id_1 AND sem_id = :sem_id_1 GROUP BY course_id) max_ts WHERE results.course_id = max_ts.course_id AND results.timestamp = max_ts.ts AND student_id = :student_id_2 AND sem_id = :sem_id_2");
			$stmt->bindParam(':student_id_1', $student_id);
			$stmt->bindParam(':sem_id_1', $sem_id);
			$stmt->bindParam(':student_id_2', $student_id);
			$stmt->bindParam(':sem_id_2', $sem_id);
			$stmt->execute();
			$results = $stmt->fetchAll();

			foreach ($results as $res) {
				$theory_grade_id = $res['theory_grade'];
				$lab_grade_id = $res['lab_grade'];

				$theory_grade_name = ecell_grade_id_to_name($conn, $theory_grade_id); 
				$lab_grade_name = ecell_grade_id_to_name($conn, $lab_grade_id);

				if ($theory_grade_name == 'F' || $theory_grade_name == 'F(Ab)' || $lab_grade_name == 'F' || $lab_grade_name == 'F(Ab)') {
					$flag = 1;
					break;
				}
			}

			if ($flag == 1) {
				break;
			}

			$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);

			$total_credits_earned += $total_sem_credits;
		}

		return $total_credits_earned;
	}

	function ecell_total_credits_appeared($conn, $enrollment_no) {
		$total_credits_appeared = 0;

		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$stmt = $conn->prepare("SELECT DISTINCT courses.course_id, courses.theory_credit, courses.lab_credit FROM results, courses WHERE results.course_id = courses.course_id AND results.student_id = :student_id");
		$stmt->bindParam(':student_id', $student_id);
		$stmt->execute();
		$course_credits = $stmt->fetchAll();

		foreach ($course_credits as $crd) {
			$total_credits_appeared += ($crd['theory_credit'] + $crd['lab_credit']); 
		}

		return $total_credits_appeared;
	}

	function ecell_cgpi($conn, $enrollment_no) {
		$total_sgpi = 0;

		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$sem_id_list = ecell_sem_id_list($conn, $enrollment_no);

		foreach ($sem_id_list as $sem_id) {
			$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);
			$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

			$total_sgpi += ($total_sem_credits * $sgpi);
		}

		$total_program_credits = ecell_total_credits_earned($conn, $enrollment_no);

		$cgpi = $total_sgpi / $total_program_credits;
		$cgpi = number_format((float)$cgpi, 2, '.', '');

		return $cgpi;
	}

	function ecell_semester_results($conn, $enrollment_no, $year, $sem_code) {
		$sem_code_name = ecell_sem_code_description($conn, $sem_code);
		$academic_session = $sem_code_name.' '.$year;

		$branch_change_roll_no = ecell_branch_change_roll_no($conn, $enrollment_no);

		if ($branch_change_roll_no != 'NA') {
			$enrollment_no = $branch_change_roll_no;
		}

		$student_details = ecell_student_details($conn, $enrollment_no);
		
		$student_id = $student_details['student_id'];
		$program_id = $student_details['program_id'];
		$year_of_joining = $student_details['year_of_joining'];
		$sem_code_of_joining = $student_details['sem_code_of_joining'];
		$student_name = $student_details['student_name'];

		$program_details = ecell_program_details($conn, $program_id);
		$program_name = $program_details['program_name'];

		$student_photo = ecell_student_photo($conn, $enrollment_no);
		$image = $student_photo['image'];
		$mime = $student_photo['mime'];

		$stmt = $conn->prepare("SELECT * FROM results INNER JOIN course_structure ON results.sem_id = course_structure.sem_id INNER JOIN (SELECT course_id, MAX(`timestamp`) AS ts FROM results INNER JOIN course_structure ON results.sem_id = course_structure.sem_id WHERE results.student_id = :student_id_1 AND course_structure.sem_id_year = :year_1 AND course_structure.sem_id_sem_code = :sem_code_1 AND results.exam_type = 'END' GROUP BY course_id) max_ts ON (results.course_id = max_ts.course_id AND results.timestamp = max_ts.ts AND results.sem_id = course_structure.sem_id) WHERE results.student_id = :student_id_2 AND course_structure.sem_id_year = :year_2 AND course_structure.sem_id_sem_code = :sem_code_2 AND results.exam_type = 'END'");
        $stmt->bindParam(':student_id_1', $student_id);
        $stmt->bindParam(':year_1', $year);
        $stmt->bindParam(':sem_code_1', $sem_code);
        $stmt->bindParam(':student_id_2', $student_id);
        $stmt->bindParam(':year_2', $year);
        $stmt->bindParam(':sem_code_2', $sem_code);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $sem_id = $results[0]['sem_id'];
        $sem_title = $results[0]['sem_title'];

        $course_list_array = array();

        foreach ($results as $res) {
        	$course_id = $res['course_id'];
            $theory_grade_id = $res['theory_grade'];
            $lab_grade_id = $res['lab_grade'];

            $theory_grade_name = ecell_grade_id_to_name($conn, $theory_grade_id);
            $lab_grade_name = ecell_grade_id_to_name($conn, $lab_grade_id);

            $course_details = ecell_course_details($conn, $course_id);

            $course_code = $course_details['course_code'];
         	$course_name = $course_details['course_name'];
            $course_theory_credits = $course_details['course_theory_credits'];
            $course_lab_credits = $course_details['course_lab_credits'];

            $course_list_array[] = array(
            		'course_code' => $course_code,
            		'course_name' => $course_name,
            		'course_theory_credits' => $course_theory_credits,
            		'course_lab_credits' => $course_lab_credits,
            		'theory_grade' => $theory_grade_name,
            		'lab_grade' => $lab_grade_name
            	);
        }

        $total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);

        if (ecell_has_back_paper($conn, $enrollment_no, $sem_id)) {
        	$sgpi = 'XXXX';
        } else {
        	$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);
            
            if ($sgpi == 0.00) {
                $sgpi = 'XXXX';
            }
        }

        $stmt = $conn->prepare("SELECT MAX(date_of_declaration) FROM results WHERE student_id = :student_id AND sem_id = :sem_id AND exam_type = 'END'");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':sem_id', $sem_id);
        $stmt->execute();
        $res_dec_date = $stmt->fetchAll()[0]['MAX(date_of_declaration)'];

        if ($res_dec_date == '0000-00-00') {
            $res_dec_date = 'NA';
        } else {
            $res_dec_date = date('d/m/Y', strtotime($res_dec_date));
        }

        date_default_timezone_set('Asia/Kolkata');
        $res_prep_date = date('d/m/Y');

        if (ecell_sem_cancel($conn, $enrollment_no, $year, $sem_code)) {
        	$result_status = 'Result: Semester Cancelled';
        } else if (ecell_has_back_paper($conn, $enrollment_no, $sem_id)) {
        	$result_status = 'Result: Withheld';
        } else {
        	$result_status = 'Result: Passed and Promoted to Next Semester';
        }

        $semester_results_array = array(
        		'academic_session' => $academic_session,
        		'enrollment_no' => $enrollment_no,
        		'student_name' => $student_name,
        		'program_name' => $program_name,
        		'semester' => $sem_title,
        		'student_photo_image' => $image,
        		'student_photo_mime' => $mime,
        		'course_list' => $course_list_array,
        		'total_sem_credits' => $total_sem_credits,
        		'sgpi' => $sgpi,
        		'result_declaration_date' => $res_dec_date,
        		'result_preparation_date' => $res_prep_date,
        		'result_status' => $result_status
        	);

        return $semester_results_array;
	}

	function ecell_generate_grade_card($conn, $mpdf, $enrollment_no, $year, $sem_code) {
		$semester_results = ecell_semester_results($conn, $enrollment_no, $year, $sem_code);

		$academic_session = $semester_results['academic_session'];

		$student_id = $semester_results['student_id'];
        $student_name = $semester_results['student_name'];
        $program_name = $semester_results['program_name'];
        $sem_title = $semester_results['semester'];
        $image = $semester_results['student_photo_image'];
        $mime = $semester_results['student_photo_mime'];
        $course_list = $semester_results['course_list'];
        $total_sem_credits = $semester_results['total_sem_credits'];
        $sgpi = $semester_results['sgpi'];
		$res_dec_date = $semester_results['result_declaration_date'];
		$res_prep_date = $semester_results['result_preparation_date'];
        $result_status = $semester_results['result_status'];

        $mpdf->AddPage();

        $html_front = "
            <html>         
                <body>
                    <p style='font-size:17;font-family:Times New Roman;text-align:center;line-height:2px;padding-top:40mm'><b>Academic Session : $academic_session</b></p>
                    <p style='font-size:17;font-family:Times New Roman;text-align:center;line-height:2px'><b>$program_name</b></p>
		            <div>
		                <div style='float:right;width:20%'>
		                    <img src='data:$mime;base64,$image' width='25mm' style='padding-top:18mm'/>
		                </div>
		                <div style='float:left;width:70%'>
		                    <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-top:20mm;padding-left:5mm'><b>Enrollment No. :</b> $enrollment_no</p>
		                    <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'><b>Name :</b> $student_name</p>
		                    <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'><b>Program :</b> $program_name</p>
        ";

        if (preg_match('/Semester/', $sem_title)) {
            $sem_title = preg_replace('/\D/', '', $sem_title);

            $html_front .= "
                <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-bottom:20mm;padding-left:5mm'><b>Semester :</b> $sem_title</p>
            ";
        } else {
            $html_front .= "
                <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-bottom:20mm;padding-left:5mm'><b>$sem_title</b></p>
            ";
        }

        $html_front .= "
                </div>
            </div>
            <table align='center' style='border-collapse:collapse'>
                <tr>
                    <th colspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Course</b></th>
                    <th colspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Credits</b></th>
                    <th colspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Grade</b></th>
                </tr>
                <tr>
                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Code</b></th>
                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:70mm'><b>Name</b></th>
                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Theory</b></th>
                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Lab</b></th>
                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Theory</b></th>
                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Lab</b></th>
                </tr>
        ";

        foreach ($course_list as $crs) {
            $course_code = $crs['course_code'];
            $course_name = $crs['course_name'];
            $course_theory_credits = $crs['course_theory_credits'];
            $course_lab_credits = $crs['course_lab_credits'];
            $theory_grade_name = $crs['theory_grade'];
            $lab_grade_name = $crs['lab_grade'];

            $html_front .= "
                <tr>
                    <td style='font-size:13;font-family:Courier;text-align:left;border:1px solid black;height:20px'>$course_code</td>
                    <td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px'>$course_name</td>
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$course_theory_credits</td>
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$course_lab_credits</td>
            ";

            if ($theory_grade_name == 'F' || $theory_grade_name == 'F(Ab)') {
                $html_front .= "
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;background-color:#ff0404;height:20px'>$theory_grade_name</td>
                ";
            } else {
                $html_front .= "
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$theory_grade_name</td>
                ";
            }

            if ($lab_grade_name == 'F' || $lab_grade_name == 'F(Ab)') {
                $html_front .= "
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;background-color:#ff0404;height:20px'>$lab_grade_name</td>
                ";
            } else {
                $html_front .= "
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$lab_grade_name</td>
                ";
            }

            $html_front .= "</tr>";
        }

        $html_front .= "
		                <tr>
		                    <td colspan='3' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Total Credits : $total_sem_credits</b></td>
		                    <td colspan='3' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>SGPI = $sgpi</b></td>
		                </tr>
		            </table>
		            <p style='font-size:13;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'>Date of Result Declaration : $res_dec_date</p>
		            <p style='font-size:13;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'>Prepared on : $res_prep_date</p>
                    <p style='font-size:13;font-family:Times New Roman;text-align:center;padding-top:30mm'><b>$result_status</b></p>
                </body>
            </html>
        ";

        $mpdf->WriteHTML($html_front);

        // Back side of page

        $mpdf->AddPage();

        $html_back = "
            <html>
                <body>
                    <div style='border:1px solid black;padding:20px'>
                        <p style='font-size:13;font-family:Times New Roman;text-align:center'><b><u>Award System and Other Relevant Information</u></b></p>
                        <ul style='font-size:13;font-family:Times New Roman;text-align:left;line-height:25px'>
                            <li>Medium of Instruction: English.</li>
                            <li>Program consist of Eight Semesters.</li>
                            <li>One Theory Credit denotes 1 hour of lecture per week and one Practical Credit denotes 1 and 1/2 hours work per week.</li>
                            <li>Duration of each Semester is a minimum of 18 instructional weeks.</li>
                            <li>Senate of the Institute is highest Academic Body.</li>
                            <li>CGPI 7 and above will be considered as first division/class.</li>
                            <li>The institute follows relative grading pattern.</li>
                            <li>No equivalent PERCENTAGE of marks is awarded as institute follows a relative grading system.</li>
                            <li>Student earning CGPI of 8.5 or above are awarded the Degree of B.Tech with Honours.</li>
                            <li>Grade Description:</li>
                        </ul>
                        <table align='center'>
                            <tr>
                                <th style='font-size:13;font-family:Times New Roman;text-align:left;height:20px;width:20mm'><b>Grade</b></th>
                                <th style='font-size:13;font-family:Times New Roman;text-align:left;height:20px;width:25mm'><b>Meaning</b></th>
                                <th style='font-size:13;font-family:Times New Roman;text-align:center;height:20px;width:40mm'><b>Grade Value</b></th>
                            </tr>
        ";

        $stmt = $conn->prepare("SELECT * FROM grades");
        $stmt->execute();
        $allowedGrades = $stmt->fetchAll();

        foreach ($allowedGrades as $value) {
            $grade_name = $value['grade'];
            $grade_meaning = $value['description'];
            $grade_credit = $value['credit'];

            if ($grade_credit > 0 || $grade_name == 'F') {
                $html_back .= "
                    <tr>
                        <td style='font-size:13;font-family:Times New Roman;text-align:left;height:20px'>$grade_name</td>
                        <td style='font-size:13;font-family:Times New Roman;text-align:left;height:20px'>$grade_meaning</td>
                        <td style='font-size:13;font-family:Times New Roman;text-align:center;height:20px'>$grade_credit</td>
                    </tr>
                ";
            }  
        }

        $html_back .= "
                        </table>
                    </div>
                </body>
            </html>
        ";

        $mpdf->WriteHTML($html_back);
	}

	function ecell_back_results($conn, $enrollment_no, $year, $month) {
		if ($month == '01') {
            $month_name = 'January';
        } else if ($month == '02') {
            $month_name = 'February';
        } else if ($month == '03') {
            $month_name = 'March';
        } else if ($month == '04') {
            $month_name = 'April';
        } else if ($month == '05') {
            $month_name = 'May';
        } else if ($month == '06') {
            $month_name = 'June';
        } else if ($month == '07') {
            $month_name = 'July';
        } else if ($month == '08') {
            $month_name = 'August';
        } else if ($month == '09') {
            $month_name = 'September';
        } else if ($month == '10') {
            $month_name = 'October';
        } else if ($month == '11') {
            $month_name = 'November';
        } else if ($month == '12') {
            $month_name = 'December';
        }

		$academic_session = $month_name.' '.$year;

		$branch_change_roll_no = ecell_branch_change_roll_no($conn, $enrollment_no);

		if ($branch_change_roll_no != 'NA') {
			$enrollment_no = $branch_change_roll_no;
		}

		$student_details = ecell_student_details($conn, $enrollment_no);
		
		$student_id = $student_details['student_id'];
		$program_id = $student_details['program_id'];
		$year_of_joining = $student_details['year_of_joining'];
		$sem_code_of_joining = $student_details['sem_code_of_joining'];
		$student_name = $student_details['student_name'];

		$program_details = ecell_program_details($conn, $program_id);
		$program_name = $program_details['program_name'];

		$student_photo = ecell_student_photo($conn, $enrollment_no);
		$image = $student_photo['image'];
		$mime = $student_photo['mime'];

		$date_of_exam = '%'.$year.'-'.$month.'-%';

		$stmt = $conn->prepare("SELECT * FROM results, (SELECT course_id, MAX(`timestamp`) AS ts FROM results WHERE student_id = :student_id_1 AND date_of_exam LIKE :date_of_exam_1 AND exam_type = 'BACK' GROUP BY course_id) max_ts WHERE results.course_id = max_ts.course_id AND results.timestamp = max_ts.ts AND student_id = :student_id_2 AND date_of_exam LIKE :date_of_exam_2 AND exam_type = 'BACK'");
        $stmt->bindParam(':student_id_1', $student_id);
        $stmt->bindParam(':date_of_exam_1', $date_of_exam);
        $stmt->bindParam(':student_id_2', $student_id);
        $stmt->bindParam(':date_of_exam_2', $date_of_exam);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $sem_id = $results[0]['sem_id'];

        $course_list_array = array();
        $flag = 0;

        foreach ($results as $res) {
        	$course_id = $res['course_id'];
            $theory_grade_id = $res['theory_grade'];
            $lab_grade_id = $res['lab_grade'];

            $theory_grade_name = ecell_grade_id_to_name($conn, $theory_grade_id);
            $lab_grade_name = ecell_grade_id_to_name($conn, $lab_grade_id);

            if ($theory_grade_name == 'F' || $theory_grade_name == 'F(Ab)' || $lab_grade_name == 'F' || $lab_grade_name == 'F(Ab)') {
            	$flag = 1;	
            }

            $course_details = ecell_course_details($conn, $course_id);

            $course_code = $course_details['course_code'];
         	$course_name = $course_details['course_name'];
            $course_theory_credits = $course_details['course_theory_credits'];
            $course_lab_credits = $course_details['course_lab_credits'];

            $course_list_array[] = array(
            		'course_code' => $course_code,
            		'course_name' => $course_name,
            		'course_theory_credits' => $course_theory_credits,
            		'course_lab_credits' => $course_lab_credits,
            		'theory_grade' => $theory_grade_name,
            		'lab_grade' => $lab_grade_name
            	);
        }

        $total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);

        if ($flag == 1) {
        	$sgpi = 'XXXX';
        } else {
        	$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

            if ($sgpi == 0.00) {
                $sgpi = 'XXXX';
            }
        }

        $stmt = $conn->prepare("SELECT MAX(date_of_declaration) FROM results WHERE student_id = :student_id AND date_of_exam LIKE :date_of_exam AND exam_type = 'BACK'");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':date_of_exam', $date_of_exam);
        $stmt->execute();
        $res_dec_date = $stmt->fetchAll()[0]['MAX(date_of_declaration)'];

        if ($res_dec_date == '0000-00-00') {
            $res_dec_date = 'NA';
        } else {
            $res_dec_date = date('d/m/Y', strtotime($res_dec_date));
        }

        date_default_timezone_set('Asia/Kolkata');
        $res_prep_date = date('d/m/Y');

        if (ecell_sem_cancel($conn, $enrollment_no, $year, $sem_code)) {
        	$result_status = 'Result: Semester Cancelled';
        } else if ($flag == 1) {
        	$result_status = 'Result: Withheld';
        } else {
        	$result_status = 'Result: Passed and Promoted to Next Semester';
        }

        $back_results_array = array(
        		'academic_session' => $academic_session,
        		'enrollment_no' => $enrollment_no,
        		'student_name' => $student_name,
        		'program_name' => $program_name,
        		'student_photo_image' => $image,
        		'student_photo_mime' => $mime,
        		'course_list' => $course_list_array,
        		'total_sem_credits' => $total_sem_credits,
        		'sgpi' => $sgpi,
        		'result_declaration_date' => $res_dec_date,
        		'result_preparation_date' => $res_prep_date,
        		'result_status' => $result_status
        	);

        return $back_results_array;
	}

	function ecell_generate_back_grade_card($conn, $mpdf, $enrollment_no, $year, $month) {
		$back_results = ecell_back_results($conn, $enrollment_no, $year, $month);

		$academic_session = $back_results['academic_session'];

		$student_id = $back_results['student_id'];
        $student_name = $back_results['student_name'];
        $program_name = $back_results['program_name'];
        $image = $back_results['student_photo_image'];
        $mime = $back_results['student_photo_mime'];
        $course_list = $back_results['course_list'];
        $total_sem_credits = $back_results['total_sem_credits'];
        $sgpi = $back_results['sgpi'];
		$res_dec_date = $back_results['result_declaration_date'];
		$res_prep_date = $back_results['result_preparation_date'];
        $result_status = $back_results['result_status'];

        $mpdf->AddPage();

        $html_front = "
            <html>         
                <body>
                    <p style='font-size:17;font-family:Times New Roman;text-align:center;line-height:2px;padding-top:40mm'><b>Academic Session : $academic_session</b></p>
                    <p style='font-size:17;font-family:Times New Roman;text-align:center;line-height:2px'><b>$program_name</b></p>
		            <div>
		                <div style='float:right;width:20%'>
		                    <img src='data:$mime;base64,$image' width='25mm' style='padding-top:18mm'/>
		                </div>
		                <div style='float:left;width:70%'>
		                    <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-top:20mm;padding-left:5mm'><b>Enrollment No. :</b> $enrollment_no</p>
		                    <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'><b>Name :</b> $student_name</p>
		                    <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'><b>Program :</b> $program_name</p>
		                    <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-bottom:20mm;padding-left:5mm'></p>
		                </div>
		            </div>
		            <table align='center' style='border-collapse:collapse'>
		                <tr>
		                    <th colspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Course</b></th>
		                    <th colspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Credits</b></th>
		                    <th colspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Grade</b></th>
		                </tr>
		                <tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Code</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:70mm'><b>Name</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Theory</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Lab</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Theory</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;width:20mm'><b>Lab</b></th>
		                </tr>
        ";

        foreach ($course_list as $crs) {
            $course_code = $crs['course_code'];
            $course_name = $crs['course_name'];
            $course_theory_credits = $crs['course_theory_credits'];
            $course_lab_credits = $crs['course_lab_credits'];
            $theory_grade_name = $crs['theory_grade'];
            $lab_grade_name = $crs['lab_grade'];

            $html_front .= "
                <tr>
                    <td style='font-size:13;font-family:Courier;text-align:left;border:1px solid black;height:20px'>$course_code</td>
                    <td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px'>$course_name</td>
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$course_theory_credits</td>
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$course_lab_credits</td>
            ";

            if ($theory_grade_name == 'F' || $theory_grade_name == 'F(Ab)') {
                $html_front .= "
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;background-color:#ff0404;height:20px'>$theory_grade_name</td>
                ";
            } else {
                $html_front .= "
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$theory_grade_name</td>
                ";
            }

            if ($lab_grade_name == 'F' || $lab_grade_name == 'F(Ab)') {
                $html_front .= "
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;background-color:#ff0404;height:20px'>$lab_grade_name</td>
                ";
            } else {
                $html_front .= "
                    <td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$lab_grade_name</td>
                ";
            }

            $html_front .= "</tr>";
        }

        $html_front .= "
		                <tr>
		                    <td colspan='3' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Total Credits : $total_sem_credits</b></td>
		                    <td colspan='3' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>SGPI = $sgpi</b></td>
		                </tr>
		            </table>
		            <p style='font-size:13;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'>Date of Result Declaration : $res_dec_date</p>
		            <p style='font-size:13;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'>Prepared on : $res_prep_date</p>
                    <p style='font-size:13;font-family:Times New Roman;text-align:center;padding-top:30mm'><b>$result_status</b></p>
                </body>
            </html>
        ";

        $mpdf->WriteHTML($html_front);

        // Back side of page

        $mpdf->AddPage();

        $html_back = "
            <html>
                <body>
                    <div style='border:1px solid black;padding:20px'>
                        <p style='font-size:13;font-family:Times New Roman;text-align:center'><b><u>Award System and Other Relevant Information</u></b></p>
                        <ul style='font-size:13;font-family:Times New Roman;text-align:left;line-height:25px'>
                            <li>Medium of Instruction: English.</li>
                            <li>Program consist of Eight Semesters.</li>
                            <li>One Theory Credit denotes 1 hour of lecture per week and one Practical Credit denotes 1 and 1/2 hours work per week.</li>
                            <li>Duration of each Semester is a minimum of 18 instructional weeks.</li>
                            <li>Senate of the Institute is highest Academic Body.</li>
                            <li>CGPI 7 and above will be considered as first division/class.</li>
                            <li>The institute follows relative grading pattern.</li>
                            <li>No equivalent PERCENTAGE of marks is awarded as institute follows a relative grading system.</li>
                            <li>Student earning CGPI of 8.5 or above are awarded the Degree of B.Tech with Honours.</li>
                            <li>Grade Description:</li>
                        </ul>
                        <table align='center'>
                            <tr>
                                <th style='font-size:13;font-family:Times New Roman;text-align:left;height:20px;width:20mm'><b>Grade</b></th>
                                <th style='font-size:13;font-family:Times New Roman;text-align:left;height:20px;width:25mm'><b>Meaning</b></th>
                                <th style='font-size:13;font-family:Times New Roman;text-align:center;height:20px;width:40mm'><b>Grade Value</b></th>
                            </tr>
        ";

        $stmt = $conn->prepare("SELECT * FROM grades");
        $stmt->execute();
        $allowedGrades = $stmt->fetchAll();

        foreach ($allowedGrades as $value) {
            $grade_name = $value['grade'];
            $grade_meaning = $value['description'];
            $grade_credit = $value['credit'];

            if ($grade_credit > 0 || $grade_name == 'F') {
                $html_back .= "
                    <tr>
                        <td style='font-size:13;font-family:Times New Roman;text-align:left;height:20px'>$grade_name</td>
                        <td style='font-size:13;font-family:Times New Roman;text-align:left;height:20px'>$grade_meaning</td>
                        <td style='font-size:13;font-family:Times New Roman;text-align:center;height:20px'>$grade_credit</td>
                    </tr>
                ";
            }  
        }

        $html_back .= "
                        </table>
                    </div>
                </body>
            </html>
        ";

        $mpdf->WriteHTML($html_back);
	}

	function ecell_transcript_results($conn, $enrollment_no) {
		$student_details = ecell_student_details($conn, $enrollment_no);

		$student_id = $student_details['student_id'];
		$program_id = $student_details['program_id'];
		$year_of_joining = $student_details['year_of_joining'];
		$sem_code_of_joining = $student_details['sem_code_of_joining'];
		$dob = $student_details['dob'];
		$admission_date = $student_details['admission_date'];
		$student_name = $student_details['student_name'];
		$father_name = $student_details['father_name'];

		$program_details = ecell_program_details($conn, $program_id);

		$program_name = $program_details['program_name'];
		$program_duration = $program_details['program_duration'];

		$end_date = date('Y-m-d', strtotime(date('Y-m-d', strtotime($admission_date)) . '+ '.$program_duration.' years'));
		$end_date = date('Y-m-d', strtotime(date('Y-m-d', strtotime($end_date)) . '- 1 month'));

		$time_start = strtotime($admission_date);
		$month_start = date('F', $time_start);
		$year_start = date('Y', $time_start);

		$time_end = strtotime($end_date);
		$month_end = date('F', $time_end);
		$year_end = date('Y', $time_end);
		
		if ($admission_date == 'NA') {
			$program_period = 'NA';
		} else {	
			$program_period = $month_start.' '.$year_start.' - '.$month_end.' '.$year_end;
		}

		$admission_date = date('d/m/Y', strtotime($admission_date));

		$stmt = $conn->prepare("SELECT COUNT(sem_id) FROM student, course_structure WHERE student.program_id = course_structure.program_id AND student.year = course_structure.year_of_joining AND student.sem_code = course_structure.sem_code_of_joining AND student.student_id = :student_id");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $no_of_sem = $stmt->fetchAll()[0]['COUNT(sem_id)'];

		$student_info = array(
				'student_id' => $student_id,
				'enrollment_no' => $enrollment_no,
				'student_name' => $student_name,
				'dob' => $dob,
				'father_name' => $father_name,
				'program_name' => $program_name,
				'admission_date' => $admission_date,
				'program_duration' => $program_duration,
				'program_period' => $program_period,
				'no_of_sem' => $no_of_sem
			);

		$sem_id_list = ecell_sem_id_list($conn, $enrollment_no);

		$sem_info = array();

		foreach ($sem_id_list as $sem_id) {
			$sem_details = ecell_sem_details($conn, $sem_id);

			$sem_title = $sem_details['sem_title'];
			$year = $sem_details['year'];
			$sem_code = $sem_details['sem_code'];

			$academic_session = $sem.' '.$year;

			$course_info = array();

			$stmt = $conn->prepare("SELECT * FROM results, (SELECT course_id, MAX(`timestamp`) AS ts FROM results WHERE student_id = :student_id_1 AND sem_id = :sem_id_1 GROUP BY course_id) max_ts WHERE results.course_id = max_ts.course_id AND results.timestamp = max_ts.ts AND student_id = :student_id_2 AND sem_id = :sem_id_2");
			$stmt->bindParam(':student_id_1', $student_id);
			$stmt->bindParam(':sem_id_1', $sem_id);
			$stmt->bindParam(':student_id_2', $student_id);
			$stmt->bindParam(':sem_id_2', $sem_id);
			$stmt->execute();
			$results = $stmt->fetchAll();

			foreach ($results as $res) {
				$course_id = $res['course_id'];

				$theory_grade_id = $res['theory_grade'];
				$lab_grade_id = $res['lab_grade'];

				$theory_grade_name = ecell_grade_id_to_name($conn, $theory_grade_id);
				$lab_grade_name = ecell_grade_id_to_name($conn, $lab_grade_id);

				$course_details = ecell_course_details($conn, $course_id);

				$course_code = $course_details['course_code'];
				$course_theory_credits = $course_details['course_theory_credits'];
				$course_lab_credits = $course_details['course_lab_credits'];

				$course_info[] = array(
						'course_id' => $course_id,
						'course_code' => $course_code,
						'course_theory_credits' => $course_theory_credits,
						'course_lab_credits' => $course_lab_credits,
						'theory_grade' => $theory_grade_name,
						'lab_grade' => $lab_grade_name
					);
			}

			$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);
			$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

            if ($sgpi == 0.00) {
                $sgpi = 'XXXX';
            }

			$sem_info[] = array(
					'sem_id' => $sem_id,
					'sem_title' => $sem_title,
					'academic_session' => $academic_session,
					'course_info' => $course_info,
					'total_sem_credits' => $total_sem_credits,
					'sgpi' => $sgpi
				);
		}

		$total_credits_appeared = ecell_total_credits_appeared($conn, $enrollment_no);
		$total_credits_earned = ecell_total_credits_earned($conn, $enrollment_no);
		$cgpi = ecell_cgpi($conn, $enrollment_no);

		$transcript_info = array(
				'student_info' => $student_info,
				'sem_info' => $sem_info,
				'total_credits_appeared' => $total_credits_appeared,
				'total_credits_earned' => $total_credits_earned,
				'cgpi' => $cgpi
			);

		return $transcript_info;
	}

	function ecell_generate_transcript($conn, $mpdf, $enrollment_no) {
		$mpdf->AddPage();

		$transcript_info = ecell_transcript_results($conn, $enrollment_no);

		$student_info = $transcript_info['student_info'];

		$student_id = $student_info['student_id'];
		$enrollment_no = $student_info['enrollment_no'];
		$student_name = $student_info['student_name'];
		$dob = $student_info['dob'];
		$father_name = $student_info['father_name'];
		$program_name = $student_info['program_name'];
		$admission_date = $student_info['admission_date'];
		$program_period = $student_info['program_period'];
		$program_duration = $student_info['program_duration'];
		$no_of_sem = $student_info['no_of_sem'];

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
                            <td style='font-size:11;font-family:Times New Roman;text-align:left;width:80mm;height:12px'>Enrollment No. : <b>$enrollment_no</b></td>
                            <td style='font-size:11;font-family:Times New Roman;text-align:left;width:100mm;height:12px;border-left:1px solid black'>Program : <b>$program_name</b></td>
                       	</tr>
                       	<tr>
                            <td style='font-size:11;font-family:Times New Roman;text-align:left;width:80mm;height:12px'>Student's Name : <b>$student_name</b></td>
                            <td style='font-size:11;font-family:Times New Roman;text-align:left;width:100mm;height:12px;border-left:1px solid black'>Date of Admission : <b>$admission_date</b></td>
                        </tr>
                        <tr>
                            <td style='font-size:11;font-family:Times New Roman;text-align:left;width:80mm;height:12px'>Date of Birth : <b>$dob</b></td>
                            <td style='font-size:11;font-family:Times New Roman;text-align:left;width:100mm;height:12px;border-left:1px solid black'>Period of Program : <b>$program_period</b></td>
                       	</tr>
                       	<tr>
                            <td style='font-size:11;font-family:Times New Roman;text-align:left;width:80mm;height:12px'>Father's Name : <b>$father_name</b></td>
                            <td style='font-size:11;font-family:Times New Roman;text-align:left;width:100mm;height:12px;border-left:1px solid black'>Duration of Program : <b>$program_duration years ($no_of_sem semesters)</b></td>
                       	</tr>
                   	</table>
					<div style='border:1px solid black;padding:8px 3px 8px 3px'>
						<table align='left'>
		";

		$sem_info = $transcript_info['sem_info'];

		$ctr = 0;
		$flag = 0;
		$course_list = array();

		foreach ($sem_info as $sem) {
			$ctr++;

			$sem_id = $sem['sem_id'];
			$sem_title = $sem['sem_title'];
			$academic_session = $sem['academic_session'];

	        $course_info = $sem['course_info'];

			if ($ctr == $no_of_sem && $no_of_sem % 3 == 1) {
				foreach ($course_info as $crs) {
					$theory_grade = $crs['theory_grade'];
					$lab_grade = $crs['lab_grade'];

					if ($theory_grade == 'F' || $theory_grade == 'F(Ab)' || $lab_grade == 'F' || $lab_grade == 'F(Ab)') {
						$flag = 1;
						break;
					}
				}

				if ($flag == 1) {
					break;
				}
				
				$html_front .= "
					<tr>
	                    <td colspan='3' style='vertical-align:top'>
	                    	<table style='border-collapse:collapse'>
	                    		<tr>
	                    			<td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;width:183mm;height:20px;border:1px solid black'><b>$sem_title ($academic_session)</b></td>
	                            </tr>
	                            <tr>
	                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:81mm;height:20px;border:1px solid black'><b>Course Code</b></td>
	                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:51mm;height:20px;border:1px solid black'><b>Grade</b></td>
	                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:51mm;height:20px;border:1px solid black'><b>Credits</b></td>
								</tr>
				";

				foreach ($course_info as $crs) {
					$course_id = $crs['course_id'];
					$course_list[] = $course_id;

					$course_code = $crs['course_code'];
					$course_theory_credits = $crs['course_theory_credits'];
					$course_lab_credits = $crs['course_lab_credits'];
					$theory_grade = $crs['theory_grade'];
					$lab_grade = $crs['lab_grade'];

					$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = 'BACK' ORDER BY `timestamp` DESC");
					$stmt->bindParam(':student_id', $student_id);
					$stmt->bindParam(':course_id', $course_id);
					$stmt->execute();
					$back = $stmt->fetchAll();

					if (count($back)) {
						$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = 'END' ORDER BY `timestamp` DESC");
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

					$html_front .= "
						<tr>
                            <td style='font-size:9;font-family:Courier;text-align:center;height:60px;border:1px solid black'><b>$course_code</b></td>
                            <td style='font-size:9;font-family:Courier;text-align:center;height:60px;border:1px solid black'><b>$theory_grade</b></td>
                            <td style='font-size:9;font-family:Courier;text-align:center;height:60px;border:1px solid black'><b>$course_theory_credits</b></td>
						</tr>
					";
				}

				$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);
				$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

				$html_front .= "
								<tr>
		                            <td style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Total Credits : $total_sem_credits</b></td>
		                            <td colspan='2' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>SGPI = $sgpi</b></td>
		                        </tr>
		                        <tr>
		                            <td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Result: Passed and Course Completed Successfully</b></td>
								</tr>
							</table>
						</td>		
					</tr>
				";
			} else if ($ctr == $no_of_sem && $no_of_sem % 3 == 2) {
				foreach ($course_info as $crs) {
					$theory_grade = $crs['theory_grade'];
					$lab_grade = $crs['lab_grade'];

					if ($theory_grade == 'F' || $theory_grade == 'F(Ab)' || $lab_grade == 'F' || $lab_grade == 'F(Ab)') {
						$flag = 1;
						break;
					}
				}

				if ($flag == 1) {
					break;
				}

				$html_front .= "
                    <td colspan='2' style='vertical-align:top'>
                    	<table style='border-collapse:collapse'>
                    		<tr>
                    			<td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;width:120mm;height:20px;border:1px solid black'><b>$sem_title ($academic_session)</b></td>
                            </tr>
                            <tr>
                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:50mm;height:20px;border:1px solid black'><b>Course Code</b></td>
                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:35mm;height:20px;border:1px solid black'><b>Grade</b></td>
                                <td style='font-size:10;font-family:Times New Roman;text-align:center;width:35mm;height:20px;border:1px solid black'><b>Credits</b></td>
							</tr>
				";
				
				foreach ($course_info as $crs) {
					$course_id = $crs['course_id'];
					$course_list[] = $course_id;

					$course_code = $crs['course_code'];
					$course_theory_credits = $crs['course_theory_credits'];
					$course_lab_credits = $crs['course_lab_credits'];
					$theory_grade = $crs['theory_grade'];
					$lab_grade = $crs['lab_grade'];

					$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = 'BACK' ORDER BY `timestamp` DESC");
					$stmt->bindParam(':student_id', $student_id);
					$stmt->bindParam(':course_id', $course_id);
					$stmt->execute();
					$back = $stmt->fetchAll();

					if (count($back)) {
						$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = 'END' ORDER BY `timestamp` DESC");
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

					$html_front .= "
						<tr>
                            <td style='font-size:9;font-family:Courier;text-align:center;height:40px;border:1px solid black'><b>$course_code</b></td>
                            <td style='font-size:9;font-family:Courier;text-align:center;height:40px;border:1px solid black'><b>$theory_grade</b></td>
                            <td style='font-size:9;font-family:Courier;text-align:center;height:40px;border:1px solid black'><b>$course_theory_credits</b></td>
						</tr>
					";
				}

				$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);
				$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

				$html_front .= "
								<tr>
		                            <td style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Total Credits : $total_sem_credits</b></td>
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
				foreach ($course_info as $crs) {
					$theory_grade = $crs['theory_grade'];
					$lab_grade = $crs['lab_grade'];

					if ($theory_grade == 'F' || $theory_grade == 'F(Ab)' || $lab_grade == 'F' || $lab_grade == 'F(Ab)') {
						$flag = 1;
						break;
					}
				}

				if ($flag == 1) {
					break;
				}

				if ($ctr % 3 == 1) {
					$html_front .= "
						<tr>
					";
				}

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
				
				foreach ($course_info as $crs) {
					$course_id = $crs['course_id'];
					$course_list[] = $course_id;

					$course_code = $crs['course_code'];
					$course_theory_credits = $crs['course_theory_credits'];
					$course_lab_credits = $crs['course_lab_credits'];
					$theory_grade = $crs['theory_grade'];
					$lab_grade = $crs['lab_grade'];

					$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = 'BACK' ORDER BY `timestamp` DESC");
					$stmt->bindParam(':student_id', $student_id);
					$stmt->bindParam(':course_id', $course_id);
					$stmt->execute();
					$back = $stmt->fetchAll();

					if (count($back)) {
						$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND exam_type = 'END' ORDER BY `timestamp` DESC");
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

					$html_front .= "
						<tr>
                            <td style='font-size:9;font-family:Courier;text-align:left;height:20px;border:1px solid black'>$course_code</td>
                            <td style='font-size:9;font-family:Courier;text-align:center;height:20px;border:1px solid black'>$theory_grade</td>
                            <td style='font-size:9;font-family:Courier;text-align:center;height:20px;border:1px solid black'>$lab_grade</td>
						</tr>
					";
				}

				$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);
				$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

				$html_front .= "
							<tr>
	                            <td colspan='2' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>Total Credits : $total_sem_credits</b></td>
	                            <td style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'><b>SGPI = $sgpi</b></td>
	                        </tr>
	                        <tr>
	                            <td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;height:20px;border:1px solid black'>Result: Passed and Promoted to Next Semester</td>
							</tr>
						</table>
					</td>		
				";

				if ($ctr % 3 == 0) {
					$html_front .= "
						</tr>
					";
				}
			}

			if ($flag == 1) {
				break;
			}
		}

		$html_front .= "
			</table>
		";

		$total_credits_appeared = $transcript_info['total_credits_appeared'];
		$total_credits_earned = $transcript_info['total_credits_earned'];
		$cgpi = $transcript_info['cgpi'];

		$html_front .= "
						<table align='left'>
							<tr>
								<td style='font-size:11;font-family:Times New Roman;text-align:left;line-height:2px;padding-top:10px;padding-right:25px'><b>CGPI : $cgpi</b></td>
								<td style='font-size:11;font-family:Times New Roman;text-align:left;line-height:2px;padding-top:10px;padding-right:25px'><b>Credits Appeared : $total_credits_appeared</b></td>
								<td style='font-size:11;font-family:Times New Roman;text-align:left;line-height:2px;padding-top:10px;padding-right:25px'><b>Credits Earned : $total_credits_earned</b></td>
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

        $stmt = $conn->prepare("SELECT * FROM grades");
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
        	$course_list_1 = array_slice($course_list, 0, ceil($course_list_length / 2));
			$course_list_2 = array_slice($course_list, ceil($course_list_length / 2));
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
    		$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :crs");
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
    		$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = :crs");
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

	function ecell_result_sheet_details($conn, $sem_id) {
		$result_sheet_info = array();
		$course_list_info = array();
		$student_results_info = array();

		$stmt = $conn->prepare("SELECT DISTINCT student_id FROM results WHERE sem_id = :sem_id ORDER BY student_id");
		$stmt->bindParam(':sem_id', $sem_id);
		$stmt->execute();
		$student_id_list = $stmt->fetchAll();

		$stmt = $conn->prepare("SELECT DISTINCT course_id FROM results WHERE sem_id = :sem_id ORDER BY course_id");
		$stmt->bindParam(':sem_id', $sem_id);
		$stmt->execute();
		$course_id_list = $stmt->fetchAll();

		foreach ($course_id_list as $crs) {
			$course_id = $crs['course_id'];

			$course_details = ecell_course_details($conn, $course_id);
			$course_code = $course_details['course_code'];
			$course_theory_credits = $course_details['course_theory_credits'];
			$course_lab_credits = $course_details['course_lab_credits'];

			if ($course_theory_credits > 0) {
				$course_list_info[] = $course_code.' (Theory)';
			}

			if ($course_lab_credits > 0) {
				$course_list_info[] = $course_code.' (Lab)';
			}
		}

        $s_no = 0;

		foreach ($student_id_list as $stud) {
			$course_results_info = array();
            $s_no++;

			$student_id = $stud['student_id'];

			$stmt = $conn->prepare("SELECT * FROM student WHERE student_id = :student_id");
			$stmt->bindParam(':student_id', $student_id);
			$stmt->execute();
			$student_details = $stmt->fetchAll();

            $enrollment_no = $student_details[0]['enrollment_no'];
            $first_name = $student_details[0]['first_name'];
            $middle_name = $student_details[0]['middle_name'];
            $last_name = $student_details[0]['last_name'];

            if (!empty($middle_name)) {
                $student_name = $first_name.' '.$middle_name.' '.$last_name;
            } else {
                $student_name = $first_name.' '.$last_name;
            }

			foreach ($course_id_list as $crs) {
				$course_id = $crs['course_id'];

				$course_details = ecell_course_details($conn, $course_id);
				$course_theory_credits = $course_details['course_theory_credits'];
				$course_lab_credits = $course_details['course_lab_credits'];

				$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = :student_id AND course_id = :course_id AND sem_id = :sem_id AND exam_type = 'END' ORDER BY `timestamp` DESC LIMIT 1");
				$stmt->bindParam(':student_id', $student_id);
				$stmt->bindParam(':course_id', $course_id);
				$stmt->bindParam(':sem_id', $sem_id);
				$stmt->execute();
				$results = $stmt->fetchAll();

				if (count($results)) {
					$theory_grade_id = $results[0]['theory_grade'];
					$lab_grade_id = $results[0]['lab_grade'];

					$theory_grade_name = ecell_grade_id_to_name($conn, $theory_grade_id);
					$lab_grade_name = ecell_grade_id_to_name($conn, $lab_grade_id);

					if ($course_theory_credits > 0) {
						$course_results_info[] = $theory_grade_name;
					}

					if ($course_lab_credits > 0) {
						$course_results_info[] = $lab_grade_name;
					}
				} else {
					if ($course_theory_credits > 0) {
						$course_results_info[] = 'NO';
					}

					if ($course_lab_credits > 0) {
						$course_results_info[] = 'NO';
					}
				}
			}

			$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

			$student_results_info[] = array(
                    's_no' => $s_no,
					'enrollment_no' => $enrollment_no,
                    'student_name' => $student_name,
					'course_results_info' => $course_results_info,
					'sgpi' => $sgpi
				);
		}

		$result_sheet_info = array(
				'course_list_info' => $course_list_info,
				'student_results_info' => $student_results_info
			);

		return $result_sheet_info;
	}

	function ecell_generate_result_sheet_pdf($conn, $mpdf, $sem_id) {
		$result_sheet_info = ecell_result_sheet_details($conn, $sem_id);

		$course_list_info = $result_sheet_info['course_list_info'];

		$mpdf->AddPage();

		$html = "
			<html>
				<body>
					<table align='center' style='border-collapse:collapse'>
		                <tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>S. No.</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Enrollment No.</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>Student Name</b></th>
		";

		foreach ($course_list_info as $course_code) {
			$html .= "
	                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>$course_code</b></th>
	        ";
	    }

        $html .= "
                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'><b>SGPI</b></th>
            </tr>
		";

		$student_results_info = $result_sheet_info['student_results_info'];

		foreach ($student_results_info as $stud) {
            $s_no = $stud['s_no'];
            $enrollment_no = $stud['enrollment_no'];
			$student_name = $stud['student_name'];

			$html .= "
				<tr>
				    <td style='font-size:12;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$s_no</th>
                    <td style='font-size:12;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px'>$enrollment_no</th>
                    <td style='font-size:12;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px'>$student_name</th>
			";

			$course_results_info = $stud['course_results_info'];

			foreach ($course_results_info as $course_grade) {
				$html .= "
                	<td style='font-size:12;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$course_grade</th>
				";
			}

			$sgpi = $stud['sgpi'];

			$html .= "
                	<td style='font-size:12;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px'>$sgpi</th>
				</tr>
			";
		}

		$html .= "
	                </table>
                </body>
            </html>
		";

		$mpdf->WriteHTML($html);
	}

    function ecell_generate_result_sheet_csv($conn, $sem_id) {
        $sem_details = ecell_sem_details($conn, $sem_id);

        $program_id = $sem_details['program_id'];
        $year_of_joining = $sem_details['year_of_joining'];
        $sem_code_of_joining = $sem_details['sem_code_of_joining'];
        $year = $sem_details['year'];
        $sem_code = $sem_details['sem_code'];

        $program_details = ecell_program_details($conn, $program_id);
        $program_code = $program_details['program_code'];

        $sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);
        $sem_code_name = ecell_sem_code_description($conn, $sem_code);

        $file_name = $program_code.'_'.$year_of_joining.'_'.$sem_code_of_joining_name.'_'.$year.'_'.$sem_code_name.'_Result_Sheet.csv';

        $student_results_list = array();

        $result_sheet_info = ecell_result_sheet_details($conn, $sem_id);

        $course_list_info = $result_sheet_info['course_list_info'];

        $result_sheet_header = array('S. No.', 'Enrollment No.', 'Student Name');
        $result_sheet_header = array_merge($result_sheet_header, $course_list_info, array('SGPI'));

        $student_results_info = $result_sheet_info['student_results_info'];

        foreach ($student_results_info as $stud) {
            $s_no = $stud['s_no'];
            $enrollment_no = $stud['enrollment_no'];
            $student_name = $stud['student_name'];

            $course_results_info = $stud['course_results_info'];

            $sgpi = $stud['sgpi'];

            $student_results_list[] = array_merge(array($s_no, $enrollment_no, $student_name), $course_results_info, array($sgpi));
        }

        $output = fopen("php://output",'w') or die("Can't open php://output");
        header("Content-Type:application/csv");
        header("Content-Disposition:attachment;filename=$file_name");

        fputcsv($output, $result_sheet_header);
        foreach ($student_results_list as $list) {
            fputcsv($output, $list);
        }

        fclose($output);
    }
?>
