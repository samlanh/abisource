<?php
class Accounting_CoursestudyController extends Zend_Controller_Action {
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
    		$db = new Accounting_Model_DbTable_DbCourStudey();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
			}
	    	else{
    			$search = array(
    				'adv_search' => '',
    				'branch' => '',
    				'study_year' => '',
    				'degree_gep' => '',
    				'grade_gep'=>'',
    				'user' => '',
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'));
    		}
    		$this->view->adv_search=$search;
    		$rs_rows= $db->getAllStudentGep($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getGernder($rs_rows, BASE_URL);
    		//$rs_rows = $glClass->getGetPayTerm($rs_rows, BASE_URL );
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","STUDENT_ID","NAME_KH","NAME_EN","SEX","RECEIPT_NO","DEGREE","CLASS",
    				          "SUBTOTAL","PAID_AMOUNT","BALANCE","DATE_PAY","USER","STATUS","DELETE");
    		$link=array(
    				'module'=>'accounting','controller'=>'coursestudy','action'=>'edit',
    		);
    		$link1=array(
    				'module'=>'accounting','controller'=>'coursestudy','action'=>'delete',
    		);
    		$this->view->list=$list->getCheckList(2, $collumns, $rs_rows,array('stu_code'=>$link,'receipt_number'=>$link,'stu_khname'=>$link,'stu_enname'=>$link,'delete'=>$link1));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    	}
    	$db=new Accounting_Model_DbTable_DbCourStudey();
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
      		$db = new Accounting_Model_DbTable_DbCourStudey();
      		$db->addStudentGep($_data);
      		if(isset($_data['save_new'])){
      			Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'),  '/accounting/coursestudy/add');
      		}else{
      			Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), '/accounting/coursestudy/index');
      		}
      	} catch (Exception $e) {
      		Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
      		//echo $e->getMessage();exit();
      	}
      }
       $frm = new Accounting_Form_FrmCourseStudy();
       $frm_register=$frm->FrmRegistarWU();
       Application_Model_Decorator::removeAllDecorator($frm_register);
       $this->view->frm_register = $frm_register;
       
       $_db = new Application_Model_DbTable_DbGlobal();
       $this->view->all_dept = $_db->getAllFecultyNamess(2);
       $this->view->branch = $_db->getAllBranch();
       $this->view->all_time = $_db->getAllTime(2);
       
       $db = new Registrar_Model_DbTable_DbRegister();
       $this->view->exchange_rate = $db->getExchangeRate();
       $this->view->room = $db->getAllRoom();
       
       
    }
    public function editAction()
    {
    $id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
//     		$_data['payment_id']=$id;
    		try {
    			$db = new Accounting_Model_DbTable_DbCourStudey();
    			if(isset($_data['save_new'])){
    				$db->updateStudentGep($_data);
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
    			}else{
    				$db->updateStudentGep($_data);
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$db = new Accounting_Model_DbTable_DbCourStudey();
    	
    	$this->view->user_type = $db->getUserType();
    	
    	$row_gep=$db->getStuentGepById($id);
    	$this->view->row_gep=$row_gep;
    	$frm = new Accounting_Form_FrmCourseStudy();
    	$frm_register=$frm->FrmRegistarWU($row_gep);
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->all_dept = $_db->getAllFecultyNamess(2);
    	$this->view->branch = $_db->getAllBranch();
    	$this->view->all_time = $_db->getAllTime(2);
    	
    	$db = new Accounting_Model_DbTable_DbCourStudey();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	
    	
    	$db = new Accounting_Model_DbTable_DbRegister();
    	$this->view->room = $db->getAllRoom();
    	$this->view->old_stu_code = $db->getAllGepOldStudent($row_gep['stu_id']);
    	$this->view->old_stu_name = $db->getAllGepOldStudentName($row_gep['stu_id']);
    }
    
    public function copyAction()
    {
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_data['id']=$id;
    		try {
    			$db = new Accounting_Model_DbTable_DbCourStudey();
    			$db->addStudentGep($_data);
    			Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$db = new Accounting_Model_DbTable_DbCourStudey();
    	$row_gep=$db->getStuentGepById($id);
    	
    	$this->view->row_gep=$row_gep;
    	$frm = new Accounting_Form_FrmCourseStudy();
    	$frm_register=$frm->FrmRegistarWU($row_gep);
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->all_dept = $_db->getAllFecultyNamess(2);
    	$this->view->branch = $_db->getAllBranch();
    	$this->view->all_time = $_db->getAllTime(2);
    	
    	$db = new Registrar_Model_DbTable_DbRegister();
    	$this->view->exchange_rate = $db->getExchangeRate();
    	$this->view->room = $db->getAllRoom();
    }
    
    public function deleteAction(){
    	$id=$this->getRequest()->getParam('id');
    	$db = new Accounting_Model_DbTable_DbRegister();
    	 
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db->deleteRecord($data,$id);
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL . '/coursestudy/index');
    	}
    	 
    	$this->view->detail = $db->getReceiptDetail($id);
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
    		$db = new Accounting_Model_DbTable_DbCourStudey();
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
    		$receipt = $db->getRecieptNo();
    		print_r(Zend_Json::encode($receipt));
    		exit();
    	}
    }
    
    function getYearByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbCourStudey();
    		$data['time'] = empty($data['time'])?0:$data['time'];
    		$year = $db->getAllYearByBranch($data['branch_id'],$data['time']);
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    
    function getOldStuIdByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbCourStudey();
    		$student = $db->getAllStudentByBranch($data['branch_id']);
    		print_r(Zend_Json::encode($student));
    		exit();
    	}
    }
    
    function getDropStuIdByBranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbCourStudey();
    		$student = $db->getAllStudentDropByBranch($data['branch_id']);
    		print_r(Zend_Json::encode($student));
    		exit();
    	}
    }
    
    
}



