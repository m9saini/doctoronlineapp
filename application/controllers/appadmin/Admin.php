<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

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
        $this->output->set_template('main');
        $this->load->model('AdminProfileModel','Admin');
        $result=$this->session->userdata('admin');
		if(empty($result))
			redirect('/');
		else {
			$this->sessionData=$result;
		}   
    }

	 
    public function index()
    {
        $data['title']      = 'Dashboard';
        //$app_type=['Home'];
        $data['patients_count']=$this->CommonModel->callAggregate('patients','count');
        $data['providers_count']=$this->CommonModel->callAggregate('providers','count');
        $data['appointments_count']=$this->CommonModel->callAggregate('patientAppointments','count');
        //$b_search=['patient_status'=>1,'provider_status'=>1,'appointment_type'=>['$nin'=>$app_type]];
        //echo $app_count=$this->CommonModel->callAggregate('patientAppointments','count',$b_search);
        $h_search=['patient_status'=>2,'provider_status'=>2];
        $home_app_count=$this->CommonModel->callAggregate('patientAppointments','count',$h_search);
        $data['booked_count']=$home_app_count;
		$this->load->view("admin/dashboard", $data);
    }

    public function profile(){
    	$data['title']      = 'Profile';
    	$data['User_Detail']=$this->Admin->get_data($this->sessionData['email']);
    	if($this->input->post()){ 

            $params=$this->input->post();
            /*      * *** Profile Image Update *******      */
            $image = (isset($_FILES['image']['name']) &&  !empty($_FILES['image']['name'])) ?$_FILES['image']['name']:'';

                 if(!empty($image))
                    {
                        $this->load->library('upload');
                        $config['upload_path'] = './assets/';
                        $config['allowed_types'] = 'jpg|jpeg|png';
                        $config['max_size'] = '100000000000';
                        $config['overwrite'] = TRUE;

                        $title = date('YmdHis');
                        $rand = rand(100000,999999);
                        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $fileName = 'admin'.'.'.$ext;
                        $image = $fileName;
                        $config['file_name'] = $fileName;
                        $this->upload->initialize($config);
                        if ($this->upload->do_upload('image')){
                            $this->upload->data();
                            $image = $fileName;
                            /*** Image resize ****/
                            $this->load->library('image_lib');
                            $resize['image_library'] = 'gd2';
                            $resize['source_image'] = './assets/'.$image;
                            $tnumb = 'admin'.'_thumb.'.$ext;
                            $resize['new_image'] = "./assets/$tnumb";
                            $resize['maintain_ratio'] = TRUE;
                            $resize['width']         = 160;
                            $resize['height']       = 160;
                            $this->image_lib->initialize($resize);
                            $this->image_lib->resize();
                            $params['photo'][0]=$image;
                            $params['photo'][1]=$tnumb;
                    
                        } 
                    }
                /* ****** End Profile Imahe Upoload Section *****     */
			if($this->Admin->update_profile($this->sessionData['email'],$params)){
 				$this->session->set_flashdata('flashSuccess','SuccessFully Your profile updated');
				redirect('/admin/profile');
			} else {
				$this->session->set_flashdata('flashError','Please try again');
			}
			
		}
		$this->load->view("admin/profile", $data);
    }
    
    public function change_password(){
    	$data['title']      = 'Change Password';
        $param=[];
    	if($this->input->post()){ 
            $post_data= $this->input->post();
            $param['email']=$this->sessionData['email'];
            $param['password']=$post_data['old_password'];
    		if($this->Admin->login($param)){
				if($this->Admin->change_password($this->sessionData['email'],$post_data['new_password'])){
					$this->session->set_flashdata('flashSuccess','SuccessFully Your password updated');
					redirect('/admin/dashboard');
				} else {
					$this->session->set_flashdata('flashError','Please try again');
				}
			} else{
					$this->session->set_flashdata('flashError','Old Password Mismatch');
			}
		}
		$this->load->view("admin/change_password", $data);

    }

    /**
     * @param array
     * @function is used to updated value by admin user
     * @return true/false
     */
    public function statusUpdatedByAdmin(){
    	$formData=$this->input->post(); 
    	if(isset($formData['object_id']) && isset($formData['type']) && isset($formData['fieldtype'])){ 

    		$status=(isset($formData['status']) && $formData['status']==1)?1:0;
    		$updates=array($formData['fieldtype']=>$status); 
    		// get colections name
    		$collections=getDbvs($formData['type']);
            if($collections){
                if(isset($formData['data_type']) && $formData['data_type']=='list' && count($formData['data_list'])>0)
                {
                    for($i=0;$i<count($formData['data_list']);$i++){

                        $this->CommonModel->upsert($collections,$updates,$formData['data_list'][$i],true);
                    }
                $resultData=1;
                }else
    		      $resultData=$this->CommonModel->upsert($collections,$updates,$formData['object_id'],true);
                  if($resultData){
                    switch($collections){
                        case 'patients' || 'providers':
                            $selected=['device_token','mobile','country_code','email'];
                            if($status){
                                $title='Active';
                                $notification_msg='Your account activated,login to continue process';
                                $type='Active';
                            }else{
                                $title='Deactivate';
                                $notification_msg='Your account deactivated Please contact to admin';
                                $type='Logout';
                            }
                                if(isset($formData['data_type']) && $formData['data_type']=='list' && count($formData['data_list'])>0)
                                {                            
                                    for($i=0;$i<count($formData['data_list']);$i++){
                                        $search=['_id'=> new \MongoId($formData['data_list'][$i])];
                                        $result=$this->CommonModel->getCollectionData($collections,$search,$selected);
                                        sendNotification($result[0]['device_token'],$title,$notification_msg,$type,0);
                                    }
                                }else{
                                    $search=['_id'=> new \MongoId($formData['object_id'])];
                                    $result=$this->CommonModel->getCollectionData($collections,$search,$selected);
                                    sendNotification($result[0]['device_token'],$title,$notification_msg,$type,0);
                                }
                        break;
                    }
                    echo 1;
                }else{
                    echo 404;
                }
            }else{
                echo 404;
            }	
		} else {

			echo 404; 
		}
		
	die;
    }

    /**
     * @param array
     * @function is used to soft delate or permanently delete by admin user
     * @return true/false
     */
    public function deletedByAdmin(){ 
    	$formData=$this->input->post(); 
    	if(isset($formData['object_id']) && isset($formData['type'])){ 
    		// get colections name 
            $timezone=$this->sessionData['timezone'];
    		$collections=getDbvs($formData['type']);
            if($collections){
                $selected=['device_token','mobile','country_code','email'];
                $title='Deleted';
                $notification_msg='Your account deleted Please contact to admin';
                $type='Logout';
            $permanentlyDel= (isset($formData['del_type']) && $formData['del_type']=='del')?true:false;
                if(isset($formData['data_type']) && $formData['data_type']=='list' && count($formData['data_list'])>0)
                {
                      foreach ($formData['data_list'] as $key => $value) {

                        switch($collections){
                        case 'patients' || 'providers':
                            $search=['_id'=> new \MongoId($formData['data_list'][$i])];
                            $result=$this->CommonModel->getCollectionData($collections,$search,$selected);
                            sendNotification($result[0]['device_token'],$title,$notification_msg,$type,0);                            
                        break;
                        }

                        $this->CommonModel->delete($collections,["_id"=> new \MongoId($value)],$timezone,$permanentlyDel);
                    }
                $resultData=1;
                }else{
    		            switch($collections){
                        case 'patients' || 'providers':                         
                                    $search=['_id'=> new \MongoId($formData['object_id'])];
                                    $result=$this->CommonModel->getCollectionData($collections,$search,$selected);
                                    sendNotification($result[0]['device_token'],$title,$notification_msg,$type,0);
                                
                        break;
                        }

                        $resultData=$this->CommonModel->delete($collections,["_id"=> new \MongoId($formData['object_id'])],$timezone,$permanentlyDel);
                }
                
                if($resultData){
                    
                    echo 1;
                }else{
                    echo 404;
                }
            }else{
                echo 404;
            }	
		} else {

			echo 404; 
		}
		
	die;
    }

    /**
     * @param array
     * @function is used to soft delate or permanently delete by admin user
     * @return true/false
     */
    public function restoreByAdmin(){
        $formData=$this->input->post();
        if(isset($formData['object_id']) && isset($formData['type'])){ 
            // get colections name
            $collections=getDbvs($formData['type']);
            if($collections){
                if(isset($formData['data_type']) && $formData['data_type']=='list' && count($formData['data_list'])>0)
                {
                    for($i=0;$i<count($formData['data_list']);$i++){

                        $this->CommonModel->delete($collections,array(),$formData['data_list'][$i],$permanentlyDel);
                    }
                $resultData=1;
                }else
                    $resultData=$this->CommonModel->upsert($collections,array(),$formData['object_id'],true);

                if($resultData){
                    switch($collections){
                        case 'patients' || 'providers':
                            $selected=['device_token','mobile','country_code','email'];
                            $title='Restore';
                            $notification_msg='Your account activated';
                            $type='Restore';
                                if(isset($formData['data_type']) && $formData['data_type']=='list' && count($formData['data_list'])>0)
                                {                            
                                    for($i=0;$i<count($formData['data_list']);$i++){
                                        $search=['_id'=> new \MongoId($formData['data_list'][$i])];
                                        $result=$this->CommonModel->getCollectionData($collections,$search,$selected);
                                        sendNotification($result[0]['device_token'],$title,$notification_msg,$type,0);
                                    }
                                }else{
                                    $search=['_id'=> new \MongoId($formData['object_id'])];
                                    $result=$this->CommonModel->getCollectionData($collections,$search,$selected);
                                    sendNotification($result[0]['device_token'],$title,$notification_msg,$type,0);
                                }
                        break;
                    }
                    echo 1;
                }else{
                    echo 404;
                } 
            } else{
                echo 404;
            }  
        } else {

            echo 404; 
        }
        
    die;
    }
    

    public function logout(){
       
        $this->session->sess_destroy();
        redirect('/');
    }

}
