<?php

class Accounting_ExpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/expense';
	protected $tr;
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Accounting_Model_DbTable_DbExpense();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"=>'',
    					"category"=>'',
    					"branch"=>'',
    					'asset_id'=>'',
    					"currency_type"=>-1,
    					"status"=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		$this->view->search=$formdata;
    		
			$rs_rows= $db->getAllExpense($formdata);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_ID","RECEIPT_NO","EXPENSE_TITLE","CATEGORY_EXPENSE","CURRENCY_TYPE","TOTAL_EXPENSE","NOTE","FOR_DATE","STATUS");
    		$link=array(
    				'module'=>'accounting','controller'=>'expense','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch'=>$link,'title'=>$link,'invoice'=>$link,'total_amount'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		$frm = new Registrar_Form_FrmSearchexpense();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	$db = new Allreport_Model_DbTable_DbRptOtherExpense();
    	$this->view->fix_asset=$db->getAllFixAssetName();
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Accounting_Model_DbTable_DbExpense();				
			try {
				$db->addexpense($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/expense");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/expense/add");
				}
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
    	$pructis=new Registrar_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$_dbs = new Application_Model_DbTable_DbGlobal();
    	$cate_income = $_dbs->getCategoryName(0);
    	array_unshift($cate_income, array('id'=>'-1','name'=>$tr->translate("ADD_NEW")));
    	array_unshift($cate_income, array('id'=>'0','name'=>$tr->translate("SELECT_CATEGORY")));
    	$this->view->cate_expense = $cate_income;
    }
 
    public function editAction()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$data['id'] = $id;
			$db = new Accounting_Model_DbTable_DbExpense();				
			try {
				$db->updateExpense($data);				
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/expense");
			} catch (Exception $e) {
				$this->view->msg = $tr->translate("EDIT_FAIL");
				Application_Form_FrmMessage::Sucessfull('EDIT_FAIL', "/accounting/expense");
			}
		}
	
		$db = new Accounting_Model_DbTable_DbExpense();
		$row  = $db->getexpensebyid($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),"/accounting/expense");
			exit();
		}
		$this->view->row = $row;
		
    	$pructis=new Registrar_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
		
    	
    	$_dbs = new Application_Model_DbTable_DbGlobal();
    	$cate_income = $_dbs->getCategoryName(0);
    	array_unshift($cate_income, array('id'=>'-1','name'=>$tr->translate("ADD_NEW")));
    	array_unshift($cate_income, array('id'=>'0','name'=>$tr->translate("SELECT_CATEGORY")));
    	$this->view->cate_expense = $cate_income;
    }
    
    function getTotalassetAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbExpense();
    		$cate_income = $db->getTotalFixedAsset($data['fixed_id']);
    		print_r(Zend_Json::encode($cate_income));
    		exit();
    	}
    }

}







