<?php
class Registrar_ParkingPaymentController extends Zend_Controller_Action {
	protected $tr;
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
	}
	
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    					'title'	        =>	'',
    					'cus_name'		=>	"",
    					'start_date'	=>	date("Y-m-d"),
    					'end_date'		=>	date("Y-m-d"),
				);
    		}
			$db = new Registrar_Model_DbTable_DbParkingPayment();
			$rs_rows = $db->getAllParkingPayment($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$this->view->old_cus = $db->getAllCustomerName();
			$this->view->search = $search;
			$list = new Application_Form_Frmtable();
    		$collumns = array("RECEIPT_NO","CUS_ID","NAME","PHONE","ថ្លៃកិបសំបុត្រម៉ូតូ","ថ្លៃកិបសំបុត្រកង់","លក់អេតចាយ","TOTAL","NOTE","USER","CREATE_DATE","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'parkingpayment','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('customer_code'=>$link,'receipt_no'=>$link,'name'=>$link,'phone'=>$link));
			
			$db = new Registrar_Model_DbTable_DbRegister();
			$this->view->all_student_name = $db->getAllGerneralOldStudentName();
			$this->view->all_student_code = $db->getAllGerneralOldStudent();
		}catch (Exception $e){
			echo $e->getMessage();
		}
		
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
    public function addAction()
    {
    	$db = new Registrar_Model_DbTable_DbParkingPayment();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->addParkingPayment($_data);
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/parkingpayment/index");
    				exit();
    			}else{
    				Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	
    	$this->view->cus_id=$db->getCusId(0);
    	$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    }
    
    public function adddataonlyAction()
    {
    	$db = new Registrar_Model_DbTable_DbParkingPayment();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->addParkingPayment($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/parkingpayment/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/parkingpayment/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	 
    	$this->view->cus_id=$db->getCusId();
    	$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    	 
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    }
    
    public function addblankAction()
    {
    	$db = new Registrar_Model_DbTable_DbParkingPayment();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->addParkingPayment($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/parkingpayment/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/parkingpayment/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    
    	$this->view->cus_id=$db->getCusId();
    	$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    }
    
    
	public function editAction(){
    	$db = new Registrar_Model_DbTable_DbParkingPayment();
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->editCustomerPayment($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/parkingpayment/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/parkingpayment/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$row=$this->view->row=$db->getParkingById($id);
    	
    	$this->view->cus_id=$db->getCusId();
    	$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    }
    
    public function viewAction(){
    	$db = new Registrar_Model_DbTable_DbParkingPayment();
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_data['id']=$id;
    		try {
    			$db->editCustomerPayment($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/customerpayment/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/customerpayment/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$row=$this->view->row=$db->getCustomerById($id);
//     	if($row['last_piad']==0){
//     		Application_Form_FrmMessage::Sucessfull($this->tr->translate('Can not edit.!!!'), "/accounting/customerpayment/index");
//     	}
    	$this->view->cus_id=$db->getCusId();
    	$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    }
    
    function getCustomerInfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbParkingPayment();
    		$cus_info= $db->getCustomerInfo($data['cus_id']);
    		print_r(Zend_Json::encode($cus_info));
    		exit();
    	}
    }
}
