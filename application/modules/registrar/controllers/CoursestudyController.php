<?php
class Registrar_CoursestudyController extends Zend_Controller_Action {
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
    		$db = new Registrar_Model_DbTable_DbCourStudey();
    		    		if($this->getRequest()->isPost()){
    		    			$search=$this->getRequest()->getPost();
    		    		}
    		    		else{
    		    			$search = array(
    		    					'adv_search' => '',
    		    					'study_year' => '',
    		    					'degree_gep' => '',
    		    					'session'=>'',
    		    					'grade'=>'',
    		    					'user' => '',
    		    					'start_date'=> date('Y-m-d'),
    		    					'end_date'=>date('Y-m-d'));
    		    		}
    		$this->view->adv_search=$search;
    		$rs_rows= $db->getAllStudentGep($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getGernder($rs_rows, BASE_URL);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("STUDENT_ID","NAME_KH","NAME_EN","SEX","DEGREE","CLASS","RECEIPT_NO",
    				          "SUBTOTAL","PAID_AMOUNT","BALANCE","DATE_PAY","USER","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'coursestudy','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('stu_code'=>$link,'receipt_number'=>$link,'stu_khname'=>$link,'stu_enname'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$db=new Registrar_Model_DbTable_DbCourStudey();
    	$this->view->rows_deg=$db->getDegree();
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    public function addAction()
    {
    if($this->getRequest()->isPost()){
      	$_data = $this->getRequest()->getPost();
      	try {
      		$db = new Registrar_Model_DbTable_DbCourStudey();
      		$db->addStudentGep($_data);
      		if(isset($_data['save_new'])){
      			Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
      		}else{
      			Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
      		}
      	} catch (Exception $e) {
      		Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
      	}
      }
       $frm = new Registrar_Form_FrmCourseStudy();
       $frm_register=$frm->FrmRegistarWU();
       Application_Model_Decorator::removeAllDecorator($frm_register);
       $this->view->frm_register = $frm_register;
       
       $_db = new Application_Model_DbTable_DbGlobal();
       $this->view->all_dept = $_db->getAllFecultyNamess(2);
       
       $db = new Registrar_Model_DbTable_DbCourStudey();
       $this->view->all_product = $db->getAllProduct();
       $this->view->exchange_rate = $db->getExchangeRate();
       $this->view->room = $db->getAllRoom();
       
       $dbg = new Application_Model_DbTable_DbGlobal();
       $this->view->branch_info = $dbg->getBranchInfo();
       $this->view->all_time = $dbg->getAllTime(2);
       
       $key = new Application_Model_DbTable_DbKeycode();
       $this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    
    public function adddataonlyAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Registrar_Model_DbTable_DbCourStudey();
    			$db->addStudentGep($_data);
    			if(isset($_data['save_new'])){
    				Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			echo $e->getMessage();exit();
    		}
    	}
    	$frm = new Registrar_Form_FrmCourseStudy();
    	$frm_register=$frm->FrmRegistarWU();
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->all_dept = $_db->getAllFecultyNamess(2);
    	 
    	$db = new Registrar_Model_DbTable_DbCourStudey();
    	$this->view->all_product = $db->getAllProduct();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	$this->view->room = $db->getAllRoom();
    	 
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    	$this->view->all_time = $dbg->getAllTime(2);
    	 
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    
    public function addblankAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		//       	print_r($_data);exit();
    		try {
    			$db = new Registrar_Model_DbTable_DbCourStudey();
    			$db->addStudentGep($_data);
    			if(isset($_data['save_new'])){
    				Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			echo $e->getMessage();exit();
    		}
    	}
    	$frm = new Registrar_Form_FrmCourseStudy();
    	$frm_register=$frm->FrmRegistarWU();
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->all_dept = $_db->getAllFecultyNamess(2);
    
    	$db = new Registrar_Model_DbTable_DbCourStudey();
    	$this->view->all_product = $db->getAllProduct();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	$this->view->room = $db->getAllRoom();
    
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    	$this->view->all_time = $dbg->getAllTime(2);
    
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    
    public function editAction()
    {
    	$id=$this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Registrar_Model_DbTable_DbCourStudey();
    			if(isset($_data['save_new'])){
    				$db->updateStudentGep($_data);
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
    			}else{
    				$db->updateStudentGep($_data);
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('EDIT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$db = new Registrar_Model_DbTable_DbCourStudey();
    	$row_gep=$db->getStuentGepById($id);
    	if (empty($row_gep)){
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),self::REDIRECT_URL . '/coursestudy/index');
    		exit();
    	}
    	$is_start=$row_gep['is_start'];
    	if($is_start==0){
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate('Can not edit!'), self::REDIRECT_URL . '/coursestudy/index');
    	}
    	$this->view->row_gep=$row_gep;
    	$frm = new Registrar_Form_FrmCourseStudy();
    	$frm_register=$frm->FrmRegistarWU($row_gep);
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	
    	$this->view->user_type = $db->getUserType();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->all_dept = $_db->getAllFecultyNamess(2);
    	
    	$db = new Registrar_Model_DbTable_DbCourStudey();
    	$this->view->all_product = $db->getAllProduct();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	
    	
    	$db = new Accounting_Model_DbTable_DbRegister();
    	$this->view->room = $db->getAllRoom();
    	if($row_gep['buy_product']==1){
    		$this->view->row_product = $db->getStudentBuyProductById($id);
    	}
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    	$this->view->all_time = $dbg->getAllTime(2);
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    
    public function addoldreceiptAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		//       	print_r($_data);exit();
    		try {
    			$db = new Registrar_Model_DbTable_DbCourStudey();
    			$db->addStudentGep($_data);
    			if(isset($_data['save_new'])){
    				Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$frm = new Registrar_Form_FrmCourseStudy();
    	$frm_register=$frm->FrmRegistarWU();
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->all_dept = $_db->getAllFecultyNamess(2);
    	 
    	$db = new Registrar_Model_DbTable_DbCourStudey();
    	$this->view->all_product = $db->getAllProduct();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	$this->view->room = $db->getAllRoom();
    	 
    	 
    }
    
    public function getStudentinfoallAction(){
    	if($this->getRequest()->isPost()){
    		$_db = new Registrar_Model_DbTable_DbGetStudentInfo();
    		$_rs_student = $_db->getAllStudent();
    		print_r(Zend_Json::encode($_rs_student));
    		exit();
    	}
    }
    function getPaymentGepAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbCourStudey();
    		$payment = $db->getPaymentGep($data['study_year'],$data['levele'],$data['payment_term']);
    		print_r(Zend_Json::encode($payment));
    		exit();
    	}
    }
    function getGepOldStudentAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$gep = $db->getGepOldStudent($data['student_id']);
    		print_r(Zend_Json::encode($gep));
    		exit();
    	}
    }
    function getStuNoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$stu_no = $db->getNewAccountNumber($data['dept_id'],$data['branch_id']);
    		print_r(Zend_Json::encode($stu_no));
    		exit();
    	}
    }
    function getBanlancePriceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$payment = $db->getBalance($data['servce_id'],$data['student_id'],$data['type']);
    		print_r(Zend_Json::encode($payment));
    		exit();
    	}
    }
    function getGradeAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$grade = $db->getAllGradeGEP($data['dept_id']);
    		print_r(Zend_Json::encode($grade));
    		exit();
    	}
    }
    
    function getReceiptNoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$data['payment_method']=empty($data['payment_method'])?1:$data['payment_method'];
    		$receipt = $db->getRecieptNo($data['type'],$data['branch_id'],$data['payment_method']);
    		print_r(Zend_Json::encode($receipt));
    		exit();
    	}
    }
}