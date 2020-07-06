<?php

class Allreport_Model_DbTable_DbRptStudentBalance extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
    	 
//     }
    function getAllStudentBalance($search){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT 
    				spd.id,
    				spd.fee AS fee,
    				spd.discount_fix AS discount,
    				spd.subtotal AS payment,
    				spd.paidamount AS paid,
    				spd.is_complete AS complete,
    				spd.balance AS balance,
    				spd.validate AS validate,
    				spd.comment AS comment,
    				sp.create_date AS paid_date,
    				sp.receipt_number AS receipt,    
    				sp.payfor_type, 
    				
					(SELECT stu_code FROM rms_student WHERE rms_student.stu_id=sp.student_id LIMIT 1)AS code,
					(SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id= sp.student_id and ser.type=4 LIMIT 1) as transport_code,
					(SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id= sp.student_id and ser.type=5 LIMIT 1) as lunch_code,

					(SELECT CONCAT(stu_khname,' - ',stu_enname) FROM rms_student WHERE rms_student.stu_id=sp.student_id LIMIT 1)AS name,
					(SELECT stu_khname FROM rms_student WHERE rms_student.stu_id=sp.student_id LIMIT 1)AS stu_khname,
					(SELECT stu_enname FROM rms_student WHERE rms_student.stu_id=sp.student_id LIMIT 1)AS stu_enname,
					
					(SELECT name_en FROM rms_view WHERE rms_view.type=2 and key_code=(SELECT sex FROM rms_student WHERE rms_student.stu_id=sp.student_id LIMIT 1) LIMIT 1)AS sex,
					(SELECT title FROM rms_program_name WHERE rms_program_name.service_id=spd.service_id LIMIT 1)AS service
				FROM 
					rms_student_payment AS sp,
					rms_student_paymentdetail AS spd 
				WHERE 
					spd.payment_id=sp.id 
					and spd.is_complete=0
					and spd.balance>0
					and sp.is_void=0
					$branch_id
    		   ";
    	
     	$order=" ORDER by sp.receipt_number ASC ";
    	$WHERE = '';
     	
     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
     	
     	$WHERE = " AND ".$from_date." AND ".$to_date;
     	
     	if(!empty($search['service'])){
     		$WHERE .=" and spd.service_id = ".$search['service'];
     	}
     	if(!empty($search['branch'])){
     		$WHERE .=" and sp.branch_id = ".$search['branch'];
     	}
     	if($search['status']!=""){
     		$WHERE .=" and spd.is_complete = ".$search['status'];
     	}
     	
     	if(!empty($search['payfor_type'])){
     		$WHERE .=" and sp.payfor_type = ".$search['payfor_type'];
     	}
     	
    		if(!empty($search['txtsearch'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['txtsearch']));
    			$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT stu_code FROM rms_student WHERE rms_student.stu_id=sp.student_id LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT CONCAT(stu_khname,stu_enname) FROM rms_student WHERE rms_student.stu_id=sp.student_id LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT title FROM rms_program_name WHERE rms_program_name.service_id=spd.service_id LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " spd.comment LIKE '%{$s_search}%'";
    			$WHERE .=' AND ( '.implode(' OR ',$s_where).')';
    		}
    	return $db->fetchAll($sql.$WHERE.$order);
    }
    
}
   
    
   