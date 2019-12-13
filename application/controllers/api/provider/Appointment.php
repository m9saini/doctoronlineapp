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
class Appointment extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='patientAppointments';
        $this->load->model('ApiModel', 'Api');
    }

    /**
     * @param array 
     * @function is used to insert and update Work 
     * @return true/false
     */
    public function upsert_put()
    {
        $response = $Objectids =[];
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2)); 
        $type=explode(',',APPOINTMENT_TYPE_LIST);
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_type','firstname','lastname','appointment_date','appointment_for','speciality_id','latitude','longitude','timezone'];
        $otherRules=['appointment_type'=>''];

        if($params['appointment_for']=='Other'){
        	array_push($required, 'dob','gender');
        }

        if(isset($params['appointment_type'][0]) && $params['appointment_type'][0] =='Home'){
        	array_push($required,'mobile','country_code','city','state','pincode','services_id','address2');
        	$otherRules=array_merge($otherRules,['services_id'=>'']);
        	
        }
         if(isset($params['appointment_type'][0]) && $params['appointment_type'][0] =='Walkin'){
        	array_push($required,'complete_address');
        }
         $validation = $this->CommonModel->validation($params,$required,$otherRules); 
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('add','edit')) && is_array($params["appointment_type"]) )
          { 	
          	try{
		             $typeValue= array_intersect ($type,$params['appointment_type']);
                    if(empty($typeValue)) {
                        $response["status"] = 0;
                        $response["message"] = 'Please insert valid schedule type';
                        $this->response($response);
                    }
                    if(isset($params['services_id'])){

		            	if(is_array($params["services_id"])){
			            	foreach ($params["services_id"] as $key => $value) {
			            		
			            		$Objectids[]= new \MongoId($value);
			            	}
			            }else{
							$response['status']=0;
			            	$response['message']='Services Invalid Array.';
			            	$this->response($response);
			            }
                    $params['services_id']=$Objectids;
		            }
		            
		            $patientExists = getPatientData($params['userid']);
		            if($patientExists['status']){
		            	$params['speciality_id']= new \MongoId($params['speciality_id']);
		            	$params['patient_id']= new \MongoId($params['userid']);
		                $id=(isset($params['appointment_id']))?$params['appointment_id']:0;
		                $update=($url[1]=='edit')?true:false;

		                if(($url[1]=='edit') && $params['appointment_type'][0]!='Wakin' && isset($_FILES['photo'])) {
		                	$params['image']=$this->imageUpload($_FILES);
		                }
		                foreach ($required as $key => $value) {
		                	$upsertData[$value]=$params[$value];
		                }
		                unset($upsertData['appointment_id'],$upsertData['userid']);
                        $upsertData['patient_id']=$params['patient_id'];
		                $upsertData['image']=[];
		                if(isset($params["timezone"])){
		                	date_default_timezone_set($params['timezone']);
		                	$upsertData['appointment_date']=strtotime(date('Y-m-d',$params['appointment_date']));
		                	if(isset($params["dob"]) && !empty($params['dob']))
		                	$upsertData['dob']=strtotime(date('Y-m-d',$params['dob']));
		            	}
		            	
		            	$upsertData['appointment_date']=utc_date(date('Y-m-d',$upsertData['appointment_date']),$params['timezone'],true);
		                	if(isset($params["dob"]) && !empty($upsertData['dob']))
		                	$upsertData['dob']=utc_date(date('Y-m-d',$upsertData['dob']),$params['timezone'],true);
		                if($url[1]=='add'){
                            $upsertData['patient_status']=0;
                            $upsertData['provider_status']=0;
                            $upsertData['appointment_time']=0;
                            $upsertData['provider_id']='';
                            $upsertData['provider_price']=0;
                        }
                        if(isset($params['notes']) && !empty($params['notes'])) {
                            $upsertData['notes']=$params['notes'];
                        }
		                $dataResult = $this->CommonModel->upsert($this->collection,$upsertData,$id,$update);
		                    if ($dataResult) {

		                        $response["status"] = 1;
		                        $response['message'] = 'ScucessFully Data '.(($update)?'updated':'added');
		                        $scheduleWheres['date']=$params['appointment_date'];
		                        // search appointment type                        
		                         if(isset($params['appointment_type'])){
		                            $scheduleWheres['appointment_type']=['$in'=>$params['appointment_type']];
		                        }
		                        if(isset($params['appointment_type'][0]) && $params['appointment_type'][0] !='Home'){
		                            
		                            //$wheres['']
		                        }
		                        $response['data']['appointment_id'] = $dataResult;
		                        
		                    } else {
		                        $response["status"] = 0;
		                        $response["message"]='Data not '.(($update)?'updated':'added');
		                    }
		                
		            }else{
		                $response["status"] = 0;
		                $response["message"]='Patient id does not exists.';
		            }
		         }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or Services';
                } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response['message']='Appointment type Invalid.';
        }
        $this->response($response);
    }

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
        $required=['userid','appointment_type','appointment_id','timezone'];
        $otherRules=['appointment_type'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRules); 
        if(empty($validation) && is_array($params['appointment_type'])) {
             try{      
                    if(isset($params['offset']) && !empty($params['offset'])){
                        $offset=$params['offset'];  
                    } else{
                        $offset=0;
                    }
                    $selectd=['services_id','complete_address','latitude','longitude','appointment_date']; 
                    $wheres['patient_id']= new \MongoId($params['userid']);
                    $wheres['_id']= new \MongoId($params['appointment_id']);
                    $checkValidAppointment = $this->CommonModel->getCollectionData($this->collection,$wheres,$selectd);
                    if($checkValidAppointment){
                        $appointment_date= (int)$checkValidAppointment[0]['appointment_date'];
	                    $patient_login_id= new \MongoId($params['userid']);
	                    $query=['date'=>$appointment_date ,'type'=>['$in'=>$params['appointment_type']]];
                        $dataScheduleList = $this->CommonModel->getCollectionData('providerSchedules',$query);
		                    if ($dataScheduleList) {

		                        $response["status"] = 1;
		                        $response['message'] = 'success';
                                $i=0;
		                        foreach ($dataScheduleList as $key => $schedule_value) {
		                        	$provider_id=$schedule_value["userid"]->{'$id'};
		                        	$providerInfo = getParovidersData($provider_id);
                                    if($providerInfo['status']==1 &&  $providerInfo['data']['deleted']=='' ){
                                        $dataList['patient_id']=$params['userid'];
                                        $dataList['appointment_id']=$params['appointment_id'];
    		                        	$searchData=['userid'=> new \MongoId($provider_id)];
    		                        	$pWorks = $this->CommonModel->getCollectionData('providerWorks',$searchData,['name','address'],['created'=> -1],1);
    		                        	$pEducations = $this->CommonModel->getCollectionData('providerEducations',$searchData,['name','city','to','from'],['created'=> -1],1);
                                        $frequencyData = $this->CommonModel->getCollectionData('frequency',['_id'=>$schedule_value["frequency_id"]],['time_in_mins']);
    		                         	$dataList['provider_name']=$providerInfo['data']['sufix'].' '.$providerInfo['data']['firstname'].' '.$providerInfo['data']['lastname'];
                                        $dataList['provider_image']=$providerInfo['data']['image'];
                                        $dataList['provider_id']=$provider_id;
    		                        	$dataList['schedule_id']=$schedule_value["_id"]->{'$id'};
    		                        	$dataList['works_list']=($pWorks)?$pWorks:[];
    		                        	$dataList['education_list']=($pEducations)?$pEducations:[];
    		                        	$dataList['appointment_type']=$schedule_value["type"];
                                        $dataList['appointment_type_selected']=$params["appointment_type"];
    		                        	$dataList['rating']=3.5;
    		                        	$dataList['total_user']=500;
    		                        	$dataList['call_at']= 'Rs 50'.' per '.$frequencyData[0]["time_in_mins"].' mins' ;
                                        $dataList['total_experience']= "24 years experience";

    		                        	$providersList[$i]=$dataList;
                                        $i++;
                                    }
		                        }
		                        $response['data']=$providersList;
		                    } else {
		                        $response["status"] = 0;
		                        $response["message"] = 'Doctor not available.';
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


    /**
     * @param array 
     * @function is used to get Work list
     * @return true/false
     */ 
    public function list_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) 
        { 
            try{
                    $selectd=['appointment_type','firstname','lastname','appointment_date','appointment_for','services_id','complete_address','latitude','longitude','provider_status','patient_status','address1','address2','city','state','pincode','provider_id','frequency_id','patient_id']; 
                    if(isset($params['appointment_type']) && is_array($params['appointment_type']))
                        $wheres['appointment_type']=['$in'=>$params['appointment_type']];
                    if(isset($params['user_type']) && $params['user_type']=='provider')
                        $wheres['provider_id']= new \MongoId($params['userid']);
                     else
                        $wheres['patient_id']= new \MongoId($params['userid']);
                    $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres,$selectd);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        foreach ($dataList as $key => $value) { 
                            $value['start_btn_active_before_min']= APPOINTMENT_START_BUTTON_BEFORE_ACTIVE_MIN;
                            $value['notification_count']=5;
                            $value['distance']='5.22 Miles';
                            $value['provider_status']=isset($value['provider_status'])?$value['provider_status']:0;
                            $value['patient_id']=(isset($value['patient_id']) && !empty($value['patient_id']))?$value['patient_id']->{'$id'}:"";
                            $value['patient_status']=isset($value['patient_status'])?$value['patient_status']:0;
                            $value['gender']=isset($value['gender'])?$value['gender']:0;
                            $value['services']=isset($value['services_id'])?$this->Api->services_list($value['services_id']):[];
                            $value['appointment_time']=isset($value['appointment_time'])?$value['appointment_time']:0;
                            if(isset($value['speciality_id'])){
                                $speciality=$this->CommonModel->getCollectionData('speciality',['_id'=>$value['speciality_id']],['name']);
                                $value['speciality']=($speciality)?$speciality[0]['name']:'';
                            }
                            else{
                                $value['speciality']='';
                            }
                            if(isset($value['frequency_id'])){
                                $frequency=$this->CommonModel->getCollectionData('frequency',['_id'=>$value['frequency_id']],['time_in_mins']);
                                $value['frequency']=($frequency)?$frequency[0]['time_in_mins']:0;
                            }
                            else{
                                $value['frequency']=0;
                            }
                            unset($value['services_id']);
                            if(isset($value['complete_address']) )
                                $value['complete_address']=$value['complete_address'];
                            else{
                                $address=isset($value['address1'])?$value['address1']:'';
                                $address2=isset($value['address2'])?$value['address2']:'';
                                $address3=isset($value['city'])?$value['city']:'';
                                $address4=isset($value['state'])?$value['state']:'';
                                $address5=isset($value['pincode'])?$value['pincode']:'';
                                $value['complete_address']= trim($address .' '.$address2 .' '.$address3 .' '. $address4 .' '. $address5);
                            }

                            if(isset($value['provider_status']) &&  $value['provider_status']>0 && isset($value['provider_id']) ){
                            
                                if((string)$value['provider_id']){
                                    $docName=$this->CommonModel->getCollectionData('providers',['_id'=>$value['provider_id']],['firstname','lastname','sufix']);
                                     $value['doctor_name']= ($docName)?$docName[0]['sufix'].' '.$docName[0]['firstname'].' '.$docName[0]['lastname']:'';
                                    $value['provider_id'] = $value['provider_id']->{'$id'};
                                } else {
                                    $value['doctor_name']='';
                                    $value['provider_id'] ='';
                                }
                            }else{
                                $value['doctor_name']='';
                                $value['provider_id'] ='';
                            }
                           
                            $result[]=$value;
                        }
                          $response['data']['appointment_status_list'] = $this->Api->appointment_status_list('patient');
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
     * @function is used to get Work details
     * @return true/false
     */
    public function view_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
             try{       
                    $wheres['patient_id']= new \MongoId($params['userid']);
                    $wheres['_id']= new \MongoId($params['appointment_id']);
                    $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $dataList[0]['image']=isset($dataList[0]['image'])?$dataList[0]['image']:[];
                        $patient_info= getPatientData($params['userid']);
                        $patient_info= $patient_info['data'];
                        $dataList[0]['start_btn_active_before_min']= APPOINTMENT_START_BUTTON_BEFORE_ACTIVE_MIN;
                        $dataList[0]['mobile']=isset($dataList[0]['mobile'])?$dataList[0]['mobile']:$patient_info['mobile'];
                        $dataList[0]['dob']=isset($dataList[0]['dob'])?$dataList[0]['dob']:$patient_info['dob'];
                        $dataList[0]['gender']=isset($dataList[0]['gender'])?$dataList[0]['gender']:$patient_info['gender'];
                        $dataList[0]['services_id']=isset($dataList[0]['services_id'])?$this->Api->services_list($dataList[0]['services_id']):[];
                        $dataList[0]['provider_id']=((isset($dataList[0]['provider_id']) && !empty($dataList[0]['provider_id']))?$dataList[0]['provider_id']->{'$id'}:"");

                        if(isset($dataList[0]['speciality_id'])){
                                $speciality=$this->CommonModel->getCollectionData('speciality',['_id'=>$dataList[0]['speciality_id']],['name']);
                                $dataList[0]['speciality']=($speciality)?$speciality[0]['name']:'';
                            }
                            else{
                                $value['speciality']='';
                            }
                            if(isset($dataList[0]['frequency_id'])){
                                $frequency=$this->CommonModel->getCollectionData('frequency',['_id'=>$dataList[0]['frequency_id']],['time_in_mins']);

                                $dataList[0]['frequency']=($frequency)?$frequency[0]['time_in_mins']:0;
                            }
                            else{
                                $dataList[0]['frequency']=0;
                            }
                        $response['data'] = $dataList;
                     } else {
                        $response["status"] = 0;
                        $response["message"] = 'Appointment id or userid not found.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }
    
    
     /**
     * @param null
     * @function is used to get Work details
     * @return true/false
     */
    public function patient_view_with_appointment_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
             try{       
                    $wheres['patient_id']= new \MongoId($params['userid']);
                    $wheres['_id']= new \MongoId($params['appointment_id']);
                    $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $dataList;
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Appointment id or userid not found.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

    /**
     * @param array or object id
     * @function is used to soft delete
     * @return true/false
     */
    public function delete_get()
    {
        $response = [];
        $params = $this->get();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
            try{
                    $wheres['patient_id']= new \MongoId($params['userid']);
                    $wheres['_id']= new \MongoId($params['appointment_id']);
                    $dataList =  $this->CommonModel->delete($this->collection,$wheres,$params['timezone']);
                     if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                    } else {
                        $response["status"] = 0;
                        $response["message"]='Appointment not deleted.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                } 
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
        }
        $this->response($response);
    }

    public function imageUpload($files)
    {
         /*      * *** Profile Image Update *******      */
        $imageArray=[];
        if (!empty($files) && is_array($files) && isset($_FILES['photo']['name'][0]) && $_FILES['photo']['error'][0]!='') {

            $this->load->library('upload');
            $img_count= count($files['photo']['name']); 
            //pr($value);
            for($i=0;$i<$img_count;$i++) {
                $config['upload_path'] = './assets/upload/patients/appointments/';
                $config['allowed_types'] = 'jpg|jpeg|png';
                $config['max_size'] = '1000000000000000';
                $config['overwrite'] = TRUE;
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
                    //$resize['maintain_ratio'] = TRUE;
                    $resize['width'] = 150;
                    $resize['height'] = 150;
                    $this->image_lib->initialize($resize);
                    if($this->image_lib->resize()){
                        $image_info['thumb']=$tnumb;
                    }
                    else{
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
}
