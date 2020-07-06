<?php

class Allreport_Model_DbTable_DbRptAttCar extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_service';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    
    public function getAllAttCar($search){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("ser.`branch_id`");
    	
    	$sql = "SELECT
				  ser.`stu_code`,
				  (SELECT branch_namekh FROM rms_branch WHERE br_id = ser.branch_id LIMIT 1) as branch_name,
			      (SELECT last_name FROM rms_users as u WHERE u.id = sp.user_id LIMIT 1) as user_name,
				  st.stu_khname,
				  st.stu_enname,
				  CONCAT(st.`stu_khname`,'-',st.`stu_enname`) AS name,
				  (SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code=st.`sex` LIMIT 1) AS sex,
				  st.`tel` as stu_phone,
				  pn.`title` as service_name,
				  ser.`time_for_car`,
				  (SELECT name_en FROM rms_view WHERE TYPE=6 AND key_code=spd.`payment_term` LIMIT 1) AS payment_term,
				  spd.payment_term as term_id,
				  sp.tuition_fee,
				  spd.fee,
				  spd.qty,
				  spd.`start_date`,
				  spd.`validate`,
				  ser.`car_id`,
				  c.carid,
				  c.`drivername`,
				  c.tel
				FROM
				  `rms_service` AS ser,
				  `rms_program_name` AS pn,
				  `rms_student` AS st,
				  `rms_student_payment` AS sp,
				  `rms_student_paymentdetail` AS spd,
				  `rms_car` AS c
				WHERE
				  ser.`stu_id`=st.`stu_id`
				  AND ser.`service_id`=pn.`service_id`
				  AND ser.`type`=4
				  AND sp.`payfor_type`=3
				  AND sp.id=spd.`payment_id`
				  AND spd.`service_id`=ser.`service_id`
				  AND spd.`is_start`=1
				  AND c.id = ser.`car_id`  
				  AND sp.`student_id`=ser.`stu_id`
				  AND ser.is_suspend=0
				  $branch_id ";
    	$WHERE = " ";
    	$group_by = " group by ser.`stu_id` ";
    	$order=" ORDER BY c.`carid` ASC , ser.id ASC, ser.stu_code ASC ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$group_by.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " ser.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " c.carid LIKE '%{$s_search}%'";
    		$s_where[] = " c.drivername LIKE '%{$s_search}%'";
    		$WHERE .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['carid'] > 0){
    		$WHERE.= " AND c.id = ".$search['carid'];
    	}
    	if($search['service'] > 0){
    		$WHERE.= " AND ser.`service_id` = ".$search['service'];
    	}
    	if($search['branch'] > 0){
    		$WHERE.= " AND ser.`branch_id` = ".$search['branch'];
    	}
    	//echo $sql.$WHERE;
    	return $db->fetchAll($sql.$WHERE.$group_by.$order);
    }
   
    function getAllCar(){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("branch_id");
    	
    	$sql="SELECT id,carid as name FROM rms_car WHERE status=1 $branch_id ";
    	return $db->fetchAll($sql);
    }
    
    function getAllService(){
    	$db = $this->getAdapter();
    	$sql="SELECT service_id as id,title as name FROM rms_program_name WHERE status=1 AND type=2";
    	return $db->fetchAll($sql);
    }
    
    
    
    
}