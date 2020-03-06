<?php

class Foundation_Model_DbTable_DbStudentChangeCar extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_change_car';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	public function getAllStudentTransportID(){
		$_db = $this->getAdapter();
		
		$db=new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission("s.branch_id");
		
		$sql = "SELECT s.stu_id, s.stu_code FROM `rms_student` as st,rms_service as s where s.status = 1 and s.stu_id=st.stu_id and s.type=4 and s.is_suspend=0 $branch_id ";
		$orderby = " ORDER BY s.id ";
		return $_db->fetchAll($sql.$orderby);		
	}
	
	function getAllStudentTransportName(){
		$_db = $this->getAdapter();
		
		$db=new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission("s.branch_id");
		
		$sql = "SELECT s.stu_id, CONCAT(st.stu_enname,'-',st.stu_khname) as name FROM `rms_student` as st,rms_service as s where s.status = 1 and s.stu_id=st.stu_id and s.type=4 and s.is_suspend=0 $branch_id ";
		$orderby = " ORDER BY s.id ";
		return $_db->fetchAll($sql.$orderby);
	}
	
	public function getAllStudentDropTransport($search){
		$_db = $this->getAdapter();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission("ser.branch_id");
		
		$sql = "SELECT 
					scc.id,
					ser.stu_code,
					s.stu_khname,
					s.stu_enname,
					s.tel,
					
					(select title from rms_program_name where service_id = ser.service_id) as service_name,
					(select carid from rms_car as c where c.id = scc.old_car_id) as old_car_id,
					(select carid from rms_car as c where c.id = scc.new_car_id) as new_car_id,
					
					scc.note,
					scc.change_date 
				from 
					rms_student as s,
					rms_service as ser,
					rms_student_change_car as scc
				where 
					s.stu_id=ser.stu_id 
					and ser.stu_id = scc.student_id
					and scc.status=1 
					and ser.type=4
					$branch_id
				";
		
		$order_by = " order by scc.id DESC";
		
		$where=' ';
		
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " ser.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		
		$from_date =(empty($search['start_date']))? '1': "scc.change_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "scc.change_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['branch'])){
			$where.=" AND ser.branch_id = ".$search['branch'];
		}
		if(!empty($search['service'])){
			$where.=" AND ser.service_id = ".$search['service'];
		}
		
		return $_db->fetchAll($sql.$where.$order_by);
	}
	
	public function getStudentChangeCar($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_change_car WHERE id =".$id;
		return $db->fetchRow($sql);
	}
	public function addStudentChangeCar($_data){
		try{	
			$_db= $this->getAdapter();
			$_db->beginTransaction();
			
			$_arr= array(
					'student_id'	=>$_data['studentid'],
					
					'service_id'	=>$_data['service'],
					'old_car_id'	=>$_data['old_car_id'],
					'new_car_id'	=>$_data['new_car_id'],
					
					'note'			=>$_data['note'],
					'change_date'	=>$_data['change_date'],
					'create_date'	=>date("Y-m-d"),
					'user_id'		=>$this->getUserId(),
					);
			$id = $this->insert($_arr);
			
			$this->_name = 'rms_service';
			$where = " stu_id = ".$_data['studentid']." and type = 4 and service_id = ".$_data['service'];
			$arr=array(
				'car_id'=>$_data['new_car_id'],
			);
			$this->update($arr, $where);
			
			
			$_db->commit();
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	
	
	public function updateStudentChangeCar($_data){
		$db= $this->getAdapter();
		$db->beginTransaction();
		try{	
			$_arr=array(
					'student_id'	=>$_data['studentid'],
					
					'service_id'	=>$_data['service'],
					'old_car_id'	=>$_data['old_car_id'],
					'new_car_id'	=>$_data['new_car_id'],
					
					'note'			=>$_data['note'],
					'change_date'	=>$_data['change_date'],
					'user_id'		=>$this->getUserId(),
					);
			$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
			$this->update($_arr, $where);
			
			
			$this->_name = 'rms_service';
			$where = " stu_id = ".$_data['studentid']." and type = 4 and service_id = ".$_data['service'];
			$arr=array(
				'car_id'=>$_data['new_car_id'],
			);
			$this->update($arr, $where);
			
			
			$db->commit();
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	function getAllGrade($grade_id){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,major_enname As name FROM rms_major WHERE dept_id=".$grade_id;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					CONCAT(st.stu_khname,' - ',st.stu_enname) as name,
					st.sex,
					st.tel,
					(select s.service_id from rms_service as s where s.stu_id = st.stu_id and type=4 limit 1) as service,
					(select s.car_id from rms_service as s where s.stu_id = st.stu_id and type=4 limit 1) as car_id
				FROM 
					`rms_student` as st
				WHERE 
					stu_id=$stu_id LIMIT 1 
			";
		
		return $db->fetchRow($sql);
	}
	
	function getAllTransportService(){
		$db = $this->getAdapter();
		$sql = "select service_id as id,title as name from rms_program_name where type=2 and ser_cate_id = 3 and status =1 ";
		return $db->fetchAll($sql);
	}
	
	function getAllCar(){
		$db = $this->getAdapter();
		$sql = "select id,carid as name from rms_car where status = 1 ";
		return $db->fetchAll($sql);
	}
	
	
}

