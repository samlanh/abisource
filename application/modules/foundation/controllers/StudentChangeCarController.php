<?php
class Foundation_StudentChangeCarController extends Zend_Controller_Action {
	
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
		
		$db= new Foundation_Model_DbTable_DbStudentChangeCar();
		$rs_rows = $db->getAllStudentDropTransport($search);
		$list = new Application_Form_Frmtable();
		$collumns = array("STUDENT_ID","NAME_KH","NAME_EN","PHONE","SERVICE","CAR_ID","NEW_CAR_ID","NOTE","CHANGE_DATE");
		$link=array(
				'module'=>'foundation','controller'=>'studentchangecar','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('stu_code'=>$link,'stu_khname'=>$link,'stu_enname'=>$link));

		$this->view->adv_search = $search;
		
		$this->view->transport_service = $db->getAllTransportService();
		
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				$_add = new Foundation_Model_DbTable_DbStudentChangeCar();
 				$_add->addStudentChangeCar($_data);
 				if(!empty($_data['save_close'])){
 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentchangecar");
 				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				echo $e->getMessage();exit();
			}
		}
		
		$db = new Foundation_Model_DbTable_DbStudentChangeCar();
		$this->view->stu_id = $db->getAllStudentTransportID();
		$this->view->stu_name = $db->getAllStudentTransportName();
		$this->view->transport_service = $db->getAllTransportService();
		$this->view->all_car = $db->getAllCar();
		
// 		print_r($a);exit();
// 		$_db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->degree = $rows = $_db->getAllFecultyName();
// 		$this->view->occupation = $row =$_db->getOccupation();
// 		$this->view->province = $row =$_db->getProvince();
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		
		$db= new Foundation_Model_DbTable_DbStudentChangeCar();
		$row = $this->view->row = $db->getStudentChangeCar($id);
		
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$data["id"]=$id;
				$db = new Foundation_Model_DbTable_DbStudentChangeCar();
				$row=$db->updateStudentChangeCar($data);
				
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/studentchangecar/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}	
		
		$db = new Foundation_Model_DbTable_DbStudentChangeCar();
		$this->view->stu_id = $db->getAllStudentTransportID();
		$this->view->stu_name = $db->getAllStudentTransportName();
		$this->view->transport_service = $db->getAllTransportService();
		$this->view->all_car = $db->getAllCar();
		//print_r($add);exit();
	}

	
	
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentChangeCar();
			$grade = $db->getStudentInfoById($data['studentid']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
}
