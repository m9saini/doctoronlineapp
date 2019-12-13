<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends CI_Controller {

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
       	$slug_config = array('table' => 'pages','id' => '_id','field' => 'slug','title' => 'title','replacement' => 'dash');
        $this->load->helper(array('url','form'));
        $this->load->library(array('session','form_validation')); 
       	$this->load->library('slug', $slug_config);
        $this->output->set_template('main');
        
    	$this->collection='pages';
        $result=$this->session->userdata('admin');
		if(empty($result))
			redirect('/');
		else {
			$this->sessionData=$result;
		}  
    
    }

	 
    public function index()
    {
        $data['title']      = 'List of Static Pages';
        $data['page_list']	= $this->CommonModel->getCollectionData($this->collection);
		$this->load->view("admin/pages/index", $data);
    }

   public function add(){
   	$data['title']      = 'Create New Pages';
   	if($this->input->post()){
   		$prams=$this->input->post();
   		$validation=[['field'=>'title','rules'=>'trim|required'],['field'=>'heading','rules'=>'trim|required'],
   					 ['field'=>'content','rules'=>'required']
   					 ];
   		$data['page_data']=$prams;
   		$this->form_validation->set_data($prams);
        $this->form_validation->set_rules($validation);
        if($this->form_validation->run()==TRUE){
        		
        		$add['title']=ucwords($prams['title']);
        		$add['slug']=$this->slug->create_uri(['title'=>$prams['title']]); 
        		$add['heading']=ucwords($prams['heading']);
        		$add['content']=$prams['content'];
        		$this->CommonModel->upsert($this->collection,$add);

                $this->session->set_flashdata('flashSuccess','This page has been successfully created.');
				redirect('admin/pages');
        }else{
        	$this->session->set_flashdata('flashError','Please enter value in content');
        }
   	}
	$this->load->view("admin/pages/add", $data);
   }

   public function edit(){
   	$data['title']      = 'Edit Pages';
   	$slug = $this->uri->segment(4); 
   	$page_info= $this->CommonModel->getCollectionData('pages',['slug'=>$slug]);
   	$data['page_data']=$page_info[0];
   	if($data['page_data']){
		   		if($this->input->post()){
		   		$prams=$this->input->post();
		   		$validation=[['field'=>'title','rules'=>'trim|required'],['field'=>'heading','rules'=>'trim|required'],
		   					 ['field'=>'content','rules'=>'required']
		   					 ];
		   		$this->form_validation->set_data($prams);
		        $this->form_validation->set_rules($validation);
		        if($this->form_validation->run()==TRUE){
		        		
		        		$add['title']=ucwords($prams['title']);
		        		$add['heading']=ucwords($prams['heading']);
		        		$add['content']=$prams['content'];
		        		$this->CommonModel->upsert($this->collection,$add,$page_info[0]['_id']->{'$id'},true);

		                $this->session->set_flashdata('flashSuccess','This page has been successfully updated.');
						redirect('admin/pages');
		        }else{
        			$this->session->set_flashdata('flashError','Please enter value in content');
        		}
   			}
		$this->load->view("admin/pages/edit", $data);
	}else{
		$this->session->set_flashdata('flashError','This page could not be found.');
		redirect('admin/pages');
	}
   }

 
	
	public function delete(){
		
		$u_id = $this->Commonmodel->_update("webpages", array('delete_status'=>'1'),array('id'=>$pageid));
		if($u_id){
			$this->session->set_flashdata('flashSuccess','This page has been successfully updated.');
			redirect('admin/Pages/viewlist');
		}
		else{
			$this->session->set_flashdata('flashError','This page could not be updated please try again.');
			redirect('admin/Pages/viewlist');
		}
		
	}
}
