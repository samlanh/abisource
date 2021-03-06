<?php
class Registrar_StudentservicepaymentController extends Zend_Controller_Action {
	protected $tr;
	const REDIRECT_URL ='/registrar';
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
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		    		if($this->getRequest()->isPost()){
    		    			$search=$this->getRequest()->getPost();
    		    		}
    		    		else{
    		    			$search = array(
    		    					'adv_search' => '',
    		    					'user' => '',
    		    					'start_date'=> date('Y-m-d'),
    		    					'end_date'=>date('Y-m-d')
    		    			);
    		    		}
    		$this->view->adv_search=$search;
    		$rs_rows= $db->getAllStudenTServicePayment($search);
    		
    		$list = new Application_Form_Frmtable();
    		$collumns = array("STUDENT_ID","NAME","SEX","RECEIPT_NO","SUBTOTAL","PAID_AMOUNT","BALANCE","DATE_PAY","USER","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'studentservicepayment','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('year'=>$link,'receipt_number'=>$link,'name'=>$link,'service_name'=>$link,'code'=>$link));
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$forms=new Registrar_Form_FrmSearchInfor();
    	$form=$forms->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	$_db = new Registrar_Model_DbTable_DbStudentServicePayment();
    }
    public function addAction()
    {
    if($this->getRequest()->isPost()){
      	$_data = $this->getRequest()->getPost();
      	try {
      		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
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
      	} catch (Exception $e) {
      		Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
      	}
      }
       $frm = new Registrar_Form_FrmStudentServicePayment();
       $frm_register=$frm->FrmRegistarWU();
       Application_Model_Decorator::removeAllDecorator($frm_register);
       $this->view->frm_register = $frm_register;
       $key = new Application_Model_DbTable_DbKeycode();
       $this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
       $model = new Application_Form_FrmGlobal();
      
       $db = new Application_Model_DbTable_DbGlobal();
       $abc=$this->view->payment_term = $db->getAllPaymentTerm(null,null,null);
       
       $tr = Application_Form_FrmLanguages::getCurrentlanguage();
       $db = new Registrar_Model_DbTable_DbStudentServicePayment();
       $this->view->rs = $db->getAllStudentCode();
       $this->view->row = $db->getAllStudentName();
       $service = $db->getAllService();
       array_unshift($service, array ( 'id' => -2, 'name' => $tr->translate("ADD_NEW") ) );
       array_unshift($service, array ( 'id' => -1, 'name' => $tr->translate("SELECT_SERVICE") ) );
       $this->view->service = $service;
       
       $this->view->all_car = $db->getAllCar();
       
       $servicetype = $db->getAllServiceType();
       array_unshift($servicetype, array ( 'id' => -1, 'name' => $tr->translate("ADD_NEW") ) );
       array_unshift($servicetype, array ( 'id' => '', 'name' => $tr->translate("SELECT_SERVICE") ) );
       $this->view->service_type = $servicetype;
       
       $this->view->new_stu_name =  $db->getAllNewStudentName();
//        print_r($db->getAllNewStudentName());exit();
       
       $this->view->old_stu_name = $db->getAllOldStudentName();
       $this->view->old_car_id = $db->getAllOldCarId();
       
       $this->view->drop_stu_name = $db->getAllDropStudentName();
       $this->view->drop_car_id = $db->getAllDropCarId();
       
       
       $db = new Registrar_Model_DbTable_DbRegister();
       $this->view->all_product = $db->getAllProduct();
       $this->view->exchange_rate = $db->getExchangeRate();
       
       $dbg = new Application_Model_DbTable_DbGlobal();
       $this->view->branch_info = $dbg->getBranchInfo();
       
    }
    public function adddataonlyAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Registrar_Model_DbTable_DbStudentServicePayment();
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
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			echo $e->getMessage();exit();
    		}
    	}
    	$frm = new Registrar_Form_FrmStudentServicePayment();
    	$frm_register=$frm->FrmRegistarWU();
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
    	$model = new Application_Form_FrmGlobal();
    
    	$db = new Application_Model_DbTable_DbGlobal();
    	$abc=$this->view->payment_term = $db->getAllPaymentTerm(null,null,null);
    	 
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    	$this->view->rs = $db->getAllStudentCode();
    	$this->view->row = $db->getAllStudentName();
    	$service = $db->getAllService();
    	array_unshift($service, array ( 'id' => -2, 'name' => $tr->translate("ADD_NEW")) );
    	array_unshift($service, array ( 'id' => -1, 'name' => $tr->translate("SELECT_SERVICE") ) );
    	$this->view->service = $service;
    	 
    	$this->view->all_car = $db->getAllCar();
    	 
    	$servicetype = $db->getAllServiceType();
    	array_unshift($servicetype, array ( 'id' => -1, 'name' => $tr->translate("ADD_NEW")) );
    	array_unshift($servicetype, array ( 'id' => '', 'name' => $tr->translate("SELECT_SERVICE")) );
    	$this->view->service_type = $servicetype;
    	 
    	$this->view->new_stu_name =  $db->getAllNewStudentName();
    	//        print_r($db->getAllNewStudentName());exit();
    	 
    	$this->view->old_stu_name = $db->getAllOldStudentName();
    	$this->view->old_car_id = $db->getAllOldCarId();
    	 
    	$this->view->drop_stu_name = $db->getAllDropStudentName();
    	$this->view->drop_car_id = $db->getAllDropCarId();
    	 
    	 
    	$db = new Registrar_Model_DbTable_DbRegister();
    	$this->view->all_product = $db->getAllProduct();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	 
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    	 
    }
    
    public function addblankAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Registrar_Model_DbTable_DbStudentServicePayment();
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
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			echo $e->getMessage();exit();
    		}
    	}
    	$frm = new Registrar_Form_FrmStudentServicePayment();
    	$frm_register=$frm->FrmRegistarWU();
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
    	$model = new Application_Form_FrmGlobal();
    
    	$db = new Application_Model_DbTable_DbGlobal();
    	$abc=$this->view->payment_term = $db->getAllPaymentTerm(null,null,null);
    
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    	$this->view->rs = $db->getAllStudentCode();
    	$this->view->row = $db->getAllStudentName();
    	$service = $db->getAllService();
    	array_unshift($service, array ( 'id' => -2, 'name' => $tr->translate("ADD_NEW")) );
    	array_unshift($service, array ( 'id' => -1, 'name' => $tr->translate("SELECT_SERVICE") ) );
    	$this->view->service = $service;
    
    	$this->view->all_car = $db->getAllCar();
    
    	$servicetype = $db->getAllServiceType();
    	array_unshift($servicetype, array ( 'id' => -1, 'name' => $tr->translate("ADD_NEW")) );
    	array_unshift($servicetype, array ( 'id' => '', 'name' => $tr->translate("SELECT_SERVICE")) );
    	$this->view->service_type = $servicetype;
    
    	$this->view->new_stu_name =  $db->getAllNewStudentName();
    	//        print_r($db->getAllNewStudentName());exit();
    
    	$this->view->old_stu_name = $db->getAllOldStudentName();
    	$this->view->old_car_id = $db->getAllOldCarId();
    
    	$this->view->drop_stu_name = $db->getAllDropStudentName();
    	$this->view->drop_car_id = $db->getAllDropCarId();
    
    
    	$db = new Registrar_Model_DbTable_DbRegister();
    	$this->view->all_product = $db->getAllProduct();
    	$this->view->exchange_rate = $db->getExchangeRate();
    
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    
    }
    
    public function editAction()
    {
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    			$db->updateStudentServicePayment($_data);
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/studentservicepayment/index');
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/studentservicepayment/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    			echo $err;
    		}
    	}
    	
    	$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    	$payment=$this->view->row=$db->getStudentServicePaymentByID($id);
    	
    	if($payment['is_start']==0){
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate('Can note Edit'), self::REDIRECT_URL . '/studentservicepayment/index');
    	}
    	
    	if($payment['buy_product']==1){
    		$this->view->row_product = $db->getStudentBuyProductById($id);
    	}
    	
    	$payment_detail=$db->getStudentServicePaymentDetailByID($id);
    	$this->view->detail = $payment_detail;
    	$this->view->user_type = $db->getUserType();
    	$frm = new Registrar_Form_FrmStudentServicePayment();
    	$frm_register=$frm->FrmRegistarWU($payment);
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    	$service = $db->getAllService();
    	array_unshift($service, array ('id' => -1, 'name' => $tr->translate("SELECT_SERVICE")) );
    	$this->view->service = $service;
    	
    	$this->view->all_car = $db->getAllCar();
    	
    	$this->view->all_stu_name =  $db->getAllStudentName();
    	$this->view->old_stu_name = $db->getAllOldStudentName();
    	$this->view->old_car_id = $db->getAllOldCarId();
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$abc=$this->view->payment_term = $db->getAllPaymentTerm();
    	
    	$db = new Registrar_Model_DbTable_DbCourStudey();
    	$this->view->all_product = $db->getAllProduct();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
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
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$price = $db->getAllpriceByServiceTerm($data['studentid'],$data['service'],$data['term'],$data['year']);
    		print_r(Zend_Json::encode($price));
    		exit();
    	}
    }
    
    function getPriceEditAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$price = $db->getAllpriceByServiceTermEdit($data['service'],$data['term'],$data['year']);
    		print_r(Zend_Json::encode($price));
    		exit();
    	}
    }
    
    function getStudentAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$studentinfo = $db->getAllStudentInfo($data['studentid']);
    		print_r(Zend_Json::encode($studentinfo));
    		exit();
    	}
    }
    
    
    function getServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$year = $db->getAllService($data['year']);
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    function getStudentIdAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$year = $db->getStudentID($data['study_year'],$data['type']);
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    
    function addPopupServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$service = $db->addService($data);
    		print_r(Zend_Json::encode($service));
    		exit();
    	}
    }
	
	function getServiceCateAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$service_cate = $db->getServiceCate($data['service_id']);
    		print_r(Zend_Json::encode($service_cate));
    		exit();
    	}
    }
    
    function getStartDateServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$service_start_date = $db->getServiceStartDate($data['service_id'],$data['stu_id']);
    		print_r(Zend_Json::encode($service_start_date));
    		exit();
    	}
    }
    
    function getNewCarIdAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$new_car_id = $db->getNewCarId(0);
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
    
    
    
    
    
}







