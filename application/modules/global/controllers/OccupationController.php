<?php
class Global_OccupationController extends Zend_Controller_Action {
	protected $tr;
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'title' => $_data['title'],
						'status' => $_data['status_search']);
			}
			else{
				$search = array(
						'title' => '',
						'status' => -1);
			}
			$db = new Global_Model_DbTable_DbOccupation();
			$rs_rows= $db->getAllOccupation($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("OCCUPATION","CREATED_DATE","STATUS","BY_USER");
			$link=array(
					'module'=>'global','controller'=>'occupation','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('occu_name'=>$link,'occu_enname'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Global_Form_FrmSearchMajor();
		$frms =$frm->FrmsearchOccupation();
		Application_Model_Decorator::removeAllDecorator($frms);
		$this->view->frm_search = $frms;
		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Global_Model_DbTable_DbOccupation();
				$_major_id = $_dbmodel->addNewOccupation($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/global/occupation");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/global/occupation/add");
					
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		
		}
	}
	public function editAction(){
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbOccupation();
				$db->updateOccupation($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/occupation/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$db = new Global_Model_DbTable_DbOccupation();
		$row=$db->getOccupationById($id);
		
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),"/global/occupation/index");
			exit();
		}
		$this->view->rs= $row;
	}
	function addcompositionAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbOccupation();
			$id = $db->addOccupation($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	
}

