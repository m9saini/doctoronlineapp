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
class CommonApi extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        
    }

    /**
     * @param null
     * @function is used to get countries list
     * @return true/false
     */
    public function countries_get()
    {
        $response = [];
        $params = $this->get();

        try {
            if (isset($params['country_id']) && !empty($params['country_id'])) {
                $wheres['_id'] = new MongoId($params['country_id']);
            }
            //$wheres["status"]="1";
            $countriesList = true; //$this->CommonModel->getCollectionData('countries');
            if ($countriesList) {
                $response["status"] = 1;
                $response['message'] = 'success';
                $response['data']['countries_list'][0] = ["dialcode" => "93", "iso2" => "AF", "iso3" => "AFG", "currencycode" => "AFA"];
                $response['data']['countries_list'][0] = ["dialcode" => "91", "iso2" => "IN", "iso3" => "IND", "currencycode" => "INR"];
                $response['data']['gender_list'][0] = ["key_name" => "Female", "key_value" => "0"];
                $response['data']['gender_list'][1] = ["key_name" => "Male", "key_value" => "1"];
                $response['data']['language_list']= [["name" =>"Hindi"],["name" =>"English"],["name" =>"Punjabi"]];
            } else {
                $response["status"] = 0;
                $response["error_data"] = ['error_message' => 'Countries list empty'];
            }
        } catch (MongoException $ex) {

            $response["status"] = 0;
            $response["error_data"] = ['error_message' => 'Invalid User or country id'];
        }

        $this->response($response);
    }

    
    public function logout_get()
    {
        $response = [];
        $params = $this->get();
        $required = ['userid','user_type'];

            $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {
                if($params['user_type']=='patient')
                    $userExists= getPatientData($params['userid']);
                else
                    $userExists= getProviderData($params['userid']);
                if($userExists['status']){
                    $response["status"] = 1;
                    $response['message'] = 'SuccessFully logout';
                    $update=['device_id'=>'','device_type'=>'','device_token'=>'','login_status'=>0];
                    $this->CommonModel->upsert($params['user_type'].'s',$update,$params['userid'],true);
                }else{
                    $response["status"] = 0;
                    $response['message'] = 'Invalid User';
                }
            } catch (Exception $ex) {

                $response["status"] = 0;
                $response['message'] = 'Invalid Object Id';
            }
        }else{
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }

        $this->response($response);
    }

   
    /**
     * @param null
     * @function is used to get gender list
     * @return true/false
     */
    public function gender_get()
    {
        $response = [];
        $params = $this->get();
        $response["status"] = 1;
        $response['message'] = 'success';
        $response['data'][0] = ["key_name" => "Female", "key_value" => "0"];
        $response['data'][1] = ["key_name" => "Male", "key_value" => "1"];


        $this->response($response);
    }
    /**
     * @param null
     * @function is used to login with social
     * @return true/false
     */
    public function social_signup_put()
    {
        try {
            $response =     $login  =   $data   =   [];
            $wheres     =   [];
            $params = $this->put();
            $first_time = $signup = $checkMobile = false;
            /*         * ******* CEHCK MANDATORY FIELDS ************* */
            $required = ['device_id', 'device_token', 'device_type', 'social_type', 'user_type', 'social_token_id'];

            $validation = $this->CommonModel->validation($params, $required);
            if (empty($validation) && in_array($params['user_type'], ['patients', 'providers'])) {

                $checkSocial = $this->CommonModel->alreadyExists($params['user_type'], array('social_token_id' => $params['social_token_id'], 'social_type' => $params['social_type']),'','deleted');
                
                /** ******* CEHCK EMAIL ************* */
                $ext_name=($params['user_type']=='patients')?QUICKBLOX_PATIENT_EMAIL_EXTENSION : QUICKBLOX_PROVIDER_EMAIL_EXTENSION;
                if (empty($checkSocial) && $params['social_token_id'] != '' && $params['social_type'] != '') {

                    $insertdata  =  $this->signup_data($params);
                    if(isset($params['email']) && !empty($params['email'])){

                        $checkDelated = $this->CommonModel->alreadyExists($params['user_type'],['email'=>$params['email']],'','deleted');
                      
                        if($checkDelated){
                           if($checkDelated==1){
                                $response["status"] = 0;
                                $response['message'] = 'Your account deactivated Please contact to admin';
                                $this->response($response);
                           }else{
                                $checkEmail = $this->CommonModel->alreadyExists($params['user_type'],['email'=>$params['email']],$checkDelated);
                                if($checkEmail){
                                    $login_id     =  $checkEmail;
                                }else{
                                    $emailUserExists=$this->CommonModel->getCollectionData($params['user_type'],['email'=>$params['email']]);
                                    $login_id = (string)$emailUserExists[0]['_id'];
                                    $insertdata  =  $this->signup_data_update($params,$login_id);
                                    $this->CommonModel->upsert($params['user_type'], $insertdata,$login_id,true);
                                }
                            }
                        }else{
                            $insertdata['status']=1;
                            $login_id     =   $this->CommonModel->upsert($params['user_type'], $insertdata);
                            $quickBlox['quickblox_info']=['email'=>$login_id.$ext_name,'password'=>QUICKBLOX_USER_PASSWORD,'quickblox_id'=>''];
                            $this->CommonModel->upsert($params['user_type'],$quickBlox,$login_id,true);
                        }
                    }else{
                        $insertdata['status']=1;
                        $login_id     =   $this->CommonModel->upsert($params['user_type'], $insertdata);
                        $quickBlox['quickblox_info']=['email'=>$login_id.$ext_name,'password'=>QUICKBLOX_USER_PASSWORD,'quickblox_id'=>''];
                        $this->CommonModel->upsert($params['user_type'],$quickBlox,$login_id,true);
                    }

                }else{

                   $login_id     =  $checkSocial;
                   if($checkSocial==1){
                        $response["status"] = 0;
                        $response['message'] = 'Your account deactivated Please contact to admin';
                        $this->response($response);
                   }else{
                            $insertdata  =  $this->signup_data_update($params,$login_id);
                            $insertdata['status'] = 1;
                            $this->CommonModel->upsert($params['user_type'], $insertdata,$login_id,true);
                        }
                      
                }
                if($params['user_type']=='patients')
                    $login  =   getPatientData($login_id);
                else
                    $login  =   getProviderData($login_id);

                $login= $login['data'];
                $login['userid']     =   $login_id;
                //pr($login);die;
                if($login){

                    if($login['status']==0 || !empty($login['deleted'])) {
                        $response["status"] = 0;
                        $response['message'] = 'Your account deactivated Please contact to admin';
                        $this->response($response);
                    }
                    $quickBlox['quickblox_info']=['email'=>$login_id.$ext_name,'password'=>QUICKBLOX_USER_PASSWORD,'quickblox_id'=>''];
                    $response["status"] = 1;
                    $response['message'] = 'SuccessFully Logined';
                    if(isset($login['quickblox_info']))
                        $login['quickblox_info']=$login['quickblox_info'];
                    else
                        $login['quickblox_info']=$quickBlox['quickblox_info'];

                    $response["data"] = $login;
                }else{
                    $response["status"] = 0;
                    $response['message'] = 'Please Try Again.';
                }


            } else {
                $response["status"] = 0;
                $response['message'] = 'Mandatory fields are required.';
                $response["error_data"] = $validation;

            }
        } catch (Exception $ex) {

            $response["status"] = 0;
            $response['message'] = 'Invalid data or Object Id.';
            $response["error_data"] = ['error_message' => $ex->getMessage()];
        }
        $this->response($response);
    }

    // Filter data for signup both patient and provider
    public function signup_data($params)
    {
         
        $this->load->library('encryption');
        $this->load->library('encrypt');
        $key = bin2hex($this->encryption->create_key(16));
        $data = [];
        if ($params['user_type'] == 'patients') {
            
            $encode=['firstname','lastname','dob','city','state','gender'];
            
            $blank=['latitude','longitude','zipcode','social_type', 'social_token_id','device_type','device_token','device_id'];
            $data = ['user_key'=>$key,'country_code'=>'','image'=>''];

            foreach ($encode as $select){
                $data[$select] = isset($params[$select]) ?$this->encrypt->encode($params[$select], $key) : '';           
                }

            foreach ($blank as $select){
                $data[$select] = isset($params[$select])?$params[$select]:'';           
                }
           
        } else if ($params['user_type'] == 'providers') {

             $selected=['firstname','lastname','dob', 'latitude','longitude','gender','city','state', 'zipcode','street_add','social_type', 'social_token_id','device_type','device_token','device_id', 'language','about'];
             $data = [ "sufix" => "Dr.",'country_code'=>'','image'=>''];

            foreach ($selected as $select){
                $data[$select] = isset($params[$select]) ?$params[$select] : '';           
                }
            
        }
        if(isset($params['email']) && !empty($params['email'])) {
            $data['email']=$params['email'];
            $data['email_status']=1;
        } else{
            $data['email']="";
            $data['email_status']=0;
        }
        if(isset($params['mobile']) && !empty($params['mobile'])) {
            $data['mobile']=$params['mobile'];
            $data['mobile_status']=1;
        } else{
            $data['mobile']="";
            $data['mobile_status']=0;
        }

        return $data;
    }

    // Filter data for signup both patient and provider
    public function signup_data_update($params,$id)
    {
         
        $this->load->library('encryption');
        $this->load->library('encrypt');
        $login_data=$this->CommonModel->getCollectionData($params['user_type'],["_id"=> new \MongoId($id)]);
        $data = [];
        if ($params['user_type'] == 'patients') {
            
            $encode=['firstname','lastname','dob','city','state','gender'];
            
            $blank=['social_type', 'social_token_id','device_type','device_token','device_id'];
            
            foreach ($encode as $select){
                if(isset($params[$select]))
                {
                    if(!empty($params[$select])){
                        $data[$select]=$this->encrypt->encode($params[$select], $login_data[0]['user_key']);
                    }           
                }
 
                }

            foreach ($blank as $select){
                   if(isset($params[$select]))
                    {
                        if(!empty($params[$select])){
                            $data[$select]=$params[$select];
                        }           
                    }           
                }
           
        } else if ($params['user_type'] == 'providers') {

             $selected=['firstname','lastname','dob','city','state','social_type', 'social_token_id','device_type','device_token','device_id'];

            foreach ($selected as $select){
                if(isset($params[$select]))
                {
                    if(!empty($params[$select])){
                        $data[$select]=$params[$select];
                    }           
                }
            }
            
        }
        if(isset($params['email']) && !empty($params['email'])) {
            $data['email']=$params['email'];
            $data['email_status']=1;
        } else{
            $data['email']="";
            $data['email_status']=0;
        }
        if(isset($params['mobile']) && !empty($params['mobile'])) {
            $data['mobile']=$params['mobile'];
            $data['mobile_status']=1;
        } else{
            $data['mobile']="";
            $data['mobile_status']=0;
        }

        return $data;
    }

    public function device_token_update_put()
    {
        try {
            $response = [];
            $params = $this->put();
            /*         * ******* CEHCK MANDATORY FIELDS ************* */
            $required = ['device_id', 'device_token', 'device_type','user_type','userid'];
            $validation = $this->CommonModel->validation($params, $required);
            if (empty($validation) && in_array($params['user_type'], ['patient', 'provider'])) {
                    
                $login=$this->CommonModel->getCollectionData($params['user_type'].'s',['_id'=> new \MongoId($params['userid'])]);
                if($login){
                    $update=['device_id'=>$params['device_id'],'device_type'=>$params['device_type'],'device_token'=>$params['device_token']];
                    $this->CommonModel->upsert($params['user_type'].'s',$update,$params['userid'],true);
                    $response["status"] = 1;
                    $response['message'] = 'SuccessFully Updated';
                   
                }else{
                    $response["status"] = 0;
                    $response['message'] = 'Please Try Again.';
                }


            } else {
                $response["status"] = 0;
                $response['message'] = 'Mandatory fields are required.';
                $response["error_data"] = $validation;

            }
        } catch (Exception $ex) {

            $response["status"] = 0;
            $response['message'] = 'Invalid data or Object Id.';
            $response["error_data"] = ['error_message' => $ex->getMessage()];
        }
        $this->response($response);
    }
    
     /**
     * @param null
     * @function is used to sent otp on user email or phone.
     * @return true(list)/false
     */
    public function otp_sent_post()
    {
        // resend otp used after edit profile of provider and patient
        $response = [];
        $params = $this->post();
        $url=explode('-',$this->uri->segment(1));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['user_type', 'key_value', 'key_type'];
        $validation = $this->CommonModel->validation($params, $required);
        $mobile_number=0;
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider']) && isset($url[1]) && in_array($url[1],['resend','send'])) {
            $params['key_value']=(string)$params['key_value'];
            if($url[1]=='send'){
                if($params['key_type']=='mobile' && isset($params['userid']) && isset($params['country_code'])){
                    $wheres = ['_id' => new MongoId($params['userid'])];
                    $mobile_number=$params['country_code'].$params['key_value'];
                }else
                    $wheres = [$params['key_type'] => $params['key_value']];
            } else{
              $wheres=['change_data' =>['$elemMatch'=>[$params['key_type']=> $params['key_value']]]];
            }
            $otpUserData = $this->CommonModel->getCollectionData($params['user_type'] . 's', $wheres, ['email', 'mobile','country_code','change_data']);
            if ($otpUserData) {
		                $valid=0;
		                if($params['key_type']=='email'){
		                    $digits = 6;
		                    $updateValue = rand(pow(10, $digits-1), pow(10, $digits)-1);
		                    $message= $updateValue.' is your one time password(OTP) for email verification.';
		                    send_email($params['key_value'],'OTP Verification Code',$message);
		                    $response["status"] = 1;
		                    $response['message'] = 'OTP successfully sent on your email id';
		                    $response['data'] = ['userid' => $otpUserData[0]['_id']->{'$id'}];
		                    $valid=1;
		                }else if($params['key_type']=='mobile'){ 
		                    $mobile_number=($mobile_number)?$mobile_number:$otpUserData[0]['country_code'].$otpUserData[0]['mobile'];
		                    $updateValue=send_sms('sent',$mobile_number);
		                    if($updateValue){
		                        $response["status"] = 1;
		                        $response['message'] = 'OTP successFully sent on your mobile number';
		                        $response['data'] = ['userid' => $otpUserData[0]['_id']->{'$id'}];
		                        $valid=1;
		                    }else{
		                        $response["status"] = 0;
		                        $response['message'] = 'Please try again.';
		                         $this->response($response);
		                    }

		                }else{
		                	$response["status"] = 0;
		                    $response['message'] = 'Please call valid key type value.';
		                    $this->response($response);
		                }
		                if($valid){
		                    if($url[1]=='resend'){
		                        $otpUserData[0]['change_data'][0][$params['key_type'].'_otp']=(string)$updateValue;
		                        $updatedData=['change_data'=>$otpUserData[0]['change_data']];
		                    }
		                    else{
		                        
		                        $updatedData=['otp_or_sessionId'=>(string)$updateValue];
		                    }
		                    $this->CommonModel->upsert($params['user_type'].'s',$updatedData,(string)$otpUserData[0]['_id'],true);

		            } else {
		                $response["status"] = 0;
		                 $msg=($params['key_type']=='mobile')?' number':' id';
		                $response["message"] = 'Your ' . $params['key_type'].$msg.' invalid';
		            }
		     } else {
		            $response["status"] = 0;
		            $msg=($params['key_type']=='mobile')?' number':' id';
		            $response["message"] = 'Your ' . $params['key_type'].$msg.' not found';
        		}

        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

     /**
     * @param null
     * @function is used to set price accroding to frequency time
     * @return true/false
     */
    public function verify_get()
    {
        $response = [];
        $params = $this->get();
        $url=$this->uri->segment(2);
         /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid','user_type','otp'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && in_array($params['user_type'], array('patient','provider')) && in_array($url, array('mobile','email'))) {
            try{
                 $valid=0;
                 $key_name=($url=="email")?"email":"mobile";
                 $userExist= $this->CommonModel->getCollectionData($params['user_type'].'s',['_id' => new \MongoId($params['userid'])]);
                    if($userExist){
                            $updated[$key_name.'_status']=1;
                            $params['otp']=(string)$params['otp'];
                            if($url=='email'){
                                $valid=($params['otp']==$userExist[0]['otp_or_sessionId'])?1:0;
                            }else{
                                if(isset($params['country_code']) && isset($params['mobile'])){
                                    $updated['country_code']=$params['country_code'];
                                    $updated['mobile']=$params['mobile'];
                                }else{
                                    $response["status"] = 0;
                                    $response["message"] = 'Mobile number and country_code must be required';
                                    $this->response($response);
                                }
                                $valid=(send_sms('otp-match',$params['otp'],$userExist[0]['otp_or_sessionId']))?1:0;
                            }

                            if($valid){
                                $response["status"] = 1;
                                $this->CommonModel->upsert($params['user_type'].'s',$updated,$params['userid'],true);
                                $msg=($url=='email')?'email id':"mobile number";
                                $response['message'] = 'Your '.$msg.' has been verified';
                            }else{
                                $response["status"] = 0;
                                $response['message'] = 'Your OTP Mismatched Please try again.';
                            }
                        
                    }else{

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
        } else {
            $response["status"] = 0;
            $response['message'] = 'User type invalid or url.';
            if($validation)
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }

    /**
     * @param null
     * @function is used to send queery by login user
     * @return true/false
     */
    public function password_update_post()
    {
        $response = [];
        $params = $this->post();
        $url = explode('-', $this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['user_type', 'userid', 'password', 'cpassword','otp'];
        if (isset($url[0]) && $url[0] == 'change') {
            array_push($required, 'oldpassword');
        }
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider']) && isset($url[0]) && in_array($url[0], ['change', 'reset'])) {

            try {
                if ($params['password'] == $params['cpassword']) {
                    $wheres['_id'] = new \MongoId($params['userid']);
                    //$wheres['password'] = md5($params['password']);
                    $oldpasswordCheck = $this->CommonModel->getCollectionData($params['user_type'] . 's', $wheres);
                    if (!empty($oldpasswordCheck)) {
                        $params['otp']=(string)$params['otp'];
                        if($params['otp']!=$oldpasswordCheck[0]['otp_or_sessionId']) {
                            $response["status"] = 0;
                            $response['message'] = 'Your OTP Mismatched Please try again.';
                            $this->response($response);
                        }
                        $updatedValue['password'] = md5($params['password']);
                        $userdata = $this->CommonModel->upsert($params['user_type'] . 's', $updatedValue, $params['userid'], true);
                        if ($userdata) {
                            $response["status"] = 1;
                            $response['message'] = 'Your password has been updated successfully';
                        } else {
                            $response["status"] = 0;
                            $response['message'] = 'Invalid User id.';
                        }
                    } else {

                        $response["status"] = 0;
                        $response['message'] = 'Invalid User';
                    }
                } else {
                    $response["status"] = 0;
                    $response['message'] = 'Password and confirm password does not match.';
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response['message'] = 'Invalid User';
            }
        } else {

            $response["status"] = 0;
            $response['message'] = 'Pleaes valid post data or user_type';
            if ($validation)
                $response["error_data"] = $validation;
            else
                $response["error_data"] = ['error_message' => 'Pleaes valid url or user_type'];
        }
        $this->response($response);
    }

     /**
     * @param null
     * @function is used to set update mobile and email after edit profile
     * @return true/false
     */
    public function update_post()
    {
        $response = [];
        $params = $this->post();
        $url=$this->uri->segment(2);
         /*         * ******* CEHCK MANDATORY FIELDS ************* mk@brsoftech.o hitesh.shrimali@brsoftech.org */ 
        $required = ['userid','user_type','key_value','otp'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && in_array($params['user_type'], array('patient','provider')) && in_array($url, array('mobile','email'))) {
            try{
                 $key_name=($url=="email")?"email":"mobile";
                 $sWheres=['_id' => new \MongoId($params['userid']),'change_data'=>['$elemMatch'=>[$key_name=>$params['key_value']]]];
                 $userExist= $this->CommonModel->getCollectionData($params['user_type'].'s',$sWheres);
                    if($userExist){
                        $valid=0;
                        $params['otp']=(string)$params['otp'];
                        if($url=='email'){
                            $valid=($params['otp']==$userExist[0]['change_data'][0]['email_otp'])?1:0;
                       }else{
                        $valid=(send_sms('otp-match',$params['otp'],$userExist[0]['change_data'][0]['mobile_otp']))?1:0;
                       }
                        if($valid){
                            $checkEmail = $this->CommonModel->alreadyExists($params['user_type'].'s',[$key_name=>$params['key_value']],$params['userid']);
                            $msg=($url=='email')?'email id':"mobile number";
                            if($checkEmail){
                                $response["status"] = 0;
                                $response['message'] = 'This '.$msg.' has been already taken';
                            }else{
                                $response["status"] = 1;
                                if($key_name=='email'){
                                 $ch_key='mobile';
                                 $ch_value=(isset($userExist[0]['change_data'][0]['mobile']))?$userExist[0]['change_data'][0]['mobile']:0;  
                                }else{
                                    $ch_key='email';
                                    $ch_value=(isset($userExist[0]['change_data'][0]['email']))?$userExist[0]['change_data'][0]['email']:'';
                                }
                                $change_data[0]=[$ch_key=>$ch_value,$key_name=>''];
                                $updateData=[$key_name=>$params['key_value'],'change_data'=>$change_data];
                                $this->CommonModel->upsert($params['user_type'].'s',$updateData,$params['userid'],true);
                                $response['message'] = 'Your '.$msg.' has been verified and updated.';
                            }
                        }else{
                            $response["status"] = 0;
                            $response['message'] = 'Your OTP Mismatched Please try again.';
                            $this->response($response);
                        }
                        
                    }else{

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User or email id.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
        } else {
            $response["status"] = 0;
            $response['message'] = 'User type invalid or url.';
            if($validation)
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }
    /**
     * @param null
     * @function is used to get frequency list
     * @return true/false
     */
    public function frequency_get()
    {
        $response = [];
        $params = $this->get();

        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        if (!in_array(null, $params) && isset($params['userid']) && isset($params['user_type']) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {
                $loginExist = $this->CommonModel->getCollectionData($params['user_type']. 's', ['_id' => new MongoId($params['userid'])]);
                if ($loginExist) {

                    $frequencyList = $this->CommonModel->getCollectionData('frequency', ['status' => 1], ['time_in_mins']);

                    if ($frequencyList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $frequencyList;
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'frequency list empty.';
                    }
                } else {
                    $response["status"] = 0;
                    $response['message'] = 'Invalid userid';
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = "Invalid Object id";
            }

        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
        }
        $this->response($response);
    }

    /**
     * @param null
     * @function is used to get services list
     * @return true/false
     */ 
    public function servicesList_get()
    {
        $response = [];
        $params = $this->get();

        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        if (!in_array(null, $params) && isset($params['userid']) && isset($params['type']) && in_array($params['type'], ['patient', 'provider'])) {
            try {
                $loginExist = $this->CommonModel->getCollectionData($params['type']. 's', ['_id' => new MongoId($params['userid'])]);
                $wheres['status']=1;
                if(isset($params['list_type']) && $params['list_type']=='Free'){

                    $wheres['type']="Free";
                }else {
                    $wheres['type']="";
                }   
                if ($loginExist) {
                    $servicesList = $this->CommonModel->getCollectionData('providerServices', $wheres , ['name','type']);

                    if ($servicesList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $servicesList;
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'Services list empty.';
                    }
                } else {
                    $response["status"] = 0;
                    $response['message'] = 'Invalid userid';
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = "Invalid Object id";
            }

        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
        }
        $this->response($response);
    }
    
     /**
     * @param null
     * @function is used to get services list
     * @return true/false
     */
    public function speciality_list_get()
    {
        $response = [];
        $params = $this->get();

        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        if (!in_array(null, $params) && isset($params['userid']) && isset($params['user_type']) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {
                $loginExist = $this->CommonModel->getCollectionData($params['user_type']. 's', ['_id' => new MongoId($params['userid'])]);
                if ($loginExist) {
                    $specialityList = $this->CommonModel->getCollectionData('speciality', ['status' => 1], ['name']);

                    if ($specialityList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $specialityList;
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'Speciality list empty.';
                    }
                } else {
                    $response["status"] = 0;
                    $response['message'] = 'Invalid userid';
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = "Invalid Object id";
            }

        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
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

                $checkValidAppment = $this->CommonModel->getCollectionData("patientAppointments", $appmentWheres, ['patient_id']);
                if ($checkValidAppment) {

                         $appmentStatusSearch['provider_id'] = new \MongoId($params['provider_id']);
                        $appmentStatusSearch['patient_id'] = new \MongoId($params['patient_id']);
                        $response["status"] = 1;
                        if ($params['user_type'] == 'provider') {

                            $appmentStatusSearch['provider_status'] = (int)$params['appointment_status'];
                            if($appmentStatusSearch['provider_status']==2)
                            {
                                $appmentStatusSearch['patient_status'] = 2;
                                $response['message'] = ucwords($params['user_type']) . " booking status updated";
                            }else if ($appmentStatusSearch['provider_status']==4){

                                $response['message'] = "Appointment has been finished.";

                            }else{
                                $response['message'] = ucwords($params['user_type']) . " booking status updated";
                            }

                            
                        } else {
                            $appmentStatusSearch['patient_status'] = (int)$params['appointment_status'];
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
     * @function is used to get appointment status list
     * @return true/false
     */
    public function status_common_list_post()
    {
        $response = [];
        $params = $this->post();

        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        if (!in_array(null, $params) && isset($params['userid']) && isset($params['user_type']) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {

                $loginExist = $this->CommonModel->getCollectionData($params['user_type'] . 's', ['_id' => new MongoId($params['userid'])]);
                if($params['user_type']=='provider')
                    $satusList=explode(',',APPOINTMENT_PROVIDER_STATUS_LIST);
                else
                    $satusList=explode(',',APPOINTMENT_PATIENT_STATUS_LIST);
                if ($loginExist) {
                    
                    $loginid= new MongoId($params['userid']);
                    $status_type=($params['user_type']=='provider')?'provider_status':'patient_status';
                    foreach ($satusList as $key => $value) {

                        $wheres['appointment_type']=['$in'=>['Home']];
                        if($key==0 ){
                            $app_list='';
                            if($params['user_type']=='provider'){
                                $wheres['provider_status']=1; 
                                $wheres['patient_status']=0;
                                $wheres['confirm_doctors_ids']=['$in'=>[$loginid]];
                                $app_list = $this->CommonModel->getCollectionData('patientAppointments', $wheres);
                                unset($wheres['confirm_doctors_ids']);
                            } 
                            $wheres['provider_status']=0; 
                            $wheres['patient_status']=0;                   
                            $dataList = $this->CommonModel->getCollectionData('patientAppointments', $wheres);
                            $count=count($dataList)+count($app_list);
                        }else{
                                $wheres[$params['user_type'] . '_id'] =['$exists'=>true,'$eq'=>$loginid];
                                if($key==1){
                                    if($params['user_type']=='provider'){
                                        $wheres['patient_status']=['$ne'=>3];
                                    }else{
                                        $wheres['provider_status']=['$ne'=>3];
                                    }
                                }
                                
                                $wheres[$status_type]=$key;
                                $dataList = $this->CommonModel->getCollectionData('patientAppointments', $wheres);
                                $count=count($dataList);
                            }
                        
                        $wheres=[];
                        $satusListUpdated[]=['key_value'=>$key,'key_name'=>$satusList[$key],'count'=>$count];
                    }
                    if ($satusListUpdated) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $satusListUpdated;
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'list empty.';
                    }
                } else {
                    $response["status"] = 0;
                    $response['message'] = 'Invalid userid';
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = "Invalid Object id";
            }

        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
        }
        $this->response($response);
    }

   

    

    /**
     * @param null
     * @function is used to send queery by login user
     * @return true/false
     */
    public function contact_us_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['user_type', 'userid', 'name', 'mobile', 'message', 'email'];
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {
                $params['userid'] = new \MongoId($params['userid']);
                $userdata = $this->CommonModel->upsert('contactUs', $params);
                if ($userdata) {
                    $response["status"] = 1;
                    $response['message'] = 'Your contact request has been send successfully';
                } else {
                    $response["status"] = 0;
                    $response["error_data"] = ['error_message' => 'User Query not submited.'];
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["error_data"] = ['error_message' => 'Invalid User'];
            }
        } else {
            $response["status"] = 0;
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

    


    /**
     * @param null
     * @function is used to get custom list of any collections .
     * @return true(list)/false
     */
    public function custom_list_post()
    {
        $response = [];
        $params = $this->post();
        $url = explode('-', $this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['list_type', 'selected_fields'];
        $otherRules = ['selected_fields' => ''];
        $validation = $this->CommonModel->validation($params, $required, $otherRules);
        if (empty($validation) && in_array($params['list_type'], ['patients', 'providers','providerWorks']) && is_array($params['selected_fields'])) {
            try {
                $wheres['deleted']='';
                if(isset($params['userid']))
                $wheres=['userid'=> new \MongoId($params['userid'])];
                $dataList = $this->CommonModel->getCollectionData($params['list_type'],$wheres,$params['selected_fields']);
                if ($dataList) {
                    $response['status'] = 1;
                    $response['message'] = 'success';
                    $response['data'] = $dataList;
                } else {
                    $response["status"] = 0;
                    $response["message"] = 'Data not available ';
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = 'Invalid User';
            }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

     /**
     * @param null
     * @function is used to get email list for login user relevant to appintment both patient and provider 
     * @return true/false
     */
    public function email_list_get()
    {
        $response = [];
        $params = $this->get();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['user_type', 'userid'];
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {

                $user_type=$params['user_type'];
                $wheres[$user_type.'_id'] = new \MongoId($params['userid']);
                if($user_type=='patient'){
                    $selected_type="provider_id";
                    $list_type="providers";
                    $wheres['provider_id']=['$exists'=>true,'$ne'=>'','$ne'=>null];
                 }else{
                    $list_type="patients";
                    $selected_type="patient_id";
                    $wheres['patient_id']=['$exists'=>true,'$ne'=>'','$ne'=>null];
                }
                $project=[$selected_type,'confirm_doctors_ids','patient_status','provider_status'];

                $idsList = $this->CommonModel->getCollectionData('patientAppointments',$wheres,$project,['created'=>1]);
                if ($idsList) {
                    $ids=[];
                    foreach ($idsList as $key => $value) {

                        if(count($ids)>0)
                            $ids=array_merge($ids,[$value[$selected_type]]);
                        else
                            $ids=[$value[$selected_type]];
                        if($value['patient_status']==0 && $value['provider_status']==1 && $user_type=='patient'){
                            if(count($value['confirm_doctors_ids'])>0){
                                $ids=array_merge($ids,$value['confirm_doctors_ids']);
                            }
                            
                        }

                        
                    }
                    if(count($ids)>0)
                    {
                        $selected=['_id','firstname','lastname','email'];
                        if($list_type=="patients"){
                            $patient_list=$this->CommonModel->getCollectionData($list_type,['_id'=>['$in'=>$ids]],$selected,['firstname'=>1,'lastname'=>1]);
                            foreach ($patient_list as $key => $value) {
                                
                                $pData=getPatientData($value['_id']->{'$id'},[],$selected);
                                $list[]=$pData['data'];
                            }
                        }else{
                        $list=$this->CommonModel->getCollectionData($list_type,['_id'=>['$in'=>$ids]],$selected);
                        }
                        if($list){
                            $response["status"] = 1;
                            $response['message'] = 'SuccessFully email List';
                            $response['data'] = $list;
                        }else{
                            $response["status"] = 0;
                            $response["message"] = 'Email list empty';
                        }
                    }else{
                        $response["status"] = 0;
                        $response["message"] = 'Email list empty';
                    }
                    
                } else {
                    $response["status"] = 0;
                    $response["message"] = 'Email list empty';
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = 'Invalid User';
            }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

     /**
     * @param null
     * @function is used to get email list for login user relevant to appintment both patient and provider 
     * @return true/false
     */
    public function quickblox_post()
    {
        $response = [];
        $params = $this->post();
        $url = explode('-', $this->uri->segment(1));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['user_type', 'userid'];
        if(in_array($url[3],['info','id','dialogid'])){
            if($url[3]=='id'){
                array_push($required, 'quickblox_id');
            }

            if($url[3]=='dialogid'){
                array_push($required, 'dialog_id','appointment_id');
            }
        }else{
            $response["status"] = 0;
            $response["message"] = 'Invalid URL';
            $this->response($response);
        }
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {
                $user_type=$params['user_type'];
                $userid= new \MongoId($params['userid']);
                $userExists=$this->CommonModel->getCollectionData($user_type.'s',['_id'=>$userid]);
                if($userExists){
                    
                    $ext_name=($user_type=='patient')?QUICKBLOX_PATIENT_EMAIL_EXTENSION:QUICKBLOX_PROVIDER_EMAIL_EXTENSION;
                    $quickBlox['quickblox_info']=['email'=>(string)$userExists[0]["_id"].$ext_name,'password'=>QUICKBLOX_USER_PASSWORD,'quickblox_id'=>''];
                    $response["status"] = 1;
                    $response["message"] = 'Success';
                   if($url[3]=='id'){   
                    $quickBlox['quickblox_info']['quickblox_id']=$params['quickblox_id'];
                    $this->CommonModel->upsert($user_type.'s', $quickBlox,$params['userid'],true);            
                    $data['quickblox_info']=$quickBlox['quickblox_info'];

                   } else if($url[3]=='dialogid'){

                        if(isset($userExists[0]['quickblox_info']['quickblox_id']) && !empty($userExists[0]['quickblox_info']['quickblox_id']))
                        {   $wheres[$user_type.'_id'] = $userid;
                            $wheres['_id'] = new \MongoId($params['appointment_id']);
                            $exists=$this->CommonModel->getCollectionData('patientAppointments',$wheres);
                            if($exists){
                                $updated['dialog_id']=$params['dialog_id'];
                                $this->CommonModel->getCollectionData('patientAppointments',$updated,$params['appointment_id'],true);
                                $data['dialog_id']=$params['dialog_id'];
                                $data['quickblox_info']=$userExists[0]['quickblox_info'];
                            }else{
                                $response["status"] = 0;
                                $response["message"] = 'Invalid Appointment';
                                $this->response($response);
                            }
                        }else{
                            $response["status"] = 0;
                            $response["message"] = 'Please create first quickblox_id';
                            $this->response($response);
                        }
                   } else if($url[3]=='info'){
                    if(isset($userExists[0]['quickblox_info']))
                        $data['quickblox_info']=$userExists[0]['quickblox_info'];
                    else
                        $data['quickblox_info']=$quickBlox['quickblox_info'];
                   }
                $response['data']=$data;
                }else{
                    $response["status"] = 0;
                    $response["message"] = 'Invalid User Id';
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = 'Invalid User';
            }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

     public function imageUpload_post()
     {
     	$response = [];
        $params = $this->post();
        $url = explode('-', $this->uri->segment(1));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['user_type', 'userid'];
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {

		     	$image = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';

		        if (!empty($image)) {
            	$user_type=$params['user_type'];
                $userid= new \MongoId($params['userid']);
                $userExists=$this->CommonModel->getCollectionData($user_type.'s',['_id'=>$userid]);
                if($userExists){
	            	$this->load->library('upload');
		            $config['upload_path'] = "./assets/upload/$user_type"."s/";
		            $config['allowed_types'] = 'jpg|jpeg|png';
		            $config['max_size'] = '1000000000000000';
		            $config['overwrite'] = TRUE;

		            $title = date('YmdHis');
		            $rand = rand(100000, 999999);
		            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
		            $fileName = $rand . $title . '.' . $ext;
		            $image = $fileName;
		            $config['file_name'] = $fileName;
		            $this->upload->initialize($config);

		            if ($this->upload->do_upload('image')) {
		                $this->upload->data();
		                $image = $fileName;
		                /*** Image resize ****/
		                $this->load->library('image_lib');
		                $resize['image_library'] = 'gd2';
		                $resize['source_image'] = "./assets/upload/$user_type"."s/". $image;
		                $tnumb = $rand . $title . '_thumb.' . $ext;
		                $resize['new_image'] = "./assets/upload/$user_type"."s/$tnumb";
		                $resize['maintain_ratio'] = TRUE;
		                $resize['width'] = 150;
		                $resize['height'] = 150;
		                $this->image_lib->initialize($resize);
		                $this->image_lib->resize();
		                $params['img_extension'] = $ext;
		                if($user_type=='patient'){
		                	$this->load->library('encrypt');
		                	$imageName['image'] = $this->encrypt->encode($image, $userExists[0]['user_key']);
		                }else{
		                	$imageName['image'] = $image;
		            	}
		                $this->CommonModel->upsert($user_type.'s', $imageName,$params['userid'],true); 

		                $response["status"] = 1;
	                	$response["message"] = 'Profile Image uploaded.';
                        $response["image_url"] = base_url()."assets/upload/$user_type"."s/". $image;

		            } else {
		                $response["status"] = 0;
	                	$response["message"] = 'Image not uploaded Please try again.';
		            }
		        }else{
		        	$response["status"] = 0;
                	$response["message"] = 'Invalid User';
		        }
	        }else{
	        	$response["status"] = 0;
                $response["message"] = 'Image not uploaded Please try again.';
	        }
	    } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = 'Invalid User';
            }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }
}
