<?php
class Accounting_StudentservicepaymentController extends Zend_Controller_Action {
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
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    						'adv_search' => '',
    		    			'branch' => '',
    		    			'user' => '',
    		    			'start_date'=> date('Y-m-d'),
    		    			'end_date'=>date('Y-m-d')
    					);
    		}
    		$this->view->adv_search=$search;
    		$rs_rows= $db->getAllStudenTServicePayment($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","STUDENT_ID","NAME","SEX","RECEIPT_NO","SUBTOTAL","PAID_AMOUNT","BALANCE","DATE_PAY","USER","STATUS","DELETE");
    		$link=array(
    				'module'=>'accounting','controller'=>'studentservicepayment','action'=>'edit',
    		);
    		$link1=array(
    				'module'=>'accounting','controller'=>'studentservicepayment','action'=>'delete',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('year'=>$link,'receipt_number'=>$link,'name'=>$link,'service_name'=>$link,'code'=>$link,'delete'=>$link1));
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
	      		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
	      		$exist = $db->addStudentServicePayment($_data);
	      		if($exist==-1){
	      			Application_Form_FrmMessage::message("RECORD_EXIST");
	      		}else{
		      		if(isset($_data['save_new'])){
		      			Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
		      		}else{
		      			Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/studentservicepayment/index');
		      		}
	      		}
	      	} catch (Exception $e){
	      		Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
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
	       $abc=$this->view->payment_term = $db->getAllPaymentTerm(null,null,1);
	       $this->view->branch_id = $db->getAllBranch();
	       
	       $db = new Accounting_Model_DbTable_DbStudentServicePayment();

	       $this->view->rs = $db->getAllStudentCode();
	       $this->view->row = $db->getAllStudentName();
	       
	       $tr = Application_Form_FrmLanguages::getCurrentlanguage();
	       $servicetype = $db->getAllServiceType();
	       array_unshift($servicetype, array ( 'id' => -1, 'name' => $tr->translate("ADD_NEW")) );
	       array_unshift($servicetype, array ( 'id' => '', 'name' => $tr->translate("SELECT_SERVICE")) );
	       $this->view->service_type = $servicetype;
	       
	       
	       $db = new Registrar_Model_DbTable_DbRegister();
	       $this->view->exchange_rate = $db->getExchangeRate();
    }
    public function editAction()
    {
    	$id=$this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    			$db->updateStudentServicePayment($_data);
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/studentservicepayment/index');
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/studentservicepayment/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	
    	$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    	
    	$this->view->user_type = $db->getUserType();
    	$payment=$db->getStudentServicePaymentByID($id);
    	if (empty($payment)){
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),self::REDIRECT_URL . '/studentservicepayment/index');
    		exit();
    	}
    	$this->view->row=$payment;
    	
    	$payment_detail=$db->getStudentServicePaymentDetailByID($id);
    	$this->view->detail = $payment_detail;
    	
    	$frm = new Accounting_Form_FrmStudentServicePayment();
    	$frm_register=$frm->FrmRegistarWU($payment);
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    	$this->view->rs = $db->getAllStudentCode();
    	$this->view->row_name = $db->getAllStudentName();
    	$service = $db->getAllService($payment['branch_id']);
    	array_unshift($service, array ( 'id' => -1, 'name' => $tr->translate("SELECT_SERVICE")) );
    	$this->view->service = $service;
    	$this->view->all_car = $db->getAllCar($payment['branch_id']);
    	
    	$this->view->all_stu_name =  $db->getAllStudentName();
    	$this->view->old_stu_name = $db->getAllOldStudentName($payment['student_id']);
    	$this->view->old_car_id = $db->getAllOldCarId($payment['student_id']);
    	
    	$this->view->year = $db->getYearService();
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$abc=$this->view->payment_term = $db->getAllPaymentTerm();
    	$this->view->branch_id = $db->getAllBranch();
    	
    	$db = new Registrar_Model_DbTable_DbCourStudey();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	
    }
    
    public function deleteAction(){
    	$id=$this->getRequest()->getParam('id');
    	$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db->deleteRecord($data,$id);
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/studentservicepayment/index');
    	}
    	$db = new Accounting_Model_DbTable_DbRegister();
    	$this->view->detail = $db->getReceiptDetail($id);
    }
    
    
    function getGradeAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$grade = $db->getAllGrade($data['dept_id']);
    		print_r(Zend_Json::encode($grade));
    		exit();
    	}
    }
    
    function getPriceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$price = $db->getAllpriceByServiceTerm($data['studentid'],$data['service'],$data['term'],$data['year']);
    		print_r(Zend_Json::encode($price));
    		exit();
    	}
    }
    
    function getPriceEditAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$price = $db->getAllpriceByServiceTermEdit($data['service'],$data['term'],$data['year']);
    		print_r(Zend_Json::encode($price));
    		exit();
    	}
    }
    
    function getStudentAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$studentinfo = $db->getAllStudentInfo($data['studentid']);
    		print_r(Zend_Json::encode($studentinfo));
    		exit();
    	}
    }
    
    
    function getServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$year = $db->getAllService($data['year']);
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    function getStudentIdAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$year = $db->getStudentID($data['study_year'],$data['type']);
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    
    function addPopupServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$service = $db->addService($data);
    		print_r(Zend_Json::encode($service));
    		exit();
    	}
    }
	
	function getServiceCateAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$service_cate = $db->getServiceCate($data['service_id']);
    		print_r(Zend_Json::encode($service_cate));
    		exit();
    	}
    }
    
    function getStartDateServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$service_start_date = $db->getServiceStartDate($data['service_id'],$data['stu_id']);
    		print_r(Zend_Json::encode($service_start_date));
    		exit();
    	}
    }
    
    function getYearByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbCourStudey();
    		$year = $db->getAllYearByBranch($data['branch_id']);
    		
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		array_unshift($year, array ( 'id' => -1, 'name' => $tr->translate("SELECT_ACADEMIC_YEAR")) );
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    
    function getNewCarIdAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$new_car_id = $db->getNewCarId($data['branch_id']);
    		print_r(Zend_Json::encode($new_car_id));
    		exit();
    	}
    }
    
    function getCarIdByServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$car_id = $db->getCarIdByService($data['service_id']);
    		print_r(Zend_Json::encode($car_id));
    		exit();
    	}
    }
    

    function getNewStudentByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$new_student = $db->getAllNewStudentName($data['branch_id']);
    		print_r(Zend_Json::encode($new_student));
    		exit();
    	}
    }
    
    function getOldStudentByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$old_student = $db->getAllOldStudent($data['branch_id']);
    		print_r(Zend_Json::encode($old_student));
    		exit();
    	}
    }
    
    function getDropStudentByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$drop_student = $db->getAllDropStudent($data['branch_id']);
    		print_r(Zend_Json::encode($drop_student));
    		exit();
    	}
    }
    
    function getServiceYearByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$year = $db->getAllServiceYear($data['branch_id']);
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    
    function getServiceByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$year = $db->getAllService($data['branch_id']);
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    
    function getCarByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbStudentServicePayment();
    		$year = $db->getAllCar($data['branch_id']);
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
}










