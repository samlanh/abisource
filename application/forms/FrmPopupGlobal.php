<?php

class Application_Form_FrmPopupGlobal extends Zend_Dojo_Form
{

protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_num;
	protected $text;
	protected $tarea;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function addProgramName($data=null,$type=null){
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_db = new Application_Model_DbTable_DbGlobal();
		if(!empty($type)){
			$rows = $_db->getServiceType(2);
		}else{
			$rows = $_db->getServiceType(1);
		}
		
		//array_unshift($rows,array('id' => '-1',"title"=>$this->tr->translate("ADD")) );
		$opt = "";
		if(!empty($rows))foreach($rows AS $row) $opt[$row['id']]=$row['title'];
		$_service_name = new Zend_Dojo_Form_Element_FilteringSelect("type");
		$_service_name->setMultiOptions($opt);
		$_service_name->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',));
		
		$_desc = new Zend_Dojo_Form_Element_Textarea('desc');
		$_desc->setAttribs(array('dojoType'=>$this->tarea,'class'=>'fullside','style'=>'width:96%;min-height:50px;'	));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_program');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				2=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		if (!empty($data)){
			$_title->setValue($data['title']);
			$_desc->setValue($data['desc']);
		}
		$this->addElements(array($_service_name,$_title,$_desc,$_status));
		return $this;
	}
	public function addProServiceCategory($data=null){
		$_title = new Zend_Dojo_Form_Element_ValidationTextBox('servicetype_title');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside','required'=>'true'));
		
		$_tem_desc = new Zend_Dojo_Form_Element_TextBox('item_desc');
		$_tem_desc->setAttribs(array('dojoType'=>$this->text,'required'=>'true','class'=>'fullside',));
	
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('sertype_status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('ser_type');
		$_status_type = array(
				1=>$this->tr->translate("SERVICE"),
				2=>$this->tr->translate("PROGRAM"));
		$_type->setMultiOptions($_status_type);
		
		$_type->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		
		$_id = new Zend_Form_Element_Hidden('id');
		$_id->setAttribs(array('dojoType'=>$this->text));
		
		if($data !=null){
			$_id->setValue($data['id']);
			$_title->setValue($data['title']);
			$_tem_desc->setValue($data['item_desc']);
			$_status->setValue($data['status']);
			$_type->setValue($data['type']);
			
		}
		$this->addElements(array($_title,$_tem_desc,$_status,$_type,$_id));
	
		return $this;
	
	}
	
	function footerReportAccountInEnglish(){
		$str='
				<table width="100%" border="0" style="white-space: nowrap;font-size: 13px;">
					<tr align="center">
						<td width="15%">Auditor</td>
						<td width="18%">Senior Accountant</td>
						<td width="18%">Accountant Income</td>
						<td width="16%">Accounting Manager</td>
						<td width="18%">School Principal</td>
						<td width="15%">Cashier</td>
					</tr>
					<tr>
						<td colspan="6">&nbsp;</td>
					</tr>
					<tr align="center">
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
					</tr>
				</table>
		';
		return $str;
	}
	function footerReportAccountStockInEnglish(){
		$str='
				<table width="100%" border="0" style="white-space: nowrap;font-size: 13px;">
					<tr align="center">
						<td width="15%">Auditor</td>
						<td width="18%">Accountant Income</td>
						<td width="16%">Accounting Manager</td>
						<td width="18%">Cash Collector</td>
						<td width="18%">School Principal</td>
						<td width="15%">Cashier</td>
					</tr>
					<tr>
						<td colspan="6">&nbsp;</td>
					</tr>
					<tr align="center">
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
						<td>
							<div style="font-size: 13px;line-height: 20px;">
								Name:..............................<br />
								Date:........../........../..........
							</div>
						</td>
					</tr>
				</table>
		';
		return $str;
	}
	function footerReportAccountInKhmer(){
		
		$str='
			<table width="100%" border="0" style="white-space: nowrap;font-size: 13px;">
				<tr align="center">
					<td width="15%">សវនករ</td>
					<td width="18%">ប្រធានក្រុមគណនេយ្យ</td>
					<td width="18%">គណនេយ្យចំណូល</td>
					<td width="16%">ប្រធានផ្នែកគណនេយ្យ</td>
					<td width="18%">ប្រធានសាខា</td>
					<td width="15%">បេឡាករ</td>
				</tr>
				<tr>
					<td colspan="6">&nbsp;</td>
				</tr>
				<tr align="center">
					<td>
						<div style="font-size: 13px;line-height: 20px;">
							ឈ្មោះ:..............................<br />
							ថ្ងៃទី:........../........../..........
						</div>
					</td>
					<td>
						<div style="font-size: 13px;line-height: 20px;">
							ឈ្មោះ:..............................<br />
							ថ្ងៃទី:........../........../..........
						</div>
					</td>
					<td>
						<div style="font-size: 13px;line-height: 20px;">
							ឈ្មោះ:..............................<br />
							ថ្ងៃទី:........../........../..........
						</div>
					</td>
					<td>
						<div style="font-size: 13px;line-height: 20px;">
							ឈ្មោះ:..............................<br />
							ថ្ងៃទី:........../........../..........
						</div>
					</td>
					<td>
						<div style="font-size: 13px;line-height: 20px;">
							ឈ្មោះ:..............................<br />
							ថ្ងៃទី:........../........../..........
						</div>
					</td>
					<td>
						<div style="font-size: 13px;line-height: 20px;">
							ឈ្មោះ:..............................<br />
							ថ្ងៃទី:........../........../..........
						</div>
					</td>
				</tr>
			</table>
		';
		return $str;
	}
	function footerReportAdminInKhmer(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='
				<table width="100%" border="0" style="white-space: nowrap;font-size: 13px;">
						<tr>
							<td align="center" width="25%">
								<span style="font-size: 14px;">'.$tr->translate("APPROVED_BY").'</span>
							</td>
							<td align="center" width="25%">
								<span style="font-size: 14px;">'.$tr->translate("VERIFIED_BY").'</span>
							</td>
							<td align="center" width="25%">
								<span style="font-size: 14px;">'.$tr->translate("CHECKED_BY").'</span>
							</td>
							<td align="center"  width="25%">
								<span style="font-size: 14px;text-align: right;">'.$tr->translate("PREPARED_BY").'</span>
							</td>
						</tr>
					</table>
		';
		return $str;
	}
	function footerReportAdminEnglish(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='
				<table width="100%">
						<tr>
							<td align="center" width="25%">
								<span style="font-weight: bold;font-size: 14px;">Approved by</span>
							</td>
							<td align="center" width="25%">
								<span style="font-weight: bold;font-size: 14px;">Verified by</span>
							</td>
							<td align="center" width="25%">
								<span style="font-weight: bold;font-size: 14px;">Checked by</span>
							</td>
							<td align="center"  width="25%">
								<span style="font-weight: bold;font-size: 14px;">Prepared by</span>
							</td>
						</tr>
					</table>
		';
		return $str;
	}
}

