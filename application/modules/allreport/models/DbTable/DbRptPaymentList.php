<?php

class Allreport_Model_DbTable_DbRptPaymentList extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_service';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    function getAllAmountStudentByType($search,$type){
    	$db = $this->getAdapter();
    	 
    	//print_r($search);
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	
    	$sql="SELECT 
    				sp.id,COUNT(sp.student_id) 
    			FROM 
    				`rms_student_payment` AS sp,
    				rms_student AS s 
    			WHERE 
    				s.`stu_id`=sp.`student_id` 
    				AND sp.payfor_type=$type 
    				$branch_id
    		";
    	
    	$where = " ";
    	
//     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	 
//     	$where = " AND ".$from_date." AND ".$to_date;
    	
//     	if(!empty($search['for_month'])){
//     		$first_day = 1;
//     		$last_day = 31;
//     		$year=$search['for_year'];
//     		$for_month = $search['for_month'];
    	
//     		$end = $year.'-'.$for_month.'-'.$last_day;
    		
//     		$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
    		
//     		$where = " AND ".$to_date;
//     	}
    	
    	$today = date("Y-m-d");
    	$to_date = (empty($today))? '1': "sp.create_date <= '".$today." 23:59:59'";
    	$where .= " AND ".$to_date;
    	
    	$group_by = " GROUP BY student_id ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$group_by);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	 
    	if(!empty($search['degree'])){
    		$where.= " and (select spd.is_start from rms_student_paymentdetail as spd where spd.payment_id=sp.id limit 1) = 1 AND s.`degree` = ".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.= " and (select spd.is_start from rms_student_paymentdetail as spd where spd.payment_id=sp.id limit 1) = 1 AND s.`grade` = ".$search['grade'];
    	}
    	if(!empty($search['room'])){
    		$where.= " and (select spd.is_start from rms_student_paymentdetail as spd where spd.payment_id=sp.id limit 1) = 1 AND s.`room` = ".$search['room'];
    	}
    	if(!empty($search['branch'])){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	
    	if(!empty($search['service'])){
    		$where.= " AND (select spd.service_id from rms_student_paymentdetail as spd where spd.payment_id=sp.id order by spd.id ASC limit 1) = ".$search['service'];
    	}
    	
    	$total_student = $db->fetchAll($sql.$where.$group_by);
    	$drop_student = $this->getAllAmountStudentDropByType($search,$type,null);
    	
    	$student_study = count($total_student) - count($drop_student);
    	
    	
    	$drop_student_this_month = $this->getAllAmountStudentDropByType($search,$type,1);
    	$new_student_this_month = $this->getAllAmountNewStudentByType($search, $type, 1);
    	
    	
    	$string = '';
    	if(!empty($total_student)){
    		$string .= '<table width="100%">';
    			$string .= '<tr>';
    				$string .= '<td width="20%" valign="top">';
			    		$string .= '<table border="1" width="100%" style="border-collapse: collapse;margin-top: 25px;text-align: center;border: 1px solid #000;">';
			    			$string .= '<tr>';
			    				$string .= '<td width="70%">Total Student All</td>';
			    				$string .= '<td width="30%">'.count($total_student).'</td>';
			    			$string .= '</tr>';
			    			
			    			$string .= '<tr>';
			    				$string .= '<td>Stop Student All</td>';
			    				$string .= '<td>'.count($drop_student).'</td>';
			    			$string .= '</tr>';
			    			
			    			$string .= '<tr>';
			    				$string .= '<td style="background: #c1d0f3;"><strong>Net Total Student</strong></td>';
			    				$string .= '<td style="background: #eaeaea;"><strong>'.$student_study.'</strong></td>';
			    			$string .= '</tr>';
			    		$string .= '</table>';
			    	$string .= '</td>';
			    		
			    	$string .= '<td width="5%">&nbsp;</td>';
			    	
			    	if($type==1){
			    		$label_new = "ចំនួនសិស្សថ្មីក្នុងខែ ".date("m")." សរុប";
			    		$label_stop = "ចំនួនសិស្សឈប់ក្នុងខែ ".date("m")." សរុប";
			    	}else{
			    		$label_new = "Total New Student of ".date("m")."-".date("Y");
			    		$label_stop = "Total Stopped Student of ".date("m")."-".date("Y");
			    	}
			    	
			    	
			    	$string .= '<td width="30%" valign="top">';
			    		$string .= '<table border="1" width="100%" style="border-collapse: collapse;margin-top: 25px;text-align: center;border: 1px solid #000;">';
			    			$string .= '<tr>';
					    		$string .= '<td width="70%">'.$label_new.'</td>';
					    		$string .= '<td width="30%">'.count($new_student_this_month).'</td>';
			    			$string .= '</tr>';
			    			$string .= '<tr>';
				    			$string .= '<td width="70%">'.$label_stop.'</td>';
				    			$string .= '<td width="30%">'.count($drop_student_this_month).'</td>';
			    			$string .= '</tr>';
			    		 
			    		$string .= '</table>';
			    	$string .= '</td>';
			    	
			    	$string .= '<td width="45%">&nbsp;</td>';
			    	
			    $string .= '</tr>';
			$string .= '</table>';
			    		
    		
    	}
    	return $string;
    }
    
    function getAllAmountStudentDropByType($search,$type,$this_month){
//     	print_r($search);
    	
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	 
    	$sql="SELECT 
    				COUNT(s.stu_id) 
    			FROM 
    				`rms_student_payment` AS sp,
    				`rms_student` AS s 
    			WHERE 
    				s.`stu_id`=sp.`student_id` 
    				AND sp.`payfor_type`=$type 
    				AND s.`is_subspend`!=0
			    	$branch_id
    		";
    	 
    	$where = " ";
    	
    	 
//     	if(!empty($search['for_month'])){
//     		$first_day = 1;
//     		$last_day = 31;
//     		$year=$search['for_year'];
//     		$for_month = $search['for_month'];
    		 
//     		$end = $year.'-'.$for_month.'-'.$last_day;
    	
//     		$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
    		
//     		$suspend_date = "(select sd.date from rms_student_drop as sd where sd.stu_id=sp.student_id order by id DESC limit 1) <= '".$end." 23:59:59'";
    		
//     		$where .= " AND ".$to_date." and ".$suspend_date ;
//     	}
    	
    	$today = date("Y-m-d");
    	$to_date = (empty($today))? '1': "sp.create_date <= '".$today." 23:59:59'";
    	$where .= " AND ".$to_date;
    	
    	$group_by = " GROUP BY student_id ";
    	 
//     	if(empty($search)){
//     		return $db->fetchAll($sql.$group_by);
// 	    }
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
	    
	    if(!empty($search['degree'])){
	    	$where.= " and (select spd.is_start from rms_student_paymentdetail as spd where spd.payment_id=sp.id limit 1) = 1 AND s.`degree` = ".$search['degree'];
	    }
	    if(!empty($search['grade'])){
	    	$where.= " and (select spd.is_start from rms_student_paymentdetail as spd where spd.payment_id=sp.id limit 1) = 1 AND s.`grade` = ".$search['grade'];
	    }
    	if(!empty($search['room'])){
    		$where.= " and (select spd.is_start from rms_student_paymentdetail as spd where spd.payment_id=sp.id limit 1) = 1 AND s.`room` = ".$search['room'];
	    }
	    if(!empty($search['branch'])){
	    	$where.= " AND sp.`branch_id` = ".$search['branch'];
	    }
	    if(!empty($search['service'])){
	    	$where.= " AND (select spd.service_id from rms_student_paymentdetail as spd where spd.payment_id=sp.id order by spd.id ASC limit 1) = ".$search['service'];
	    }
	    
	    if(!empty($this_month)){
	    	$first_day = 1;
    		$last_day = date("t");
	    	$year=date("Y");
	    	$for_month = date("m");
    		
    		$start = $year.'-'.$for_month.'-'.$first_day;
    		$end = $year.'-'.$for_month.'-'.$last_day;
	    	
	    	$from_date =(empty($start))? '1': "(select sd.date from rms_student_drop as sd where sd.stu_id=sp.student_id and sd.status=1 and sd.drop_from =1 order by id DESC limit 1) >= '".$start." 00:00:00'";
	    	$to_date = (empty($end))? '1': "(select sd.date from rms_student_drop as sd where sd.stu_id=sp.student_id and sd.status=1 and sd.drop_from =1 order by id DESC limit 1) <= '".$end." 23:59:59'";
	    
	    	$where .= " AND ".$from_date." AND ".$to_date;
	    	//echo $sql.$where;
	    }
	    
	    
	    //echo $sql.$where.$group_by;
	    
	    return $db->fetchAll($sql.$where.$group_by);
    }
    
    
