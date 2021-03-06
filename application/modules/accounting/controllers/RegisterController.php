<?php
class Accounting_RegisterController extends Zend_Controller_Action {
	protected $tr;
	const REDIRECT_URL ='/accounting';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction(){
    	try{
    		$db = new Accounting_Model_DbTable_DbRegister();
    		if($this->getRequest()->isPost()){
    		    $search=$this->getRequest()->getPost();
    		}
    		else{
    		   $search = array(
    		    	'adv_search' => '',
    		    	'branch' 	=> '',
    		    	'study_year' => '',
    		    	'degree_ft' => '',
    		    	'time'   =>'', 
    		    	'session'=>'',
    		    	'grade_ft'=>'',
    		    	'user'=>'',
    		    	'start_date'=> date('Y-m-d'),
    		    	'end_date'=>date('Y-m-d'));
    		}
    		$this->view->adv_search=$search;
    		$rs_rows= $db->getAllStudentRegister($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getGernder($rs_rows, BASE_URL );
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_ID","STUDENT_ID","NAME_KH","NAME_EN","SEX","DEGREE","CLASS","RECEIPT_NO",
    				          "SUBTOTAL","PAID_AMOUNT","BALANCE","DATE_PAY","USER","STATUS","DELETE");
    		$link=array(
    				'module'=>'accounting','controller'=>'register','action'=>'edit',
    		);
    		$link1=array(
    				'module'=>'accounting','controller'=>'register','action'=>'delete',
    		);
    		$this->view->list=$list->getCheckList(2, $collumns, $rs_rows,array('stu_code'=>$link,'receipt_number'=>$link,'stu_khname'=>$link,'stu_enname'=>$link,'del'=>$link1));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message($this->tr->translate('APPLICATION_FAIL'));
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$data = new Accounting_Model_DbTable_DbRegister();
    	$this->view->rows_degree=$data->getDegree();
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    public function addAction(){
      if($this->getRequest()->isPost()){
      	$_data = $this->getRequest()->getPost();
      	try {
      		$db = new Accounting_Model_DbTable_DbRegister();
      		$db->addRegister($_data);
      		if(isset($_data['save_new'])){
      			Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
      		}else{
      			Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/register/index');
      		}
      	} catch (Exception $e) {
      		Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
      		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
      	}
      }
       $frm = new Accounting_Form_FrmRegister();
       $frm_register=$frm->FrmRegistarWU();
       Application_Model_Decorator::removeAllDecorator($frm_register);
       $this->view->frm_register = $frm_register;
       
       $_db = new Application_Model_DbTable_DbGlobal();
       $data=$this->view->all_dept = $_db->getAllFecultyNamess(1);
       $this->view->branch_id = $_db->getAllBranch();
       $this->view->all_time = $_db->getAllTime(1);
       
       $db = new Accounting_Model_DbTable_DbRegister();
       $this->view->room = $db->getAllRoom();
       
       $db = new Registrar_Model_DbTable_DbRegister();
       $this->view->exchange_rate = $db->getExchangeRate();
    }
    
    public function editAction(){
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Accounting_Model_DbTable_DbRegister();
    			$db->updateRegister($_data);
    			if(isset($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/register/index');
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/register/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$db = new Accounting_Model_DbTable_DbRegister();
    	$this->view->user_type = $db->getUserType();
    	
        $form_row=$db->getRegisterById($id);
        $this->view->degree_row=$form_row;
        
    	$frm = new Accounting_Form_FrmRegister();
    	$frm_register=$frm->FrmRegistarWU($form_row);
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->all_dept = $_db->getAllFecultyNamess(1);
    	$this->view->branch_id = $_db->getAllBranch();
    	$this->view->all_time = $_db->getAllTime(1);
    	
    	$db = new Accounting_Model_DbTable_DbRegister();
    	$this->view->room = $db->getAllRoom();
    	$db = new Registrar_Model_DbTable_DbRegister();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	$this->view->old_stu_code = $db->getAllGerneralOldStudent($form_row['stu_id']);
    	$this->view->old_stu_name = $db->getAllGerneralOldStudentName($form_row['stu_id']);
    }
    
    public function copyAction(){
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Accounting_Model_DbTable_DbRegister();
    			$db->addRegister($_data);
    			if(isset($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/register/index');
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/register/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$db = new Accounting_Model_DbTable_DbRegister();
    	$form_row=$db->getRegisterById($id);
    
    	$this->view->degree_row=$form_row;
    	$frm = new Accounting_Form_FrmRegister();
    	$frm_register=$frm->FrmRegistarWU($form_row);
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->all_dept = $_db->getAllFecultyNamess(1);
    	$this->view->branch_id = $_db->getAllBranch();
    	$this->view->all_time = $_db->getAllTime(1);
    	
    	$db = new Accounting_Model_DbTable_DbRegister();
    	$this->view->room = $db->getAllRoom();
    	 
    	$db = new Registrar_Model_DbTable_DbRegister();
    	$this->view->exchange_rate = $db->getExchangeRate();
    }
    
    public function deleteAction(){
    	$id=$this->getRequest()->getParam('id');
    	$db = new Accounting_Model_DbTable_DbRegister();
    	
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
	    	$db->deleteRecord($data,$id);
	    	Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/register/index');
    	}
    	$this->view->detail = $db->getReceiptDetail($id);
    }
    public function oldaddAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_model = new Registrar_Model_DbTable_DbwuRegister();
    		$_model->AddNewStudent($_data);
    	}
    	$frm = new Registrar_Form_FrmRegister();
    	$frm_register=$frm->FrmRegistarWU();
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	 
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
    	$model = new Application_Form_FrmGlobal();
    	$this->view->footer=$model->getReceiptFooter();
    	 
    	$this->view->invoice_no = Application_Model_GlobalClass::getInvoiceNo();
    	 
    	$__student_card = array();
    	$this->view->student_card = $__student_card;
    	$db = new Registrar_Model_DbTable_DbwuRegister();
    	$this->view->invoice_num = $db->getGaneratInvoiceWU();
    }
    public function getStudentinfoallAction(){
    	if($this->getRequest()->isPost()){
    		$_db = new Registrar_Model_DbTable_DbGetStudentInfo();
    		$_rs_student = $_db->getAllStudent();
    		print_r(Zend_Json::encode($_rs_student));
    		exit();
    	}
    }
    function getGradeAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRegister();
    		$grade = $db->getAllGrade($data['dept_id']);
    		print_r(Zend_Json::encode($grade));
    		exit();
    	}
    }
    function getPaymentTermAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRegister();
    		$payment = $db->getPaymentTerm($data['generat_id'],$data['pay_id'],$data['grade_id']);
    		print_r(Zend_Json::encode($payment));
    		exit();
    	}
    }
    function getBanlancePriceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRegister();
    		$payment = $db->getBalance($data['servce_id'],$data['student_id'],$data['type']);
    		print_r(Zend_Json::encode($payment));
    		exit();
    	}
    }
    function getGeneralOldStudentAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRegister();
    		$general = $db->getGeneralOldStudentById($data['student_id']);
    		print_r(Zend_Json::encode($general));
    		exit();
    	}
    }
    function getOldStuIdByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRegister();
    		$student = $db->getAllStudentByBranch($data['branch_id']);
    		print_r(Zend_Json::encode($student));
    		exit();
    	}
    }
    function getDropStuIdByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRegister();
    		$student = $db->getDropStudentByBranch($data['branch_id']);
    		
    		print_r(Zend_Json::encode($student));
    		exit();
    	}
    }
    function checkStuCodeAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRegister();
    		$stu_code = $db->checkStuCode($data['stu_code'],$data['branch']);
    		print_r(Zend_Json::encode($stu_code));
    		exit();
    	}
    }
}