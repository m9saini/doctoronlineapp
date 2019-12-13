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
class Text extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
    }

    /************************** Patient Authontication Apis *************************/


     /**
     * @param null
     * @function is used to set text accroding to type 
     * @return true/false
     */
    public function text_data_put()
    {
        $response = [];
        $update=false;
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $type=['Walkin','Audio','Video','Chat','Home'];
        $required = ['userid'];
        if(isset($url[1]) && in_array($url[1], array('edit'))){
            array_push($required, 'text_id');
        }
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && isset($url[1]) && $url[1]=='put' && is_array($params['text_data']) && isset($params['text_data'][0]['type']) && isset($params['text_data'][0]['text_value'])) {
            try{
                    $userExist= getProviderData($params['userid']);
                    if($userExist['status']){
                        foreach ($params['text_data'] as $key => $value) {
                        $insertData=['userid'=> new \MongoId($params['userid']),'text_value'=>$value['text_value'],'type'=>$value['type']];
                        $searchData=['userid'=> new \MongoId($params['userid']),'type'=>$value['type']];
                            $existData = $this->CommonModel->getCollectionData('providerScheduleText',$searchData);
                            if($existData)
                                $userdata = $this->CommonModel->upsert('providerScheduleText',$insertData,$existData[0]['_id']->{'$id'},true);
                            else 
                                $userdata = $this->CommonModel->upsert('providerScheduleText',$insertData);
                        }

                        if ($userdata) {
                            $response["status"] = 1;
                            $response['message'] = 'SuccessFully submited.';
                        } else {
                            $response["status"] = 0;
                            $response['message'] = 'Text data not submited';
                        }
                    }else{

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User';
                    } 
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or price id';
                } 
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            if($validation)
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }


    /**
     * @param null
     * @function is used to set price accroding to frequency time
     * @return true/false
     */
    public function text_put()
    {
        $response = [];
        $update=false;
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $type=['Walkin','Audio','Video','Chat','Home'];
        $required = ['userid','text_value','type'];
        if(isset($url[1]) && in_array($url[1], array('edit'))){
            array_push($required, 'text_id');
        }
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('add','edit')) && in_array($params['type'],$type)) {
            try{
                    $userExist= getProviderData($params['userid']);
                    if($userExist['status']){
                        $insertData=['userid'=> new \MongoId($params['userid']),'text_value'=>$params['text_value'],'type'=>$params['type']];
                        $searchData=['userid'=> new \MongoId($params['userid']),'type'=>$params['type']];
                            $existData = $this->CommonModel->getCollectionData('providerScheduleText',$searchData);
                            if($existData)
                                $userdata = $this->CommonModel->upsert('providerScheduleText',$insertData,$existData[0]['_id']->{'$id'},true);
                            else 
                                $userdata = $this->CommonModel->upsert('providerScheduleText',$insertData);
                        if ($userdata) {
                            $response["status"] = 1;
                            
                            if($url[1]=='add')
                                $response['message'] = 'SuccessFully Added';
                            else
                                $response['message'] = 'SuccessFully Updated';
                            $response['data']= ['text_id'=>$userdata];
                        } else {
                            $response["status"] = 0;
                            $response['message'] = 'Text data not submited';
                        }
                    }else{

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User';
                    } 
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or price id';
                } 
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            if($validation)
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }

    public function view_get()
    {
        $response = [];
        $params = $this->get();
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $type=['Walkin','Audio','Video','Chat','Home'];
        $required = ['userid','text_id'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('view')) ) {
            try{
                    $searchData=['userid'=> new \MongoId($params['userid']),'_id'=> new \MongoId($params['text_id'])];
                         $existData = $this->CommonModel->getCollectionData('providerScheduleText',$searchData);
                    if ($existData) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data']= $existData;
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'You have no any text data';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or text id';
                } 
        } else {
            $response["status"] = 0;
            $response['message'] = 'Mandatory fields are required.';
            if($validation)
            $response['error_data'] = $validation;
        }
        $this->response($response);
    }


    public function list_get()
    {
        $response = [];
        $params = $this->get();
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $type=['Walkin','Audio','Video','Chat','Home'];
        $required = ['userid'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('list'))) {
            try{
                    $wheres['userid']= new \MongoId($params['userid']);
                    if(isset($params['type']) && in_array($params['type'],$type))
                        $wheres['type']= $params['type'];
                    $userdata = $this->CommonModel->getCollectionData('providerScheduleText',$wheres,['text_value','type']);
                  
                    if ($userdata) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data']= $userdata;
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'You have no any text data';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or text id';
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
