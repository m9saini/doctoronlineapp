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
class Speciality extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
    }

    /************************** Patient Authontication Apis *************************/


    /**
     * @param null
     * @function is used to set price accroding to frequency time
     * @return true/false
     */
    public function speciality_put()
    {
        $response = [];
        $update=false;
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid','speciality_ids'];
        $other=['speciality_ids'=>''];
        $validation = $this->CommonModel->validation($params, $required,$other);
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('add','edit','put')) && is_array($params['speciality_ids'])) {
            try{
                    $search_ids=[];
                    foreach ($params['speciality_ids'] as $key => $splty_id) {
                        $search_ids[]= new \MongoId($splty_id);
                    }
                    $wheres=['_id'=> new \MongoId($params['userid'])];
                    $userExist= $this->CommonModel->getCollectionData('providers',$wheres);
                    if($userExist){

                        if(isset($userExist[0]['speciality_ids']) && count($userExist[0]['speciality_ids'])>0)
                            $response['message'] = 'Successfully Updated';
                        else
                            $response['message'] = 'Successfully Added';
                         $insertData['speciality_ids']= $search_ids;
                         $userdata = $this->CommonModel->upsert('providers',$insertData,$params['userid'],true);
                          if($userdata){
                                $response["status"] = 1;
                            } else {
                                $response["status"] = 0;
                                $response['message'] = 'Speciality data not submited';
                            }
                        }else {

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid Speciality ids';
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
        $response = $result = [];
        $params = $this->get();
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required = ['userid'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && isset($url[1]) && $url[1]=='list') {
            try{
                    $wheres['_id']= new \MongoId($params['userid']);
                    $userdata = $this->CommonModel->getCollectionData('providers',$wheres);
                  
                    if ($userdata) {
                        if(isset($userdata[0]['speciality_ids']) && count($userdata[0]['speciality_ids'])>0)
                        {
                            foreach ($userdata[0]['speciality_ids'] as $key => $value) {

                                $wheres['_id']= $value;
                                $listData= $this->CommonModel->getCollectionData('speciality',$wheres,['name']);
                                if($listData){ $result[]=$listData[0]; }
                            }
                        }
                        if(count($result)>0)
                        {
                            $response["status"] = 1;
                            $response['message'] = 'success';
                            $response['data']= $result;
                        }else{
                            $response["status"] = 0;
                            $response['message'] = 'You have no any speciality';
                        }
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'Invalid User';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid user or speciality';
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
