<?php 
Class Accounting_Form_Frmasset extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmAsset($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("SEARCH_FIXD_NAME")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$asset_name = new Zend_Dojo_Form_Element_ValidationTextBox('asset_name');
		$asset_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'onchange'=>"getAssetInfo(1);"
				'required'=>true
				));
// 		$rows = $db->getAssetByType();
// 		$options=array(''=>"------Select------",-1=>"Add New");
// 		if(!empty($rows))foreach($rows AS $row) $options[$row['id']]=$row['account_name_en'];
// 		$asset_name->setMultiOptions($options);
	
		$asset_code = new Zend_Dojo_Form_Element_TextBox('asset_code');
		$asset_code->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'onchange'=>"getAssetInfo(2);",
		));
		
		//$rows = $db->getAssetByType();
// 		$options=array(''=>"------Select------",-1=>"Add New");
// 		if(!empty($rows))foreach($rows AS $row) $options[$row['id']]=$row['account_code'];
// 		$asset_code->setMultiOptions($options);
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		$paid_type = new Zend_Dojo_Form_Element_FilteringSelect('paid_type');
		$paid_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'checkasset();',
				'required'=>true
		));
		$opt=array(
				//''=>"------Select------",
				//-1=>"Add New",
				1=>"Cash",
				//2=>"Credit",
				//3=>"Other"
			);
		
		$paid_type->setMultiOptions($opt);
		$paid_type->setValue(1);
		
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				 
		));
		
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
			
		));
		$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$_stutas->setMultiOptions($options);
		

		
		$some_payamount = new Zend_Dojo_Form_Element_ValidationTextBox('some_payamount');
		$some_payamount->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'required'=>true
		));
		
		
		
		$asset_type = new Zend_Dojo_Form_Element_FilteringSelect('asset_type');
		$asset_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt=array(
				1=>'Long Term',
				2=>'Short Term'
			);
		$asset_type->setMultiOptions($opt);
		
		$asset_cost=new Zend_Dojo_Form_Element_NumberTextBox('asset_cost');
		$asset_cost->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'onchange'=>'calculateDepreciation();',
				'class'=>'fullside',
				'required'=>'true'
				));
		
		$useful_life = new Zend_Dojo_Form_Element_NumberTextBox('usefull_life');
		$useful_life->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'onkeyup'=>'calculateDepreciation();setEndDate();',
				'class'=>'fullside',
				'required'=>true
		));
		
		$salvage_value = new Zend_Dojo_Form_Element_NumberTextBox('salvage_value');
		$salvage_value->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'calculateDepreciation();',
				'required'=>'true'
				));
		
		$payment_method=new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
				));
		$option=array(
				1=>'Straight line',
				//2=>'Double-declining banlance',
				//3=>'Sum of the year'
			);
		$payment_method->setMultiOptions($option);
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onChange'	 =>"setEndDate()"
				));
		$Date->setValue(date('Y-m-d'));
		
		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onChange'	 =>'setEndDate()'
		));
		$start_date->setValue(date('Y-m-d'));
		
		
		$_branch_id=new Zend_Dojo_Form_Element_FilteringSelect('branch');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
				));
		$db = new Global_Model_DbTable_DbTuitionFee();
		$rows = $db->getAllBranch();
		$options='';
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_branch_id->setMultiOptions($options);
		
		$current_type = new Zend_Dojo_Form_Element_FilteringSelect('current_type');
		$current_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true,
				'readonly'=>true
		));
		$opt=array(1=>"រៀល-KHR",2=>"ដុល្លា-USD",3=>"បាត-THB");
		$current_type->setMultiOptions($opt);
		$current_type->setValue(2);
		
		$tem_type = new Zend_Dojo_Form_Element_FilteringSelect('tem_type');
		$tem_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt=array(1=>"Diploma-Diploma",2=>"Associate-Associate",3=>"Bechelor-Bechelor",4=>"Master-Master",5=>"PhD-PhD");
		$tem_type->setMultiOptions($opt);
		$tem_type->setValue(1);
		
		$journal = new Zend_Dojo_Form_Element_CheckBox('journal');
		$journal->setAttribs(array(
				'dojoType'=>'dijit.form.CheckBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$installment_amount = new Zend_Dojo_Form_Element_TextBox('installment_amount');
		$installment_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'	  =>'fullside',
				'readonly'=>true,
				'required'=>true
		));
		
		$amount = new Zend_Dojo_Form_Element_TextBox('amount');
		$amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'	  =>'fullside',
				'readonly'=>true,
				'required'=>true
		));
		 
		$_id = new Zend_Form_Element_Hidden('id');
// 		print_r($data);exit();
		if($data!=null){
			$tem_type->setValue($data['term_type']);
			$_branch_id->setValue($data['branch_id']);
			$asset_name->setValue($data['fixed_assetname']);
			$asset_type->setValue($data['fixed_asset_type']);
			$asset_cost->setValue($data['asset_cost']);
			$useful_life->setValue($data['usefull_life']);
			$salvage_value->setValue($data['salvagevalue']);
			$amount->setValue($data['paid_month']);
			$payment_method->setValue($data['payment_method']);
			 
			$start_date->setValue($data['start_date']);
			$Date->setValue($data['end_date']);
			$asset_code->setValue($data['asset_code']);
			$paid_type->setValue($data['pay_type']);
			$some_payamount->setValue($data['some_payamount']);
			$note->setValue($data['note']);
			$_stutas->setValue($data['status']);
			//$journal->setValue($data['auto_post']);
			$_id->setValue($data['id']);
			
			$installment_amount->setValue($data['installment_amount']);
		}
		
		$this->addElements(array($installment_amount,$_title,$asset_name,$asset_type,$asset_cost,$start_date,$useful_life,$salvage_value,$payment_method,
				$amount,$Date,$_branch_id,$_id,$asset_code,$paid_type,$note,$_stutas,$some_payamount,$current_type,$journal,$tem_type));
		return $this;
		
	}	
}