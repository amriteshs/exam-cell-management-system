<?php
	include_once('connect.php');
	include_once('session.php');
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	$hashstr = ecell_grade_hash();

	function ecell_convert_date($date) {
	    $date = explode('/', $date);

	    $day = str_pad($date[1], 2, '0', STR_PAD_LEFT);
	    $month = str_pad($date[0], 2, '0', STR_PAD_LEFT);
	    $year = $date[2];

	    $date = $year . '-' . $month . '-' . $day;

	    return $date;
	}

	function ecell_grade_hash() {
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

	function ecell_check_status($str) {
		$conn = ecell_get_conn();
		$sql = "SELECT status_id FROM status WHERE status_name = :status_name";

		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':status_name', $str);
		$stmt->execute();

		$res = $stmt->fetchAll()[0][0];
		return $res;
	}

	function ecell_get_status($str) {
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

	function ecell_get_val($table_name, $column_name, $value, $req) {
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

	function ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn) {
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

	function ecell_is_string_valid($str) {
	    $str = trim($str);
	    $aValid = array(' ');

	    if (ctype_alpha(str_replace($aValid, '', $str))) {
	        return true;
	    } else {
	        return false;
	    }
	}

	function ecell_is_alpha_num($str) {
	    $str = trim($str);
	    if (ctype_alnum($str)) {
	        return true;
	    } else {
	        return false;
	    }
	}

	function ecell_get_staff_ranks() {
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

	function ecell_redirect_if_below($designation, $to) {
	    $all_ranks = ecell_get_staff_ranks();
	    if ($_SESSION['rank'] < $all_ranks[$designation]) {
	        header("Location: $to");
	    }
	}

	$all_ranks = ecell_get_staff_ranks();
	header('Cache-Control: max-age=100');

	function ecell_get_status_value_id($conn, $table_name, $attribute_list) {
		if (empty($attribute_list)) {
			return;
		}

		$sql = "SELECT * FROM ".$table_name." WHERE 1";
		foreach ($attribute_list as $att => $val) {
			$sql .= sprintf(' AND `%s` = :%s', $att, $att);
		}

		$stmt = $conn->prepare($sql);

		foreach ($attribute_list as $att => $val) {
			$stmt->bindValue(':'.$att, $val);
		}

		$stmt->execute();
		$status_value_id = $stmt->fetchAll()[0]['status_value_id'];

		return $status_value_id;
	}

	function ecell_old_status_and_log($conn, $status_value_id) {
		$sql = "SELECT * FROM status_value WHERE status_value_id = :status_value_id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':status_value_id', $status_value_id);
		$stmt->execute();
		$oldv = $stmt->fetchAll();

		$old_status_id = $oldv[0]['status_id'];
		$old_log_id = $oldv[0]['log_id'];

		$old_values = array(
				'old_status_id' => $old_status_id,
				'old_log_id' => $old_log_id
			);

		return $old_values;
	}

	function ecell_update_history($conn, $table_name, $attribute_name, $old_value, $old_log_id, $new_log_id) {
		$sql = "INSERT INTO update_history VALUES (:old_log_id, :new_log_id, :old_value, :table_name, :attribute_name)";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':old_log_id', $old_log_id);
		$stmt->bindParam(':new_log_id', $new_log_id);
		$stmt->bindParam(':old_value', $old_value);
		$stmt->bindParam(':table_name', $table_name);
		$stmt->bindParam(':attribute_name', $attribute_name);
		$stmt->execute(); 

		$ac_on = 'Inserted old value '.$old_value.', old log id '.$old_log_id.' and new log id '.$new_log_id.' for attribute \'.'.$attribute_name.'\' in table \''.$table_name.'\'.';
		$s_i = $_SESSION['staff_id'];
		$r = $_SESSION['rank'];
		$tn = 'update_history';

		$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);
	}

	function ecell_update_status_value_history($conn, $old_status_id, $old_log_id, $new_log_id) {
		$sql = "INSERT INTO update_status_value_history VALUES (:old_log_id, :new_log_id, :old_status_id)";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':old_log_id', $old_log_id);
		$stmt->bindParam(':new_log_id', $new_log_id);
		$stmt->bindParam(':old_status_id', $old_status_id);
		$stmt->execute();

		$old_status_name = ecell_status_id_to_name($conn, $old_status_id);

		$ac_on = 'Inserted old status \''.$old_status_name.'\', old log id '.$old_log_id.' and new log id '.$new_log_id.'.';
		$s_i = $_SESSION['staff_id'];
		$r = $_SESSION['rank'];
		$tn = 'update_status_value_history';

		$log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);
	}

	function ecell_status_id_to_name($conn, $status_id) {
		$stmt = $conn->prepare("SELECT status_name FROM status WHERE status_id = :status_id");
		$stmt->bindParam(':status_id', $status_id);
		$stmt->execute();
		$status_name = $stmt->fetchAll()[0]['status_name'];

		return $status_name;
	}

	function ecell_modify_status($conn, $table_name, $status_value_id, $new_status_id) {
		$old_values = ecell_old_status_and_log($conn, $status_value_id);
		$old_status_id = $old_values['old_status_id'];
		$old_log_id = $old_values['old_log_id'];

		$sql = "UPDATE status_value SET status_id = :new_status_id WHERE status_value_id = :status_value_id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':new_status_id', $new_status_id);
		$stmt->bindParam(':status_value_id', $status_value_id);
		$stmt->execute(); 

		$old_status_name = ecell_status_id_to_name($conn, $old_status_id);
		$new_status_name = ecell_status_id_to_name($conn, $new_status_id);

		$ac_on = 'Updated status from \''.$old_status_name.'\' to \''.$new_status_name.'\' for status value '.$status_value_id.'.';
		$s_i = $_SESSION['staff_id'];
		$r = $_SESSION['rank'];
		$tn = $table_name;

		$new_log_id = ecell_log_procedure($s_i, $r, $sql, $ac_on, $conn, $tn);

		$sql = "UPDATE status_value SET log_id = :new_log_id WHERE status_value_id = :status_value_id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':new_log_id', $new_log_id);
		$stmt->bindParam(':status_value_id', $status_value_id);
		$stmt->execute(); 

		ecell_update_history($conn, 'status_value', 'status_id', $old_status_id, $old_log_id, $new_log_id);		
		ecell_update_status_value_history($conn, $old_status_id, $old_log_id, $new_log_id);
	}

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
		$DOB = date('M d, Y', strtotime($DOB));

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

		$stmt = $conn->prepare("SELECT media, mime FROM student_documents WHERE student_id = :student_id AND category = 'Photo'");
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

	function ecell_student_sign($conn, $enrollment_no) {
		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$stmt = $conn->prepare("SELECT media, mime FROM student_documents WHERE student_id = :student_id AND category = 'Signature'");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $docs = $stmt->fetchAll();
        $image = $docs[0]['media'];
        $mime = $docs[0]['mime'];

        $sign_array = array(
        		'image' => $image,
        		'mime' => $mime
        	);

        return $sign_array;
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
	function ecell_get_val_on($table_name, $column_name, $value, $req) {
	    try {
	        $conn = new PDO("mysql:host=localhost;dbname=offline", "root", "");
	        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        $on_id = ecell_check_status('on');
	        $sql = "SELECT $req FROM $table_name, status_value WHERE $column_name = '$value' AND $table_name.status_value_id = status_value.status_value_id AND status_value.status_id = $on_id";

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
        $course_type_id = $course_details[0]['course_type'];
        $course_theory_credits = $course_details[0]['theory_credit'];
        $course_lab_credits = $course_details[0]['lab_credit'];

        $stmt = $conn->prepare("SELECT * FROM course_type WHERE course_type_id = :course_type_id");
        $stmt->bindParam(':course_type_id', $course_type_id);
        $stmt->execute();
        $course_type = $stmt->fetchAll()[0]['course_type_description'];

        $course_type = substr($course_type, 0, 1);

        $course_array = array(
        		'course_code' => $course_code,
        		'course_name' => $course_name,
        		'course_type' => $course_type,
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
		$flag = 0;

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

			$theory_grade_name = ecell_grade_id_to_name($conn, $theory_grade_id); 
			$lab_grade_name = ecell_grade_id_to_name($conn, $lab_grade_id);

			if ($theory_grade_name == 'F' || $theory_grade_name == 'F(Ab)' || $lab_grade_name == 'F' || $lab_grade_name == 'F(Ab)') {
				$flag = 1;
				break;
			}

			$theory_grade_credits = ecell_grade_id_to_credit($conn, $theory_grade_id); 
			$lab_grade_credits = ecell_grade_id_to_credit($conn, $lab_grade_id);

			$course_details = ecell_course_details($conn, $course_id);

			$course_theory_credits = $course_details['course_theory_credits'];
			$course_lab_credits = $course_details['course_lab_credits'];

			if ($course_theory_credits > 0 && $theory_grade_credits > 0) {
				$sgpi += ($theory_grade_credits * $course_theory_credits);
			} 
			
			if ($course_lab_credits > 0 && $lab_grade_credits > 0) {
				$sgpi += ($lab_grade_credits * $course_lab_credits);
			}
		}

		if ($flag == 1) {
			$sgpi = 0.00;
	    } else {
	    	$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);

			$sgpi /= $total_sem_credits;
	        $sgpi = number_format((float)$sgpi, 2, '.', '');
	    }

		return $sgpi;
	}

	function ecell_total_credits_earned($conn, $enrollment_no) {
		$total_credits_earned = 0;
		$failed_credits = 0;

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
				$course_id = $res['course_id'];
				$theory_grade_id = $res['theory_grade'];
				$lab_grade_id = $res['lab_grade'];

				$theory_grade_name = ecell_grade_id_to_name($conn, $theory_grade_id); 
				$lab_grade_name = ecell_grade_id_to_name($conn, $lab_grade_id);

				if ($theory_grade_name == 'F' || $theory_grade_name == 'F(Ab)') {
					$course_details = ecell_course_details($conn, $course_id);
					$course_theory_credits = $course_details['course_theory_credits'];

					$failed_credits += $course_theory_credits;
				}

				if ($lab_grade_name == 'F' || $lab_grade_name == 'F(Ab)') {
					$course_details = ecell_course_details($conn, $course_id);
					$course_lab_credits = $course_details['course_lab_credits'];

					$failed_credits += $couse_lab_credits;
				}
			}

			$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);

			$total_credits_earned += $total_sem_credits;
		}

		$total_credits_earned -= $failed_credits;

		return $total_credits_earned;
	}

	function ecell_total_credits_appeared($conn, $enrollment_no) {
		$total_credits_appeared = 0;

		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$sem_id_list = ecell_sem_id_list($conn, $enrollment_no);

		foreach ($sem_id_list as $sem_id) {
			$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);

			$total_credits_appeared += $total_sem_credits;
		}

		return $total_credits_appeared;
	}

	function ecell_cgpi($conn, $enrollment_no) {
		$flag = 0;
		$total_sgpi = 0;

		$student_details = ecell_student_details($conn, $enrollment_no);
		$student_id = $student_details['student_id'];

		$sem_id_list = ecell_sem_id_list($conn, $enrollment_no);

		$total_program_credits = ecell_total_credits_earned($conn, $enrollment_no);

		foreach ($sem_id_list as $sem_id) {
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

				if ($theory_grade_name == 'F' || $theory_grade_name == 'F(Ab)' || $lab_grade_name == 'F' || $lab_grade_name == 'F(Ab)') {
					$flag = 1;
					break;
				}

				$theory_grade_credits = ecell_grade_id_to_credit($conn, $theory_grade_id); 
				$lab_grade_credits = ecell_grade_id_to_credit($conn, $lab_grade_id);

				$course_details = ecell_course_details($conn, $course_id);

				$course_theory_credits = $course_details['course_theory_credits'];
				$course_lab_credits = $course_details['course_lab_credits'];

				if ($theory_grade_credits < 0) {
					$total_program_credits -= $course_theory_credits;
				} 
			
				if ($lab_grade_credits < 0) {
					$total_program_credits -= $course_lab_credits;
				}
			}

			if ($flag == 1) {
				break;
			}

			$total_sem_credits = ecell_total_sem_credits($conn, $enrollment_no, $sem_id);
			$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

			$total_sgpi += ($total_sem_credits * $sgpi);
		}

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
		$program_code = $program_details['program_code'];

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
         	$course_type = $course_details['course_type'];
            $course_theory_credits = $course_details['course_theory_credits'];
            $course_lab_credits = $course_details['course_lab_credits'];

            $course_list_array[] = array(
            		'course_code' => $course_code,
            		'course_name' => $course_name,
            		'course_type' => $course_type,
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
                $sgpi = 'NA';
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
            $res_dec_date = date('M d, Y', strtotime($res_dec_date));
        }

        date_default_timezone_set('Asia/Kolkata');
        $res_prep_date = date('M d, Y');

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
        		'program_code' => $program_code,
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

	function ecell_generate_grade_card_pdf($conn, $mpdf, $enrollment_no, $year, $sem_code) {
		$semester_results = ecell_semester_results($conn, $enrollment_no, $year, $sem_code);

		$academic_session = $semester_results['academic_session'];

		$student_id = $semester_results['student_id'];
        $student_name = $semester_results['student_name'];
        $program_name = $semester_results['program_name'];
        $program_code = $semester_results['program_code'];
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
		                    <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'><b>Program :</b> $program_code</p>
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

	function ecell_generate_grade_card_tex($conn, $enrollment_no, $year, $sem_code) {
		$semester_results = ecell_semester_results($conn, $enrollment_no, $year, $sem_code);

		$academic_session = $semester_results['academic_session'];
		$student_id = $semester_results['student_id'];
        $student_name = $semester_results['student_name'];
        $program_name = $semester_results['program_name'];
        $program_code = $semester_results['program_code'];
        $sem_title = $semester_results['semester'];
        $image = $semester_results['student_photo_image'];
        $mime = $semester_results['student_photo_mime'];
        $course_list = $semester_results['course_list'];
        $total_sem_credits = $semester_results['total_sem_credits'];
        $sgpi = $semester_results['sgpi'];
		$res_dec_date = $semester_results['result_declaration_date'];
		$res_prep_date = $semester_results['result_preparation_date'];
        $result_status = $semester_results['result_status'];

        $sem_title .= ' ('.$academic_session.')';

        $tex = "
        		\\AddToShipoutPicture*{\\FrontsidePic}

				\\begin{minipage}{.8\\textwidth}
					\\vspace{20mm}
				\\end{minipage}

        		\\footnotesize

				\\begin{figure}[H]
					\\begin{minipage}{.85\\textwidth}		
						\\begin{tabular}[!bt]{ p{12cm} p{2cm}}
							\\begin{tabular}{ p{2.5cm} p{9cm} }
								\\textbf{Student Name:} & $student_name \\\\
								\\textbf{Enrollment no.:} & $enrollment_no \\\\
								\\textbf{Programme:} & $program_name \\\\
								\\textbf{Semester:} & $sem_title \\\\
							\\end{tabular}
						\\end{tabular}
						
					\\end{minipage}
					\\begin{minipage}[]{.12\\textwidth}
						\\centering
						\\includegraphics[width=\\textwidth]{/home/ecell/apache_files/cert/$enrollment_no/$enrollment_no}
					\\end{minipage}\\hfill
				\\end{figure}
		
				\\vspace{5mm}

				\\setlength{\\LTpre}{0pt}
				\\setlength{\\LTpost}{0pt}
				\\setlength{\\LTleft}{13pt}
				\\hspace{5mm}
				\\begin{longtable}[t]{|p{85mm}|p{7mm}|p{7mm}|p{7mm}|p{7mm}|p{7mm}|p{6mm}|}\\hline
					\\bf{COURSE} & \\bfseries{TYPE} & \\multicolumn{2}{c|}{\\bf{THEORY}} & \\multicolumn{2}{c|}{\\bf{LAB}} & \\bf{SGPI} \\\\
					& & \\scriptsize{\\bf{Credits}} & \\scriptsize{\\bf{Grade}} & \\scriptsize{\\bf{Credits}} & \\scriptsize{\\bf{Grade}} & \\\\\\hline\\hline
					& & & & & & \\\\
		";

		$i = 0;
		$len = count($course_list);

        foreach ($course_list as $crs) {
            $course_code = $crs['course_code'];
            $course_name = strtoupper($crs['course_name']);
            $course_type = $crs['course_type'];
            $course_theory_credits = $crs['course_theory_credits'];
            $course_lab_credits = $crs['course_lab_credits'];
            $theory_grade_name = $crs['theory_grade'];
            $lab_grade_name = $crs['lab_grade'];

            $tex .= "\t\t\t$course_name & $course_type & $course_theory_credits & $theory_grade_name & $course_lab_credits & $lab_grade_name & ";

            if ($i == 0) {
				if ($sgpi == 'NA') {
					$sgpi = '';
				}

                $tex .= "$sgpi \\\\\n\t\t";
            } else {
                $tex .= "\\\\\n\t\t";
            }

            if ($i == $len - 1) {
                $tex .= "\t\t\t& & & & & & \\\\\n\t\t";
            }

            $i++;
        }

		$tex .= "
					\\hline
				\\end{longtable}

				\\vspace{8mm}

				\\normalsize

				\\begin{tabular}{ p{12.5cm} p{9cm} }
					\\hspace{-3mm}
					\\textbf{Date of result declaration:} $res_dec_date \\\\
					\\hspace{-3mm}
					\\textbf{Grade sheet prepared on:} $res_prep_date \\\\
				\\end{tabular}

				\\vfill

				% ----------- Rear side -------------

				\\newpage
				\\pagestyle{empty}

				\\begin{center}
					\\bf{{\\LARGE Award section and other relevant information}}
					\\vspace{1mm}
					\\centering
					\\hrule
					\\hrule
					\\hrule
				\\end{center}

				\\footnotesize

				\\begin{itemize}[wide = 0.5em, nosep, after=\\vspace{-\\baselineskip},leftmargin=.3in]
					\\item Medium of Instruction is English.
					\\item Senate of Indian Institute of Information Technology, Allahabad, prescribes course work.
					\\item Programme consists of semesters of 6 months each, with every semester consisting of about 14 instructional weeks.
					\\item Courses are of three types, \\textit{viz.}: Core (C), Elective (E) and Project (P).
					\\item One theory credit denotes 1 hour of lecture per week and one lab credit denotes 1.5 hours of laboratory work per week.
					\\item Details of grades awarded are given in Table 1. 
					\\item '*' on a grade indicates that it was cleared through Back Paper Exam.
					\\item Grades under 'Lab' may refer to practical/term paper/project.
					\\item The Semester Grade Point Index (SGPI) is the weighted average of the grade points earned by a student in all the courses credited in a semester. 
					\\item Cumulative Grade Point Index (CGPI) is the weighted average of the grade points earned by a student in all the courses credited in all the courses registered, including the latest completed semester. 
					\\item Only grades with numeric values shall be used in computing SGPI and CGPI.
					\\item CGPI of 7 and above will be considered as First Division/First Class.
					\\item A CGPI 8.5 and above shall be considered as Honours (only) for Undergraduates.
					\\item No equivalent PERCENTAGE of marks is awarded as Institute follows a relative grading system.
				\\end{itemize}

				\\vspace{5mm}

				\\begin{minipage}[t]{0.99\\textwidth}
					\\centering
					\\setlength{\\LTpre}{10pt}
					\\setlength{\\LTpost}{5pt}
					\\setlength{\\LTleft}{100pt}
					
					\\begin{longtable}[t]{|p{1.0cm} p{4.8cm} p{1.0cm}|}
						\\hline
						\\bf{Grade} & \\bf{Description} & \\bfseries{Value} \\\\
						\\hline
						\\enspace A+ & Outstanding  & \\enspace 10 \\\\   
						\\enspace A  & Excellent  & \\enspace  9 \\\\   
						\\enspace B+ & Good  & \\enspace  8 \\\\   
						\\enspace B  & Average  & \\enspace 7 \\\\   
						\\enspace C  & Below average  & \\enspace 6 \\\\   
						\\enspace D  & Just pass & \\enspace 5 \\\   
						\\enspace S  & Satisfactory  & \\enspace - \\\\   
						\\enspace E  & Exposed & \\enspace 0 \\\\   
						\\enspace F  & Fail  & \\enspace 0 \\\\   
						\\enspace X  & Rejected  & \\enspace - \\\\   
						\\enspace R  & Registration cancelled for want of minimum attendance & \\enspace - \\\\   
						\\enspace I  & Incomplete & \\enspace - \\\\   
						\\hline
						\\caption*{\\textbf{Table 1.} Grade point values}\\\\
					\\end{longtable}
				\\end{minipage}
        ";

        return $tex;
	}

	function ecell_back_results($conn, $enrollment_no, $year, $month) {
		if ($month == '01') {
            $month_name = 'Jan';
        } else if ($month == '02') {
            $month_name = 'Feb';
        } else if ($month == '03') {
            $month_name = 'Mar';
        } else if ($month == '04') {
            $month_name = 'Apr';
        } else if ($month == '05') {
            $month_name = 'May';
        } else if ($month == '06') {
            $month_name = 'Jun';
        } else if ($month == '07') {
            $month_name = 'Jul';
        } else if ($month == '08') {
            $month_name = 'Aug';
        } else if ($month == '09') {
            $month_name = 'Sep';
        } else if ($month == '10') {
            $month_name = 'Oct';
        } else if ($month == '11') {
            $month_name = 'Nov';
        } else if ($month == '12') {
            $month_name = 'Dec';
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
		$program_code = $program_details['program_code'];

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
         	$course_type = $course_details['course_type'];
            $course_theory_credits = $course_details['course_theory_credits'];
            $course_lab_credits = $course_details['course_lab_credits'];

            $course_list_array[] = array(
            		'course_code' => $course_code,
            		'course_name' => $course_name,
            		'course_type' => $course_type,
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
            $res_dec_date = date('M d, Y', strtotime($res_dec_date));
        }

        date_default_timezone_set('Asia/Kolkata');
        $res_prep_date = date('M d, Y');

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
        		'program_code' => $program_code,
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

	function ecell_generate_back_grade_card_pdf($conn, $mpdf, $enrollment_no, $year, $month) {
		$back_results = ecell_back_results($conn, $enrollment_no, $year, $month);

		$academic_session = $back_results['academic_session'];

		$student_id = $back_results['student_id'];
        $student_name = $back_results['student_name'];
        $program_name = $back_results['program_name'];
        $program_code = $back_results['program_code'];
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
		                    <p style='font-size:15;font-family:Times New Roman;text-align:left;line-height:2px;padding-left:5mm'><b>Program :</b> $program_code</p>
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

	function ecell_generate_back_grade_card_tex($conn, $enrollment_no, $year, $month) {
		$back_results = ecell_back_results($conn, $enrollment_no, $year, $month);

		$academic_session = $back_results['academic_session'];
		$student_id = $back_results['student_id'];
        $student_name = $back_results['student_name'];
        $program_name = $back_results['program_name'];
        $program_code = $back_results['program_code'];
        $image = $back_results['student_photo_image'];
        $mime = $back_results['student_photo_mime'];
        $course_list = $back_results['course_list'];
        $total_sem_credits = $back_results['total_sem_credits'];
        $sgpi = $back_results['sgpi'];
		$res_dec_date = $back_results['result_declaration_date'];
		$res_prep_date = $back_results['result_preparation_date'];

        $tex = "
        		\\AddToShipoutPicture*{\\FrontsidePic}

				\\begin{minipage}{.8\\textwidth}
					\\vspace{20mm}
				\\end{minipage}

        		\\footnotesize

				\\begin{figure}[H]
					\\begin{minipage}{.85\\textwidth}		
						\\begin{tabular}[!bt]{ p{12cm} p{2cm}}
							\\begin{tabular}{ p{2.5cm} p{9cm} }
								\\textbf{Student Name:} & $student_name \\\\
								\\textbf{Enrollment no.:} & $enrollment_no \\\\
								\\textbf{Programme:} & $program_name \\\\
							\\end{tabular}
						\\end{tabular}
						
					\\end{minipage}
					\\begin{minipage}[]{.12\\textwidth}
						\\centering
						\\includegraphics[width=\\textwidth]{/home/ecell/apache_files/cert/$enrollment_no/$enrollment_no}
					\\end{minipage}\\hfill
				\\end{figure}
		
				\\vspace{5mm}

				\\setlength{\\LTpre}{0pt}
				\\setlength{\\LTpost}{0pt}
				\\setlength{\\LTleft}{13pt}
				\\hspace{5mm}
				\\begin{longtable}[t]{|p{85mm}|p{7mm}|p{7mm}|p{7mm}|p{7mm}|p{7mm}|p{6mm}|}\\hline
					\\bf{COURSE} & \\bfseries{TYPE} & \\multicolumn{2}{c|}{\\bf{THEORY}} & \\multicolumn{2}{c|}{\\bf{LAB}} & \\bf{SGPI} \\\\
					& & \\scriptsize{\\bf{Credits}} & \\scriptsize{\\bf{Grade}} & \\scriptsize{\\bf{Credits}} & \\scriptsize{\\bf{Grade}} & \\\\\\hline\\hline
					& & & & & & \\\\
		";

        foreach ($course_list as $crs) {
            $course_code = $crs['course_code'];
            $course_name = strtoupper($crs['course_name']);
            $course_type = $crs['course_type'];
            $course_theory_credits = $crs['course_theory_credits'];
            $course_lab_credits = $crs['course_lab_credits'];
            $theory_grade_name = $crs['theory_grade'];
            $lab_grade_name = $crs['lab_grade'];

            $tex .= "\t\t\t$course_name & $course_type & $course_theory_credits & $theory_grade_name & $course_lab_credits & $lab_grade_name & ";

            if ($i == 0) {
				if ($sgpi == 'NA') {
					$sgpi = '';
				}

                $tex .= "$sgpi \\\\\n\t\t";
            } else {
                $tex .= "\\\\\n\t\t";
            }

            if ($i == $len - 1) {
                $tex .= "\t\t\t& & & & & & \\\\\n\t\t";
            }

            $i++;
        }

		$tex .= "
					\\hline
				\\end{longtable}

				\\vspace{8mm}

				\\normalsize

				\\begin{tabular}{ p{12.5cm} p{9cm} }
					\\hspace{-3mm}
					\\textbf{Date of result declaration:} $res_dec_date \\\\
					\\hspace{-3mm}
					\\textbf{Grade sheet prepared on:} $res_prep_date \\\\
				\\end{tabular}

				\\vfill

				% ----------- Rear side -------------

				\\newpage
				\\pagestyle{empty}

				\\begin{center}
					\\bf{{\\LARGE Award section and other relevant information}}
					\\vspace{1mm}
					\\centering
					\\hrule
					\\hrule
					\\hrule
				\\end{center}

				\\footnotesize

				\\begin{itemize}[wide = 0.5em, nosep, after=\\vspace{-\\baselineskip},leftmargin=.3in]
					\\item Medium of Instruction is English.
					\\item Senate of Indian Institute of Information Technology, Allahabad, prescribes course work.
					\\item Programme consists of semesters of 6 months each, with every semester consisting of about 14 instructional weeks.
					\\item Courses are of three types, \\textit{viz.}: Core (C), Elective (E) and Project (P).
					\\item One theory credit denotes 1 hour of lecture per week and one lab credit denotes 1.5 hours of laboratory work per week.
					\\item Details of grades awarded are given in Table 1. 
					\\item '*' on a grade indicates that it was cleared through Back Paper Exam.
					\\item Grades under 'Lab' may refer to practical/term paper/project.
					\\item The Semester Grade Point Index (SGPI) is the weighted average of the grade points earned by a student in all the courses credited in a semester. 
					\\item Cumulative Grade Point Index (CGPI) is the weighted average of the grade points earned by a student in all the courses credited in all the courses registered, including the latest completed semester. 
					\\item Only grades with numeric values shall be used in computing SGPI and CGPI.
					\\item CGPI of 7 and above will be considered as First Division/First Class.
					\\item A CGPI 8.5 and above shall be considered as Honours (only) for Undergraduates.
					\\item No equivalent PERCENTAGE of marks is awarded as Institute follows a relative grading system.
				\\end{itemize}

				\\vspace{5mm}

				\\begin{minipage}[t]{0.99\\textwidth}
					\\centering
					\\setlength{\\LTpre}{10pt}
					\\setlength{\\LTpost}{5pt}
					\\setlength{\\LTleft}{100pt}
					
					\\begin{longtable}[t]{|p{1.0cm} p{4.8cm} p{1.0cm}|}
						\\hline
						\\bf{Grade} & \\bf{Description} & \\bfseries{Value} \\\\
						\\hline
						\\enspace A+ & Outstanding  & \\enspace 10 \\\\   
						\\enspace A  & Excellent  & \\enspace  9 \\\\   
						\\enspace B+ & Good  & \\enspace  8 \\\\   
						\\enspace B  & Average  & \\enspace 7 \\\\   
						\\enspace C  & Below average  & \\enspace 6 \\\\   
						\\enspace D  & Just pass & \\enspace 5 \\\   
						\\enspace S  & Satisfactory  & \\enspace - \\\\   
						\\enspace E  & Exposed & \\enspace 0 \\\\   
						\\enspace F  & Fail  & \\enspace 0 \\\\   
						\\enspace X  & Rejected  & \\enspace - \\\\   
						\\enspace R  & Registration cancelled for want of minimum attendance & \\enspace - \\\\   
						\\enspace I  & Incomplete & \\enspace - \\\\   
						\\hline
						\\caption*{\\textbf{Table 1.} Grade point values}\\\\
					\\end{longtable}
				\\end{minipage}
        ";

        return $tex;
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
		$year_end = date('y', $time_end);
		
		if ($admission_date == 'NA') {
			$program_period = 'NA';
		} else {	
			$program_period = $year_start.'-'.$year_end;
		}

		$admission_date = date('M d, Y', strtotime($admission_date));

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

			$sem_title = strtoupper($sem_details['sem_title']);
			$year = $sem_details['year'];
			$sem_code = $sem_details['sem_code'];

			$sem_code_name = ecell_sem_code_description($conn, $sem_code);

			$academic_session = $sem_code_name.' '.$year;

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
				$course_name = $course_details['course_name'];
				$course_type = $course_details['course_type'];
				$course_theory_credits = $course_details['course_theory_credits'];
				$course_lab_credits = $course_details['course_lab_credits'];

				$course_info[] = array(
						'course_id' => $course_id,
						'course_code' => $course_code,
						'course_name' => $course_name,
						'course_type' => $course_type,
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

	        $stmt = $conn->prepare("SELECT MAX(date_of_declaration) FROM results, (SELECT course_id, MAX(`timestamp`) AS ts FROM results WHERE student_id = :student_id_1 AND sem_id = :sem_id_1 GROUP BY course_id) max_ts WHERE results.course_id = max_ts.course_id AND results.timestamp = max_ts.ts AND student_id = :student_id_2 AND sem_id = :sem_id_2");
			$stmt->bindParam(':student_id_1', $student_id);
			$stmt->bindParam(':sem_id_1', $sem_id);
			$stmt->bindParam(':student_id_2', $student_id);
			$stmt->bindParam(':sem_id_2', $sem_id);
			$stmt->execute();
			$result_date = $stmt->fetchAll()[0]['MAX(date_of_declaration)'];

			if ($result_date == '0000-00-00') {
				$result_date = 'NA';
			} else {
				$result_date = date('M d, Y', strtotime($result_date));
			}

			$sem_info[] = array(
					'sem_id' => $sem_id,
					'sem_title' => $sem_title,
					'academic_session' => $academic_session,
					'course_info' => $course_info,
					'total_sem_credits' => $total_sem_credits,
					'sgpi' => $sgpi,
					'result_date' => $result_date
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

	function ecell_generate_transcript_tex($conn, $enrollment_no) {
	    $tex = "% ----------- BODY BEGINS -------------\n\t\\newpage\n\n\t\\pagestyle{empty}\n\t\\AddToShipoutPicture*{\\FrontsidePic}\n\n\t\\begin{minipage}{.95\\textwidth}\n\t\\vspace{20mm}\n\t\\end{minipage}\n\n\t";

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

	    $tex .= "\\normalsize\n\t\\noindent\n\t\\begin{minipage}{.95\\textwidth}\n\t\t\\textbf{Student's Name:} $student_name \\\\\n\t\t\\textbf{Father's Name:} $father_name \\\\\n\t\t\\textbf{Programme:} $program_name\\\\\n\t\t\\textbf{Enrollment Number:} $enrollment_no\\strut\n\t\\end{minipage}\n\n\t\\noindent\n\t\\begin{minipage}{0.5\\textwidth}\n\t\t\\textbf{Date of Birth:} $dob\\\\\n\t\t\\textbf{Date of Admission:} $admission_date\n\t\\end{minipage}%\n\t\\begin{minipage}{0.5\\textwidth}\n\t\t\\textbf{Period of Programme:} $program_period\\\\\n\t\t\\textbf{Duration of Programme:} $program_duration years ($no_of_sem semesters)\n\t\\end{minipage}\n\n\t\\scriptsize\n\t\\noindent\n\t\\vspace{5mm}\n\n\t\\setlength{\\LTpre}{0pt}\n\t\\setlength{\\LTpost}{0pt}\n\t\\setlength{\\LTleft}{0pt}\n\t";

	    $sem_info = $transcript_info['sem_info'];

	    $ctr = 0;
	    $flag = 0;
	    $course_list = array();

	    $tex .= "\\begin{longtable}[t]{|p{26mm}|p{65mm}|p{7mm}|p{7mm}|p{7mm}|p{7mm}|p{7mm}|p{6mm}|}\\hline\n\t\t\\bf{SEMESTER} & \\bf{COURSE} & \\bfseries{TYPE} & \\multicolumn{2}{c|}{\\bf{THEORY}} & \\multicolumn{2}{c|}{\\bf{LAB}} & \\bf{SGPI} \\\\\n\t\t& & & \\scriptsize{\\bf{Credits}} & \\scriptsize{\\bf{Grade}} & \\scriptsize{\\bf{Credits}} & \\scriptsize{\\bf{Grade}} &  \\\\\\hline\\hline\n\t\t";

	    foreach ($sem_info as $sem) {
	        $sem_id = $sem['sem_id'];
	        $sem_title = $sem['sem_title'];
	        $academic_session = strtoupper($sem['academic_session']);

	        $course_info = $sem['course_info'];

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

	        $i = 0;
	        $len = count($course_info);

	        foreach ($course_info as $crs) {
	            $course_id = $crs['course_id'];
	            $course_list[] = $course_id;

	            $course_code = $crs['course_code'];
	            $course_name = $crs['course_name'];
	            $course_type = $crs['course_type'];
	            $course_theory_credits = $crs['course_theory_credits'];
	            $course_lab_credits = $crs['course_lab_credits'];
	            $theory_grade = $crs['theory_grade'];
	            $lab_grade = $crs['lab_grade'];

	            if (strpos($course_name, '&') !== FALSE) {
	                $course_name = str_replace('&', 'and', $course_name);
	            }

	            $course_name = strtoupper($course_name);

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

	                $theory_grade_back = ecell_grade_id_to_name($conn, $back1[0]['theory_grade']);
	                $lab_grade_back = ecell_grade_id_to_name($conn, $back1[0]['lab_grade']);

	                if ($theory_grade_back == 'F' || $theory_grade_back == 'F(Ab)') {
	                    $theory_grade = '*'.$theory_grade;
	                }

	                if ($lab_grade_back == 'F' || $lab_grade_back == 'F(Ab)') {
	                    $lab_grade = '*'.$lab_grade;
	                }
	            }

	            if ($i == 0) {
	                $tex .= "$sem_title ";
	            }

	            if ($i == 1) {
	            	$tex .= "($academic_session) ";
	            }

	            $tex .= "& $course_name & $course_type & $course_theory_credits & $theory_grade & $course_lab_credits & $lab_grade & ";

	            if ($i == 0) {
	                $sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

					if ($sgpi == 0.00) {
						$sgpi = 'NA';
					}

	                $tex .= "$sgpi \\\\\n\t\t";
	            } else {
	                $tex .= "\\\\\n\t\t";
	            }

	            if ($i == $len - 1) {
	                if ($len > 1) {
		                $tex .= "& & & & & & & \\\\\n\t\t";
	                } else if ($len == 1) {
		            	$tex .= "($academic_session) & & & & & & & \\\\\n\t\t& & & & & & & \\\\\n\t\t";
	            	}
	            }

	            $i++;
	        }       

	        $ctr++;
	    }

	    $total_credits_appeared = $transcript_info['total_credits_appeared'];
	    $total_credits_earned = $transcript_info['total_credits_earned'];
	    $cgpi = $transcript_info['cgpi'];

	    $tex .= "\\hline\n\t\\end{longtable}\n\n\t\\normalsize\n\t\\vspace{5mm}\n\t\\centering\n\t\\textbf{Credits Appeared: $total_credits_appeared} \\hspace{1cm} \\textbf{Credits Earned: $total_credits_earned} \\hspace{1cm} \\textbf{CGPI: $cgpi}\\\\\n\t\\vspace{1mm}\n\t\\textbf{STATUS:} Completed academic requirements successfully \\\\\n\n\t";

	    // Back side of page

	    $tex .= "
		    	\\newpage
				\\pagestyle{empty}
				
				\\begin{center}
					\\bf{{\\LARGE Award section and other relevant information}}
					\\vspace{1mm}
					\\centering
					\\hrule
					\\hrule
					\\hrule
				\\end{center}
				
				\\footnotesize
				
				\\begin{itemize}[wide = 0.5em, nosep, after=\\vspace{-\\baselineskip},leftmargin=.3in]
					\\item Medium of Instruction is English.
					\\item Senate of Indian Institute of Information Technology, Allahabad, prescribes course work.
					\\item Programme consists of semesters of 6 months each, with every semester consisting of about 14 instructional weeks.
					\\item Courses are of three types, \\textit{viz.}: Core (C), Elective (E) and Project (P).
					\\item One theory credit denotes 1 hour of lecture per week and one lab credit denotes 1.5 hours of laboratory work per week.
					\\item Details of grades awarded are given in Table 1. 
					\\item '*' on a grade indicates that it was cleared through Back Paper Exam.
					\\item Grades under 'Lab' may refer to practical/term paper/project.
					\\item The Semester Grade Point Index (SGPI) is the weighted average of the grade points earned by a
					student in all the courses credited in a semester. 
					\\item Cumulative Grade Point Index (CGPI) is the weighted average of the grade points earned by a
					student in all the courses credited in all the courses registered, including the latest
					completed semester. 
					\\item Only grades with numeric values shall be used in computing SGPI and CGPI.
					\\item CGPI of 7 and above will be considered as First Division/First Class.
					\\item A CGPI 8.5 and above shall be considered as Honours (only) for Undergraduates.
					\\item No equivalent PERCENTAGE of marks is awarded as Institute follows a relative grading system.
				\\end{itemize}

				\\vspace{5mm}

				\\begin{minipage}[t]{0.50\\textwidth}
					\\centering
					\\setlength{\\LTpre}{10pt}
					\\setlength{\\LTpost}{5pt}
					\\begin{longtable}[t]{|p{1.0cm} p{4.8cm} p{1.0cm}|}
						\\hline
						\\bf{Grade} & \\bf{Description} & \\bfseries{Value} \\\\
						\\hline
						\\enspace A+ & Outstanding  & \\enspace 10 \\\\   
						\\enspace A  & Excellent  & \\enspace  9 \\\\   
						\\enspace B+ & Good  & \\enspace  8 \\\\   
						\\enspace B  & Average  & \\enspace 7 \\\\   
						\\enspace C  & Below average  & \\enspace 6 \\\\   
						\\enspace D  & Just pass & \\enspace 5 \\\\   
						\\enspace S  & Satisfactory  & \\enspace - \\\\   
						\\enspace E  & Exposed & \\enspace 0 \\\\   
						\\enspace F  & Fail  & \\enspace 0 \\\\   
						\\enspace X  & Rejected  & \\enspace - \\\\   
						\\enspace R  & Registration cancelled for want of minimum attendance & \\enspace - \\\\   
						\\enspace I  & Incomplete & \\enspace - \\\\ 
						\\enspace NA  & Not Applicable & \\enspace - \\\\   
						\\hline
						\\caption*{\\textbf{Table 1.} Grade point values}\\\\
					\\end{longtable}
				\\end{minipage}

				% ----------- BODY ENDS -------------
			";

	    return $tex;
	}

	function ecell_generate_transcript_pdf($conn, $mpdf, $enrollment_no) {
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
                			<td colspan='3' style='font-size:10;font-family:Times New Roman;text-align:center;width:60mm;height:20px;border:1px solid black'><b>$sem_title ($academic_session)</b></td>
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

					$theory_grade_back = ecell_grade_id_to_name($conn, $back1[0]['theory_grade']);
					$lab_grade_back = ecell_grade_id_to_name($conn, $back1[0]['lab_grade']);

					if ($theory_grade_back == 'F' || $theory_grade_back == 'F(Ab)') {
						$theory_grade = '*'.$theory_grade;
					}

					if ($lab_grade_back == 'F' || $lab_grade_back == 'F(Ab)') {
						$lab_grade = '*'.$lab_grade;
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

			if ($ctr % 3 == 0 || $ctr == $no_of_sem) {
				$html_front .= "
					</tr>
				";
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

    function ecell_cumulative_sgpi($conn, $program_id, $year_of_joining, $sem_code_of_joining) {
    	$student_results_header = array();
    	$student_results_list = array();
    	$flag = 0;

		$stmt = $conn->prepare("SELECT DISTINCT student.student_id FROM student, results WHERE student.student_id = results.student_id AND student.program_id = :program_id AND student.year = :year_of_joining AND student.sem_code = :sem_code_of_joining ORDER BY student_id");
		$stmt->bindParam(':program_id', $program_id);
		$stmt->bindParam(':year_of_joining', $year_of_joining);
		$stmt->bindParam(':sem_code_of_joining', $sem_code_of_joining);
		$stmt->execute();
		$student_id_list = $stmt->fetchAll();

		$student_results_header = array('S. No.', 'Enrollment No.', 'Student Name');

		$s_no = 1;

		foreach ($student_id_list as $stud) {
			$student_results_info = array();

			$student_id = $stud['student_id'];

			$stmt = $conn->prepare("SELECT enrollment_no, first_name, middle_name, last_name FROM student WHERE student_id = :student_id");
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

			$student_results_info = array($s_no, $enrollment_no, $student_name);

			$sem_id_list = ecell_sem_id_list($conn, $enrollment_no);

			foreach ($sem_id_list as $sem_id) {
				$sem_details = ecell_sem_details($conn, $sem_id);
				$sem_title = $sem_details['sem_title'];

				$sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);

				if ($sgpi == 0.00 || is_null($sgpi)) {
					$sgpi = 'XXXX';
				}

				if ($flag == 0) {
					$student_results_header = array_merge($student_results_header, array('SGPI ('.$sem_title.')'));
				}

				$student_results_info = array_merge($student_results_info, array($sgpi));
			}

			for ($i = 0; $i < sizeof($student_results_header); $i++) {
				if (empty($student_results_info[$i])) {
					$student_results_info[$i] = 'NA';
				}
			}

			$flag = 1;

			$cgpi = ecell_cgpi($conn, $enrollment_no);

			$student_results_info = array_merge($student_results_info, array($cgpi));

			$student_results_list[] = $student_results_info;

			$s_no++;
		}

		$student_results_header = array_merge($student_results_header, array('CGPI'));

		$student_results = array(
				'student_results_header' => $student_results_header,
				'student_results_list' => $student_results_list
			);

		return $student_results;
	}

    function ecell_generate_cumulative_sgpi_csv($conn, $program_id, $year_of_joining, $sem_code_of_joining) {
        $program_details = ecell_program_details($conn, $program_id);
        $program_code = $program_details['program_code'];

        $sem_code_of_joining_name = ecell_sem_code_description($conn, $sem_code_of_joining);

        $file_name = $program_code.'_'.$year_of_joining.'_'.$sem_code_of_joining_name.'_Cumulative_SGPI.csv';

        $student_results = ecell_cumulative_sgpi($conn, $program_id, $year_of_joining, $sem_code_of_joining);

        $student_results_header = $student_results['student_results_header'];
        $student_results_list = $student_results['student_results_list'];

        $output = fopen("php://output",'w') or die("Can't open php://output");
        header("Content-Type:application/csv");
        header("Content-Disposition:attachment;filename=$file_name");

        fputcsv($output, $student_results_header);
        
        foreach ($student_results_list as $list) {
            fputcsv($output, $list);
        }

        fclose($output);
    }

    function ecell_regular_programme_details($conn, $year) {
        $stmt = $conn->prepare("SELECT DISTINCT program_id FROM student WHERE year = :year");
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $program_list = $stmt->fetchAll();

        foreach ($program_list as $prg) {
            $program_id = $prg['program_id'];

            $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE year = :year AND program_id = :program_id AND DASA = 0");
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':program_id', $program_id);
            $stmt->execute();
            $total_admitted = $stmt->fetchAll()[0]['COUNT(student_id)'];

            $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE year = :year AND program_id = :program_id AND DASA = 1");
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':program_id', $program_id);
            $stmt->execute();
            $total_admitted .= ('+'.$stmt->fetchAll()[0]['COUNT(student_id)']);

            $program_details = ecell_program_details($conn, $program_id);
            $program_name = $program_details['program_name'];
            $program_duration = $program_details['program_duration'];
           
            $duration_years = preg_replace('/\D/', '', $program_duration);
            
            $programmes_arr[] = array(
	                'program_name' => $program_name,
	                'total_seats' => '',
	                'total_admitted' => $total_admitted,
	                'duration_years' => $duration_years,
	                'duration_months' => '0',
	                'exam_system' => 'Semester'
	            );
        } 

        return $programmes_arr;
    }

    function ecell_regular_programme_details_pdf($conn, $mpdf, $year) {
    	$session = $year.' - '.($year + 1);

    	$mpdf->AddPage();

		$html = "
			<html>
				<body>
					<h2 align='center'><u>SURVEY YEAR $session</u></h2>
					<table align='center' style='border-collapse:collapse;width:800px'>
						<tr>
		                    <th colspan='0' style='font-size:14;font-family:Times New Roman;text-align:left;border:1px solid #fff;border-bottom:1px solid black;height:20px;padding:5px'><b>Regular Programme Details</b></th>
						</tr>
		                <tr>
		                    <th rowspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Name of Program</b></th>
		                    <th rowspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Total Seats (I<sup>st</sup> Sem.)</b></th>
		                    <th rowspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Intake (Non-DASA students + DASA students)</b></th>
		                    <th colspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Duration of Program</b></th>
							<th rowspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Examination System</b></th>
						</tr>
						<tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Years</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Months</b></th>
						</tr>
		";

		$regular_program_details = ecell_regular_programme_details($conn, $year);

    	foreach ($regular_program_details as $rpd) {
	    	$program_name = $rpd['program_name'];
	    	$total_seats = $rpd['total_seats'];
	    	$total_admitted = $rpd['total_admitted'];
	    	$duration_years = $rpd['duration_years'];
	    	$duration_months = $rpd['duration_months'];
	    	$exam_system = $rpd['exam_system'];

	    	$html .= "
				<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$program_name</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$total_seats</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$total_admitted</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$duration_years</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$duration_months</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$exam_system</td>
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

    function sksort(&$array, $subkey="id", $sort_ascending=false) {
        if (count($array)) {
            $temp_array[key($array)] = array_shift($array);
        }
        
        foreach ($array as $key => $val) {
            $offset = 0;
            $found = false;

            foreach ($temp_array as $tmp_key => $tmp_val) {
                if (!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                    $temp_array = array_merge(    
                                            (array)array_slice($temp_array,0,$offset),
                                            array($key => $val),
                                            array_slice($temp_array,$offset)
                                        );

                    $found = true;
                }

                $offset++;
            }

            if (!$found) {
                $temp_array = array_merge($temp_array, array($key => $val));
            }
        }

        if ($sort_ascending) {
            $array = array_reverse($temp_array);
        } else {
            $array = $temp_array;
        }
    }

    function ecell_toppers_list($conn, $sem_id, $top_n) {
    	$stmt = $conn->prepare("SELECT DISTINCT student_id FROM results WHERE sem_id = :sem_id AND exam_type = 'END'");
        $stmt->bindParam(':sem_id', $sem_id);
        $stmt->execute();
        $student_list = $stmt->fetchAll();

        foreach ($student_list as $stud) {
        	$student_id = $stud['student_id'];

        	$stmt = $conn->prepare("SELECT enrollment_no, first_name, middle_name, last_name FROM student WHERE student_id = :student_id");
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

	        $sgpi = ecell_sgpi($conn, $enrollment_no, $sem_id);
	        
	        if ($sgpi == 0.00) {
	        	$toppers_list_1[] = array(
	        			'enrollment_no' => $enrollment_no,
	        			'student_name' => $student_name,
	        			'sgpi' => '--'
	        		);
	        } else {
	        	$toppers_list[] = array(
	        			'enrollment_no' => $enrollment_no,
	        			'student_name' => $student_name,
	        			'sgpi' => $sgpi
	        		);
	        }
        }

        sksort($toppers_list, 'sgpi', false);
        $toppers_list = array_merge($toppers_list, $toppers_list_1);
        $toppers_list = array_slice($toppers_list, 0, $top_n);

        return $toppers_list;
    }

    function ecell_toppers_list_pdf($conn, $mpdf, $sem_id, $program_id, $top_n) {
  	  	$mpdf->AddPage();

    	$program_details = ecell_program_details($conn, $program_id);
    	$program_name = $program_details['program_name'];

    	$sem_details = ecell_sem_details($conn, $sem_id);
    	$sem_title = $sem_details['sem_title'];

		$html = "
			<html>
				<body>
					<h2 align='center'><u>List of Toppers</u></h2>
					<div style='margin:0 22% 0 22%'><b>Program :</b> $program_name</div>
					<div style='margin:0 22% 0 22%'><b>$sem_title</b></div>
					<table align='center' style='border-collapse:collapse;width:400px'>
		                <tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:30%'><b>Enrollment No.</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:50%'><b>Student's Name</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:20%'><b>SGPI</b></th>
						</tr>
		";

		$toppers_list = ecell_toppers_list($conn, $sem_id, $top_n);

    	foreach ($toppers_list as $top) {
    		$enrollment_no = $top['enrollment_no'];
    		$student_name = $top['student_name'];
    		$sgpi = $top['sgpi'];

    		$html .= "
				<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$enrollment_no</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$student_name</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$sgpi</td>
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

    function ecell_foreign_student_enrollment($conn, $year) {
        $stmt = $conn->prepare("SELECT DISTINCT dasa_country FROM student WHERE DASA = 1 ORDER BY dasa_country");
        $stmt->execute();
        $country_list = $stmt->fetchAll();

        foreach ($country_list as $cnt) {
            $country_name = $cnt['dasa_country'];
            
            $stmt = $conn->prepare("SELECT program.program_id, program.program_name FROM program, student WHERE program.program_id = student.program_id AND student.DASA = 1 AND student.dasa_country = :country_name");
            $stmt->bindParam(':country_name', $country_name);
            $stmt->execute();
            $program_list = $stmt->fetchAll();

            foreach ($program_list as $prg) {
                $program_id = $prg['program_id'];
                $program_name = $prg['program_name'];

                $stmt = $conn->prepare("SELECT sem_title, year_of_joining FROM course_structure WHERE program_id = :program_id AND sem_id_year = :year AND sem_id_sem_code = 2");
                $stmt->bindParam(':program_id', $program_id);
                $stmt->bindParam(':year', $year);
                $stmt->execute();
                $sem_list = $stmt->fetchAll();

                foreach ($sem_list as $sem) {
                    $year_of_joining = $sem['year_of_joining'];
                    $sem_title = $sem['sem_title'];
                    $sem_title = preg_replace('/\D/', '', $sem_title);

                    $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND DASA = 1 AND gender = 'Male' AND dasa_country = :country_name");
                    $stmt->bindParam(':program_id', $program_id);
                    $stmt->bindParam(':year_of_joining', $year_of_joining);
                    $stmt->bindParam(':country_name', $country_name);
                    $stmt->execute();
                    $country_m = $stmt->fetchAll()[0]['COUNT(student_id)'];

                    $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND DASA = 1 AND gender = 'Female' AND dasa_country = :country_name");
                    $stmt->bindParam(':program_id', $program_id);
                    $stmt->bindParam(':year_of_joining', $year_of_joining);
                    $stmt->bindParam(':country_name', $country_name);
                    $stmt->execute();
                    $country_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

                    if ($country_m == 0 && $country_f == 0) {
                        $perc_country_m = '0%';
                        $perc_country_f = '0%'; 
                    } else {
                        $perc_country_m = round((100 * $country_m / ($country_m + $country_f)), 2).'%';
                        $perc_country_f = round((100 * $country_f / ($country_m + $country_f)), 2).'%';
                    }

                    if ($country_m > 0 || $country_f > 0) {
                        $foreign[] = array(
                                'country_name' => $country_name,
                                'program_name' => $program_name,
                                'sem_title' => $sem_title,
                                'country_m' => $country_m.' ('.$perc_country_m.')',
                                'country_f' => $country_f.' ('.$perc_country_f.')' 
                            );
                    } 
                }
            }
        }

        $foreign = array_map('unserialize', array_unique(array_map('serialize', $foreign)));

        return $foreign;
    }

    function ecell_foreign_student_enrollment_pdf($conn, $mpdf, $year) {
    	$session = $year.' - '.($year + 1);

    	$mpdf->AddPage();

		$html = "
			<html>
				<body>
					<h2 align='center'><u>SURVEY YEAR $session</u></h2>
					<table align='center' style='border-collapse:collapse;width:800px'>
						<tr>
		                    <th colspan='0' style='font-size:14;font-family:Times New Roman;text-align:left;border:1px solid #fff;border-bottom:1px solid black;height:20px;padding:5px'><b>Foreign Student Enrollment</b></th>
						</tr>
		                <tr>
		                    <th rowspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Country</b></th>
		                    <th rowspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Programme</b></th>
		                    <th rowspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Semester</b></th>
		                    <th colspan='2' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Number of Students Enrolled</b></th>
						</tr>
						<tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Male</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Female</b></th>
						</tr>
		";

		$foreign = ecell_foreign_student_enrollment($conn, $year);

    	foreach ($foreign as $frg) {
    		$country_name = $frg['country_name'];
	    	$program_name = $frg['program_name'];
	    	$sem_title = $frg['sem_title'];
	    	$country_m = $frg['country_m'];
	    	$country_f = $frg['country_f'];

	    	$html .= "
				<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$country_name</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$program_name</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$sem_title</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$country_m</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$country_f</td>
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

    function ecell_students_dasa($conn, $program_id_list, $year_of_joining_1, $year_of_joining_2) {
    	for ($year_of_joining = $year_of_joining_1; $year_of_joining <= $year_of_joining_2; $year_of_joining++) {
	    	foreach ($program_id_list as $prg) {
	    		$program_id = $prg['program_id'];

	    		$program_details = ecell_program_details($conn, $program_id);
	    		$program_name = $program_details['program_name'];
    		
		    	$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND DASA = 1 AND gender = 'Male'");
		        $stmt->bindParam(':program_id', $program_id);
		        $stmt->bindParam(':year_of_joining', $year_of_joining);
		        $stmt->execute();
		        $total_admitted_m = $stmt->fetchAll()[0]['COUNT(student_id)'];
		        
		        $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND DASA = 1 AND gender = 'Female'");
		        $stmt->bindParam(':program_id', $program_id);
		        $stmt->bindParam(':year_of_joining', $year_of_joining);
		        $stmt->execute();
		        $total_admitted_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

		        $total_admitted = $total_admitted_m + $total_admitted_f;

		        if ($total_admitted > 0) {
		            $perc_total_admitted = '100%';
		        } else {
		            $perc_total_admitted = '0%';
		        }

		        if ($total_admitted_m == 0 && $total_admitted_f == 0) {
		            $perc_total_admitted_m = '0%';
		            $perc_total_admitted_f = '0%';  
		        } else {
		            $perc_total_admitted_m = round((100 * $total_admitted_m / $total_admitted), 2).'%';
		            $perc_total_admitted_f = round((100 * $total_admitted_f / $total_admitted), 2).'%';
		        }

		        $dasa[] = array(
	        			'program_name' => $program_name,
	        			'year_of_joining' => $year_of_joining,
	                    'country_name' => 'Total',
	                    'country_total' => $total_admitted.' ('.$perc_total_admitted.')',
	                    'country_m' => $total_admitted_m.' ('.$perc_total_admitted_m.')',
	                    'country_f' => $total_admitted_f.' ('.$perc_total_admitted_f.')'
	                );

		        $stmt = $conn->prepare("SELECT DISTINCT dasa_country FROM student WHERE DASA = 1");
		        $stmt->execute();
		        $country_list = $stmt->fetchAll();

		        foreach ($country_list as $cnt) {
		            $country_name = $cnt['dasa_country'];
		            
		            $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND DASA = 1 AND gender = 'Male' AND dasa_country = :country_name");
		            $stmt->bindParam(':program_id', $program_id);
		            $stmt->bindParam(':year_of_joining', $year_of_joining);
		            $stmt->bindParam(':country_name', $country_name);
		            $stmt->execute();
		            $country_m = $stmt->fetchAll()[0]['COUNT(student_id)'];
		            
		            $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND DASA = 1 AND gender='Female' AND dasa_country = :country_name");
		            $stmt->bindParam(':program_id', $program_id);
		            $stmt->bindParam(':year_of_joining', $year_of_joining);
		            $stmt->bindParam(':country_name', $country_name);
		            $stmt->execute();
		            $country_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

		            if ($country_m == 0 && $country_f == 0) {
		                $perc_country_m = '0%';
		                $perc_country_f = '0%'; 
		            } else {
		                $perc_country_m = round((100 * $country_m / $total_admitted), 2).'%';
		                $perc_country_f = round((100 * $country_f / $total_admitted), 2).'%';
		            }

		            $country_total = $country_m + $country_f;
		            $perc_country_total = round((100 * $country_total / $total_admitted), 2).'%';

		            $dasa[] = array(
	            			'program_name' => $program_name,
	        				'year_of_joining' => $year_of_joining,
	                        'country_name' => $country_name,
	                        'country_total' => $country_total.' ('.$perc_country_total.')',
	                        'country_m' => $country_m.' ('.$perc_country_m.')',
	                        'country_f' => $country_f.' ('.$perc_country_f.')'
	                    );
		        }
		    }
	    }

	    return $dasa;
    }

    function ecell_students_dasa_pdf($conn, $mpdf, $program_id_list, $year_of_joining_1, $year_of_joining_2) {
    	$mpdf->AddPage();

		$html = "
			<html>
				<body>
					<h2 align='center'><u>DASA Students Distribution</u></h2>
					<table align='center' style='border-collapse:collapse;width:800px'>
		                <tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:25%'><b>Program</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:10%'><b>Year of Joining</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:20%'><b>Country</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:15%'><b>Total Admitted</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:15%'><b>Boys Admitted</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:15%'><b>Girls Admitted</b></th>
						</tr>
		";

		$dasa = ecell_students_dasa($conn, $program_id_list, $year_of_joining_1, $year_of_joining_2);

    	foreach ($dasa as $ds) {
    		$program_name = $ds['program_name'];
    		$year_of_joining = $ds['year_of_joining'];
    		$country_name = $ds['country_name'];
	    	$country_total = $ds['country_total'];
	    	$country_m = $ds['country_m'];
	    	$country_f = $ds['country_f'];

	    	$html .= "
				<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$program_name</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$year_of_joining</td>
			";

			if ($country_name == 'Total') {
				$html .= "
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'><b>$country_name</b></td>
				"; 
			} else {
				$html .= "
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$country_name</td>
				"; 
			}

			$html .= "	
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$country_total</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$country_m</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$country_f</td>
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

    function ecell_students_program_level($conn, $year_of_joining_1, $year_of_joining_2) {
    	$stmt = $conn->prepare("SELECT DISTINCT program_type FROM program");
    	$stmt->execute();
    	$program_type_list = $stmt->fetchAll();

    	for ($year_of_joining = $year_of_joining_1; $year_of_joining <= $year_of_joining_2; $year_of_joining++) {
    		$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE year = :year_of_joining");
            $stmt->bindParam(':year_of_joining', $year_of_joining);
            $stmt->execute();
            $total_admitted = $stmt->fetchAll()[0]['COUNT(student_id)'];

            foreach ($program_type_list as $prg) {
            	$program_type = $prg['program_type'];

	            $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student, program WHERE student.program_id = program.program_id AND student.year = :year_of_joining AND student.gender = 'Male' AND program.program_type = :program_type");
	            $stmt->bindParam(':year_of_joining', $year_of_joining);
	            $stmt->bindParam(':program_type', $program_type);
	            $stmt->execute();
	            $prog_m = $stmt->fetchAll()[0]['COUNT(student_id)'];

	            $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student, program WHERE student.program_id = program.program_id AND student.year = :year_of_joining AND student.gender = 'Female' AND program.program_type = :program_type");
	            $stmt->bindParam(':year_of_joining', $year_of_joining);
	            $stmt->bindParam(':program_type', $program_type);
	            $stmt->execute();
	            $prog_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

	            if ($prog_m == 0 && $prog_f == 0) {
	                $perc_prog_m = '0%';
	                $perc_prog_f = '0%';  
	            } else {
	                $perc_prog_m = round((100 * $prog_m / $total_admitted), 2).'%';
	                $perc_prog_f = round((100 * $prog_f / $total_admitted), 2).'%';
	            }

	            $program_level_arr[] = array(
	            		'program_level' => $program_type,
	            		'year_of_joining' => $year_of_joining,
	            		'boys_admitted' => $prog_m.' ('.$perc_prog_m.')',
	            		'girls_admitted' => $prog_f.' ('.$perc_prog_f.')'
	            	);
	        }
        }

        return $program_level_arr;
    }

    function ecell_students_program_level_pdf($conn, $mpdf, $year_of_joining_1, $year_of_joining_2) {
    	$mpdf->AddPage();

		$html = "
			<html>
				<body>
					<h2 align='center'><u>Program Level-wise Distribution of Students</u></h2>
					<table align='center' style='border-collapse:collapse;width:500px'>
		                <tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:35%'><b>Program</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:25%'><b>Year of Joining</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:20%'><b>Boys Admitted</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:20%'><b>Girls Admitted</b></th>
						</tr>
		";

		$program_level_list = ecell_students_program_level($conn, $year_of_joining_1, $year_of_joining_2);

    	foreach ($program_level_list as $prg) {
    		$program_level = $prg['program_level'];
    		$year_of_joining = $prg['year_of_joining'];
	    	$boys_admitted = $prg['boys_admitted'];
	    	$girls_admitted = $prg['girls_admitted'];

	    	$html .= "
				<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$program_level</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$year_of_joining</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$boys_admitted</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$girls_admitted</td>
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

    function ecell_students_program_dropouts($conn, $program_id_list, $year_of_joining_1, $year_of_joining_2) {
    	for ($year_of_joining = $year_of_joining_1; $year_of_joining <= $year_of_joining_2; $year_of_joining++) {
	    	foreach ($program_id_list as $prg) {
	    		$program_id = $prg['program_id'];

	    		$program_details = ecell_program_details($conn, $program_id);
	    		$program_name = $program_details['program_name'];
    		
		    	$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND gender = 'Male' AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'dropout')");
                $stmt->bindParam(':program_id', $program_id);
                $stmt->bindParam(':year_of_joining', $year_of_joining);
                $stmt->execute();
                $no_stud_m = $stmt->fetchAll()[0]['COUNT(student_id)'];

                $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND gender = 'Female' AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'dropout')");
                $stmt->bindParam(':program_id', $program_id);
                $stmt->bindParam(':year_of_joining', $year_of_joining);
                $stmt->execute();
                $no_stud_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

                $no_stud = $no_stud_m + $no_stud_f;

                if ($no_stud_m == 0 && $no_stud_f == 0) {
                    $perc_no_stud_m = '0%';
                    $perc_no_stud_f = '0%'; 
                } else {
                    $perc_no_stud_m = round((100 * $no_stud_m / $no_stud), 2).'%';
                    $perc_no_stud_f = round((100 * $no_stud_f / $no_stud), 2).'%';
                }

                $dropouts[] = array(
                		'program_name' => $program_name,
                		'year_of_joining' => $year_of_joining,
                		'total_dropouts' => $no_stud,
                		'male_dropouts' => $no_stud_m.' ('.$perc_no_stud_m.')',
                		'female_dropouts' => $no_stud_f.' ('.$perc_no_stud_f.')',
                	);
		    }
	    }

	    return $dropouts;
    }

    function ecell_students_program_dropouts_pdf($conn, $mpdf, $program_id_list, $year_of_joining_1, $year_of_joining_2) {
    	$mpdf->AddPage();

		$html = "
			<html>
				<body>
					<h2 align='center'><u>Programwise Dropouts Distribution</u></h2>
					<table align='center' style='border-collapse:collapse;width:800px'>
		                <tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:35%'><b>Program</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:20%'><b>Year of Joining</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:15%'><b>Total Dropouts</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:15%'><b>Male Dropouts</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:15%'><b>Female Dropouts</b></th>
						</tr>
		";

		$dropouts = ecell_students_program_dropouts($conn, $program_id_list, $year_of_joining_1, $year_of_joining_2);

    	foreach ($dropouts as $drp) {
    		$program_name = $drp['program_name'];
    		$year_of_joining = $drp['year_of_joining'];
	    	$total_dropouts = $drp['total_dropouts'];
	    	$male_dropouts = $drp['male_dropouts'];
	    	$female_dropouts = $drp['female_dropouts'];

	    	$html .= "
				<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$program_name</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$year_of_joining</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$total_dropouts</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$male_dropouts</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$female_dropouts</td>
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

    function ecell_students_category($conn, $category_id_list, $program_id_list, $year_of_joining_1, $year_of_joining_2, $category_type) {
    	for ($year_of_joining = $year_of_joining_1; $year_of_joining <= $year_of_joining_2; $year_of_joining++) {
    		foreach ($program_id_list as $prg) {
    			$program_id = $prg['program_id'];

    			$program_details = ecell_program_details($conn, $program_id);
    			$program_name = $program_details['program_name'];

    			$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND gender = 'Male'");
                $stmt->bindParam(':program_id', $program_id);
                $stmt->bindParam(':year_of_joining', $year_of_joining);
                $stmt->execute();
                $total_admitted_m = $stmt->fetchAll()[0]['COUNT(student_id)'];
                
                $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND gender = 'Female'");
                $stmt->bindParam(':program_id', $program_id);
                $stmt->bindParam(':year_of_joining', $year_of_joining);
                $stmt->execute();
                $total_admitted_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

                $total_admitted = $total_admitted_m + $total_admitted_f;
                
                if ($total_admitted_m == 0 && $total_admitted_f == 0) {
                    $perc_total_admitted_m = '0%';
                    $perc_total_admitted_f = '0%';  
                } else {
                    $perc_total_admitted_m = round((100 * $total_admitted_m / $total_admitted), 2).'%';
                    $perc_total_admitted_f = round((100 * $total_admitted_f / $total_admitted), 2).'%';
                }

                $category_arr[] = array(
                		'program_name' => $program_name,
                		'year_of_joining' => $year_of_joining,
                		'category_name' => 'Total',
                		'total_admitted' => $total_admitted,
	            		'boys_admitted' => $total_admitted_m.' ('.$perc_total_admitted_m.')',
	            		'girls_admitted' => $total_admitted_f.' ('.$perc_total_admitted_f.')'
                	);

    			foreach ($category_id_list as $cat) {
    				$category_id = $cat['category_id'];

    				$stmt = $conn->prepare("SELECT category_name FROM student_category WHERE category_id = :category_id");
    				$stmt->bindParam(':category_id', $category_id);
    				$stmt->execute();
    				$category_name = $stmt->fetchAll()[0]['category_name'];

    				if ($category_type == 'main') {
	    				$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND category_id = :category_id AND year = :year_of_joining AND gender = 'Male'");
		                $stmt->bindParam(':program_id', $program_id);
		                $stmt->bindParam(':category_id', $category_id);
		                $stmt->bindParam(':year_of_joining', $year_of_joining);
		                $stmt->execute();
		                $category_admitted_m = $stmt->fetchAll()[0]['COUNT(student_id)'];
		                
		                $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND category_id = :category_id AND year = :year_of_joining AND gender = 'Female'");
		                $stmt->bindParam(':program_id', $program_id);
		                $stmt->bindParam(':category_id', $category_id);
		                $stmt->bindParam(':year_of_joining', $year_of_joining);
		                $stmt->execute();
		                $category_admitted_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

		                $category_admitted = $category_admitted_m + $category_admitted_f;
		                
		                if ($category_admitted_m == 0 && $category_admitted_f == 0) {
		                	$perc_category_admitted = '0%';
		                    $perc_category_admitted_m = '0%';
		                    $perc_category_admitted_f = '0%';  
		                } else {
		                	$perc_category_admitted = round((100 * $category_admitted / $total_admitted), 2).'%';
		                    $perc_category_admitted_m = round((100 * $category_admitted_m / $total_admitted), 2).'%';
		                    $perc_category_admitted_f = round((100 * $category_admitted_f / $total_admitted), 2).'%';
		                }

		                $category_arr[] = array(
			            		'program_name' => $program_name,
			            		'year_of_joining' => $year_of_joining,
			            		'category_name' => $category_name,
			            		'total_admitted' => $category_admitted.' ('.$perc_category_admitted.')',
			            		'boys_admitted' => $category_admitted_m.' ('.$perc_category_admitted_m.')',
			            		'girls_admitted' => $category_admitted_f.' ('.$perc_category_admitted_f.')'
			            	);
		            } else if ($category_type == 'admission') {
		            	$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND admission_category_id = :category_id AND year = :year_of_joining AND gender = 'Male'");
		                $stmt->bindParam(':program_id', $program_id);
		                $stmt->bindParam(':category_id', $category_id);
		                $stmt->bindParam(':year_of_joining', $year_of_joining);
		                $stmt->execute();
		                $category_admitted_m = $stmt->fetchAll()[0]['COUNT(student_id)'];
		                
		                $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND admission_category_id = :category_id AND year = :year_of_joining AND gender = 'Female'");
		                $stmt->bindParam(':program_id', $program_id);
		                $stmt->bindParam(':category_id', $category_id);
		                $stmt->bindParam(':year_of_joining', $year_of_joining);
		                $stmt->execute();
		                $category_admitted_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

		                $category_admitted = $category_admitted_m + $category_admitted_f;
		                
		                if ($category_admitted_m == 0 && $category_admitted_f == 0) {
		                	$perc_category_admitted = '0%';
		                    $perc_category_admitted_m = '0%';
		                    $perc_category_admitted_f = '0%';  
		                } else {
		                	$perc_category_admitted = round((100 * $category_admitted / $total_admitted), 2).'%';
		                    $perc_category_admitted_m = round((100 * $category_admitted_m / $total_admitted), 2).'%';
		                    $perc_category_admitted_f = round((100 * $category_admitted_f / $total_admitted), 2).'%';
		                }

		                $category_arr[] = array(
			            		'program_name' => $program_name,
			            		'year_of_joining' => $year_of_joining,
			            		'category_name' => $category_name,
			            		'total_admitted' => $category_admitted.' ('.$perc_category_admitted.')',
			            		'boys_admitted' => $category_admitted_m.' ('.$perc_category_admitted_m.')',
			            		'girls_admitted' => $category_admitted_f.' ('.$perc_category_admitted_f.')'
			            	);
		            }
    			}

    			if (count($category_id_list) > 1) {
    				$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND gender = 'Male' AND muslim_minority = 1");
	                $stmt->bindParam(':program_id', $program_id);
	                $stmt->bindParam(':year_of_joining', $year_of_joining);
	                $stmt->execute();
	                $muslim_m = $stmt->fetchAll()[0]['COUNT(student_id)'];

	                $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND gender = 'Female' AND muslim_minority = 1");
	                $stmt->bindParam(':program_id', $program_id);
	                $stmt->bindParam(':year_of_joining', $year_of_joining);
	                $stmt->execute();
	                $muslim_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

	                $muslim = $muslim_m + $muslim_f;

	                if ($muslim_m == 0 && $muslim_f == 0) {
	                	$perc_muslim = '0%';
	                    $perc_muslim_m = '0%';
	                    $perc_muslim_f = '0%'; 
	                } else {
	                	$perc_muslim = round((100 * $muslim / $total_admitted), 2).'%';
	                    $perc_muslim_m = round((100 * $muslim_m / $total_admitted), 2).'%';
	                    $perc_muslim_f = round((100 * $muslim_f / $total_admitted), 2).'%';
	                }

	                $category_arr[] = array(
		            		'program_name' => $program_name,
		            		'year_of_joining' => $year_of_joining,
		            		'category_name' => 'Muslim Minority',
		            		'total_admitted' => $muslim.' ('.$perc_muslim.')',
		            		'boys_admitted' => $muslim_m.' ('.$perc_muslim_m.')',
		            		'girls_admitted' => $muslim_f.' ('.$perc_muslim_f.')'
		            	);

	                $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND gender = 'Male' AND other_minority = 1");
	                $stmt->bindParam(':program_id', $program_id);
	                $stmt->bindParam(':year_of_joining', $year_of_joining);
	                $stmt->execute();
	                $other_m = $stmt->fetchAll()[0]['COUNT(student_id)'];

	                $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE program_id = :program_id AND year = :year_of_joining AND gender = 'Female' AND other_minority = 1");
	                $stmt->bindParam(':program_id', $program_id);
	                $stmt->bindParam(':year_of_joining', $year_of_joining);
	                $stmt->execute();
	                $other_f = $stmt->fetchAll()[0]['COUNT(student_id)'];

	                $other = $other_m + $other_f;

	                if ($other_m == 0 && $other_f == 0) {
	                	$perc_other = '0%';
	                    $perc_other_m = '0%';
	                    $perc_other_f = '0%'; 
	                } else {
	                	$perc_other = round((100 * $other / $total_admitted), 2).'%';
	                    $perc_other_m = round((100 * $other_m / $total_admitted), 2).'%';
	                    $perc_other_f = round((100 * $other_f / $total_admitted), 2).'%';
	                }

	                $category_arr[] = array(
		            		'program_name' => $program_name,
		            		'year_of_joining' => $year_of_joining,
		            		'category_name' => 'Other Minority',
		            		'total_admitted' => $other.' ('.$perc_other.')',
		            		'boys_admitted' => $other_m.' ('.$perc_other_m.')',
		            		'girls_admitted' => $other_f.' ('.$perc_other_f.')'
		            	);
    			}
    		}
    	}

    	return $category_arr;
    }

    function ecell_students_category_pdf($conn, $mpdf, $category_id_list, $program_id_list, $year_of_joining_1, $year_of_joining_2, $category_type) {
    	$mpdf->AddPage();

		$html = "
			<html>
				<body>
					<h2 align='center'><u>Categorywise Distribution of Students</u></h2>
					<table align='center' style='border-collapse:collapse;width:800px'>
		                <tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:34%'><b>Program</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:12%'><b>Year of Joining</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:18%'><b>Category</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:12%'><b>Total Admitted</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:12%'><b>Boys Admitted</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:12%'><b>Girls Admitted</b></th>
						</tr>
		";

		$categories = ecell_students_category($conn, $category_id_list, $program_id_list, $year_of_joining_1, $year_of_joining_2, $category_type);

    	foreach ($categories as $cat) {
    		$program_name = $cat['program_name'];
    		$year_of_joining = $cat['year_of_joining'];
    		$category_name = $cat['category_name'];
	    	$total_admitted = $cat['total_admitted'];
	    	$boys_admitted = $cat['boys_admitted'];
	    	$girls_admitted = $cat['girls_admitted'];

	    	$html .= "
				<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$program_name</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$year_of_joining</td>
			";

			if ($category_name == 'Total') {
				$html .= "
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'><b>$category_name</b></td>
				";
			} else {
				$html .= "
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$category_name</td>
				";
			}

			$html .= "
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$total_admitted</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$boys_admitted</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$girls_admitted</td>
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

    function ecell_students_active_dropout($conn, $category_id_list, $year_of_joining_1, $year_of_joining_2, $category_type) {
    	for ($year_of_joining = $year_of_joining_1; $year_of_joining <= $year_of_joining_2; $year_of_joining++) {
    		$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE year = :year_of_joining");
            $stmt->bindParam(':year_of_joining', $year_of_joining);
            $stmt->execute();
            $total_admitted = $stmt->fetchAll()[0]['COUNT(student_id)'];

			foreach ($category_id_list as $cat) {
				$category_id = $cat['category_id'];

				$stmt = $conn->prepare("SELECT category_name FROM student_category WHERE category_id = :category_id");
				$stmt->bindParam(':category_id', $category_id);
				$stmt->execute();
				$category_name = $stmt->fetchAll()[0]['category_name'];

				if ($category_type == 'main') {
    				$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE year = :year_of_joining AND category_id = :category_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name <> 'dropout')");
                    $stmt->bindParam(':year_of_joining', $year_of_joining);
                    $stmt->bindParam(':category_id', $category_id);
                    $stmt->execute();
                    $cat_study = $stmt->fetchAll()[0]['COUNT(student_id)'];

                    $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE year = :year_of_joining AND category_id = :category_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'dropout')");
                    $stmt->bindParam(':year_of_joining', $year_of_joining);
                    $stmt->bindParam(':category_id', $category_id);
                    $stmt->execute();
                    $cat_drop = $stmt->fetchAll()[0]['COUNT(student_id)'];

                    if ($cat_study == 0 && $cat_drop == 0) {
                        $perc_cat_study = '0%';
                        $perc_cat_drop = '0%';  
                    } else {
                        $perc_cat_study = round((100 * $cat_study / $total_admitted), 2).'%';
                        $perc_cat_drop = round((100 * $cat_drop / $total_admitted), 2).'%';
                    }

	                $study_drop_arr[] = array(
		            		'year_of_joining' => $year_of_joining,
		            		'category_name' => $category_name,
		            		'active' => $cat_study.' ('.$perc_cat_study.')',
		            		'dropout' => $cat_drop.' ('.$perc_cat_drop.')'
		            	);
	            } else if ($category_type == 'admission') {
	            	$stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE year = :year_of_joining AND admission_category_id = :category_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name <> 'dropout')");
                    $stmt->bindParam(':year_of_joining', $year_of_joining);
                    $stmt->bindParam(':category_id', $category_id);
                    $stmt->execute();
                    $cat_study = $stmt->fetchAll()[0]['COUNT(student_id)'];

                    $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE year = :year_of_joining AND admission_category_id = :category_id AND status_value_id IN (SELECT status_value.status_value_id FROM status_value, status WHERE status_value.status_id = status.status_id AND status.status_name = 'dropout')");
                    $stmt->bindParam(':year_of_joining', $year_of_joining);
                    $stmt->bindParam(':category_id', $category_id);
                    $stmt->execute();
                    $cat_drop = $stmt->fetchAll()[0]['COUNT(student_id)'];

                    if ($cat_study == 0 && $cat_drop == 0) {
                        $perc_cat_study = '0%';
                        $perc_cat_drop = '0%';  
                    } else {
                        $perc_cat_study = round((100 * $cat_study / $total_admitted), 2).'%';
                        $perc_cat_drop = round((100 * $cat_drop / $total_admitted), 2).'%';
                    }

	                $study_drop_arr[] = array(
		            		'year_of_joining' => $year_of_joining,
		            		'category_name' => $category_name,
		            		'active' => $cat_study.' ('.$perc_cat_study.')',
		            		'dropout' => $cat_drop.' ('.$perc_cat_drop.')'
		            	);
	            }
    		}
    	}

    	return $study_drop_arr;
    }

    function ecell_students_active_dropout_pdf($conn, $mpdf, $category_id_list, $program_id_list, $year_of_joining_1, $year_of_joining_2, $category_type) {
    	$mpdf->AddPage();

		$html = "
			<html>
				<body>
					<h2 align='center'><u>Distribution of Active and Dropout Students</u></h2>
					<table align='center' style='border-collapse:collapse;width:600px'>
		                <tr>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:20%'><b>Year of Joining</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:30%'><b>Category</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:25%'><b>Currently Studying</b></th>
		                    <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:25%'><b>Dropped Out</b></th>
						</tr>
		";

		$study_drop = ecell_students_active_dropout($conn, $category_id_list, $year_of_joining_1, $year_of_joining_2, $category_type);

    	foreach ($study_drop as $std) {
    		$year_of_joining = $std['year_of_joining'];
    		$category_name = $std['category_name'];
	    	$active = $std['active'];
	    	$dropout = $std['dropout'];

	    	$html .= "
				<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$year_of_joining</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$category_name</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$active</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$dropout</td>
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

    function ecell_students_state_board($conn, $year_of_joining_1, $year_of_joining_2, $params, $category_type) {
    	for ($year_of_joining = $year_of_joining_1; $year_of_joining <= $year_of_joining_2; $year_of_joining++) {
    		$sql = "SELECT COUNT(student_id) FROM student WHERE 1";
	        foreach ($params as $key => $value) {
	            if ($key == 'percentage_10' || $key == 'percentage_12') {
	                $sql .= sprintf(' AND `%s` >= :%s', $key, $key);
	            } else {
	                $sql .= sprintf(' AND `%s` = :%s', $key, $key);
	            }
	        }

	        $stmt = $conn->prepare("SELECT COUNT(student_id) FROM student WHERE year = :year_of_joining");
	        $stmt->bindParam(':year_of_joining', $year_of_joining);
	        $stmt->execute();
	        $total_admitted = $stmt->fetchAll()[0]['COUNT(student_id)'];

	        $stmt = $conn->prepare($sql." AND gender = 'Male' AND year = :year_of_joining");
	        foreach ($params as $key => $value) {
	            $stmt->bindValue(':'.$key, $value);
	        }
	        $stmt->bindValue(':year_of_joining', $year_of_joining);
	        $stmt->execute();
	        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
	        $total_m = $row[0]['COUNT(student_id)'];

	        $stmt = $conn->prepare($sql." AND gender = 'Female' AND year = :year_of_joining");
	        foreach ($params as $key => $value) {
	            $stmt->bindValue(':'.$key, $value);
	        }
	        $stmt->bindValue(':year_of_joining', $year_of_joining);
	        $stmt->execute();
	        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
	        $total_f = $row[0]['COUNT(student_id)'];

	        if ($total_m == 0 && $total_f == 0) {
	            $perc_total_m = '0%';
	            $perc_total_f = '0%';   
	        } else {
	            $perc_total_m = round((100 * $total_m / $total_admitted), 2).'%';
	            $perc_total_f = round((100 * $total_f / $total_admitted), 2).'%';
	        }

	        $category_arr[] = array(
	        		'year_of_joining' => $year_of_joining,
	        		'category_name' => 'Total',
	        		'total_admitted' => $total_admitted,
	        		'boys_admitted' => $total_m.' ('.$perc_total_m.')',
	        		'girls_admitted' => $total_f.' ('.$perc_total_f.')'
	        	);

	        $stmt = $conn->prepare("SELECT * FROM student_category");
	        $stmt->execute();
	        $category_list = $stmt->fetchAll();

	        if ($category_type == 'main') {
	            foreach ($category_list as $cat) {
	            	$category_id = $cat['category_id'];
	                $category_name = $cat['category_name'];
	                
	                $stmt = $conn->prepare($sql." AND gender = 'Male' AND category_id = :category_id AND year = :year_of_joining");
	                foreach ($params as $key => $value) {
	                    $stmt->bindValue(':'.$key, $value);
	                }
	                $stmt->bindValue(':category_id', $category_id);
	                $stmt->bindValue(':year_of_joining', $year_of_joining);
	                $stmt->execute();
	                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
	                $cat_m = $row[0]['COUNT(student_id)'];

	                $stmt = $conn->prepare($sql." AND gender = 'Female' AND category_id = :category_id AND year = :year_of_joining");
	                foreach ($params as $key => $value) {
	                    $stmt->bindValue(':'.$key, $value);
	                }
	                $stmt->bindValue(':category_id', $category_id);
	                $stmt->bindValue(':year_of_joining', $year_of_joining);
	                $stmt->execute();
	                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
	                $cat_f = $row[0]['COUNT(student_id)'];

	                $cat_total = $cat_m + $cat_f;

	                if ($cat_m == 0 && $cat_f == 0) {
	                	$perc_cat_total = '0%';
	                    $perc_cat_m = '0%';
	                    $perc_cat_f = '0%'; 
	                } else {
	                	$perc_cat_total = round((100 * $cat_total / $total_admitted), 2).'%';
	                    $perc_cat_m = round((100 * $cat_m / $total_admitted), 2).'%';
	                    $perc_cat_f = round((100 * $cat_f / $total_admitted), 2).'%';
	                }

	                $category_arr[] = array(
	                		'year_of_joining' => $year_of_joining,
	                		'category_name' => $category_name,
	                		'total_admitted' => $cat_total.' ('.$perc_cat_total.')',
	                		'boys_admitted' => $cat_m.' ('.$perc_cat_m.')',
	                		'girls_admitted' => $cat_f.' ('.$perc_cat_f.')'
	                	);
	            }
	        } else if ($category_type == 'admission') {
	            foreach ($category_list as $cat) {
	            	$category_id = $cat['category_id'];
	                $category_name = $cat['category_name'];
	                
	                $stmt = $conn->prepare($sql." AND gender = 'Male' AND admission_category_id = :category_id AND year = :year_of_joining");
	                foreach ($params as $key => $value) {
	                    $stmt->bindValue(':'.$key, $value);
	                }
	                $stmt->bindValue(':category_id', $category_id);
	                $stmt->bindValue(':year_of_joining', $year_of_joining);
	                $stmt->execute();
	                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
	                $cat_m = $row[0]['COUNT(student_id)'];

	                $stmt = $conn->prepare($sql." AND gender = 'Female' AND admission_category_id = :category_id AND year = :year_of_joining");
	                foreach ($params as $key => $value) {
	                    $stmt->bindValue(':'.$key, $value);
	                }
	                $stmt->bindValue(':category_id', $category_id);
	                $stmt->bindValue(':year_of_joining', $year_of_joining);
	                $stmt->execute();
	                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
	                $cat_f = $row[0]['COUNT(student_id)'];

	                $cat_total = $cat_m + $cat_f;

	                if ($cat_m == 0 && $cat_f == 0) {
	                	$perc_cat_total = '0%';
	                    $perc_cat_m = '0%';
	                    $perc_cat_f = '0%'; 
	                } else {
	                	$perc_cat_total = round((100 * $cat_total / $total_admitted), 2).'%';
	                    $perc_cat_m = round((100 * $cat_m / $total_admitted), 2).'%';
	                    $perc_cat_f = round((100 * $cat_f / $total_admitted), 2).'%';
	                }

	                $category_arr[] = array(
	                		'year_of_joining' => $year_of_joining,
	                		'category_name' => $category_name,
	                		'total_admitted' => $cat_total.' ('.$perc_cat_total.')',
	                		'boys_admitted' => $cat_m.' ('.$perc_cat_m.')',
	                		'girls_admitted' => $cat_f.' ('.$perc_cat_f.')'
	                	);
	            }
	        }
	    }

	    return $category_arr;
    }

    function ecell_students_state_board_pdf($conn, $mpdf, $year_of_joining_1, $year_of_joining_2, $params, $category_type) {
    	$mpdf->AddPage();

		$html = "
			<html>
				<body>
					<h2 align='center'><u>Categorywise Distribution of Students</u></h2>
					<table align='center' style='border-collapse:collapse;width:800px'>
		";

		$state_id = $params['perm_state_id'];

		$stmt = $conn->prepare("SELECT state_name FROM state WHERE state_id = :state_id");
        $stmt->bindParam(':state_id', $state_id);
        $stmt->execute();
        $state_name = $stmt->fetchAll()[0]['state_name'];

		$html .= "
			<tr>
                <td colspan='100%' style='font-size:14;font-family:Times New Roman;text-align:left;border:1px solid #fff;border-bottom:1px solid black;height:20px'><b>State :</b> $state_name</th>
			</tr>
            <tr>
                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:15%'><b>Year of Joining</b></th>
                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:25%'><b>Category</b></th>
                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:20%'><b>Total Admitted</b></th>
                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:20%'><b>Boys Admitted</b></th>
                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px;width:20%'><b>Girls Admitted</b></th>
			</tr>
		";

		$state_board_arr = ecell_students_state_board($conn, $year_of_joining_1, $year_of_joining_2, $params, $category_type);

    	foreach ($state_board_arr as $stb) {
    		$year_of_joining = $stb['year_of_joining'];
    		$category_name = $stb['category_name'];
    		$total_admitted = $stb['total_admitted'];
	    	$boys_admitted = $stb['boys_admitted'];
	    	$girls_admitted = $stb['girls_admitted'];

	    	$html .= "
				<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$year_of_joining</td>
			";

			if ($category_name == 'Total') {
				$html .= "
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'><b>$category_name</b></td>
				";
			} else {
				$html .= "
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$category_name</td>
				";
			}

			$html .= "
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$total_admitted</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$boys_admitted</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$girls_admitted</td>
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

    function ecell_semester_result_summary($conn, $sem_id) {
    	$sem_details = ecell_sem_details($conn, $sem_id);

    	$sem_title = $sem_details['sem_title'];
    	$sem_title = preg_replace('/\D/', '', $sem_title);

    	$program_id = $sem_details['program_id'];
    	$year = $sem_details['year'];
    	$sem_code = $sem_details['sem_code'];

    	$program_details = ecell_program_details($conn, $program_id);
    	$program_name = $program_details['program_name'];

    	$sem_code_name = ecell_sem_code_description($conn, $sem_code);

    	$session = $sem_code_name.' '.$year;

        $stmt = $conn->prepare("SELECT COUNT(DISTINCT student_id) FROM results WHERE sem_id = :sem_id AND exam_type = 'END'");
        $stmt->bindParam(':sem_id', $sem_id);
        $stmt->execute();
        $total_students = $stmt->fetchAll()[0]['COUNT(DISTINCT student_id)'];

        $stmt = $conn->prepare("SELECT DISTINCT course_id FROM results WHERE sem_id = :sem_id AND exam_type = 'END'");
        $stmt->bindParam(':sem_id', $sem_id);
        $stmt->execute();
        $course_list = $stmt->fetchAll();
        
        foreach ($course_list as $crs) {
            $course_id = $crs['course_id'];

            $course_details = ecell_course_details($conn, $course_id);

            $course_code = $course_details['course_code'];
            $course_name = $course_details['course_name'];

            $stmt = $conn->prepare("SELECT MIN(date_of_exam) FROM results WHERE sem_id = :sem_id AND course_id = :course_id AND exam_type = 'END'");
            $stmt->bindParam(':sem_id', $sem_id);
	        $stmt->bindParam(':course_id', $course_id);
	        $stmt->execute();
	        $date_of_exam = $stmt->fetchAll()[0]['MIN(date_of_exam)'];
            
            if ($date_of_exam == '0000-00-00') {
            	$date_of_exam = 'NA';
            } else {
            	$date_of_exam = date('d/m/Y', strtotime($date_of_exam));
            }

            $stmt = $conn->prepare("SELECT faculty_name FROM faculty, faculty_course WHERE faculty_course.faculty_id = faculty.faculty_id AND faculty_course.course_id = :course_id");
            $stmt->bindParam(':course_id', $course_id);
            $stmt->execute();
            $faculty_list = $stmt->fetchAll();
            
            $faculty_name = '';
            foreach ($faculty_list as $fac => $val) {
                if ($fac == end(array_keys($faculty_list))) {
                    $faculty_name .= ($val['faculty_name']);
                } else {
                    $faculty_name .= ($val['faculty_name'].' / ');
                }
            }

            $stmt = $conn->prepare("SELECT * FROM grades ORDER BY credit DESC, grade");
            $stmt->execute();
            $grades_possible = $stmt->fetchAll();

            $grade_count_theory = '';
            $grade_count_lab = '';
            foreach ($grades_possible as $grd) {
            	$grade_id = $grd['grade_id'];
                $grade_name = $grd['grade'];

                if ($grade_name == 'NO' || $grade_name == '-' || $grade_name == 'F(Ufm)') {
                    continue;
                }

                $stmt = $conn->prepare("SELECT COUNT(DISTINCT student_id) FROM results WHERE sem_id = :sem_id AND course_id = :course_id AND theory_grade = :grade_id AND exam_type = 'END' ORDER BY `timestamp` DESC");
                $stmt->bindParam(':sem_id', $sem_id);
                $stmt->bindParam(':course_id', $course_id);
                $stmt->bindParam(':grade_id', $grade_id);
                $stmt->execute();
                $gc_t = $stmt->fetchAll()[0]['COUNT(DISTINCT student_id)'];

                if ($grade_name != 'F' && $grade_name != 'F(Ab)') {
                    $grade_count_theory .= ($grade_name.' = '.$gc_t.'<br>');
                } else if ($grade_name == 'F') {
                    $grade_count_theory .= ($grade_name.' = '.$gc_t);
                } else {
                    $grade_count_theory .= ('+'.$gc_t);
                }

                $stmt = $conn->prepare("SELECT COUNT(DISTINCT student_id) FROM results WHERE sem_id = :sem_id AND course_id = :course_id AND lab_grade = :grade_id AND exam_type = 'END' ORDER BY `timestamp` DESC");
                $stmt->bindParam(':sem_id', $sem_id);
                $stmt->bindParam(':course_id', $course_id);
                $stmt->bindParam(':grade_id', $grade_id);
                $stmt->execute();
                $gc_l = $stmt->fetchAll()[0]['COUNT(DISTINCT student_id)'];

                if ($grade_name != 'F' && $grade_name != 'F(Ab)') {
                    $grade_count_lab .= ($grade_name.' = '.$gc_l.'<br>');
                } else if ($grade_name == 'F') {
                    $grade_count_lab .= ($grade_name.' = '.$gc_l);
                } else {
                    $grade_count_lab .= ('+'.$gc_l);
                }
            }

            $results_info[] = array(
	                'course_name' => $course_name,
	                'course_code' => $course_code,
	                'date_of_exam' => $date_of_exam,
	                'grade_count_theory' => $grade_count_theory,
	                'grade_count_lab' => $grade_count_lab,
	                'faculty_name' => $faculty_name
	            );
        }

        $stmt = $conn->prepare("SELECT MAX(date_of_declaration) FROM results WHERE sem_id = :sem_id AND exam_type = 'END'");
        $stmt->bindParam(':sem_id', $sem_id);
        $stmt->execute();
        $date_of_declaration = $stmt->fetchAll()[0]['MAX(date_of_declaration)'];

        if ($date_of_declaration == '0000-00-00') {
        	$date_of_declaration = 'NA';
        } else {
        	$date_of_declaration = date('d/m/Y', strtotime($date_of_declaration));
        }

        $sem_result_summary = array(
        		'session' => $session,
        		'program_name' => $program_name,
        		'sem_title' => $sem_title,
        		'total_students' => $total_students,
        		'results_info' => $results_info,
        		'date_of_declaration' => $date_of_declaration
        	);

        return $sem_result_summary;
    }

    function ecell_semester_result_summary_pdf($conn, $mpdf, $sem_id) {
    	$mpdf->AddPage();

    	$sem_result_summary = ecell_semester_result_summary($conn, $sem_id);

    	$session = $sem_result_summary['session'];
    	$program_name = $sem_result_summary['program_name'];
    	$sem_title = $sem_result_summary['sem_title'];
    	$total_students = $sem_result_summary['total_students'];
    	$date_of_declaration = $sem_result_summary['date_of_declaration'];
    	$results_info = $sem_result_summary['results_info'];

		$html = "
			<html>
				<body>
					<h2 align='center'><u>Semester Result Summary ($session)</u></h2>
					<table align='center' style='border-collapse:collapse;width:800px'>
			            <tr>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Program</b></th>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Semester</b></th>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Total Students</b></th>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Date of Result Declaration</b></th>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Paper Title</b></th>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Paper Code (8 characters)</b></th>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Date of End Semester Exam</b></th>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Paperwise Grade Summary (Theory)</b></th>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Paperwise Grade Summary (Lab)</b></th>
			                <th style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'><b>Name of Faculty</b></th>
						</tr>
						<tr>
							<td rowspan='100%' style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$program_name</td>
							<td rowspan='100%' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$sem_title</td>
							<td rowspan='100%' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$total_students</td>
							<td rowspan='100%' style='font-size:13;font-family:Times New Roman;text-align:center;border:1px solid black;height:20px;padding:5px'>$date_of_declaration</td>
						</tr>
		";

    	foreach ($results_info as $res) {	
    		$course_name = $res['course_name'];
    		$course_code = $res['course_code'];
    		$date_of_exam = $res['date_of_exam'];
    		$grade_count_theory = $res['grade_count_theory'];
    		$grade_count_lab = $res['grade_count_lab'];
    		$faculty_name = $res['faculty_name'];

	    	$html .= "
	    		<tr>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$course_name</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$course_code</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$date_of_exam</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$grade_count_theory</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$grade_count_lab</td>
					<td style='font-size:13;font-family:Times New Roman;text-align:left;border:1px solid black;height:20px;padding:5px'>$faculty_name</td>
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
?>
