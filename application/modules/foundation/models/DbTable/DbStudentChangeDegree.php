<?php

class Foundation_Model_DbTable_DbStudentChangeDegree extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_change_degree';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	public function getAllStudentID(){
		$_db = $this->getAdapter();
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission("branch_id");
		$sql = "SELECT stu_id,stu_code FROM `rms_student` where status = 1 and is_subspend=0 $branch_id ";
		$orderby = " ORDER BY stu_code ";
		return $_db->fetchAll($sql.$orderby);		
	}
	
	function getAllStudentName(){
		$_db = $this->getAdapter();
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission("branch_id");
		$sql = "SELECT stu_id,CONCAT(stu_khname,'-',stu_enname) as name FROM `rms_student` where status = 1 and is_subspend=0 $branch_id ";
		$orderby = " ORDER BY stu_code ";
		return $_db->fetchAll($sql.$orderby);
	}
	
	public function getAllStudentIDEdit(){
		$_db = $this->getAdapter();
		$sql = "SELECT stu_id,stu_code FROM `rms_student` where stu_type=1 and status = 1 ";
		//$orderby = " ORDER BY stu_code ";
		return $_db->fetchAll($sql);
	}
	
	public function getAllStudentChangeDegree($search){
		$_db = $this->getAdapter();
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission('cd.branch_id');
		$sql = "select 
					cd.id,
					s.stu_code,
					CASE WHEN stu_khname IS NULL THEN stu_enname ELSE stu_khname END as name,
					(SELECT CONCAT(from_academic,'-',to_academic,' ',(SELECT name_en FROM rms_view WHERE TYPE=7 AND key_code=time)) FROM rms_tuitionfee WHERE rms_tuitionfee.id=cd.old_academic_year) as old_academic_year, 
					(SELECT `en_name` FROM `rms_dept` WHERE `dept_id`=cd.old_degree ) AS old_degree,
					(SELECT `major_enname` FROM `rms_major` WHERE `major_id`=cd.old_grade ) AS old_grade,
					(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE `rms_view`.`type` = 4 AND `rms_view`.`key_code` = cd.old_session ) AS old_session,
					
					new_su_code,
					(SELECT CONCAT(from_academic,'-',to_academic,' ',(SELECT name_en FROM rms_view WHERE TYPE=7 AND key_code=time)) FROM rms_tuitionfee WHERE rms_tuitionfee.id=cd.academic_year) as academic_year, 
					(SELECT `en_name` FROM `rms_dept` WHERE `dept_id`=cd.degree ) AS degree,
					(SELECT `major_enname` FROM `rms_major` WHERE `major_id`=cd.grade ) AS grade,
					(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE `rms_view`.`type` = 4 AND `rms_view`.`key_code` = cd.session ) AS session,
					cd.note
				from 
					rms_student_change_degree as cd,
					rms_student as s
				where
					cd.stu_id = s.stu_id
					and cd.status=1
					$branch_id
			";
		$order_by=" order by id DESC";
		$where='';
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=" AND cd.old_academic_year = ".$search['study_year'];
		}
		if(!empty($search['degree_kh_ft'])){
			$where.=" AND cd.old_degree = ".$search['degree_kh_ft'];
		}
		if(!empty($search['grade_kh_ft'])){
			$where.=" AND cd.old_grade=".$search['grade_kh_ft'];
		}
		if(!empty($search['session'])){
			$where.=" AND cd.old_session=".$search['session'];
		}
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getStudentChangeBranchById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_change_branch WHERE id =".$id;
		return $db->fetchRow($sql);
	}
	
	
	public function addStudentChangeBranch($_data){
		try{	
			$_db = $this->getAdapter();
			$_db->beginTransaction();
			
			$code = new Registrar_Model_DbTable_DbRegister();
			$stu_code = $code->getNewAccountNumber($_data['degree'], $_data['old_branch']);
			
			$_arr = array(
					'branch_id'			=>$_data['old_branch'],
					'stu_id'			=>$_data['studentid'],
					
					'old_academic_year'	=>$_data['old_academic_year_id'],  
					'old_degree'		=>$_data['old_degree_id'],
					'old_grade'			=>$_data['old_grade_id'],
					'old_session'		=>$_data['old_session_id'],
					
					'new_su_code'		=>$stu_code,
					'academic_year'		=>$_data['academic_year'],
					'degree'			=>$_data['degree'],
					'grade'				=>$_data['grade'],
					'session'			=>$_data['session'],
					'note'				=>$_data['note'],
					
					'create_date'		=>date('Y-m-d H:i:s'),
					'user_id'			=>$this->getUserId(),
				);
			$id = $this->insert($_arr);
			
		// add new student info to tb_student
			$sql="select * from rms_student where stu_id = ".$_data['studentid'];
			$student_info = $_db->fetchRow($sql);
			
			$arr = array(
					'branch_id'			=>$_data['old_branch'],
					'stu_type'			=>1, // khmer
						
					'stu_enname'		=>$student_info['stu_enname'],
					'stu_khname'		=>$student_info['stu_khname'],
					
					'stu_code'			=>$stu_code,
					'academic_year'		=>$_data['academic_year'],
					'degree'			=>$_data['degree'],
					'grade'				=>$_data['grade'],
					'session'			=>$_data['session'],
					//'room'				=>$_data['room'],
					
					'sex'				=>$student_info['sex'],
					'dob'				=>$student_info['dob'],
					'pob'				=>$student_info['pob'],
					'tel'				=>$student_info['tel'],
					'email'				=>$student_info['email'],
					'address'			=>$student_info['address'],
					
					'home_num'			=>$student_info['home_num'],
					'street_num'		=>$student_info['street_num'],
					'village_name'		=>$student_info['village_name'],
					'commune_name'		=>$student_info['commune_name'],
					'district_name'		=>$student_info['district_name'],
					'province_id'		=>$student_info['province_id'],
					
					'father_enname'		=>$student_info['father_enname'],
					'father_khname'		=>$student_info['father_khname'],
					'father_dob'		=>$student_info['father_dob'],
					'father_nation'		=>$student_info['father_nation'],
					'father_job'		=>$student_info['father_job'],
					'father_phone'		=>$student_info['father_phone'],
					
					'mother_khname'		=>$student_info['mother_khname'],
					'mother_enname'		=>$student_info['mother_enname'],
					'mother_dob'		=>$student_info['mother_dob'],
					'mother_nation'		=>$student_info['mother_nation'],
					'mother_job'		=>$student_info['mother_job'],
					'mother_phone'		=>$student_info['mother_phone'],
					
					'guardian_enname'	=>$student_info['guardian_enname'],
					'guardian_khname'	=>$student_info['guardian_khname'],
					'guardian_dob'		=>$student_info['guardian_dob'],
					'guardian_nation'	=>$student_info['guardian_nation'],
					'guardian_job'		=>$student_info['guardian_job'],
					'guardian_tel'		=>$student_info['guardian_tel'],
					
					'user_id'			=>$this->getUserId(),
					'remark'			=>$_data['note'],
					'is_stu_new'		=>0, // old student change degree
					'create_date'		=>date("Y-m-d"),
				);
			
			$this->_name='rms_student';
			$stu_id = $this->insert($arr);
			
			
			
			$arr1=array(
					'new_stu_id'=>$stu_id,
			);
			$this->_name='rms_student_change_degree';
			$where = " id = $id";
			$this->update($arr1, $where);
			
			
			
		// update old student to stop
// 			$array = array(
// 					'is_subspend'	=>2,// stop
// 					);
// 			$where = " stu_id = ".$_data['studentid'];
// 			$this->update($array, $where);
			
			
		// add to table rms_student_id for count id 
			$arr1=array(
					'branch_id'		=>$_data['old_branch'],
					'stu_type'		=>1, // khmer
					'stu_id'		=>$stu_id,
					'degree'		=>$_data['degree'],
				);
			$this->_name='rms_student_id';
			$this->insert($arr1);
			
		// add to table rms_student_drop for student old degree
// 			$arr2=array(
// 					'type'		=>2,// stop
// 					'stu_id'	=>$_data['studentid'],
// 					'status'	=>1,
// 					'date'		=>date("Y-m-d"),
// 					'user_id'	=>$this->getUserId(),
// 					'reason'	=>$_data['note'],
// 			);
// 			$this->_name='rms_student_drop';
// 			$this->insert($arr2);
			
		// get last paymet id
			$sql="select id from rms_student_payment where payfor_type=1 and is_void=0 and student_id = ".$_data['studentid']." order by id DESC limit 1";
			$payment_id = $_db->fetchOne($sql);
		// update last payment info to new student that just insert
			if(!empty($payment_id)){
				$arra = array(
						'student_id'=>$stu_id,
					);
				$where = " id = $payment_id ";
				$this->_name='rms_student_payment';
				$this->update($arra, $where);
			}
			
			$_db->commit();
			
		}catch(Exception $e){
			$_db->rollBack();
			echo $e->getMessage();exit();
		}
	}
	public function updateStudentChangeBranch($_data){
		$db= $this->getAdapter();
		try{	
			if($_data['status']==0){
				$this->_name='rms_student_change_branch';
				$arr = array(
						'status'=>0,
						);
				$where=" id = ".$_data['id'];
				$this->update($arr, $where);
				
			// update student branch to old_branch	
				$this->_name='rms_student';
				$where=" stu_id=".$_data['studentid'];
				$arr=array(
						'branch_id'	=>	$_data['old_branch'],
				);
				$this->update($arr, $where);
				
			// update study_history

				$this->_name='rms_study_history';
				$sql="select id_record_finished,id from rms_study_history where is_finished_grade = 0 and status=1  and stu_id = ".$_data['studentid']." limit 1";
				$study_history = $db->fetchRow($sql);
				if(!empty($study_history)){
				
				// update old record back to using	
					$arra = array(
							'is_finished_grade'=>0,
							);
					$where = "id = ".$study_history['id_record_finished'];
					$this->update($arra, $where);
					
				// update new record to not use(deactive)	
					$array=array(
							'status'=>0,
							);
					$where = " id = ".$study_history['id'];
					$this->update($array, $where);
				}
				
			}else{
				$this->_name='rms_student_change_branch';
				$_arr=array(
					'new_branch'	=>$_data['new_branch'],
					'reason'		=>$_data['reason'],
					'user_id'		=>$this->getUserId(),
						);
				$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
				$this->update($_arr, $where);
				
				
				
			// update student branch to new_branch that updated	
				$this->_name='rms_student';
				$where=" stu_id=".$_data['studentid'];
				$arr=array(
						'branch_id'	=>	$_data['new_branch'],
				);
				$this->update($arr, $where);
			
				
				$this->_name='rms_study_history';
				$array=array(
						'branch_id'=>$_data['new_branch'],
				);
				$where = " stu_id = ".$_data['studentid']." and is_finished_grade = 0 and status=1 " ;
				$this->update($array, $where);
			}
			
			$db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
		$sql = "SELECT CONCAT(st.stu_khname,' - ',st.stu_enname) as name,st.sex,st.branch_id,
			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')',' ',(SELECT name_en FROM rms_view WHERE TYPE=7 AND key_code=time)) FROM rms_tuitionfee WHERE rms_tuitionfee.id=st.academic_year) as academic_year, 
			st.academic_year as academic_year_id,
			st.degree as degree_id,
			st.grade as grade_id,
			st.session as session_id,
			(SELECT `en_name` FROM `rms_dept` WHERE `dept_id`=st.degree ) AS degree,
			(SELECT `major_enname` FROM `rms_major` WHERE `major_id`=st.grade ) AS grade,
			(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE `rms_view`.`type` = 4 AND `rms_view`.`key_code` = st.session ) AS session
			FROM `rms_student` as st WHERE stu_id=$stu_id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	
	function getAllBranch(){	
		$db = $this->getAdapter();
		$sql = "SELECT br_id As id,branch_namekh as name FROM rms_branch WHERE status = 1 ";
		return $db->fetchAll($sql);
	}
	
	function getAllKhDegree(){
		$db = $this->getAdapter();
		$sql = "SELECT dept_id as id ,en_name as name FROM rms_dept WHERE is_active = 1 and dept_id IN(1,2,3) ";
		return $db->fetchAll($sql);
	}
	
	function getAllKhGrade(){
		$db = $this->getAdapter();
		$sql = "SELECT major_id as id ,major_enname as name FROM rms_major WHERE is_active = 1 and dept_id IN(1,2,3) ";
		return $db->fetchAll($sql);
	}
	
	function getAllSession(){
		$db = $this->getAdapter();
		$sql = "SELECT key_code as id ,name_kh as name FROM rms_view WHERE status = 1 and type=4";
		return $db->fetchAll($sql);
	}
	
	
}












