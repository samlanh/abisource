<?php

class Allreport_Model_DbTable_DbRptStudentChangeCar extends Zend_Db_Table_Abstract
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
    
    
    public function getAllStudentChangeCar($search){
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("s.branch_id");
    	 
    	$sql = "SELECT
			    	s.stu_code ,
			    	
			    	(select branch_namekh from rms_branch where br_id = st.branch_id) as branch_name,
			    	
			    	CONCAT(st.stu_khname,' - ',st.stu_enname) as name,
			    	st.tel,
			    	
			    	(select p.title from rms_program_name as p where p.service_id = s.service_id) as service_name,
			    	(select carid from rms_car as c where c.id = scc.old_car_id) as old_car_id,
					(select carid from rms_car as c where c.id = scc.new_car_id) as new_car_id,
					
			    	scc.note,
			    	scc.change_date,
			    	(select first_name from rms_users as u where u.id=scc.user_id) as user
			    from
			    	rms_student_change_car as scc,
			    	rms_student as st,
			    	rms_service as s
			    where
			    	scc.student_id=st.stu_id
			    	and s.stu_id = scc.student_id
			    	and scc.status = 1
			    	and s.type=4
			    	$branch_id
    			";
    
    	$where = ' ';
    	 
    	$order = " order by scc.id DESC ";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
	    }
	    
	    if(!empty($search['title'])){
		    $s_where = array();
		    $s_search = addslashes(trim($search['title']));
		    $s_where[] = " s.stu_code LIKE '%{$s_search}%'";
		    $s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
		    $s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
		    $where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
	    
	    $from_date =(empty($search['start_date']))? '1': "scc.change_date >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': "scc.change_date <= '".$search['end_date']." 23:59:59'";
	    $where .= " AND ".$from_date." AND ".$to_date;
	     
	    if(!empty($search['service'])){
	    	$where.=' AND scc.service_id='.$search['service'];
	    }
    	if($search['branch'] > 0){
    		$where.= " AND st.`branch_id` = ".$search['branch'];
    	}
	    if($search['user'] > 0){
	    	$where.= " AND scc.`user_id` = ".$search['user'];
	    }
	    return $db->fetchAll($sql.$where.$order);
    }
    
    
    function getAllServiceByCategory($cate_id){
    	$db = $this->getAdapter();
    	$sql="select service_id as id,title as name from rms_program_name where ser_cate_id = $cate_id and type=2 and status=1 ";
    	return $db->fetchAll($sql);
    }
    
    
    
    
}