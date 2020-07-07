<?php
class Global_FacultyController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	protected $tr;
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
    public function indexAction()
    {
    	$db_dept=new Global_Model_DbTable_DbDept();
    	if($this->getRequest()->isPost()){
    		$_data=$this->getRequest()->getPost();
    		$search = array(
    				'title' => $_data['title'],
    				'status' => $_data['status_search'],
    				'type' => $_data['type'],
    				);
    	}
    	else{
    		$search='';
    	}
    	
        $rs_rows = $db_dept->getAllFacultyList($search);
        $glClass = new Application_Model_GlobalClass();
        $rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
        
    	$list = new Application_Form_Frmtable();
    	$collumns = array("DEGREE","TYPE","SHORTCUT","CREATED_DATE","STATUS","BY_USER");
    	$link=array(
    			'module'=>'global','controller'=>'faculty','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('en_name'=>$link));
    	$frm1 = new Global_Form_FrmSearchMajor();
    	$frm = $frm1->FrmDepartment();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	
    	$frm = new Application_Form_FrmOther();
    	$frm =  $frm->FrmAddDept(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->add_dept = $frm;
    }
    
    function addAction(){
    	if($this->getRequest()->isPost()){
    		try {
    			$_data = $this->getRequest()->getPost();
    			$_dbmodel = new Global_Model_DbTable_DbDept();
    			$_dbmodel->AddNewDepartment($_data);
    			
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"), "/global/faculty/index");
    			}
    			Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"), "/global/faculty/add");
    		} catch (Exception $e) {
    			echo $e->getMessage();
    		}
    	}
    	$frm = new Application_Form_FrmOther();
    	$frm->FrmAddDept();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_dept = $frm;
    	$db_glopbal=new Global_Model_DbTable_DbDept();
    	$rs_eng=$db_glopbal->getAllDegreeNameEn();
    	array_unshift($rs_eng, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->rs_degree=$rs_eng;
    }
    
    public function editAction(){
    	if($this->getRequest()->isPost()){
    		try {
    			$_data = $this->getRequest()->getPost();
    			$_dbmodel = new Global_Model_DbTable_DbDept();
    			$_dbmodel->UpdateDepartment($_data);
    			
    			Application_Form_FrmMessage::Sucessfull($this->tr->translate("EDIT_SUCCESS"), "/global/faculty/index");
    		} catch (Exception $e) {
    			$err =$e->getMessage();
    			Application_Form_FrmMessage::message("Application Error!");
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    			echo $e->getMessage();exit();
    		}
    	}
    	$id= $this->getRequest()->getParam("id");
    	$id = empty($id)?0:$id;
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$_row =$_db->getDeptById($id);
    	
    	if (empty($_row)){
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),"/global/faculty/index");
    		exit();
    	}
    	
    	$frm = new Application_Form_FrmOther();
    	$frm->FrmAddDept($_row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_dept = $frm;
    	
    	$db_glopbal=new Global_Model_DbTable_DbDept();
    	$rs_eng=$db_glopbal->getAllDegreeNameEn();
    	array_unshift($rs_eng, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW") ));
    	$this->view->rs_degree=$rs_eng;
    }
    function addfacultyAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbDept();
    		$faculty_id = $db->AddNewFaculty($data);
    		print_r(Zend_Json::encode($faculty_id));
    		exit();
    		
    	}
    }
    
    function addDergreeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Global_Model_DbTable_DbDept();
    		$faculty_id = $db->AddNewDegree($data);
    		print_r(Zend_Json::encode($faculty_id));
    		exit();
    
    	}
    }
  
}

