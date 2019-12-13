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
        $this->load->helper(array('url','form'));
        //$this->output->set_template('main');
    	$this->collection='pages';
    }

	 
   public function index()
    {
        //$this->output->set_template();
        $slug = $this->uri->segment(2);
        if($slug){ 
        	
	        $Exists=$this->CommonModel->getCollectionData('pages',['slug'=>$slug,'status'=>1]);
	        if($Exists){
		        $data['title']      = $Exists[0]['title'];
		        $data['content']	= $Exists[0];
				$this->load->view("pages/index", $data);
			}else{
				echo "<p>Page Not Found</p>";	
			}
		}else{
			echo "<p>Page Not Found </p>";
		}
		
    }


}
