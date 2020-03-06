<?php

class Allreport_Model_DbTable_DbRptMaterialFee extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_program_name';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
    	 
//     }
    public function getAllMaterialFee($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    					title as name,
    					(select name_en from rms_view where type=14 and key_code = product_type) as product_type,
    					price,
    					description as note,
    					(select name_kh from rms_view where type=1 and key_code = pn.status) as status
    				FROM 
    					rms_program_name as pn
    				where 
    					ser_cate_id = 5	
    		";
    	
    	$where=' ';
    	$order=" order by product_type ASC , service_id DESC";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$s_where[] = " price LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where .=' AND status = '.$search['status'];
    	}
    	if(!empty($search['product_type'])){
    		$where .=' AND product_type = '.$search['product_type'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
   
    
    
    
    
    
    
}