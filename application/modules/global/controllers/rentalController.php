<?php
class Global_RentalController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'title' 		=> '',
					'branch' 		=> '',
					'status_search' => ''
				);
			}
			$db = new Global_Model_DbTable_DbRental();
			$rs_rows= $db->getAllRental($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","CODE","NAME","SEX","PHONE","ADDRESS","NOTE","BY_USER","STATUS");
			$link=array(
					'module'=>'global','controller'=>'rental','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch'=>$link,'customer_code'=>$link,'first_name'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
		}
		$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
	}
   function addAction()
   {
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		try {
	   			$db = new Global_Model_DbTable_DbRental();
	   			$db->addRental($_data);
	   			//Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
	   			if(isset($_data['save_close'])){
	   				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/rental/index");
	   			}else{
	   				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/rental/add");
	   			}
	   		} catch (Exception $e) {
	   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
	   			echo $e->getMessage();
	   		}
	   	}
	   	$_db = new Application_Model_DbTable_DbGlobal();
	   	$this->view->branch_id = $_db->getAllBranch();
   }
   public function editAction()
   {
	   	$id=$this->getRequest()->getParam("id");
	   	$db = new Global_Model_DbTable_DbRental();
	   	if($this->getRequest()->isPost())
	   	{
	   		try{
		   		$data = $this->getRequest()->getPost();
		   		$data["id"]=$id;
		   		$db->updateRental($data);
		   		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/rental/index");
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("EDIT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	}
	   	$this->view->row = $db->getRentalById($id);
	   	$_db = new Application_Model_DbTable_DbGlobal();
	   	$this->view->branch_id = $_db->getAllBranch();
   }
}
