<?php

class Global_Model_DbTable_DbServiceType extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_program_type';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function AddServiceType($_data){
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$_arr = array(
    			'title'=>$_data['title'],
    			'item_desc'=>$_data['item_desc'],
    			'type'=>$_data['type'],
    			'create_date'=> new Zend_Date(),
    			'user_id' => $this->getUserId(),
    			);
    	
    	if(!empty($_data['id'])){
    		$db = $this->getAdapter();
    		$_arr['status']=$_data['status'];
    		$where = $db->quoteInto('id=?', $_data['id']);
    		$this->update($_arr, $where);
    	}else{
    		$_arr['status']=1;
    		$this->insert($_arr);
    	}
    }
    
    public function sqlServiceType($search=''){
    	$sql = "SELECT id,title,item_desc,status
    		,create_date
    		,(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE user_id=id ) AS user_name
    		,type FROM rms_program_type
    	WHERE 1";
    	$order=" ORDER BY title";
    	$where = '';
    	if(empty($search)){
    		return $sql.$order;
    	}
    	if(!empty($search['txtsearch'])){
    		$where.=" AND title LIKE '%".$search['txtsearch']."%'";
    	}
    	if($search['type']>-1){
    		$where.= " AND type = ".$search['type'];
    	}
    	if($search['status']>-1){
    		$where.= " AND status = '".$search['status']."'";
    	}
    	//print_r($search);exit();
    	return $sql.$where.$order;
    }
    function getAllServiceType($search, $start, $limit){
    
    	$sql_rs = $this->sqlServiceType($search)." LIMIT ".$start.", ".$limit;
    	if ($limit == 'All') {
    		$sql_rs = $this->sqlServiceType($search);
    	}
    	$sql_count = $this->sqlServiceType();
    	if(!empty($search)){
    		$sql_count = $this->sqlServiceType($search);
    	}
    	$_db = new Application_Model_DbTable_DbGlobal();
    	return($_db ->getGlobalResultList($sql_rs,$sql_count));
    	// 		return array(0=>$rows,1=>$_count);//get all result by param 0 ,get count record by param 1
    }
    //////////////
    public function getAllServicesType($search=''){
		$db = $this->getAdapter();
    	$sql = "SELECT id,title,item_desc
    	,type,status
    	,create_date
    	,(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE user_id=id ) AS user_name
    	 FROM rms_program_type
    	WHERE 1";
    	$order=" ORDER BY id DESC";
    	$where = '';
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=9 and rms_view.key_code=rms_program_type.type) LIKE '%{$s_search}%'";
//     		$s_where[] = " kh_name LIKE '%{$s_search}%'";
//     		$s_where[] = " en_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function getServiceTypeById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_program_type WHERE id = $id limit 1";
    	return $db->fetchRow($sql);
    	
    }
}



