<?php
class Accounting_Model_DbTable_DbStudentTest extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_test';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->branch_id;
	}
	function addStudentTest($data){
		//print_r($data);exit();
		
		if($data['dob']==""){
			$dob = null;
		}else{
			$dob = $data['dob'];
		}
		
		$status = 1;
		if(!empty($data['is_void'])){
			$status = 0 ;
		}
		
		if($data['shift']==1){
			$create_date = $data['create_date']." 10:00:00" ;
		}else if($data['shift']==2){
			$create_date = $data['create_date']." 14:00:00" ;
		}else if($data['shift']==3){
			$create_date = $data['create_date']." 18:00:00" ;
		}
		
		$array = array(
					'branch_id'	=>$data['branch'],
					'receipt'	=>$data['receipt'],
					'kh_name'	=>$data['kh_name'],
					'en_name'	=>$data['en_name'],
					'sex'		=>$data['sex'],
					'dob'		=>$dob,
					'phone'		=>$data['phone'],
					'degree'	=>$data['degree'],
					'note'		=>$data['note'],
					'address'	=>$data['address'],
					'user_id'	=>$this->getUserId(),
					'total_price'=>$data['test_cost'],
					'create_date'=>$create_date,
					'shift'		=>$data['shift'],
					'status'	=>$status,
				);
		$this->insert($array);
 	}
	function updateStudentTest($data,$id){
		
		$updated_result = 0;
		if(!empty($data['degree_result']) && !empty($data['grade_result']) && !empty($data['session_result'])){
			$updated_result = 1;
		}
		
		if($data['dob']==""){
			$dob = null;
		}else{
			$dob = $data['dob'];
		}
		
		$status = 1;
		if(!empty($data['is_void'])){
			$status = 0;
		}
		
		if($data['shift']==1){
			$create_date = $data['create_date']." 10:00:00" ;
		}else if($data['shift']==2){
			$create_date = $data['create_date']." 14:00:00" ;
		}else if($data['shift']==3){
			$create_date = $data['create_date']." 18:00:00" ;
		}
		
		$array = array(
					'branch_id'	=>$data['branch'],
					'receipt'	=>$data['receipt'],
					'kh_name'	=>$data['kh_name'],
					'en_name'	=>$data['en_name'],
					'sex'		=>$data['sex'],
					'dob'		=>$dob,
					'phone'		=>$data['phone'],
					'degree'	=>$data['degree'],
					'note'		=>$data['note'],
					'address'	=>$data['address'],
					//'user_id'	=>$this->getUserId(),
					'total_price'=>$data['test_cost'],
					'status'	=>$status,
				
					'create_date'=>$create_date,
					'shift'		=>$data['shift'],
				
// 					'degree_result'	=>$data['degree_result'],
// 					'grade_result'	=>$data['grade_result'],
// 					'session_result'=>$data['session_result'],
				
// 					'updated_result'=>$updated_result,
				
				);
		$where="id = $id";
		$this->update($array, $where);
	}
	
	function getStudentTestById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_student_test where id=$id ";
		return $db->fetchRow($sql);
	}	
	function getAllStudentTest($search=null){
		$db = $this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal;
		$branch_id = $_db->getAccessPermission('branch_id');
		
		$session_user=new Zend_Session_Namespace('authstu');
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		
		$where = " and ".$from_date." AND ".$to_date;
		
		$sql="  SELECT 
					id,
					(SELECT branch_namekh FROM rms_branch WHERE br_id = branch_id limit 1) as branch,
					receipt,
					kh_name,
					en_name,
					(SELECT name_kh FROM rms_view WHERE type=2 and key_code=sex LIMIT 1) as sex,
					dob,
					phone,
					(SELECT en_name FROM rms_dept WHERE dept_id=degree LIMIT 1) as degree,
					note,
					total_price,
					(SELECT first_name FROM `rms_users` WHERE id=rms_student_test.user_id LIMIT 1),
					(SELECT name_en FROM rms_view WHERE type=1 and key_code=rms_student_test.status LIMIT 1) as status,
					'delete'
				FROM 
					rms_student_test
				WHERE
					register=0 
					$branch_id
				";
		
		if (!empty($search['txtsearch'])){
				$s_where = array();
				$s_search = trim(addslashes($search['txtsearch']));
				$s_where[] = " kh_name LIKE '%{$s_search}%'";
				$s_where[] = " en_name LIKE '%{$s_search}%'";
				$s_where[] = " receipt LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
		}      
		
		if(!empty($search['branch'])){
			$where.=" AND branch_id=".$search['branch'];
		}
		if($search['status_search']!=""){
			$where.=" AND status=".$search['status_search'];
		}
		
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}	
	
	function getNewReceiptNumber($branch){
		$db=$this->getAdapter();
		
		if($branch==0){
			$branch_id = $this->getBranchId();
		}else{
			$branch_id = $branch;
		}
		
		$sql="SELECT count(id) FROM rms_student_test WHERE branch_id = $branch_id ";
		$result = $db->fetchOne($sql);
		
		$new_acc_no = (int)$result+1;
		$length = strlen($new_acc_no);
		
		$pre="TEST";
		
		for($i = $length;$i<6;$i++){
			$pre.='0';
		}
		
		return $pre.$new_acc_no;
		
	}
	
	function getAllDegreeName(){
		$db = $this->getAdapter();
		$sql="SELECT dept_id as id,en_name as name FROM rms_dept WHERE is_active = 1 ";
		return $db->fetchAll($sql);
	}
	
	function getAllSession(){
		$db = $this->getAdapter();
		$sql="SELECT key_code as id,name_en as name FROM rms_view WHERE type = 4 and status = 1 ";
		return $db->fetchAll($sql);
	}
	
	function getDegreeTypeByid($degree_id){
		$db = $this->getAdapter();
		$sql="SELECT dep.`type` FROM `rms_dept` AS dep WHERE dep.`dept_id`=$degree_id";
		return $db->fetchOne($sql);
	}
	
	function deleteRecord($id){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$this->_name = "rms_student_test";
		$where = "id = $id";
		$this->delete($where);
	}
	
	
	
	
}