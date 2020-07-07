<?php
class Global_MajorController extends Zend_Controller_Action {
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
    	try{
	    	$_model = new Global_Model_DbTable_DbDept();
	    	if($this->getRequest()->isPost()){
	    		$_data=$this->getRequest()->getPost();
		    	$search = array(
	    				'txtsearch' => $_data['txtsearch'],
		    			'degree' => $_data['degree']
	    		);
    	   }else{
    			$search = array(
	    				'txtsearch' => '',
		    			'degree' => ''
	    		);
    	   }
    	   $this->view->search = $search;
    	   
	    	$rs_rows= $_model->getAllMajorList($search);
	    	$glClass = new Application_Model_GlobalClass();
	    	$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
	    	
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("GRADE","DEGREE","CREATED_DATE","STATUS","BY_USER");
	    	$link=array(
	    			'module'=>'global','controller'=>'major','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('major_enname'=>$link ,'major_khname'=>$link,'dept_name'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$db = new Global_Model_DbTable_DbDept();
    	$degree = $db->getAllDept();
    	$this->view->degree = $degree;
    }
    
    
    public function addAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$_dbmodel = new Global_Model_DbTable_DbDept();
    			$_major_id = $_dbmodel->AddNewMajor($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"),"/global/major/index");
    			}
    			Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"),"/global/major/add");
    			
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    			
    	}
    	$db = new Global_Model_DbTable_DbDept();
    	$dept = $db->getAllDept();
    	array_unshift($dept, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->dept = $dept;
    }
    
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$id = empty($id)?0:$id;
    	if($this->getRequest()->isPost()){
    		try{
	    		$_data = $this->getRequest()->getPost();
	    		$_dbmodel = new Global_Model_DbTable_DbDept();
	    		$_dbmodel ->updatMajorById($_data);
	    		Application_Form_FrmMessage::Sucessfull($this->tr->translate("EDIT_SUCCESS"), "/global/major/index");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$db_model = new Global_Model_DbTable_DbDept();
    	$row=$db_model->getMajorById($id);
    	$this->view->rs = $row;
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate("NO_RECORD"),"/global/major/index");
    		exit();
    	}
    		$frm = new Application_Form_FrmOther();
    		$frm->FrmAddMajor($row);
    		Application_Model_Decorator::removeAllDecorator($frm);
    		$this->view->frm_major = $frm;
    	
    	
    	$db = new Global_Model_DbTable_DbDept();
    	$dept = $db->getAllDept();
    	array_unshift($dept, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->dept = $dept;
    }
    
    function addDeptAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbDept();
    			$row = $db->addDept($data);
    			$result = array("id"=>$row);
    			print_r(Zend_Json::encode($row));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
    
 
}

