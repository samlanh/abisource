<?php

class Allreport_Model_DbTable_DbRptFee extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    function getAllTuitionFee($search){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT 
    				id,
    				(SELECT branch_namekh FROM rms_branch WHERE br_id = branch_id LIMIT 1) as branch_name,
    				CONCAT(from_academic,' - ',to_academic) AS academic,
    				note,
    		    	generation,
    		    	(SELECT name_en FROM `rms_view` WHERE `rms_view`.`type`=7 AND `rms_view`.`key_code`=`rms_tuitionfee`.`time` LIMIT 1)AS time,
    				create_date ,
    				status 
    			FROM 
    				`rms_tuitionfee` 
    			WHERE 
    				status=1 
    				$branch_id
    		";
    	$WHERE= ' ';
    	$order= " ORDER BY id DESC ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	if(!empty($search['study_year'])){
    		$WHERE.=" AND id = ".$search['study_year'] ;
    	}
    	if(!empty($search['branch'])){
    		$WHERE.=" AND branch_id = ".$search['branch'] ;
    	}
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$abc = $s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " CONCAT(from_academic,'-',to_academic) LIKE '%{$s_search}%'";
    		$s_where[] = " rms_tuitionfee.generation LIKE '%{$s_search}%'";
    		$s_where[] = " rms_tuitionfee.from_academic LIKE '%{$s_search}%'";
    		$s_where[] = " rms_tuitionfee.to_academic LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT name_en FROM rms_view WHERE rms_view.type=7 AND rms_view.key_code=rms_tuitionfee.time LIMIT 1) LIKE '%{$s_search}%'";
    		$WHERE .=' AND ( '.implode(' OR ',$s_where).')';
    	}

    	return $db->fetchAll($sql.$WHERE.$order);
    }
    function getFeebyOther($fee_id,$grade_search,$degree_id){
    	//print_r($fee_id);exit();
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				tf.*,
			    	m.major_enname as class,
			    	(SELECT name_en FROM rms_view WHERE type=4 AND key_code=tf.session LIMIT 1) as session,
			    	(SELECT en_name FROM `rms_dept` WHERE dept_id=m.dept_id LIMIT 1) as degree
			    FROM 
			    	rms_tuitionfee_detail as tf,
			    	rms_major as m 
			    WHERE  
    				m.major_id=tf.class_id 
    				AND tf.fee_id = $fee_id ";
    	
    	$WHERE = ' ';
    	$order = ' ORDER BY tf.id ASC';
    	 
    	if($degree_id>0){
    		$WHERE.=" AND m.dept_id = ".$degree_id;
    	}
    	if($grade_search>0){
    		$WHERE.=" AND tf.class_id = ".$grade_search;
    	}
//     	echo $sql.$WHERE;
    	return $db->fetchAll($sql.$WHERE);
    }
    
    function getAllYearFee(){
    	$db=$this->getAdapter();
    	$sql=" SELECT id, CONCAT(from_academic,'-',to_academic,'(',generation,')') as year,(SELECT name_en FROM rms_view WHERE type=7 AND key_code=time LIMIT 1) as time FROM rms_tuitionfee ";
    	return $db->fetchAll($sql);
    }
    
    
}
   
    
   