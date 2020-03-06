<?php
class Foundation_FoodController extends Zend_Controller_Action {
	
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
				'title'		=>'',
				'branch'	=>'',
				'service'	=>0,
				'start_date'=> date('Y-m-d'),
				'end_date'	=>date('Y-m-d'),
			);
		}
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db= new Foundation_Model_DbTable_DbFood();
		$rs_rows = $db->getAllStudentTransport($search);
		$list = new Application_Form_Frmtable();
		$collumns = array("STUDENT_ID","NAME_KH","NAME_EN","PHONE","SERVICE");
		$link=array(
				'module'=>'foundation','controller'=>'food','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('stu_code'=>$link,'stu_khname'=>$link,'stu_enname'=>$link));

		$this->view->adv_search = $search;
		$this->view->food_service = $db->getAllFoodService();
	}
	function addAction(){
		Application_Form_FrmMessage::Sucessfull("Sorry can not add !!!","/foundation/food");
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		
		$db= new Foundation_Model_DbTable_DbFood();
		$row = $this->view->row = $db->getStudentFood($id);
		
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$data["id"]=$id;
				$db = new Foundation_Model_DbTable_DbFood();
				$row=$db->updateStudentChangeCar($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/food/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}	
		
		$db = new Foundation_Model_DbTable_DbFood();
		$this->view->food_service = $db->getAllFoodService();
	}

	
	
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbFood();
			$grade = $db->getStudentInfoById($data['studentid']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
}
