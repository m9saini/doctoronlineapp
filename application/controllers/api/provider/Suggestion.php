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
class Suggestion extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
    }

   
    /**
     * @param null
     * @function is used to add suggestion on appointment by provider 
     * @return true/false
     */
    public function upsert_put()
    {
        $response = [];
        $params = $this->put();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid','appointment_id','timezone'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation)) {
            try{
                    $searchData=['provider_id'=> new \MongoId($params['userid']),'_id'=> new \MongoId($params['appointment_id'])];
                    $app_data= $this->CommonModel->getCollectionData('patientAppointments',$searchData);
                    if($app_data){ 
                        
                        $insertData['suggestion']=$params['suggestion'];
                        $appdata = $this->CommonModel->upsert('patientAppointments',$insertData,$params['appointment_id'],true);
                          
                        if ($appdata) {
                            $response["status"] = 1;
                            $response['message'] = 'SuccessFully submited suggestion';
                        } else {
                            $response["status"] = 0;
                            $response['message'] = 'Please try again.';
                        }
                    }else{

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User or appointment';
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

}
