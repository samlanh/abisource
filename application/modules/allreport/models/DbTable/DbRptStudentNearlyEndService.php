<?php

class Allreport_Model_DbTable_DbRptStudentNearlyEndService extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
    	 
//     }
    function getAllStudentNearlyEndService($search){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");		
    	
    	$sql="SELECT 
				  sp.`receipt_number` AS receipt,
				  sp.payfor_type,
				  sp.note,
				  (SELECT branch_namekh FROM rms_branch WHERE br_id = sp.branch_id LIMIT 1) as branch_name,
				  
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
				  spd.discount_percent,
				  spd.`start_date` as start,
				  spd.`validate` as end,
				  (SELECT ser.is_suspend FROM rms_service AS ser WHERE ser.stu_id= sp.student_id AND sp.payfor_type=3 AND ser.type=4 LIMIT 1) AS car_suspend,
  				  (SELECT ser.is_suspend FROM rms_service AS ser WHERE ser.stu_id= sp.student_id AND sp.payfor_type=4 AND ser.type=5 LIMIT 1) AS lunch_suspend   
				FROM
				  `rms_student_paymentdetail` AS spd,
				  `rms_student_payment` AS sp,
				  `rms_program_name` AS pn,
				  rms_student as s
				WHERE spd.`is_start` = 1
				  AND sp.is_void=0
				  AND s.stu_id=sp.student_id 
				  AND sp.id=spd.`payment_id`
				  AND spd.`service_id`=pn.`service_id` 
				  AND s.is_subspend=0
				  $branch_id
    		";
    	
     	$order=" ORDER by spd.`validate` DESC ";
     	//$FROM_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
     	$WHERE=" ";
     	$str_next = '+1 week';
     	$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));

//      $FROM_date = (empty($search['start_date']))? '1': "spd.validate >= '".$search['start_date']." 00:00:00'";
     	$to_date = (empty($search['end_date']))? '1': "spd.validate <= '".$search['end_date']." 23:59:59'";
     	//$WHERE .= " AND ".$FROM_date." AND ".$to_date;
     	$WHERE .= " AND ".$to_date;
     	
     	if($search['service']>0){
     		$WHERE .=" AND spd.service_id=".$search['service'];
     	}
     	if($search['branch'] > 0){
     		$WHERE.= " AND sp.`branch_id` = ".$search['branch'];
     	}
     	if($search['degree_all']>0){
     		$WHERE.=" AND s.degree=".$search['degree_all'];
     	}
     	if($search['grade_all']>0){
     		$WHERE.=" AND s.grade=".$search['grade_all'];
     	}
     	
    	if(!empty($search['txtsearch'])){
    		$s_WHERE = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_WHERE[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$s_WHERE[] = " s.stu_code  LIKE '%{$s_search}%'";
    		$s_WHERE[] = " (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id= sp.student_id AND ser.type=4 LIMIT 1)  LIKE '%{$s_search}%'";
    		$s_WHERE[] = " (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id= sp.student_id AND ser.type=5 LIMIT 1)  LIKE '%{$s_search}%'";
    		$s_WHERE[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_WHERE[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_WHERE[] = " (SELECT title FROM rms_program_name WHERE rms_program_name.service_id=spd.service_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_WHERE[] = " spd.comment LIKE '%{$s_search}%'";
    		$WHERE .=' AND ( '.implode(' OR ',$s_WHERE).')';
    	}
    		
//     		echo $sql.$WHERE;
    	return $db->fetchAll($sql.$WHERE.$order);
    }
    
}
   
    
   