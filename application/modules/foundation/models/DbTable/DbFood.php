<?php

class Foundation_Model_DbTable_DbFood extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_service';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	public function getAllStudentTransport($search){
		$_db = $this->getAdapter();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission("ser.branch_id");
		
		$sql = "SELECT 
					ser.id,
					ser.stu_code,
					s.stu_khname,
					s.stu_enname,
					s.tel,
					(select title from rms_program_name where service_id = ser.service_id) as service_name
				from 
					rms_student as s,
					rms_service as ser
				where 
					s.stu_id=ser.stu_id 
					and ser.type=5
					and ser.is_suspend=0
					$branch_id
				";
		
		$order_by = " order by ser.stu_code ASC";
		
		$where=' ';
		
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		
		$from_date =(empty($search['start_date']))? '1': "ser.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "ser.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " ser.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		
		if(!empty($search['branch'])){
			$where.=" AND ser.branch_id = ".$search['branch'];
		}
		if(!empty($search['service'])){
			$where.=" AND ser.service_id = ".$search['service'];
		}
		
		return $_db->fetchAll($sql.$where.$order_by);
	}
	
	public function getStudentFood($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  ser.*,
				  s.`stu_enname`,
				  s.`stu_khname` 
				FROM
				  rms_service AS ser,
				  `rms_student` AS s 
				WHERE 
					s.`stu_id`=ser.`stu_id`	
					AND id = $id
				LIMIT 1
			";
		return $db->fetchRow($sql);
	}
	
	
	public function updateStudentChangeCar($_data){
		$db= $this->getAdapter();
		$db->beginTransaction();
		try{	
			$_arr=array(
					'stu_code'	=>$_data['stu_code'],
					'service_id'=>$_data['service'],
					);
			$this->_name = 'rms_service';
			$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
			$this->update($_arr, $where);
			
			$db->commit();
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	
	function getAllFoodService(){
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission("(branch=50 OR branch ",1);
		
		$db = $this->getAdapter();
		$sql = "select service_id as id,title as name from rms_program_name where type=2 and ser_cate_id = 2 and status =1 $branch_id ";
		return $db->fetchAll($sql);
	}
	
}

