<?php

class Registrar_StudenttestController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/studenttest';
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Registrar_Model_DbTable_DbStudentTest();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'txtsearch'		=>'',
    					'branch'		=>'',
    					'status_search'	=>-1,
    					'start_date'	=> date('Y-m-d'),
    					'end_date'		=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $search;
			$rs_rows= $db->getAllStudentTest($search);//call frome model
    		$list = new Application_Form_Frmtable();
    		$collumns = array("RECEIPT_NO","NAME_KH","NAME_EN","SEX","DOB","PHONE","DEGREE","NOTE","PRICE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'studenttest','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('receipt'=>$link,'kh_name'=>$link,'en_name'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Registrar_Model_DbTable_DbStudentTest();				
			try {
				$db->addStudentTest($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Registrar_Model_DbTable_DbStudentTest();
		$this->view->degree = $db->getAllDegreeName();
		$dbg = new Application_Model_DbTable_DbGlobal();
		$this->view->branch_info = $dbg->getBranchInfo();
		$this->view->payment_option = $dbg->getViewListById(18,0);
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    
    public function adddataonlyAction()
    {
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentTest();
    		try {
    			$db->addStudentTest($data);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest");
    			}else{
    				Application_Form_FrmMessage::message("INSERT_SUCCESS");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$db = new Registrar_Model_DbTable_DbStudentTest();
    	$this->view->degree = $db->getAllDegreeName();
    
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    
    public function addblankAction()
    {
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentTest();
    		try {
    			$db->addStudentTest($data);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest");
    			}else{
    				Application_Form_FrmMessage::message("INSERT_SUCCESS");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$db = new Registrar_Model_DbTable_DbStudentTest();
    	$this->view->degree = $db->getAllDegreeName();
    
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch_info = $dbg->getBranchInfo();
    
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbStudentTest();				
			try {
				$id = empty($data['id'])?$id:$data['id'];
				$db->updateStudentTest($data,$id);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/registrar/studenttest");		
			} catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("EDIT_FAIL");
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbStudentTest();
		$this->view->rs = $row  = $db->getStudentTestById($id);
		if($row['register']==1){
			Application_Form_FrmMessage::Sucessfull('You can not edit because student already registered !!! ', "/registrar/studenttest");
		}
		
		$db = new Registrar_Model_DbTable_DbStudentTest();
		$this->view->degree = $db->getAllDegreeName();
		$this->view->session = $db->getAllSession();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$this->view->branch_info = $dbg->getBranchInfo();
		$this->view->payment_option = $dbg->getViewListById(18,0);
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    
    
    function getReceiptNumberAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentTest();
    		$payment_method=empty($data['payment_method'])?1:$data['payment_method'];
    		$receipt_no = $db->getNewReceiptNumber(0,$payment_method);
    		print_r(Zend_Json::encode($receipt_no));
    		exit();
    	}
    }
    function getdegreetypeAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentTest();
    		$receipt_no = $db->getDegreeTypeByid($data['degree']);
    		print_r(Zend_Json::encode($receipt_no));
    		exit();
    	}
    }
    
    
    

}







