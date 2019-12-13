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
        // $this->load->model('ApiModel', 'Api');
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
                $response['data']['gender_list'][0] = ["key_name" => "Female", "key_value" => "0"];
                $response['data']['gender_list'][1] = ["key_name" => "Male", "key_value" => "1"];
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

                $checkSocial = $this->CommonModel->alreadyExists($params['user_type'], array('social_token_id' => $params['social_token_id'], 'social_type' => $params['social_type']));

                $insertdata  =  $this->signup_data($params);
                /** ******* CEHCK EMAIL ************* */

                if (empty($checkSocial) && $params['social_token_id'] != ''
                    && $params['social_type'] != '') {

                    $signup     =   $this->CommonModel->upsert($params['user_type'], $insertdata);

                    $wheres     =   ['_id' =>  new \MongoId($signup)];
                    $login  =   $this->CommonModel->login($params['user_type'], $wheres);

                }else{

                    $wheres  =   [
                                    'social_token_id'   =>      $params['social_token_id'],
                                    'social_type'       =>      $params['social_type'],
                                ];

                    $updatefield    =   [
                                            'device_id'=>$params['device_id'],
                                            'device_type'=>$params['device_type'],
                                            'device_token'=>$params['device_token']
                                        ];
                    $login   =   $this->CommonModel->updateDevice($params['user_type'], $wheres, $updatefield);

                }
                if($params['user_type'] == 'providers') {

                    $selected=['sufix','firstname','lastname','email','dob', 'country_code', 'mobile', 'latitude','longitude','gender','city','state', 'zipcode','street_add','image','profile','status','social_type', 'social_token_id','device_type','device_token','device_id', 'language'];

                    $data["userid"]         =   $login[0]['_id']->{'$id'};

                    foreach ($selected as $select){

                        if($select == 'image'){

                            $data["image"] =  (isset($login[0]['image']) && !empty($login[0]['image']))?base_url().'assets/upload/providers/'.$login[0]['image']:'';

                        }else {
                            $data[$select] = isset($login[0][$select]) ? $login[0][$select] : '';
                        }
                    }

                }else if($params['user_type'] == 'patients'){

                    $this->load->library('encryption');
                    $this->load->library('encrypt');

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
                    $data['image']          =  (isset($login[0]['image']) && !empty($login[0]['image']))?base_url().'assets/upload/providers/'.$this->encrypt->decode($login[0]['image'], $key):'';
                    $data['profile']        =   $login[0]['profile'];
                    $data['userid']         =   $login[0]['_id']->{'$id'};

                    $wheres['patient_id']   =   new \MongoId($login[0]['_id']->{'$id'});

                    $selectd=['appointment_type','firstname','lastname','appointment_date','appointment_for','services_id','complete_address','latitude','longitude'];

                    $appointmentsList = $this->CommonModel->getCollectionData('patientAppointments',$wheres,$selectd);

                    if($appointmentsList) {
                        $data['appointments'] = $appointmentsList;
                    }
                }

                $response["status"] = 1;
                $response['message'] = 'SuccessFully Logined';
                $response["data"] = $data;


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
        if (!in_array(null, $params) && $params['user_type'] == 'patients') {
            $data = [
                "firstname" => $this->encrypt->encode($params['firstname'], $key),
                "lastname" => $this->encrypt->encode($params['lastname'], $key),
                "email" => $params['email'],
                "user_key" => $key,
                "dob" => isset($params['dob']) ? $this->encrypt->encode($params['dob'], $key) : "",
                "gender" => isset($params['gender']) ? $this->encrypt->encode($params['gender'], $key) : "",
                "mobile" => (isset($params['mobile']) && $params['mobile'] != "") ? $params['mobile'] : "",
                "social_type" => $params['social_type'],
                "social_token_id" => $params['social_token_id'],
                "device_type" => $params['device_type'],
                "device_token" => $params['device_token'],
                "device_id" => $params['device_id'],
                "longitude" => $params['longitude'],
                "latitude" => $params['latitude'],
                "profile" => 0,
                "status" => 0,
                "deleted" => '',
                "created" => strtotime(date('Y-m-d H:i:s a'))
            ];

        } else if (!in_array(null, $params) && $params['user_type'] == 'providers') {

            $data = [
                "sufix" => "Dr.",
                "firstname" => $params['firstname'],
                "lastname" => $params['lastname'],
                "email" => $params['email'],
                "dob" => isset($params['dob']) ? $params['dob'] : "",
                "gender" => isset($params['gender']) ? $params['gender'] : "",
                "mobile" => (isset($params['mobile']) && $params['mobile'] != "") ? $params['mobile'] : "",
                "social_type" => $params['social_type'],
                "social_token_id" => $params['social_token_id'],
                "device_type" => $params['device_type'],
                "device_token" => $params['device_token'],
                "device_id" => $params['device_id'],
                "longitude" => $params['longitude'],
                "latitude" => $params['latitude'],
                "profile" => 0,
                "status" => 0,
                "deleted" => '',
                "created" => strtotime(date('Y-m-d H:i:s a'))
            ];
        }

        return $data;
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
                $loginExist = $this->CommonModel->getCollectionData($params['type'] . 's', ['_id' => new MongoId($params['userid'])]);
                if ($loginExist) {
                    $servicesList = $this->CommonModel->getCollectionData('providerServices', ['status' => 1], ['name']);

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
     * @function is used to get appointments status updated both provider and patient.
     * @return true/false
     */
    public function appointment_status_update_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['patient_id', 'provider_id', 'schedule_id', 'appointment_id', 'appointment_time', 'user_type', 'appointment_status'];
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider'])) {
            try {
                $appmentWheres = ['patient_id' => new \MongoId($params['patient_id']),
                    '_id' => new \MongoId($params['appointment_id'])
                ];

                $checkValidAppment = $this->CommonModel->getCollectionData("patientAppointments", $appmentWheres, ['patient_id']);
                if ($checkValidAppment) {
                    //Valid Schedule check
                    $wheres = ['userid' => new \MongoId($params['provider_id']),
                        '_id' => new \MongoId($params['schedule_id'])
                    ];
                    $dataList = $this->CommonModel->getCollectionData("providerSchedules", $wheres, ['startdate', 'starttime', 'endtime', 'frequency']);
                    if ($dataList) {


                        $appmentStatusSearch['appointment_id'] = new \MongoId($params['appointment_id']);
                        $appmentStatusSearch['appointment_time'] = $params['appointment_time'];
                        $appmentStatusSearch['provider_id'] = new \MongoId($params['provider_id']);
                        $appmentStatusSearch['patient_id'] = new \MongoId($params['patient_id']);
                        $appmentStatusSearch['schedule_id'] = new \MongoId($dataList[0]['_id']->{'$id'});
                        $appBooked = $this->CommonModel->getCollectionData("patientAppointmentVisits", $appmentStatusSearch);
                        $response["status"] = 1;
                        if ($params['user_type'] == 'provider') {

                            $appmentStatusSearch['provider_status'] = $params['appointment_status'];
                            $response['message'] = ucwords($params['user_type']) . " booking status updated";
                        } else {
                            $appmentStatusSearch['patient_status'] = $params['appointment_status'];
                            $response['message'] = ucwords($params['user_type']) . " appointment status updated";
                        }
                        if ($appBooked)
                            $statusUpdate = $this->CommonModel->upsert('patientAppointmentVisits', $appmentStatusSearch, $appBooked[0]['_id']->{'$id'}, true);
                        else
                            $statusUpdate = $this->CommonModel->upsert('patientAppointmentVisits', $appmentStatusSearch);

                        if (empty($statusUpdate)) {
                            $response["status"] = 0;
                            $response["message"] = "Status not updated";
                        }

                    } else {
                        $response["status"] = 0;
                        $response["message"] = "You have not any schedule";
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
     * @function is used to get home type appointments list .
     * @return true(list)/false
     */
    public function appointment_list_post()
    {
        $response = [];
        $params = $this->post();
        $url = explode('-', $this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid', 'type', 'appointment_status', 'appointment_type'];
        $otherRules = ['appointment_type' => ''];
        $validation = $this->CommonModel->validation($params, $required, $otherRules);
        if (empty($validation) && in_array($params['type'], ['patient', 'provider'])) {
            try {
                $wheres = [$params['type'] . '_id' => new \MongoId($params['userid']), $params['type'] . '_status' => (int)$params['appointment_status']];
                $type = ['Walking', 'Audio', 'Video', 'Chat', 'Home'];
                $cat_type = array_intersect($params['appointment_type'], $type);
                if ($cat_type) {
                    $wheres['appointment_type'] = ['$in' => $cat_type];
                }
                $appointmentList = $this->CommonModel->getCollectionData('patientAppointments', $wheres);
                if ($appointmentList) {
                    $response['status'] = 1;
                    $response['message'] = 'Appointments List';
                    $response['data'] = $appointmentList;
                } else {
                    $response["status"] = 0;
                    $response["error_data"] = ['error_message' => 'You have not any appointment'];
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["error_data"] = ['error_message' => 'Invalid User or Account Id'];
            }
        } else {
            $response["status"] = 0;
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

    /**
     * @param null
     * @function is used to get home type appointments list .
     * @return true(list)/false
     */
    public function otp_sent_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['user_type', 'key_value', 'key_type'];
        $validation = $this->CommonModel->validation($params, $required);

        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider'])) {

            $wheres = [$params['key_type'] => $params['key_value']];

            $otpUserData = $this->CommonModel->getCollectionData($params['user_type'] . 's', $wheres, ['email', 'mobile']);
            if ($otpUserData) {
                $otp = 123456;
                $response["status"] = 1;
                $response['message'] = 'SuccessFully otp set on your ' . $params['key_type'];
                $response['data'] = ['otp' => $otp, 'userid' => $otpUserData[0]['_id']->{'$id'}];
            } else {
                $response["status"] = 0;
                $response["error_data"] = ['error_message' => 'Your invalid ' . $params['key_type']];
            }
        } else {
            $response["status"] = 0;
            $response["error_data"] = $validation;
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
                    $response['message'] = 'SuccessFully Your query submited.';
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
     * @function is used to send queery by login user
     * @return true/false
     */
    public function password_update_post()
    {
        $response = [];
        $params = $this->post();
        $url = explode('-', $this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['user_type', 'userid', 'password', 'cpassword'];
        if (isset($url[0]) && $url[0] == 'change') {
            array_push($required, 'oldpassword');
        }
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($params['user_type'], ['patient', 'provider']) && isset($url[0]) && in_array($url[0], ['change', 'reset'])) {

            try {
                if ($params['password'] == $params['cpassword']) {
                    $wheres['_id'] = new \MongoId($params['userid']);
                    $wheres['password'] = md5($params['password']);
                    $oldpasswordCheck = $this->CommonModel->getCollectionData($params['user_type'] . 's', $wheres);
                    if ($oldpasswordCheck || $url[0] == 'reset') {
                        $updatedValue['password'] = md5($params['password']);
                        $userdata = $this->CommonModel->upsert($params['user_type'] . 's', $updatedValue, $params['userid'], true);
                        if ($userdata) {
                            $response["status"] = 1;
                            $response['message'] = 'SuccessFully updated password.';
                        } else {
                            $response["status"] = 0;
                            $response["error_data"] = ['error_message' => 'Invalid User id.'];
                        }
                    } else {

                        $response["status"] = 0;
                        $response["error_data"] = ['error_message' => 'Old password does not match.'];
                    }
                } else {
                    $response["status"] = 0;
                    $response["error_data"] = ['error_message' => 'Password and confirm password does not match.'];
                }
            } catch (MongoException $ex) {

                $response["status"] = 0;
                $response["error_data"] = ['error_message' => 'Invalid User'];
            }
        } else {

            $response["status"] = 0;
            if ($validation)
                $response["error_data"] = $validation;
            else
                $response["error_data"] = ['error_message' => 'Pleaes valid url or user_type'];
        }
        $this->response($response);
    }
}
