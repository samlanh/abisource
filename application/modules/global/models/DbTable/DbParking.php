<?php

class Global_Model_DbTable_DbParking extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_parking';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    function getAllParking($search=null){
    	$db = $this->getAdapter();
    	$sql = " SELECT 
    					id,
    					(select branch_namekh from rms_branch where br_id = branch_id limit 1) as branch,
    					customer_code, 
    					name,
    					(select name_kh from rms_view where type=2 and key_code = rms_parking.sex limit 1) as sex,
    					phone,
    					address,
    					(SELECT  CONCAT(first_name,' ', last_name) FROM rms_users WHERE rms_users.id=user_id )AS user_name,
    					(select name_kh from rms_view where type=1 and key_code = rms_parking.status limit 1) as status
				    FROM 
				     	rms_parking
				    WHERE 
	    				1 
    			";
    	$order=" order by id DESC ";
    	$where = ' ';
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=trim(addslashes($search['title']));
    		$s_where[]=" customer_code LIKE '%{$s_search}%'";
    		$s_where[]=" name LIKE '%{$s_search}%'";
    		$s_where[]=" phone LIKE '%{$s_search}%'";
    		$s_where[]=" address LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status_search']!=''){
    		$where.= " AND status = ".$search['status_search'];
    	}
    	if(!empty($search['branch'])){
    		$where.=" AND branch_id=".$search['branch'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
	public function addParking($_data){
		$arr=array(
				'branch_id'		=> $_data['branch'],
				'customer_code'	=> $_data['code'],
				'name'			=> $_data['name'],
				'sex'	  		=> $_data['sex'],
				'phone'	  		=> $_data['phone'],
				'address'	  	=> $_data['address'],
				'create_date' 	=> date("Y-m-d"),
				'user_id'	  	=> $this->getUserId()
		);
		return  $this->insert($arr);
	}
	public function getTimeById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_parking WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateParking($data){
		$_arr=array(
				'branch_id'		=> $data['branch'],
				'customer_code'	=> $data['code'],
				'name'			=> $data['name'],
				'sex'	  		=> $data['sex'],
				'phone'	  		=> $data['phone'],
				'address'	  	=> $data['address'],
				'status' 	  	=> $data['status'],
				'user_id'	 	=> $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("id=?", $data["id"]);
		$this->update($_arr, $where);
	}
	
}

