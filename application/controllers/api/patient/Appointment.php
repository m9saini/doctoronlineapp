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
        $required=['userid','appointment_type','firstname','lastname','appointment_date','appointment_for','speciality_id','timezone'];
        $otherRules=['appointment_type'=>''];

        if(isset($params['appointment_for']) && $params['appointment_for']=='Other'){
            array_push($required, 'dob','gender');
        }

        if(isset($params['appointment_type'][0]) && $params['appointment_type'][0] =='Home'){
            array_push($required,'mobile','country_code','city','state','pincode','services_id','address2','latitude','longitude');
            $otherRules=array_merge($otherRules,['services_id'=>'']);
            
        }
         if(isset($params['appointment_type'][0]) && $params['appointment_type'][0] =='Walkin'){
            array_push($required,'complete_address','latitude','longitude');
        }
         $validation = $this->CommonModel->validation($params,$required,$otherRules); 
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('add')) && is_array($params["appointment_type"]) )
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
                        $upsertData['location']=['type'=>"Point",'coordinates'=>[$params['longitude'],$params['latitude']]];
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
     * @function is used to insert and update Work 
     * @return true/false
     */
    public function edit_post()
    {
        $response = $Objectids =[];
        $params = $this->post();
        $url=explode('-',$this->uri->segment(2)); 
        $type=explode(',',APPOINTMENT_TYPE_LIST);
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','appointment_type','firstname','lastname','appointment_date','appointment_for','speciality_id','timezone'];
        $otherRules=['appointment_type'=>''];

        if(isset($params['appointment_type'])) {
            if(is_string($params['appointment_type']))
                $params['appointment_type']=[$params['appointment_type']];             
        }
        if(isset($params['appointment_for']) && $params['appointment_for']=='Other'){
            array_push($required, 'dob','gender');
        }

        if(isset($params['appointment_type'][0]) && $params['appointment_type'][0] =='Home'){
            array_push($required,'mobile','country_code','city','state','pincode','services_id','address2','latitude','longitude');
            $otherRules=array_merge($otherRules,['services_id'=>'']);
            
        }
         if(isset($params['appointment_type'][0]) && $params['appointment_type'][0] =='Walkin'){
            array_push($required,'complete_address','latitude','longitude');
        }
         $validation = $this->CommonModel->validation($params,$required,$otherRules); 
        if(empty($validation) && is_array($params["appointment_type"]) )
          {     
            try{
                    
                    $exists=$this->CommonModel->getCollectionData($this->collection,['_id'=> new \MongoId($params['appointment_id'])],['_id','image']);
                    if(empty($exists)){
                        $response["status"] = 0;
                        $response["message"] = 'Invalid appointment';
                        $this->response($response);
                    }
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
                        $id=$params['appointment_id'];
                        $images=[];
                        if($params['appointment_type'][0]!='Wakin' && isset($_FILES['photo'])) {
                            $images=$this->imageUpload($_FILES);
                        }
                        foreach ($required as $key => $value) {
                            $upsertData[$value]=$params[$value];
                        }
                        unset($upsertData['appointment_id'],$upsertData['userid']);
                        $upsertData['patient_id']=$params['patient_id'];
                        if(isset($exists[0]['image']) && count($exists[0]['image'])>0){
                            if(count($images)>0)
                                $upsertData['image']=array_merge($exists[0]['image'],$images);
                        }else{

                            if(count($images)>0)
                                $upsertData['image']=$images;
                        }
                       if(isset($params["timezone"])){
                            date_default_timezone_set($params['timezone']);
                            $upsertData['appointment_date']=strtotime(date('Y-m-d',$params['appointment_date']));
                            if(isset($params["dob"]) && !empty($params['dob']))
                            $upsertData['dob']=strtotime(date('Y-m-d',$params['dob']));
                        }
                        
                        $upsertData['appointment_date']=utc_date(date('Y-m-d',$upsertData['appointment_date']),$params['timezone'],true);
                            if(isset($params["dob"]) && !empty($upsertData['dob']))
                            $upsertData['dob']=utc_date(date('Y-m-d',$upsertData['dob']),$params['timezone'],true);
                       
                        if(isset($params['notes']) && !empty($params['notes'])) {
                            $upsertData['notes']=$params['notes'];
                        }
                        $upsertData['location']=['type'=>"Point",'coordinates'=>[$params['longitude'],$params['latitude']]];
                        $dataResult = $this->CommonModel->upsert($this->collection,$upsertData,$id,true);
                            if ($dataResult) {

                                $response["status"] = 1;
                                $response['message'] = 'ScucessFully appointment updated';
                                $response['data']['appointment_id'] = $dataResult;
                                
                            } else {
                                $response["status"] = 0;
                                $response["message"]='Appointment not updated';
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
                    $confirm_doctors_ids=[];
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
                        // price filter used for doctor ids from this appointment doctors object 
                        $price_search['type']=$params['appointment_type'][0];
                        if(isset($params['price_min']) && isset($params['price_max']))
                        {
                            
                            $price_search['price_value']=['$gte'=>(int)$params['price_min'],'$lte'=>(int)$params['price_max']];
                            $price_doctors=$this->CommonModel->getCollectionData('providerPrices',$price_search,['userid'=>1]);
                            if(count($price_doctors)>0){
                                foreach($price_doctors as $dc_id_key=>$c_d_ids) { 
                                        $confirm_doctors_ids[]= $c_d_ids['userid'];
                                }
                            }
                            $query['userid']=['$in'=>$confirm_doctors_ids];
                        }
                        $dataScheduleList = $this->CommonModel->getCollectionData('providerSchedules',$query);
                            if ($dataScheduleList) {
                                $providersList=[];
                                $i=0;
                                foreach ($dataScheduleList as $key => $schedule_value) {
                                    $provider_id=$schedule_value["userid"]->{'$id'};
                                    $frequency_id=$schedule_value["frequency_id"][$params['appointment_type'][0]];
                                    $provider_search['_id']=$schedule_value["userid"];
                                    if(isset($params['gender']))
                                    {
                                        if($params['gender']=='Male')
                                            $provider_search['gender']=$params['gender'];
                                        else if($params['gender']=='Female')
                                            $provider_search['gender']=$params['gender'];
                                    }
                                    if(isset($params['language']) && is_array($params['language']))
                                    {
                                        $provider_search['language']=['$in'=>$params['language']];
                                    }
                                    $provider_price_search=['userid'=>$schedule_value["userid"],'frequency_id'=>$frequency_id];
                                    $providerInfo = $this->CommonModel->getCollectionData('providers',$provider_search);
                                $provider_price_exists = $this->CommonModel->getCollectionData('providerPrices',$provider_price_search);
                                    $response["status"] = 1;
                                    $response['message'] = 'success';
                                    if(!empty($providerInfo) && !empty($provider_price_exists)){
                                        $dataList['patient_id']=$params['userid'];
                                        $dataList['appointment_id']=$params['appointment_id'];
                                        $searchData=['userid'=> new \MongoId($provider_id)];
                                        $pWorks = $this->CommonModel->getCollectionData('providerWorks',$searchData,['name','address','total_works','title'],['created'=> -1],1);
                                        $pEducations = $this->CommonModel->getCollectionData('providerEducations',$searchData,['name','city','to','from','degree'],['created'=> -1],1);
                                        $frequencyData = $this->CommonModel->getCollectionData('frequency',['_id'=>$frequency_id],['time_in_mins']);
                                        $dataList['provider_name']=$providerInfo[0]['sufix'].' '.$providerInfo[0]['firstname'].' '.$providerInfo[0]['lastname'];
                                        $dataList['provider_image']=(isset($providerInfo[0]['image']) && !empty($providerInfo[0]['image']))?base_url('assets/upload/providers/').$providerInfo[0]['image']:'';
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
                                        $dataList['about']= (isset($providerInfo[0]['about']))?$providerInfo[0]['about']:'';
                                        $providersList[$i]=$dataList;
                                        $i++;
                                    }
                                }
                                if(count($providersList)>0)
                                    $response['data']=$providersList;
                                else{
                                    $response["status"] = 0;
                                    $response["message"] = 'Doctor not available.';
                                }
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
                    $sorting_by=['created' => -1];
                    if(isset($params['appointment_type']) && is_array($params['appointment_type']))
                        $wheres['appointment_type']=['$in'=>$params['appointment_type']];
                    if(isset($params['user_type']) && $params['user_type']=='provider')
                        $wheres['provider_id']= new \MongoId($params['userid']);
                     else
                        $wheres['patient_id']= new \MongoId($params['userid']);
                    
                    date_default_timezone_set($params['timezone']);
                    if(isset($params['startdate']) || ((isset($params['sort_type']) && !empty($params['sort_type']))))
                    {
                        if(isset($params['sort_type']) && !empty($params['sort_type'])) {
                            if($params['sort_type']=='Recent'){
                                $params['startdate']=date('Y-m-d h:i:s');
                                $sorting_by= ['appointment_date' => 1];
                            }else{
                                $params['startdate']=date('Y-m-d h:i:s');
                                $params['enddate']=date('Y-m-t h:i:s');
                                $sorting_by= ['appointment_date' => 1];
                            }
                        }
                        $appointment_time=0;
                        if(isset($params['starttime'])){
                            $appointment_time=1;
                            $startdate=$params['startdate'].' '.$params['starttime'];
                            $startdate=strtotime($startdate);
                            date_default_timezone_set('UTC');
                            $startdate=strtotime(date('Y-m-d H:i:s',$startdate));
                        }else{
                            $startdate=utc_date($params['startdate'],$params['timezone'],true);
                        }
                        if(isset($params['enddate'])){
                            if(isset($params['endtime'])){
                                $enddate=$params['enddate'].' '.$params['endtime'];
                                $enddate=strtotime($enddate);
                                date_default_timezone_set('UTC');
                                $startdate=strtotime(date('Y-m-d H:i:s',$enddate));
                            }else{
                                $enddate=utc_date($params['enddate'],$params['timezone'],true);
                            }
                        }else{
                            date_default_timezone_set($params['timezone']);
                            
                            if(isset($params['starttime'])){
                                $datestart=strtotime($params['startdate'].' '.$params['starttime']);
                            }

                            if(isset($params['endtime'])){
                                $datestart=strtotime($params['startdate'].' '.$params['endtime']);
                            }else{
                                $datestart=$startdate;
                            }
                            $enddate=strtotime(date('Y-m-d H:i:s',$datestart).'+1 Days');
                            $enddate=date('Y-m-d',$enddate);
                            $enddate=utc_date($enddate,$params['timezone'],true);
                        }
                        if($appointment_time)
                            $wheres['appointment_time']= ['$gte'=>$startdate,'$lt'=>$enddate];
                        else
                            $wheres['appointment_date']= ['$gte'=>$startdate,'$lt'=>$enddate];
                    }
                    if(isset($params['appointment_status']) && is_array($params['appointment_status']) && count($params['appointment_status'])>0)
                    {
                        $user_type=(isset($params['user_type']) && $params['user_type']=='provider')?'provider':'patient';
                        $cancel_by_you=(isset($params['user_type']) && $params['user_type']=='provider')?'patient':'provider';
                        for($i=0;$i<count($params['appointment_status']);$i++){

                            if($params['appointment_status'][$i]==0 || $params['appointment_status'][$i]==1 || $params['appointment_status'][$i]==2 ){
                                $wheres[$cancel_by_you.'_status']=['$ne'=>3];
                            } 
                            if($params['appointment_status'][$i]==33){
                                $or_data[$i]=[$cancel_by_you."_status"=>3];
                            }else{
                                $or_data[$i]=[$user_type."_status"=>$params['appointment_status'][$i]];
                            }
                        }
                        $wheres['$or']= $or_data;
                    }
                    $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres,[],$sorting_by);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';

                        foreach ($dataList as $key => $value) { 
                            $doc_count=0;
                            $value['start_btn_active_before_min']= APPOINTMENT_START_BUTTON_BEFORE_ACTIVE_MIN;
                            if($value['patient_status']==0){
                                $providerSearch=['date'=>$value['appointment_date'],'type'=>['$in'=>$value['appointment_type']],'speciality_ids'=>['$in'=>[$value['speciality_id']]]];
                                $doc_search=$this->CommonModel->getCollectionData('providerSchedules',$providerSearch,['_id']);
                                $doc_count=($doc_search)?count($doc_search):0;
                            }
                            $patient_info=getPatientData((string)$value['patient_id']);
                            if($value['appointment_type']!='Home'){
                                
                                if($patient_info['status']){
                                    $value['country_code']=$patient_info['data']['country_code'];
                                    $value['mobile']=$patient_info['data']['mobile'];
                                }
                            }
                            if($value['appointment_for']=='Self'){             
                                $value['dob']=$patient_info['data']['dob'];
                            }
                            $value['notification_count']=$doc_count;
                            $value['distance']='5.22 Miles';
                            $value['provider_status']=isset($value['provider_status'])?$value['provider_status']:0;
                            $value['patient_id']=(isset($value['patient_id']) && !empty($value['patient_id']))?$value['patient_id']->{'$id'}:"";
                            $value['patient_status']=isset($value['patient_status'])?$value['patient_status']:0;
                            $value['gender']=isset($value['gender'])?$value['gender']:$patient_info['data']['gender'];
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
                                if($value['appointment_for']=='Self'){ 
                                $address=(isset($patient_info['data']['street_add']) && !empty($patient_info['data']['street_add']))?$patient_info['data']['street_add'].' ':'';
                                $address3=(isset($patient_info['data']['city']) && !empty($patient_info['data']['city']))?$patient_info['data']['city'].' ':'';
                                $address4=(isset($patient_info['data']['state']) && !empty($patient_info['data']['state']))?$patient_info['data']['state'].' ':'';
                                $address5=(isset($patient_info['data']['zipcode']) && !empty($patient_info['data']['zipcode']))?$patient_info['data']['zipcode']:'';         
                                $value['complete_address']=trim($address.$address3.$address4.$address5);
                                }
                            }

                            if(isset($value['provider_status']) &&  $value['provider_status']>0 && isset($value['provider_id']) ){
                            
                                if((string)$value['provider_id']){
                                    $docName=getProviderData((string)$value['provider_id']);
                                    if($docName['status']){
                                     $value['doctor_name']= $docName['data']['sufix'].' '.$docName['data']['firstname'].' '.$docName['data']['lastname'];
                                     $value['doctor_country_code']= $docName['data']['country_code'];
                                     $value['doctor_mobile']=$docName['data']['mobile'];
                                    }else{
                                        $value['doctor_name']='';
                                    }

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
                        $response['data']['appointment_status_list'] = $this->Api->appointment_status_list('patient',true);
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
                        date_default_timezone_set($params['timezone']);
                        $current_time= strtotime(date('Y-m-d H:i:s'));
                        date_default_timezone_set("UTC");
                        $current_time= strtotime(date('Y-m-d H:i:s',$current_time));
                        $dataList[0]['server_current_time_in_UTC']=$current_time;
                        $dataList[0]['start_btn_active_before_min']= APPOINTMENT_START_BUTTON_BEFORE_ACTIVE_MIN;
                        $dataList[0]['mobile']=isset($dataList[0]['mobile'])?$dataList[0]['mobile']:$patient_info['mobile'];
                        $dataList[0]['dob']=isset($dataList[0]['dob'])?$dataList[0]['dob']:$patient_info['dob'];
                        $dataList[0]['gender']=isset($dataList[0]['gender'])?$dataList[0]['gender']:$patient_info['gender'];
                        $dataList[0]['patient_image']=$patient_info['image'];
                        $dataList[0]['updated']=$dataList[0]['updated'];
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
                            if($dataList[0]['patient_status']==1 && $dataList[0]['provider_status']==1 && in_array($dataList[0]['appointment_type'][0],['Chat','Walkin','Video','Audio']) && isset($patient_info['quickblox_info'])) {
                                $dataList[0]['patient_quickblox_info']=$patient_info['quickblox_info'];
                                $provider_info=getProviderData((string)$dataList[0]['provider_id'],[],['quickblox_info','firstname','lastname','sufix','image']);             
                                if(isset($provider_info['data']['quickblox_info'])) {
                                    $dataList[0]['doctor_name']=$provider_info['data']['sufix'].' '.$provider_info['data']['firstname'].' '.$provider_info['data']['lastname'];
                                    $dataList[0]['doctor_image']=$provider_info['data']['image'];
                                    $dataList[0]['provider_quickblox_info']= $provider_info['data']['quickblox_info'];
                                }
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
        if (!empty($files) && is_array($files) && isset($_FILES['photo']['name'][0])) {
            $this->load->library('upload');
            $img_count= count($files['photo']['name']); 
            //pr($value);
            for($i=0;$i<$img_count;$i++) {
                 $config= $image_info=[];

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

     /**
     * @param array or object id
     * @function is used to soft delete
     * @return true/false
     */
    public function image_delete_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone','image_name'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
            try{
                    $wheres['patient_id']= new \MongoId($params['userid']);
                    $wheres['_id']= new \MongoId($params['appointment_id']);
                    $dataList =  $this->CommonModel->getCollectionData($this->collection,$wheres,['image']);
                     if ($dataList) {
                        if(isset($dataList[0]['image']) && count($dataList[0]['image'])){

                            $key = array_search($params['image_name'], array_column($dataList[0]['image'], 'name'));
                             if($key !== false){ 
                                unset($dataList[0]['image'][$key]);
                                $updated['image']=array_merge($dataList[0]['image'],[]);
                                $this->CommonModel->upsert($this->collection,$updated,$params['appointment_id'],true);
                                $response["status"] = 1;
                                $response['message'] = 'SuccessFully image deleted';
                            }else{
                                $response["status"] = 0;
                                $response['message'] = 'Image does not exists.';
                            }
                        }else{
                            $response["status"] = 0;
                            $response['message'] = 'Image does not exists.';
                        }
                    } else {
                        $response["status"] = 0;
                        $response["message"]='Invalid Appointment';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                } 
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response['error_data']= $validation;
        }
        $this->response($response);
    }
}