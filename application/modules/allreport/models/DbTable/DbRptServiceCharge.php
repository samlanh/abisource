<?php

class Allreport_Model_DbTable_DbRptServiceCharge extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_servicefee';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
    	 
//     }
    function getAllServiceFee($search){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT 
    				sf.id,
    				CONCAT(from_academic,'-',to_academic,'(',(SELECT branch_namekh FROM rms_branch WHERE br_id = branch_id),')') AS years
    			FROM 
    				`rms_servicefee` as sf
    			where 
    				sf.status=1 
    				$branch_id
    		";
    	
    	$order=" ORDER BY sf.id DESC ,sf.branch_id ASC";
    	$where=' ';
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	if(!empty($search['study_year'])){
    		$where.=" AND sf.id = ".$search['study_year'] ;
    	}
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " CONCAT(from_academic,'-',to_academic) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
//     	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    	
    }    
    function getServiceFeebyId($service_id,$service_type,$serid){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				sd.id,
    				sd.service_id,
    				sd.price_fee,
    				sd.payment_term,
    				sd.remark,
    				p.title AS service_name ,
    				(SELECT pt.title FROM `rms_program_type` AS pt WHERE pt.id=p.ser_cate_id LIMIT 1) as ser_type
    			FROM 
    				`rms_servicefee_detail` as sd,
    				rms_program_name p 
    			WHERE 
    				p.service_id=sd.service_id 
    				AND sd.service_feeid=".$service_id;
    	
    	if($service_type>0){
    		$sql.=" AND p.ser_cate_id = ".$service_type;
    	}
    	if($serid>0){
    		$sql.=" AND sd.service_id = ".$serid;
    	}
    	$sql.=" ORDER BY p.ser_cate_id DESC ";
    	return $db->fetchAll($sql);
    }

    function getAllYearService(){
    	$db=$this->getAdapter();
    	$sql=" select 
    				CONCAT(from_academic,'-',to_academic,'(',(SELECT branch_namekh FROM rms_branch WHERE br_id = branch_id LIMIT 1),')')as year ,
    				sf.id 
    			from 
    				rms_servicefee as sf
    			where 
    				sf.status=1 
    			order by 
    				id ASC
    		";
    	return $db->fetchAll($sql);
    }	
    
    function getServicesAll(){
    	$db=$this->getAdapter();
    	$sql="SELECT service_id AS id,title FROM rms_program_name WHERE `type` = 2 AND title!='' order by ser_cate_id DESC";
    	return $db->fetchAll($sql);
    }
    
    function getServicesType(){
    	$db=$this->getAdapter();
    	$sql="SELECT id,title FROM rms_program_type WHERE title!='' and id IN(2,3) order by id DESC";
    	return $db->fetchAll($sql);
    }
    
}




