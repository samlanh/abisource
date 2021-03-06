<?php
class accounting_FixedAssetController extends Zend_Controller_Action {
	const REDIRECT_URL = '/accounting/fixedasset';
	protected $tr;
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
			$db = new Accounting_Model_DbTable_DbAsset();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' => '',
						'branch' => '',
						'status' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$rs_rows= $db->getAllAsset($search);
				
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","FIXED_ASSETNAME","ASSET_COST","SALVAGE_VALUE","INSTALLMENT_AMOUNT","USEFULL_LIFE","PAID_PER_MONTH","REMAINING","START_DATE","END_DATE","NOTE","STATUS");
			$link=array(
					'module'=>'accounting','controller'=>'fixedasset','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('fixed_assetname'=>$link,'branch_name'=>$link,'asset_cost'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->frm_fixedasset=$form;
	
		$frm = new Registrar_Form_FrmSearchexpense();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
			
	}
	
	public function addAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbAsset();
			try {
				$db->addAsset($data);
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/index');
				}else{
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/add');
				}
			} catch (Exception $e) {
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::message("INSERT_FAIL");
			}
		}
		$fm = new Accounting_Form_Frmasset();
		$frm = $fm->FrmAsset();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fixedasset = $frm;
	}

	
	public function editAction()
	{
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$data['id']=$id;
			$db = new Accounting_Model_DbTable_DbAsset();
			try {
				$db->updatAsset($data);
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', self::REDIRECT_URL . '/index');
			} catch (Exception $e) {
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::message("EDIT_FAIL");
			}
		}
		$db = new Accounting_Model_DbTable_DbAsset();
		$row  = $db->getassetbyid($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),self::REDIRECT_URL . '/index');
			exit();
		}
		
		$pructis=new Accounting_Form_Frmasset();
		$frm = $pructis->FrmAsset($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fixedasset=$frm;
	}
}
