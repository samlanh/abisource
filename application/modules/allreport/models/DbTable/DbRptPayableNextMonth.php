<?php

class Allreport_Model_DbTable_DbRptPayableNextMonth extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';

    function getAllPayableNextMonth($search,$payfor_type){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.branch_id");
    	
    	$sql="SELECT 
    			  sp.id,
    			  
    			  (SELECT branch_namekh FROM rms_branch WHERE br_id = sp.branch_id LIMIT 1) as branch_name,
			      (SELECT last_name FROM rms_users as u WHERE u.id = sp.user_id LIMIT 1) as user_name,
    			  
				  sp.`receipt_number` AS receipt,
				  s.stu_code AS code,
				  (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id = s.stu_id AND ser.type=4 LIMIT 1) as transport_code,
				  (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id = s.stu_id AND ser.type=5 LIMIT 1) as lunch_code,
				  CONCAT(stu_khname,' - ',stu_enname) AS name,
				  stu_khname,
				  stu_enname,
				  s.tel,
				  sp.discount_percent,
				  sp.discount_fix,
				  sp.grand_total_payment,
				  sp.note,
				  (SELECT name_en FROM rms_view WHERE rms_view.type=2 AND key_code=s.sex LIMIT 1) AS sex,
				  (SELECT major_enname FROM rms_major WHERE major_id = s.grade LIMIT 1) as grade,
				  pn.`title` service,
				  spd.`start_date` as start,
				  spd.`validate` as end
				FROM
				  `rms_student_paymentdetail` AS spd,
				  `rms_student_payment` AS sp,
				  `rms_program_name` AS pn,
				   rms_student as s
				WHERE 
				  spd.`is_start` = 1 
				  AND s.stu_id = sp.student_id
				  AND sp.id=spd.`payment_id`
				  AND spd.`service_id`=pn.`service_id`
				  AND sp.payfor_type=$payfor_type
				  AND sp.is_void=0
				  AND s.is_subspend=0
				  $branch_id
    		";
    	
     	$order=" ORDER by spd.validate ASC , s.stu_code ASC ";
     	
     	$WHERE = " ";
     	
     	if(!empty($search['for_month'])){
     		$first_day = 1;
     		$last_day = 31;
     		$year = $search['for_year'];
     		$for_month = $search['for_month'];
     		 
     		$start = $year.'-'.$for_month.'-'.$first_day;
     		$end = $year.'-'.$for_month.'-'.$last_day;
     		 
     		//$FROM_date = (empty($start))? '1': "spd.validate >= '".$start." 00:00:00'";
     		$to_date = (empty($end))? '1': "spd.validate <= '".$end." 23:59:59'";
     		 
     		$WHERE .= " AND ".$to_date;
     	}
     	
     	if(!empty($search['degree_en_ft'])){
     		$WHERE .= " AND s.degree = ".$search['degree_en_ft'];
     	}
     	if(!empty($search['grade_en_ft'])){
     		$WHERE .= " AND s.grade = ".$search['grade_en_ft'];
     	}
     	if(!empty($search['degree_kh_ft'])){
     		$WHERE .= " AND s.degree = ".$search['degree_kh_ft'];
     	}
     	if(!empty($search['grade_kh_ft'])){
     		$WHERE .= " AND s.grade = ".$search['grade_kh_ft'];
     	}
     	if(!empty($search['degree_gep'])){
     		$WHERE .= " AND s.degree = ".$search['degree_gep'];
     	}
     	if(!empty($search['grade_gep'])){
     		$WHERE .= " AND s.grade = ".$search['grade_gep'];
     	}
     	if(!empty($search['transport_service'])){
     		$WHERE .= " AND spd.service_id = ".$search['transport_service'];
     	}
     	if(!empty($search['lunch_service'])){
     		$WHERE .= " AND spd.service_id = ".$search['lunch_service'];
     	}
     	if(!empty($search['branch'])){
     		$WHERE .= " AND sp.branch_id = ".$search['branch'];
     	}
    	if(!empty($search['txtsearch'])){
    		$s_WHERE = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_WHERE[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$s_WHERE[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_WHERE[] = " (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id = s.stu_id AND ser.type=4 LIMIT 1) LIKE '%{$s_search}%'";
    		$s_WHERE[] = " (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id = s.stu_id AND ser.type=5 LIMIT 1) LIKE '%{$s_search}%'";
    		$s_WHERE[] = " stu_enname LIKE '%{$s_search}%'";
    		$s_WHERE[] = " stu_khname LIKE '%{$s_search}%'";
    		$s_WHERE[] = " (SELECT title FROM rms_program_name WHERE rms_program_name.service_id=spd.service_id) LIKE '%{$s_search}%'";
    		$s_WHERE[] = " spd.comment LIKE '%{$s_search}%'";
    		$WHERE .=' AND ( '.implode(' OR ',$s_WHERE).')';
    	}
    		
    		//echo $sql.$WHERE;
    	return $db->fetchAll($sql.$WHERE.$order);
    }
    
    
	function getAllPayableNextMonthService($search,$payfor_type,$service_type){
    	$db=$this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.branch_id");
    	
    	$sql="SELECT
			    	sp.id,
			    		
			    	(SELECT branch_namekh FROM rms_branch WHERE br_id = sp.branch_id LIMIT 1) as branch_name,
			    	(SELECT last_name FROM rms_users as u WHERE u.id = sp.user_id LIMIT 1) as user_name,
			    		
			    	sp.`receipt_number` AS receipt,
			    	s.stu_code AS code,
			    	(SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id = s.stu_id AND ser.type=4 LIMIT 1) as transport_code,
			    	(SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id = s.stu_id AND ser.type=5 LIMIT 1) as lunch_code,
			    	CONCAT(stu_khname,' - ',stu_enname) AS name,
					stu_khname,
					stu_enname,
					s.tel,
				    sp.discount_percent,
				    sp.discount_fix,
				    sp.grand_total_payment,
				    sp.note,
			    	(SELECT name_en FROM rms_view WHERE rms_view.type=2 AND key_code=s.sex LIMIT 1) AS sex,
			    	(SELECT major_enname FROM rms_major WHERE major_id = s.grade LIMIT 1) as grade,
			    	pn.`title` service,
			    	spd.`start_date` as start,
			    	spd.`validate` as end,
			    	ser.time_for_car
			    FROM
			    	`rms_student_paymentdetail` AS spd,
			    	`rms_student_payment` AS sp,
			    	`rms_program_name` AS pn,
			    	rms_student as s,
			    	rms_service as ser
			    WHERE spd.`is_start` = 1
			    	AND s.stu_id = sp.student_id
			    	AND ser.stu_id = sp.student_id
			    	AND sp.id=spd.`payment_id`
			    	AND spd.`service_id`=pn.`service_id`
			    	AND sp.payfor_type=$payfor_type
			    	AND sp.is_void=0
			    	AND ser.is_suspend=0
					AND ser.type=$service_type
			    	$branch_id
	    	";
    	 
	    	$order=" ORDER by spd.validate ASC , ser.stu_code ASC ";
	    
	    	$WHERE = " ";
	    
	    	if(!empty($search['for_month'])){
	    	$first_day = 1;
	    	$last_day = 31;
	    	$year = $search['for_year'];
	    	$for_month = $search['for_month'];
	    
	    	$start = $year.'-'.$for_month.'-'.$first_day;
	    	$end = $year.'-'.$for_month.'-'.$last_day;
	    
	    	//$FROM_date = (empty($start))? '1': "spd.validate >= '".$start." 00:00:00'";
	    	$to_date = (empty($end))? '1': "spd.validate <= '".$end." 23:59:59'";
	    
	    	$WHERE .= " AND ".$to_date;
    	}
    
    	if(!empty($search['degree_en_ft'])){
	    	$WHERE .= " AND s.degree = ".$search['degree_en_ft'];
	    }
	    if(!empty($search['grade_en_ft'])){
	    	$WHERE .= " AND s.grade = ".$search['grade_en_ft'];
	    }
	    if(!empty($search['degree_kh_ft'])){
	    	$WHERE .= " AND s.degree = ".$search['degree_kh_ft'];
	    }
	    if(!empty($search['grade_kh_ft'])){
	    	$WHERE .= " AND s.grade = ".$search['grade_kh_ft'];
	    }
	    if(!empty($search['degree_gep'])){
	    	$WHERE .= " AND s.degree = ".$search['degree_gep'];
	    }
	    if(!empty($search['grade_gep'])){
	    	$WHERE .= " AND s.grade = ".$search['grade_gep'];
	    }
	    if(!empty($search['transport_service'])){
	    	$WHERE .= " AND spd.service_id = ".$search['transport_service'];
	    }
	    if(!empty($search['lunch_service'])){
	    	$WHERE .= " AND spd.service_id = ".$search['lunch_service'];
	    }
	    if(!empty($search['car_id'])){
	    	$WHERE .= " AND ser.car_id = ".$search['car_id'];
	    }
	    if(!empty($search['branch'])){
	    	$WHERE .= " AND sp.branch_id = ".$search['branch'];
	    }
	    
	    
	    if(!empty($search['txtsearch'])){
	    $s_WHERE = array();
	    $s_search = addslashes(trim($search['txtsearch']));
    		$s_WHERE[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$s_WHERE[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_WHERE[] = " (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id = s.stu_id AND ser.type=4 LIMIT 1) LIKE '%{$s_search}%'";
    		$s_WHERE[] = " (SELECT ser.stu_code FROM rms_service as ser WHERE ser.stu_id = s.stu_id AND ser.type=5 LIMIT 1) LIKE '%{$s_search}%'";
    		$s_WHERE[] = " stu_enname LIKE '%{$s_search}%'";
    		$s_WHERE[] = " stu_khname LIKE '%{$s_search}%'";
    		$s_WHERE[] = " (SELECT title FROM rms_program_name WHERE rms_program_name.service_id=spd.service_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_WHERE[] = " spd.comment LIKE '%{$s_search}%'";
    		$WHERE .=' AND ( '.implode(' OR ',$s_WHERE).')';
	    }
	    
	    //echo $sql.$WHERE.$order;
	    return $db->fetchAll($sql.$WHERE.$order);
    }
    
    
    
    
    
    
    
    
}
   
    
   