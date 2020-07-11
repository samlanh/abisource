<?php

class Registrar_Model_DbTable_DbRptStudentNearlyEndService extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    function getAllStudentNearlyEndService($search){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal;
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	
    	$sql="SELECT 
				  sp.`receipt_number` AS receipt,
				  sp.payfor_type,
				  
				  s.stu_code,
				  (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id= sp.student_id AND ser.type=4 LIMIT 1) as transport_code,
				  (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id= sp.student_id AND ser.type=5 LIMIT 1) as lunch_code,
				  
				  CONCAT(s.stu_khname,' - ',s.stu_enname) AS name,
				  s.stu_khname,
				  s.stu_enname,
				  (SELECT name_en FROM rms_view WHERE rms_view.type=2 AND key_code=s.sex LIMIT 1)AS sex,
				  s.tel,
				  
				  (SELECT en_name FROM rms_dept WHERE dept_id=s.degree LIMIT 1) as degree,
				  (SELECT major_enname FROM rms_major WHERE major_id=s.grade LIMIT 1) as grade,
				  
				  pn.`title` service,
				  spd.`start_date` as start,
				  spd.`validate` as end,
				  (SELECT ser.is_suspend FROM rms_service AS ser WHERE ser.stu_id= sp.student_id AND sp.payfor_type=3 AND ser.type=4 LIMIT 1) AS car_suspend,
  				  (SELECT ser.is_suspend FROM rms_service AS ser WHERE ser.stu_id= sp.student_id AND sp.payfor_type=4 AND ser.type=5 LIMIT 1) AS lunch_suspend
				FROM
				  `rms_student_paymentdetail` AS spd,
				  `rms_student_payment` AS sp,
				  `rms_program_name` AS pn,
				  rms_student as s
				WHERE 
				  spd.`is_start` = 1
				  AND sp.is_void=0
				  AND s.stu_id=sp.student_id 
				  AND sp.id=spd.`payment_id`
				  AND spd.`service_id`=pn.`service_id` 
				  AND s.is_subspend = 0 
				  $branch_id
				  
    		";
    		//OR (SELECT ser.is_suspend FROM rms_service as ser WHERE ser.stu_id= sp.student_id AND ser.type=5 LIMIT 1) = 0
			//OR (SELECT ser.is_suspend FROM rms_service as ser WHERE ser.stu_id= sp.student_id AND ser.type=4 LIMIT 1) = 0   
    	
			 	
    	
    	
    	$WHERE=" ";
    	$order=" ORDER by spd.`validate` DESC ";
    	$str_next = '+1 week';
     	$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
      	$to_date = (empty($search['end_date']))? '1': " spd.validate <= '".$search['end_date']." 23:59:59'";
//       	$from_date = (empty($search['start_date']))? '1': " spd.validate >= '".$search['start_date']." 00:00:00'";
      	$WHERE .= " AND ".$to_date;
	      	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_code  LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id= sp.student_id AND ser.type=4 LIMIT 1)  LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id= sp.student_id AND ser.type=5 LIMIT 1)  LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT title FROM rms_program_name WHERE rms_program_name.service_id=spd.service_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " spd.comment LIKE '%{$s_search}%'";
    		$WHERE .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['service']>0){
    		$WHERE.=" AND spd.service_id=".$search['service'];
    	}
    	if($search['degree_all']>0){
    		$WHERE.=" AND s.degree=".$search['degree_all'];
    	}
    	if($search['grade_all']>0){
    		$WHERE.=" AND s.grade=".$search['grade_all'];
    	}
    	return $db->fetchAll($sql.$WHERE.$order);
    }
    
}
   
    
   