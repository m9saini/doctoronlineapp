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
class Account extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection="providerAccounts";
    }

    /************************** Provider Authontication Apis *************************/

    public function upsert_put()
    {
        $response = [];
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2)); 
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','bank_name','account_name','account_number','bsb','paypal_email'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)  && isset($url[1]) && in_array($url[1], array('add','edit'))) { 
            
            $parovidreExists = getProviderData($params['userid']);
            if($parovidreExists['status']){

                $id=(isset($params['account_id']))?$params['account_id']:null;
                $update=($url[1]=='edit')?true:false;
                unset($params['account_id']);
                $checkExists=array('account_number'=>$params['account_number'],'userid'=> new \MongoId($params['userid']));
                $alreadyExists=$this->CommonModel->alreadyExists($this->collection,$checkExists,$id);
                if(empty($alreadyExists)){
                    $params['userid']= new \MongoId($params['userid']);
                $dataResult = $this->CommonModel->upsert($this->collection,$params,$id,$update);
                    if ($dataResult) {
                        $response["status"] = 1;
                        $response['message'] = 'ScucessFully Data '.(($update)?'updated':'added');
                        $response['data'] = array('account_id'=>$dataResult);
                    } else {
                        $response["status"] = 0;
                        $response["error_data"] = ['error_message' => 'Data not '.(($update)?'updated':'added')];
                    }
                }else{
                    $response["status"] = 0;
                    $response["error_data"] = ['error_message' => 'Account number already exists.'];
                }
            }else{
                    $response["status"] = 0;
                    $response["error_data"] = ['error_message' => 'Provider id does not exists.'];
                }
        } else {
            $response["status"] = 1;
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

    /**
     * @param array 
     * @function is used to get Work list
     * @return true/false
     */
    public function list_get()
    {
        $response = [];
        $params = $this->get();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        if (!in_array(null, $params) && isset($params['userid'])) {
            try{
                 $wheres['userid']= new \MongoId($params['userid']);
                
                $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres);
                if ($dataList) {
                    $response["status"] = 1;
                    $response['message'] = 'success';
                    $response['data'] = $dataList;
                } else {
                    $response["status"] = 0;
                    //$response['message'] = 'error';
                    $response["error_data"] = ['error_message' => 'You have no any account.'];
                }
           }catch (MongoException $ex) {

            $response["status"] = 0;
            $response["error_data"] = ['error_message' => 'Invalid User'];
        } 
        } else {
            $response["status"] = 0;
            $response["error_data"] = ['error_message' => 'Mandatory fields are required.'];
        }
        $this->response($response);
    }

     /**
     * @param null
     * @function is used to get Work details
     * @return true/false
     */
    public function view_get()
    {
        $response = [];
        $params = $this->get();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        if (!in_array(null, $params) && isset($params['userid']) && isset($params['account_id'])) {
            try{
                    $wheres['userid']= new \MongoId($params['userid']);
                    $wheres['_id']= new MongoId($params['account_id']);
                    $dataList = $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $dataList;
                    } else {
                        $response["status"] = 0;
                        $response["error_data"] = ['error_message' => 'You have no any account.'];
                    }
                 }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["error_data"] = ['error_message' => 'Invalid User or Account Id'];
                } 
        } else {
            $response["status"] = 0;
            $response["error_data"] = ['error_message' => 'Mandatory fields are required.'];
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
        $required=['userid','account_id','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
             try{
                    $wheres['userid']= new MongoId($params['userid']);
                    $wheres['_id']= new MongoId($params['account_id']);
                  //  $schedule['userid']= new MongoId($params['userid']);
                  //  $schedule['work_id']= new MongoId($params['account_id']);
                   // $scheduleData =  $this->CommonModel->getCollectionData('providerSchedules',$schedule);
                   // if($scheduleData){
                        $dataList =  $this->CommonModel->delete($this->collection,$wheres,$params['timezone']);
                        if ($dataList) {
                            $response["status"] = 1;
                            $response['message'] = 'success';
                        } else {
                            $response["status"] = 0;
                            $response['message'] = 'Account not deleted.';
                        }
                 /*   } else{
                        $response["status"] = 0;
                        $response['message'] = 'You have not deleted.';
                    } */
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or card id.';
                } 
        } else {
           $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }
}
