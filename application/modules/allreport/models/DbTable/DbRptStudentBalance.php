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
    	
    	$sql = "select 
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
    				
					(select stu_code from rms_student where rms_student.stu_id=sp.student_id limit 1)AS code,
					(select ser.stu_code from rms_service as ser where ser.stu_id= sp.student_id and ser.type=4 limit 1) as transport_code,
					(select ser.stu_code from rms_service as ser where ser.stu_id= sp.student_id and ser.type=5 limit 1) as lunch_code,

					(select CONCAT(stu_khname,' - ',stu_enname) from rms_student where rms_student.stu_id=sp.student_id limit 1)AS name,
					(select stu_khname from rms_student where rms_student.stu_id=sp.student_id limit 1)AS stu_khname,
					(select stu_enname from rms_student where rms_student.stu_id=sp.student_id limit 1)AS stu_enname,
					
					(select name_en from rms_view where rms_view.type=2 and key_code=(select sex from rms_student where rms_student.stu_id=sp.student_id limit 1) LIMIT 1)AS sex,
					(select title from rms_program_name where rms_program_name.service_id=spd.service_id limit 1)AS service
				from 
					rms_student_payment AS sp,
					rms_student_paymentdetail AS spd 
				where 
					spd.payment_id=sp.id 
					and spd.is_complete=0
					and spd.balance>0
					and sp.is_void=0
					$branch_id
    		   ";
    	
     	$order=" ORDER by sp.receipt_number ASC ";
    	$where = '';
     	
     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
     	
     	$where = " AND ".$from_date." AND ".$to_date;
     	
     	if(!empty($search['service'])){
     		$where .=" and spd.service_id = ".$search['service'];
     	}
     	if(!empty($search['branch'])){
     		$where .=" and sp.branch_id = ".$search['branch'];
     	}
     	if($search['status']!=""){
     		$where .=" and spd.is_complete = ".$search['status'];
     	}
     	
     	if(!empty($search['payfor_type'])){
     		$where .=" and sp.payfor_type = ".$search['payfor_type'];
     	}
     	
    		if(!empty($search['txtsearch'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['txtsearch']));
    			$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    			$s_where[] = " (select stu_code from rms_student where rms_student.stu_id=sp.student_id LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " (select CONCAT(stu_khname,stu_enname) from rms_student where rms_student.stu_id=sp.student_id LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " (select title from rms_program_name where rms_program_name.service_id=spd.service_id LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " spd.comment LIKE '%{$s_search}%'";
    			$where .=' AND ( '.implode(' OR ',$s_where).')';
    		}
//     		echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    
}
   
    
   