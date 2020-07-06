<?php

class Allreport_Model_DbTable_DbRenAndPaymentList extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_customer';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
     
function getCustomer($search=null){
    	//print_r($search);exit();
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('cp.branch_id');
    	
    	$sql="SELECT 
    				c.*,
    				(SELECT branch_namekh FROM rms_branch WHERE br_id = cp.branch_id LIMIT 1) as branch_name,
    				cp.status As cp_status 
    			FROM 
    				rms_customer AS c,
    				rms_customer_paymentdetail AS cp
            	WHERE 
            		c.id=cp.cus_id
    				and cp.status=1 
    				$branch_id
    		";
    	$WHERE = '';
    	
    	if(!empty($search["title"])){
    		$s_WHERE=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_WHERE[]="  c.customer_code LIKE '%{$s_search}%'";
    		$s_WHERE[]="  c.first_name LIKE '%{$s_search}%'";
    		$s_WHERE[]="  c.phone LIKE '%{$s_search}%'";
    		$s_WHERE[]="  c.email LIKE '%{$s_search}%'";
    		$s_WHERE[]="  c.address LIKE '%{$s_search}%'";
    		
    		$s_WHERE[]="  cp.rent_receipt_no LIKE '%{$s_search}%'";
    		$s_WHERE[]="  cp.water_total LIKE '%{$s_search}%'";
    		$s_WHERE[]="  cp.fire_total LIKE '%{$s_search}%'";
    		$s_WHERE[]="  cp.all_total_amount LIKE '%{$s_search}%'";
    		$s_WHERE[]="  cp.paid LIKE '%{$s_search}%'";
    		$s_WHERE[]="  cp.balance LIKE '%{$s_search}%'";
    		$s_WHERE[]="  cp.rent_paid LIKE '%{$s_search}%'";
    		$s_WHERE[]="  cp.hygiene_price LIKE '%{$s_search}%'";
    		$WHERE.=' AND ('.implode(' OR ', $s_WHERE).')';
    	}
    		
    	if($search["status_search"]>-1){
    		$WHERE.=' AND cp.status='.$search["status_search"];
    	}
    	 
    	if($search['cus_name']>0){
    		$WHERE.=' AND c.id='.$search["cus_name"];
    	}
    	if($search['branch']>0){
    		$WHERE.=' AND cp.branch_id='.$search["branch"];
    	}
    	
    	$group_by = " GROUP BY cp.cus_id ";
    	
    	$order = " ORDER BY c.customer_code ASC , cp.id ASC ";
    	
    	//echo $sql.$WHERE.$group_by.$order;//exit();
    	
    	return $db->fetchAll($sql.$WHERE.$group_by.$order);
    }
    
    function getCusPaymentDetail($id){
    	$db=$this->getAdapter();
    	$sql="SELECT 
		       cp.*,
		       (SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id=cp.user_id LIMIT 1) AS user_name
		       
		       FROM rms_customer AS c,rms_customer_paymentdetail AS cp
		       WHERE c.id=cp.cus_id
		       AND cp.cus_id=$id ";
    	//echo $sql;
    	return $db->fetchAll($sql);
    }
    
    function getNearlydayEndServiceCustomer($search=null){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('c.branch_id');
    	$sql="SELECT 
    				c.*,
    				(SELECT rent_start_date FROM rms_customer_paymentdetail as cd WHERE c.id = cd.cus_id and status=1 order by cd.id DESC LIMIT 1) as start_date,
    				(SELECT rent_end_date FROM rms_customer_paymentdetail as cd WHERE c.id = cd.cus_id and status=1 order by cd.id DESC LIMIT 1) as end_date,
    				(SELECT v.name_kh FROM rms_view AS v WHERE v.key_code=c.sex AND v.type=2 LIMIT 1 ) AS c_sex,
    				(SELECT v.name_kh FROM rms_view AS v WHERE v.key_code=c.status AND v.type=1 LIMIT 1 ) AS c_status,
    				(SELECT CONCAT(first_name,' ',last_name) FROM rms_users WHERE rms_users.id=c.user_id LIMIT 1) AS user_name
    			FROM 
    				rms_customer AS c 
    			WHERE 
    				status = 1
    				$branch_id
    		";
    	$WHERE = '';
    	if(!empty($search["title"])){
    		$s_WHERE=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_WHERE[]="  c.customer_code LIKE '%{$s_search}%'";
    		$s_WHERE[]="  c.first_name LIKE '%{$s_search}%'";
    		$s_WHERE[]="  c.address LIKE '%{$s_search}%'";
    		$s_WHERE[]="  c.phone LIKE '%{$s_search}%'";
    		$s_WHERE[]="  c.email LIKE '%{$s_search}%'";
    		$WHERE.=' AND ('.implode(' OR ', $s_WHERE).')';
    	}
    	if($search['cus_name']>0){
    		$WHERE.=' AND c.id='.$search["cus_name"];
    	}
    	if($search['branch']>0){
    		$WHERE.=' AND c.branch_id='.$search["branch"];
    	}
    
    	$order=" ORDER BY c.id DESC ";
    	//$str_next = '+1 3 days';
    	$str_next = '+1 week';
    	$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
    	$to_date = (empty($search['end_date']))? '1': " (SELECT rent_end_date FROM rms_customer_paymentdetail as cd WHERE c.id = cd.cus_id and status=1 order by cd.id DESC LIMIT 1) <= '".$search['end_date']." 23:59:59'";
    	$WHERE .= " AND ".$to_date;
    	
    	$order = " order by (SELECT rent_end_date FROM rms_customer_paymentdetail as cd WHERE c.id = cd.cus_id and status=1 order by cd.id DESC LIMIT 1) DESC ";
    	
    	//echo $sql.$WHERE;
    	return $db->fetchAll($sql.$WHERE.$order);
    }
    
    
    
}