<?php
class Allreport_CalculatorController extends Zend_Controller_Action {	
	
 public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	
	}
	function indexAction(){
		try{
			$db = new Allreport_Model_DbTable_Dbcashcount();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'branch'=>0,
						'user'=>0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->adv_search=$search;
			$rs_rows= $db->getAllcashcount($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("FOR_DATE","ALL_TOTAL_DOLLAR","ALL_TOTAL_REIL","EXCHANGE_RATE","CONVERT_FROM_REIL","GRAND_TOTAL_DOLLAR","NOTE","USER","STATUS");
			$link=array(
					'module'=>'allreport','controller'=>'calculator','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('input_date'=>$link,'total_dollar'=>$link,'total_reil'=>$link,'exchange_rate'=>$link,'dollar_fromreil'=>$link));
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
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
    			$_dbmodel = new Allreport_Model_DbTable_Dbcashcount();
    			$_dbmodel->addCashCount($_data);
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/allreport/Calculator");
    		}catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
		$frm_cal = new Global_Form_FrmCal();
		$myform = $frm_cal -> FrmCalculator();
		Application_Model_Decorator::removeAllDecorator($myform);
		$this->view->frm_cal = $myform;
		
    }
    
    public function editAction()
    {
    	$_dbmodel = new Allreport_Model_DbTable_Dbcashcount();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$_dbmodel->addCashCount($_data);
    			Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/allreport/Calculator");
    		}catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$id=$this->getRequest()->getParam('id');
    	$row = $_dbmodel->getCashcountbyId($id);
    	$this->view->row = $row;
    	$frm_cal = new Global_Form_FrmCal();
    	$myform = $frm_cal -> FrmCalculator($row);
    	Application_Model_Decorator::removeAllDecorator($myform);
    	$this->view->frm_cal = $myform;
    }
}
