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
				  (select branch_namekh from rms_branch where br_id = sp.branch_id limit 1) as branch_name,
				  
				  s.stu_code,
				  (select ser.stu_code from rms_service as ser where ser.stu_id= sp.student_id and ser.type=4 limit 1) as transport_code,
				  (select ser.stu_code from rms_service as ser where ser.stu_id= sp.student_id and ser.type=5 limit 1) as lunch_code,
				  
				  CONCAT(s.stu_khname,' - ',s.stu_enname) AS name,
				  s.stu_khname,
				  s.stu_enname,
				  (select name_en from rms_view where rms_view.type=2 and key_code=s.sex limit 1)AS sex,
				  s.tel,
				  
				  (select en_name from rms_dept where dept_id=s.degree limit 1) as degree,
				  (select major_enname from rms_major where major_id=s.grade limit 1) as grade,
				  
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
				  and sp.is_void=0
				  AND s.stu_id=sp.student_id 
				  AND sp.id=spd.`payment_id`
				  AND spd.`service_id`=pn.`service_id` 
				  and s.is_subspend=0
				  $branch_id
    		";
    	
     	$order=" ORDER by spd.`validate` DESC ";
     	//$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
     	$where=" ";
     	$str_next = '+1 week';
     	$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));

//      $from_date = (empty($search['start_date']))? '1': "spd.validate >= '".$search['start_date']." 00:00:00'";
     	$to_date = (empty($search['end_date']))? '1': "spd.validate <= '".$search['end_date']." 23:59:59'";
     	//$where .= " AND ".$from_date." AND ".$to_date;
     	$where .= " AND ".$to_date;
     	
     	if($search['service']>0){
     		$where .=" and spd.service_id=".$search['service'];
     	}
     	if($search['branch'] > 0){
     		$where.= " AND sp.`branch_id` = ".$search['branch'];
     	}
     	if($search['degree_all']>0){
     		$where.=" AND s.degree=".$search['degree_all'];
     	}
     	if($search['grade_all']>0){
     		$where.=" AND s.grade=".$search['grade_all'];
     	}
     	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_code  LIKE '%{$s_search}%'";
    		$s_where[] = " (select ser.stu_code from rms_service as ser where ser.stu_id= sp.student_id and ser.type=4 limit 1)  LIKE '%{$s_search}%'";
    		$s_where[] = " (select ser.stu_code from rms_service as ser where ser.stu_id= sp.student_id and ser.type=5 limit 1)  LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " (select title from rms_program_name where rms_program_name.service_id=spd.service_id limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " spd.comment LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    		
//     		echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    
}
   
    
   