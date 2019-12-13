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
class ApiNotification extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='notifications';
        $this->load->model('ApiNotificationModel', 'Notification');
    }



     /**
     * @param null
     * @function is used to get patient notification list and details
     * @return true/false
     */
    public function notification_get()
    {
        $response = [];
        $params = $this->get();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','user_type'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && in_array($params['user_type'], ['provider','patient'])) {
            try{
                    $user_type=$params['user_type'];
                    $to = new MongoId($params['userid']);
                    $wheres=['to'=> $to];
                    $notifiactionData = $this->CommonModel->getCollectionData($this->collection,$wheres,['created','from','status','message','type']);
                    if ($notifiactionData) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        foreach ($notifiactionData as $key => $value) {
                            if($user_type=='patient'){
                                $data=getProviderData((string)$value['from'],[],['firstname','lastname','image','sufix']);
                            }else{
                                $data=getPatientData((string)$value['from'],[],['firstname','lastname','image']);
                            }
                            $listItem['user_info']=($data['status'])?$data['data']:[];
                            unset($value['from']);
                            $listItem['message']=$value;
                            $list[]=$listItem;
                        }
                        $response['data'] = $list;
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Notification List Empty.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
                
        } else {
            $response["status"] = 0;
            if($validation){
                $response["message"] = 'Mandatory fields are required.';
                $response["error_data"] = $validation;
            }
            else
                $response["message"] = 'Invalid url or user type';
        }
        $this->response($response);
    }

     /**
     * @param null
     * @function is used to get patient notification list and details
     * @return true/false
     */
    public function notification_view_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','user_type','notification_id','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && in_array($params['user_type'], ['provider','patient'])) {
            try{
                    $user_type=$params['user_type'];
                    $id = new MongoId($params['notification_id']);
                    $wheres=['_id'=>$id];
                    $loginUser=$this->CommonModel->getCollectionData($user_type.'s',['_id'=> new \MongoId($params['userid'])]);
                    if($loginUser){
                        $notifiactionData = $this->CommonModel->getCollectionData($this->collection,$wheres);
                        if ($notifiactionData) {
                            $response["status"] = 1;
                            $response['message'] = 'success';
                            if($user_type=='patient'){
                                $data=getProviderData((string)$notifiactionData[0]['from'],[],['firstname','lastname','image','sufix']);
                            }else{
                                $data=getPatientData((string)$notifiactionData[0]['from'],[],['firstname','lastname','image']);
                            }
                            $sWheres=['timezone'=>$params['timezone'],'status'=>1];
                            $this->CommonModel->upsert($this->collection,$sWheres,$params['notification_id'],true);
                            $listItem['user_info']=($data['status'])?$data['data']:[];
                            unset($notifiactionData[0]['from'],$notifiactionData[0]['to']);
                            $listItem['message']=$notifiactionData[0];
                            $response['data'] = $listItem;
                        } else {
                            $response["status"] = 0;
                            $response["message"] = 'You have no any notification.';
                        }
                    } else {
                            $response["status"] = 0;
                            $response["message"] = 'Invalid Login User';
                        }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
                
        } else {
            $response["status"] = 0;
            if($validation){
                $response["message"] = 'Mandatory fields are required.';
                $response["error_data"] = $validation;
            }
            else
                $response["message"] = 'Invalid url or user type';
        }
        $this->response($response);
    }
     /**
     * @param null
     * @function is used to get patient notification list and details
     * @return true/false
     */
    public function notification_add_put()
    {
        $response = [];
        $params = $this->put();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','user_type','to','message','type','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && in_array($params['user_type'], ['provider','patient'])) {
            try{
                    $user_type=$params['user_type'];
                    $params['from'] = $params['userid'];
                    $wheres=['_id'=> $from];
                    $loginExists = $this->CommonModel->getCollectionData($user_type.'s',$wheres);
                    if ($loginExists) {
                        $loginExists = $this->Notification->add($params);
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Invalid User.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
                
        } else {
            $response["status"] = 0;
            if($validation)
                $response["error_data"] = $validation;
            else
                $response["error_data"] = ['error_message' => 'Invalid url or user type'];   
        }
        $this->response($response);
    }

     /**
     * @param array or object id
     * @function is used to soft delete
     * @return true/false
     */
    public function delete_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','notification_id','timezone','user_type'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && in_array($params['user_type'], ['provider','patient'])) {
             try{
                    $userWheres['_id']= new MongoId($params['userid']);
                    $exists=$this->CommonModel->getCollectionData($params['user_type'].'s',$wheres);
                    if($exists){
                        $wheres['_id']= new MongoId($params['notification_id']);
                        $dataList =  $this->CommonModel->delete($this->collection,$wheres,$params['timezone']);
                            if ($dataList) {
                                $response["status"] = 1;
                                $response['message'] = 'success';
                            } else {
                                $response["status"] = 0;
                                $response['message'] = 'Notification not deleted.';
                            }
                    }else{
                        $response["status"] = 0;
                        $response['message'] = 'Invalid User.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or Notification.';
                } 
        } else {
           $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }
}
