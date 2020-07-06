<?php

class Allreport_Model_DbTable_DbRptAllStudent extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
    	 
//     }
    public function getAllStudent($search){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql ="SELECT 
    				stu_id,
    				
    				(SELECT branch_namekh FROM rms_branch WHERE br_id = branch_id LIMIT 1) as branch_name,
			        (SELECT last_name FROM rms_users as u WHERE u.id = user_id LIMIT 1) as user_name,
    				
    				CONCAT(stu_enname,'-',stu_khname)AS name,
    				stu_enname,
    				stu_khname,
    				nationality,
    				tel,
    				email,
    				stu_code,
    				home_num,
    				street_num,
    				village_name,
    		  		commune_name,
    		  		district_name,
	    		   (SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=academic_year LIMIT 1) as academic_year,	
	    		   (SELECT name_kh FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=rms_student.session LIMIT 1)AS session,
	    		   (SELECT major_enname FROM rms_major WHERE rms_major.major_id=rms_student.grade LIMIT 1)AS grade,
	    		   (SELECT en_name FROM rms_dept WHERE rms_dept.dept_id=rms_student.degree LIMIT 1)AS degree,
    		  
    		  		(SELECT name_en FROM rms_view WHERE type=5 AND key_code=is_subspend LIMIT 1) as status,
    		  
	    		   (SELECT province_en_name FROM rms_province WHERE rms_province.province_id = rms_student.province_id LIMIT 1)AS province,	   	
	    		   (SELECT name_en FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=rms_student.sex LIMIT 1)AS sex
    		  	FROM 
    				rms_student 
    			WHERE 
    				status=1
    				$branch_id
    		";
    	
    	$WHERE=" ";
    	
    	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$WHERE .= " AND ".$from_date." AND ".$to_date;
    	
    	$order="  order by branch_id,stu_id,degree,grade,academic_year DESC";
    	//$group_by=" GROUP BY academic_year,grade,session,degree ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT en_name FROM rms_dept WHERE rms_dept.dept_id=rms_student.degree LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT major_enname FROM rms_major WHERE rms_major.major_id=rms_student.grade LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT name_en FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=rms_student.session LIMIT 1) LIKE '%{$s_search}%'";
    		$WHERE .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$WHERE.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['degree_type'])){
    		$WHERE.=' AND (SELECT rms_dept.type FROM rms_dept WHERE rms_dept.dept_id=rms_student.degree LIMIT 1) = '.$search['degree_type'];
    	}
    	if(!empty($search['degree_all'])){
    		$WHERE.=' AND degree = '.$search['degree_all'];
    	}
    	if(!empty($search['grade_all'])){
    		$WHERE.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$WHERE.=' AND session='.$search['session'];
    	}
    	if(!empty($search['room'])){
    		$WHERE.=' AND room = '.$search['room'];
    	}
    	if(!empty($search['branch'])){
    		$WHERE.=' AND branch_id='.$search['branch'];
    	}
    	
    	if($search['stu_type']==0){
    		$WHERE.=' AND is_stu_new = 0 AND is_subspend=0 ';
    	}else if($search['stu_type']==1){
    		$WHERE.=' AND is_stu_new = 1 AND is_subspend = 0 AND is_comeback = 0 ';
    	}else if($search['stu_type']==2){
    		$WHERE.=' AND is_subspend != 0 ';
    	}else if($search['stu_type']==3){
    		$WHERE.=' AND is_comeback = 1 AND is_subspend = 0';
    	}else if($search['stu_type']==4){
    		$WHERE.=' AND is_stu_new = 1 AND is_subspend = 0 ';
    	}
    	
    	//echo $sql.$WHERE.$order;exit();
    	return $db->fetchAll($sql.$WHERE.$order);
    	 
    }
   
    public function getAllAmountStudent($search){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql ="SELECT 
    				stu_id,
    				
    				(SELECT branch_namekh FROM rms_branch WHERE br_id = branch_id LIMIT 1) as branch_name,
			        (SELECT last_name FROM rms_users as u WHERE u.id = user_id LIMIT 1) as user_name,
    				
    				CONCAT(stu_enname,' - ',stu_khname)AS name,nationality,tel,email,stu_code,home_num,street_num,village_name,
			    	commune_name,district_name,
			    	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=academic_year LIMIT 1) as academic_year,
			    	(SELECT name_en FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=rms_student.session LIMIT 1)AS session,
			    	(SELECT major_enname FROM rms_major WHERE rms_major.major_id=rms_student.grade LIMIT 1)AS grade,
			    	(SELECT en_name FROM rms_dept WHERE rms_dept.dept_id=rms_student.degree LIMIT 1)AS degree,
			    
			    	(SELECT name_en FROM rms_view WHERE type=5 AND key_code=is_subspend LIMIT 1) as status,
			    
			    	(SELECT province_en_name FROM rms_province WHERE rms_province.province_id = rms_student.province_id LIMIT 1)AS province,
			    	(SELECT name_en FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=rms_student.sex LIMIT 1)AS sex
			    FROM 
    				rms_student 
    			WHERE 
    				status = 1 
    				$branch_id
    		";
    	$WHERE = ' ';
    	 
    	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$WHERE .= " AND ".$from_date." AND ".$to_date;
    	 
    	$order="  order by branch_id ASC,academic_year DESC,degree ASC,grade ASC,session ASC,stu_id ASC";
    	//$group_by=" GROUP BY academic_year,grade,session,degree ";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " CONCAT(stu_enname,stu_khname) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT en_name FROM rms_dept WHERE rms_dept.dept_id=rms_student.degree LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT major_enname FROM rms_major WHERE rms_major.major_id=rms_student.grade LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT name_en FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=rms_student.session LIMIT 1) LIKE '%{$s_search}%'";
    		$WHERE .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$WHERE.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_all'])){
    		$WHERE.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$WHERE.=' AND session='.$search['session'];
    	}
    	if(!empty($search['branch'])){
    		$WHERE.=' AND branch_id='.$search['branch'];
    	}
    	 
    	return $db->fetchAll($sql.$WHERE.$order);
    
    }
       
}