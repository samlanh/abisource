<?php

class Allreport_Model_DbTable_DbRptResultIncome extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_service';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    public function getAllResultIncome($search,$payfor_type){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	
    	$sql = "SELECT
					spd.id,
					sp.grand_total_payment,
					sp.grand_total_paid_amount,
					(SELECT ser_cate_id FROM `rms_program_name` WHERE spd.`service_id`=rms_program_name.`service_id` limit 1) AS category_id,
					(SELECT account_no FROM `rms_program_type` WHERE id=(SELECT ser_cate_id FROM `rms_program_name` WHERE spd.`service_id`=rms_program_name.`service_id` limit 1) limit 1) AS account_no,
					(SELECT title FROM `rms_program_type` WHERE id=(SELECT ser_cate_id FROM `rms_program_name` WHERE spd.`service_id`=rms_program_name.`service_id` limit 1) limit 1 ) AS category,
					(SELECT title FROM rms_program_name WHERE rms_program_name.`service_id`=spd.`service_id` limit 1) AS service_name,
					spd.subtotal,
					spd.`paidamount`
				FROM
					`rms_student_payment` AS sp,
					`rms_student_paymentdetail` AS spd
				WHERE
					sp.id=spd.`payment_id`
					and sp.is_void=0
					and sp.payfor_type=$payfor_type
				    $branch_id 
    		  ";
    	
    	$where = " ";
    	
    	if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 10:00:00'";
    	}
    	else if($search['shift']==2){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 10:00:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 16:00:00'";
    	}
    	else if($search['shift']==3){
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 16:00:01'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	$order=" ORDER BY (SELECT ser_cate_id FROM `rms_program_name` WHERE spd.`service_id`=rms_program_name.`service_id`) ASC ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND sp.`user_id` = ".$search['user'];
    	}
