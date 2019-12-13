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
class Message extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='messages';
    }



    /**
     * @param array
     * @function is used to put messages in inbox appointments price .
     * @return true/false
     */
    public function upsert_put()
    {
        $response = [];
        $params = $this->put();
        $user_type=explode('-',$this->uri->segment(1));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid','to', 'subject','message', 'timezone'];
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($user_type[1], ['provider','patient'])) {
            try {
                
               // $from= new \MongoId($params['userid']);
                //$to= new \MongoId($params['to']);
                $checkUser = $this->CommonModel->getCollectionData($user_type[1]."s",['_id'=> new \MongoId($params['userid'])]);
                if ($checkUser) { 
                    $addMessages=$this->message_store($user_type[1],$params,true);
                    $addMessages['timezone']=$params['timezone'];
                    $Updated = $this->CommonModel->upsert($this->collection, $addMessages);
                    $params['message_id']=$Updated;
                    $reply_data=$this->reply_data_store($user_type[1],$params);
                    $replyData['reply_data']=$reply_data;
                    $this->CommonModel->upsert($this->collection,$replyData,$Updated,true);
                    send_email($addMessages['to'][0]['email'],$addMessages['subject'],$addMessages['message']);
                    $response["status"] = 1;
                    $response["message"] = "SuccessFully send message";          
                } else {
                    $response["status"] = 0;
                    $response["message"] = "Invalid user";
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
     * @param array
     * @function is used to put reply messages in inbox appointments price .
     * @return true/false
     */
    public function reply_put()
    {
        $response = [];
        $params = $this->put();
        $user_type=explode('-',$this->uri->segment(1));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['message_id','userid','to','subject','message', 'timezone'];
        $validation = $this->CommonModel->validation($params, $required);
        if (empty($validation) && in_array($user_type[1], ['provider','patient'])) {
            try {
                $loginExists=$this->CommonModel->getCollectionData($user_type[1].'s',['_id'=> new \MongoId($params['userid'])]);
                if($loginExists){

                    $exists=$this->CommonModel->getCollectionData($this->collection,['_id'=> new \MongoId($params['message_id'])]);
                    if($exists){
                        
                        $addMessages=$this->message_store($user_type[1],$params);
                        if(isset($exists[0]['reply_data']) && count($exists[0]['reply_data'])>0)
                            $reply_data=array_merge($exists[0]['reply_data'],[$addMessages]);
                        else
                            $reply_data[]=$addMessages;
                        $updateData['reply_data']=$reply_data;
                        $this->CommonModel->upsert($this->collection,$updateData,$params['message_id'],true);
                        send_email($addMessages['to']['email'],$addMessages['subject'],$addMessages['message']);
                        $response["status"] = 1;
                        $response["message"] = "SuccessFully send message";
                         
                    }else{
                        $response["status"] = 0;
                        $response["message"] = "Invalid Message id";
                    }
                } else {
                        $response["status"] = 0;
                        $response["message"] = "Invalid user";
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
     * @function is used to get patient notification list and details
     * @return true/false
     */

    public function reply_data_store($user_type=NULL,$params=array()) {

        $reply_data=[];
        $exists=$this->CommonModel->getCollectionData($this->collection,['_id'=> new \MongoId($params['message_id'])]);
        if($exists){
            
            $addMessages=$this->message_store($user_type,$params);
            if(isset($exists[0]['reply_data']) && count($exists[0]['reply_data'])>0)
                $reply_data=array_merge($exists[0]['reply_data'],[$addMessages]);
            else
                $reply_data[]=$addMessages;
             
        }
        return $reply_data;
    }

    public function message_store($user_type=null,$params=array(),$front=null){

        $selected=['_id','firstname','lastname','email'];
            if($user_type=="patient"){
                $to=$this->CommonModel->getCollectionData('providers',["_id"=> new \MongoId($params['to'])],$selected);
                $from=getPatientData($params['userid'],[],$selected); 
                $addMessages['to']=($front)?$to:$to[0];
                $addMessages['from']=($front)?[$from['data']]:$from['data'];
            }else{
                $from=$this->CommonModel->getCollectionData('providers',["_id"=>new \MongoId($params['userid'])],$selected);
                $to=getPatientData($params['to'],[],$selected);
                $addMessages['to']=($front)?[$to['data']]:$to['data'];
                $addMessages['from']=($front)?$from:$from[0];
            }        
            $addMessages['subject']=$params['subject'];
            $addMessages['message']=$params['message'];
            $addMessages['created']=time();
            return $addMessages;
    }
    public function list_get()
    {
        $response = [];
        $params = $this->get();
        $user_type=explode('-',$this->uri->segment(1));
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
       if(isset($params['userid']) && in_array($url[0], ['sent','inbox']) && in_array($user_type[1], ['provider','patient'])) {
            try{
                    $field=($url[0]=='sent')?'from':'to';
                    $wheres=[$field =>['$elemMatch'=>["_id"=> new MongoId($params['userid'])]]];
                    $messagesData = $this->CommonModel->getCollectionData($this->collection,$wheres,['to','from','created','message','subject','status']);
                    if ($messagesData) {
                        $response["status"] = 1;
                        $response['message'] = 'your '.$url[0].' list';
                        $response['data'] = $messagesData; 
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'your '.$url[0].' list Empty.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
                
        } else {
            $response["status"] = 0;
            $response["message"] = 'Invalid url or user type';   
        }
        $this->response($response);
    }

    public function view_get()
    {
        $response = [];
        $params = $this->get();
        $user_type=explode('-',$this->uri->segment(1));
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
       if(isset($params['userid']) && isset($params['message_id']) && in_array($user_type[1], ['provider','patient'])) {
            try{
                    $loginExists=$this->CommonModel->getCollectionData($user_type[1].'s',['_id'=> new \MongoId($params['userid'])]);
                    if($loginExists){
                        $field=($url[0]=='sent')?'from':'to';
                        $wheres=["_id"=> new MongoId($params['message_id'])];
                        $messagesData = $this->CommonModel->getCollectionData($this->collection,$wheres,['reply_data'=>1,'_id'=>0]);
                        if ($messagesData) {
                            $response["status"] = 1;
                            $response['message'] = 'your '.$url[0].' list';
                            $response['data'] = $messagesData; 
                        } else {
                            $response["status"] = 0;
                            $response["message"] = 'your '.$url[0].' list Empty.';
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
            $response["message"] = 'Invalid url or user type';   
        }
        $this->response($response);
    }
    

}
