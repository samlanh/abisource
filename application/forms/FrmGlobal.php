<?php

class Application_Form_FrmGlobal{
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function getReceiptFooter(){
		$_dbmodel = new Application_Model_DbTable_DbKeycode();
		$keycode=$_dbmodel->getKeyCodeMiniInv(TRUE);
			$str="";
			$str.="<tr bgcolor='6D5CDD'>";
				$str.='<td colspan="4" style="text-align: center; color:#fff;background:#6D5CDD;">';
				$brachs = explode('/',$keycode['footer_branch']);
					$str.='<ul style="list-style-type: none;float:left; text-align: left;padding-left:10px;">';
						foreach ($brachs AS $key =>$branch){
							$str.="<li> $branch;</li>";
						}
					$str.='</ul>';
					$phones = explode('/',$keycode['foot_phone']);
					$str.='<ul style="list-style-type: none;float:left;text-align: left;padding-left:10px;">';
						foreach ($phones AS $key =>$phone)
							$str.="<li> $phone </li>";
					$str.='</ul>';
					$contacts= explode('/',$keycode['f_email_website']);
					$str.='<ul style="list-style-type: none;float:left;text-align: left;padding-left:10px;">';
						foreach ($contacts AS $key =>$contact){
							$str.="<li> $contact </li>";
						}
					$str.='</ul>';
				$str.='</td>';
			$str.='</tr>';						
		return $str;
	}
	
	
	public function getReceiptHeader($payment_method,$branch_name,$receipt_type,$receipt_number){
		
		$logo = Zend_Controller_Front::getInstance()->getBaseUrl().'/images/logo.png';
		$double_underline = Zend_Controller_Front::getInstance()->getBaseUrl().'/images/double_underline.png';
		
		if($payment_method==1){
			$show_hide_header = "hidelabel showlabel";
		}else{
			$show_hide_header = "showlabel";
		}
		$str="";
		$str.='<table width="100%" style="white-space:nowrap; font-family:Khmer OS Battambang;margin-top:-10px;font-weight:bold;" >';
			$str.='<tr style="font-size:10px;border:1px solid #000;height:10px;">';
				$str.="<td width='25%' align='left' style='font-size:11px;'>";
					$str.="<div class='".$show_hide_header."' style='font-size:15px;'>";
						$str.=$branch_name;
					$str.='</div>';
					$str.=date('l , jS / m / Y , H:i:s ',strtotime(Zend_Date::now()));
				$str.='</td>';
				$str.="<td width='50%' align='center' class='".$show_hide_header."' >";
					$str.="<img style='width: 20%;' alt='' src='".$logo."'>";
				$str.='</td>';
				$str.="<td width='25%' class='".$show_hide_header."' align='center' style='font-size:14px;'>".$receipt_type."<br /><div style='font-size:17px;color:red;margin-top:-5px;'>N<sup>o</sup> : ".$receipt_number;
				$str.='</td>';
			$str.='</tr>';		
		$str.='</table>';
		$str.='<table width="100%" style="white-space:nowrap; font-family:Khmer OS Battambang;margin-top:-10px;font-weight:bold;" >';	
			$str.='<tr style="font-size:10px;border:1px solid #000;height:10px; ">';	
				$str.='<td width="40%">';
					$str.="<div class='".$show_hide_header."' ><img style='width: 100%;height:4px;' alt='' src='".$double_underline."' ></div>";
				$str.='</td>';
				
				$str.="<td width='20%' align='center' style='font-size:16px;'><div class='".$show_hide_header."'  >បង្កាន់ដៃទទួលប្រាក់ RECEIPT</div> ";
				
				$str.='</td>';
				
				$str.='<td width="40%">';
					$str.="<div class='".$show_hide_header."' ><img style='width: 100%;height:4px;' alt='' src='".$double_underline."' ></div>";
				$str.='</td>';
			$str.='</tr>';	
		$str.='</table>';	
		return $str;
	}
	
}

