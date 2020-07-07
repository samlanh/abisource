<?php
class Global_CarController extends Zend_Controller_Action {
	protected $tr;
    public function init()
    {    	
     	/* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
					'title' => $_data['title'],
				);
			}else{
				$search = array(
						'title' => '',
				);
			
			}
			$db = new Global_Model_DbTable_DbCar();
			$rs_rows= $db->getAllCars($search);
			
			$this->view->search =  $search;
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","CAR_ID","DRIVER_NAME","PHONE","NOTE","CREATE_DATE");
			$link=array(
					'module'=>'global','controller'=>'car','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('carname'=>$link,'carid'=>$link,'drivername'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
	}
	
	function addAction()
	{
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbcar = new Global_Model_DbTable_DbCar();
				$_dbcar->addcar($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"),"/global/car");
				}
				Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"),"/global/car/add");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$db=new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranch();
		
	}
	
	public function editAction()
	{
		$db=new Global_Model_DbTable_DbCar();
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		if($this->getRequest()->isPost())
		{
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbCar();
			$db->updateCar($data);
			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/car/index");
		}
		
		$row = $db->getCarById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),"/global/car/index");
			exit();
		}
		$this->view->rs = $row;
		
		$db=new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranch();
	}
}

