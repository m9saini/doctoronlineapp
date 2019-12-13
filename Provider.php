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
class Provider extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->model('api/provider/ProviderModel', 'Api');
    }

    /************************** Patient Authontication Apis *************************/

    /**
     * @param null
     * @function is used to provider registrations
     * @return array
     */
    public function signup_put()
    {

        try {
            $response = [];
            $params = $this->put();
            /*         * ******* CEHCK MANDATORY FIELDS ************* */
            $required = ['firstname', 'lastname', 'email', 'password', 'cpassword', 'dob', 'mobile', 'device_id', 'device_token', 'device_type', 'latitude', 'longitude'];
            $validation = $this->CommonModel->validation($params, $required);
            if (empty($validation)) {

                $checkEmail = $this->CommonModel->alreadyExists('providers', array('email' => $params['email']));

                /** ******* CEHCK EMAIL ************* */
                if (empty($checkEmail) && $params['email'] != '') {

                    $checkMobile = $this->CommonModel->alreadyExists('providers', array('mobile' => $params['mobile']));
                    if (empty($checkMobile) && $params['mobile'] != '') {
                        /*             * ******* CEHCK PASSWORD ************* */
                        if ($params['password'] == $params['cpassword']) {
                            $signup = $this->Api->signup($params);
                            if ($signup) {
                                $response["status"] = 1;
                                $response['message'] = 'Your account has been successfully created. Check your email for further instructions.';
                            } else {
                                $response["status"] = 0;
                                $response['message'] = 'Please try again';
                                $response["error_data"] = ['error_message' => 'Please try again'];
                            }
                        } else {
                            $response["status"] = 0;
                            $response['message'] = 'Confirm password does not match.';
                            $response["error_data"] = ['error_message' => 'Confirm password does not match.'];
                        }
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'This mobile number has already been taken.';
                        $response["error_data"] = ['error_message' => 'This mobile number has already been taken.'];

                    }
                } else {

                    $response["status"] = 0;
                    $response['message'] = 'This email address has already been taken.';
                    $response["error_data"] = ['error_message' => 'This email address has already been taken.'];
                }
            } else {
                $response["status"] = 0;
                $response['message'] = 'Mandatory fields are required.';
                $response["error_data"] = $validation;

            }
        } catch (Exception $ex) {

            $response["status"] = 0;
            $response['message'] = 'Invalid Object Id.';
            $response["error_data"] = ['error_message' => $ex->getMessage()];
        }
        $this->response($response);
    }

    /**
     * @param null
     * @function is used to provider login
     * @return array
     */
    public function login_post()
    {
        try {
            $response = $data = [];
            $params = $this->post();
            $required = ['email', 'password', 'device_id', 'device_token', 'device_type', 'latitude', 'longitude'];
            $selected = ['sufix', 'firstname', 'lastname', 'email', 'dob', 'country_code', 'mobile', 'latitude', 'longitude', 'gender', 'city', 'state', 'zipcode', 'street_add', 'image', 'profile', 'status', 'social_type', 'social_token_id', 'device_type', 'device_token', 'device_id', 'language'];
            $validation = $this->CommonModel->validation($params, $required);
            if (empty($validation)) {
                $login = $this->Api->login($params);

                if ($login) {

                    $data["userid"] = $login[0]['_id']->{'$id'};

                    foreach ($selected as $select) {

                        if ($select == 'image') {

                            $data["image"] = (isset($login[0]['image']) && !empty($login[0]['image'])) ? base_url() . 'assets/upload/providers/' . $login[0]['image'] : '';

                        } else {
                            $data[$select] = isset($login[0][$select]) ? $login[0][$select] : '';
                        }
                    }

                    $response["status"] = 1;
                    $response['message'] = 'Success.';
                    $response['data'] = $data;

                } else {
                    $response["status"] = 0;
                    $response['message'] = 'Incorrect email or password or your account not verify.';
                }

            } else {

                $response["status"] = 0;
                $response['message'] = 'Mandatory fields are required.';
                $response["error_data"] = $validation;
            }
        } catch (Exception $ex) {
            $response["status"] = 0;
            $response['message'] = 'Invalid Object Id';
            $response["error_data"] = ['error_message' => $ex->getMessage()];

        }

        $this->response($response);
    }

    /**
     * @param null
     * @function is used to get provider profile data
     * @return array
     */
    public function profile_view_get()
    {
        try {
            $response = $userdata = $data = [];
            $params = $this->get();
            /*         * ******* CEHCK MANDATORY FIELDS ************* */
            $required = ['userid'];
            $validation = $this->CommonModel->validation($params, $required);
            if (empty($validation)) {
                $selected = ['sufix', 'firstname', 'lastname', 'email', 'dob', 'country_code', 'mobile', 'latitude', 'longitude', 'gender', 'city', 'state', 'zipcode', 'street_add', 'image', 'profile', 'status', 'language'];
                $wheres = ['_id' => new MongoId($params['userid'])];
                $userdata = $this->CommonModel->getCollectionData('providers', $wheres, $selected);
                if ($userdata) {

                    $data["userid"] = $userdata[0]['_id']->{'$id'};

                    foreach ($selected as $select) {
                        if ($select == 'image') {
                            $data["image"] = (isset($userdata[0]['image']) && !empty($userdata[0]['image'])) ? base_url() . 'assets/upload/providers/' . $userdata[0]['image'] : '';
                        } else {
                            $data[$select] = isset($userdata[0][$select]) ? $userdata[0][$select] : '';
                        }
                    }

                    $response["status"] = 1;
                    $response['message'] = 'success.';
                    $response['data'] = $data;

                } else {

                    $response["status"] = 0;
                    $response['message'] = 'User id not found';
                    $response["error_data"] = ['error_message' => ''];
                }
            } else {
                $response["status"] = 0;
                $response['message'] = 'Mandatory fields are required.';
                $response["error_data"] = $validation;
            }
        } catch (Exception $ex) {

            $response["status"] = 0;
            $response['message'] = 'Invalid Object Id.';
            $response["error_data"] = ['error_message' => $ex->getMessage()];
        }

        $this->response($response);
    }

    /**
     * @param null
     * @function is used to provider edit profile
     * @return array
     */
    public function profile_edit_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','firstname','lastname','email','dob','country_code','mobile'];
        $validation = $this->CommonModel->validation($params,$required);
        if (empty($validation)) {
            $checkEmail = $this->CommonModel->alreadyExists('providers', array('email' => $params['email']), $params['userid']);
            /** ******* CEHCK EMAIL ************* */
            if (empty($checkEmail) && $params['email'] != '') {

                $checkMobile = $this->CommonModel->alreadyExists('providers', array('mobile' => $params['mobile']), $params['userid']);

                if (empty($checkMobile) && $params['email'] != '') {
                    $img_error = 1;
                    /*      * *** Profile Image Update *******      */
                    $image = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';

                    if (!empty($image)) {

                        $this->load->library('upload');
                        $config['upload_path'] = './assets/upload/providers/';
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
                            $resize['source_image'] = './assets/upload/providers/' . $image;
                            $tnumb = $rand . $title . '_thumb.' . $ext;
                            $resize['new_image'] = "./assets/upload/providers/$tnumb";
                            //$resize['maintain_ratio'] = TRUE;
                            $resize['width'] = 150;
                            $resize['height'] = 150;
                            $this->image_lib->initialize($resize);
                            $this->image_lib->resize();
                            $params['img_extension'] = $ext;
                            $params['image'] = $image;
                        } else {
                            $img_error = 0;
                        }

                    }
                    /* ****** End Profile Imahe Upoload Section *****     */
                    if ($img_error == 1) {
                        $update_profile = $this->Api->update_profile($params);
                        if ($update_profile) {
                            $response["status"] = 1;
                            $response['message'] = 'Your account has been successfully updated. Check your email for further change email address.';
                            $response['data'] = $update_profile;
                        } else {
                            $response["status"] = 0;
                            $response['message'] = 'Invalid Object Id.';
                        }
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'Please upload valid image.';
                    }

                } else {

                    $response["status"] = 0;
                    $response['message'] = 'This mobile number has already been taken.';

                }
            } else {

                $response["status"] = 0;
                $response['message'] = 'This email address has already been taken.';

            }

        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }

        $this->response($response);
    }

    /**
     * @param null
     * @function is used to get provider dashboard data
     * @return array
     */
    public function dashboard_get()
    {

        $response = [];
        $params = $this->get();
        /*         * ******* CEHCK VALID OBJECT ID************* */
        $userdata = $this->Api->getDashboardData($params['userid']);
        if ($userdata) {
            $response["status"] = 1;
            $response['message'] = 'success';
            $response['data'] = $userdata;
        } else {
            $response["status"] = 0;
            $response['message'] = 'Invalid User Id.';
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
        $required = ['userid'];
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation)) {
            $userdata = '';
            /**** Email send ******/
            if ($userdata) {
                $response["status"] = 1;
                $response['message'] = 'success';
            } else {
                $response["status"] = 0;
                $response['message'] = 'User Query not submited.';
            }
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }

    /**
     * @param null
     * @function is used to update provider notification settings
     * @return true/false
     */
    public function provider_setting_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid'];
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation)) {
            $patientSettingData = $this->Api->providerSetting($params);
            if ($patientSettingData) {
                $response["status"] = 1;
                $response['message'] = 'success';
            } else {
                $response["status"] = 0;
                $response['message'] = 'Data Not Updated';
            }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
        }
        $this->response($response);
    }


}
