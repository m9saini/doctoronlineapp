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
class Patient extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->model('api/patient/PatientModel', 'Patient');
    }

    /************************** Patient Authontication Patients *************************/

    /**
     * @param null
     * @function is used to patient registrations
     * @return array
     */
    public function signup_put()
    {

       try{
        $response = [];
        $params = $this->put();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['firstname','lastname','email','password','cpassword','dob','mobile','device_id','device_token','device_type','latitude','longitude'];
        $validation = $this->CommonModel->validation($params,$required);
        if(empty($validation)) {

             $checkEmail = $this->CommonModel->alreadyExists('patients',array('email'=>$params['email']));

            /** ******* CEHCK EMAIL ************* */
            if (empty($checkEmail) && $params['email'] != '') {

                $checkmobile = $this->CommonModel->alreadyExists('patients',array('mobile'=>$params['mobile']));
                if (empty($checkmobile) && $params['mobile']) {
                /*             * ******* CEHCK PASSWORD ************* */
                if (isset($params['password']) &&  $params['password'] == $params['cpassword']) {
                    $signup = $this->Patient->signup($params);
                    if ($signup) {
                        $response["status"] = 1;
                        $response['message'] = 'Your account has been successfully created. Check your email for further instructions.';
                        $digits = 6;
                        $otp_or_sessionId = rand(pow(10, $digits-1), pow(10, $digits)-1);
                        $message= $otp_or_sessionId.' is your one time password(OTP) for email verification.';
                        send_email($params['email'],'OTP Verification Code','Welcome in Online Appointment '.$message);
                        //$otp_or_sessionId=send_sms('sent',$params['country_code'].$params['mobile']);
                        $this->CommonModel->upsert('patients',['otp_or_sessionId'=>(string)$otp_or_sessionId],$signup->{'$id'},true);
                        $response['data'] = ['userid'=>$signup->{'$id'}];
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Data not saved.';
                    }

                } else {

                    $response["status"] = 0;
                    $response["message"] = 'Confirm password does not match.';
                }
            } else {

                $response["status"] = 0;
                $response["message"] = 'This mobile number has already been taken.';
            }

            } else {

                $response["status"] = 0;
                $response["message"] = 'This email address has already been taken.';
            }
        } else {
            //$msg = $model->getErrors();
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
    } catch(Exception $ex){

            $response["status"] = 0;
            $response["message"] = 'Invalid Object id.';
            $response["error_data"] = ['error_message' => $ex->getMessage()];
        }
        $this->response($response);
    }

    /**
     * @param null
     * @function is used to patient login
     * @return array
     */
    public function login_post()
    {
        try{
                $response   =   $data   =   [];
                $params = $this->post();
                $required=['email','password','device_id','device_token','device_type'];
                $validation = $this->CommonModel->validation($params,$required);
                if(empty($validation) ) {

                    $this->load->library('encryption');
                    $this->load->library('encrypt');

                    $login = $this->Patient->login($params);
                    if($login){

                        if($login[0]['status']!=1 || $login[0]['deleted']!=''){
                            $response["status"] = 0;
                            $response["message"] = 'Your account deactivated Please contact to admin';
                            $this->response($response);
                        }


                        $key                    =   $login[0]['user_key'];
                        $data['firstname']      =   $this->encrypt->decode($login[0]['firstname'], $key);
                        $data['lastname']       =   $this->encrypt->decode($login[0]['lastname'], $key);
                        $data['email']          =   $login[0]['email'];
                        $data['dob']            =   isset($login[0]['dob'])? $this->encrypt->decode($login[0]['dob'], $key):'';
                        $data['gender']         =   isset($login[0]['gender'])? $this->encrypt->decode($login[0]['gender'], $key):'';
                        $data['mobile']         =   isset($login[0]['mobile'])?$login[0]['mobile']:'';
                        $data['country_code']   =   isset($login[0]['country_code'])? $login[0]['country_code']:'';
                        $data['city']           =   isset($login[0]['city'])?$this->encrypt->decode($login[0]['city'], $key):'';
                        $data['state']          =   isset($login[0]['state'])?$this->encrypt->decode($login[0]['state'], $key):'';
                        $data['plot_unit_no']   =   isset($login[0]['plot_unit_no'])?$this->encrypt->decode($login[0]['plot_unit_no'], $key):'';
                        $data['street_add']     =   isset($login[0]['street_add'])?$this->encrypt->decode($login[0]['street_add'], $key):'';
                        $data['image']          =  (isset($login[0]['image']) && !empty($login[0]['image']))?base_url().'assets/upload/patients/'.$this->encrypt->decode($login[0]['image'], $key):'';

                        $data['mobile_status'] =   isset($login[0]['mobile_status'])?$login[0]['mobile_status']:0;
                        $data['email_status'] =   isset($login[0]['email_status'])?$login[0]['email_status']:0;
                        $data['userid']        =   $login[0]['_id']->{'$id'};
                        
                        $wheres['patient_id']   =   new \MongoId($login[0]['_id']->{'$id'});

                        $response["status"]     = 1;
                        $response['message']    = 'Success.';
                        if($data['email_status']!=1)
                        {
                            $digits = 6;
                            $otp_or_sessionId = rand(pow(10, $digits-1), pow(10, $digits)-1);
                            $message= $otp_or_sessionId.' is your one time password(OTP) for email verification.';
                            send_email($params['email'],'OTP Verification Code','Welcome in Online Appointment '.$message);
                            //$otp_or_sessionId=send_sms('sent',$params['country_code'].$params['mobile']);
                            $this->CommonModel->upsert('patients',['otp_or_sessionId'=>(string)$otp_or_sessionId],$data["userid"],true);
                        }
                        if(isset($login[0]['quickblox_info']))
                            $data['quickblox_info'] =   $login[0]['quickblox_info'];
                        else
                            $data['quickblox_info'] =['email'=>(string)$login[0]['_id'].QUICKBLOX_PATIENT_EMAIL_EXTENSION,'password'=>QUICKBLOX_USER_PASSWORD,'quickblox_id'=>''];
                        $data["location"]=(isset($login[0]['location']))?$login[0]['location']:'';
                        $response['data']       = $data;
                    }else{
                        $response["status"] = 0;
                        $response["message"] = 'Please enter valid user name and Password';
                    }

                } else {
                    $response["status"] = 0;
                    $response["message"] = 'Mandatory fields are required.';
                }
            } catch(Exception $ex){

                $response["status"] = 0;
                $response["message"] = 'Invalid Object id.';
                $response["error_data"] = ['error_message' => $ex->getMessage()];
             }

        $this->response($response);
    }

    /**
     * @param null
     * @function is used to get patient inforamtion
     * @return array
     */
    public function profile_view_get()
    {
        try{
                $response   =   $userdata   =   [];
                $params = $this->get();
                /*         * ******* CEHCK MANDATORY FIELDS ************* */
                $required=['userid'];
                $validation = $this->CommonModel->validation($params,$required);
                if(empty($validation)) {

                    $userdata   =   getPatientData($params['userid']);
                    if ($userdata['status']) {

                        $response["status"] = 1;
                        $response['message'] = 'success.';
                        unset($userdata['data']['user_key']);
                        $response['data'] = $userdata['data'];

                    } else {

                        $response["status"] = 1;
                         $response["message"] = 'Invalid Object id.';
                    }
                } else {
                    $response["status"] = 0;
                     $response["message"] = 'Invalid Object id.';
                    $response["error_data"] = $validation;
                }
            } catch(Exception $ex){

                $response["status"] = 0;
                 $response["message"] = 'Invalid Object id.';
                $response["error_data"] = ['error_message'=> $ex->getMessage()];
             }

        $this->response($response);
    }
    /**
     * @param null
     * @function is used to patient edit profile
     * @return array
     */
    public function profile_edit_post()
    {
        $response = []; $image_not_error=0;
        $params = $this->post();
         /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','firstname','lastname','email','dob','country_code','mobile'];
        $validation = $this->CommonModel->validation($params,$required);
        if(empty($validation) )
        {
             $checkEmail = $this->CommonModel->alreadyExists('patients',['email'=>$params['email']],$params['userid']);
                /** ******* CEHCK EMAIL ************* */
                if (empty($checkEmail) && $params['email'] != '') {

                    $checkmobile = $this->CommonModel->alreadyExists('patients',['mobile'=>$params['mobile']],$params['userid']);

                    if (empty($checkmobile) && $params['mobile'] != '') {
                        $image_not_error  =   1;

                            /*      * *** Profile Image Update *******      */
                            $image = (isset($_FILES['image']['name']) &&  !empty($_FILES['image']['name'])) ?$_FILES['image']['name']:'';

                                 if(!empty($image))
                                    {
                                        $this->load->library('upload');
                                        $config['upload_path'] = './assets/upload/patients/';
                                        $config['allowed_types'] = 'jpg|jpeg|png';
                                        $config['max_size'] = '1000000000000000';
                                        $config['overwrite'] = TRUE;

                                        $title = date('YmdHis');
                                        $rand = rand(100000,999999);
                                        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                                        $fileName = $rand .$title.'.'.$ext;
                                        $image = $fileName;
                                        $config['file_name'] = $fileName;
                                        $this->upload->initialize($config);
                                        if ($this->upload->do_upload('image')){
                                            $this->upload->data();
                                            $image = $fileName;
                                            /*** Image resize ****/
                                            $this->load->library('image_lib');
                                            $resize['image_library'] = 'gd2';
                                            $resize['source_image'] = './assets/upload/patients/'.$image;
                                            $tnumb = $rand .$title.'_thumb.'.$ext;
                                            $resize['new_image'] = "./assets/upload/patients/$tnumb";
                                            //$resize['maintain_ratio'] = TRUE;
                                            $resize['width']         = 150;
                                            $resize['height']       = 150;
                                            $this->image_lib->initialize($resize);
                                            $this->image_lib->resize();
                                            $params['image']=$image;
                                    
                                        } else{
                                            $image_not_error=0;
                                        }
                                    }
                                /* ****** End Profile Imahe Upoload Section *****     */
                                if($image_not_error){
                                    $update_profile = $this->Patient->update_profile($params);
                                    if ($update_profile) {
                                        $response["status"] = 1;
                                        $response['message'] = 'Your account has been successfully updated. Check your email for further change email address.';
                                        $response['data'] = $update_profile;
                                    } else {
                                        $response["status"] = 0;
                                         $response["message"] = 'Invalid Object id.';

                                    }
                                }else{
                                    $response["status"] = 0;
                                    $response["message"] = 'Please upload valid image.';
                                }
                            } else {

                                $response["status"] = 0;
                                $response["message"] = 'This mobile number has been already  taken.';

                            }

                        } else {

                            $response["status"] = 0;
                            $response["message"] = "This email address has been already taken.";

                        }

                    } else {
                        
                        $response["status"] = 0;
                         $response["message"] = 'Invalid Object id.';
                        $response["error_data"] = $validation;
                    }

        $this->response($response);
    }
    /**
     * @param null
     * @function is used to get dashboard data
     * @return array
     */
    public function dashboard_get()
    {

        $response = [];
        $params = $this->get();
        /*         * ******* CEHCK VALID OBJECT ID************* */
        $idExists=getPatientData($params['userid']);
        if ($idExists['status']) {
            $userdata = $this->Patient->getDashboardData($params['userid']);
            if ($userdata) {
                $response["status"] = 1;
                $response['message'] = 'success';
                $response['data'] = $userdata;
            } else {
                $response["status"] = 0;
                $response["message"] = 'Data not found';
            }
        } else {
            //$msg = $model->getErrors();
            $response["status"] = 0;
            $response["message"] = $idExists['message'];
        }

        $this->response($response);
    }

     /**
     * @param null
     * @function is used to update patient settings
     * @return true/false
     */
    public function patient_setting_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        if (isset($params['userid']) && !empty($params['userid'])) {
            try {
                    $search=['_id'=> new \MongoId($params['userid'])];
                    $patientSettingData = $this->CommonModel->getCollectionData('patients',$search,['setting']);
                    if($patientSettingData){ 
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        if(isset($params['setting']) && is_array($params['setting'])){
                                $update['setting']=$params['setting'];
                        }else{
                            if($patientSettingData[0]['setting']){
                                $update['setting']=$patientSettingData[0]['setting'];
                            }else{
                                $update['setting']['notification']=[['booking_notify'=>0,'medical_specialists'=>0]];
                            }
                        }
                        $this->CommonModel->upsert('patients',$update,$params['userid'],true);
                        $response['data'] = $update;
                    }else{
                        $response["status"] = 0;
                        $response['message'] = 'Invalid Object';
                    }
                } catch (Exception $ex) {

                $response["status"] = 0;
                $response['message'] = 'Invalid Object Id';
            }
            
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
        }
        $this->response($response);
    }
}
