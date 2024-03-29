<?php 
Class Registrar_Form_Frmexpense extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
	}
	public function FrmAddExpense($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$title = new Zend_Dojo_Form_Element_ValidationTextBox('title');
		$title->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				));
		
		$for_date = new Zend_Dojo_Form_Element_FilteringSelect('for_date');
		$for_date->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		$options= array(1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"10",11=>"11",12=>"12");
		$for_date->setMultiOptions($options);
		
		$_Date = new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
				
		));
		$_Date->setValue(date('Y-m-d'));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				
				'onchange'=>'getNewReceiptNo();',
		));
		$_db = new Application_Model_DbTable_DbGlobal();
		$rows = $_db->getAllBranch();
		$opt =array(''=>$this->tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row) $opt[$row['id']]=$row['name'];
		$_branch_id->setMultiOptions($opt);
		$_branch_id->setValue($request->getParam("branch_id"));
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('Stutas');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',			
		));
		$options= array(1=>"ប្រើប្រាស់",2=>"មិនប្រើប្រាស់");
		$_stutas->setMultiOptions($options);
		
		$_Description = new Zend_Dojo_Form_Element_Textarea('Description');
		$_Description ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>'width:98%',
		));
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'readMoneyInKhmer();',
				'required'=>true,
		));
		
		$convert_to_dollar=new Zend_Dojo_Form_Element_NumberTextBox('convert_to_dollar');
		$convert_to_dollar->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				//'required'=>true
		));
		
		$invoice=new Zend_Dojo_Form_Element_TextBox('invoice');
		$invoice->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'style'=>'color:red',
				//'readonly'=>'readonly',
		));
		
		$_category = new Zend_Dojo_Form_Element_FilteringSelect('cat_income');
		$_category->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>false,
				'onchange'=>'getValue();',
		));
		$_dbs = new Application_Model_DbTable_DbGlobal();
		$rows = $_dbs->getCategoryName(1);
		$opt =array(''=>$this->tr->translate("SELECT_CATEGORY"),'-1'=>$this->tr->translate("ADD_NEW"));
		if(!empty($rows))foreach($rows AS $row) $opt[$row['id']]=$row['name'];
		$_category->setMultiOptions($opt);
		$_category->setValue($request->getParam("cat_income"));
		
		///category expend
		$_cat_expend = new Zend_Dojo_Form_Element_FilteringSelect('cat_expend');
		$_cat_expend->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>false
				//'onchange'=>'convertToDollar();',
		));
		$rows = $_dbs->getCategoryName(0);
		$opt =array(''=>$this->tr->translate("PLEASE_SELECTED"));
		if(!empty($rows))foreach($rows AS $row) $opt[$row['id']]=$row['name'];
		$_cat_expend->setMultiOptions($opt);
		$_cat_expend->setValue($request->getParam("cat_expend"));
		
		$id = new Zend_Form_Element_Hidden("id");
		$_currency_type = new Zend_Dojo_Form_Element_FilteringSelect('currency_type');
		$_currency_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				//'onchange'=>'convertToDollar();',
		));
		$opt = $db->getViewById(8,1);
		$_currency_type->setMultiOptions($opt);

		$_sex =  new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$sex_opt = array(
				1=>$this->tr->translate("MALE"),
				2=>$this->tr->translate("FEMALE"));
		$_sex->setMultiOptions($sex_opt);
		
		$name = new Zend_Dojo_Form_Element_TextBox('name');
		$name->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$phone = new Zend_Dojo_Form_Element_TextBox('phone');
		$phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_fixed_id = new Zend_Dojo_Form_Element_FilteringSelect('fixed_id');
		$_fixed_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getFixedAssetTotalPaid();',
		));
		$rows = $_dbs->getAllFixedAsset();
		$opt =array(''=>$this->tr->translate("SELECT_FIXED_ASSET"));
		if(!empty($rows))foreach($rows AS $row) $opt[$row['id']]=$row['name'];
		$_fixed_id->setMultiOptions($opt);
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required'=>'true',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside',
			'onchange'=>'getNewReceiptNo("");',
		));
		
		$opts = $_db->getViewListById(18,1);
		$payment_method->setMultiOptions($opts);
		
		$note_payment = new Zend_Dojo_Form_Element_Textarea('note_payment');
		$note_payment->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'style'=>'min-height:40px;font-family:Khmer os Battambang',
				'class'=>'fullside'
		));
		
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$_category->setValue($data['cat_id']);
			$_currency_type->setValue($data['curr_type']);
			$_Date->setValue($data['for_date']);
			$_Description->setValue($data['desc']);
			if(!empty($data['fixedasset_id'])){
				$_fixed_id->setValue($data['fixedasset_id']);
			}
			$invoice->setValue($data['invoice']);
			$total_amount->setValue($data['total_amount']);
			$title->setValue($data['title']);
			$_stutas->setValue($data['status']);
			$id->setValue($data['id']);
			$payment_method->setValue($data['payment_method']);
			$note_payment->setValue($data['payment_note']);
			if (!empty($data['name'])){
				$name->setValue($data['name']);
			}
			if (!empty($data['phone'])){
				$phone->setValue($data['phone']);
			}
			if (!empty($data['sex'])){
				$_sex->setValue($data['sex']);
			}
		}
		
		$this->addElements(array($note_payment,$payment_method,$_fixed_id,$name,$phone,$_sex,$_branch_id,$_category,$_cat_expend,$invoice,$_currency_type,$title,$_Date ,$_stutas,$_Description,
				$total_amount,$convert_to_dollar,$for_date,$id,));
		return $this;
		
	}	
}