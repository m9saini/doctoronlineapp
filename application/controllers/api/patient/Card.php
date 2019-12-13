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
class Card extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='patientCards';
    }

    /**
     * @param array
     * @function is used to insert and update Work 
     * @return true/false
     */
    public function upsert_put()
    {
        $response = [];
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2)); 
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','firstname','lastname','card_bank','card_type','card_number','card_exp'];
         if(isset($url[1]) && in_array($url[1], array('edit'))){
            array_push($required, 'card_id');
        }
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('add','edit'))) { 
            $patientExists = getPatientData($params['userid']);
            if($patientExists['status']){
                $id=(isset($params['card_id']))?$params['card_id']:0;
                $update=($url[1]=='edit')?true:false;
                unset($params['card_id']);
                 $params['userid']= new \MongoId($params['userid']);
                $checkExists=array('card_number'=>$params['card_number'],'userid'=>$params['userid']);
                $alreadyExists=$this->CommonModel->alreadyExists($this->collection,$checkExists,$id);
                if(empty($alreadyExists)){
                    $dataResult = $this->CommonModel->upsert($this->collection,$params,$id,$update);
                    if ($dataResult) {
                        $response["status"] = 1;

                        $response['message'] = 'Card detail submitted Successfully';
                        $response['data'] = array('card_id'=> $dataResult);
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Card detail not submitted';
                    }
                }else{
                    $response["status"] = 0;
                    $response["message"] = 'Card number already exists.';
                }
            }else{
                $response["status"] = 0;
                $response["message"] = 'Patient userid does not exists.';
            }
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
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
        $required=['userid'];
          $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
            try{ 
                    $wheres['userid']= new MongoId($params['userid']);
                    $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $dataList;
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'You have no any card.';
                    }
               }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
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
     * @function is used to get Work details
     * @return true/false
     */
    public function view_get()
    {
        $response = [];
        $params = $this->get();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','card_id'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
            try{
                    $wheres['userid']= new MongoId($params['userid']);
                    $wheres['_id']= new MongoId($params['card_id']);
                    $dataList = $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $dataList;
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Card id or userid not found.';
                    }
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
        $required=['userid','card_id','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
             try{
                    $wheres['userid']= new MongoId($params['userid']);
                    $wheres['_id']= new MongoId($params['card_id']);
                    $dataList =  $this->CommonModel->delete($this->collection,$wheres,$params['timezone']);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'Card not deleted.';
                    }
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
