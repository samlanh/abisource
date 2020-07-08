<?php 
class Global_BookAndUniformController extends Zend_Controller_Action {
	
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search=array(
						'txtsearch' =>'',
						'status' 	=>-1,
						'product_type'=>'',
					);
			}
			$db = new Global_Model_DbTable_DbBookAndUniform();
			$rs_rows = $db->getAllServiceNames($search);
			$list = new Application_Form_Frmtable();
			if(!empty($rs_rows)){
				$glClass = new Application_Model_GlobalClass();
				$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			}
			else{
				$result = Application_Model_DbTable_DbGlobal::getResultWarning();
			}
			$collumns = array("PROGRAM_TITLE","PRODUCT_TYPE","DISCRIPTION","PRICE","STATUS","MODIFY_DATE","BY_USER");
			$link=array(
					'module'=>'global','controller'=>'bookanduniform','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('cate_name'=>$link,'title'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$frm = new Global_Form_FrmSearchMajor();
		$frm = $frm->frmSearchServiceProgram();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->adv_search = $search;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				$_model = new Global_Model_DbTable_DbBookAndUniform();
				$_model->addProduct($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/global/bookanduniform");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/global/bookanduniform/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$db = new Global_Model_DbTable_DbBookAndUniform();
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$data["id"]=$id;
				$row=$db->updateProduct($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/bookanduniform/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$rows = $db->getServiceById($id);
		if (empty($rows)){
			Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),"/global/bookanduniform/index");
			exit();
		}
		$this->view->row=$rows;
	}


}