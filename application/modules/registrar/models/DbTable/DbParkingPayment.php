<?php

class Registrar_Model_DbTable_DbParkingPayment extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_parking';
 	
	public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->branch_id;
    }
    
    function getAllParkingPayment($search=null){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('pd.branch_id');
    	
    	$sql=" SELECT 
    				pd.id,
    				pd.receipt_no,
    				p.customer_code,
    				p.name,
		       		p.phone,
		       		parking_moto_fee,
		       		parking_bike_fee,
		       		broken_thing_fee,
		       		total_fee,
		       		pd.note,
		      		(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id=pd.user_id LIMIT 1) AS user_name,
		      		pd.create_date,
		      		pd.status
		      	FROM 
		      		rms_parking AS p,
		      		rms_parking_detail AS pd
		      	WHERE 
    				p.id=pd.parking_id
    				and is_void=0
    				and pd.status=1
    				$branch_id
    		";
    	$where = '';
    	
    	$from_date =(empty($search['start_date']))? '1': "pd.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "pd.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  p.customer_code LIKE '%{$s_search}%'";
    		$s_where[]="  p.name LIKE '%{$s_search}%'";
    		$s_where[]="  p.phone LIKE '%{$s_search}%'";
    		$s_where[]="  p.email LIKE '%{$s_search}%'";
    		$s_where[]="  p.address LIKE '%{$s_search}%'";
    		$s_where[]="  pd.receipt_no LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	
    	if($search['cus_name']>0){
    		$where.=' AND p.id='.$search["cus_name"];
    	}
    	$order=" ORDER BY pd.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getCheckCustomer($id){
    	$db=$this->getAdapter();
    	$sql="SELECT cus_id FROM rms_customer_paymentdetail WHERE STATUS=1 AND cus_id=$id";
    	return $db->fetchRow($sql);
    }
 
	public function addParkingPayment($data){
		 
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
					"branch_id" 	=> 	$this->getBranchId(),
					//"customer_code" => 	$data["cus_id"],
					"name"    		=> 	$data["cus_name"],
					"sex"  			=> 	$data["sex"],
					"phone"  		=> 	$data["phone"],
					"email"  		=> 	$data['email'],
					"address"		=> 	$data['address'],
					"user_id"       => 	$this->getUserId(),
					"create_date"   => 	date("Y-m-d"),
					
			);
			if(empty($data['is_new_cu'])){
				$arr['customer_code'] = $this->getCusId(0);
				$parking_id = $this->insert($arr); 
			}else{
				$arr['customer_code'] = $data["cus_id"];
				$where="id = ".$data['old_cus'];
				$parking_id=$data['old_cus'];
				$this->update($arr, $where);
			}
			
			//check customer 
			
			$arr_payment=array(
					"parking_id"     	=> 	$parking_id,
					"receipt_no"  		=> 	$this->getReceiptNo(0),
					
					"moto_ticket_start" => 	$data["moto_ticket_start"],
					"moto_ticket_end"  	=> 	$data["moto_ticket_end"],
					"moto_amount_ticket"=> 	$data["moto_amount_ticket"],
					"moto_price_per_ticket"  => $data["moto_price_per_ticket"],
					
					"parking_moto_fee_in_riel"  => 	$data["moto_total_in_riel"],
					"parking_moto_fee"  => 	$data["moto_total_in_dollar"],
					"moto_for_date"     => 	$data["moto_for_date"],
					
					"bike_ticket_start" => 	$data["bike_ticket_start"],
					"bike_ticket_end"  	=> 	$data["bike_ticket_end"],
					"bike_amount_ticket"=> 	$data["bike_amount_ticket"],
					"bike_price_per_ticket"  => $data["bike_price_per_ticket"],
					
					"parking_bike_fee_in_riel"  => 	$data["bike_total_in_riel"],
					"parking_bike_fee"  => 	$data["bike_total_in_dollar"],
					"bike_for_date"  	=> 	$data['bike_for_date'],
					
					"broken_thing_fee_in_riel"  => 	$data["broken_in_riel"],
					"broken_thing_fee"  => 	$data["broken_total_in_dollar"],
					"broken_for_date"  	=> 	$data['broken_for_date'],
					
					"total_fee"  		=> 	$data["all_total_amount"],
					
					"note"  			=> 	$data["note"],
					
					"create_date"		=>	date('Y-m-d H:i:s'),
					"branch_id"     	=> 	$this->getBranchId(),
					"user_id"     		=> 	$this->getUserId(),
			);
			$this->_name="rms_parking_detail";
			$this->insert($arr_payment);
			$db->commit();
			
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
	 
	public function editCustomerPayment($data){
		 
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
					"branch_id" 	=> 	$this->getBranchId(),
					"customer_code" => 	$data["cus_id"],
					"name"    		=> 	$data["cus_name"],
					"sex"  			=> 	$data["sex"],
					"phone"  		=> 	$data["phone"],
					"email"  		=> 	$data['email'],
					"address"		=> 	$data['address'],
					"user_id"       => 	$this->getUserId(),
					//"status"        => 	$data['status'],
			);
			
			$where="id=".$data['old_cus'];
			$parking_id = $data['old_cus'];
			$this->update($arr, $where);
			
			
			$arr_payment=array(
					"parking_id"     	=> 	$parking_id,
					"receipt_no"  		=> 	$data["receipt_no"],
					
					"moto_ticket_start" => 	$data["moto_ticket_start"],
					"moto_ticket_end"  	=> 	$data["moto_ticket_end"],
					"moto_amount_ticket"=> 	$data["moto_amount_ticket"],
					"moto_price_per_ticket"  => $data["moto_price_per_ticket"],
					
					"parking_moto_fee_in_riel"  => 	$data["moto_total_in_riel"],
					"parking_moto_fee"  => 	$data["moto_total_in_dollar"],
					"moto_for_date"     => 	$data["moto_for_date"],
					
					"bike_ticket_start" => 	$data["bike_ticket_start"],
					"bike_ticket_end"  	=> 	$data["bike_ticket_end"],
					"bike_amount_ticket"=> 	$data["bike_amount_ticket"],
					"bike_price_per_ticket"  => $data["bike_price_per_ticket"],
					
					"parking_bike_fee_in_riel"  => 	$data["bike_total_in_riel"],
					"parking_bike_fee"  => 	$data["bike_total_in_dollar"],
					"bike_for_date"  	=> 	$data['bike_for_date'],
					
					"broken_thing_fee_in_riel"  => 	$data["broken_in_riel"],
					"broken_thing_fee"  => 	$data["broken_total_in_dollar"],
					"broken_for_date"  	=> 	$data['broken_for_date'],
					
					"total_fee"  		=> 	$data["all_total_amount"],
					
					"note"  			=> 	$data["note"],
					
					"branch_id"     	=> 	$this->getBranchId(),
					"user_id"     		=> 	$this->getUserId(),
					
					"status"        	=> 	$data['status'],
			);
			$this->_name="rms_parking_detail";
			$where=" id = ".$data['id'];
			$this->update($arr_payment, $where);
			$db->commit();
			
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
	
	function getParkingById($id){
		$db=$this->getAdapter();
		$sql="SELECT 
					*,
					pd.status as parking_status
		      	FROM 
		      		rms_parking AS p,
		      		rms_parking_detail AS pd
				WHERE 
					p.id=pd.parking_id
					AND pd.id=$id
			";
		return $db->fetchRow($sql);
	}
	
	function getCusId($branch_id=0){
		$db=$this->getAdapter();
		if($branch_id>0){
			$branch = $branch_id;
		}else{
			$branch = $this->getBranchId();
		}
		
		$sql="SELECT count(id) FROM rms_parking where branch_id = $branch limit 1 ";
		$amount=$db->fetchOne($sql);
		
		$new_amount = $amount + 1;
		
		$length = strlen($new_amount);
		
		$prefix = 'P';
		
		for($i=$length;$i<5;$i++){
			$prefix.='0';
		}
		return $prefix.$new_amount;
	} 
	
	function getReceiptNo($branch=0){
		$db=$this->getAdapter();
		
		if($branch>0){
    		$branch_id = $branch;
    	}else{
    		$branch_id = $this->getBranchId();
    	}
		
		$create_date="";
		if($branch_id==6){
			$create_date = " and create_date > '2018-04-01 00:00:00'";
		}else if($branch_id==5 || $branch_id==11){
			$create_date = " and create_date > '2018-12-01 00:00:00'";
		}else if($branch_id==9){
			$create_date = " and create_date > '2018-12-03 00:00:00'";
		}else if($branch_id==14){
			$create_date = " and create_date > '2018-12-12 00:00:00'";
		}else if($branch_id==1){
			$create_date = " and create_date > '2019-01-30 00:00:00'";
		}else if($branch_id==12){
			$create_date = " and create_date > '2019-01-31 00:00:00'";
		}else if($branch_id==10){
			$create_date = " and create_date > '2019-02-08 00:00:00'";
		}else if($branch_id==16){
			$create_date = " and create_date > '2019-02-11 00:00:00'";
		}else if($branch_id==18){
			$create_date = " and create_date > '2019-02-12 00:00:00'";
		}else if($branch_id==17 || $branch_id==19 ){
			$create_date = " and create_date > '2019-02-23 00:00:00'";
		}else if($branch_id==3 || $branch_id==13 || $branch_id==15){
			$create_date = " and create_date > '2019-02-25 00:00:00'";
		}else if($branch_id==8){
			$create_date = " and create_date > '2019-03-01 00:00:00'";
		}else if($branch_id==2){
			$create_date = " and create_date > '2019-03-02 00:00:00'";
		}
		
		$sql="SELECT count(id) FROM rms_parking_detail WHERE branch_id = $branch_id $create_date ORDER BY id DESC limit 1";
		
		$amount=$db->fetchOne($sql);
		
		$new_amount = $amount + 1;
		
		$length = strlen($new_amount);
		
		$prefix = '';
		
		for($i=$length;$i<6;$i++){
			$prefix.='0';
		}
		return $prefix.$new_amount;
	}
	
	function getOldCustomer(){
		$db=$this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('branch_id');
		
		$sql="SELECT id,name FROM rms_parking WHERE status=1 $branch_id ";
		return $db->fetchAll($sql);
	}
	
	function getCustomerInfo($id){
		$db=$this->getAdapter();
		$sql="SELECT  
					p.customer_code,
					p.name,
					p.sex,
					p.phone,
					p.email,
					p.address,
			    	pd.*  
			    FROM 
			    	rms_parking AS p,
			    	rms_parking_detail AS pd
				WHERE 
					p.id=pd.parking_id
					AND pd.parking_id=$id
			";
		return $db->fetchRow($sql);
	}
	
	//select custoemr name
	function getAllCustomerName(){
		$db=$this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('branch_id');
		
		$sql="SELECT id,name FROM rms_parking WHERE status=1 $branch_id ";
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$order);
	}
	
	function getReilMoney(){
		$db=$this->getAdapter();
		$sql="SELECT reil FROM rms_exchange_rate WHERE active=1";
		return $db->fetchRow($sql);
	}
	
}



