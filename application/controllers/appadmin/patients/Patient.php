<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Patient extends CI_Controller {

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
        	$this->output->set_template('main');
        	$this->load->library('session');  
        	$this->collection='patients'; 
        	$result=$this->session->userdata('admin');
			if(empty($result))
				redirect('/');
			else {
				$this->sessionData=$result;
			}  
        	
        }

	 
	public function index()
	{
		$data['DataListType']= $type =$this->uri->segment(3);
		$data['title']      = 'Patients list';
		$selects= $data['DataHeading']=array('name','email','mobile','gender','created');
		if($type=='deleted')
			$data['DataList']=$this->CommonModel->getDeletedDocument($this->collection,'deleted',array(''));
		else if($type=='deactive')
			$data['DataList']=$this->CommonModel->getCollectionData($this->collection,array('status'=>0));
		else
			$data['DataList']=$this->CommonModel->getCollectionData($this->collection,[],[],['created'=> -1],150,0);
		$this->load->view('admin/patients/list',$data);
	}
	public function view()
	{
		$result= getPatientData($this->uri->segment(3)); 
		if($result['status']){
			$i=0;
			$appointment_type=[];
			$data['title']      = 'Patients Profile';
			$data['patientData']= $result['data'];
			$wheres=['userid'=> $result['data']['_id']];
			$data['cardsData']=$this->CommonModel->getCollectionData('patientCards',$wheres);
			$app_type=['Video','Audio','Call','Chat','Home','Walkin'];
			foreach ($app_type as $key => $value) {
				$exits_search['appointment_type']=['$in'=>[$app_type[$key]]];
				$exits_search['patient_id']= $result['data']['_id'];
				$appointment_type_exists=$this->CommonModel->getCollectionData('patientAppointments',$exits_search);
				if($appointment_type_exists){
					$appointment_type[$i]=$value;
					$i++;
				}
			}
			$data['appointment_type']=$appointment_type;
			$data['Waiting']=$this->CommonModel->getCollectionData('patientAppointments',$wheres);;
			$data['Booked']=0;
			$data['Canceled']=0;
			$this->load->view('admin/patients/view',$data);
		} else{
			redirect('/admin/patients');
		}
	}
   
}
