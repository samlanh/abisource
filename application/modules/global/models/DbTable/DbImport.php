<?php

class Global_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'ldc_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    
    function getExistStudentByStudentID($stu_code,$stu_khname){
    	$db = $this->getAdapter();
    	$sql="select stu_id from rms_student where stu_code = '$stu_code' and stu_khname='$stu_khname' ";
    	return $db->fetchOne($sql);
    }
    
    function getExistGradeByName($grade_name){
    	if(!empty($grade_name)){
	    	$db = $this->getAdapter();
	    	$sql="select major_id from rms_major where major_enname = '$grade_name' ";
	    	return $db->fetchOne($sql);
    	}else{
    		return 0;
    	}
    }
    
    function getExistRoomByName($room_name){
    	if(!empty($room_name)){
	    	$db = $this->getAdapter();
	    	$sql="select room_id from rms_room where room_name = '$room_name' ";
	    	return $db->fetchOne($sql);
    	}else{
    		return -1;
    	}
    }

//////////////////////////////////////////////////////////// english FT //////////////////////////////////////////////////////////////////////    
    
    public function updateItemsByImportEFT($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	//print_r($data);exit();
    	$payment_id = 0;
    	$branch_id = 2;
    	try{
	    	for($i=3; $i<=$count; $i++){
	    		
	    		if(empty($data[$i]['K']) && empty($data[$i]['M']) && empty($data[$i]['Z'])){
	    			continue;
	    		}
	    		
		    	if($data[$i]['I']=="Mor"){
		    		$session = 1;
		    	}else{
		    		$session = 2;
		    	}	
		    	
		    	$date = $data[$i]['K'];//exit();
		    	$date = str_replace("/", "-", $date);
		    	$create_date = date("Y-m-d",strtotime($date));
		    	//echo $date;exit();
		    	
		    	$start = $data[$i]['P'];//exit();
		    	$start = str_replace("/", "-", $start);
		    	$start_date = date("Y-m-d",strtotime($start));
		    	
		    	$end = $data[$i]['Q'];//exit();
		    	$end = str_replace("/", "-", $end);
		    	$end_date = date("Y-m-d",strtotime($end));
	    		
	    /////////////////////////////////// exist grade //////////////////////////////////////	
	
		    	$grade_id = $this->getExistGradeByName($data[$i]['G']); // level => ពេលបង់លុយ
		    	if(empty($grade_id)){
		    		$arr = array(
		    				'major_enname'=>$data[$i]['G'],
		    				'modify_date'=>date("Y-m-d"),
		    				'is_active'=>1,
		    				'user_id'=>1,
		    		);
		    		$this->_name='rms_major';
		    		$grade_id = $this->insert($arr);
		    	}
		    	
	    		$current_grade_id = $this->getExistGradeByName($data[$i]['F']); // level(shortcut) => កំពុងរៀន
	    		if(empty($current_grade_id)){
	    			$arr = array(
	    					'major_enname'=>$data[$i]['F'],
	    					'modify_date'=>date("Y-m-d"),
	    					'is_active'=>1,
	    					'user_id'=>1,
	    			);
	    			$this->_name='rms_major';
	    			$current_grade_id = $this->insert($arr);
	    		}
	    /////////////////////////////////// exist room //////////////////////////////////////
	    		$room_id = $this->getExistRoomByName($data[$i]['H']);
	    		if(empty($room_id)){
	    			$arr = array(
	    					'room_name'=>$data[$i]['H'],
	    					'modify_date'=>date("Y-m-d"),
	    					'is_active'=>1,
	    					'user_id'=>1,
	    			);
	    			$this->_name='rms_room';
	    			$room_id = $this->insert($arr);
	    		}
	    /////////////////////////////////// exist student //////////////////////////////////////
	    		
	    		
	    		if(!empty($data[$i]['AE'])){
	    			$is_suspend=2;
	    		}else{
	    			$is_suspend=0;
	    		}
	    		
		    	$mystring = $data[$i]['G']; // level => ពេលបង់លុយ
	    		if (strpos($mystring, 'Level') !== false) {
	    			$degree=5;
	    		}else{
	    			$degree=4;
	    		}
	    		
	    		$mystring1 = $data[$i]['F']; // level(shortcut) => កំពុងរៀន
	    		if (strpos($mystring1, 'Level') !== false) {
	    			$current_degree=5;
	    		}else{
	    			$current_degree=4;
	    		}
	    		
	    		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
	    		if(empty($stu_id)){
	    			$this->_name='rms_student';
	    			$arr = array(
		    				'branch_id'=>$branch_id,
	    					'stu_type'=>2,// Eng FT
		    				'user_id'=>1,
		    				'stu_code'=>$data[$i]['B'],
		    				'stu_khname'=>$data[$i]['C'],
		    				'stu_enname'=>$data[$i]['D'],
		    				'sex'=>($data[$i]['E']=="M")?1:2,
	    					'degree'=>$current_degree, // Eng FT
		    				'grade'=>$current_grade_id,
		    				'room'=>$room_id,
		    				'session'=>$session,
	    					
	    					'is_subspend'=>$is_suspend,
	    					
	    					'is_stu_new'=>(empty($data[$i]['J']))?0:1,
	    					
		    				'tel'=>$data[$i]['AC'],
	    					'create_date'=>$create_date,
		    		);
		    		$stu_id = $this->insert($arr);
		    		
		    		$this->_name='rms_student_id';
		    		$arr1 = array(
		    				'branch_id'=>$branch_id,
		    				'stu_id'=>$stu_id,
		    				'stu_type'=>2,// Eng FT
		    				'degree'=>$current_degree, // Eng FT
		    				'status'=>1,
		    				'is_finish'=>0,
		    				'is_parent'=>null,
		    		);
		    		$this->insert($arr1);
	    		}
	    		
	    		if(!empty($data[$i]['AE'])){
	    			$this->_name='rms_student_drop';
		    		$arr2 = array(
		    				'type'=>2,// drop
		    				'stu_id'=>$stu_id,
		    				'status'=>1,
		    				'date'=>date("Y-m-d"),
		    				'reason'=>$data[$i]['AD'],
		    				'user_id'=>1,
		    				'drop_from'=>1, // student drop
		    		);
		    		$this->insert($arr2);
	    		}
	    		
	    		$qty=1;
	    		$qty_day=0;
	    		$mystring = $data[$i]['R'];
	    		$findme   = 'day';
	    		$pos = strpos($mystring, $findme);
	    		if(!empty($pos)){
	    			$payment_term = 5;
	    			$qty_day = str_replace("day", "", $data[$i]['R']);
	    			$qty = $qty_day;
	    		}else if($data[$i]['R']==3){
	    			$payment_term = 2;//
	    		}else if($data[$i]['R']==6){
	    			$payment_term = 3;
	    		}else if($data[$i]['R']==12){
	    			$payment_term = 4;
	    		}else{
	    			$payment_term = 1;
	    			$qty = $data[$i]['R'];
	    		}
	    		
	    		
	    	$this->_name = 'rms_student_payment';
	    		$array = array(
		    				'branch_id'=>$branch_id,
			    			'student_id'=>$stu_id,
			    			'receipt_number'=>$data[$i]['L'],
	    					'degree'=>$degree, // Eng FT
			    			'grade'=>$grade_id,
			    			'session'=>$session,
			    			'room_id'=>$room_id,
	    				
		    				'payment_term'=>$payment_term,
	    					'amount_sec'=>$qty_day,
	    				
		    				'exchange_rate'=>4100,
		    				'tuition_fee'=>$data[$i]['M'],
		    				'discount_percent'=>$data[$i]['N'],
		    				'tuition_fee_after_discount'=>$data[$i]['O'],
		    				
		    				'material_fee'=>$data[$i]['V'],
		    				'admin_fee'=>$data[$i]['S'],
		    				
		    				'total_payment'=>$data[$i]['Z'],
		    				'receive_amount'=>$data[$i]['Z'],
		    				'paid_amount'=>$data[$i]['Z'],
		    				'balance_due'=>$data[$i]['Y'],
		    				
		    				'payfor_type'=>6, // Eng FT
		    				
		    				'grand_total_payment'=>$data[$i]['Z'],
		    				'grand_total_payment_in_riel'=>$data[$i]['Z'] * 4100,
		    				'grand_total_paid_amount'=>$data[$i]['Z'],
		    				'grand_total_balance'=>$data[$i]['Y'],
		    				
		    				'note'=>$data[$i]['AB'],
		    				'user_id'=>1,
		    				'create_date'=>$create_date,
	    				
	    					'is_subspend'=>$is_suspend,
		    				'is_new'=>(empty($data[$i]['J']))?0:1,
		    				
		    				'student_type'=>3,//old student
	    				);
	    	//$payment_id = $this->insert($array);
	    	
	    	$this->insert($array);
	    	$payment_id++;
	    	
	    	$this->_name = 'rms_student_paymentdetail';
	    	
		    	$is_start = 1;
	    	
	    		$array1 = array(
	    				'payment_id'=>$payment_id,
	    				'type'=>1,
	    				'service_id'=>4,
	    				'payment_term'=>$payment_term,
	    				 
	    				'fee'=>$data[$i]['M'],
	    				'qty'=>$qty,
	    				
	    				'discount_percent'=>$data[$i]['N'],
	    				 
	    				'material_fee'=>$data[$i]['V'],
	    				'admin_fee'=>$data[$i]['S'],
	    				 
	    				'subtotal'=>$data[$i]['Z'],
	    				'paidamount'=>$data[$i]['Z'],
	    				'balance'=>$data[$i]['Y'],
	    				
	    				'start_date'=>$start_date,
	    				'validate'=>$end_date,
	    				 
	    				'note'=>$data[$i]['AB'],
	    				'user_id'=>1,
	    				'is_complete'=>1,
	    				'comment'=>'បង់រួច',
	    				 
	    				'is_start'=>$is_start,
	    		);
	    		$this->insert($array1);
	    	}
    	}catch(Exception $e){
    		echo $e->getMessage();exit();
    	}
    }
    
    ///////////////////////////////////////////////////////////// english PT //////////////////////////////////////////////////////////////////////////////
    
    public function updateItemsByImportEPT($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	//print_r($data);exit();
    	
    	$payment_id = 938;
    	$branch_id = 2;
    	
    	for($i=3; $i<=$count; $i++){
    		if(empty($data[$i]['K']) && empty($data[$i]['M']) && empty($data[$i]['S'])){
    			continue;
    		}
    
    		$date = $data[$i]['K'];//exit();
    		$date = str_replace("/", "-", $date);
    		$create_date = date("Y-m-d",strtotime($date));
    		//echo $date;exit();
    
    		$start = $data[$i]['O'];//exit();
    		$start = str_replace("/", "-", $start);
    		$start_date = date("Y-m-d",strtotime($start));
    
    		$end = $data[$i]['P'];//exit();
    		$end = str_replace("/", "-", $end);
    		$end_date = date("Y-m-d",strtotime($end));
    
    		if(!empty($data[$i]['X'])){
    			$is_suspend=2;
    		}else{
    			$is_suspend=0;
    		}
    
    		/////////////////////////////////// exist grade //////////////////////////////////////
    
    		$grade_id = $this->getExistGradeByName($data[$i]['G']); // level => ពេលបង់លុយ
	    	if(empty($grade_id)){
	    		$arr = array(
	    				'major_enname'=>$data[$i]['G'],
	    				'modify_date'=>date("Y-m-d"),
	    				'is_active'=>1,
	    				'user_id'=>1,
	    		);
	    		$this->_name='rms_major';
	    		$grade_id = $this->insert($arr);
	    	}
	    	
    		$current_grade_id = $this->getExistGradeByName($data[$i]['F']); // level(shortcut) => កំពុងរៀន
    		if(empty($current_grade_id)){
    			$arr = array(
    					'major_enname'=>$data[$i]['F'],
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_major';
    			$current_grade_id = $this->insert($arr);
    		}
    		/////////////////////////////////// exist room //////////////////////////////////////
    		$room_id = $this->getExistRoomByName($data[$i]['H']);
    		if(empty($room_id)){
    			$arr = array(
    					'room_name'=>$data[$i]['H'],
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_room';
    			$room_id = $this->insert($arr);
    		}
    		
    		/////////////////////////////////// exist student //////////////////////////////////////
    		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    		if(empty($stu_id)){
    			$this->_name='rms_student';
    			$arr = array(
    					'branch_id'=>$branch_id,
    					'stu_type'=>3, // Eng PT
    					'user_id'=>1,
    					'stu_code'=>$data[$i]['B'],
    					'stu_khname'=>$data[$i]['C'],
    					'stu_enname'=>$data[$i]['D'],
    					'sex'=>($data[$i]['E']=="M")?1:2,
    					'degree'=>6, // Eng PT
    					'grade'=>$current_grade_id,
    					'room'=>$room_id,
    					
    					'is_subspend'=>$is_suspend,
    					
    					'is_stu_new'=>(empty($data[$i]['J']))?0:1,
    					'session'=>3,// everning
    					'tel'=>$data[$i]['V'],
    					'create_date'=>$create_date,
    			);
    			$stu_id = $this->insert($arr);
    
    			$this->_name='rms_student_id';
    			$arr1 = array(
    					'branch_id'=>$branch_id,
    					'stu_id'=>$stu_id,
    					'stu_type'=>3,// Eng PT
    					'degree'=>6, // Eng PT
    					'status'=>1,
    					'is_finish'=>0,
    					'is_parent'=>null,
    			);
    			$this->insert($arr1);
    		}
    
    		if(!empty($data[$i]['X'])){
    			$this->_name='rms_student_drop';
    			$arr2 = array(
    					'type'=>2,// drop
    					'stu_id'=>$stu_id,
    					'status'=>1,
    					'date'=>date("Y-m-d"),
    					'reason'=>$data[$i]['W'],
    					'user_id'=>1,
    					'drop_from'=>1, // student drop
    			);
    			$this->insert($arr2);
    		}
    		
    		$qty=1;
    		$qty_day=0;
    		$mystring = $data[$i]['Q'];
    		$findme   = 'day';
    		$pos = strpos($mystring, $findme);
    		if(!empty($pos)){
    			$payment_term = 5;
    			$qty_day = str_replace("day", "", $data[$i]['Q']);
    			$qty = $qty_day;
    		}else if($data[$i]['Q']==3){
    			$payment_term = 2;//
    		}else if($data[$i]['Q']==6){
    			$payment_term = 3;
    		}else if($data[$i]['Q']==12){
    			$payment_term = 4;
    		}else{
    			$payment_term = 1;
    			$qty = $data[$i]['Q'];
    		}
    
    		$this->_name = 'rms_student_payment';
    		$array = array(
    				'branch_id'=>$branch_id,
    				'student_id'=>$stu_id,
    				'receipt_number'=>$data[$i]['L'],
    				'degree'=>6, // Eng PT
    				'grade'=>$grade_id,
    				'session'=>3, //evening
    				'time'=>$data[$i]['I'],
    				'room_id'=>$room_id,
    
    				'payment_term'=>$payment_term,
    				'amount_sec'=>$qty_day,
    
    				'exchange_rate'=>4100,
    				'tuition_fee'=>$data[$i]['M'],
    				'discount_percent'=>$data[$i]['N'],
    				'tuition_fee_after_discount'=>$data[$i]['S'],
    					
    				'material_fee'=>0,
    				'admin_fee'=>0,
    					
    				'total_payment'=>$data[$i]['S'],
    				'receive_amount'=>$data[$i]['S'],
    				'paid_amount'=>$data[$i]['S'],
    				'balance_due'=>$data[$i]['R'],
    					
    				'payfor_type'=>2, // Eng PT
    					
    				'grand_total_payment'=>$data[$i]['S'],
    				'grand_total_payment_in_riel'=>$data[$i]['S'] * 4100,
    				'grand_total_paid_amount'=>$data[$i]['S'],
    				'grand_total_balance'=>$data[$i]['R'],
    					
    				'is_subspend'=>$is_suspend,
    				
    				'note'=>$data[$i]['U'],
    				'user_id'=>1,
    				'create_date'=>$create_date,
    				'is_new'=>(empty($data[$i]['J']))?0:1,
    					
    				'student_type'=>3,
    		);
//     		$payment_id = $this->insert($array);
    		$this->insert($array);
    		$payment_id++;
    
    		$this->_name = 'rms_student_paymentdetail';
    		
    		$is_start = 1;
    		$array1 = array(
    				'payment_id'=>$payment_id,
    				'type'=>2, // part time
    				'service_id'=>4,
    				'payment_term'=>$payment_term,
    					
    				'fee'=>$data[$i]['M'],
    				'qty'=>$qty,
    
    				'discount_percent'=>$data[$i]['N'],
    					
    				'material_fee'=>0,
    				'admin_fee'=>0,
    					
    				'subtotal'=>$data[$i]['S'],
    				'paidamount'=>$data[$i]['S'],
    				'balance'=>$data[$i]['R'],
    
    				'start_date'=>$start_date,
    				'validate'=>$end_date,
    					
    				'note'=>$data[$i]['U'],
    				'user_id'=>1,
    				'is_complete'=>1,
    				'comment'=>'បង់រួច',
    					
    				'is_start'=>$is_start,
    		);
    		$this->insert($array1);
    	}
    }
    
    
//////////////////////////////////////////////////////////////// Khmer /////////////////////////////////////////////////////////////////////////////////////   
    
    public function updateItemsByImportKID($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	//print_r($data);exit();
    	$payment_id = 1089;
    	$branch_id = 2;
    
    	for($i=3; $i<=$count; $i++){
    
    		if(empty($data[$i]['K']) && empty($data[$i]['M']) && empty($data[$i]['Z'])){
    			continue;
    		}
    
    		$date = $data[$i]['K'];//exit();
    		$date = str_replace("/", "-", $date);
    		$create_date = date("Y-m-d",strtotime($date));
    		//echo $create_date;exit();
    		 
    		$start = $data[$i]['P'];//exit();
    		$start = str_replace("/", "-", $start);
    		$start_date = date("Y-m-d",strtotime($start));
    
    		$end = $data[$i]['Q'];//exit();
    		$end = str_replace("/", "-", $end);
    		$end_date = date("Y-m-d",strtotime($end));
    
    		if($data[$i]['I']=="ព្រឹក"){ // ព្រឹក // Mor
    			$session = 1;
    		}else{
    			$session = 2;
    		}
    
    		if(!empty($data[$i]['AE'])){
    			$is_suspend=2;
    		}else{
    			$is_suspend=0;
    		}
    		
    		/////////////////////////////////// exist grade //////////////////////////////////////
    
    		$grade_id = $this->getExistGradeByName($data[$i]['G']); // level (តាមការបង់លុយ)
    		if(empty($grade_id)){
    			$arr = array(
    					'major_enname'=>$data[$i]['G'],
    					'dept_id'=>1,
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_major';
    			$grade_id = $this->insert($arr);
    		}
    
    		$current_grade_id = $this->getExistGradeByName($data[$i]['F']); // level(shortcut) (កំពុងរៀនបច្ចុប្បន្ន)
    		if(empty($current_grade_id)){
    			$arr = array(
    					'major_enname'=>$data[$i]['F'],
    					'dept_id'=>1,
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_major';
    			$current_grade_id = $this->insert($arr);
    		}
    		/////////////////////////////////// exist room //////////////////////////////////////
    		$room_id = $this->getExistRoomByName($data[$i]['H']);
    		if(empty($room_id)){
    			$arr = array(
    					'room_name'=>$data[$i]['H'],
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_room';
    			$room_id = $this->insert($arr);
    		}
    		/////////////////////////////////// exist student //////////////////////////////////////
    		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    
    		if(empty($stu_id)){
    			$this->_name='rms_student';
    			$arr = array(
    					'branch_id'=>$branch_id,
    					'stu_type'=>1,// Khmer FT
    					'user_id'=>1,
    					'stu_code'=>$data[$i]['B'],
    					'stu_khname'=>$data[$i]['C'],
    					'stu_enname'=>$data[$i]['D'],
    					'sex'=>($data[$i]['E']=="ប")?1:2,
    					'degree'=>1,
    					'grade'=>$current_grade_id,
    					'room'=>$room_id,
    					'session'=>$session,
    					'tel'=>$data[$i]['AC'],
    						
    					'is_stu_new'=>(empty($data[$i]['J']))?0:1,
    					
    					'is_subspend'=>$is_suspend,
    						
    					'create_date'=>$create_date,
    			);
    			$stu_id = $this->insert($arr);
    
    			$this->_name='rms_student_id';
    			$arr1 = array(
    					'branch_id'=>$branch_id,
    					'stu_id'=>$stu_id,
    					'stu_type'=>1,// Khmer FT
    					'degree'=>1, // Kinder
    					'status'=>1,
    					'is_finish'=>0,
    					'is_parent'=>null,
    			);
    			$this->insert($arr1);
    		}
    
    		if(!empty($data[$i]['AE'])){
	    		$this->_name='rms_student_drop';
		    	$arr2 = array(
		    			'type'=>2,// drop
		    			'stu_id'=>$stu_id,
		    			'status'=>1,
		    			'date'=>date("Y-m-d"),
		    			'reason'=>$data[$i]['AD'],
		    			'user_id'=>1,
		    			'drop_from'=>1, // student drop
		    	);
		    	$this->insert($arr2);
	    	}
    
   			$qty=1;
  			$qty_day=0;
   			$mystring = $data[$i]['R'];
   			$findme   = 'day';
   			$pos = strpos($mystring, $findme);
   			if(!empty($pos)){
   				$payment_term = 5;
   				$qty_day = str_replace("day", "", $data[$i]['R']);
   				$qty = $qty_day;
   			}else if($data[$i]['R']==3){
   				$payment_term = 2;//
   			}else if($data[$i]['R']==6){
   				$payment_term = 3;
   			}else if($data[$i]['R']==12){
   				$payment_term = 4;
   			}else{
   				$payment_term = 1;
   				$qty = $data[$i]['R'];
   			}
   			 
   			$this->_name = 'rms_student_payment';
   			$_arr = array(
   					'branch_id'=>$branch_id,
   					'student_id'=>$stu_id,
   					'receipt_number'=>$data[$i]['L'],
   					'degree'=>1,
   					'grade'=>$grade_id,
   					'session'=>$session,
   					'room_id'=>$room_id,
   					 
   					'payment_term'=>$payment_term,
   					'amount_sec'=>$qty_day,
   					 
   					'exchange_rate'=>4100,
   					'tuition_fee'=>$data[$i]['M'],
   					'discount_percent'=>$data[$i]['N'],
   					'tuition_fee_after_discount'=>$data[$i]['O'],
   
   					'material_fee'=>$data[$i]['V'],
   					'admin_fee'=>$data[$i]['S'],
   
   					'total_payment'=>$data[$i]['Z'],
   					'receive_amount'=>$data[$i]['Z'],
   					'paid_amount'=>$data[$i]['Z'],
   					'balance_due'=>$data[$i]['Y'],
   
   					'payfor_type'=>1,
   
   					'grand_total_payment'=>$data[$i]['Z'],
   					'grand_total_payment_in_riel'=>$data[$i]['Z'] * 4100,
   					'grand_total_paid_amount'=>$data[$i]['Z'],
   					'grand_total_balance'=>$data[$i]['Y'],
   
   					'note'=>$data[$i]['AB'],
   					'user_id'=>1,
   					'create_date'=>$create_date,
   					'is_new'=>(empty($data[$i]['J']))?0:1,
   					 
   					'is_subspend'=>$is_suspend,
   
   					'student_type'=>3,
   			);
   			//$payment_id = $this->insert($_arr);
   			$this->insert($_arr);
   			$payment_id++;
   	   
   			$is_start = 1;
   	   
   			$_arr2 = array(
   					'payment_id'=>$payment_id,
   					'type'=>1,
   					'service_id'=>4,
   					'payment_term'=>$payment_term,
   
   					'fee'=>$data[$i]['M'],
   					'qty'=>$qty,
   					 
   					'discount_percent'=>$data[$i]['N'],
   
   					'material_fee'=>$data[$i]['V'],
   					'admin_fee'=>$data[$i]['S'],
    
   					'subtotal'=>$data[$i]['Z'],
   					'paidamount'=>$data[$i]['Z'],
   					'balance'=>$data[$i]['Y'],
   					 
   					'start_date'=>$start_date,
   					'validate'=>$end_date,
   
   					'note'=>$data[$i]['AB'],
   					'user_id'=>1,
   					'is_complete'=>1,
   					'comment'=>'បង់រួច',
   
   					'is_start'=>$is_start,
   			);
   			$this->_name ='rms_student_paymentdetail';
   			$this->insert($_arr2);
   			//exit();
    		
    	}
    }
    
     
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    
    public function updateItemsByImportPrimary($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	//print_r($data);exit();
    	$payment_id = 1488;
    	$branch_id = 2;
    	 
    	for($i=3; $i<=$count; $i++){
    
    		if(empty($data[$i]['K']) && empty($data[$i]['M']) && empty($data[$i]['Z'])){
    			continue;
    		}
    
    		$date = $data[$i]['K'];//exit();
    		$date = str_replace("/", "-", $date);
    		$create_date = date("Y-m-d",strtotime($date));
    		//echo $create_date;exit();
    		 
    		$start = $data[$i]['P'];//exit();
    		$start = str_replace("/", "-", $start);
    		$start_date = date("Y-m-d",strtotime($start));
    
    		$end = $data[$i]['Q'];//exit();
    		$end = str_replace("/", "-", $end);
    		$end_date = date("Y-m-d",strtotime($end));
    
    		if($data[$i]['I']=="ព្រឹក"){ // Mor // ព្រឹក
    			$session = 1;
    		}else{
    			$session = 2;
    		}
    
    		if(!empty($data[$i]['AE'])){
    			$is_suspend=2;
    		}else{
    			$is_suspend=0;
    		}
    		
    		/////////////////////////////////// exist grade //////////////////////////////////////
    
    		$grade_id = $this->getExistGradeByName($data[$i]['G']); // level (តាមការបង់លុយ)
    		if(empty($grade_id)){
    			$arr = array(
    					'major_enname'=>$data[$i]['G'],
    					'dept_id'=>2,
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_major';
    			$grade_id = $this->insert($arr);
    		}
    
    		$current_grade_id = $this->getExistGradeByName($data[$i]['F']); // level(shortcut) (កំពុងរៀនបច្ចុប្បន្ន)
    		if(empty($current_grade_id)){
    			$arr = array(
    					'major_enname'=>$data[$i]['F'],
    					'dept_id'=>2,
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_major';
    			$current_grade_id = $this->insert($arr);
    		}
    		/////////////////////////////////// exist room //////////////////////////////////////
    		$room_id = $this->getExistRoomByName($data[$i]['H']);
    		if(empty($room_id)){
    			$arr = array(
    					'room_name'=>$data[$i]['H'],
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_room';
    			$room_id = $this->insert($arr);
    		}
    		/////////////////////////////////// exist student //////////////////////////////////////
    		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    
    		if(empty($stu_id)){
    			$this->_name='rms_student';
    			$arr = array(
    					'branch_id'=>$branch_id,
    					'stu_type'=>1,// Khmer FT
    					'user_id'=>1,
    					'stu_code'=>$data[$i]['B'],
    					'stu_khname'=>$data[$i]['C'],
    					'stu_enname'=>$data[$i]['D'],
    					'sex'=>($data[$i]['E']=="ប")?1:2,
    					'degree'=>2,//primary
    					'grade'=>$current_grade_id,
    					'room'=>$room_id,
    					'session'=>$session,
    					
    					'is_stu_new'=>(empty($data[$i]['J']))?0:1,
    						
    					'is_subspend'=>$is_suspend,
    					
    					'tel'=>$data[$i]['AC'],
    					'create_date'=>$create_date,
    			);
    			$stu_id = $this->insert($arr);
    
    			$this->_name='rms_student_id';
    			$arr1 = array(
    					'branch_id'=>$branch_id,
    					'stu_id'=>$stu_id,
    					'stu_type'=>1,// Khmer FT
    					'degree'=>2,//primary
    					'status'=>1,
    					'is_finish'=>0,
    					'is_parent'=>null,
    			);
    			$this->insert($arr1);
    		}
    
    		if(!empty($data[$i]['AE'])){
	    		$this->_name='rms_student_drop';
		    	$arr2 = array(
		    			'type'=>2,// drop
		    			'stu_id'=>$stu_id,
		    			'status'=>1,
		    			'date'=>date("Y-m-d"),
		    			'reason'=>$data[$i]['AD'],
		    			'user_id'=>1,
		    			'drop_from'=>1, // student drop
		    	);
		    	$this->insert($arr2);
	    	}
    
    
    		$qty=1;
    		$qty_day=0;
    		$mystring = $data[$i]['R'];
    		$findme   = 'day';
    		$pos = strpos($mystring, $findme);
    		if(!empty($pos)){
    			$payment_term = 5;
    			$qty_day = str_replace("day", "", $data[$i]['R']);
    			$qty = $qty_day;
    		}else if($data[$i]['R']==3){
    			$payment_term = 2;//
    		}else if($data[$i]['R']==6){
    			$payment_term = 3;
    		}else if($data[$i]['R']==12){
    			$payment_term = 4;
    		}else{
    			$payment_term = 1;
    			$qty = $data[$i]['R'];
    		}
    
    		$this->_name = 'rms_student_payment';
    		$array = array(
    				'branch_id'=>$branch_id,
    				'student_id'=>$stu_id,
    				'receipt_number'=>$data[$i]['L'],
    				'degree'=>2,//primary
    				'grade'=>$grade_id,
    				'session'=>$session,
    				'room_id'=>$room_id,
    
    				'payment_term'=>$payment_term,
    				'amount_sec'=>$qty_day,
    
    				'exchange_rate'=>4100,
    				'tuition_fee'=>$data[$i]['M'],
    				'discount_percent'=>$data[$i]['N'],
    				'tuition_fee_after_discount'=>$data[$i]['O'],
    					
    				'material_fee'=>$data[$i]['V'],
    				'admin_fee'=>$data[$i]['S'],
    					
    				'total_payment'=>$data[$i]['Z'],
    				'receive_amount'=>$data[$i]['Z'],
    				'paid_amount'=>$data[$i]['Z'],
    				'balance_due'=>$data[$i]['Y'],
    					
    				'payfor_type'=>1,//Khmer FT
    					
    				'grand_total_payment'=>$data[$i]['Z'],
    				'grand_total_payment_in_riel'=>$data[$i]['Z'] * 4100,
    				'grand_total_paid_amount'=>$data[$i]['Z'],
    				'grand_total_balance'=>$data[$i]['Y'],
    					
    				'note'=>$data[$i]['AB'],
    				'user_id'=>1,
    				'create_date'=>$create_date,
    				'is_new'=>(empty($data[$i]['J']))?0:1,
    
    				'is_subspend'=>$is_suspend,
    					
    				'student_type'=>3,
    		);
    		//$payment_id = $this->insert($array);
    		$this->insert($array);
    		$payment_id++;
    
    		$this->_name = 'rms_student_paymentdetail';
    
    		$is_start = 1;
    
    		$array1 = array(
    				'payment_id'=>$payment_id,
    				'type'=>1,
    				'service_id'=>4,
    				'payment_term'=>$payment_term,
    					
    				'fee'=>$data[$i]['M'],
    				'qty'=>$qty,
    
    				'discount_percent'=>$data[$i]['N'],
    					
    				'material_fee'=>$data[$i]['V'],
    				'admin_fee'=>$data[$i]['S'],
    					
    				'subtotal'=>$data[$i]['Z'],
    				'paidamount'=>$data[$i]['Z'],
    				'balance'=>$data[$i]['Y'],
    
    				'start_date'=>$start_date,
    				'validate'=>$end_date,
    					
    				'note'=>$data[$i]['AB'],
    				'user_id'=>1,
    				'is_complete'=>1,
    				'comment'=>'បង់រួច',
    					
    				'is_start'=>$is_start,
    		);
    		$this->insert($array1);
    	}
    }
    
    
    public function updateItemsByImportHigh($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	
    	$payment_id=2502;
    	$branch_id = 2;
    	
    	for($i=3; $i<=$count; $i++){
    
    		if(empty($data[$i]['K']) && empty($data[$i]['M']) && empty($data[$i]['W'])){
    			continue;
    		}
    
    		$date = $data[$i]['K'];//exit();
    		$date = str_replace("/", "-", $date);
    		$create_date = date("Y-m-d",strtotime($date));
    		//echo $create_date;exit();
    		 
    		$start = $data[$i]['P'];//exit();
    		$start = str_replace("/", "-", $start);
    		$start_date = date("Y-m-d",strtotime($start));
    
    		$end = $data[$i]['Q'];//exit();
    		$end = str_replace("/", "-", $end);
    		$end_date = date("Y-m-d",strtotime($end));
    
    		if($data[$i]['I']=="ព្រឹក"){ //Mor //ព្រឹក
    			$session = 1;
    		}else{
    			$session = 2;
    		}
    
    		if(!empty($data[$i]['AB'])){
    			$is_suspend=2;
    		}else{
    			$is_suspend=0;
    		}
    
    		/////////////////////////////////// exist grade //////////////////////////////////////
    
    		$grade_id = $this->getExistGradeByName($data[$i]['G']); // level (តាមការបង់លុយ)
    		if(empty($grade_id)){
    			$arr = array(
    					'major_enname'=>$data[$i]['G'],
    					'dept_id'=>2,
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_major';
    			$grade_id = $this->insert($arr);
    		}
    
    		$current_grade_id = $this->getExistGradeByName($data[$i]['F']); // level(shortcut) (កំពុងរៀនបច្ចុប្បន្ន)
    		if(empty($current_grade_id)){
    			$arr = array(
    					'major_enname'=>$data[$i]['F'],
    					'dept_id'=>2,
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_major';
    			$current_grade_id = $this->insert($arr);
    		}
    		/////////////////////////////////// exist room //////////////////////////////////////
    		$room_id = $this->getExistRoomByName($data[$i]['H']);
    		if(empty($room_id)){
    			$arr = array(
    					'room_name'=>$data[$i]['H'],
    					'modify_date'=>date("Y-m-d"),
    					'is_active'=>1,
    					'user_id'=>1,
    			);
    			$this->_name='rms_room';
    			$room_id = $this->insert($arr);
    		}
    		/////////////////////////////////// exist student //////////////////////////////////////
    		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    
    		if(empty($stu_id)){
    			$this->_name='rms_student';
    			$arr = array(
    					'branch_id'=>$branch_id,
    					'stu_type'=>1,// Khmer FT
    					'user_id'=>1,
    					'stu_code'=>$data[$i]['B'],
    					'stu_khname'=>$data[$i]['C'],
    					'stu_enname'=>$data[$i]['D'],
    					'sex'=>($data[$i]['E']=="ប")?1:2,
    					'degree'=>3, //high school
    					'grade'=>$current_grade_id,
    					'room'=>$room_id,
    					'session'=>$session,
    					
    					'is_stu_new'=>(empty($data[$i]['J']))?0:1,
    					
    					'is_subspend'=>$is_suspend,
    					
    					'tel'=>$data[$i]['Z'],
    					'create_date'=>$create_date,
    			);
    			$stu_id = $this->insert($arr);
    
    			$this->_name='rms_student_id';
    			$arr1 = array(
    					'branch_id'=>$branch_id,
    					'stu_id'=>$stu_id,
    					'stu_type'=>1,// Khmer FT
    					'degree'=>3, //high school
    					'status'=>1,
    					'is_finish'=>0,
    					'is_parent'=>null,
    			);
    			$this->insert($arr1);
    		}
    
    		$this->_name='rms_student_drop';
    		if(!empty($data[$i]['AB'])){
    			$arr_drop = array(
    					'type'=>2,// drop
    					'stu_id'=>$stu_id,
    					'status'=>1,
    					'date'=>date("Y-m-d"),
    					'reason'=>$data[$i]['AA'],
    					'user_id'=>1,
    					'drop_from'=>1, // student drop
    			);
    			$this->insert($arr_drop);
    		}

    		
    		$qty=1;
    		$qty_day=0;
    		$mystring = $data[$i]['R'];
    		$findme   = 'day';
    		$pos = strpos($mystring, $findme);
    		if(!empty($pos)){
    			$payment_term = 5;
    			$qty_day = str_replace("day", "", $data[$i]['R']);
    			$qty = $qty_day;
    		}else if($data[$i]['R']==3){
    			$payment_term = 2;//
    		}else if($data[$i]['R']==6){
    			$payment_term = 3;
    		}else if($data[$i]['R']==12){
    			$payment_term = 4;
    		}else{
    			$payment_term = 1;
    			$qty = $data[$i]['R'];
    		}
    
    		$this->_name = 'rms_student_payment';
    		$array = array(
    				'branch_id'=>$branch_id,
    				'student_id'=>$stu_id,
    				'receipt_number'=>$data[$i]['L'],
    				'degree'=>3, //high school
    				'grade'=>$grade_id,
    				'session'=>$session,
    				'room_id'=>$room_id,
    
    				'payment_term'=>$payment_term,
    				'amount_sec'=>$qty_day,
    
    				'exchange_rate'=>4100,
    				'tuition_fee'=>$data[$i]['M'],
    				'discount_percent'=>$data[$i]['N'],
    				'tuition_fee_after_discount'=>$data[$i]['O'],
    					
    				'material_fee'=>0,
    				'admin_fee'=>$data[$i]['S'],
    					
    				'total_payment'=>$data[$i]['W'],
    				'receive_amount'=>$data[$i]['W'],
    				'paid_amount'=>$data[$i]['W'],
    				'balance_due'=>$data[$i]['V'],
    					
    				'payfor_type'=>1,
    					
    				'grand_total_payment'=>$data[$i]['W'],
    				'grand_total_payment_in_riel'=>$data[$i]['W'] * 4100,
    				'grand_total_paid_amount'=>$data[$i]['W'],
    				'grand_total_balance'=>$data[$i]['V'],
    					
    				'note'=>$data[$i]['Y'],
    				'user_id'=>1,
    				'create_date'=>$create_date,
    				'is_new'=>(empty($data[$i]['J']))?0:1,
    
    				'is_subspend'=>$is_suspend,
    					
    				'student_type'=>3,
    		);
    		//$payment_id = $this->insert($array);
    		$this->insert($array);
    		$payment_id++;
    
    		$this->_name = 'rms_student_paymentdetail';
    
    		$is_start = 1;
    		$array1 = array(
    				'payment_id'=>$payment_id,
    				'type'=>1,
    				'service_id'=>4,
    				'payment_term'=>$payment_term,
    					
    				'fee'=>$data[$i]['M'],
    				'qty'=>$qty,
    
    				'discount_percent'=>$data[$i]['N'],
    					
    				'material_fee'=>0,
    				'admin_fee'=>$data[$i]['S'],
    					
    				'subtotal'=>$data[$i]['W'],
    				'paidamount'=>$data[$i]['W'],
    				'balance'=>$data[$i]['V'],
    
    				'start_date'=>$start_date,
    				'validate'=>$end_date,
    					
    				'note'=>$data[$i]['Y'],
    				'user_id'=>1,
    				'is_complete'=>1,
    				'comment'=>'បង់រួច',
    					
    				'is_start'=>$is_start,
    		);
    		$this->insert($array1);
    	}
    }
    

    
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
   
    function getExistStudentInfoByStudentName($stu_khname,$branch_id){
    	$db=$this->getAdapter();
    	$sql="select stu_id from rms_student where stu_khname='$stu_khname' and branch_id = $branch_id limit 1";
    	return $db->fetchOne($sql);
    }
    
    function insertFirstRecord($stu_id,$service_id){
    	$db=$this->getAdapter();
    	$sql="select id from rms_service where stu_id = $stu_id and service_id = $service_id limit 1";
    	return $db->fetchOne($sql);
    }
    
    function getExistCarByCarID($car_id){
    	$db=$this->getAdapter();
    	$sql="select id from rms_car where carid = '$car_id' limit 1";
    	return $db->fetchOne($sql);
    }
    
    function getExistServiceNameByCarID($service_name){
    	$db=$this->getAdapter();
    	$sql="select service_id as id from rms_program_name where title = '$service_name' limit 1";
    	return $db->fetchOne($sql);
    }
    
/////////////////////////////////////////////////////////// Lunch Service //////////////////////////////////////////////////////////////////////////////////
    
    public function updateItemsByImportCar($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	
    	$branch_id = 2;
    	
// 		for($i=3; $i<=$count; $i++){
//     	    $stu_id = $this->getExistStudentInfoByStudentName($data[$i]['C'],$branch_id);
//     		if($stu_id>0){
//     	    	echo $data[$i]['A']."have<br>";
//     	    }else{
//     	    	echo $data[$i]['A'].", ".$data[$i]['C']." = dont have <br>";
//     	    }
//     	}
//     	exit();

    	for($i=3; $i<=$count; $i++){
    
    		if(empty($data[$i]['C']) && empty($data[$i]['B'])){
    			continue;
    		}
    		
    		$date = $data[$i]['O'];//exit();
    		$date = str_replace("/", "-", $date);
    		$create_date = date("Y-m-d",strtotime($date));
    		//echo $create_date;exit();
    		 
    		$start = $data[$i]['S'];//exit();
    		$start = str_replace("/", "-", $start);
    		$start_date = date("Y-m-d",strtotime($start));
    
    		$end = $data[$i]['T'];//exit();
    		$end = str_replace("/", "-", $end);
    		$end_date = date("Y-m-d",strtotime($end));
    
    		if($data[$i]['J']=='P' || $data[$i]['J']=='ü'){
    			$time1 = 1;
    			$time1 = $time1.",";
    		}else{
    			$time1=null;
    		}
    		
    		if($data[$i]['K']=='P' || $data[$i]['K']=='ü'){
    			$time2 = 2;
    			$time2 = $time2.",";
    		}else{
    			$time2=null;
    		}
    		
    		if($data[$i]['L']=='P' || $data[$i]['L']=='ü'){
    			$time3 = 3;
    			$time3 = $time3.",";
    		}else{
    			$time3=null;
    		}
    		
    		if($data[$i]['M']=='P' || $data[$i]['M']=='ü'){
    			$time4 = 4;
    		}else{
    			$time4=null;
    		}
    		$arr_time = $time1.$time2.$time3.$time4;
    		
    		if(!empty($data[$i]['AB'])){
    			$is_suspend=2;
    		}else{
    			$is_suspend=0;
    		}
    	//////////////////////////////////// insert into rms_car ////////////////////////////////////////////////////////////////	
    		
    		$car_id = $this->getExistCarByCarID($data[$i]['F']);
    		if(empty($car_id)){
    			$this->_name="rms_car";
    			$arr = array(
    					'branch_id'	=>$branch_id,
    					'carid'		=>$data[$i]['F'],
    					'userid'	=>1,
    					'create_date'=>$create_date,
    					);
    			$car_id = $this->insert($arr);
    		}
    		
    	//////////////////////////////////// insert into rms_program_name ////////////////////////////////////////////////////////////////	
    		
    		$service_id = $this->getExistServiceNameByCarID($data[$i]['I']);
    		if(empty($service_id)){
    			$this->_name="rms_program_name";
    			$arr = array(
    					'ser_cate_id'=>3,
    					'title'=>$data[$i]['I'],
    					'car_id'=>$car_id,
    					'status'=>1,
    					'user_id'=>1,
    					'type'=>2,
    			);
    			$service_id = $this->insert($arr);
    		}
    		
    
    		/////////////////////////////////// exist student //////////////////////////////////////
    		$stu_id = $this->getExistStudentInfoByStudentName($data[$i]['C'],$branch_id);
    
    		if(!empty($stu_id)){
    			$result = $this->insertFirstRecord($stu_id, $service_id);
	    		if(empty($result)){
	    			$this->_name='rms_service';
	    			$arr = array(
	    					'branch_id'	=>$branch_id,
	    					'type'		=>4,// Transport
	    					'stu_id'	=>$stu_id,
	    					'stu_code'	=>$data[$i]['B'],
	    					'service_id'=>$service_id ,//car service
	    					'car_id'	=>$car_id, // car id
	    					'time_for_car'=>$arr_time, // time for car 
	    					
	    					'is_suspend'=>$is_suspend,
	    					
	    					'create_date'=>$create_date,
	    					'is_new'	=>(empty($data[$i]['N']))?0:1,
	    			);
	    			$this->insert($arr);
    			}
    		}
    		
    		$this->_name='rms_student_drop';
    		if(!empty($data[$i]['AB'])){
    			$arr_drop = array(
    					'type'=>2,
    					'stu_id'=>$stu_id,
    					'status'=>1,
    					'date'=>date("Y-m-d"),
    					'reason'=>$data[$i]['AA'],
    					'user_id'=>1,
    					'drop_from'=>2 // 2=transport drop
    			);
    			$this->insert($arr_drop);
    		}
    
    		$qty=1;
    		$qty_day=0;
    		$mystring = $data[$i]['U'];
    		$findme   = 'day';
    		$pos = strpos($mystring, $findme);
    		if(!empty($pos)){
    			$payment_term = 5;
    			$qty_day = str_replace("day", "", $data[$i]['U']);
    			$qty = $qty_day;
    		}else if($data[$i]['U']==3){
    			$payment_term = 2;//
    		}else if($data[$i]['U']==6){
    			$payment_term = 3;
    		}else if($data[$i]['U']==12){
    			$payment_term = 4;
    		}else{
    			$payment_term = 1;
    			$qty = $data[$i]['U'];
    		}
    
    		$this->_name = 'rms_student_payment';
    		$array = array(
    				'branch_id'=>$branch_id,
    				'student_id'=>$stu_id,
    				'receipt_number'=>$data[$i]['P'],
    
    				'payment_term'=>$payment_term,
    				'amount_sec'=>$qty_day,
    
    				'exchange_rate'=>4100,
    				'tuition_fee'=>$data[$i]['Q'],
    				'discount_percent'=>$data[$i]['R'],
    				'tuition_fee_after_discount'=>$data[$i]['W'],
    					
    				'material_fee'=>0,
    				'admin_fee'=>0,
    					
    				'total_payment'=>$data[$i]['W'],
    				'receive_amount'=>$data[$i]['W'],
    				'paid_amount'=>$data[$i]['W'],
    				'balance_due'=>$data[$i]['V'],
    					
    				'payfor_type'=>3, // transport
    				'time_for_car'=>$arr_time,
    					
    				'grand_total_payment'=>$data[$i]['W'],
    				'grand_total_payment_in_riel'=>$data[$i]['W'] * 4100,
    				'grand_total_paid_amount'=>$data[$i]['W'],
    				'grand_total_balance'=>$data[$i]['V'],
    					
    				'is_subspend'=>$is_suspend,
    				
    				'note'=>$data[$i]['Y'],
    				'user_id'=>1,
    				'create_date'=>$create_date,
    				'is_new'=>(empty($data[$i]['N']))?0:1,
    
    				'student_type'=>3,
    		);
    		$payment_id = $this->insert($array);
    
    
    		$this->_name = 'rms_student_paymentdetail';
    
    		$is_start = 1;
    		$array1 = array(
    				'payment_id'=>$payment_id,
    				'type'=>3, // car service
    				'service_id'=>$service_id,
    				'payment_term'=>$payment_term,
    					
    				'fee'=>$data[$i]['Q'],
    				'qty'=>$qty,
    
    				'discount_percent'=>$data[$i]['R'],
    					
    				'material_fee'=>0,
    				'admin_fee'=>0,
    					
    				'subtotal'=>$data[$i]['W'],
    				'paidamount'=>$data[$i]['W'],
    				'balance'=>$data[$i]['V'],
    
    				'start_date'=>$start_date,
    				'validate'=>$end_date,
    					
    				'note'=>$data[$i]['Y'],
    				'user_id'=>1,
    				'is_complete'=>1,
    				'comment'=>'បង់រួច',
    					
    				'is_start'=>$is_start,
    		);
    		$this->insert($array1);
    	}
    }    
    
    
    public function updateItemsByImportFood($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	
    	$branch_id = 2;
    	
//     	for($i=3; $i<=$count; $i++){
//     		$stu_id = $this->getExistStudentInfoByStudentName($data[$i]['C'],$branch_id);
//     		if($stu_id>0){
//     	    	echo $data[$i]['A']."have<br>";
//     	    }else{
//     	    	echo $data[$i]['A'].", ".$data[$i]['C']." = dont have <br>";
//     	    }
//     	}
//     	exit();

    	for($i=3; $i<=$count; $i++){
    	
    		if(empty($data[$i]['C']) && empty($data[$i]['B'])){
    			continue;
    		}
    		
	    	$date = $data[$i]['J'];//exit();
	    	$date = str_replace("/", "-", $date);
	    	$create_date = date("Y-m-d",strtotime($date));
	    	//echo $create_date;exit();
	    	 
	    	$start = $data[$i]['N'];//exit();
	    	$start = str_replace("/", "-", $start);
	    	$start_date = date("Y-m-d",strtotime($start));
	    
	    	$end = $data[$i]['O'];//exit();
	    	$end = str_replace("/", "-", $end);
	    	$end_date = date("Y-m-d",strtotime($end));
    
	    	if(!empty($data[$i]['V'])){
	    		$is_suspend=2;
	    	}else{
	    		$is_suspend=0;
	    	}
	    	/////////////////////////////////// exist student //////////////////////////////////////
	    	if($data[$i]['H']=="P" && $data[$i]['I']=="P"){
	    		$service_id = 93; //អាហារ និង ស្នាក់នៅ
	    	}else if($data[$i]['H']=="P"){
	    		$service_id = 91; //អាហារ
	    	}else{
	    		$service_id = 92; //ស្នាក់នៅ
	    	}
	    	
	    	$stu_id = $this->getExistStudentInfoByStudentName($data[$i]['C'],$branch_id);
		    if(!empty($stu_id)){
		    	$result = $this->insertFirstRecord($stu_id,$service_id);
		    	if(empty($result)){
			    	$this->_name='rms_service';
			    	$arr = array(
			    			'branch_id'	=>$branch_id,
			    			'type'		=>5,// Food
			    			'stu_id'	=>$stu_id,
			    			'stu_code'	=>$data[$i]['B'],
			    			'service_id'=>$service_id,
			    			'status'	=>1,
			    			
			    			'is_suspend'=>$is_suspend,
			    			
			    			'create_date'=>$create_date,
			    			'is_new'	=>(empty($data[$i]['G']))?0:1,
			    	);
			    	$this->insert($arr);
		    	}
		    }
	    
		    $this->_name='rms_student_drop';
		    if(!empty($data[$i]['V'])){
		    	$arr_drop = array(
		    			'type'=>2,
		    			'stu_id'=>$stu_id,
		    			'status'=>1,
		    			'date'=>date("Y-m-d"),
		    			'reason'=>$data[$i]['U'],
		    			'user_id'=>1,
		    			'drop_from'=>3 // 3=food drop
		    		);
		    	$this->insert($arr_drop);
		    }
	    
	    	$qty=1;
	    	$qty_day=0;
	    	$mystring = $data[$i]['P'];
	    	$findme   = 'day';
	    	$pos = strpos($mystring, $findme);
	    	if(!empty($pos)){
		    	$payment_term = 5;
		    	$qty_day = str_replace("day", "", $data[$i]['P']);
		    	$qty = $qty_day;
	    	}else if($data[$i]['P']==3){
	    		$payment_term = 2;//
	    	}else if($data[$i]['P']==6){
	    		$payment_term = 3;
	    	}else if($data[$i]['P']==12){
	    		$payment_term = 4;
	    	}else{
		    	$payment_term = 1;
		    	$qty = $data[$i]['P'];
	    	}
	    
	    	$this->_name = 'rms_student_payment';
	    	$array = array(
	    			'branch_id'=>$branch_id,
	    			'student_id'=>$stu_id,
	    			'receipt_number'=>$data[$i]['K'],
	    
	    			'payment_term'=>$payment_term,
	    			'amount_sec'=>$qty_day,
	    
	    			'exchange_rate'=>4100,
	    			'tuition_fee'=>$data[$i]['L'],
	    			'discount_percent'=>$data[$i]['M'],
	    			'tuition_fee_after_discount'=>$data[$i]['R'],
	    				
	    			'material_fee'=>0,
	    			'admin_fee'=>0,
	    				
	    			'total_payment'=>$data[$i]['R'],
	    			'receive_amount'=>$data[$i]['R'],
	    			'paid_amount'=>$data[$i]['R'],
	    			'balance_due'=>$data[$i]['Q'],
	    				
	    			'payfor_type'=>4,//food
	    				
	    			'grand_total_payment'=>$data[$i]['R'],
	    			'grand_total_payment_in_riel'=>$data[$i]['R'] * 4100,
	    			'grand_total_paid_amount'=>$data[$i]['R'],
	    			'grand_total_balance'=>$data[$i]['Q'],
	    				
	    			'is_subspend'=>$is_suspend,
	    			
	    			'note'=>$data[$i]['T'],
	    			'user_id'=>1,
	    			'create_date'=>$create_date,
	    			'is_new'=>(empty($data[$i]['G']))?0:1,
	    				
	    			'student_type'=>3,
	    	);
	    	$payment_id = $this->insert($array);
	    
	    
	    	$this->_name = 'rms_student_paymentdetail';
	    
	    	$is_start = 1;
	    	$array1 = array(
	    			'payment_id'=>$payment_id,
	    			'type'=>5, // lunch
	    			'service_id'=>$service_id,
	    			'payment_term'=>$payment_term,
	    				
	    			'fee'=>$data[$i]['L'],
	    			'qty'=>$qty,
	    
	    			'discount_percent'=>$data[$i]['M'],
	    				
	    			'material_fee'=>0,
	    			'admin_fee'=>0,
	    				
	    			'subtotal'=>$data[$i]['R'],
	    			'paidamount'=>$data[$i]['R'],
	    			'balance'=>$data[$i]['Q'],
	    
	    			'start_date'=>$start_date,
	    			'validate'=>$end_date,
	    				
	    			'note'=>$data[$i]['T'],
	    			'user_id'=>1,
	    			'is_complete'=>1,
	    			'comment'=>'បង់រួច',
	    				
	    			'is_start'=>$is_start,
	    	);
	    	$this->insert($array1);
    	}
    }
    
    
////////////////////////////////////////////////////////// Rental ///////////////////////////////////////////////////////////////////    
    
    function getExistCustomerByName($name){
    	$db=$this->getAdapter();
    	$sql="select id from rms_customer where last_name='$name' limit 1";
    	return $db->fetchOne($sql);
    }
    
    
    public function updateItemsByImportRental($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	//print_r($data);exit();
    	
    	$branch_id = 18;
    	$customer_code=1;
    	
    	for($i=3; $i<=$count; $i++){
    		 
    		if(empty($data[$i]['C']) && empty($data[$i]['B'])){
    			continue;
    		}
    		
    		$start = $data[$i]['F'];//exit();
    		$start = str_replace("/", "-", $start);
    		$start_date = date("Y-m-d",strtotime($start));
    	  
    		$end = $data[$i]['G'];//exit();
    		$end = str_replace("/", "-", $end);
    		$end_date = date("Y-m-d",strtotime($end));
    		
    /////////////////////////////// rent /////////////////////////////////////////////////////
    		$rent = $data[$i]['H'];//exit();
    		$rent = str_replace("/", "-", $rent);
    		$rent_paid_date = date("Y-m-d",strtotime($rent));
    		//echo $create_date;exit();
    		
    		$rent_start = $data[$i]['K'];//exit();
    		$rent_start = str_replace("/", "-", $rent_start);
    		$rent_start_date = date("Y-m-d",strtotime($rent_start));
    		
    		$rent_end = $data[$i]['L'];//exit();
    		$rent_end = str_replace("/", "-", $rent_end);
    		$rent_end_date = date("Y-m-d",strtotime($rent_end));

    /////////////////////////////// water /////////////////////////////////////////////////////
    		
    		$water_start = $data[$i]['S'];//exit();
    		$water_start = str_replace("/", "-", $water_start);
    		$water_start_date = date("Y-m-d",strtotime($water_start));
    		
    		$water_end = $data[$i]['T'];//exit();
    		$water_end = str_replace("/", "-", $water_end);
    		$water_end_date = date("Y-m-d",strtotime($water_end));
    		
    /////////////////////////////// electricity /////////////////////////////////////////////////////
    		
    		$fire_start = $data[$i]['AA'];//exit();
    		$fire_start = str_replace("/", "-", $fire_start);
    		$fire_start_date = date("Y-m-d",strtotime($fire_start));
    		
    		$fire_end = $data[$i]['AB'];//exit();
    		$fire_end = str_replace("/", "-", $fire_end);
    		$fire_end_date = date("Y-m-d",strtotime($fire_end));
    		
    /////////////////////////////// cleaning /////////////////////////////////////////////////////
    		
    		$clean_start = $data[$i]['AE'];//exit();
    		$clean_start = str_replace("/", "-", $clean_start);
    		$clean_start_date = date("Y-m-d",strtotime($clean_start));
    		
    		$clean_end = $data[$i]['AF'];//exit();
    		$clean_end = str_replace("/", "-", $clean_end);
    		$clean_end_date = date("Y-m-d",strtotime($clean_end));
    
    /////////////////////////////////// exist customer //////////////////////////////////////
    
    		
    		
    		$cus_id = $this->getExistCustomerByName($data[$i]['B']);
    		if(empty($cus_id)){
    			$this->_name='rms_customer';
    			$arr = array(
    					'branch_id'		=>$branch_id,
    					'customer_code'	=>"C0000".$customer_code++,
    					'first_name'	=>$data[$i]['B'],
    					'last_name'		=>$data[$i]['B'],
    					'sex'			=>($data[$i]['C']=="ប")?1:2,
    					'address'		=>$data[$i]['D'],// 
    					'phone'			=>$data[$i]['E'],// 
    					'start_date'	=>$start_date,
    					'end_date'		=>$end_date,
    					'user_id'		=>1,
    			);
    			$cus_id = $this->insert($arr);
    		}
    
    
    		$this->_name = 'rms_customer_paymentdetail';
    		$array = array(
    				'branch_id'		=>$branch_id,
    				'cus_id'		=>$cus_id,
    				
    				'water_old_congtor'=>$data[$i]['O'],
    				'water_new_congtor'=>$data[$i]['N'],
    				'water_qty'=>$data[$i]['P'],
    				'water_cost'=>$data[$i]['Q'],
    				'water_total'=>$data[$i]['P'] * $data[$i]['Q'],
    				'water_exc_rate'=>$data[$i]['R'],
    				'water_start_date'=>$water_start_date,
    				'water_end_date'=>$water_end_date,
    				
    				'fire_old_congtor'=>$data[$i]['W'],
    				'fire_new_congtor'=>$data[$i]['V'],
    				'fire_qty'=>$data[$i]['X'],
    				'fire_cost'=>$data[$i]['Y'],
    				'fire_total'=>$data[$i]['X'] * $data[$i]['Y'],
    				'fire_exc_rate'=>$data[$i]['Z'],
    				'fire_start_date'=>$fire_start_date,
    				'fire_end_date'=>$fire_end_date,
    				
    				'rent_date_paid'=>$rent_paid_date,
    				'rent_receipt_no'=>$data[$i]['I'],
    				'rent_paid'=>$data[$i]['J'],
    				'rent_start_date'=>$rent_start_date,
    				'rent_end_date'=>$rent_end_date,
    
    				'hygiene_price'=>$data[$i]['AD'],
    				'hygiene_start_date'=>$clean_start_date,
    				'hygiene_end_date'=>$clean_end_date,
    
    				'all_total_amount'=>$data[$i]['AI'],
    				'paid'=>$data[$i]['AI'],
    				'user_id'=>1,
    				'create_date'=>$rent_paid_date,
    
    				'note'=>$data[$i]['AJ'],
    		);
    		$id = $this->insert($array);
    	}
    }
    
    
    
/////////////////////////////////////////////////////////// khmer Drop //////////////////////////////////////////////////////////////////////////////////
        
    
    public function updateItemsByImportKIDDrop($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    
    	//     	for($i=3; $i<=$count; $i++){
    	//     		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    	//     		if(empty($stu_id)){
    	//     			echo $i.",null=".$data[$i]['B'].",".$data[$i]['C']."<br />";
    	//     		}else{
    	//     			echo $i.",have"."<br />";
    	//     		}
    	//     	}
    	//     	exit();
    
    	for($i=3; $i<=$count; $i++){
    
    		$date = $data[$i]['K'];//exit();
    		$date = str_replace("/", "-", $date);
    		$create_date = date("Y-m-d",strtotime($date));
    		//echo $create_date;exit();
    		 
    		$start = $data[$i]['P'];//exit();
    		$start = str_replace("/", "-", $start);
    		$start_date = date("Y-m-d",strtotime($start));
    
    		$end = $data[$i]['Q'];//exit();
    		$end = str_replace("/", "-", $end);
    		$end_date = date("Y-m-d",strtotime($end));
    
    		if($data[$i]['I']=="Mor"){
    			$session = 1;
    		}else{
    			$session = 2;
    		}
    
    		/////////////////////////////////// exist student //////////////////////////////////////
    		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    
    
    		if(!empty($data[$i]['AE'])){
    			$qty=1;
    			$qty_day=0;
    			$mystring = $data[$i]['R'];
    			$findme   = 'day';
    			$pos = strpos($mystring, $findme);
    			if(!empty($pos)){
    				$payment_term = 5;
    				$qty_day = str_replace("day", "", $data[$i]['R']);
    				$qty = $qty_day;
    			}else if($data[$i]['R']==3){
    				$payment_term = 2;//
    			}else if($data[$i]['R']==6){
    				$payment_term = 3;
    			}else if($data[$i]['R']==12){
    				$payment_term = 4;
    			}else{
    				$payment_term = 1;
    				$qty = $data[$i]['R'];
    			}
    
    			$this->_name = 'rms_student_payment';
    			$_arr = array(
    					'branch_id'=>6,
    					'student_id'=>$stu_id,
    					'receipt_number'=>$data[$i]['L'],
    					'degree'=>1,
    					'grade'=>'',
    					'session'=>$session,
    					'room_id'=>'',
    
    					'payment_term'=>$payment_term,
    					'amount_sec'=>$qty_day,
    
    					'exchange_rate'=>4100,
    					'tuition_fee'=>$data[$i]['M'],
    					'discount_percent'=>$data[$i]['N'],
    					'tuition_fee_after_discount'=>$data[$i]['O'],
    
    					'material_fee'=>$data[$i]['V'],
    					'admin_fee'=>$data[$i]['S'],
    
    					'total_payment'=>$data[$i]['Z'],
    					'receive_amount'=>$data[$i]['Z'],
    					'paid_amount'=>$data[$i]['Z'],
    					'balance_due'=>$data[$i]['Y'],
    
    					'payfor_type'=>1,
    
    					'grand_total_payment'=>$data[$i]['Z'],
    					'grand_total_payment_in_riel'=>$data[$i]['Z'] * 4100,
    					'grand_total_paid_amount'=>$data[$i]['Z'],
    					'grand_total_balance'=>$data[$i]['Y'],
    
    					'note'=>$data[$i]['AB'],
    					'user_id'=>1,
    					'create_date'=>$create_date,
    					'is_new'=>(empty($data[$i]['J']))?0:1,
    
    					'is_subspend'=>(empty($data[$i]['AE']))?0:2,
    
    					'student_type'=>3,
    			);
    			$paymentid = $this->insert($_arr);
    
    
    			$is_start = 1;
    
    
    			$_arr2 = array(
    					'payment_id'=>$paymentid,
    					'type'=>1,
    					'service_id'=>4,
    					'payment_term'=>$payment_term,
    
    					'fee'=>$data[$i]['M'],
    					'qty'=>$qty,
    
    					'discount_percent'=>$data[$i]['N'],
    
    					'material_fee'=>$data[$i]['V'],
    					'admin_fee'=>$data[$i]['S'],
    
    					'subtotal'=>$data[$i]['Z'],
    					'paidamount'=>$data[$i]['Z'],
    					'balance'=>$data[$i]['Y'],
    
    					'start_date'=>$start_date,
    					'validate'=>$end_date,
    
    					'note'=>$data[$i]['AB'],
    					'user_id'=>1,
    					'is_complete'=>1,
    					'comment'=>'បង់រួច',
    
    					'is_start'=>$is_start,
    			);
    			$this->_name ='rms_student_paymentdetail';
    			$this->insert($_arr2);
    			//exit();
    		}
    	}
    }
    
    
    public function updateItemsByImportPrimaryDrop($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    
    	//     	for($i=3; $i<=$count; $i++){
    	//     		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    	//     		if(empty($stu_id)){
    	//     			echo $i.",null=".$data[$i]['B'].",".$data[$i]['C']."<br />";
    	//     		}else{
    	//     			echo $i.",have"."<br />";
    	//     		}
    	//     	}
    	//     	exit();
    	 
    	 
    	for($i=3; $i<=$count; $i++){
    
    		$date = $data[$i]['K'];//exit();
    		$date = str_replace("/", "-", $date);
    		$create_date = date("Y-m-d",strtotime($date));
    		//echo $create_date;exit();
    		 
    		$start = $data[$i]['P'];//exit();
    		$start = str_replace("/", "-", $start);
    		$start_date = date("Y-m-d",strtotime($start));
    
    		$end = $data[$i]['Q'];//exit();
    		$end = str_replace("/", "-", $end);
    		$end_date = date("Y-m-d",strtotime($end));
    
    		if($data[$i]['I']=="Mor"){
    			$session = 1;
    		}else{
    			$session = 2;
    		}
    
    
    		/////////////////////////////////// exist student //////////////////////////////////////
    
    		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    
    		if(!empty($stu_id)){
    			$qty=1;
    			$qty_day=0;
    			$mystring = $data[$i]['R'];
    			$findme   = 'day';
    			$pos = strpos($mystring, $findme);
    			if(!empty($pos)){
    				$payment_term = 5;
    				$qty_day = str_replace("day", "", $data[$i]['R']);
    				$qty = $qty_day;
    			}else if($data[$i]['R']==3){
    				$payment_term = 2;//
    			}else if($data[$i]['R']==6){
    				$payment_term = 3;
    			}else if($data[$i]['R']==12){
    				$payment_term = 4;
    			}else{
    				$payment_term = 1;
    				$qty = $data[$i]['R'];
    			}
    			 
    			 
    			$this->_name = 'rms_student_payment';
    			$array = array(
    					'branch_id'=>6,
    					'student_id'=>$stu_id,
    					'receipt_number'=>$data[$i]['L'],
    					'degree'=>2,//primary
    					'grade'=>'',
    					'session'=>$session,
    					'room_id'=>'',
    					 
    					'payment_term'=>$payment_term,
    					'amount_sec'=>$qty_day,
    					 
    					'exchange_rate'=>4100,
    					'tuition_fee'=>$data[$i]['M'],
    					'discount_percent'=>$data[$i]['N'],
    					'tuition_fee_after_discount'=>$data[$i]['O'],
    
    					'material_fee'=>0,
    					'admin_fee'=>$data[$i]['S'],
    
    					'total_payment'=>$data[$i]['W'],
    					'receive_amount'=>$data[$i]['W'],
    					'paid_amount'=>$data[$i]['W'],
    					'balance_due'=>$data[$i]['V'],
    
    					'payfor_type'=>1,//Khmer FT
    
    					'grand_total_payment'=>$data[$i]['W'],
    					'grand_total_payment_in_riel'=>$data[$i]['W'] * 4100,
    					'grand_total_paid_amount'=>$data[$i]['W'],
    					'grand_total_balance'=>$data[$i]['V'],
    
    					'note'=>$data[$i]['Y'],
    					'user_id'=>1,
    					'create_date'=>$create_date,
    					'is_new'=>(empty($data[$i]['J']))?0:1,
    					 
    					'is_subspend'=>(empty($data[$i]['AB']))?0:2,
    
    					'student_type'=>3,
    			);
    			$payment_id = $this->insert($array);
    			 
    			 
    			$this->_name = 'rms_student_paymentdetail';
    			 
    			$is_start = 1;
    			 
    			$array1 = array(
    					'payment_id'=>$payment_id,
    					'type'=>1,
    					'service_id'=>4,
    					'payment_term'=>$payment_term,
    
    					'fee'=>$data[$i]['M'],
    					'qty'=>$qty,
    					 
    					'discount_percent'=>$data[$i]['N'],
    
    					'material_fee'=>0,
    					'admin_fee'=>$data[$i]['S'],
    
    					'subtotal'=>$data[$i]['W'],
    					'paidamount'=>$data[$i]['W'],
    					'balance'=>$data[$i]['V'],
    					 
    					'start_date'=>$start_date,
    					'validate'=>$end_date,
    
    					'note'=>$data[$i]['Y'],
    					'user_id'=>1,
    					'is_complete'=>1,
    					'comment'=>'បង់រួច',
    
    					'is_start'=>$is_start,
    			);
    			$this->insert($array1);
    		}
    	}
    }
    
    
    public function updateItemsByImportHighDrop($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    
    	//     	    	for($i=3; $i<=$count; $i++){
    	//     	    		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    	//     	    		if(empty($stu_id)){
    	//     	    			echo $i.",null=".$data[$i]['B'].",".$data[$i]['C']."<br />";
    	//     	    		}else{
    	//     	    			echo $i.",have"."<br />";
    	//     	    		}
    	//     	    	}
    	//     	    	exit();
    	 
    
    	for($i=3; $i<=$count; $i++){
    
    		$date = $data[$i]['K'];//exit();
    		$date = str_replace("/", "-", $date);
    		$create_date = date("Y-m-d",strtotime($date));
    		//echo $create_date;exit();
    		 
    		$start = $data[$i]['P'];//exit();
    		$start = str_replace("/", "-", $start);
    		$start_date = date("Y-m-d",strtotime($start));
    
    		$end = $data[$i]['Q'];//exit();
    		$end = str_replace("/", "-", $end);
    		$end_date = date("Y-m-d",strtotime($end));
    
    		if($data[$i]['I']=="Mor"){
    			$session = 1;
    		}else{
    			$session = 2;
    		}
    
    		/////////////////////////////////// exist student //////////////////////////////////////
    		$stu_id = $this->getExistStudentByStudentID($data[$i]['B'], $data[$i]['C']);
    
    		if(!empty($stu_id)){
    			$qty=1;
    			$qty_day=0;
    			$mystring = $data[$i]['R'];
    			$findme   = 'day';
    			$pos = strpos($mystring, $findme);
    			if(!empty($pos)){
    				$payment_term = 5;
    				$qty_day = str_replace("day", "", $data[$i]['R']);
    				$qty = $qty_day;
    			}else if($data[$i]['R']==3){
    				$payment_term = 2;//
    			}else if($data[$i]['R']==6){
    				$payment_term = 3;
    			}else if($data[$i]['R']==12){
    				$payment_term = 4;
    			}else{
    				$payment_term = 1;
    				$qty = $data[$i]['R'];
    			}
    			 
    			 
    			$this->_name = 'rms_student_payment';
    			$array = array(
    					'branch_id'=>6,
    					'student_id'=>$stu_id,
    					'receipt_number'=>$data[$i]['L'],
    					'degree'=>3, //high school
    					'grade'=>'',
    					'session'=>$session,
    					'room_id'=>'',
    					 
    					'payment_term'=>$payment_term,
    					'amount_sec'=>$qty_day,
    					 
    					'exchange_rate'=>4100,
    					'tuition_fee'=>$data[$i]['M'],
    					'discount_percent'=>$data[$i]['N'],
    					'tuition_fee_after_discount'=>$data[$i]['O'],
    
    					'material_fee'=>0,
    					'admin_fee'=>$data[$i]['S'],
    
    					'total_payment'=>$data[$i]['W'],
    					'receive_amount'=>$data[$i]['W'],
    					'paid_amount'=>$data[$i]['W'],
    					'balance_due'=>$data[$i]['V'],
    
    					'payfor_type'=>1,
    
    					'grand_total_payment'=>$data[$i]['W'],
    					'grand_total_payment_in_riel'=>$data[$i]['W'] * 4100,
    					'grand_total_paid_amount'=>$data[$i]['W'],
    					'grand_total_balance'=>$data[$i]['V'],
    
    					'note'=>$data[$i]['Y'],
    					'user_id'=>1,
    					'create_date'=>$create_date,
    					'is_new'=>(empty($data[$i]['J']))?0:1,
    					 
    					'is_subspend'=>(empty($data[$i]['AB']))?0:2,
    
    					'student_type'=>3,
    			);
    			$payment_id = $this->insert($array);
    			 
    			 
    			$this->_name = 'rms_student_paymentdetail';
    			 
    			$is_start = 1;
    			 
    			 
    			$array1 = array(
    					'payment_id'=>$payment_id,
    					'type'=>1,
    					'service_id'=>4,
    					'payment_term'=>$payment_term,
    
    					'fee'=>$data[$i]['M'],
    					'qty'=>$qty,
    					 
    					'discount_percent'=>$data[$i]['N'],
    
    					'material_fee'=>0,
    					'admin_fee'=>$data[$i]['S'],
    
    					'subtotal'=>$data[$i]['W'],
    					'paidamount'=>$data[$i]['W'],
    					'balance'=>$data[$i]['V'],
    					 
    					'start_date'=>$start_date,
    					'validate'=>$end_date,
    
    					'note'=>$data[$i]['Y'],
    					'user_id'=>1,
    					'is_complete'=>1,
    					'comment'=>'បង់រួច',
    
    					'is_start'=>$is_start,
    			);
    			$this->insert($array1);
    		}
    	}
    }
    
    
    
    
}   

