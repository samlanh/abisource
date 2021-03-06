<?php
class Global_Model_DbTable_DbBookAndUniform extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_program_name';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    public function addProduct($_data){
    	$db = $this->getAdapter();
    		$_arr = array(
    				'title'		=>$_data['title'],
    				'type'		=>1, // product
    				'product_type'=>$_data['product_type'], 
    				'ser_cate_id'=>5,// product category
    				'description'=>$_data['desc'],
    				'price'		=>$_data['price'],
    				'create_date'=>Zend_Date::now(),
    				'status'	=>1,
    				'user_id'	=>$this->getUserId(),
    		);
    		return ($this->insert($_arr));
    } 
    public function updateProduct($_data){
    	$_arr=array(
	    			'title'		=>$_data['title'],
    				'product_type'=>$_data['product_type'],
    				'description'=>$_data['desc'],
    				'status'	=>$_data['status'],
    				'price'		=>$_data['price'],
    				'user_id'	=>$this->getUserId(),
    			);
    	$where=' service_id='.$_data['id'];
    	return $this->update($_arr, $where);
    }
    public function getServiceById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_program_name WHERE service_id = ".$id;
    	return $db->fetchRow($sql);
    }	
    
    public function getAllServiceNames($search=''){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				service_id,
    				title,
    				(select name_en from rms_view where type=14 and key_code = product_type LIMIT 1) as product_type,
    				description,
    				price,
    				status,
    				create_date,
			    	(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE user_id=id LIMIT 1) AS user_name
			      FROM 
    				rms_program_name 
    			  Where 
    				type=1 
    		";
    	
    	$order=" ORDER BY service_id DESC";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	$where="";
	    if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
		 	$s_where[] = " title LIKE '%{$s_search}%'";
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