//     	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
    
    function getAllRentPayment($search){
    	$db = $this->getAdapter();
//     	print_r($search);exit();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("cp.`branch_id`");
    	 
    	$sql = "SELECT
    				cp.id,
    				cp.cus_id,
    				rent_receipt_no,
    				all_total_amount,
    				paid,
    				balance,
    				cp.status
			    FROM
			    	`rms_customer_paymentdetail` AS cp
			    WHERE
			    	1
			    	$branch_id
    			";
    	 
    	$where = " ";
    	 
   		if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "cp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "cp.create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
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
    	
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$order=" ORDER BY cp.rent_receipt_no ASC ";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['branch'] > 0){
    		$where.= " AND cp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    	
    }
    
    function getAllParkingPayment($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("pd.`branch_id`");
    
    	$sql = "SELECT
			    	id,
			    	receipt_no,
			    	total_fee,
			    	pd.status
			    FROM
			    	`rms_parking_detail` AS pd
			    WHERE
			    	1
			    	$branch_id
    		";
    
    	$where = " ";
    
    	if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "pd.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "pd.create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
    		$from_date =(empty($search['start_date']))? '1': "pd.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "pd.create_date <= '".$search['end_date']." 10:30:00'";
    	}
    	else if($search['shift']==2){
    		$from_date =(empty($search['start_date']))? '1': "pd.create_date >= '".$search['start_date']." 10:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "pd.create_date <= '".$search['end_date']." 16:30:00'";
    	}
    	else if($search['shift']==3){
    		$from_date =(empty($search['start_date']))? '1': "pd.create_date >= '".$search['start_date']." 16:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "pd.create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	$order=" ORDER BY pd.receipt_no ASC ";
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
// 	    	$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
// 	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
// 	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
// 	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    			 
    	if($search['branch'] > 0){
    		$where.= " AND pd.`branch_id` = ".$search['branch'];
	    }
	    if($search['user'] > 0){
	    	$where.= " AND `user_id` = ".$search['user'];
	    }
	    
	    //echo $sql.$where;
	    
	    return $db->fetchAll($sql.$where.$order);
	     
	}
    
    
    function getAllOtherIncome($search){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("`branch_id`");
    	
    	$sql = "SELECT
			    	id,
			    	invoice,
			    	curr_type,
			    	total_amount,
			    	status
			    FROM
			    	`ln_income`
			    WHERE
			    	1
			    	$branch_id
    		";
    	
    	$where = " ";
    	
    	if($search['shift']==0){
    		$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
    		$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 10:30:00'";
    	}
    	else if($search['shift']==2){
    		$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 10:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 16:30:00'";
    	}
    	else if($search['shift']==3){
    		$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 16:30:01'";
    		$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	$order=" ORDER BY invoice ASC ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
// 	    	$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
// 	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
// 	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
// 	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['branch'] > 0){
    		$where.= " AND `branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
//     	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getAllStudentTest($search){
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("`branch_id`");
    	 
    	$sql = "SELECT
			    	id,
			    	receipt,
			    	total_price,
			    	status
			    FROM
			    	`rms_student_test`
			    WHERE
			    	1
			    	$branch_id
    		";
    	 
    	$where = " ";
    	 
    	if($search['shift']==0){
	    	$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	}else if($search['shift']==1){
	    	$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 10:30:00'";
    	}
    	else if($search['shift']==2){
	    	$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 10:30:01'";
	    	$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 16:30:00'";
    	}
    	else if($search['shift']==3){
	    	$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 16:30:01'";
	    	$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	}
    	$where .= " AND ".$from_date." AND ".$to_date;
    	 
    	$order=" ORDER BY receipt ASC ";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	//$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
	    	//$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	//$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	//$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	 
    	if($search['branch'] > 0){
    		$where.= " AND `branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
   
    function getKhmerFullTimePayment($search){
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	 
    	$sql = "SELECT
			    	sp.id,
			    	receipt_number,
			    	grand_total_payment,
			    	grand_total_paid_amount,
			    	grand_total_balance,
			    	sp.is_void
			    FROM
			    	`rms_student_payment` AS sp
			    WHERE
			    	payfor_type = 1
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
    	
    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	$order=" ORDER BY sp.receipt_number ASC ";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	 
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
//     	echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    
    
    function getEnglishFullTimePayment($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql = "SELECT
			    	sp.id,
			    	receipt_number,
			    	grand_total_payment,
			    	grand_total_paid_amount,
			    	grand_total_balance,
			    	sp.is_void
			    FROM
			    	`rms_student_payment` AS sp
			    WHERE
			    	payfor_type = 6
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
    
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$order=" ORDER BY sp.receipt_number ASC ";
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
    		 
    	return $db->fetchAll($sql.$where.$order);
    }
    
    
    function getEnglishPartTimePayment($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql = "SELECT
			    	sp.id,
			    	receipt_number,
			    	grand_total_payment,
			    	grand_total_paid_amount,
			    	grand_total_balance,
			    	sp.is_void
			    FROM
			    	`rms_student_payment` AS sp
			    WHERE
			    	payfor_type = 2
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
    
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$order=" ORDER BY sp.receipt_number ASC ";
    
	    if(empty($search)){
	    	return $db->fetchAll($sql.$order);
	    }
    
	    if($search['branch'] > 0){
	    	$where.= " AND sp.`branch_id` = ".$search['branch'];
	    }
	    if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
    		 
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getStudyMaterialPayment($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql = "SELECT
			    	sp.id,
			    	receipt_number,
			    	grand_total_payment,
			    	grand_total_paid_amount,
			    	grand_total_balance,
			    	sp.is_void
			    FROM
			    	`rms_student_payment` AS sp
			    WHERE
			    	payfor_type = 5
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
    
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$order=" ORDER BY sp.receipt_number ASC ";
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
    				 
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getTransportationPayment($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql = "SELECT
			    	sp.id,
			    	receipt_number,
			    	grand_total_payment,
			    	grand_total_paid_amount,
			    	grand_total_balance,
			    	sp.is_void
			    FROM
			    	`rms_student_payment` AS sp
			    WHERE
			    	payfor_type = 3
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
    
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$order=" ORDER BY sp.receipt_number ASC ";
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getFoodAndStayPayment($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql = "SELECT
			    	sp.id,
			    	receipt_number,
			    	grand_total_payment,
			    	grand_total_paid_amount,
			    	grand_total_balance,
			    	sp.is_void
			    FROM
			    	`rms_student_payment` AS sp
			    WHERE
			    	payfor_type = 4
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
    
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$order=" ORDER BY sp.receipt_number ASC ";
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getRielAndDollarAmount($search,$payfor_type){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql="select 
    				total_amount,
					amount_usd,
					amount_riel,
					shift,
					branch_id,
					user_id
    			from
    				rms_submit_daily_income as sdi
    			where 
    				status=1
    				and payfor_type = $payfor_type
    				and shift!=0
    				$branch_id
    		";
    	$where = " ";
    	
    	$from_date =(empty($search['start_date']))? '1': "for_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "for_date <= '".$search['end_date']." 23:59:59'";
    	$where.= " and ".$from_date." and ".$to_date;
    	
    	if($search['shift'] > 0){
    		$where.=" and shift = ".$search['shift'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND `branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$where.= " AND `user_id` = ".$search['user'];
    	}
    	
    	//echo $sql.$where;
    	
    	return $db->fetchAll($sql.$where);
    }
    
    
}







