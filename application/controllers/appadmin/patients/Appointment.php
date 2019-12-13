<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 
 */
class Appointment extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->output->set_template('main');
        $this->load->library('session');  
        $this->load->model('AdminModel','Admin');
        $this->collection='patientAppointments';
        $result=$this->session->userdata('admin');
        if(empty($result))
            redirect('/');
        else {
            $this->sessionData=$result;
        }
    }

    
    /**
     * @param array 
     * @function is used to get Work list
     * @return true/false
     */
    public function index()
    {
        $wheres=[];
        $data['title']= "Patients Appointments list";
        $data['timezone']=$this->sessionData['timezone'];
        $data['DataListType']= $type =$this->uri->segment(3);
        $data['patient_id']= $patient_id = $this->uri->segment(4); 
        $list_startdate = $this->uri->segment(5); 
        $data['CollectionKey']      = 'PTS'; // Collection key define function in helper 
        $data['CollectionField']    = 'status'; // Collection field
        date_default_timezone_set($this->sessionData['timezone']);
        $data['startdate']=$data['enddate']=strtotime(date('m/d/Y'));
        if($list_startdate){
            $data['startdate']=$list_startdate;
        }
        $data['search_action']=0;
        $data['search_type']=0;
        $data['search_type_list']=$search_type_list = ["All","Audio","Chat","Home","Video","Walkin"];
        try{ 
	        if($this->input->post()){
	            $search=$this->input->post();
	            $data['startdate']=(strtotime((isset($search['startdate']) && !empty($search['startdate']))?$search['startdate']:date('m/d/Y')));
	            $data['enddate']=(strtotime((isset($search['enddate']) && !empty($search['enddate']))?$search['enddate']:date('m/d/Y')));
	            if(isset($search['search_action']) && !empty($search['search_action']) && $search['search_action']!=0){
	                $search['search_action']=(int)($search['search_action']==1)?0:$search['search_action'];
	                $wheres['patient_status']=$search['search_action'];
	            }
	            if(isset($search['type']) && !empty($search['type']) && $search['type']!=0 )
	                $wheres['appointment_type']=['$in'=>[$search_type_list[$search['type']]]];
	            $data['search_action']=((isset($search['search_action']) && !empty($search['search_action']))?$search['search_action']:0);
	            $data['search_type']=((isset($search['type']) && !empty($search['type']))?$search['type']:0);
	        }
	        $startdate=utc_date(date('Y-m-d',$data['startdate']),$this->sessionData['timezone'],true);
	        $enddate=utc_date(date('Y-m-d',$data['enddate']),$this->sessionData['timezone'],true);
	        if($patient_id)
                $wheres['patient_id']= new \MongoId($patient_id);
	        $wheres['appointment_date']=['$gte'=>$startdate,'$lte'=>$enddate];
            if($type=='booked'){
                $wheres=['patient_status'=>2,'provider_status'=>2];
            }
	        $data['DataList'] = $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres,[],['appointment_date'=>-1]);
	        date_default_timezone_set($this->sessionData['timezone']);
	        $data['startdate']=date('m/d/Y',$data['startdate']);
	        $data['enddate']=date('m/d/Y',$data['enddate']);
            $this->load->view('admin/patients/appointments/list',$data);
	    }catch (MongoException $ex) {
    		$data['heading']='Error';
            $data['message']=$ex->getMessage();
            $this->load->view('errors/cli/error_db',$data);
        }
        
    }

     /**
     * @param null
     * @function is used to get Work details
     * @return true/false
     */
    public function view()
    {
        $appointment_id = $this->uri->segment(4);
        
        try{ 
	        date_default_timezone_set($this->sessionData['timezone']);
            $data=$this->Admin->apppointment_view($appointment_id);
            $data['title']= "Patients Appointments View";
            $data['user_type']= $type = $this->uri->segment(2);
            $data['userid']=$user_id = $this->uri->segment(5);
            $data['app_date']=$app_date = $this->uri->segment(6);
            $data['timezone']=$this->sessionData['timezone'];
            if($data)
                $this->load->view('admin/patients/appointments/view',$data);
            else{
                
                $this->session->set_flashdata('flashError','Invalid Appointment');
                if($type=='provider'){
                    if($user_id){
                        if($app_date)
                            redirect("/admin/provider/schedule/$user_id/$app_date");
                        else
                            redirect("/admin/provider/schedule/$user_id");
                    }else{
                        redirect('/admin/providers');
                    }
                    
                }else{
                    if($user_id){
                        if($app_date)
                            redirect("/admin/patients/appointments-all-list/$user_id/$app_date");
                        else
                            redirect("/admin/patients/appointments-all-list/$user_id");
                    }else{
                        redirect('/admin/patients');
                    }
                }              
            }
    	}catch (MongoException $ex) {
    		$data['heading']='Error';
            $data['message']=$ex->getMessage();
            $this->load->view('errors/cli/error_db',$data);
        } 
    }
    
    
}
