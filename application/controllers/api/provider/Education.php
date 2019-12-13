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
class Education extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='providerEducations';
    }

    /**^
     * @param null
     * @function is used to insert and update education 
     * @return true/false
     */
    public function upsert_put()
    {
        $response = [];
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2)); 
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','name','from','to','city','state','address','degree','specialist','completed'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('add','edit'))) { 
             $parovidreExists = getProviderData($params['userid']);
            if($parovidreExists['status']){
                $id=(isset($params['education_id']))?$params['education_id']:null;
                $update=($url[1]=='edit')?true:false;
                unset($params['education_id']);
                $params['userid']= new \MongoId($params['userid']);
                $dataResult = $this->CommonModel->upsert($this->collection,$params,$id,$update);
                if ($dataResult) {
                    $response["status"] = 1;
                    $response['message'] = 'ScucessFully Data '.(($update)?'updated':'added');
                    $response['data'] = array('education_id'=>$dataResult);
                } else {
                    $response["status"] = 0;
                    $response["error_data"] = ['error_message' => 'Data not '.(($update)?'updated':'added')];
                }
            }else {
                    $response["status"] = 0;
                    $response["error_data"] = ['error_message' => 'Provider id does not exists.'];
                }
        } else {
            $response["status"] = 0;
            $response["error_data"] = $validation;
        }
        $this->response($response);

    }

     /**
     * @param null
     * @function is used to get education list
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
                        $response['error_data']=['error_message' => 'You have no any education.'];
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["data"] = ['error_message' => 'Invalid User'];
                } 
        } else {
            $response["status"] = 0;
            $response['error_data']=['error_message' => 'Mandatory fields are required.'];
        }
        $this->response($response);
    }

     /**
     * @param null
     * @function is used to get education details
     * @return true/false
     **/
    public function view_get() 
    {
        $response = [];
        $params = $this->get();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        if (!in_array(null, $params) && isset($params['userid']) && isset($params['education_id'])) {
            try{    
                    $wheres['_id']= new MongoId($params['education_id']);
                    $wheres['userid']= new \MongoId($params['userid']);
                    $dataList = $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $dataList;
                    } else {
                        $response["status"] = 0;
                        $response["error_data"] = ['error_message' => 'You have no any education.'];
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["data"] = ['error_message' => 'Invalid User or Account Id'];
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
        $required=['userid','education_id','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
             try{
                    $wheres['userid']= new MongoId($params['userid']);
                    $wheres['_id']= new MongoId($params['education_id']);
                    //$schedule['userid']= new MongoId($params['userid']);
                    //$schedule['work_id']= new MongoId($params['education_id']);
                   // $scheduleData =  $this->CommonModel->getCollectionData('providerSchedules',$schedule);
                   // if($scheduleData){
                        $dataList =  $this->CommonModel->delete($this->collection,$wheres,$params['timezone']);
                        if ($dataList) {
                            $response["status"] = 1;
                            $response['message'] = 'success';
                        } else {
                            $response["status"] = 0;
                            $response['message'] = 'Education not deleted.';
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
