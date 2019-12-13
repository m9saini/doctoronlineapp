<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Services extends CI_Controller {

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
    	$this->collection='providerServices';
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
		$data['timezone']= $this->sessionData['timezone'];
		$data['title']      = 'Providers Services list';
		$data['CollectionKey']      = 'PDSE'; // Collection key define function in helper 
		$data['CollectionField']    = 'status'; // Collection field
		$data['ShowAction']      	=  3; // List action show 3 (active/de,view, del) ,2 (active/de,view) and 3(view)
		$data['ActionEdit']      	=  true;
		$selects= $data['DataHeading']=array('name','created','updated');
		/* if($this->input->post()){
			$params= $this->input->post(); 
			if(isset($params['name']) && !empty($params['name']) ) {
				$wheres=['name'=>ucwords(trim($params['name']))];
				$addService=['name'=>ucwords(trim($params['name']))];
				$checkExist=$this->CommonModel->alreadyExists($this->collection,$wheres);
				if(empty($checkExist)){
					$ids=$this->CommonModel->upsert($this->collection,$addService);
						if($ids){
								$this->session->set_flashdata('flashSuccess','SuccessFully Services Added.');
								$data['DataList']=$this->CommonModel->getCollectionData($this->collection);
								redirect('admin/providers/services-list');
						}else{
							$this->session->set_flashdata('flashError','Please try again');
						}
				}else {
					$this->session->set_flashdata('flashError','Services name already Exists');
				}
				} else {
				$this->session->set_flashdata('flashError','Please enter services name');
			}

		} */ 
		$data['DataList']=$this->CommonModel->getCollectionData($this->collection);
		$this->load->view('admin/providers/services/view',$data);

	}

	
	public function edit()
	{
		if($this->input->post()){
			$params=$this->input->post();

			if(isset($params['name']) && !empty($params['name'])){

				$id=$update=NULL;
				if(isset($params['id']) && !empty($params['id'])){
					$id=$params['id'];
					$update=true;
				}
				$wheres=['name'=>ucwords(trim($params['name']))];
				$checkExist=$this->CommonModel->alreadyExists($this->collection,$wheres,$id);
				if(empty($checkExist)){
				$ids=$this->CommonModel->upsert($this->collection,$wheres,$id,$update);
				echo ($ids)?json_encode(array('id'=>$ids)):'Please try again'; die;
				}else{
					echo json_encode(array('Services Name already exists.')); die;
				}
			}else{
				echo json_encode(array("Please enter services name")); die; 
			}
			
		} else{
			echo json_encode(array("Please enter services name")); die;
		}
	}
   
}
