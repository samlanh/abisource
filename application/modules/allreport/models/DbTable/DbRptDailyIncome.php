<?php

class Allreport_Model_DbTable_DbRptDailyIncome extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_service';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    function getRate(){
    	$db= $this->getAdapter();
    	$sql = "select reil from rms_exchange_rate where active = 1 limit 1";
    	return $db->fetchOne($sql);
    }
    
    public function getDailyIncomeEnglishFulltime($search){
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("st.`branch_id`");
    	 
    	$sql = "SELECT
			    	(SELECT branch_namekh from rms_branch where br_id = st.branch_id LIMIT 1) as branch_name,
			    	(SELECT last_name from rms_users as u where u.id = sp.user_id LIMIT 1) as user_name,
			    	st.`stu_code`,
			    	st.`stu_enname`,
			    	st.`stu_khname`,
			    	(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` LIMIT 1) AS sex,
			    	st.`tel`,
			    	sp.id,
					sp.time,
					sp.material_fee ,
					sp.tuition_fee_after_discount,
					sp.`create_date`,
			    	sp.`receipt_number`,
					sp.`student_id`,
			    	(SELECT major_enname from rms_major where major_id =  sp.`grade` LIMIT 1) as grade,
					(SELECT  `v`.`name_kh` FROM `rms_view` v WHERE ((`v`.`type` = 18) AND (`v`.`key_code` = `sp`.`payment_method`)) LIMIT 1) AS `payment_method_title`,
			    	sp.`tuition_fee`,
			    	sp.`discount_percent`,
			    	sp.`discount_fix`,
			    	sp.`total_payment`,
			    	sp.`admin_fee`,
			    	sp.`other_fee`,
			    	sp.is_void,
			    	sp.`grand_total_paid_amount`,
			    	sp.`grand_total_balance`,
			    	sp.`note`,
			    	sp.is_complete,
			    	sp.`payment_method`
			    FROM
			    	`rms_student` AS st INNER JOIN
			    	v_dialyenglishfulltime as sp
					ON (st.`stu_id`=sp.`student_id`)

			    WHERE
					1
			    	$branch_id
    		";
    	 
    	$where = " ";
    	 
    	if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 10:30:00'";
    	}
    	else if($search['shift']==2){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 10:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 16:30:00'";
    	}
    	else if($search['shift']==3){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 16:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where .= " AND ".$from_date." AND ".$to_date;
    	 
    	
    	if(!empty($search['from_receipt']) && !empty($search['to_receipt'])){
    		$where .= " AND receipt_number between '".$search['from_receipt']."' AND '".$search['to_receipt']."'";
    	}
    	 
    	$order=" ORDER BY sp.`receipt_number` ASC,sp.`grade` ASC,sp.id ASC ";
    		
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    				 
    	if($search['degree_en_ft'] > 0){
    		$where.= " AND sp.`degree` = ".$search['degree_en_ft'];
    	}
    	if($search['grade_en_ft'] > 0){
    		$where.= " AND sp.`grade` = ".$search['grade_en_ft'];
    	}
	    if($search['room'] > 0){
	   		$where.= " AND sp.`room_id` = ".$search['room'];
	    }
	    if($search['branch'] > 0){
	    	$where.= " AND st.`branch_id` = ".$search['branch'];
	    }
	    if($search['user'] > 0){
	    	$where.= " AND sp.`user_id` = ".$search['user'];
	    }
	    if(!empty($search['payment_method'])){
	    	$where.= " AND sp.`payment_method` = ".$search['payment_method'];
	    }
	    return $db->fetchAll($sql.$where.$order);
    }
    
    
    public function getDailyIncomeEnglishParttime($search){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	
    	$sql = "SELECT
					sp.*,
					sp.id,
					(select branch_namekh from rms_branch where br_id = sp.branch_id LIMIT 1) as branch_name,
			    	(select last_name from rms_users as u where u.id = sp.user_id LIMIT 1) as user_name,
			    	
					sp.`student_id`,
					st.`stu_code`,
					st.`stu_enname`,
					st.`stu_khname`,
					(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` LIMIT 1) AS sex,
					st.`tel`,
					sp.`create_date`,
					sp.`is_new`,
					sp.`receipt_number`,
					(select en_name from rms_dept where dept_id = sp.`degree` LIMIT 1) as degree,
					(select major_enname from rms_major where major_id =  sp.`grade` LIMIT 1) as grade,
					(select room_name from rms_room where rms_room.room_id = sp.`room_id` LIMIT 1) as room,
					sp.`time`,
					
					sp.`tuition_fee`,
					sp.`discount_percent`,
					sp.`discount_fix`,
					sp.`total_payment`,
					sp.`admin_fee`,
					sp.`other_fee`,
					
					sp.`grand_total_payment`,
					sp.`grand_total_paid_amount`,
					sp.`grand_total_balance`,
					sp.`note`,
					sp.is_subspend,
					
					sp.is_void,
					
					spd.`start_date`,
					spd.`validate`,
					spd.`payment_term`,
					spd.is_complete,
					spd.qty,
					
					(SELECT name_kh FROM rms_view WHERE TYPE=18 AND key_code = sp.`payment_method` LIMIT 1) AS payment_method_title,
			    	sp.`payment_method`,
			    	sp.`payment_note`
			    	
				FROM 
					`rms_student_payment` AS sp,
					`rms_student_paymentdetail` AS spd,
					`rms_student` AS st
				WHERE 
					sp.id=spd.`payment_id`
					AND st.`stu_id`=sp.`student_id`
					AND sp.`payfor_type`=2
					AND spd.`service_id`=4
					
					$branch_id
    		  ";
    	
    	$where = " ";
    	
    	if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 10:30:00'";
    	}
    	else if($search['shift']==2){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 10:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 16:30:00'";
    	}
    	else if($search['shift']==3){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 16:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['from_receipt']) && !empty($search['to_receipt'])){
    		$where .= " AND receipt_number between '".$search['from_receipt']."' AND '".$search['to_receipt']."'";
    	}
    	
    	$order=" ORDER BY sp.`receipt_number` ASC ";
					
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['degree_gep'] > 0){
    		$where.= " AND sp.`degree` = ".$search['degree_gep'];
    	}
    	if($search['grade_gep'] > 0){
    		$where.= " AND sp.`grade` = ".$search['grade_gep'];
    	}
    	if($search['room'] > 0){
    		$where.= " AND sp.`room_id` = ".$search['room'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND sp.`user_id` = ".$search['user'];
    	}
    	
    	if(!empty($search['payment_method'])){
    		$where.= " AND sp.`payment_method` = ".$search['payment_method'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    	 
    }

    public function getDailyIncomeKhmerFulltime($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("st.`branch_id`");
    
    	$sql = "SELECT
			    	sp.id,
			    	(SELECT branch_namekh from rms_branch where br_id = st.branch_id LIMIT 1) as branch_name,
			    	(SELECT last_name from rms_users as u where u.id = sp.user_id LIMIT 1) as user_name,
			    	st.`stu_code`,
			    	st.`stu_enname`,
			    	st.`stu_khname`,
			    	(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` LIMIT 1) AS sex,
			    	st.`tel`,
					sp.`student_id`,
			    	sp.`create_date`,
			    	sp.`receipt_number`,
			    	(SELECT major_enname from rms_major where major_id =  sp.`grade` LIMIT 1) as grade,
			    	sp.`time`,
					sp.is_void,
					sp.tuition_fee_after_discount,
					sp.material_fee,
			    	sp.`tuition_fee`,
			    	sp.`discount_percent`,
			    	sp.`discount_fix`,
			    	sp.`total_payment`,
			    	sp.`admin_fee`,
			    	sp.`other_fee`,
			    	sp.`grand_total_paid_amount`,
			    	sp.`grand_total_balance`,
			    	sp.`note`,
			    	sp.is_complete,
			    	(SELECT name_kh FROM rms_view WHERE TYPE=18 AND key_code = sp.`payment_method` LIMIT 1) AS payment_method_title,
			    	sp.`payment_method`
			    FROM
			    	`rms_student` AS st
			    	INNER JOIN v_dailykhmerfulltime sp
					ON (st.`stu_id`=sp.`student_id`)
			    WHERE
			    	1
			    	$branch_id ";
    
    	$where = " ";
    	if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 10:30:00'";
    	}
    	else if($search['shift']==2){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 10:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 16:30:00'";
    	}
    	else if($search['shift']==3){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 16:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where .= " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['from_receipt']) && !empty($search['to_receipt'])){
    		$where .= " AND receipt_number between '".$search['from_receipt']."' AND '".$search['to_receipt']."'";
    	}
    	$order=" ORDER BY sp.`receipt_number` ASC,sp.`grade` ASC ";
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['degree_kh_ft'] > 0){
    		$where.= " AND sp.`degree` = ".$search['degree_kh_ft'];
    	}
    	if($search['grade_kh_ft'] > 0){
    		$where.= " AND sp.`grade` = ".$search['grade_kh_ft'];
    	}
    	if($search['room'] > 0){
    		$where.= " AND sp.`room_id` = ".$search['room'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND st.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND sp.`user_id` = ".$search['user'];
    	}
    	if(!empty($search['payment_method'])){
    		$where.= " AND sp.`payment_method` = ".$search['payment_method'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
    
    public function getDailyIncomeTransport($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("st.`branch_id`");
    	$sql = "SELECT 
					(SELECT branch_namekh FROM rms_branch WHERE br_id = st.branch_id LIMIT 1) AS branch_name,
					st.`stu_enname`,
					st.`stu_khname`,
					st.`tel`,
					(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` LIMIT 1) AS sex,
					sc.stu_code,
					sc.car_number AS car_id,
					(SELECT title FROM rms_program_name AS p WHERE p.service_id = sc.service_id LIMIT 1) AS service_name,
					sp.* 
					FROM 
					(`rms_student` st
						INNER JOIN 
					`v_dailytransport` sp
						ON st.`stu_id`=sp.`student_id`)
					LEFT JOIN v_student_carnumber AS sc
					ON sc.stu_id=st.stu_id
					WHERE 
					1
			       $branch_id
    		";
    
    	$where = " ";
    
    	if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 10:30:00'";
    	}
    	else if($search['shift']==2){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 10:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 16:30:00'";
    	}
    	else if($search['shift']==3){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 16:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where .= " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['from_receipt']) && !empty($search['to_receipt'])){
    		$where .= " AND receipt_number between '".$search['from_receipt']."' AND '".$search['to_receipt']."'";
    	}
    
    	$order=" ORDER BY sp.`receipt_number` ASC ";
    
    
    	if(empty($search)){
	    	return $db->fetchAll($sql.$order);
	    }
     
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " sc.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	 
    	if($search['branch'] > 0){
    		$where.= " AND st.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND sp.`user_id` = ".$search['user'];
    	}
    	
    	if(!empty($search['payment_method'])){
    		$where.= " AND sp.`payment_method` = ".$search['payment_method'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    
    }
    
    
    public function getDailyIncomeFoodandstay($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("st.`branch_id`");
    
    	// $sql = "SELECT
		// 	    	sp.*,
		// 	    	sp.id,
		// 	    	(select branch_namekh from rms_branch where br_id = sp.branch_id LIMIT 1) as branch_name,
		// 	    	(select last_name from rms_users as u where u.id = sp.user_id LIMIT 1) as user_name,
			    	
		// 	    	sp.`student_id`,
		// 	    	(select stu_code from rms_service where rms_service.stu_id = sp.student_id and rms_service.type=5 LIMIT 1) as stu_code ,
		// 	    	st.`stu_enname`,
		// 	    	st.`stu_khname`,
		// 	    	st.`tel`,
		// 	    	(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` LIMIT 1) AS sex,
		// 	    	sp.`create_date`,
		// 	    	sp.`receipt_number`,
		// 	    	sp.`tuition_fee`,
		// 	    	sp.`discount_percent`,
		// 	    	sp.`total_payment`,
		// 	    	sp.`admin_fee`,
		// 	    	sp.`other_fee`,
		// 	    	sp.`grand_total_payment`,
		// 	    	sp.`grand_total_paid_amount`,
		// 	    	sp.`grand_total_balance`,
		// 	    	sp.`note`,
		// 	    	spd.service_id,
		// 	    	spd.`start_date`,
		// 	    	spd.`validate`,
		// 	    	spd.`payment_term`,
		// 	    	spd.is_complete,
		// 	    	(SELECT name_kh FROM rms_view WHERE TYPE=18 AND key_code = sp.`payment_method` LIMIT 1) AS payment_method_title,
		// 	    	sp.`payment_method`,
		// 	    	sp.`payment_note`
			    	
		// 	    FROM
		// 	    	`rms_student_payment` AS sp,
		// 	    	`rms_student_paymentdetail` AS spd,
		// 	    	`rms_student` AS st,
		// 	    	rms_service as s
		// 	    WHERE
		// 	    	sp.id=spd.`payment_id`
		// 	    	and s.stu_id = st.stu_id
		// 	    	AND st.`stu_id`=sp.`student_id`
		// 	    	AND sp.`payfor_type`=4
		// 	    	AND spd.`type`=5
		// 	    	and s.type=5
		// 	    	$branch_id
    	// 	";

			$sql = "SELECT 
					(SELECT branch_namekh FROM rms_branch WHERE br_id = st.branch_id LIMIT 1) AS branch_name,
					st.`stu_enname`,
					st.`stu_khname`,
					st.`tel`,
					(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` LIMIT 1) AS sex,
					sf.stu_code,
					sp.service_id,
					(SELECT title FROM rms_program_name AS p WHERE p.service_id = sp.service_id LIMIT 1) AS service_name,
					sp.* 
					FROM 
					(`rms_student` st
						INNER JOIN 
					`v_dailyfood` sp
						ON st.`stu_id`=sp.`student_id`)
					LEFT JOIN v_student_food AS sf
					ON sf.stu_id=st.stu_id
					WHERE 
					1
			$branch_id ";

    	$where = " ";
    
    	if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 10:30:00'";
    	}
    	else if($search['shift']==2){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 10:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 16:30:00'";
    	}
    	else if($search['shift']==3){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 16:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where .= " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['from_receipt']) && !empty($search['to_receipt'])){
    		$where .= " AND receipt_number between '".$search['from_receipt']."' AND '".$search['to_receipt']."'";
    	}
    
    	$order=" ORDER BY sp.`receipt_number` ASC,sp.`grade` ASC,sp.id ASC ";
    
    
    	if(empty($search)){
	    	return $db->fetchAll($sql.$order);
	    }
     
	    if(!empty($search['txtsearch'])){
		    $s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " sf.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    
    	if($search['branch'] > 0){
    		$where.= " AND st.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND sp.`user_id` = ".$search['user'];
    	}
    	if(!empty($search['payment_method'])){
    		$where.= " AND sp.`payment_method` = ".$search['payment_method'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    
    }
    
    
    public function getDailyIncomeMaterial($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql = "SELECT
			    	sp.*,
			    	sp.id,
			    	
			    	(select branch_namekh from rms_branch where br_id = sp.branch_id LIMIT 1) as branch_name,
			    	(select last_name from rms_users as u where u.id = sp.user_id LIMIT 1) as user_name,
			    	
			    	sp.`student_id`,
			    	st.`stu_code`,
			    	st.`stu_enname`,
			    	st.`stu_khname`,
			    	(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` LIMIT 1) AS sex,
			    	st.`tel`,
			    	sp.`create_date`,
			    	sp.`is_new`,
			    	sp.`receipt_number`,
			    	(select en_name from rms_dept where dept_id = sp.`degree` LIMIT 1) as degree,
			    	(select major_enname from rms_major where major_id =  st.`grade` LIMIT 1) as grade,
			    	(select room_name from rms_room where rms_room.room_id = sp.`room_id` LIMIT 1) as room,
			    	sp.`time`,
			    	 
			    	sp.`tuition_fee`,
			    	sp.`discount_percent`,
			    	sp.`total_payment`,
			    	sp.`admin_fee`,
			    	sp.`other_fee`,
			    	 
			    	sp.`grand_total_payment`,
			    	sp.`grand_total_paid_amount`,
			    	sp.`grand_total_balance`,
			    	
			    	sp.is_subspend,

			    	sp.is_void,
			    	
			    	spd.service_id,
					
					(select product_type from rms_program_name as pn where pn.service_id = spd.service_id LIMIT 1) as product_type,
					
			    	(select title from rms_program_name where rms_program_name.service_id = spd.service_id LIMIT 1) as service_name,
			    	spd.`note`,
			    	spd.`start_date`,
			    	spd.`validate`,
			    	spd.`payment_term`,
			    	spd.subtotal,
			    	spd.paidamount,
			    	spd.qty,
			    	
			    	(SELECT name_kh FROM rms_view WHERE TYPE=18 AND key_code = sp.`payment_method` LIMIT 1) AS payment_method_title,
			    	sp.`payment_method`,
			    	sp.`payment_note`
			    	
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_student_paymentdetail` AS spd,
			    	`rms_student` AS st
			    WHERE
			    	sp.id=spd.`payment_id`
			    	AND st.`stu_id`=sp.`student_id`
			    	AND sp.`payfor_type`=5
			    	AND spd.`type`=4
			    	$branch_id
    		";
    
    	$where = " ";
    
    	if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 10:30:00'";
    	}
    	else if($search['shift']==2){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 10:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 16:30:00'";
    	}
    	else if($search['shift']==3){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 16:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where .= " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['from_receipt']) && !empty($search['to_receipt'])){
    		$where .= " AND receipt_number between '".$search['from_receipt']."' AND '".$search['to_receipt']."'";
    	}
    
    	$order=" ORDER BY sp.`receipt_number` ASC ";
    
    
    	if(empty($search)){
	    	return $db->fetchAll($sql.$order);
	    }
     
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND sp.`user_id` = ".$search['user'];
    	}
    	if(!empty($search['payment_method'])){
    		$where.= " AND sp.`payment_method` = ".$search['payment_method'];
    	}
    	
    	return $db->fetchAll($sql.$where.$order);
    
    }
    
    
    public function getDailyIncomeParkingCanteen($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("cp.`branch_id`");
    
    	$sql = "SELECT
			    	cp.id,
			    	
			    	(select branch_namekh from rms_branch where br_id = cp.branch_id LIMIT 1) as branch_name,
			    	(select last_name from rms_users as u where u.id = cp.user_id LIMIT 1) as user_name,
			    	
			    	c.customer_code,
			    	c.first_name,
			    	cp.rent_receipt_no,
			    	
			    	rent_paid,
			    	water_exc_rate,
			    	fire_exc_rate,
			    	hygiene_price,
			    	other_price,
			    	
					(SELECT name_kh FROM rms_view WHERE TYPE=18 AND key_code = cp.`payment_method` LIMIT 1) AS payment_method_title,
					
			    	all_total_amount,
			    	paid,
			    	cp.note,
			    	cp.create_date,
			    	cp.status
			    	
			    FROM
			    	rms_customer as c,
			    	rms_customer_paymentdetail as cp
			    WHERE
			    	c.id = cp.cus_id
			    	$branch_id
    		";
    
    	$where = " ";
    
    	if($search['shift']==0){
	    	$from_date =(empty($search['start_date']))? '1': "cp.create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "cp.create_date <= '".$search['end_date']." 23:59:59'";
	    }
	    else if($search['shift']==1){
	    		$from_date =(empty($search['start_date']))? '1': "cp.create_date >= '".$search['start_date']." 00:00:00'";
	    		$to_date = (empty($search['end_date']))? '1': "cp.create_date <= '".$search['end_date']." 10:30:00'";
	    }
	    else if($search['shift']==2){
		    	$from_date =(empty($search['start_date']))? '1': "cp.create_date >= '".$search['start_date']." 10:30:01'";
		    	$to_date = (empty($search['end_date']))? '1': "cp.create_date <= '".$search['end_date']." 16:30:00'";
	    }
	    else if($search['shift']==3){
		    	$from_date =(empty($search['start_date']))? '1': "cp.create_date >= '".$search['start_date']." 16:30:01'";
		    	$to_date = (empty($search['end_date']))? '1': "cp.create_date <= '".$search['end_date']." 23:59:59'";
	    }
	    $where .= " AND ".$from_date." AND ".$to_date;
	    
	    if(!empty($search['from_receipt']) && !empty($search['to_receipt'])){
	    	$where .= " AND rent_receipt_no between '".$search['from_receipt']."' AND '".$search['to_receipt']."'";
	    }
	    
	    $order=" ORDER BY cp.rent_receipt_no ASC ";
	    
	    if(empty($search)){
	    	return $db->fetchAll($sql.$order);
	    }
     
	    if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " cp.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " cp.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " cp.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " cp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    
    	if($search['branch'] > 0){
    		$where.= " AND cp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND cp.`user_id` = ".$search['user'];
    	}
    
    	if(!empty($search['payment_method'])){
    		$where.= " AND cp.payment_method = ".$search['payment_method'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    
    }
    
    
    
    function getAllGrade(){
    	$db = $this->getAdapter();
    	$sql="select major_id as id, CONCAT(major_enname,' (',(select shortcut from rms_dept as d where d.dept_id = m.dept_id LIMIT 1),')') as name from rms_major as m where is_active = 1  ";
    	return $db->fetchAll($sql);
    }
    
    function getAllSession(){
    	$db = $this->getAdapter();
    	$sql="select key_code as id, name_en as name from rms_view where type = 4 and status = 1 and key_code IN(1,2,3) ";
    	return $db->fetchAll($sql);
    }
    
    function getAllRoom(){
    	$db = $this->getAdapter();
    	$sql="select room_id as id, room_name as name from rms_room where is_active = 1  ";
    	return $db->fetchAll($sql);
    }
    
    
    
    
    
    
    
}







