<?php
class Accounting_StudentlunchpaymentController extends Zend_Controller_Action {
	protected $tr;
	const REDIRECT_URL ='/accounting';
	public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction()
    {
    	try{
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		    		if($this->getRequest()->isPost()){
    		    			$search=$this->getRequest()->getPost();
    		    		}
    		    		else{
    		    			$search = array(
    		    					'adv_search' => '',
    		    					'branch' => '',
    		    					'user' => '',
    		    					'start_date'=> date('Y-m-d'),
    		    					'end_date'=>date('Y-m-d'));
    		    		}
    		$this->view->adv_search=$search;
    		$rs_rows= $db->getAllStudenTServicePayment($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","STUDENT_ID","NAME","SEX","RECEIPT_NO","SUBTOTAL","PAID_AMOUNT","BALANCE","DATE_PAY","USER","STATUS","DELETE");
    		$link=array(
    				'module'=>'accounting','controller'=>'studentlunchpayment','action'=>'edit',
    		);
    		$link1=array(
    				'module'=>'accounting','controller'=>'studentlunchpayment','action'=>'delete',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('year'=>$link,'receipt_number'=>$link,'name'=>$link,'service_name'=>$link,'code'=>$link,'delete'=>$link1));
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
    	$forms=new Registrar_Form_FrmSearchInfor();
    	$form=$forms->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	
    }
    public function addAction()
    {
    if($this->getRequest()->isPost()){
      	$_data = $this->getRequest()->getPost();
      	try {
      		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
      		$exist = $db->addStudentLunchPayment($_data);
      		if($exist==-1){
      			Application_Form_FrmMessage::message("RECORD_EXIST");
      		}else{
	      		if(isset($_data['save_new'])){
	      			Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
	      		}else{
	      			Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/studentlunchpayment/index');
	      		}
      		}
      	} catch (Exception $e) {
      		Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
      		$err =$e->getMessage();
      		Application_Model_DbTable_DbUserLog::writeMessageError($err);
      	}
      }
       $frm = new Accounting_Form_FrmStudentServicePayment();
       $frm_register=$frm->FrmRegistarWU();
       Application_Model_Decorator::removeAllDecorator($frm_register);
       $this->view->frm_register = $frm_register;
       $key = new Application_Model_DbTable_DbKeycode();
       $this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
       $model = new Application_Form_FrmGlobal();
      
       $db = new Application_Model_DbTable_DbGlobal();
       $abc=$this->view->payment_term = $db->getAllPaymentTerm(null,null,null);
       $this->view->branch_id = $db->getAllBranch();
       
       $tr = Application_Form_FrmLanguages::getCurrentlanguage();
       $db = new Accounting_Model_DbTable_DbStudentLunchPayment();
       $this->view->rs = $db->getAllStudentCode();
       $this->view->row = $db->getAllStudentName();
       $service = $db->getAllLunchService();
       array_unshift($service, array ( 'id' => -2, 'name' => $tr->translate("ADD_NEW") ));
       array_unshift($service, array ( 'id' => -1, 'name' => $tr->translate("SELECT_SERVICE") ));
       $this->view->service = $service;
       
       $servicetype = $db->getAllServiceType();
       array_unshift($servicetype, array ( 'id' => -1, 'name' => $tr->translate("ADD_NEW") ));
       array_unshift($servicetype, array ( 'id' => '', 'name' => $tr->translate("SELECT_SERVICE") ));
       $this->view->service_type = $servicetype;
       
       $db = new Registrar_Model_DbTable_DbRegister();
       $this->view->all_product = $db->getAllProduct();
       $this->view->exchange_rate = $db->getExchangeRate();
    }
    public function editAction()
    {
    	$id=$this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    			$db->updateStudentLunchPayment($_data);
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/studentlunchpayment/index');
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/studentlunchpayment/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    			echo $err;
    		}
    	}
    	
    	$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    	$this->view->user_type = $db->getUserType();
    	$payment=$db->getStudentServicePaymentByID($id);
    	if (empty($payment)){
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),self::REDIRECT_URL . '/studentlunchpayment/index');
    		exit();
    	}
    	$this->view->row=$payment;
    	
    	$payment_detail=$db->getStudentServicePaymentDetailByID($id);
    	$this->view->detail = $payment_detail;
    	
    	
    	$frm = new Accounting_Form_FrmStudentServicePayment();
    	$frm_register=$frm->FrmRegistarWU($payment);
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    	$this->view->rs = $db->getAllStudentCode();
    	$this->view->row_name = $db->getAllStudentName();
    	$service = $db->getAllLunchService();
    	array_unshift($service, array ( 'id' => -1, 'name' => $tr->translate("SELECT_SERVICE") ));
    	$this->view->service = $service;
    	
    	$this->view->old_stu_name = $db->getAllOldStudentName($payment['student_id']);
    	$this->view->old_car_id = $db->getAllOldLunchId($payment['student_id']);
    	
    	$this->view->year = $db->getYearService();
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$abc=$this->view->payment_term = $db->getAllPaymentTerm();
    	$this->view->branch_id = $db->getAllBranch();
    	
    	$db = new Registrar_Model_DbTable_DbRegister();
    	$this->view->all_product = $db->getAllProduct();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	
    }
    
    public function deleteAction(){
    	$id=$this->getRequest()->getParam('id');
    	$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db->deleteRecord($data,$id);
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/studentlunchpayment/index');
    	}
    	$db = new Accounting_Model_DbTable_DbRegister();
    	$this->view->detail = $db->getReceiptDetail($id);
    }
    
    function getPriceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$price = $db->getAllpriceByServiceTerm($data['studentid'],$data['service'],$data['term'],$data['year']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($price));
    		exit();
    	}
    }
    
    function getPriceEditAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$price = $db->getAllpriceByServiceTermEdit($data['service'],$data['term'],$data['year']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($price));
    		exit();
    	}
    }
    
    function getStudentAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$studentinfo = $db->getAllStudentInfo($data['studentid']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($studentinfo));
    		exit();
    	}
    }
    
    
    function getServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$year = $db->getAllService($data['year']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    function getStudentIdAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$year = $db->getStudentID($data['study_year'],$data['type']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    
    function addPopupLunchServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$service = $db->addService($data);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($service));
    		exit();
    	}
    }
	
	function getServiceCateAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$service_cate = $db->getServiceCate($data['service_id']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($service_cate));
    		exit();
    	}
    }
    
    function getStartDateServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$service_start_date = $db->getServiceStartDate($data['service_id'],$data['stu_id']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($service_start_date));
    		exit();
    	}
    }
    
    function getNewLunchIdAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentLunchPayment();
    		$new_car_id = $db->getNewLunchId($data['branch_id']);
    		print_r(Zend_Json::encode($new_car_id));
    		exit();
    	}
    }
    
    
    
    function getNewStudentByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$new_student = $db->getAllNewStudentName($data['branch_id']);
    		print_r(Zend_Json::encode($new_student));
    		exit();
    	}
    }
    
    function getOldStudentByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$old_student = $db->getAllOldStudent($data['branch_id']);
    		print_r(Zend_Json::encode($old_student));
    		exit();
    	}
    }
    
    function getDropStudentByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentLunchPayment();
    		$drop_student = $db->getAllDropStudent($data['branch_id']);
    		print_r(Zend_Json::encode($drop_student));
    		exit();
    	}
    }
    
    
    
    
    
    
    
}







