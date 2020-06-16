<?php
class Allreport_Model_DbTable_DbSubmitDailyIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_submit_daily_income';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->branch_id;
	}
	
	
	
	function SubmitDailyIncome($data , $payfor_type , $degree_type){
		
		$db = $this->getAdapter();
		
		if(!empty($data['branch_id'])){
			$branch = $data['branch_id'];
		}else{
			$branch = $this->getBranchId();
		}
		
		if(!empty($data['user_id'])){
			$user = $data['user_id'];
		}else{
			$user = $this->getUserId();
		}
		
		$sql="select 
					id 
				from 
					rms_submit_daily_income 
				where 
					payfor_type = $payfor_type 
					and shift = ".$data['shift_id']."
					and branch_id = $branch
					and user_id = $user
					and for_date = '".$data['for_date']."'
					limit 1
			";		
		//echo $sql;exit();
		$exist = $db->fetchOne($sql);
		
    	$arr = array(
    			'payfor_type'	=>$payfor_type,
    			'degree_type'	=>$degree_type,
    			'total_amount'	=>$data['total_amount'],
    			'amount_usd'	=>$data['amount_usd'],
    			'amount_riel'	=>$data['amount_riel'],
    			'shift'			=>$data['shift_id'],
    			'branch_id'		=>$branch,
    			'user_id'		=>$user,
    			'for_date'		=>$data['for_date'],
    			//'create_date'	=>date("Y-m-d"),
   		);
    	
    	if(!empty($exist)){
    		$where = " id = $exist";
    		$this->update($arr, $where);
    	}else{
    		$arr['create_date']=date("Y-m-d");
    		$this->insert($arr);
    	}
    	return 0;
    } 
    
    
    function getAllSubmitDailyIncome($search){
    	$db = $this->getAdapter();
    	 
    	//print_r($search);exit();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	 
    	$sql = "SELECT
    				*,
    				(select first_name from rms_users as u where user_id = u.id limit 1) as user_name,
    				(select branch_namekh from rms_branch where br_id = branch_id limit 1) as branch_name
				    from
				    	rms_submit_daily_income 
				    where
				    	status=1
				    	and shift>0
    					$branch_id
    			";
    	
    	$where=' ';
    	 
    	$order=" order by branch_id ASC , for_date DESC , shift ASC , payfor_type ASC ";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	$from_date =(empty($search['start_date']))? '1': "for_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "for_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	 
    	if(!empty($search['user'])){
    		$where.=' AND user_id='.$search['user'];
    	}
    	if($search['branch'] > 0){
    		$where.= " AND `branch_id` = ".$search['branch'];
    	}
    	if($search['shift'] > 0){
    		$where.= " AND shift = ".$search['shift'];
    	}
    	
    	if($search['type'] > 0){
    		$where.= " AND payfor_type = ".$search['type'];
    	}
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getSubmitById($id){
    	$db=$this->getAdapter();
    	$sql=" SELECT *,(select first_name from rms_users where rms_users.id = user_id LIMIT 1) as user_name FROM rms_submit_daily_income WHERE id = $id limit 1 ";
    	return $db->fetchRow($sql);
    }
    
    function updateSubmitDailyIncome($data,$id){
    	$array = array(
    				"total_amount"	=>$data['total_amount'],
	    			"amount_usd"	=>$data['amount_usd'],
	    			"amount_riel"	=>$data['amount_riel'],
	    			"shift"			=>$data['shift'],
	    			"for_date"		=>$data['for_date'],
    			);
    	$where = " id = $id";
    	if(!empty($data['delete'])){
    		$this->delete($where);
    	}else{
    		$this->update($array, $where);
    	}
    }
    
    function getExchangeRate(){
    	$db=$this->getAdapter();
    	$sql=" select reil from rms_exchange_rate where active=1 ";
    	return $db->fetchOne($sql);
    }
    
    
}



