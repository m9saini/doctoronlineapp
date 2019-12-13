<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Provider extends CI_Controller {

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

		$this->load->library('encryption');
		$this->load->helper(array('url','form'));
    	$this->output->set_template('main');
    	$this->load->library('session');  
    	$this->collection='providers';
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
		$data['title']      = 'Providers list';
		$data['CollectionKey']      = 'PDS'; // Collection key define function in helper 
		$data['CollectionField']    = 'status'; // Collection field
		$data['ShowAction']      	=  3; // List action show 3 (active/de,view, del) ,2 (active/de,view) and 3(view)
		$data['ActionView']    		=  base_url('admin/providers-view/');
		$selects= $data['DataHeading']=array('name','email','mobile','gender','created');
		if($type=='deleted')
			$data['DataList']=$this->CommonModel->getDeletedDocument($this->collection,'deleted',array(''));
		else if($type=='deactive')
			$data['DataList']=$this->CommonModel->getCollectionData($this->collection,array('status'=>0));
		else 
			$data['DataList']=$this->CommonModel->getCollectionData($this->collection,[],[],['created'=> -1],150,0);
		$this->load->view('admin/providers/list',$data);
	}
	public function view()
	{
		
		$result= getProviderData($this->uri->segment(3)); 
		if($result['status']){
			$data['title']      = 'Provider Profile';
			$wheres=["userid"=> $result['data']['_id']];
			$data['providersData']= $result['data'];
			$data['educatoinsData']=$this->CommonModel->getCollectionData('providerEducations',$wheres);
			$data['accountsData']=$this->CommonModel->getCollectionData('providerAccounts',$wheres);
			$data['worksData']=$this->CommonModel->getCollectionData('providerWorks',$wheres);
			if(isset($result['data']['speciality_ids']) && is_array($result['data']['speciality_ids']) && count($result['data']['speciality_ids'])>0)
			$data['specialities']=$this->CommonModel->getCollectionData('speciality',['_id'=>['$in'=>$result['data']['speciality_ids']]]);
			$this->load->view('admin/providers/view',$data);
		} else{
			redirect('/admin/providers');
		}
	}
   
}