///////////////////////////////////////////////////// service type /////////////////////////////////////////////////////////////////////////////////////////////////    
    
    function getAllAmountServiceStudentByType($search,$payfor_type,$service_type){
    	$db = $this->getAdapter();
    
    	//print_r($search);
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	 
    	$sql="SELECT
			    	sp.id,COUNT(sp.student_id)
			    FROM
			    	`rms_student_payment` AS sp,
			    	rms_service AS s,
			    	rms_student as st
			    WHERE
			    	s.`stu_id`=sp.`student_id`
			    	and st.stu_id = sp.student_id
			    	AND sp.payfor_type=$payfor_type
			    	and s.type=$service_type
    				$branch_id
    		";
    	 
    	$where = " ";
    	 
    	//     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	//     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    
    	//     	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	//     	if(!empty($search['for_month'])){
    	//     		$first_day = 1;
    	//     		$last_day = 31;
    	//     		$year=$search['for_year'];
    	//     		$for_month = $search['for_month'];
    			 
    	//     		$end = $year.'-'.$for_month.'-'.$last_day;
    
    	//     		$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
    
    			//     		$where = " AND ".$to_date;
    	//     	}
    	 
    	$today = date("Y-m-d");
    	$to_date = (empty($today))? '1': "sp.create_date <= '".$today." 23:59:59'";
    	$where .= " AND ".$to_date;
    			 
    	$group_by = " GROUP BY student_id ";
    			 
    	if(empty($search)){
    		return $db->fetchAll($sql.$group_by);
    	}
	    if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
	    
    	if(!empty($search['degree'])){
    		$where.= " AND sp.`degree` = ".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.= " AND sp.`grade` = ".$search['grade'];
	    }
    	if(!empty($search['room'])){
    		$where.= " AND sp.`room` = ".$search['room'];
	    }
	    if(!empty($search['branch'])){
	    	$where.= " AND sp.`branch_id` = ".$search['branch'];
	    }
	     
	    if($search['service']>0){
	    	$where.= " AND s.service_id = ".$search['service'];
	    }
	     
	    //echo $sql.$where.$group_by;
	    
	    $total_student = $db->fetchAll($sql.$where.$group_by);
	    $drop_student = $this->getAllAmountStudentDropServiceByType($search,$payfor_type,null,$service_type);
	     
	    $student_study = count($total_student) - count($drop_student);
	     
	     
	    $drop_student_this_month = $this->getAllAmountStudentDropServiceByType($search,$payfor_type,1,$service_type);
	    $new_student_this_month = $this->getAllAmountNewServiceStudentByType($search, $payfor_type,1,$service_type);
	     
	     
	    $string = '';
	    if(!empty($total_student)){
	    	$string .= '<table width="100%">';
	    		$string .= '<tr>';
	    			$string .= '<td width="20%" valign="top">';
	    				$string .= '<table border="1" width="100%" style="border-collapse: collapse;margin-top: 25px;text-align: center;border: 1px solid #000;">';
					    	$string .= '<tr>';
						    	$string .= '<td width="70%">Total Student All</td>';
						    	$string .= '<td width="30%">'.count($total_student).'</td>';
					    	$string .= '</tr>';
	    
					    	$string .= '<tr>';
						    	$string .= '<td>Stop Student All</td>';
						    	$string .= '<td>'.count($drop_student).'</td>';
					    	$string .= '</tr>';
					    
					    	$string .= '<tr>';
						    	$string .= '<td style="background: #c1d0f3;"><strong>Net Total Student</strong></td>';
						    	$string .= '<td style="background: #eaeaea;"><strong>'.$student_study.'</strong></td>';
					    	$string .= '</tr>';
	    				$string .= '</table>';
	    			$string .= '</td>';
	    	 
	    			$string .= '<td width="5%">&nbsp;</td>';
	    
			    	if($payfor_type==3){
			    		$label_new = "ចំនួនសិស្សថ្មីក្នុងខែ ".date("m")." សរុប";
			    		$label_stop = "ចំនួនសិស្សឈប់ក្នុងខែ ".date("m")." សរុប";
			    	}else{
			    		$label_new = "Total New Student of ".date("m")."-".date("Y");
			    		$label_stop = "Total Stopped Student of ".date("m")."-".date("Y");
			    	}
	    
	    
			    	$string .= '<td width="30%" valign="top">';
				    	$string .= '<table border="1" width="100%" style="border-collapse: collapse;margin-top: 25px;text-align: center;border: 1px solid #000;">';
					    	$string .= '<tr>';
						    	$string .= '<td width="70%">'.$label_new.'</td>';
						    	$string .= '<td width="30%">'.count($new_student_this_month).'</td>';
					    	$string .= '</tr>';
					    	$string .= '<tr>';
						    	$string .= '<td width="70%">'.$label_stop.'</td>';
						    	$string .= '<td width="30%">'.count($drop_student_this_month).'</td>';
					    	$string .= '</tr>';
			    
			    		$string .= '</table>';
			    	$string .= '</td>';
	    
	    			$string .= '<td width="45%">&nbsp;</td>';
	    
	    		$string .= '</tr>';
	    	$string .= '</table>';
	    	 
	    
	    }
	    //echo $string;exit();
	    return $string;
	    
	    
	    
    }
    
    function getAllAmountStudentDropServiceByType($search,$type,$this_month,$service_type){
    	//     	print_r($search);
    	 
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql="SELECT
			    	COUNT(s.stu_id)
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_service` AS s,
			    	rms_student as st
			    WHERE
			    	s.`stu_id`=sp.`student_id`
			    	and st.stu_id = sp.student_id
			    	AND sp.`payfor_type`=$type
			    	AND s.`is_suspend`!=0
			    	and s.type = $service_type
			    	$branch_id
		    ";
    
    	$where = " ";
    	 
    
//     	if(!empty($search['for_month'])){
// 	    	$first_day = 1;
// 	    	$last_day = 31;
// 	    	$year=$search['for_year'];
// 	    	$for_month = $search['for_month'];
	    	 
// 	    	$end = $year.'-'.$for_month.'-'.$last_day;
	    	 
// 	    	$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
	    
// 	    	$suspend_date = "(select sd.date from rms_student_drop as sd where sd.stu_id=sp.student_id order by id DESC limit 1) <= '".$end." 23:59:59'";
	    
// 	    	$where .= " AND ".$to_date." and ".$suspend_date ;
//     	}

    	$today = date("Y-m-d");
    	$to_date = (empty($today))? '1': "sp.create_date <= '".$today." 23:59:59'";
    	$where .= " AND ".$to_date;
    	 
    	$group_by = " GROUP BY student_id ";
    
    	//     	if(empty($search)){
    	//     		return $db->fetchAll($sql.$group_by);
    	// 	    }
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	 
    	if(!empty($search['degree'])){
    		$where.= " AND sp.`degree` = ".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.= " AND sp.`grade` = ".$search['grade'];
    	}
    	if(!empty($search['room'])){
    		$where.= " AND sp.`room_id` = ".$search['room'];
    	}
    	if(!empty($search['branch'])){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if($search['service']>0){
    		$where.= " AND s.service_id = ".$search['service'];
    	}
    	 
    	if(!empty($this_month)){
	    	$first_day = 1;
	    	$last_day = date("t");
	    	$year=date("Y");
	    	$for_month=date("m");
    		
	    	$start = $year.'-'.$for_month.'-'.$first_day;
    		$end = $year.'-'.$for_month.'-'.$last_day;
			
			if($type==3){//transport
    			$drop_type=" AND sd.drop_from=2";
    		}else{
    			$drop_type=" AND sd.drop_from=3";
    		}
	    
	    	$from_date =(empty($start))? '1': "(select sd.date from rms_student_drop as sd where sd.stu_id=sp.student_id and sd.status=1 $drop_type order by id DESC limit 1) >= '".$start." 00:00:00'";
	    	$to_date = (empty($end))? '1': "(select sd.date from rms_student_drop as sd where sd.stu_id=sp.student_id and sd.status=1 $drop_type order by id DESC limit 1) <= '".$end." 23:59:59'";
	    	 
	    	$where .= " AND ".$from_date." AND ".$to_date;
	    	//echo $sql.$where;
    	}
    	//echo $sql.$where.$group_by;
    	 
    	return $db->fetchAll($sql.$where.$group_by);
    }
    
    function getAllAmountNewServiceStudentByType($search,$payfor_type,$this_month,$service_type){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql="SELECT
			    	COUNT(sp.student_id)
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_service` AS s,
			    	rms_student as st
			    WHERE
			    	s.`stu_id`=sp.`student_id`
			    	and st.stu_id = sp.student_id
			    	AND sp.`payfor_type` = $payfor_type
			    	AND sp.`is_new` = 1
			    	and s.type=$service_type
			    	$branch_id
    		";
    
    	$where = " ";
    	 
    	//     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	//     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    
    	//     	$where = " AND ".$from_date." AND ".$to_date;
    
    	$group_by = " GROUP BY student_id ";
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$group_by);
    	}
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    
    	if(!empty($search['degree'])){
    		$where.= " AND sp.`degree` = ".$search['degree'];
	    }
	    if(!empty($search['grade'])){
	    	$where.= " AND sp.`grade` = ".$search['grade'];
	    }
	    if(!empty($search['room'])){
	    	$where.= " AND sp.`room_id` = ".$search['room'];
	    }
	    if(!empty($search['branch'])){
	    	$where.= " AND sp.`branch_id` = ".$search['branch'];
	    }
	    if($search['service']>0){
	    	$where.= " AND s.service_id = ".$search['service'];
	    }
     
	    if(!empty($this_month)){
		    $first_day = 1;
		    $last_day = date("t");
		    $year=date("Y");
		    $for_month = date("m");
		    
		    $start = $year.'-'.$for_month.'-'.$first_day;
		    $end = $year.'-'.$for_month.'-'.$last_day;
		    
		     
		    $from_date =(empty($start))? '1': " sp.create_date >= '".$start." 00:00:00'";
		    $to_date = (empty($end))? '1': " sp.create_date <= '".$end." 23:59:59'";
		     
		    $where .= " AND ".$from_date." AND ".$to_date;
		     
		    //echo $sql.$where;
	    }
    
     
    	return $db->fetchAll($sql.$where.$group_by);
    }
    
    
    function getAllAmountStudentByService($search,$type,$detail_type,$service_type){
    
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql="SELECT
			    	sp.id,
			    	s.service_id,
			    	(select pn.title from rms_program_name as pn where pn.service_id = s.service_id limit 1) as service_name
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_service` AS s,
			    	rms_student as st,
			    	rms_student_paymentdetail as spd
			    WHERE
			    	s.`stu_id`=sp.`student_id`
			    	and st.stu_id = sp.student_id
			    	and sp.id = spd.payment_id
			    	AND sp.`payfor_type` = $type
			    	and spd.type = $detail_type
			    	and s.is_suspend=0
			    	and s.type = $service_type
			    	and spd.is_start=1
			    	$branch_id
    		";
    
    	$where = " ";
    
    	//     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	//     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    
    	//     	$where = " AND ".$from_date." AND ".$to_date;
    
    	//     	if(!empty($search['for_month'])){
    	// 	    	$first_day = 1;
    	// 	    	$last_day = 31;
    	// 	    	$year=date("Y");
    			// 	    	$for_month = $search['for_month'];
    	 
    	// 	    	$end = $year.'-'.$for_month.'-'.$last_day;
    	 
    	// 	    	$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
    	   
    			// 	    	$where = " AND ".$to_date;
    			//     	}
    
    	$today = date("Y-m-d");
    	$to_date = (empty($today))? '1': "sp.create_date <= '".$today." 23:59:59'";
    	$where .= " AND ".$to_date;
    
    	$order=" ORDER BY s.`service_id` ASC";
    
    	$group_by = " GROUP BY sp.student_id ";
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$where.$group_by.$order);
    	}
    
    	if(!empty($search['txtsearch'])){
    	$s_where = array();
    	$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .= ' AND ( '.implode(' OR ',$s_where).')';
    	}
    	 
    	if($search['service'] > 0){
    		$where.= " AND s.`service_id` = ".$search['service'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
//     	echo $sql.$where.$group_by.$order;
    
    	$data = $db->fetchAll($sql.$where.$group_by.$order);
    	
    	$string="";
    	$old_service=0;
		$amount_student = 0;
		$service_name = "";
		$total_student = 0;
		
		$date = date("m")."-".date("Y");
		
		if(!empty($data)){
    	
    	///////////////////////////////////////////////////////////////////	សេវាកម្ម (Service) //////////////////////////////////////////////////////
    	
	    	$string .= '<table border="0" width="40%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;margin-top: 35px;">';
	    		$string .= '<tr>';
	    			$string .= '<td width="30%" valign="top">';
	    				$string .= '<table border="1" width="100%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;">';
	    					$string .= '<tr style="background: #c1d0f3;">';
					    		$string .= '<td colspan="2">Summary report for  '.$date.'</td>';
					    	$string .= '</tr>';
	    	
					    	$string .= '<tr style="background: #c1d0f3;">';
						    	$string .= '<td width="70%">ឈ្មោះសេវាកម្ម</td>';
						    	$string .= '<td width="30%">ចំនួនសិស្ស</td>';
					    	$string .= '</tr>';
	    	
					    	foreach ($data as $key=>$rs){
					    		if($key>0 && $old_service!=$rs['service_id']){
					    			$string .= '<tr>';
					    			$string .= '<td>'.$service_name.'</td>';
					    			$string .= '<td>'.$amount_student.'</td>';
					    			$string .= '</tr>';
					    	
					    			$amount_student = 1;
					    		}else{
					    			$amount_student = $amount_student + 1;
					    		}
					    		$old_service=$rs['service_id'];
					    		$service_name = $rs['service_name'];
					    		 
					    		$total_student = $total_student+1;
					    	}
					    	$string .= '<tr>';
						    	$string .= '<td>'.$service_name.'</td>';
						    	$string .= '<td>'.$amount_student.'</td>';
					    	$string .= '</tr>';
					    	
					    	$string .= '<tr>';
						    	$string .= '<td>ចំនួនសិស្សសរុប</td>';
						    	$string .= '<td style="background: #eaeaea;">'.$total_student.'</td>';
					    	$string .= '</tr>';
	    				$string .= '</table>';
	    			$string .= '</td>';
	    		$string .= '</tr>';
	    	$string .= '</table>';
		}
		//echo $string;exit();
		return $string;
    	
    }
    
    
   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    		
    function getAllAmountNewStudentByType($search,$type,$this_month){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql="SELECT
			    	COUNT(sp.student_id)
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_student` AS s
			    WHERE
			    	s.`stu_id`=sp.`student_id`
			    	AND sp.`payfor_type` = $type
			    	AND sp.`is_new` = 1
			    	$branch_id
    		";
    
    	$where = " ";
    	
//     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    
//     	$where = " AND ".$from_date." AND ".$to_date;
    
    	$group_by = " GROUP BY student_id ";
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$group_by);
	    }
	    if(!empty($search['txtsearch'])){
		    $s_where = array();
		    $s_search = addslashes(trim($search['txtsearch']));
		    $s_where[] = " s.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
    	 
    	if(!empty($search['degree'])){
	    	$where.= " and (select spd.is_start from rms_student_paymentdetail as spd where spd.payment_id=sp.id limit 1) = 1 AND s.`degree` = ".$search['degree'];
	    }
	    if(!empty($search['grade'])){
	    	$where.= " and (select spd.is_start from rms_student_paymentdetail as spd where spd.payment_id=sp.id limit 1) = 1 AND s.`grade` = ".$search['grade'];
	    }
    	if(!empty($search['room'])){
    		$where.= " and (select spd.is_start from rms_student_paymentdetail as spd where spd.payment_id=sp.id limit 1) = 1 AND s.`room` = ".$search['room'];
	    }
	    if(!empty($search['branch'])){
	    	$where.= " AND sp.`branch_id` = ".$search['branch'];
	    }
	    if(!empty($search['service'])){
	    	$where.= " AND (select spd.service_id from rms_student_paymentdetail as spd where spd.payment_id=sp.id order by spd.id ASC limit 1) = ".$search['service'];
	    }
    	
    	if(!empty($this_month)){
    		$first_day = 1;
    		$last_day = date("t");
	    	$year=date("Y");
	    	$for_month = date("m");
    		
    		$start = $year.'-'.$for_month.'-'.$first_day;
    		$end = $year.'-'.$for_month.'-'.$last_day;
    		
    	
    		$from_date =(empty($start))? '1': " sp.create_date >= '".$start." 00:00:00'";
    		$to_date = (empty($end))? '1': " sp.create_date <= '".$end." 23:59:59'";
    		 
    		$where .= " AND ".$from_date." AND ".$to_date;
    	
    		//echo $sql.$where;
    	}
    
    	
    	return $db->fetchAll($sql.$where.$group_by);
    }
    
    function getAllAmountStudentByGrade($search,$type){
    	
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql="SELECT
			    	sp.id,
			    	s.stu_enname,
			    	s.stu_khname,
			    	s.degree,
			    	s.grade,
			    	s.session,
			    	(select major_enname from rms_major where major_id = s.grade limit 1) as grade_name,
			    	(select en_name from rms_dept where dept_id = s.degree limit 1) as degree_name
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_student` AS s,
			    	rms_student_paymentdetail as spd
			    WHERE
			    	s.`stu_id`=sp.`student_id`
			    	and sp.id = spd.payment_id
			    	AND sp.`payfor_type` = $type
			    	and spd.service_id = 4
			    	and sp.is_subspend=0
			    	and spd.is_start=1
			    	$branch_id
    		";
    
    	$where = " ";
    	 
    	//     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	//     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    
    	//     	$where = " AND ".$from_date." AND ".$to_date;
    
//     	if(!empty($search['for_month'])){
//     		$first_day = 1;
//     		$last_day = 31;
//     		$year=$search['for_year'];
//     		$for_month = $search['for_month'];
    		 
//     		$end = $year.'-'.$for_month.'-'.$last_day;
    	
//     		$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
    	
//     		$where = " AND ".$to_date;
//     	}
    	
    	$today = date("Y-m-d");
    	$to_date = (empty($today))? '1': "sp.create_date <= '".$today." 23:59:59'";
    	$where .= " AND ".$to_date;
    	
    	$order=" ORDER BY s.`degree` ASC , s.`grade` ASC";
    	
    	$group_by = " GROUP BY student_id ";
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$group_by);
    	}
    	
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    
    	if($search['degree'] > 0){
    		$where.= " AND s.`degree` = ".$search['degree'];
    	}
    	if($search['grade'] > 0){
    		$where.= " AND s.`grade` = ".$search['grade'];
    	}
    	if($search['room'] > 0){
    		$where.= " AND s.`room` = ".$search['room'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND s.`branch_id` = ".$search['branch'];
    	}
    
    	//echo $sql.$where.$group_by.$order;exit();
    	
    	$data = $db->fetchAll($sql.$where.$group_by.$order);
    	
    	$string = '';
    	$date = date("m").'-'.date("Y");
    	
    	
    	if(!empty($data)){
    		if($type==1){
	    		$old_grade=0;
	    		$amount_student = 0;
	    		$grade_name = "";
	    		$total_student = 0;
	    		
	   	 ///////////////////////////////////////////////////////////////////	Kindergarten (មត្តេយ្យ) //////////////////////////////////////////////////////	
	    		
	    		$string .= '<table border="0" width="100%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;margin-top: 35px;">';
	    			$string .= '<tr>';
	    				$string .= '<td width="30%" valign="top">';
	    					$string .= '<table border="1" width="100%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;">';
								$string .= '<tr style="background: #c1d0f3;">';
									$string .= '<td colspan="2">របាយការណ៍សង្ខេបប្រចាំខែ '.$date.'</td>';
								$string .= '</tr>';
								
								$string .= '<tr>';
									$string .= '<td colspan="2">មត្តេយ្យសិក្សា</td>';
								$string .= '</tr>';
								
								$string .= '<tr style="background: #c1d0f3;">';
									$string .= '<td width="50%">កម្រិត</td>';
									$string .= '<td width="50%">ចំនួនសិស្ស</td>';
								$string .= '</tr>';
								
								foreach ($data as $key=>$rs){
									if($rs['degree']==1){
										if($key>0 && $old_grade!=$rs['grade'] && $old_grade!=""){
											$string .= '<tr>';
												$string .= '<td>'.$grade_name.'</td>';
												$string .= '<td>'.$amount_student.'</td>';
											$string .= '</tr>';
											
											$amount_student = 1;
										}else{
											$amount_student = $amount_student + 1;
										}
										$old_grade=$rs['grade'];
										$grade_name = $rs['grade_name'];
										
										$total_student = $total_student+1;
									}
								}
								$string .= '<tr>';
									$string .= '<td>'.$grade_name.'</td>';
									$string .= '<td>'.$amount_student.'</td>';
								$string .= '</tr>';
					
								$string .= '<tr>';
									$string .= '<td>ចំនួនសិស្សសរុប</td>';
									$string .= '<td style="background: #eaeaea;">'.$total_student.'</td>';
								$string .= '</tr>';
							$string .= '</table>';
						$string .= '</td>';
											
						$string .= '<td width="5%">&nbsp;</td>';
				
				///////////////////////////////////////////////////////////////////	Primary (បឋមសិក្សា) //////////////////////////////////////////////////////
						
				$old_grade=0;
				$amount_student = 0;
				$grade_name = "";
				$total_student = 0;
				
						$string .= '<td width="30%" valign="top">';
							$string .= '<table border="1" width="100%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;">';
								$string .= '<tr style="background: #c1d0f3;">';
									$string .= '<td colspan="2">របាយការណ៍សង្ខេបប្រចាំខែ '.$date.'</td>';
								$string .= '</tr>';
					
								$string .= '<tr>';
									$string .= '<td colspan="2">បឋមសិក្សា</td>';
								$string .= '</tr>';
					
								$string .= '<tr style="background: #c1d0f3;">';
									$string .= '<td width="50%">កម្រិត</td>';
									$string .= '<td width="50%">ចំនួនសិស្ស</td>';
								$string .= '</tr>';
					
								foreach ($data as $key=>$rs){
									if($rs['degree']==2){
										if($key>0 && $old_grade!=$rs['grade'] && $old_grade!=""){
											$string .= '<tr>';
											$string .= '<td>'.$grade_name.'</td>';
											$string .= '<td>'.$amount_student.'</td>';
											$string .= '</tr>';
								
											$amount_student = 1;
										}else{
											$amount_student = $amount_student + 1;
										}
										$old_grade=$rs['grade'];
										$grade_name = $rs['grade_name'];
											
										$total_student = $total_student+1;
									}
								}
								$string .= '<tr>';
									$string .= '<td>'.$grade_name.'</td>';
									$string .= '<td>'.$amount_student.'</td>';
								$string .= '</tr>';
				
								$string .= '<tr>';
									$string .= '<td>ចំនួនសិស្សសរុប</td>';
									$string .= '<td style="background: #eaeaea;">'.$total_student.'</td>';
								$string .= '</tr>';
							$string .= '</table>';
						$string .= '</td>';
				
						$string .= '<td width="5%">&nbsp;</td>';
						
						
					///////////////////////////////////////////////////////////////////	High School (វិទ្យាល័យ) //////////////////////////////////////////////////////
							
						$old_grade=0;
						$amount_student = 0;
						$grade_name = "";
						$total_student = 0;
						
						$string .= '<td width="30%" valign="top">';
							$string .= '<table border="1" width="100%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;">';
								$string .= '<tr style="background: #c1d0f3;">';
									$string .= '<td colspan="2">របាយការណ៍សង្ខេបប្រចាំខែ '.$date.'</td>';
								$string .= '</tr>';
						
								$string .= '<tr>';
									$string .= '<td colspan="2">វិទ្យាល័យ</td>';
								$string .= '</tr>';
						
								$string .= '<tr style="background: #c1d0f3;">';
									$string .= '<td width="50%">កម្រិត</td>';
									$string .= '<td width="50%">ចំនួនសិស្ស</td>';
								$string .= '</tr>';
						
								foreach ($data as $key=>$rs){
									if($rs['degree']==3){
										if($key>0 && $old_grade!=$rs['grade'] && $old_grade!=""){
											$string .= '<tr>';
											$string .= '<td>'.$grade_name.'</td>';
											$string .= '<td>'.$amount_student.'</td>';
											$string .= '</tr>';
												
											$amount_student = 1;
										}else{
											$amount_student = $amount_student + 1;
										}
										$old_grade=$rs['grade'];
										$grade_name = $rs['grade_name'];
								
										$total_student = $total_student+1;
									}
								}
								$string .= '<tr>';
									$string .= '<td>'.$grade_name.'</td>';
									$string .= '<td>'.$amount_student.'</td>';
								$string .= '</tr>';
							
								$string .= '<tr>';
									$string .= '<td>ចំនួនសិស្សសរុប</td>';
									$string .= '<td style="background: #eaeaea;">'.$total_student.'</td>';
								$string .= '</tr>';
							$string .= '</table>';
						$string .= '</td>';
					$string .= '</tr>';
				$string .= '</table>';
    		}	
    		else if($type==6){
    			
    			$old_grade=0;
    			$amount_student = 0;
    			$grade_name = "";
    			$total_student = 0;
    			 
    			///////////////////////////////////////////////////////////////////	Kindergarten (មត្តេយ្យ) //////////////////////////////////////////////////////
    			 
    			$string .= '<table border="0" width="100%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;margin-top: 35px;">';
    				$string .= '<tr>';
    					$string .= '<td width="30%" valign="top">';
    						$string .= '<table border="1" width="100%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;">';
    							$string .= '<tr style="background: #c1d0f3;">';
    								$string .= '<td colspan="2">Summary report for  '.$date.'</td>';
    							$string .= '</tr>';
    			
				    			$string .= '<tr>';
				    				$string .= '<td colspan="2">Kindergarten</td>';
				    			$string .= '</tr>';
				    			
				    			$string .= '<tr style="background: #c1d0f3;">';
					    			$string .= '<td width="50%">Level</td>';
					    			$string .= '<td width="50%">Amount Students</td>';
				    			$string .= '</tr>';
		    			
				    			foreach ($data as $key=>$rs){
				    				if($rs['degree']==4){
				    					if($key>0 && $old_grade!=$rs['grade'] && $old_grade!=""){
				    						$string .= '<tr>';
				    						$string .= '<td>'.$grade_name.'</td>';
				    						$string .= '<td>'.$amount_student.'</td>';
				    						$string .= '</tr>';
				    							
				    						$amount_student = 1;
				    					}else{
				    						$amount_student = $amount_student + 1;
				    					}
				    					$old_grade=$rs['grade'];
				    					$grade_name = $rs['grade_name'];
				    			
				    					$total_student = $total_student+1;
				    				}
				    			}
				    			$string .= '<tr>';
					    			$string .= '<td>'.$grade_name.'</td>';
					    			$string .= '<td>'.$amount_student.'</td>';
				    			$string .= '</tr>';
				    				
				    			$string .= '<tr>';
					    			$string .= '<td>Total Student`s Number</td>';
					    			$string .= '<td style="background: #eaeaea;">'.$total_student.'</td>';
				    			$string .= '</tr>';
				    		$string .= '</table>';
				    	$string .= '</td>';
				    				
				    	$string .= '<td width="5%">&nbsp;</td>';
    			
    			///////////////////////////////////////////////////////////////////	Primary (បឋមសិក្សា) //////////////////////////////////////////////////////
    			
    			$old_grade=0;
    			$amount_student = 0;
    			$grade_name = "";
    			$total_student = 0;
    			
	    				$string .= '<td width="30%" valign="top">';
	    					$string .= '<table border="1" width="100%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;">';
	    						$string .= '<tr style="background: #c1d0f3;">';
	    							$string .= '<td colspan="2">Summary report for '.$date.'</td>';
	    						$string .= '</tr>';
	    				
				    			$string .= '<tr>';
				    				$string .= '<td colspan="2">Adult</td>';
				    			$string .= '</tr>';
				    				
				    			$string .= '<tr style="background: #c1d0f3;">';
					    			$string .= '<td width="50%">Level</td>';
					    			$string .= '<td width="50%">Amount Student</td>';
				    			$string .= '</tr>';
	    				
				    			foreach ($data as $key=>$rs){
				    				if($rs['degree']==5){
				    					if($key>0 && $old_grade!=$rs['grade'] && $old_grade!=""){
				    						$string .= '<tr>';
				    						$string .= '<td>'.$grade_name.'</td>';
				    						$string .= '<td>'.$amount_student.'</td>';
				    						$string .= '</tr>';
				    			
				    						$amount_student = 1;
				    					}else{
				    						$amount_student = $amount_student + 1;
				    					}
				    					$old_grade=$rs['grade'];
				    					$grade_name = $rs['grade_name'];
				    						
				    					$total_student = $total_student+1;
				    				}
				    			}
				    			$string .= '<tr>';
					    			$string .= '<td>'.$grade_name.'</td>';
					    			$string .= '<td>'.$amount_student.'</td>';
				    			$string .= '</tr>';
				    			
				    			$string .= '<tr>';
					    			$string .= '<td>Total Student`s Number</td>';
					    			$string .= '<td style="background: #eaeaea;">'.$total_student.'</td>';
				    			$string .= '</tr>';
				    		$string .= '</table>';
				    	$string .= '</td>';
	    			$string .= '</tr>';
	    		$string .= '</table>';
    		}
    		else if($type==2){
    			 
    			$old_grade=0;
    			$amount_student = 0;
    			$grade_name = "";
    			$total_student = 0;
    		
    			///////////////////////////////////////////////////////////////////	Kindergarten (មត្តេយ្យ) //////////////////////////////////////////////////////
    		
    			$string .= '<table border="0" width="50%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;margin-top: 35px;">';
    				$string .= '<tr>';
    					$string .= '<td width="30%" valign="top">';
    						$string .= '<table border="1" width="100%" style="white-space:nowrap;border-collapse: collapse;margin: 0 auto;text-align: center;">';
    							$string .= '<tr style="background: #c1d0f3;">';
    								$string .= '<td colspan="2">Summary report for  '.$date.'</td>';
    							$string .= '</tr>';
    			 
				    			$string .= '<tr>';
				    				$string .= '<td colspan="2">Kindergarten</td>';
				    			$string .= '</tr>';
				    			 
				    			$string .= '<tr style="background: #c1d0f3;">';
					    			$string .= '<td width="50%">Level</td>';
					    			$string .= '<td width="50%">Amount Students</td>';
				    			$string .= '</tr>';
    			 
				    			foreach ($data as $key=>$rs){
			    					if($key>0 && $old_grade!=$rs['grade'] && $old_grade!=""){
			    						$string .= '<tr>';
				    						$string .= '<td>'.$grade_name.'</td>';
				    						$string .= '<td>'.$amount_student.'</td>';
			    						$string .= '</tr>';
			    						 
			    						$amount_student = 1;
			    					}else{
			    						$amount_student = $amount_student + 1;
			    					}
			    					$old_grade=$rs['grade'];
			    					$grade_name = $rs['grade_name'];
			    					 
			    					$total_student = $total_student+1;
				    			}
				    			$string .= '<tr>';
					    			$string .= '<td>'.$grade_name.'</td>';
					    			$string .= '<td>'.$amount_student.'</td>';
				    			$string .= '</tr>';
				    		
				    			$string .= '<tr>';
					    			$string .= '<td>Total Student`s Number</td>';
					    			$string .= '<td style="background: #eaeaea;">'.$total_student.'</td>';
				    			$string .= '</tr>';
				    		$string .= '</table>';
				    	$string .= '</td>';
    				$string .= '</tr>';
    			$string .= '</table>';
    		}
    	}
    	
    	
    	
    	//echo $string;exit();
    	return $string;
    	
    }
    
    
    
    
    
    function getStudentPayableLastMonth($search,$payfor_type,$type){ //  
    	$db = $this->getAdapter();

    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	
    	$sql=" SELECT 
				  sp.id,
				  sp.`student_id`,
				  spd.`validate` 
				FROM
				  rms_student as s,
				  `rms_student_payment` AS sp,
				  `rms_student_paymentdetail` AS spd 
				WHERE 
				  sp.id = spd.`payment_id`
				  and s.stu_id = sp.student_id
				  and s.stu_id = sp.student_id 
				  AND sp.is_subspend = 0 
				  AND spd.type = $type 
				  AND spd.`is_start` = 1
				  AND sp.`payfor_type` = $payfor_type
				  $branch_id
    		 ";
    	
    	$where = " ";
    	
    		
    	$first_day = 1;
    	$last_day = 31;
    	$year=date("Y");
    	$last_month = date("m") - 1;
    	 
    	$end = $year.'-'.$last_month.'-'.$last_day;
    	 
    	$to_date = (empty($end))? '1': " spd.`validate` <= '".$end." 23:59:59'";
    	 
    	$where .= " AND ".$to_date;
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	
    	if(!empty($search['service'])){
    		$where.= " AND spd.`service_id` = ".$search['service'];
    	}
    	if(!empty($search['branch'])){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if(!empty($search['degree'])){
    		$where.= " AND s.`degree` = ".$search['degree'];
    	}
    	if(!empty($search['grade'] )){
    		$where.= " AND s.`grade` = ".$search['grade'];
    	}
    	if(!empty($search['room'])){
    		$where.= " AND s.`room` = ".$search['room'];
    	}
    	//echo $sql.$where;//exit();
    	return $db->fetchAll($sql.$where);
    }
    
    
    function getStudentPayableLastMonthService($search,$payfor_type,$type,$service_type){ //
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	 
    	$sql=" SELECT
			    	sp.id,
			    	sp.`student_id`,
			    	spd.`validate`
			    FROM
			    	rms_service as s,
			    	rms_student as st,
			    	`rms_student_payment` AS sp,
			    	`rms_student_paymentdetail` AS spd
		    	WHERE
			    	sp.id = spd.`payment_id`
			    	and st.stu_id = s.stu_id
			    	and s.stu_id = sp.student_id
			    	and s.type = $service_type
			    	AND sp.is_subspend = 0
			    	AND spd.type = $type
			    	AND spd.`is_start` = 1
			    	AND sp.`payfor_type` = $payfor_type
			    	$branch_id
    	";
    	 
    	$where = " ";
    	 
    
    	$first_day = 1;
    	$last_day = 31;
    	$year=date("Y");
    	$last_month = date("m") - 1;
    
    	$end = $year.'-'.$last_month.'-'.$last_day;
    
    	$to_date = (empty($end))? '1': " spd.`validate` <= '".$end." 23:59:59'";
    
    	$where .= " AND ".$to_date;
    	 
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	 
    	 
    	if(!empty($search['service'])){
    		$where.= " AND s.`service_id` = ".$search['service'];
    	}
    	if(!empty($search['branch'])){
	    	$where.= " AND sp.`branch_id` = ".$search['branch'];
	    }
	    if(!empty($search['degree'])){
	    	$where.= " AND sp.`degree` = ".$search['degree'];
	    }
	    if(!empty($search['grade'] )){
	    	$where.= " AND sp.`grade` = ".$search['grade'];
	    }
	    if(!empty($search['room'])){
	    		$where.= " AND sp.`room_id` = ".$search['room'];
	    }
	    //echo $sql.$where;//exit();
	    return $db->fetchAll($sql.$where);
    }
    
    function getStudentPayableThisMonth($search,$payfor_type,$type){ //
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	
    	$sql=" SELECT
			    	sp.id,
			    	sp.`student_id`,
			    	spd.`validate`
			    FROM
			    	rms_student as st,
			    	rms_service as s,
			    	`rms_student_payment` AS sp,
			    	`rms_student_paymentdetail` AS spd
			    WHERE 
			    	sp.id = spd.`payment_id`
			    	and st.stu_id = sp.student_id
			    	and s.stu_id = st.stu_id
			    	AND sp.is_subspend = 0
			    	AND spd.type = $type
			    	AND spd.`is_start` = 1
			    	AND sp.`payfor_type` = $payfor_type
			    	$branch_id
    		 ";
    	 
    	$where = " ";
    	 
    	$first_day = 1;
    	$last_day = date("t");
    	$year=date("Y");
    	$for_month = date("m");

    	$start = $year.'-'.$for_month.'-'.$first_day;
    	$end = $year.'-'.$for_month.'-'.$last_day;
    	 
    	$from_date = (empty($start))? '1': " spd.`validate` >= '".$start." 00:00:00'";
    	$to_date = (empty($end))? '1': " spd.`validate` <= '".$end." 23:59:59'";
    	 
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['service'] > 0){
    		$where.= " AND spd.`service_id` = ".$search['service'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if(!empty($search['degree'])){
    		$where.= " AND st.`degree` = ".$search['degree'];
    	}
    	if(!empty($search['grade'] )){
    		$where.= " AND st.`grade` = ".$search['grade'];
    	}
    	if(!empty($search['room'])){
    		$where.= " AND st.`room` = ".$search['room'];
    	}
    	//echo $sql.$where;//exit();
    	return $db->fetchAll($sql.$where);
    }
    
    function getStudentPayableThisMonthService($search,$payfor_type,$type,$service_type){ //
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	 
    	$sql=" SELECT
			    	sp.id,
			    	sp.`student_id`,
			    	spd.`validate`
			    FROM
			    	rms_student as st,
			    	rms_service as s,
			    	`rms_student_payment` AS sp,
			    	`rms_student_paymentdetail` AS spd
	    		WHERE
			    	sp.id = spd.`payment_id`
			    	and st.stu_id = sp.student_id
			    	and s.stu_id = st.stu_id
			    	and s.type=$service_type
			    	AND sp.is_subspend = 0
			    	AND spd.type = $type
			    	AND spd.`is_start` = 1
			    	AND sp.`payfor_type` = $payfor_type
			    	$branch_id
    	";
    
    	$where = " ";
    
    	$first_day = 1;
    	$last_day = date("t");
    	$year=date("Y");
    	$for_month = date("m");
    
    	$start = $year.'-'.$for_month.'-'.$first_day;
    	$end = $year.'-'.$for_month.'-'.$last_day;
    
    	$from_date = (empty($start))? '1': " spd.`validate` >= '".$start." 00:00:00'";
    	$to_date = (empty($end))? '1': " spd.`validate` <= '".$end." 23:59:59'";
    
    	$where .= " AND ".$from_date." AND ".$to_date;
    	 
    	 
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	 
    	if($search['service'] > 0){
    		$where.= " AND s.`service_id` = ".$search['service'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	if(!empty($search['degree'])){
    		$where.= " AND sp.`degree` = ".$search['degree'];
    	}
    	if(!empty($search['grade'] )){
    		$where.= " AND sp.`grade` = ".$search['grade'];
    	}
    		if(!empty($search['room'])){
    				$where.= " AND sp.`room_id` = ".$search['room'];
	    }
	    //echo $sql.$where;//exit();
	    return $db->fetchAll($sql.$where);
    }
    
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    
    public function getAllEnglishFulltimePaymentList($search){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	
    	$sql = "SELECT
    				sp.*,
					sp.id,
					
					(select branch_namekh from rms_branch where br_id = sp.branch_id limit 1) as branch_name,
					(select last_name from rms_users as u where u.id = sp.user_id limit 1) as user_name,
					
					sp.`student_id`,
					st.`stu_code`,
					st.`stu_enname`,
					st.`stu_khname`,
					(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` limit 1) AS sex,
					st.`tel`,
					(select major_enname from rms_major where major_id =  st.`grade` limit 1) as current_grade,
					sp.`create_date`,
					sp.`is_new`,
					sp.`receipt_number`,
					(select en_name from rms_dept where dept_id = st.`degree` limit 1) as degree,
					(select major_enname from rms_major where major_id =  sp.`grade` limit 1) as grade,
					
					(select room_name from rms_room where rms_room.room_id = sp.`room_id` limit 1) as room,
					sp.`time`,
					
					sp.`tuition_fee`,
					sp.`discount_percent`,
					sp.`discount_fix`,
					sp.`total_payment`,
					sp.`admin_fee`,
					sp.`other_fee`,
					sp.`material_fee`,
					
					sp.`grand_total_payment`,
					sp.`grand_total_paid_amount`,
					sp.`grand_total_balance`,
					sp.`note`,
					sp.is_subspend,
					(select v.name_en from rms_view as v where v.type=5 and key_code = sp.is_subspend limit 1) as suspend_type,
					(select date from rms_student_drop where stu_id = sp.student_id and drop_from = 1 order by id DESC limit 1) as date_stop,
					spd.qty,
					spd.`start_date`,
					spd.`validate`,
					spd.`payment_term`,
					spd.`is_start`,
					(select name_en from rms_view where type=12 and key_code=sp.is_void limit 1) as void_status
				FROM 
					`rms_student_payment` AS sp,
					`rms_student_paymentdetail` AS spd,
					`rms_student` AS st
				WHERE 
					sp.id=spd.`payment_id`
					AND st.`stu_id`=sp.`student_id`
					AND sp.`payfor_type`=6
					AND spd.`service_id`=4
					$branch_id
    		  ";
    	
    	$where = " ";
    	
//     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	
//     	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$order=" ORDER BY st.`stu_code` ASC , sp.create_date ASC ";
					
//     	if(!empty($search['for_month'])){
//     		$first_day = 1;
//     		$last_day = 31;
//     		$year = $search['for_year'];
//     		$for_month = $search['for_month'];
    		 
//     		$end = $year.'-'.$for_month.'-'.$last_day;
    		 
//     		$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
    		 
//     		$where .= " AND ".$to_date;
//     	}
    	
    	$today = date("Y-m-d");
    	$to_date = (empty($today))? '1': "sp.create_date <= '".$today." 23:59:59'";
    	$where .= " AND ".$to_date;
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['degree_en_ft'] > 0){
    		$where.= " and spd.is_start = 1 AND st.`degree` = ".$search['degree_en_ft'];
    	}
    	if($search['grade_en_ft'] > 0){
    		$where.= " and spd.is_start = 1 AND st.`grade` = ".$search['grade_en_ft'];
    	}
    	if($search['room'] > 0){
    		$where.= " and spd.is_start = 1 AND st.`room` = ".$search['room'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	
    	$data = $db->fetchAll($sql.$where.$order);
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$string = "";
    	$student_id="";
    	$i=0;
    	 
    	if(!empty($data)){
    		$string .= '<table width="100%" cellpadding="8" border="1"​ style="margin:0 auto;border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang;" >';
    		foreach ($data as $key => $rs){
    			if($rs['student_id']!=$student_id){
    				$i++;
    				if($key>0){
    					$string .= '<tr><td colspan="22">&nbsp;</td></tr></table>';
    					$string .= '<table width="100%" cellpadding="8"​ border="1" style="margin:0 auto;border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang ;" >';
    				}
    	
    				$string .= '<tr style=" font-size:12px; line-height:20px;  font-weight: bold;background: #c1d0f3;" align="center" >';
	    				$string .= '<td class="bor">'.$tr->translate("N_O").'</td>';
	    				$string .= '<td class="bor" colspan="2">'.$tr->translate("STUDENT_ID").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("NAME_KH").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("NAME_EN").'</td>';
	    				$string .= '<td class="bor">'.$tr->translate("SEX").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("PHONE").'</td>';
	    				$string .= '<td style="border-bottom: 1px solid #000;" colspan="2">'.$tr->translate("DEGREE").'</td>';
	    				$string .= '<td style="border-bottom: 1px solid #000;" colspan="4">'.$tr->translate("CURRENT_GRADE").'</td>';
    				$string .= '</tr>';
    	
    				$string .= '<tr style="font-size:12px; line-height:20px;font-family: Khmer OS Battambang ;background: #c1d0f3;" align="center">';
	    				$string .= '<td class="bor_r">'.$i.'</td>';
	    				$string .= '<td class="bor_r" colspan="2">'.$rs['stu_code'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['stu_khname'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['stu_enname'].'</td>';
	    				$string .= '<td class="bor_r" >'.$rs['sex'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['tel'].'</td>';
	    				$string .= '<td class="bor_r" colspan="2">'.$rs['degree'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['current_grade'].'</td>';
    				$string .= '</tr>';
    	
    				$string .= '<tr id="row" align="center" style="background: #f0f07d; line-height:16px;">';
	    				$string .= '<td rowspan="2" class="padd">Levels</td>';
	    				$string .= '<td rowspan="2" class="padd">Room</td>';
	    				$string .= '<td rowspan="2" class="padd">Time</td>';
	    				$string .= '<td rowspan="2" class="padd">Is New<br/>Student</td>';
	    				$string .= '<td colspan="8" class="padd">Study Fee Payment</td>';
	    				$string .= '<td rowspan="2" class="padd">Admin<br/>Fee</td>';
	    				$string .= '<td rowspan="2" class="padd">Material<br />Fee</td>';
	    				$string .= '<td rowspan="2" class="padd">Other<br />Fee</td>';
	    				$string .= '<td rowspan="2" class="padd">Credit</td>';
	    				$string .= '<td rowspan="2" class="padd">Total<br />Amount</td>';
	    				$string .= '<td rowspan="2" class="padd">Note</td>';
	    				$string .= '<td rowspan="2" class="padd">Stop <br />Student</td>';
	    				$string .= '<td rowspan="2" colspan="2" class="padd">Date Stop</td>';
	    				$string .= '<td rowspan="2" class="padd">Status</td>';
    				$string .= '</tr>';
    	
    				$string .= '<tr id="row" align="center" style="background: #e1e29b; line-height: 14px;">';
	    				$string .= '<td class="padd">IssueDate</td>';
	    				$string .= '<td class="padd">Receipt No.</td>';
	    				$string .= '<td class="padd">Amount</td>';
	    				$string .= '<td class="padd">Discount</td>';
	    				$string .= '<td class="padd">Total Amount</td>';
	    				$string .= '<td class="padd">Start Date</td>';
	    				$string .= '<td class="padd">End Date</td>';
	    				$string .= '<td class="padd">Duration</td>';
    				$string .= '</tr>';
    			}
    			 
    			$student_id=$rs['student_id'];
    			 
    			$amount_month = '';
    			$is_new = '';
    			$color = '';
    			$discount = '';
    			$admin_fee = '';
    			$material_fee = '';
    			$other_fee = '';
    			$grand_total_balance = '';
    			$suspend_type = '';
    			$date_stop = '';
    			$void_status = '';
    			 
    			 
    			if($rs['payment_term']==1){
    				$amount_month=$rs['qty']." months";
    			}else if($rs['payment_term']==2){
    				$amount_month="3 months";
    			}else if($rs['payment_term']==3){
    				$amount_month="6  months";
    			}else if($rs['payment_term']==4){
    				$amount_month="12 months";
    			}else if($rs['payment_term']==5){
    				$amount_month=$rs['qty']." Days";
    			}
    			 
    			if($rs['is_new']==1){
    				$is_new = date('d-m-Y',strtotime($rs['create_date']));
    			}
    			if($rs["is_subspend"]>0){
    				$color = "style='color:red'";
    			}
    			 
    			if($rs['discount_percent']>0 && $rs['discount_fix']>0){
    				$discount = $rs['discount_percent']." %"." + $".$rs['discount_fix'];
    			}else if($rs['discount_percent']>0){
    				$discount = $rs['discount_percent']." %";
    			}else if($rs['discount_fix']>0){
    				$discount = "$ ".$rs['discount_fix'];
    			}
    			 
    			if($rs['admin_fee']>0){
    				$admin_fee = "$ ".$rs['admin_fee'];
    			}
    			if($rs['material_fee']>0){
    				$material_fee = "$ ".$rs['material_fee'];
    			}
    			if($rs['other_fee']>0){
    				$other_fee = "$ ".$rs['other_fee'];
    			}
    			if($rs['grand_total_balance']>0){
    				$grand_total_balance = "$ ".$rs['grand_total_balance'];
    			}
    			if($rs['is_subspend']>0){
    				$suspend_type = $rs['suspend_type'];
    			}
    			if($rs['is_subspend']>0){
    				$date_stop = date("d-m-Y",strtotime($rs['date_stop']));
    			}
    			if($rs['is_void']==1){
    				$void_status = $rs['void_status'];
    			}
    			 
    			$string .= '<tr id="row" align="center"  class="hover" '.$color.'>';
	    			$string .= '<td  class="padd">'.$rs['grade'].'</td>';
	    			$string .= '<td  class="padd">'.$rs['room'].'</td>';
	    			$string .= '<td  class="padd">'.$rs['time'].'</td>';
	    			$string .= '<td  class="padd">'.$is_new.'</td>';
	    			$string .= '<td  class="padd">'.date('d-m-Y',strtotime($rs['create_date'])).'</td>';
	    			$string .= '<td  class="padd">'.$rs['receipt_number'].'</td>';
	    			$string .= '<td  class="padd">'.' $ '.$rs['tuition_fee'].'</td>';
	    			$string .= '<td  class="padd">'.$discount.'</td>';
	    			$string .= '<td  class="padd">'.' $ '.$rs['tuition_fee_after_discount'].'</td>';
	    			$string .= '<td  class="padd">'.date('d-m-Y',strtotime($rs['start_date'])).'</td>';
	    			$string .= '<td  class="padd">'.date('d-m-Y',strtotime($rs['validate'])).'</td>';
	    			$string .= '<td  class="padd">'.$amount_month.'</td>';
	    			$string .= '<td  class="padd">'.$admin_fee.'</td>';
	    			$string .= '<td  class="padd">'.$material_fee.'</td>';
	    			$string .= '<td  class="padd">'.$other_fee.'</td>';
	    			$string .= '<td  class="padd">'.$grand_total_balance.'</td>';
	    			$string .= '<td  class="padd">'.' $ '.$rs['grand_total_payment'].'</td>';
	    			$string .= '<td  class="padd">'.$rs['note'].'</td>';
	    			$string .= '<td  class="padd">'.$suspend_type.'</td>';
	    			$string .= '<td  class="padd" colspan="2">'.$date_stop.'</td>';
	    			$string .= '<td  class="padd">'.$void_status."(".$rs['is_start'].")".'</td>';
    			$string .= '</tr>';
    		}
    		$string .= '</table>';
    	}
    	
    	//echo $string;exit();
    	return $string;
    }
   
    
    public function getAllKhFulltimePaymentList($search){
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	
    	//echo $search['for_month'];exit();
    	 
    	$sql = "SELECT
    				sp.*,
			    	sp.id,
			    	
			    	(select branch_namekh from rms_branch where br_id = sp.branch_id limit 1) as branch_name,
					(select last_name from rms_users as u where u.id = sp.user_id limit 1) as user_name,
			    	
			    	sp.`student_id`,
			    	st.`stu_code`,
			    	st.`stu_enname`,
			    	st.`stu_khname`,
			    	(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` limit 1) AS sex,
			    	st.`tel`,
			    	(select major_enname from rms_major where major_id =  st.`grade` limit 1) as current_grade,
			    	sp.`create_date`,
			    	sp.`is_new`,
			    	sp.`receipt_number`,
			    	(select en_name from rms_dept where dept_id = st.`degree` limit 1) as degree,
			    	(select major_enname from rms_major where major_id =  sp.`grade` limit 1) as grade,
			    	(select room_name from rms_room where rms_room.room_id = sp.`room_id` limit 1) as room,
			    	sp.`time`,
			    		
			    	sp.`tuition_fee`,
			    	sp.`discount_percent`,
			    	sp.`discount_fix`,
			    	sp.`total_payment`,
			    	sp.`admin_fee`,
			    	sp.`other_fee`,
			    	sp.`material_fee`,
			    		
			    	sp.`grand_total_payment`,
			    	sp.`grand_total_paid_amount`,
			    	sp.`grand_total_balance`,
			    	sp.`note`,
			    	sp.is_subspend,
			    	(select v.name_en from rms_view as v where v.type=5 and key_code = sp.is_subspend limit 1) as suspend_type,
			    	(select date from rms_student_drop where stu_id = sp.student_id and drop_from = 1 order by id DESC limit 1) as date_stop,
			    	
			    	spd.qty,
			    	spd.`start_date`,
			    	spd.`validate`,
			    	spd.`payment_term`,
			    	spd.`is_start`,
			    	(select name_en from rms_view where type=12 and key_code=sp.is_void limit 1) as void_status
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_student_paymentdetail` AS spd,
			    	`rms_student` AS st
			    WHERE
			    	sp.id=spd.`payment_id`
			    	AND st.`stu_id`=sp.`student_id`
			    	AND sp.`payfor_type`=1
			    	AND spd.`service_id`=4
			    	$branch_id
    			";
    	 
    	$where = " ";
    	$order=" ORDER BY st.`stu_code` ASC, sp.create_date ASC ";
    		
//     	if(!empty($search['for_month'])){
//     		$first_day = 1;
//     		$last_day = 31;
//     		$year=$search['for_year'];
//     		$for_month = $search['for_month'];
    		 
//     		$end = $year.'-'.$for_month.'-'.$last_day;
    	
//     		$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
    	
//     		$where = " AND ".$to_date;
//     	}
    	
//     	$from_date = "sp.create_date >= '2018-05-01 00:00:00'";
//     	$to_date = "sp.create_date <= '2018-05-05 23:59:59'";
    	
    	$today = date("Y-m-d");
    	$to_date = (empty($today))? '1': "sp.create_date <= '".$today." 23:59:59'";
    	$where .= " AND ".$to_date;
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	 
    	if($search['degree_kh_ft'] > 0){
    		$where.= " and spd.is_start=1 AND st.`degree` = ".$search['degree_kh_ft'];
	    }
	    if($search['grade_kh_ft'] > 0){
	    	$where.= " and spd.is_start=1 AND st.`grade` = ".$search['grade_kh_ft'];
	    }
	    if($search['room'] > 0){
	    	$where.= " and spd.is_start=1 AND st.`room` = ".$search['room'];
	    }
	    if($search['branch'] > 0){
	    	$where.= " AND sp.`branch_id` = ".$search['branch'];
	    }
	    
	    $data = $db->fetchAll($sql.$where.$order);
    	
	    
	    $tr = Application_Form_FrmLanguages::getCurrentlanguage();
	    
	    $string = "";
	    $student_id=0;
	    $i=0;
	    
	    if(!empty($data)){
	    	$string .= '<table cellpadding="8" border="1"​ style="margin:0 auto;width:100%; border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang;" >';
	    	foreach ($data as $key => $rs){
	    		if($rs['student_id']!=$student_id){$i++;
	    			if($key>0 ){
	    				$string .= '<tr><td colspan="25">&nbsp;</td></tr></table>';
	    				$string .= '<table cellpadding="8"​ border="1" style="margin:0 auto;width:100%; border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang ;" >';
	    			}
	    			
	    			$string .= '<tr style=" font-size:12px; line-height:20px;  font-weight: bold;background: #c1d0f3;" align="center" >';
	    				$string .= '<td class="bor">'.$tr->translate("N_O").'</td>';
	    				$string .= '<td class="bor" colspan="2">'.$tr->translate("STUDENT_ID").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("NAME_KH").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("NAME_EN").'</td>';
	    				$string .= '<td class="bor">'.$tr->translate("SEX").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("PHONE").'</td>';
	    				$string .= '<td style="border-bottom: 1px solid #000;" colspan="2">'.$tr->translate("DEGREE").'</td>';
	    				$string .= '<td style="border-bottom: 1px solid #000;" colspan="4">'.$tr->translate("CURRENT_GRADE").'</td>';
	    			$string .= '</tr>';
	    			
	    			$string .= '<tr style="font-size:12px; line-height:20px;font-family: Khmer OS Battambang ;background: #c1d0f3;" align="center">';
	    				$string .= '<td class="bor_r">'.$i.'</td>';
	    				$string .= '<td class="bor_r" colspan="2">'.$rs['stu_code'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['stu_khname'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['stu_enname'].'</td>';
	    				$string .= '<td class="bor_r" >'.$rs['sex'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['tel'].'</td>';
	    				$string .= '<td class="bor_r" colspan="2">'.$rs['degree'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['current_grade'].'</td>';
	    			$string .= '</tr>';
	    			
	    			$string .= '<tr id="row" align="center" style="background: #f0f07d; line-height:16px;">';
	    				$string .= '<td rowspan="2" class="padd">កម្រិត</td>';
	    				$string .= '<td rowspan="2" class="padd">បន្ទប់</td>';
	    				$string .= '<td rowspan="2" class="padd">វេន</td>';
	    				$string .= '<td rowspan="2" class="padd">សិស្សថ្មី</td>';
	    				$string .= '<td colspan="8" class="padd">ការទូទាត់ថ្លៃសិក្សា</td>';
	    				$string .= '<td rowspan="2" class="padd">ថ្លៃ<br />រដ្ឋបាល</td>';
	    				$string .= '<td rowspan="2" class="padd">ថ្លៃសម្ភារៈ<br />សិក្សា</td>';
	    				$string .= '<td rowspan="2" class="padd">ថ្លៃផ្សេងៗ</td>';
	    				$string .= '<td rowspan="2" class="padd">ជំពាក់</td>';
	    				$string .= '<td rowspan="2" class="padd">ទឹកប្រាក់<br />សរុបរួម</td>';
	    				$string .= '<td rowspan="2" class="padd">សម្គាល់</td>';
	    				$string .= '<td rowspan="2" class="padd">សិស្ស<br />ឈប់</td>';
	    				$string .= '<td rowspan="2" colspan="2" class="padd">ថ្ងៃឈប់</td>';
	    				$string .= '<td rowspan="2" class="padd">ស្ថានការណ៍</td>';
	    			$string .= '</tr>';
	    			
	    			$string .= '<tr id="row" align="center" style="background: #e1e29b; line-height: 14px;">';
	    				$string .= '<td class="padd">ថ្ងៃបង់ប្រាក់</td>';
	    				$string .= '<td class="padd">លេខបង្កាន់ដៃ</td>';
	    				$string .= '<td class="padd">ទឹកប្រាក់</td>';
	    				$string .= '<td class="padd">បញ្ចុះតម្លៃ</td>';
	    				$string .= '<td class="padd">ទឹកប្រាក់សរុប</td>';
	    				$string .= '<td class="padd">ថ្ងៃចាប់ផ្តើម</td>';
	    				$string .= '<td class="padd">ថ្ងៃផុតកំណត់</td>';
	    				$string .= '<td class="padd">រយះពេល</td>';
	    			$string .= '</tr>';
	    		}
	    		
	    		$student_id=$rs['student_id'];
	    		
	    		$amount_month = '';
	    		$is_new = '';
	    		$color = '';
	    		$discount = '';
	    		$admin_fee = '';
	    		$material_fee = '';
	    		$other_fee = '';
	    		$grand_total_balance = '';
	    		$suspend_type = '';
	    		$date_stop = '';
	    		$void_status = '';
	    		
	    		
	    		if($rs['payment_term']==1){
	    			$amount_month=$rs['qty']." ខែ";
	    		}else if($rs['payment_term']==2){
	    			$amount_month="3 ខែ";
	    		}else if($rs['payment_term']==3){
	    			$amount_month="6  ខែ";
	    		}else if($rs['payment_term']==4){
	    			$amount_month="12 ខែ";
	    		}else if($rs['payment_term']==5){
	    			$amount_month=$rs['qty']." ថ្ងៃ";
	    		}
	    		
	    		if($rs['is_new']==1){
	    			$is_new = date('d-m-Y',strtotime($rs['create_date']));
	    		}
	    		if($rs["is_subspend"]>0){
	    			$color = "style='color:red'";
	    		}
	    		
	    		if($rs['discount_percent']>0 && $rs['discount_fix']>0){
	    			$discount = $rs['discount_percent']." %"." + $".$rs['discount_fix'];
	    		}else if($rs['discount_percent']>0){
	    			$discount = $rs['discount_percent']." %";
	    		}else if($rs['discount_fix']>0){
	    			$discount = "$ ".$rs['discount_fix'];
	    		}
	    		
	    		if($rs['admin_fee']>0){
	    			$admin_fee = "$ ".$rs['admin_fee'];
	    		}
	    		if($rs['material_fee']>0){
	    			$material_fee = "$ ".$rs['material_fee'];
	    		}
	    		if($rs['other_fee']>0){
	    			$other_fee = "$ ".$rs['other_fee'];
	    		}
	    		if($rs['grand_total_balance']>0){
	    			$grand_total_balance = "$ ".$rs['grand_total_balance'];
	    		}
	    		if($rs['is_subspend']>0){
	    			$suspend_type = $rs['suspend_type'];
	    		}
	    		if($rs['is_subspend']>0){
	    			$date_stop = date("d-m-Y",strtotime($rs['date_stop']));
	    		}
	    		if($rs['is_void']==1){
	    			$void_status = $rs['void_status'];
	    		}
	    		
	    		$string .= '<tr id="row" align="center" class="hover" '.$color.'>';
	    			$string .= '<td class="padd">'.$rs['grade'].'</td>';
	    			$string .= '<td class="padd">'.$rs['room'].'</td>';
	    			$string .= '<td class="padd">'.$rs['time'].'</td>';
	    			$string .= '<td class="padd">'.$is_new.'</td>';
	    			$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['create_date'])).'</td>';
	    			$string .= '<td class="padd">'.$rs['receipt_number'].'</td>';
	    			$string .= '<td class="padd">'.' $ '.$rs['tuition_fee'].'</td>';
	    			$string .= '<td class="padd">'.$discount.'</td>';
	    			$string .= '<td class="padd">'.' $ '.$rs['tuition_fee_after_discount'].'</td>';
	    			$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['start_date'])).'</td>';
	    			$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['validate'])).'</td>';
	    			$string .= '<td class="padd">'.$amount_month.'</td>';
	    			$string .= '<td class="padd">'.$admin_fee.'</td>';
	    			$string .= '<td class="padd">'.$material_fee.'</td>';
	    			$string .= '<td class="padd">'.$other_fee.'</td>';
	    			$string .= '<td class="padd">'.$grand_total_balance.'</td>';
	    			$string .= '<td class="padd">'.' $ '.$rs['grand_total_payment'].'</td>';
	    			$string .= '<td class="padd">'.$rs['note'].'</td>';
	    			$string .= '<td class="padd">'.$suspend_type.'</td>';
	    			$string .= '<td class="padd" colspan="2">'.$date_stop.'</td>';
	    			$string .= '<td class="padd">'.$void_status."(".$rs['is_start'].")".'</td>';
	    		$string .= '</tr>';
	    	}
	    	$string .= '</table>';
	    }
	    
	    //echo $string;exit();
	    return $string;
	    
    }
    
    
    public function getAllEnglishParttimePaymentList($search){
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    	 
    	$sql = "SELECT
			    	sp.*,
			    	sp.id,
			    	
			    	(select branch_namekh from rms_branch where br_id = sp.branch_id limit 1) as branch_name,
					(select last_name from rms_users as u where u.id = sp.user_id limit 1) as user_name,
			    	
			    	sp.`student_id`,
			    	st.`stu_code`,
			    	st.`stu_enname`,
			    	st.`stu_khname`,
			    	(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` limit 1) AS sex,
			    	st.`tel`,
			    	(select major_enname from rms_major where major_id =  st.`grade` limit 1) as current_grade,
			    	sp.`create_date`,
			    	sp.`is_new`,
			    	sp.`receipt_number`,
			    	(select en_name from rms_dept where dept_id = st.`degree` limit 1) as degree,
			    	(select major_enname from rms_major where major_id =  sp.`grade` limit 1) as grade,
			    	(select room_name from rms_room where rms_room.room_id = sp.`room_id` limit 1) as room,
			    	sp.`time`,
			    		
			    	sp.`tuition_fee`,
			    	sp.`discount_percent`,
			    	sp.`discount_fix`,
			    	sp.`total_payment`,
			    	sp.`admin_fee`,
			    	sp.`other_fee`,
			    	sp.`material_fee`,
			    		
			    	sp.`grand_total_payment`,
			    	sp.`grand_total_paid_amount`,
			    	sp.`grand_total_balance`,
			    	sp.`note`,
			    	sp.is_subspend,
			    	(select v.name_en from rms_view as v where v.type=5 and key_code = sp.is_subspend limit 1) as suspend_type,
			    	(select date from rms_student_drop where stu_id = sp.student_id and drop_from = 1 order by id DESC limit 1) as date_stop,
			    		
			    	spd.qty,
			    	spd.`start_date`,
			    	spd.`validate`,
			    	spd.`payment_term`,
			    	spd.`is_start`,
			    	(select name_en from rms_view where type=12 and key_code=sp.is_void limit 1) as void_status
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_student_paymentdetail` AS spd,
			    	`rms_student` AS st
			    WHERE
			    	sp.id=spd.`payment_id`
			    	AND st.`stu_id`=sp.`student_id`
			    	AND sp.`payfor_type`=2
			    	AND spd.`service_id`=4
			    	$branch_id
    			";
    	 
    	$where = " ";
    	$order=" ORDER BY st.`stu_code` ASC,sp.create_date ASC ";
    		
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	 
    	if($search['degree_gep'] > 0){
    		$where.= " and spd.is_start = 1 AND st.`degree` = ".$search['degree_gep'];
	    }
	    if($search['grade_gep'] > 0){
	    	$where.= " and spd.is_start = 1 AND st.`grade` = ".$search['grade_gep'];
	    }
	    if($search['room'] > 0){
	    	$where.= " and spd.is_start = 1 AND st.`room` = ".$search['room'];
	    }
	    if($search['branch'] > 0){
	    	$where.= " AND sp.`branch_id` = ".$search['branch'];
	    }
	    
// 	    if(!empty($search['for_month'])){
// 	    	$first_day = 1;
// 	    	$last_day = 31;
// 	    	$year=$search['for_year'];
// 	    	$for_month = $search['for_month'];
	    	 
// 	    	$end = $year.'-'.$for_month.'-'.$last_day;
	    	 
// 	    	$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
	    	 
// 	    	$where .= " AND ".$to_date;
// 	    }
	    
	    $today = date("Y-m-d");
	    $to_date = (empty($today))? '1': "sp.create_date <= '".$today." 23:59:59'";
	    $where .= " AND ".$to_date;
	    
	    $data = $db->fetchAll($sql.$where.$order);
	    
	    
	    
	    $tr = Application_Form_FrmLanguages::getCurrentlanguage();
	     
	    $string = "";
	    $student_id=0;
	    $i=0;
	     
	    if(!empty($data)){
	    	$string .= '<table cellpadding="8" border="1"​ style="margin:0 auto;width:100%; border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang;" >';
	    	foreach ($data as $key => $rs){
	    		if($rs['student_id']!=$student_id){
	    			$i++;
	    			if($key>0 ){
	    				$string .= '<tr><td colspan="25">&nbsp;</td></tr></table>';
	    				$string .= '<table cellpadding="8"​ border="1" style="margin:0 auto;width:100%; border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang ;" >';
	    			}
	    
	    			$string .= '<tr style=" font-size:12px; line-height:20px;  font-weight: bold;background: #c1d0f3;" align="center" >';
		    			$string .= '<td class="bor">'.$tr->translate("N_O").'</td>';
		    			$string .= '<td class="bor" colspan="2">'.$tr->translate("STUDENT_ID").'</td>';
		    			$string .= '<td class="bor" colspan="3">'.$tr->translate("NAME_KH").'</td>';
		    			$string .= '<td class="bor" colspan="3">'.$tr->translate("NAME_EN").'</td>';
		    			$string .= '<td class="bor">'.$tr->translate("SEX").'</td>';
		    			$string .= '<td class="bor" colspan="2">'.$tr->translate("PHONE").'</td>';
		    			$string .= '<td style="border-bottom: 1px solid #000;" colspan="4">'.$tr->translate("DEGREE").'</td>';
		    			$string .= '<td style="border-bottom: 1px solid #000;" colspan="4">'.$tr->translate("CURRENT_GRADE").'</td>';
	    			$string .= '</tr>';
	    
	    			$string .= '<tr style="font-size:12px; line-height:20px;font-family: Khmer OS Battambang ;background: #c1d0f3;" align="center">';
		    			$string .= '<td class="bor_r">'.$i.'</td>';
		    			$string .= '<td class="bor_r" colspan="2">'.$rs['stu_code'].'</td>';
		    			$string .= '<td class="bor_r" colspan="3">'.$rs['stu_khname'].'</td>';
		    			$string .= '<td class="bor_r" colspan="3">'.$rs['stu_enname'].'</td>';
		    			$string .= '<td class="bor_r" >'.$rs['sex'].'</td>';
		    			$string .= '<td class="bor_r" colspan="2">'.$rs['tel'].'</td>';
		    			$string .= '<td class="bor_r" colspan="4">'.$rs['degree'].'</td>';
		    			$string .= '<td class="bor_r" colspan="4">'.$rs['current_grade'].'</td>';
	    			$string .= '</tr>';
	    
	    			$string .= '<tr id="row" align="center" style="background: #f0f07d; line-height:16px;">';
		    			$string .= '<td rowspan="2" class="padd">Level</td>';
		    			$string .= '<td rowspan="2" class="padd">Room</td>';
		    			$string .= '<td rowspan="2" class="padd">Time</td>';
		    			$string .= '<td rowspan="2" class="padd">Is New<br />Student</td>';
		    			$string .= '<td colspan="8" class="padd">Study Fee Payment</td>';
		    			$string .= '<td rowspan="2" class="padd">Other<br />Fee</td>';
		    			$string .= '<td rowspan="2" class="padd">Credit</td>';
		    			$string .= '<td rowspan="2" class="padd">Total <br />Amount</td>';
		    			$string .= '<td rowspan="2" class="padd">Note</td>';
		    			$string .= '<td rowspan="2" class="padd">Stop<br />Student</td>';
		    			$string .= '<td rowspan="2" colspan="2" class="padd">Date Stop</td>';
		    			$string .= '<td rowspan="2" class="padd">Status</td>';
	    			$string .= '</tr>';
	    
	    			$string .= '<tr id="row" align="center" style="background: #e1e29b; line-height: 14px;">';
		    			$string .= '<td class="padd">IssueDate</td>';
		    			$string .= '<td class="padd">Receipt No.</td>';
		    			$string .= '<td class="padd">Amount</td>';
		    			$string .= '<td class="padd">Discount</td>';
		    			$string .= '<td class="padd">Total Amount</td>';
		    			$string .= '<td class="padd">Start Date</td>';
		    			$string .= '<td class="padd">End Date</td>';
		    			$string .= '<td class="padd">Duration</td>';
	    			$string .= '</tr>';
	    		}
	    		 
	    		$student_id=$rs['student_id'];
	    		 
	    		$amount_month = '';
	    		$is_new = '';
	    		$color = '';
	    		$discount = '';
	    		$admin_fee = '';
	    		$material_fee = '';
	    		$other_fee = '';
	    		$grand_total_balance = '';
	    		$suspend_type = '';
	    		$date_stop = '';
	    		$void_status = '';
	    		 
	    		 
	    		if($rs['payment_term']==1){
	    			$amount_month=$rs['qty']." months";
	    		}else if($rs['payment_term']==2){
	    			$amount_month="3 months";
	    		}else if($rs['payment_term']==3){
	    			$amount_month="6 months";
	    		}else if($rs['payment_term']==4){
	    			$amount_month="12 months";
	    		}else if($rs['payment_term']==5){
	    			$amount_month=$rs['qty']." Days";
	    		}
	    		 
	    		if($rs['is_new']==1){
	    			$is_new = date('d-m-Y',strtotime($rs['create_date']));
	    		}
	    		if($rs["is_subspend"]>0){
	    			$color = "style='color:red'";
	    		}
	    		 
	    		if($rs['discount_percent']>0 && $rs['discount_fix']>0){
	    			$discount = $rs['discount_percent']." %"." + $".$rs['discount_fix'];
	    		}else if($rs['discount_percent']>0){
	    			$discount = $rs['discount_percent']." %";
	    		}else if($rs['discount_fix']>0){
	    			$discount = "$ ".$rs['discount_fix'];
	    		}
	    		 
	    		if($rs['other_fee']>0){
	    			$other_fee = "$ ".$rs['other_fee'];
	    		}
	    		if($rs['grand_total_balance']>0){
	    			$grand_total_balance = "$ ".$rs['grand_total_balance'];
	    		}
	    		if($rs['is_subspend']>0){
	    			$suspend_type = $rs['suspend_type'];
	    		}
	    		if($rs['is_subspend']>0){
	    			$date_stop = date("d-m-Y",strtotime($rs['date_stop']));
	    		}
	    		if($rs['is_void']==1){
	    			$void_status = $rs['void_status'];
	    		}
	    		 
	    		$string .= '<tr id="row" align="center" class="hover" '.$color.'>';
		    		$string .= '<td class="padd">'.$rs['grade'].'</td>';
		    		$string .= '<td class="padd">'.$rs['room'].'</td>';
		    		$string .= '<td class="padd">'.$rs['time'].'</td>';
		    		$string .= '<td class="padd">'.$is_new.'</td>';
		    		$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['create_date'])).'</td>';
		    		$string .= '<td class="padd">'.$rs['receipt_number'].'</td>';
		    		$string .= '<td class="padd">'.' $ '.$rs['tuition_fee'].'</td>';
		    		$string .= '<td class="padd">'.$discount.'</td>';
		    		$string .= '<td class="padd">'.' $ '.$rs['tuition_fee_after_discount'].'</td>';
		    		$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['start_date'])).'</td>';
		    		$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['validate'])).'</td>';
		    		$string .= '<td class="padd">'.$amount_month.'</td>';
		    		$string .= '<td class="padd">'.$other_fee.'</td>';
		    		$string .= '<td class="padd">'.$grand_total_balance.'</td>';
		    		$string .= '<td class="padd">'.' $ '.$rs['grand_total_payment'].'</td>';
		    		$string .= '<td class="padd">'.$rs['note'].'</td>';
		    		$string .= '<td class="padd">'.$suspend_type.'</td>';
		    		$string .= '<td class="padd" colspan="2">'.$date_stop.'</td>';
		    		$string .= '<td class="padd">'.$void_status."(".$rs['is_start'].")".'</td>';
	    		$string .= '</tr>';
	    	}
	    	$string .= '</table>';
	    }
	    //echo $string;exit();
	    return $string;
    
    }
    
    
    public function getAllTransportPaymentList($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql = "SELECT
			    	sp.*,
			    	sp.id,
			    	
			    	(select branch_namekh from rms_branch where br_id = sp.branch_id limit 1) as branch_name,
					(select last_name from rms_users as u where u.id = sp.user_id limit 1) as user_name,
			    	
			    	sp.`student_id`,
			    	(select stu_code from rms_service where rms_service.stu_id = sp.student_id and rms_service.type=4 limit 1) as stu_code ,
			    	st.`stu_enname`,
			    	st.`stu_khname`,
			    	(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` limit 1) AS sex,
			    	st.`tel`,
			    	
			    	(select carid from rms_car where rms_car.id = ser.car_id ) as car_id,
			    	(select title from rms_program_name where rms_program_name.service_id = spd.service_id limit 1) as service_name,
			    	
			    	(select title from rms_program_name where rms_program_name.service_id = ser.service_id limit 1) as current_service_name,
			    	
			    	sp.`create_date`,
			    	sp.`is_new`,
			    	sp.`receipt_number`,
			    	 
			    	sp.`tuition_fee`,
			    	sp.`discount_percent`,
			    	sp.`discount_fix`,
			    	sp.`total_payment`,
			    	sp.`material_fee`,
			    	 
			    	sp.`grand_total_payment`,
			    	sp.`grand_total_paid_amount`,
			    	sp.`grand_total_balance`,
			    	sp.`note`,
			    	sp.time_for_car,
			    	ser.time_for_car as current_time_for_car,
			    	sp.is_subspend,
			    	(select v.name_en from rms_view as v where v.type=5 and key_code = sp.is_subspend limit 1) as suspend_type,
			    	(select date from rms_student_drop where stu_id = sp.student_id and drop_from = 2 order by id DESC limit 1) as date_stop,
			    	 
			    	spd.`start_date`,
			    	spd.`validate`,
			    	spd.qty,
			    	spd.`payment_term`,
			    	spd.`is_start`,
			    	(select name_en from rms_view where type=12 and key_code=sp.is_void limit 1) as void_status
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_student_paymentdetail` AS spd,
			    	`rms_student` AS st,
			    	rms_service as ser
		    	WHERE
			    	sp.id=spd.`payment_id`
			    	AND st.`stu_id`=sp.`student_id`
			    	and sp.student_id = ser.stu_id
			    	AND sp.`payfor_type`=3
			    	AND spd.`type`=3
			    	and ser.type=4
			    	$branch_id
		    	";
    
    	$where = " ";
    	$order = " ORDER BY ser.stu_code ASC , sp.create_date ASC ";
    
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " ser.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    
    	if(!empty($search['for_month'])){
    		$first_day = 1;
    		$last_day = 31;
    		$year=$search['for_year'];
    		$for_month = $search['for_month'];
    		 
    		$end = $year.'-'.$for_month.'-'.$last_day;
    		 
    		$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
    		 
    		$where .= " AND ".$to_date;
    	}
    	
    	if($search['service'] > 0){
    		$where.= " AND ser.`service_id` = ".$search['service'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND sp.`branch_id` = ".$search['branch'];
    	}
    	//echo $sql.$where.$order;exit();
    	$data = $db->fetchAll($sql.$where.$order);
    	
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	 
    	$string = "";
    	$student_id=0;
    	$i=0;
    	 
    	if(!empty($data)){
    		$string .= '<table cellpadding="8" border="1"​ style="margin:0 auto;width:100%; border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang;" >';
    		foreach ($data as $key => $rs){
    			if($rs['student_id']!=$student_id){
    				$i++;
    				if($key>0 ){
    					$string .= '<tr><td colspan="25">&nbsp;</td></tr></table>';
    					$string .= '<table cellpadding="8"​ border="1" style="margin:0 auto;width:100%; border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang ;" >';
    				}
    	
    				$string .= '<tr style=" font-size:12px; line-height:20px;  font-weight: bold;background: #c1d0f3;" align="center" >';
	    				$string .= '<td class="bor">'.$tr->translate("N_O").'</td>';
	    				$string .= '<td class="bor" colspan="1">'.$tr->translate("STUDENT_ID").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("NAME_KH").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("NAME_EN").'</td>';
	    				$string .= '<td class="bor">'.$tr->translate("SEX").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("PHONE").'</td>';
	    				$string .= '<td class="bor" colspan="4">'.$tr->translate("សេវាកម្មបច្ចុប្បន្ន").'</td>';
	    				$string .= '<td class="bor" colspan="3">'.$tr->translate("វេនបច្ចុប្បន្ន").'</td>';
    				$string .= '</tr>';
    	
    				$style = 'style="width:12px;"';
    				$img_src = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/Green_tick.png";
    				$current_time_for_car = $rs['current_time_for_car'];
    				$current_time_id = explode(',', $current_time_for_car);
    				
    				$current_time1="";
    				$current_time2="";
    				$current_time3="";
    				$current_time4="";
    				 
    				if(!empty($current_time_id)){foreach ($current_time_id as $id){if($id==1){$current_time1 = "<img ".$style." src='".$img_src."' />" ;}}}
    				if(!empty($current_time_id)){foreach ($current_time_id as $id){if($id==2){$current_time2 = "<img ".$style." src='".$img_src."' />" ;}}}
    				if(!empty($current_time_id)){foreach ($current_time_id as $id){if($id==3){$current_time3 = "<img ".$style." src='".$img_src."' />" ;}}}
    				if(!empty($current_time_id)){foreach ($current_time_id as $id){if($id==4){$current_time4 = "<img ".$style." src='".$img_src."' />" ;}}}
    				
    				$string .= '<tr style="font-size:12px; line-height:20px;font-family: Khmer OS Battambang ;background: #c1d0f3;" align="center">';
	    				$string .= '<td class="bor_r">'.$i.'</td>';
	    				$string .= '<td class="bor_r" colspan="1">'.$rs['stu_code'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['stu_khname'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['stu_enname'].'</td>';
	    				$string .= '<td class="bor_r" >'.$rs['sex'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['tel'].'</td>';
	    				$string .= '<td class="bor_r" colspan="4">'.$rs['current_service_name'].'</td>';
	    				$string .= '<td class="bor_r" colspan="3">';
		    				$string .= '<table width="100%" border="0" style="border-collapse: collapse;">';
			    				$string .= '<tr align="center">';
				    				$string .= '<td class="bor_r" width="25%">'.$current_time1.'</td>';
				    				$string .= '<td class="bor_r" width="25%">'.$current_time2.'</td>';
				    				$string .= '<td class="bor_r" width="25%">'.$current_time3.'</td>';
				    				$string .= '<td class="bor_r" width="25%">'.$current_time4.'</td>';
			    				$string .= '</tr>';
		    				$string .= '</table>';
	    				$string .= '</td>';
    				$string .= '</tr>';
    	
    				$string .= '<tr id="row" align="center" style="background: #f0f07d; line-height:16px;">';
	    				$string .= '<td rowspan="2" class="padd">លេខ<br />រថយន្ត</td>';
	    				$string .= '<td rowspan="2" class="padd">អាស័យដ្ឋាន</td>';
	    				$string .= '<td rowspan="2" class="padd">សិស្សថ្មី</td>';
	    				$string .= '<td colspan="4" class="padd">វេន</td>';
	    				$string .= '<td colspan="8" class="padd">ការទូទាត់សេវាកម្មជិះរថយន្ត</td>';
	    				$string .= '<td rowspan="2" class="padd">ថ្លៃផ្សេងៗ</td>';
	    				$string .= '<td rowspan="2" class="padd">ជំពាក់</td>';
	    				$string .= '<td rowspan="2" class="padd">ទឹកប្រាក់<br />សរុបរួម</td>';
	    				$string .= '<td rowspan="2" class="padd">សម្គាល់</td>';
	    				$string .= '<td rowspan="2" class="padd">សិស្សឈប់</td>';
	    				$string .= '<td rowspan="2" class="padd">ថ្ងៃឈប់</td>';
	    				$string .= '<td rowspan="2" class="padd">ស្ថានការណ៍</td>';
    				$string .= '</tr>';
    	
    				$string .= '<tr id="row" align="center" style="background: #e1e29b; line-height: 14px;">';
	    				$string .= '<td class="padd">7:30am</td>';
	    				$string .= '<td class="padd">10:45am</td>';
	    				$string .= '<td class="padd">1:30pm</td>';
	    				$string .= '<td class="padd">4:45pm</td>';
	    				$string .= '<td class="padd">ថ្ងៃបង់ប្រាក់</td>';
	    				$string .= '<td class="padd">លេខបង្កាន់ដៃ</td>';
	    				$string .= '<td class="padd">ទឹកប្រាក់</td>';
	    				$string .= '<td class="padd">បញ្ចុះតម្លៃ</td>';
	    				$string .= '<td class="padd">ទឹកប្រាក់សរុប</td>';
	    				$string .= '<td class="padd">ថ្ងៃចាប់ផ្តើម</td>';
	    				$string .= '<td class="padd">ថ្ងៃផុតកំណត់</td>';
	    				$string .= '<td class="padd">រយះពេល</td>';
    				$string .= '</tr>';
    			}
    			 
    			$student_id=$rs['student_id'];
    			 
    			$amount_month = '';
    			$is_new = '';
    			$color = '';
    			$discount = '';
    			$other_fee = '';
    			$grand_total_balance = '';
    			$suspend_type = '';
    			$date_stop = '';
    			$void_status = '';
    			 
    			 
    			if($rs['payment_term']==1){
    				$amount_month=$rs['qty']." months";
    			}else if($rs['payment_term']==2){
    				$amount_month="3 months";
    			}else if($rs['payment_term']==3){
    				$amount_month="6 months";
    			}else if($rs['payment_term']==4){
    				$amount_month="12 months";
    			}else if($rs['payment_term']==5){
    				$amount_month=$rs['qty']." days";
    			}
    			 
    			if($rs['is_new']==1){
    				$is_new = date('d-m-Y',strtotime($rs['create_date']));
    			}
    			if($rs["is_subspend"]>0){
    				$color = "style='color:red'";
    			}
    			 
    			if($rs['discount_percent']>0 && $rs['discount_fix']>0){
    				$discount = $rs['discount_percent']." %"." + $".$rs['discount_fix'];
    			}else if($rs['discount_percent']>0){
    				$discount = $rs['discount_percent']." %";
    			}else if($rs['discount_fix']>0){
    				$discount = "$ ".$rs['discount_fix'];
    			}
    			 
    			if($rs['other_fee']>0){
    				$other_fee = "$ ".$rs['other_fee'];
    			}
    			if($rs['grand_total_balance']>0){
    				$grand_total_balance = "$ ".$rs['grand_total_balance'];
    			}
    			if($rs['is_subspend']>0){
    				$suspend_type = $rs['suspend_type'];
    			}
    			if($rs['is_subspend']>0){
    				$date_stop = date("d-m-Y",strtotime($rs['date_stop']));
    			}
    			if($rs['is_void']==1){
    				$void_status = $rs['void_status'];
    			}
    			
    			$style = 'style="width:12px;"';
    			$img_src = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/Green_tick.png";
    			$time_for_car = $rs['time_for_car'];
    			$time_id = explode(',', $time_for_car);

    			$time1="";
    			$time2="";
    			$time3="";
    			$time4="";
    			
    			if(!empty($time_id)){foreach ($time_id as $id){if($id==1){$time1 = "<img ".$style." src='".$img_src."' />" ;}}}
    			if(!empty($time_id)){foreach ($time_id as $id){if($id==2){$time2 = "<img ".$style." src='".$img_src."' />" ;}}}
    			if(!empty($time_id)){foreach ($time_id as $id){if($id==3){$time3 = "<img ".$style." src='".$img_src."' />" ;}}}
    			if(!empty($time_id)){foreach ($time_id as $id){if($id==4){$time4 = "<img ".$style." src='".$img_src."' />" ;}}}
    			
    			
    			$string .= '<tr id="row" align="center" class="hover" '.$color.'>';
	    			$string .= '<td class="padd">'.$rs['car_id'].'</td>';
	    			$string .= '<td class="padd">'.$rs['service_name'].'</td>';
	    			$string .= '<td class="padd">'.$is_new.'</td>';
	    			
	    			$string .= '<td class="padd">'.$time1.'</td>';
	    			$string .= '<td class="padd">'.$time2.'</td>';
	    			$string .= '<td class="padd">'.$time3.'</td>';
	    			$string .= '<td class="padd">'.$time4.'</td>';
	    			
	    			$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['create_date'])).'</td>';
	    			$string .= '<td class="padd">'.$rs['receipt_number'].'</td>';
	    			$string .= '<td class="padd">'.' $ '.$rs['tuition_fee'].'</td>';
	    			$string .= '<td class="padd">'.$discount.'</td>';
	    			$string .= '<td class="padd">'.' $ '.$rs['tuition_fee_after_discount'].'</td>';
	    			$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['start_date'])).'</td>';
	    			$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['validate'])).'</td>';
	    			$string .= '<td class="padd">'.$amount_month.'</td>';
	    			$string .= '<td class="padd">'.$other_fee.'</td>';
	    			$string .= '<td class="padd">'.$grand_total_balance.'</td>';
	    			$string .= '<td class="padd">'.' $ '.$rs['grand_total_payment'].'</td>';
	    			$string .= '<td class="padd">'.$rs['note'].'</td>';
	    			$string .= '<td class="padd">'.$suspend_type.'</td>';
	    			$string .= '<td class="padd">'.$date_stop.'</td>';
	    			$string .= '<td class="padd">'.$void_status."(".$rs['is_start'].")".'</td>';
    			$string .= '</tr>';
    		}
    		$string .= '</table>';
    	}
    	
    	//echo $string;exit();
    	return $string;
    }
    
    
    public function getAllStayAndLunchPaymentList($search){
    	$db = $this->getAdapter();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.`branch_id`");
    
    	$sql = "SELECT
			    	sp.*,
			    	sp.id,
			    	
			    	(select branch_namekh from rms_branch where br_id = sp.branch_id limit 1) as branch_name,
			    	(select last_name from rms_users as u where u.id = sp.user_id limit 1) as user_name,
			    	
			    	sp.`student_id`,
			    	ser.stu_code as stu_code ,
			    	st.`stu_enname`,
			    	st.`stu_khname`,
			    	(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code = st.`sex` limit 1) AS sex,
			    	st.`tel`,
			    
			    	(select title from rms_program_name where rms_program_name.service_id = spd.service_id limit 1) as service_name,
			    	
			    	(select title from rms_program_name where rms_program_name.service_id = ser.service_id limit 1) as current_service_name,
			    
			    	sp.`create_date`,
			    	sp.`is_new`,
			    	sp.`receipt_number`,
			    	 
			    	sp.`tuition_fee`,
			    	sp.`discount_percent`,
			    	sp.`discount_fix`,
			    	sp.`total_payment`,
			    	
			    	sp.`material_fee`,
			    	 
			    	sp.`grand_total_payment`,
			    	sp.`grand_total_paid_amount`,
			    	sp.`grand_total_balance`,
			    	sp.`note`,
			    	sp.time_for_car,
			    	sp.is_subspend,
			    	(select v.name_en from rms_view as v where v.type=5 and key_code = sp.is_subspend limit 1) as suspend_type,
			    	(select date from rms_student_drop where stu_id = sp.student_id and drop_from = 3 order by id DESC limit 1) as date_stop,
			    	 
			    	spd.service_id,
			    	spd.`start_date`,
			    	spd.`validate`,
			    	spd.qty,
			    	spd.`payment_term`,
			    	spd.`is_start`,
			    	(select name_en from rms_view where type=12 and key_code=sp.is_void limit 1) as void_status
			    FROM
			    	`rms_student_payment` AS sp,
			    	`rms_student_paymentdetail` AS spd,
			    	`rms_student` AS st,
			    	rms_service as ser
			    WHERE
			    	sp.id=spd.`payment_id`
			    	AND st.`stu_id`=sp.`student_id`
			    	and sp.`student_id` = ser.stu_id
			    	AND sp.`payfor_type`=4
			    	AND ser.`type`=5
			    	$branch_id
    		";
    
    	$where = " ";
    	
    	$order = " ORDER BY ser.stu_code ASC , sp.create_date ASC ";
    
    
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
	    	$s_where[] = " ser.stu_code LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
	    	$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
	    	$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    
    	if(!empty($search['for_month'])){
    		$first_day = 1;
    		$last_day = 31;
    		$year = $search['for_year'];
    		$for_month = $search['for_month'];
    		 
    		$end = $year.'-'.$for_month.'-'.$last_day;
    		 
    		$to_date = (empty($end))? '1': "sp.create_date <= '".$end." 23:59:59'";
    		 
    		$where .= " AND ".$to_date;
    	}
    	
    	if($search['service'] > 0){
    		$where.= " AND ser.`service_id` = ".$search['service'];
    	}
	    if($search['branch'] > 0){
	    	$where.= " AND sp.`branch_id` = ".$search['branch'];
	    }
	     
	    //echo $sql.$where.$order;exit();
	    $data = $db->fetchAll($sql.$where.$order);
    
	    $tr = Application_Form_FrmLanguages::getCurrentlanguage();
	    
	    $string = "";
	    $student_id=0;
	    $i=0;
	    
	    if(!empty($data)){
	    	$string .= '<table cellpadding="8" border="1"​ style="margin:0 auto;width:100%; border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang;" >';
	    	foreach ($data as $key => $rs){
	    		if($rs['student_id']!=$student_id){
	    			$i++;
	    			if($key>0 ){
	    				$string .= '<tr><td colspan="25">&nbsp;</td></tr></table>';
	    				$string .= '<table cellpadding="8"​ border="1" style="margin:0 auto;width:100%; border-collapse: collapse;white-space: nowrap;font-family: Khmer OS Battambang ;" >';
	    			}
	    			 
	    			$string .= '<tr style=" font-size:12px; line-height:20px;  font-weight: bold;background: #c1d0f3;" align="center" >';
		    			$string .= '<td class="bor">'.$tr->translate("N_O").'</td>';
		    			$string .= '<td class="bor" colspan="2">'.$tr->translate("STUDENT_ID").'</td>';
		    			$string .= '<td class="bor" colspan="4">'.$tr->translate("NAME_KH").'</td>';
		    			$string .= '<td class="bor" colspan="4">'.$tr->translate("NAME_EN").'</td>';
		    			$string .= '<td class="bor">'.$tr->translate("SEX").'</td>';
		    			$string .= '<td class="bor" colspan="4">'.$tr->translate("PHONE").'</td>';
		    			$string .= '<td class="bor" colspan="2">'.$tr->translate("សេវាកម្មបច្ចុប្បន្ន").'</td>';
	    			$string .= '</tr>';
	    			 
	    			$string .= '<tr style="font-size:12px; line-height:20px;font-family: Khmer OS Battambang ;background: #c1d0f3;" align="center">';
		    			$string .= '<td class="bor_r">'.$i.'</td>';
		    			$string .= '<td class="bor_r" colspan="2">'.$rs['stu_code'].'</td>';
		    			$string .= '<td class="bor_r" colspan="4">'.$rs['stu_khname'].'</td>';
		    			$string .= '<td class="bor_r" colspan="4">'.$rs['stu_enname'].'</td>';
		    			$string .= '<td class="bor_r" >'.$rs['sex'].'</td>';
		    			$string .= '<td class="bor_r" colspan="4">'.$rs['tel'].'</td>';
		    			$string .= '<td class="bor_r" colspan="2">'.$rs['current_service_name'].'</td>';
	    			$string .= '</tr>';
	    			 
	    			$string .= '<tr id="row" align="center" style="background: #f0f07d; line-height:16px;">';
		    			$string .= '<td rowspan="2" class="padd">សិស្សថ្មី</td>';
		    			$string .= '<td colspan="2" class="padd">ប្រភេទសេវា</td>';
		    			$string .= '<td colspan="8" class="padd">ការទូទាត់សេវាកម្មអាហារ និង ស្នាក់នៅ</td>';
		    			$string .= '<td rowspan="2" class="padd">ថ្លៃផ្សេងៗ</td>';
		    			$string .= '<td rowspan="2" class="padd">ជំពាក់</td>';
		    			$string .= '<td rowspan="2" class="padd">ទឹកប្រាក់<br />សរុបរួម</td>';
		    			$string .= '<td rowspan="2" class="padd">សម្គាល់</td>';
		    			$string .= '<td rowspan="2" class="padd">សិស្សឈប់</td>';
		    			$string .= '<td rowspan="2" class="padd">ថ្ងៃឈប់</td>';
		    			$string .= '<td rowspan="2" class="padd">ស្ថានការណ៍</td>';
	    			$string .= '</tr>';
	    			 
	    			$string .= '<tr id="row" align="center" style="background: #e1e29b; line-height: 14px;">';
		    			$string .= '<td class="padd">ញ៉ាំ</td>';
		    			$string .= '<td class="padd">ស្នាក់នៅ</td>';
		    			$string .= '<td class="padd">ថ្ងៃបង់ប្រាក់</td>';
		    			$string .= '<td class="padd">លេខបង្កាន់ដៃ</td>';
		    			$string .= '<td class="padd">ទឹកប្រាក់</td>';
		    			$string .= '<td class="padd">បញ្ចុះតម្លៃ</td>';
		    			$string .= '<td class="padd">ទឹកប្រាក់សរុប</td>';
		    			$string .= '<td class="padd">ថ្ងៃចាប់ផ្តើម</td>';
		    			$string .= '<td class="padd">ថ្ងៃផុតកំណត់</td>';
		    			$string .= '<td class="padd">រយះពេល</td>';
	    			$string .= '</tr>';
	    		}
	    
	    		$student_id=$rs['student_id'];
	    
	    		$amount_month = '';
	    		$is_new = '';
	    		$color = '';
	    		$discount = '';
	    		$other_fee = '';
	    		$grand_total_balance = '';
	    		$suspend_type = '';
	    		$date_stop = '';
	    		$void_status = '';
	    
	    
	    		if($rs['payment_term']==1){
	    			$amount_month=$rs['qty']." months";
	    		}else if($rs['payment_term']==2){
	    			$amount_month="3 months";
	    		}else if($rs['payment_term']==3){
	    			$amount_month="6 months";
	    		}else if($rs['payment_term']==4){
	    			$amount_month="12 months";
	    		}else if($rs['payment_term']==5){
	    			$amount_month=$rs['qty']." days";
	    		}
	    
	    		if($rs['is_new']==1){
	    			$is_new = date('d-m-Y',strtotime($rs['create_date']));
	    		}
	    		if($rs["is_subspend"]>0){
	    			$color = "style='color:red'";
	    		}
	    
	    		if($rs['discount_percent']>0 && $rs['discount_fix']>0){
	    			$discount = $rs['discount_percent']." %"." + $".$rs['discount_fix'];
	    		}else if($rs['discount_percent']>0){
	    			$discount = $rs['discount_percent']." %";
	    		}else if($rs['discount_fix']>0){
	    			$discount = "$ ".$rs['discount_fix'];
	    		}
	    
	    		if($rs['other_fee']>0){
	    			$other_fee = "$ ".$rs['other_fee'];
	    		}
	    		if($rs['grand_total_balance']>0){
	    			$grand_total_balance = "$ ".$rs['grand_total_balance'];
	    		}
	    		if($rs['is_subspend']>0){
	    			$suspend_type = $rs['suspend_type'];
	    		}
	    		if($rs['is_subspend']>0){
	    			$date_stop = date("d-m-Y",strtotime($rs['date_stop']));
	    		}
	    		if($rs['is_void']==1){
	    			$void_status = $rs['void_status'];
	    		}
	    		 
	    		$food="";
	    		$stay="";
	    		
	    		$style = 'style="width:12px;"';
	    		$img_src = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/Green_tick.png";
	    		 
	    		if($rs['service_id']==91 || $rs['service_id']==93){
	    			$food = "<img ".$style." src='".$img_src."' />" ;
	    		}
	    		if($rs['service_id']==92 || $rs['service_id']==93){
	    			$stay = "<img ".$style." src='".$img_src."' />" ;
	    		}
	    		 
	    		 
	    		$string .= '<tr id="row" align="center" class="hover" '.$color.'>';
		    		$string .= '<td class="padd">'.$is_new.'</td>';
		    
		    		$string .= '<td class="padd">'.$food.'</td>';
		    		$string .= '<td class="padd">'.$stay.'</td>';
		    
		    		$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['create_date'])).'</td>';
		    		$string .= '<td class="padd">'.$rs['receipt_number'].'</td>';
		    		$string .= '<td class="padd">'.' $ '.$rs['tuition_fee'].'</td>';
		    		$string .= '<td class="padd">'.$discount.'</td>';
		    		$string .= '<td class="padd">'.' $ '.$rs['tuition_fee_after_discount'].'</td>';
		    		$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['start_date'])).'</td>';
		    		$string .= '<td class="padd">'.date('d-m-Y',strtotime($rs['validate'])).'</td>';
		    		$string .= '<td class="padd">'.$amount_month.'</td>';
		    		$string .= '<td class="padd">'.$other_fee.'</td>';
		    		$string .= '<td class="padd">'.$grand_total_balance.'</td>';
		    		$string .= '<td class="padd">'.' $ '.$rs['grand_total_payment'].'</td>';
		    		$string .= '<td class="padd">'.$rs['note'].'</td>';
		    		$string .= '<td class="padd">'.$suspend_type.'</td>';
		    		$string .= '<td class="padd">'.$date_stop.'</td>';
		    		$string .= '<td class="padd">'.$void_status."(".$rs['is_start'].")".'</td>';
	    		$string .= '</tr>';
	    	}
	    	$string .= '</table>';
	    }
	    
	    //echo $string;exit();
	    return $string;
	    
	    
    }
    
    
    function getAllGrade(){
    	$db = $this->getAdapter();
    	$sql="select major_id as id, CONCAT(major_enname,' (',(select shortcut from rms_dept as d where d.dept_id = m.dept_id),')') as name from rms_major as m where is_active = 1  ";
    	return $db->fetchAll($sql);
    }
    
    function getAllSession(){
    	$db = $this->getAdapter();
    	$sql="select key_code as id, name_en as name from rms_view where type = 4 and status = 1 and key_code IN(1,2,3) ";
    	return $db->fetchAll($sql);
    }
    
    function getAllRoom(){
    	$db = $this->getAdapter();
    	$sql="select room_id as id, room_name as name from rms_room where is_active = 1  ";
    	return $db->fetchAll($sql);
    }
    
    function getAllMonth(){
    	$db = $this->getAdapter();
    	$sql="select id, month_kh as name from rms_month where status = 1  ";
    	return $db->fetchAll($sql);
    }
    
    function getAllDegree(){
    	$db = $this->getAdapter();
    	$sql="select dept_id as id, en_name as name from rms_dept where is_active = 1 and type = 2 and en_name != '' ";
    	return $db->fetchAll($sql);
    }
    
    
    
    
}







