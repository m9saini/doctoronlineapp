<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class EmailModel extends CI_Model {
    //put your code here
    public function __construct() {
		$this->load->helper('url');
		$this->load->library('email');
    }
 

	public function sendEmail($mailto ,$mailsubject, $mailbody){
		
		
		
		$config = Array(
	    'protocol' => 'smtp',
	    'smtp_host' => 'ssl://smtp.googlemail.com',
	    'smtp_port' => 465,
	    'smtp_user' => 'developer@brsoftech.com',
	    'smtp_pass' => 'brsoft@123',
	    'mailtype'  => 'html', 
	    'charset'   => 'iso-8859-1'
	);


		$this->email->initialize($config);
		$this->email->from(SITE_SUPPORT_MAIL, SITE_NAME);
		$this->email->to($mailto);
		$this->email->subject($mailsubject);
		$this->email->message($mailbody);
		$this->email->send();
	}
	
	//================= Test Second Mail using Gmail smtp =========================
	
	//-------------------------------------------------------------------------------------------
	
	public function sendMailWithAttachment($mailto,$fileLocation ,$mailsubject, $mailbody){
		$admin_detail = $this->Commonmodel->_get_data('admin',array('admin_id'=>'1'));
		$admin_email = $admin_detail[0]['admin_email'];
		$mailto = "test@niceappstore.com";
		
		$config = array(
				'protocol' => 'mail',
				'smtp_host' => '',
				'smtp_port' => 25,
				'smtp_user' => '',
				'smtp_pass' => '',
				'mailtype' => 'html',
				'charset'  => 'iso-8859-1',
				'priority' =>1
			);
 
	
		
		$this->email->initialize($config);
		$this->email->from(SITE_SUPPORT_MAIL, SITE_NAME);
		$this->email->to($mailto);
		$this->email->subject($mailsubject);
		$this->email->message($mailbody);
		$this->email->send();
		
	}
	
	
		
}
?>