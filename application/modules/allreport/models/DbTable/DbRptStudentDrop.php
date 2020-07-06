<?php

class Allreport_Model_DbTable_DbRptStudentDrop extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_drop';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->branch_id;
    }
    
    public function getAllStudentDrop($search){
    	$db = $this->getAdapter();
    	
    	//print_r($search);exit();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("st.branch_id");
    	
    	$sql = "SELECT 
    				st.stu_code as stu_id, 
    				
    				(SELECT branch_namekh FROM rms_branch WHERE br_id = st.branch_id LIMIT 1) as branch_name,
    				st.stu_khname,
    				st.stu_enname,
    				CONCAT(st.stu_khname,' - ',st.stu_enname) as name,
			    	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=st.academic_year LIMIT 1) as academic_year,
			    	(SELECT name_en FROM rms_view WHERE rms_view.type=4 and rms_view.key_code=st.session LIMIT 1)AS session,
			    	(SELECT en_name FROM rms_dept WHERE dept_id = st.degree LIMIT 1) as degree,
			    	(SELECT major_enname FROM rms_major WHERE rms_major.major_id=st.grade LIMIT 1)AS grade,
					(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=st.sex LIMIT 1)AS sex,
					(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=stdp.`type` LIMIT 1) as type,
					stdp.note,stdp.reason,
					stdp.date,
					(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=6 and `rms_view`.`key_code`=`stdp`.`status` LIMIT 1)AS status
		 		FROM 
    				rms_student_drop as stdp,
    				rms_student as st 
    			WHERE 
    				stdp.stu_id=st.stu_id 
    				and stdp.status = 1 
    				and stdp.drop_from = 1
    				$branch_id
    		";

    	$WHERE=' ';
    	
    	$order=" order by stdp.id DESC ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}

    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`stdp`.`type` LIMIT 1) LIKE '%{$s_search}%'";
    		$WHERE .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	$from_date =(empty($search['start_date']))? '1': "stdp.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "stdp.date <= '".$search['end_date']." 23:59:59'";
    	$WHERE .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['study_year'])){
    		$WHERE.=' AND st.academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_all'])){
    		$WHERE.=' AND st.grade='.$search['grade_all'];
    	}
    	if(!empty($search['degree_all'])){
    		$WHERE.=' AND st.degree='.$search['degree_all'];
    	}
    	if(!empty($search['session'])){
    		$WHERE.=' AND st.session='.$search['session'];
    	}
    	if($search['branch'] > 0){
    		$WHERE.= " AND st.`branch_id` = ".$search['branch'];
    	}
    	if($search['user'] > 0){
    		$WHERE.= " AND stdp.`user_id` = ".$search['user'];
    	}
    	//echo $sql.$WHERE;
    	return $db->fetchAll($sql.$WHERE.$order);
    	 
    }
   
    
    public function getAllStudentDropTransport($search){
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("s.branch_id");
    	 
    	$sql = "SELECT
			    	s.stu_code ,
			    	
			    	(SELECT branch_namekh FROM rms_branch WHERE br_id = st.branch_id LIMIT 1) as branch_name,
			    	st.stu_khname,
    				st.stu_enname,
			    	CONCAT(st.stu_khname,' - ',st.stu_enname) as name,
			    	(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=st.sex LIMIT 1)AS sex,
			    	st.tel,
			    	
			    	(SELECT p.title FROM rms_program_name as p WHERE p.service_id = s.service_id LIMIT 1) as service_name,
			    	
			    	(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=stdp.`type` LIMIT 1) as type,
			    	stdp.reason,
			    	stdp.date,
			    	(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=6 and `rms_view`.`key_code`=`stdp`.`status` LIMIT 1)AS status
			    FROM
			    	rms_student_drop as stdp,
			    	rms_student as st,
			    	rms_service as s
			    WHERE
			    	stdp.stu_id=st.stu_id
			    	and s.stu_id = stdp.stu_id
			    	and stdp.status = 1
			    	and stdp.drop_from = 2
			    	and s.type=4
			    	$branch_id
    			";
    
    	$WHERE = ' ';
    	 
    	$order = " order by stdp.id DESC ";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
	    }
	    
	    if(!empty($search['title'])){
		    $s_where = array();
		    $s_search = addslashes(trim($search['title']));
		    $s_where[] = " s.stu_code LIKE '%{$s_search}%'";
		    $s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
		    $s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
		    $s_where[] = " (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`stdp`.`type` LIMIT 1) LIKE '%{$s_search}%'";
		    $WHERE .=' AND ( '.implode(' OR ',$s_where).')';
	    }
	    
	    $from_date =(empty($search['start_date']))? '1': "stdp.date >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': "stdp.date <= '".$search['end_date']." 23:59:59'";
	    $WHERE .= " AND ".$from_date." AND ".$to_date;
	     
	    if(!empty($search['service'])){
	    	$WHERE.=' AND s.service_id='.$search['service'];
	    }
    	if($search['branch'] > 0){
    		$WHERE.= " AND st.`branch_id` = ".$search['branch'];
    	}
	    if($search['user'] > 0){
	    	$WHERE.= " AND stdp.`user_id` = ".$search['user'];
	    }
	    return $db->fetchAll($sql.$WHERE.$order);
    }
    
    
    public function getAllStudentDropLunchAndStay($search){
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("s.branch_id");
    	 
    	$sql = "SELECT
			    	s.stu_code ,
			    	
			    	(SELECT branch_namekh FROM rms_branch WHERE br_id = st.branch_id LIMIT 1) as branch_name,
			    	st.stu_khname,
    				st.stu_enname,
			    	CONCAT(st.stu_khname,' - ',st.stu_enname) as name,
			    	(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=st.sex LIMIT 1)AS sex,
			    	st.tel,
			    	
			    	(SELECT p.title FROM rms_program_name as p WHERE p.service_id = s.service_id LIMIT 1) as service_name,
			    	
			    	(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=stdp.`type` LIMIT 1) as type,
			    	stdp.reason,
			    	stdp.date,
			    	(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=6 and `rms_view`.`key_code`=`stdp`.`status` LIMIT 1)AS status
			    FROM
			    	rms_student_drop as stdp,
			    	rms_student as st,
			    	rms_service as s
			    WHERE
			    	stdp.stu_id=st.stu_id
			    	and s.stu_id = stdp.stu_id
			    	and stdp.status = 1
			    	and stdp.drop_from = 3
			    	and s.type=5
			    	$branch_id
    			";
    
    	$WHERE = ' ';
    	 
    	$order = " order by stdp.id DESC ";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
	    }
	    
	    if(!empty($search['title'])){
		    $s_where = array();
		    $s_search = addslashes(trim($search['title']));
		    $s_where[] = " s.stu_code LIKE '%{$s_search}%'";
		    $s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
		    $s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
		    $s_where[] = " (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`stdp`.`type` LIMIT 1) LIKE '%{$s_search}%'";
		    $WHERE .=' AND ( '.implode(' OR ',$s_where).')';
	    }
	    
	    $from_date =(empty($search['start_date']))? '1': "stdp.date >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': "stdp.date <= '".$search['end_date']." 23:59:59'";
	    $WHERE .= " AND ".$from_date." AND ".$to_date;
	     
	    if(!empty($search['service'])){
	    	$WHERE.=' AND s.service_id='.$search['service'];
	    }
    	if($search['branch'] > 0){
    		$WHERE.= " AND st.`branch_id` = ".$search['branch'];
    	}
    	if (!empty($search['user'])){
		    if($search['user'] > 0){
		    	$WHERE.= " AND stdp.`user_id` = ".$search['user'];
		    }
    	}
	    return $db->fetchAll($sql.$WHERE.$order);
    
    }
    
    function getAllServiceByCategory($cate_id){
    	$db = $this->getAdapter();
    	$sql="SELECT service_id AS id,title AS name FROM rms_program_name WHERE ser_cate_id = $cate_id AND type=2 and status=1 ";
    	return $db->fetchAll($sql);
    }
    
    
    
    
}