<?php
class Registrar_CustomerPaymentController extends Zend_Controller_Action {
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
    					'title'	        =>'',
    					'cus_name'		=>0,
    					'status_search'	=>-1,
    					'start_date'	=> date("Y-m-d"),
    					'end_date'		=> date("Y-m-d"),
				);
    		}
			$db = new Registrar_Model_DbTable_DbCustomerPayment();
			$rs_rows = $db->getAllCustomer($search);
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("RECEIPT_NO","CUS_ID","CUS_NAME","PHONE","START_DATE","END_DATE","STATUS_PAID","USER","CREATE_DATE","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'customerpayment','action'=>'edit',
    		);
    		$link1=array(
    				'module'=>'registrar','controller'=>'customerpayment','action'=>'view',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('customer_code'=>$link,'rent_receipt_no'=>$link,'first_name'=>$link,'phone'=>$link,'view'=>$link1));
			
			$db = new Registrar_Model_DbTable_DbRegister();
			$this->view->all_student_name = $db->getAllGerneralOldStudentName();
			$this->view->all_student_code = $db->getAllGerneralOldStudent();
		}catch (Exception $e){
			echo $e->getMessage();
		}
		
		$frm_major = new Accounting_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
    public function addAction()
    {
    	$db = new Registrar_Model_DbTable_DbCustomerPayment();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->addCusPayment($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/customerpayment/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/customerpayment/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $_db->getBranchInfo();
    }
    
    public function adddataonlyAction()
    {
    	$db = new Registrar_Model_DbTable_DbCustomerPayment();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->addCusPayment($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/customerpayment/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/customerpayment/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	 
    	//$this->view->cus_id=$db->getCusId();
    	//$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $_db->getBranchInfo();
    }
    
    public function addblankAction()
    {
    	$db = new Registrar_Model_DbTable_DbCustomerPayment();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->addCusPayment($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/customerpayment/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), "/registrar/customerpayment/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    
    	//$this->view->cus_id=$db->getCusId();
    	//$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $_db->getBranchInfo();
    }
    
	public function editAction(){
    	$db = new Registrar_Model_DbTable_DbCustomerPayment();
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
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
    	if($row['last_piad']==0){
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate('Can not edit.!!!'), "/registrar/customerpayment/index");
    	}
    	//$this->view->cus_id=$db->getCusId();
    	//$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $_db->getBranchInfo();
    }
    
    public function viewAction(){
    	$db = new Registrar_Model_DbTable_DbCustomerPayment();
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
    	//$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $_db->getBranchInfo();
    }
    
    function getCustomerInfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbCustomerPayment();
    		$cus_info= $db->getCustomerInfo($data['cus_id']);
    		print_r(Zend_Json::encode($cus_info));
    		exit();
    	}
    }
    
    
    function getReceiptNoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbCustomerPayment();
    		$receipt_no= $db->getReceiptNo($data['branch_id'],$data['payment_method']);
    		print_r(Zend_Json::encode($receipt_no));
    		exit();
    	}
    }
    
    function getCustomerNoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbCustomerPayment();
    		$cus_id= $db->getCusId($data['branch_id']);
    		print_r(Zend_Json::encode($cus_id));
    		exit();
    	}
    }
    
}
