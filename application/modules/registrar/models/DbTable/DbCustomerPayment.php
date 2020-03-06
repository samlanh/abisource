<?php

class Registrar_Model_DbTable_DbCustomerPayment extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_customer';
 	
	public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->branch_id;
    }
    
    function getAllCustomer($search=null){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('cp.branch_id');
    	
    	$sql=" SELECT 
    				cp.id,
    				cp.rent_receipt_no,
    				c.customer_code,
    				c.first_name,
		       		c.phone,
		       		c.start_date,
		       		c.end_date,
		      		(SELECT name_en FROM rms_view WHERE key_code=cp.last_piad AND TYPE=11 LIMIT 1 ) AS `last_piad`,
		      		(SELECT CONCAT(first_name,' ',last_name) FROM rms_users WHERE rms_users.id=cp.user_id LIMIT 1) AS user_name,
		      		cp.create_date,
		      		(SELECT name_en FROM rms_view WHERE key_code=cp.status AND TYPE=1 LIMIT 1) AS `status`
		      	FROM 
		      		rms_customer AS c,
		      		rms_customer_paymentdetail AS cp
		      	WHERE 
    				c.id=cp.cus_id  
    				$branch_id
    		";
    	$where = '';
    	
    	$from_date =(empty($search['start_date']))? '1': "cp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "cp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  c.customer_code LIKE '%{$s_search}%'";
    		$s_where[]="  c.first_name LIKE '%{$s_search}%'";
    		$s_where[]="  c.phone LIKE '%{$s_search}%'";
    		$s_where[]="  c.email LIKE '%{$s_search}%'";
    		$s_where[]="  c.address LIKE '%{$s_search}%'";
    		
    		$s_where[]="  cp.rent_receipt_no LIKE '%{$s_search}%'";
    		$s_where[]="  cp.water_total LIKE '%{$s_search}%'";
    		$s_where[]="  cp.fire_total LIKE '%{$s_search}%'";
    		$s_where[]="  cp.all_total_amount LIKE '%{$s_search}%'";
    		$s_where[]="  cp.paid LIKE '%{$s_search}%'";
    		$s_where[]="  cp.balance LIKE '%{$s_search}%'";
    		$s_where[]="  cp.rent_paid LIKE '%{$s_search}%'";
    		$s_where[]="  cp.hygiene_price LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	
    	if($search["status_search"]>-1){
    		$where.=' AND cp.status='.$search["status_search"];
    	}
    	
    	if($search['cus_name']>0){
    		$where.=' AND c.id='.$search["cus_name"];
    	}
    	
    	//$group=" GROUP BY cp.rent_receipt_no ";
    	$order=" ORDER BY cp.id DESC";
    	//echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getCheckCustomer($id){
    	$db=$this->getAdapter();
    	$sql="SELECT cus_id FROM rms_customer_paymentdetail WHERE STATUS=1 AND cus_id=$id";
    	return $db->fetchRow($sql);
    }
 
	public function addCusPayment($data){
		 
		//print_r($data);exit(); 
		
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
					"branch_id" 	=> 	$this->getBranchId(),
					//"customer_code" => 	$this->getCusId(0),
					"first_name"    => 	$data["cus_name"],
					"sex"  			=> 	$data["sex"],
					"phone"  		=> 	$data["phone"],
					
// 					"booking_fee"	=> 	$data['booking_fee'],
					"start_date"	=> 	date("Y-m-d",strtotime($data['start_date'])),
					"end_date"      => 	date("Y-m-d",strtotime($data['end_date'])),

					//"email"  		=> 	$data['email'],
					"address"		=> 	$data['address'],
					"user_id"       => 	$this->getUserId(),
					"status"        => 	1,
			);
			if(!empty($data['customer_type'])){
				$arr['customer_code'] = $this->getCusId(0);
				$cus_id= $this->insert($arr); 
			}else{
				$arr['customer_code'] = $data["cus_id"];
				$where="id=".$data['old_cus'];
				$cus_id=$data['old_cus'];
				$this->update($arr, $where);
			}
			
			//check customer 
			$cus=$this->getCheckCustomer($cus_id);
			if(!empty($cus)){
				$arr_status=array(
						"last_piad"  		=> 	0,
						);
				$this->_name="rms_customer_paymentdetail";
				$where=" cus_id=".$cus['cus_id'];
				$this->update($arr_status, $where);
			}
			
			//exit();
			
			
			$arr_payment=array(
					"cus_id"     		=> 	$cus_id,
					
					"water_old_congtor" => 	$data["water_old_congtor"],
					"water_new_congtor" => 	$data['water_new_congtor'],
					"water_qty"   		=> 	$data['water_qty'],
					"water_cost"		=> 	$data['water_cost'],
					"water_total"       => 	$data['water_total'],
					"water_exc_rate"    => 	$data['water_exc_rate'],
					"water_start_date"  => 	date("Y-m-d",strtotime($data['water_start_date'])),
					"water_end_date"  	=> 	date("Y-m-d",strtotime($data['water_end_date'])),
					
					"fire_old_congtor"  => 	$data['fire_old_congtor'],
					"fire_new_congtor"  => 	$data['fire_new_congtor'],
					"fire_qty"     		=> 	$data["fire_qty"],
					"fire_cost"     	=> 	$data["fire_cost"],
					"fire_total"   		=> 	$data["fire_total"],
					"fire_exc_rate"   	=> 	$data["fire_exc_rate"],
					"fire_start_date"   => 	date("Y-m-d",strtotime($data['fire_start_date'])),
					"fire_end_date"     => 	date("Y-m-d",strtotime($data['fire_end_date'])),
					
					//"rent_date_paid"    => 	date("Y-m-d",strtotime($data['rent_date'])),
					"rent_receipt_no"   => 	$this->getReceiptNo(0),
					
					"rent_paid"     	=> 	$data["rent_paid"],
					"rent_start_date"   => 	date("Y-m-d",strtotime($data['rent_start_date'])),
					"rent_end_date"   	=> 	date("Y-m-d",strtotime($data['rent_end_date'])),
					
					"hygiene_price"     => 	$data['hygiene_price'],
					"hygiene_start_date"=> 	date("Y-m-d",strtotime($data['hygiene_start_date'])),
					"hygiene_end_date"  => 	date("Y-m-d",strtotime($data['hygiene_end_date'])),
					//"hygiene_note"  	=> 	$data['hygiene_note'],
					
					"other_price"     	=> 	$data['other_price'],
// 					"other_start_date"	=> 	date("Y-m-d",strtotime($data['other_start_date'])),
// 					"other_end_date"  	=> 	date("Y-m-d",strtotime($data['other_end_date'])),
					//"other_note"  		=> 	$data['other_note'],
					
					"create_date"		=>	date('Y-m-d H:i:s'),
					
					"all_total_amount"  => 	$data["all_total_amount"],
					"paid"     			=> 	$data["paid"],
					"balance"     		=> 	$data["balance"],
					
					"note"  			=> 	$data['note'],
					
					"status"  			=> 	1,
					
					"branch_id"     	=> 	$this->getBranchId(),
					"user_id"     		=> 	$this->getUserId(),
					
					"last_piad"  		=> 	1,
			);
			$this->_name="rms_customer_paymentdetail";
			$this->insert($arr_payment);
			
			$db->commit();
			
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}
	 
	public function editCustomerPayment($data){
		 
		//print_r($data);exit();
		
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
					"customer_code" => 	$data["cus_id"],
					"first_name"    => 	$data["cus_name"],
					"sex"  			=> 	$data["sex"],
					"phone"  		=> 	$data["phone"],
					
					//"booking_fee"	=> 	$data['booking_fee'],
					"start_date"	=> 	date("Y-m-d",strtotime($data['start_date'])),
					"end_date"      => 	date("Y-m-d",strtotime($data['end_date'])),
					
					//"email"  		=> 	$data['email'],
					"address"		=> 	$data['address'],
					"user_id"       => 	$this->getUserId(),
					//"status"        => 	$data['status'],
			);
			
			$where=" id = ".$data['customer_id'];
			$this->update($arr, $where);
			
			
			$this->_name="rms_customer_paymentdetail";
				
			$status = 1;
			if(!empty($data['is_void'])){
				$status = 0;
			}
			$array = array(
					'status'=>$status
			);
			$where = " id = ".$data['id'];
			$this->update($array, $where);
			
			/*/check customer
			$cus=$this->getCheckCustomer($cus_id);
			if(!empty($cus)){
				$arr_status=array(
						"last_piad"  	=> 	0,
				);
				$this->_name="rms_customer_paymentdetail";
				$where=" cus_id=".$cus['cus_id'];
				$this->update($arr_status, $where);
			}
			$arr_payment=array(
					"cus_id"     		=> 	$data['customer_id'],
					"water_old_congtor" => 	$data["water_old_congtor"],
					"water_new_congtor" => 	$data['water_new_congtor'],
					"water_qty"   		=> 	$data['water_qty'],
					"water_cost"		=> 	$data['water_cost'],
					"water_total"       => 	$data['water_total'],
					"water_exc_rate"    => 	$data['water_exc_rate'],
					"water_start_date"  => 	date("Y-m-d",strtotime($data['water_start_date'])),
					"water_end_date"  	=> 	date("Y-m-d",strtotime($data['water_end_date'])),
						
					"fire_old_congtor"  => 	$data['fire_old_congtor'],
					"fire_new_congtor"  => 	$data['fire_new_congtor'],
					"fire_qty"     		=> 	$data["fire_qty"],
					"fire_cost"     	=> 	$data["fire_cost"],
					"fire_total"   		=> 	$data["fire_total"],
					"fire_exc_rate"   	=> 	$data["fire_exc_rate"],
					"fire_start_date"   => 	date("Y-m-d",strtotime($data['fire_start_date'])),
					"fire_end_date"     => 	date("Y-m-d",strtotime($data['fire_end_date'])),
						
					//"rent_date_paid"    => 	date("Y-m-d",strtotime($data['rent_date'])),
					"rent_receipt_no"   => 	$data['receipt_no'],
					"rent_paid"     	=> 	$data["rent_paid"],
					"rent_start_date"   => 	date("Y-m-d",strtotime($data['rent_start_date'])),
					"rent_end_date"   	=> 	date("Y-m-d",strtotime($data['rent_end_date'])),
						
					"hygiene_price"     => 	$data['hygiene_price'],
					"hygiene_start_date"=> 	date("Y-m-d",strtotime($data['hygiene_start_date'])),
					"hygiene_end_date"  => 	date("Y-m-d",strtotime($data['hygiene_end_date'])),
					//"hygiene_note"  	=> 	$data['hygiene_note'],
					
					"other_price"     	=> 	$data['other_price'],
					"other_start_date"	=> 	date("Y-m-d",strtotime($data['other_start_date'])),
					"other_end_date"  	=> 	date("Y-m-d",strtotime($data['other_end_date'])),
					//"other_note"  		=> 	$data['other_note'],
					
					"all_total_amount"  => 	$data["all_total_amount"],
					"paid"     			=> 	$data["paid"],
					"balance"     		=> 	$data["balance"],
					"status"  			=> 	$data["status"],
					"note"  			=> 	$data['note'],
					
					"branch_id"     	=> 	$this->getBranchId(),
					"user_id"     		=> 	$this->getUserId(),
					"last_piad"  		=> 	1,
			);
			$this->_name="rms_customer_paymentdetail";
			$where=" id = ".$data['id'];
			$this->update($arr_payment, $where);
			*/
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
	
	function getCustomerById($id){
		$db=$this->getAdapter();
		$sql="SELECT c.*, c.customer_code,c.first_name,c.sex,c.phone,c.email,c.address,c.start_date,c.end_date,
		      cp.*,cp.cus_id As customer_id FROM rms_customer AS c,rms_customer_paymentdetail AS cp
				WHERE c.id=cp.cus_id
				AND cp.id=$id";
		return $db->fetchRow($sql);
	}
	
	function getCusId($branch_id){
		$db=$this->getAdapter();
		
		if($branch_id>0){
			$branch = $branch_id;
		}else{
			$branch = $this->getBranchId();
		}
		
		$sql="SELECT count(id) FROM rms_customer where branch_id = $branch limit 1 ";
		$amount=$db->fetchOne($sql);
		
		$new_amount = $amount + 1;
		
		$length = strlen($new_amount);
		
		$prefix = 'C';
		
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
		
		$sql="SELECT count(id) FROM rms_customer_paymentdetail WHERE branch_id = $branch_id $create_date limit 1 ";
		$amount=$db->fetchOne($sql);
		
		$new_amount = $amount + 1;
		
		$length = strlen($new_amount);
		
		$prefix = 'R';
		
		for($i=$length;$i<6;$i++){
			$prefix.='0';
		}
		return $prefix.$new_amount;
	}
	
	function getOldCustomer(){
		$db=$this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('branch_id');
		
		$sql="SELECT id,first_name AS cus_name FROM rms_customer WHERE status=1 $branch_id ";
		return $db->fetchAll($sql);
	}
	
	function getCustomerInfo($id){
		$db=$this->getAdapter();
		$sql="SELECT  
					c.customer_code,
					c.first_name,
					c.sex,
					c.phone,
					c.email,
					c.address,
					c.start_date,
					c.end_date,
					c.*,
			    	cp.*  
			    FROM 
			    	rms_customer AS c,rms_customer_paymentdetail AS cp
				WHERE 
					c.id=cp.cus_id
					AND cp.last_piad=1
					AND cp.cus_id=$id
			";
		return $db->fetchRow($sql);
	}
	
	//select custoemr name
	function getAllCustomerName(){
		$db=$this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('branch_id');
		
		$sql="SELECT id,first_name AS `name` FROM rms_customer WHERE status=1 $branch_id ";
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$order);
	}
	
	function getReilMoney(){
		$db=$this->getAdapter();
		$sql="SELECT reil FROM rms_exchange_rate WHERE active=1";
		return $db->fetchRow($sql);
	}
	
}



