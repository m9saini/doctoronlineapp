<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	  public function __construct()
        {
			parent::__construct();
            $this->load->helper(array('url','form'));
            $this->load->library('session');
	        $this->load->model('AdminProfileModel','Admin');
        }
	public function index()
	{
		$result=$this->session->userdata('admin');
		if($result)
			redirect('/admin/dashboard');
		$this->load->view('index');
	}
	public function login()
	{

		 $result=$this->session->userdata('admin');
		if($result)
			redirect('/admin/dashboard'); 
		 if($this->input->post()){ 

		 	$this->load->library('form_validation');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'password', 'required');
            if ($this->form_validation->run() == true) {
                 	$result=$this->Admin->login($this->input->post());
                 	if($result){
                 		$this->session->set_flashdata('flashSuccess','SuccessFully Logined.');
                 		$result[0]['timezone']="Asia/kolkata";
		                $this->session->set_userdata(array('admin'=>$result[0]));
		                redirect('/admin/dashboard');
               		}else{
               				$this->session->set_flashdata('flashError','Invalid Email or Password ');
               		}
            } else
            {
            	$this->session->set_flashdata('flashError','Email and Password required');
            }
        }
		$this->load->view('index');
	}
   
}
