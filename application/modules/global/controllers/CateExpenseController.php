<?php

class Global_CateExpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/global/expense';
	protected $tr;
    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

public function indexAction()
    {
    	try{
    		$db = new Global_Model_DbTable_DbCateExpense();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"status"=>-1,
    			);
    		}
    		
    		$this->view->adv_search = $search;
    		
			$rs_rows= $db->getAllCateExpense($search);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("TITLE","USER","CREATE_DATE","STATUS");
    		$link=array(
    				'module'=>'global','controller'=>'cateexpense','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('category_name'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
    	$frm = new Registrar_Form_FrmSearchexpense();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Global_Model_DbTable_DbCateExpense();				
			try {
				$db->addCateExpense($data);
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/global/cateexpense");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/global/cateexpense/add");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		
    }
 
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	
    	if($this->getRequest()->isPost()){
    		
			$data=$this->getRequest()->getPost();	
			$data['id']=$id;
			$db = new Global_Model_DbTable_DbCateExpense();				
			try {
				$db->updateCateExpense($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/global/cateexpense");		
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
		
		$db = new Global_Model_DbTable_DbCateExpense();
		$row  = $db->getCateExpenseById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),"/global/cateexpense");
			exit();
		}
		$this->view->rs = $row;
		
    }

}







