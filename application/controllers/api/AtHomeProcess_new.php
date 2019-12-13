<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */

require(APPPATH . 'libraries/REST_Controller.php');

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class AtHomeProcess extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->model('ApiModel', 'Api');
    }

    /************************** Patient Authontication Apis *************************/

    /**
     * @param array
     * @function is used to get Common Appointment list with fillter
     * @return true/false
     */
    public function appointment_filter_list_post()
    {
        $response = $result= [];
        $params = $this->post();
        $latitude   =   $longitude  =   $miles  =   $sort_value     =   '';
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid', 'user_type'];
        $validation = $this->CommonModel->validation($params,$required);
        if(empty($validation) && in_array($params['user_type'],['provider']))
        {
            try{
                    $offset=(isset($params['offset']))?$params['offset']:0;
                    // fillter start ....
                    $wheres['appointment_type']=['$in'=>["Home"]];
                  //  $wheres['appointment_date']=['$gte'=>strtotime(date('Y-m-d'))];
                    if((isset($params['latitude']) && $params['latitude'] != '') && (isset($params['longitude']) && $params['longitude'] != '') && (isset($params['miles']) && $params['miles'] != '')){
                        $latitude   =      $params['latitude'];
                        $longitude  =      $params['longitude'];
                        $miles      =      $params['miles'];
                    }

                    if((isset($params['sort_type']) && $params['sort_type'] != '') && (isset($params['sort_value']) && $params['sort_value'] != '')){
                        if($params['sort_type'] == 'distance'){
                            $sort_value     =   $params['sort_value'];
                        }else if($params['sort_type'] == 'datetime'){
                            if($params['sort_value'] == 'up'){

                            }else if($params['sort_value'] == 'latest'){

                            }
                        }
                    }
                    /*if(isset($params['appoint_type']) && $params['appoint_type'] != ''){
                        if($params['appoint_type'] == 'waiting'){
                            $wheres['provider_status']  =   0;
                            $wheres[$params['user_type'].'_status']  =   0;
                        }
                    }*/
                    $where['_id']    =   new \MongoId($params['userid']);
                    $viewuser       =   $this->CommonModel->getCollectionData($params['user_type'] . 's', $where);
                    $provider_id= new \MongoId($params['userid']);
                    
                    if(isset($params['key_value'])){
                        $params['key_value']= (int)$params['key_value'];
                        if($params['key_value']==0){
                            //$wheres['doctor_ids']= ['$in'=>[$provider_id]];
                            //$wheres[$params['user_type'].'_status']=$params['key_value'];
                            
                            $wheres['confirm_doctors_ids']= ['$in'=>[$provider_id]];
                            //$wheres[$params['user_type'].'_status']=$params['key_value'];
                            $wheres['patient_status']=0;
                            //$or['$or']=['location'=>['$near'=>['$geometry'=>['type'=>"Point",'coordinates'=>[(int)$viewuser[0]['longitude'],(int)$viewuser[0]['latitude']]],'$minDistance'=>1000,'$maxDistance'=>5000]]];
                            //$or['$or']=['provider_status'=>[0,1]];
                        }else if($params['key_value']==1){
                            $wheres['patient_status']=1;
                            $wheres['provider_id']= $provider_id;
                            $wheres['provider_status']=$params['key_value'];
                        }else{
                            $wheres['provider_id']= $provider_id;
                            $wheres[$params['user_type'].'_status']=$params['key_value'];
                        }
                    }
                    
                    $dataList = $this->CommonModel->getCollectionData('patientAppointments',$wheres, [], ['created' => -1], 50, $offset);
                     if ($dataList) {

                        $result =   $this->appointment_array($dataList, $viewuser, $latitude, $longitude, $miles, $sort_value);

                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data']['appointment_status_list'] = $this->Api->appointment_status_list($params['user_type']);
                        $response['data']['appointment_list'] = $result;


                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'You have no any appointment.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            $response['error_data']= $validation;
        }
        $this->response($response);
    }

   


    /**
     * @param null
     * @function is used to set appointments price .
     * @return true/false
     */
    public function appointment_price_update_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid','appointment_id', 'patient_id','user_type', 'provider_price','free_services_ids','provider_currency'];
        $other=['free_services_ids'=>''];
        $validation = $this->CommonModel->validation($params, $required,$other);
        if (empty($validation) && in_array($params['user_type'], ['provider']) && is_array($params['free_services_ids']) ) {
            try {
                $appmentWheres = ['patient_id' => new \MongoId($params['patient_id']),
                    '_id' => new \MongoId($params['appointment_id'])
                ];
                $provider_id= new \MongoId($params['userid']);
                $checkValidAppment = $this->CommonModel->getCollectionData("patientAppointments", $appmentWheres, ['patient_id','doctor_ids','doctors']);
                if ($checkValidAppment) { 

                        $appmentUpdate['provider_status'] = 0;
                        $appmentUpdate['patient_status'] = 0;
                        foreach ($params['free_services_ids'] as $key => $value) {
                            $free_services_ids[] = new \MongoId($value);
                        }
                        if(isset($checkValidAppment[0]['doctor_ids']) && is_array($checkValidAppment[0]['doctor_ids'])){
                                $doctor=$checkValidAppment[0]['doctor_ids'];
                            }
                             else{
                                $doctor=[];
                            }
                            array_push($doctor,$provider_id);
                        if(isset($checkValidAppment[0]['confirm_doctors_ids']) && is_array($checkValidAppment[0]['confirm_doctors_ids'])){
                                $confirm_doctor=$checkValidAppment[0]['confirm_doctors_ids'];
                            }
                             else{
                                $confirm_doctor=[];
                            }
                            array_push($confirm_doctor,$provider_id);
                            array_push($doctor,$provider_id);
                        $homeDoctorInfo['provider_price'] = (int)$params['provider_price'];
                        $homeDoctorInfo['provider_currency'] = $params['provider_currency'];
                        $homeDoctorInfo['free_services_ids'] = $free_services_ids;
                        $appmentUpdate['doctor_ids'] = $doctor;
                        $appmentUpdate['confirm_doctors_ids'] = $confirm_doctor;
                        if(isset($checkValidAppment[0]['doctors']) && is_array($checkValidAppment[0]['doctors'])){
                                $appmentUpdate['doctors']=$checkValidAppment[0]['doctors'];
                            }
                             else{
                                $appmentUpdate['doctors']=[];
                            }
                        
                        $appmentUpdate['doctors']=array_merge($appmentUpdate['doctors'],[$params['userid']=>$homeDoctorInfo]);
                        $Updated = $this->CommonModel->upsert('patientAppointments', $appmentUpdate, $params['appointment_id'],true);

                        if ($Updated) {
                            $response["status"] = 1;
                            $response["message"] = "Price Updated";
                        }else{
                            $response["status"] = 0;
                            $response["message"] = "Price not updated";
                        }


                } else {
                    $response["status"] = 0;
                    $response["message"] = "You have not any appointment";
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = "Invalid Object id";
            }
        } else {
            $response["status"] = 0;
            $response["message"] = "Mandatory fields are required.";
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

    /**
     * @param null
     * @function is used to get appointments status updated both provider and patient.
     * @return true/false
     */
    public function appointment_status_update_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['patient_id', 'provider_id','appointment_id', 'user_type', 'appointment_status'];
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {
                $appmentWheres = ['patient_id' => new \MongoId($params['patient_id']),
                    '_id' => new \MongoId($params['appointment_id'])
                ];

                $checkValidAppment = $this->CommonModel->getCollectionData("patientAppointments", $appmentWheres, ['patient_id','doctor_ids']);
                if ($checkValidAppment) {

                        $provider_id = new \MongoId($params['provider_id']);
                        $appointment_status= (int) $params['appointment_status'];
                        $appmentStatusSearch['patient_id'] = new \MongoId($params['patient_id']);
                        $response["status"] = 1;
                        if ($params['user_type'] == 'provider') {

                            
                            if($appointment_status==2 || $appointment_status==3)
                            {
                                $appmentStatusSearch['provider_id'] = $provider_id;

                            }

                            if($appointment_status==2)
                            {
                                $appmentStatusSearch['patient_status'] = $appointment_status;

                            }
                            $appmentStatusSearch['provider_status'] = $appointment_status;

                            $response['message'] = ucwords($params['user_type']) . " booking status updated";
                        } else {
                            if($appointment_status==0){

                                 if(isset($checkValidAppment[0]['doctor_ids']) && is_array($checkValidAppment[0]['doctor_ids'])){
                                    $doctor=$checkValidAppment[0]['doctor_ids'];

                                }
                                 else{
                                    $doctor=[];
                                }
                                array_push($doctor,$provider_id);
                                $doctor_ids['doctor_ids']= $doctor;
                                $doctor_ids['provider_status'] = 0;
                                $this->CommonModel->upsert('patientAppointments',$doctor_ids,$params['appointment_id'],true);
                            }
                            if($appointment_status==1){
                                $appmentStatusSearch['provider_id'] = $provider_id;
                            }
                             if($appointment_status==3)
                            {
                                $appmentStatusSearch['provider_id'] = $provider_id;
                                $appmentStatusSearch['provider_status'] = $appointment_status;

                            }
                            $appmentStatusSearch['patient_status'] = $appointment_status;
                            $response['message'] = ucwords($params['user_type']) . " appointment status updated";
                        }
                         $statusUpdate = $this->CommonModel->upsert('patientAppointments', $appmentStatusSearch, $params['appointment_id'],true);


                        if (empty($statusUpdate)) {
                            $response["status"] = 0;
                            $response["message"] = "Status not updated";
                        }


                } else {
                    $response["status"] = 0;
                    $response["message"] = "You have not any appointment";
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = "Invalid Object id";
            }
        } else {
            $response["status"] = 0;
            $response["message"] = "Mandatory fields are required.";
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }


    /**
     * @param null
     * @function is used to set price accroding to frequency time
     * @return true/false
     */
    public function verify_post()
    {
        $response = [];
        $params = $this->post();
        $url=$this->uri->segment(2);
         /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $type=['Walkin','Audio','Video','Chat','Home'];
        if($url=='otp-verify'){
            $required = ['userid','user_type','appointment_id','patient_id','timezone'];
        }
        $required = ['userid','key_value','key_type','user_type','appointment_id','patient_id','timezone'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && in_array($params['user_type'], array('patient','provider')) && in_array($url, array('send-otp','dob-verify','otp-verify'))) {
            try{
                 $userExist= $this->CommonModel->getCollectionData($params['user_type'].'s',['_id'=> new \MongoId($params['userid'])]);
                    if($userExist){
                        $list=explode(',',HOME_NEXT_SCREEN);
                        $searchData=['_id'=> new \MongoId($params['appointment_id'])];
                            $existData = $this->CommonModel->getCollectionData('patientAppointments',$searchData);
                            if($existData)
                               {
                                    $go_to_screen=$list[0];
                                    $patientData = getPatientData($params['patient_id']);
                                    $response["status"] = 1;
                                    if($url=='send-otp'){
                                        $response['message'] = 'SuccessFully Send Otp';
                                        $otp = 123456;
                                        $result['otp']=$otp;
                                        $response['data']=$result;
                                    }else if($url=='otp-verify') {
                                        
                                        $go_to_screen=$list[1];
                                        $response['data']=["go_to_screen"=>$go_to_screen];
                                        $response['message'] = 'SuccessFully Verifyed';
                                    }else if($url=='dob-verify') {
                                        date_default_timezone_set($params['timezone']);
                                        if($params['key_value']== date('m/y',$existData[0]['dob'])){
                                            $go_to_screen=$list[2];
                                            $response['data']=["go_to_screen"=>$go_to_screen];
                                            $response['message'] = 'SuccessFully Verify Dob';
                                        }else{
                                            $response["status"] = 0;
                                            $response['message'] = 'Please enter valid dob';
                                        }
                                    }else {
                                        $response["status"] = 0;
                                        $response['message'] = 'Invalid Url';
                                    }
                               $this->CommonModel->upsert('patientAppointments',['go_to_screen'=>$go_to_screen],$params['appointment_id'],true);
                                } else {
                                    $response["status"] = 0;
                                    $response['message'] = 'Invalid Appointment';
                                }
                    }else{

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User';
                    }

                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or price id';
                }
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            if($validation)
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }


    /**
     * @param null
     * @function is used to set suggestion and images by provider 
     * @return true/false
     */
    public function home_process_suggestion_post()
    {
        $response = [];
        $params = $this->post();
        $url=$this->uri->segment(2);
         /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid','appointment_id','suggestion','timezone'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation)) {
            try{
                 $userExist= $this->CommonModel->getCollectionData('providers',['_id'=> new \MongoId($params['userid'])]);
                    if($userExist){
                            $searchData=['_id'=> new \MongoId($params['appointment_id'])];
                            $existData = $this->CommonModel->getCollectionData('patientAppointments',$searchData);
                            if($existData)
                               {
                                    $list=explode(',',HOME_NEXT_SCREEN);
                                    //$patientData = getPatientData($params['patient_id']);
                                    $response["status"] = 1;
                                    $image=$this->imageUpload($_FILES);
                                    if(isset($existData[0]['image']) && count($existData[0]['image'])>0 ){
                                            $updateData['image']=(count($image)>0)?array_merge($existData[0]['image'],$image):[];
                                    }else{
                                        $updateData['image']=(count($image)>0)?$image:[];
                                    }
                                    $updateData['go_to_screen']=$list[3];
                                    $updateData['suggestion']=$params['suggestion'];
                                    $this->CommonModel->upsert('patientAppointments',$updateData,$params['appointment_id'],true);
                                    $sendData['provider_price']=$existData[0]['doctors'][$params['userid']]['provider_price'];
                                    $sendData['provider_currency']=$existData[0]['doctors'][$params['userid']]['provider_currency'];
                                    $sendData['go_to_screen']=$list[3];
                                    $response['data']=$sendData;

                                } else {
                                    $response["status"] = 0;
                                    $response['message'] = 'Invalid Appointment';
                                }
                    }else{

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or price id';
                }
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            if($validation)
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }

    /**
     * @param null
     * @function is used to add extera price added by provider 
     * @return true/false
     */
    public function home_process_charges_post()
    {
        $response = [];
        $params = $this->post();
        $url=$this->uri->segment(2);
         /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid','appointment_id','timezone'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation)) {
            try{
                 $userExist= $this->CommonModel->getCollectionData('providers',['_id'=> new \MongoId($params['userid'])]);
                    if($userExist){
                            $searchData=['_id'=> new \MongoId($params['appointment_id'])];
                            $existData = $this->CommonModel->getCollectionData('patientAppointments',$searchData);
                            if($existData)
                               {
                                    $list=explode(',',HOME_NEXT_SCREEN);
                                    //$patientData = getPatientData($params['patient_id']);
                                    $response["status"] = 1;
                                    $response['message'] = 'Providers Chrages Updated';
                                    $updateData['go_to_screen']=$list[4];
                                    if(isset($params['charges'])) {
                                        $updateData['charges']=$params['charges'];
                                        $sendData['charges']=$params['charges'];
                                    }                           
                                    $this->CommonModel->upsert('patientAppointments',$updateData,$params['appointment_id'],true);
                                    $sendData['image']=$existData[0]['image'];
                                    $response['data']=$sendData;

                                } else {
                                    $response["status"] = 0;
                                    $response['message'] = 'Invalid Appointment';
                                }
                    }else{

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid Appointment';
                }
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            if($validation)
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }
    public function imageUpload($files)
    {
         /*      * *** Profile Image Update *******      */
         $imageArray=[];
        if (!empty($files) && is_array($files) && isset($_FILES['photo']['name'][0]) && $_FILES['photo']['type'][0]!='') {

            $this->load->library('upload');
            $files['photo']['name'][0];
            
            $img_count= count($files['photo']['name']); 
            //pr($value);
            for($i=0;$i<$img_count;$i++) {
                $image_info=[];
                $config['upload_path'] = './assets/upload/patients/appointments/';
                $config['allowed_types'] = 'jpg|jpeg|png';
                $config['max_size'] = '1000000000000000';
                $config['overwrite'] = FALSE;
                $title = date('YmdHis');
                $rand = rand(100000, 999999);
                $ext = pathinfo($files['photo']['name'][$i], PATHINFO_EXTENSION);
                $fileName = $rand . $title . '.' . $ext;
                $image = $fileName;
                $config['file_name'] = $fileName;
                $_FILES['photo']['name']= $files['photo']['name'][$i];
                $_FILES['photo']['type']= $files['photo']['type'][$i];
                $_FILES['photo']['tmp_name']= $files['photo']['tmp_name'][$i];
                $_FILES['photo']['error']= $files['photo']['error'][$i];
                $_FILES['photo']['size']= $files['photo']['size'][$i]; 
                $this->upload->initialize($config);
               // $this->upload->do_upload(); 
                //$this->upload->data(); 
                $image = $fileName;
                /*** Image resize ****/
                if($this->upload->do_upload('photo')){
                
                $this->upload->data();
                $this->load->library('image_lib');
                $resize['image_library'] = 'gd2';
                $resize['source_image'] = './assets/upload/patients/appointments/' . $image;

                $tnumb = $rand . $title . '_thumb.' . $ext;
                $resize['new_image'] = "./assets/upload/patients/appointments/$tnumb";
                $resize['maintain_ratio'] = TRUE;
                $resize['width'] = 150;
                $resize['height'] = 150;
                $this->image_lib->initialize($resize);
                if($this->image_lib->resize()){
                    $image_info['thumb']=$tnumb;
                }else{
                    $image_info['thumb']="";
                }
                $image_info['img_extension'] = $ext;
                $image_info['name']=$image;
                $image_info['path']='/assets/upload/patients/appointments/';
                $imageArray[] = $image_info;
            }
                
            }
            return $imageArray;

        }else{
            return $imageArray;
        }
                    /* ****** End Profile Imahe Upoload Section *****     */
    }

    // Patient Section APIs

     /**
     * @param array 
     * @function is used to get provider(Doctor) list
     * @return true/false
     */ 
    public function doctor_list_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone','user_type'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
         try{      
                if(isset($params['offset']) && !empty($params['offset'])){
                    $offset=$params['offset'];  
                } else{
                    $offset=0;
                }
               
                $wheres['_id']= new \MongoId($params['appointment_id']);
                $wheres['patient_id']= new \MongoId($params['userid']);
                $wheres['appointment_type']=['$in'=>["Home"]];
                $checkValidAppointment = $this->CommonModel->getCollectionData('patientAppointments',$wheres);
                if($checkValidAppointment){
                   
                        $docIds=(isset($checkValidAppointment[0]['doctor_ids']) && is_array($checkValidAppointment[0]['doctor_ids']))?$checkValidAppointment[0]['doctor_ids']:[];
                        $confirm_doctors=(isset($checkValidAppointment[0]['confirm_doctors_ids']) && is_array($checkValidAppointment[0]['confirm_doctors_ids']))?$checkValidAppointment[0]['confirm_doctors_ids']:[];
                        
                        if($checkValidAppointment[0]['provider_status']==1)
                            $doctorList=$this->doctor_list($docIds,$confirm_doctors,$checkValidAppointment[0]);
                        else
                            $doctorList=$this->doctor_list($docIds,$confirm_doctors,$checkValidAppointment[0]);
                        if($doctorList){

                            $response["status"] = 1;
                            $response["message"] = 'Your neartest doctor available list.';
                            $response['data']['list']=$doctorList;

                        }else{

                            $response["status"] = 0;
                            $response["message"] = 'Doctor not available on this appointment.';
                        }
                     } else {
                            $response["status"] = 0;
                            $response["message"] = 'Invalid Appointment or user.';
                        }
            }catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = 'Invalid User or appointment';
            } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

    public function doctor_list($docIds=array(),$confirm_doctors_ids=array(),$checkValidAppointment=array(),$limit=20,$offset=0){
        $list=[];
        $homeWheres['email_status']=1;
        $homeWheres['_id']=['$nin'=>$docIds];

        $providersNewList=$this->CommonModel->getCollectionData('providers',$homeWheres,[],['created'=>-1],$limit,$offset);
        $homeWheres['_id']=['$in'=>$confirm_doctors_ids];
        $requestedList=$this->CommonModel->getCollectionData('providers',$homeWheres,[],['created'=>-1],$limit,$offset);
        $requestedList=(is_array($requestedList))?$requestedList:[];
        $providersNewList=(is_array($providersNewList))?$providersNewList:[];
        $providersDataList=array_merge($requestedList,$providersNewList);
        if($providersDataList){
            $dataList['patient_id']=$checkValidAppointment['patient_id']->{'$id'};
            $dataList['appointment_id']=$checkValidAppointment['_id']->{'$id'};
             foreach ($providersDataList as $key => $value) {

            if(isset($checkValidAppointment['doctor_ids']) && is_array($checkValidAppointment['doctor_ids'])) {
                                
                    if (in_array($value['_id'], $checkValidAppointment['doctor_ids'])) {
                        $dataList['doctor_status']="Requested";
                    }else{
                        $dataList['doctor_status']="Request";  
                    }
            }else{
                $dataList['doctor_status']="Request";  
            }

            $dataList['provider_id']=$value['_id']->{'$id'};
            $dataList['provider_name']=$value['sufix'].' '.$value['firstname'].' '.$value['lastname'];
            $dataList['provider_image']=(isset($value['image']) && !empty($value['image']))? base_url().'assets/upload/providers/'.$value['image']:"";
            $dataList['provider_status']=isset($checkValidAppointment['provider_status'])?$checkValidAppointment['provider_status']:"";
            $dataList['patient_status']=isset($checkValidAppointment['patient_status'])?$checkValidAppointment['patient_status']:"";
            $searchData=['userid'=> $value['_id']];
            $pWorks = $this->CommonModel->getCollectionData('providerWorks',$searchData,['name','address'],['created'=> -1],1);
            $pEducations = $this->CommonModel->getCollectionData('providerEducations',$searchData,['name','city','to','from'],['created'=> -1],1);
            $dataList['works_list']=($pWorks)?$pWorks:[];
            $dataList['education_list']=($pEducations)?$pEducations:[];
            $dataList['rating']=3.5;
            $list[]=$dataList;
            }       
        }
        return $list;
    }
    /**
     * @param null
     * @function is used to get provider Info after request confirm
     * @return true/false
     */
    public function provider_info_post()
    {
        $response = [];
        $params = $this->post();
        $url=$this->uri->segment(2);
         /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid','user_type','appointment_id','provider_id',"provider_status"];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && in_array($params['user_type'], array('patient','provider')) && in_array($url, array('provider-info'))) {
            try{
                 $provider_id=$params['provider_id'];
                 $userExist= $this->CommonModel->getCollectionData($params['user_type'].'s',['_id'=> new \MongoId($params['userid'])],['firstname']);
                    if($userExist) {
                        $provider_status= (int) $params['provider_status'];
                        $searchData['_id']= new \MongoId($params['appointment_id']);
                           $p_id= new \MongoId($provider_id);
                            if(isset($params['doctor_status']) &&  $params['doctor_status']=="Requested") {
                                
                                $searchData['doctor_ids']=['$in'=>[$p_id]];
                            }else if($provider_status==1){
                                
                                $searchData['confirm_doctors_ids']=['$in'=>[$p_id]];
                            }
                            else{
                                $searchData['provider_id']= $p_id;
                            }
                        $searchData['appointment_type']=['$in'=>["Home"]];
                                $existData = $this->CommonModel->getCollectionData('patientAppointments',$searchData);
                              //  pr($existData);die;
                            if($existData)
                               {
                                    $providerInfo = getProviderData($provider_id);
                                    $providerData['name']=$providerInfo['data']['sufix'].' '.$providerInfo['data']['firstname'].' '.$providerInfo['data']['lastname'];
                                    $searchEdu=['userid'=> new \MongoId($provider_id)];
                                    $pEducations = $this->CommonModel->getCollectionData('providerEducations',$searchEdu,['name','city','to','from'],['created'=>-1],1);
                                    $providerData['image']=$providerInfo['data']['image'];
                                    $providerData['rating']=3.5;
                                    $providerData['total_user']=500;
                                    $providerData['total_experience']= "7 years experience";
                                    $response["status"] = 1;
                                    $response['data']['appointment_type_selected']=["Home"];
                                    $response['data']['provider_info']=$providerData;
                                    if($existData[0]['provider_status']==2){
                                        $existData[0]['provider_id']=$existData[0]['provider_id'];
                                    }else {
                                        $existData[0]['provider_id']=$p_id;
                                    }
                                    if($existData[0]['provider_status']==2 || $existData[0]['provider_status']==1){
                                        $existData[0]['provider_price']=$existData[0]['doctors'][$params['provider_id']]['provider_price'];
                                        $existData[0]['provider_currency']=$existData[0]['doctors'][$params['provider_id']]['provider_currency'];
                                    }
                                    $response['data']['provider_free_services']=isset($existData[0]['doctors'][$params['provider_id']]['free_services_ids'])?$this->Api->services_list($existData[0]['doctors'][$params['provider_id']]['free_services_ids'],'Free'):[];
                                    unset($existData[0]['doctors'],$existData[0]['confirm_doctors_ids'],$existData[0]['doctor_ids']);
                                    $response['data']['appointment_info']=$existData[0];
                                    $response['data']['education_list']=($pEducations)?$pEducations:[];
                                    $response['data']['services']=isset($existData[0]['services_id'])?$this->Api->services_list($existData[0]['services_id']):[];
                                    
                                } else {
                                    $response["status"] = 0;
                                    $response['message'] = 'Invalid Appointment';
                                }
                    }else{

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User';
                    }
                } catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or price id';
                }
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            if($validation)
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }


    /**
     * @param $appointments and $viwuser are array
     * @function is used to set a array of appointments
     * @return Array
     */
    public function appointment_array($appointments, $viewuser, $lat ='', $long='', $mile ='', $sort_value=''){

        $result     =   [];

        foreach ($appointments as $key => $value) {

           
            $data       =   [];
            $miles      =   0;
            if(isset($value['mobile']) && !empty($value['mobile'])) {
                $value['country_code']= $value['country_code'];
                $value['mobile']= $value['mobile'];
            } else {
                $value['mobile']= ($viewuser[0]['mobile_status']==1)?$viewuser[0]['mobile']:0;
                $value['country_code']= $viewuser['country_code'];
            }
            if($lat !='' &&  $long !=''){

                $miles  =  0; //$this->Api->distance($lat,$long,$value['latitude'], $value['longitude'], 'M');
                if($miles <= $mile){
                    $data  =    $this->single_appointment($value, $miles,$viewuser);
                }
            }else{

                $miles  =  0; //$this->Api->distance($viewuser[0]['latitude'],$viewuser[0]['longitude'],$value['latitude'], $value['longitude'], 'M');
                $data  =    $this->single_appointment($value, $miles ,$viewuser);
            }

            if(!empty($data)) {
                $result[] = $data;
            }
        }
        if($sort_value != '') {
            $milesort = array();
            $sorting = $sort_value == 'far' ? SORT_DESC : SORT_ASC;

            foreach ($result as $key => $row) {
                $milesort[$key] = $row['distance'];
            }
            array_multisort($milesort, $sorting, $result);
        }
        return $result;
    }

    /**
     * @param $appointments and $viwuser are array
     * @function is used to set a array of appointments
     * @return Array
     */
    public function single_appointment($appoint, $mile, $viewuser){

        $value  =   [];
        $provider_id=$viewuser[0]['_id']->{'$id'};
        $value['appointment_id']=$appoint['_id']->{'$id'};
        $value['firstname']=$appoint['firstname'];
        $value['lastname']=$appoint['lastname'];
        $value['country_code']=$appoint['country_code'];
        $value['mobile']=$appoint['mobile'];
        $value['appointment_date']=$appoint['appointment_date'];
        $value['notification_count']=5;
        $value['latitude']=$appoint['latitude'];
        $value['longitude']=$appoint['longitude'];
        $value['distance']= round($mile, 2). " Miles";
        $value['provider_status']=isset($appoint['provider_status'])?$appoint['provider_status']:0;
        $value['patient_status']=isset($appoint['patient_status'])?$appoint['patient_status']:0;
        $value['notes']=isset($appoint['notes'])?$appoint['notes']:'Dummy Data';
        $value['gender']=isset($appoint['gender'])?$appoint['gender']:0;
        $value['patient_id']=isset($appoint['patient_id'])?$appoint['patient_id']:'';
        $value['dob']=isset($appoint['dob'])?$appoint['dob']:'';
        $value['provider_price']=isset($appoint['doctors'][$provider_id]['provider_price'])?$appoint['doctors'][$provider_id]['provider_price']:0;
        $value['provider_currency']=isset($appoint['doctors'][$provider_id]['provider_currency'])?$appoint['doctors'][$provider_id]['provider_currency']:'';
        $value['services']=isset($appoint['services_id'])?$this->Api->services_list($appoint['services_id']):[];
        $value['free_services']=isset($appoint['doctors'][$provider_id]['free_services_ids'])?$this->Api->services_list($appoint['doctors'][$provider_id]['free_services_ids'],'Free'):[];
        unset($appoint['services_id']);
        if(isset($appoint['complete_address']) )
            $value['complete_address']=$appoint['complete_address'];
        else{
            $address=isset($appoint['address1'])?$appoint['address1']:'';
            $address2=isset($appoint['address2'])?$appoint['address2']:'';
            $address3=isset($appoint['city'])?$appoint['city']:'';
            $address4=isset($appoint['state'])?$appoint['state']:'';
            $address5=isset($appoint['pincode'])?$appoint['pincode']:'';
            $value['complete_address']=$address .' '.$address2 .' '.$address3 .' '. $address4 .' '. $address5;
        }
        if($value['provider_price']>0){
            $value['provider_status']=($value['provider_status']==0)?1:$value['provider_status'];
        }
        return $value;
    }
}
