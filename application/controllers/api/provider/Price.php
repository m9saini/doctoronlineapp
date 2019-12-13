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
class Price extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url','form'));
    }

    /************************** Patient Authontication Apis *************************/


    /**
     * @param null
     * @function is used to set price accroding to frequency time
     * @return true/false
     */
    public function price_put()
    {
        $response = [];
        $update=false;
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $type=['Walkin','Audio','Video','Chat','Home'];
        $required = ['userid','price_value','frequency_id','type'];
        if(isset($params['price_id'])){
            array_push($required, 'price_id');
        }

        if(isset($params['type']) && $params['type']=='Walkin'){
           array_push($required, 'work_id');
        }

        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('add','edit')) && in_array($params['type'],$type)) {
            try{
                    if(isset($params['type']) && $params['type']=='Walkin'){
                           $wheres=['_id'=> new \MongoId($params['work_id']),'userid'=> new \MongoId($params['userid'])];
                           $collection='providerWorks';
                    }else{

                         $wheres=['_id'=> new \MongoId($params['userid'])];
                          $collection='providers';
                    }
                    $userExist= $this->CommonModel->getCollectionData($collection,$wheres);
                    if($userExist){
                      
                                //check Frequency 
                                 $freqExist= $this->CommonModel->getCollectionData('frequency',['_id'=> new \MongoId($params['frequency_id'])]);
                                if($freqExist){

                                         $insertData=['userid'=> new \MongoId($params['userid']),'price_value'=>(int)$params['price_value'],'frequency_id'=> new \MongoId($params['frequency_id']),'type'=>$params['type']];
                                        
                                        $searchData=['userid'=> new \MongoId($params['userid']),'frequency_id'=> new \MongoId($params['frequency_id']),'type'=>$params['type']];

                                        if(isset($params['type']) && $params['type']=='Walkin'){
                                           $insertData['work_id']= new \MongoId($params['work_id']);
                                           $searchData['work_id']= new \MongoId($params['work_id']);
                                         }
                                            $existData = $this->CommonModel->getCollectionData('providerPrices',$searchData);
                                            if($existData)
                                                $userdata = $this->CommonModel->upsert('providerPrices',$insertData,$existData[0]['_id']->{'$id'},true);
                                            else 
                                                $userdata = $this->CommonModel->upsert('providerPrices',$insertData);

                                        if ($userdata) {
                                            $response["status"] = 1;
                                            if(empty($existData))
                                                $response['message'] = 'SuccessFully added price';
                                            else 
                                                $response['message'] = 'SuccessFully updated price';
                                            $response['data']= ['price_id'=>$userdata];
                                        } else {
                                            $response["status"] = 0;
                                            $response['message'] = 'Price data not submited';
                                        }
                                    } else {
                                        $response["status"] = 0;
                                        $response['message'] = 'Invalid frequency';
                                    }
                    }else {

                        $response["status"] = 0;
                        $response["message"] = 'Invalid User or Work id';
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
        $update=false;
        $params = $this->get();
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $type=['Walkin','Audio','Video','Chat','Home'];
        $required = ['userid','price_id'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('view')) ) {
            try{
                    $searchData=['userid'=> new \MongoId($params['userid']),'_id'=> new \MongoId($params['price_id'])];
                    $existData = $this->CommonModel->getCollectionData('providerPrices',$searchData);
                    if ($existData) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data']= $existData;
                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'You have no any price data';
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

    public function list_get()
    {
        $response = [];
        $params = $this->get();
        $url=explode('-',$this->uri->segment(2));
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $type=['Walkin','Audio','Video','Chat'];
        $required = ['userid'];
        $validation = $this->CommonModel->validation($params, $required);
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('list'))) {
            try{
                    $wheres['userid']= new \MongoId($params['userid']);
                    if(isset($params['type']) && in_array($params['type'],$type))
                        $wheres['type']= $params['type'];
                    $userdata = $this->CommonModel->getCollectionData('providerPrices',$wheres,['price_value','frequency_id','type']);
                  
                    if ($userdata) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                       if(isset($params['type']) && in_array($params['type'],$type)){
                            foreach ($userdata as $key => $value) {
                                $freq_mins = $this->CommonModel->getCollectionData('frequency',['_id'=> new \MongoId($value['frequency_id']->{'$id'})]);
                                $value['show_text']= $value['price_value'].' / '.$freq_mins[0]['time_in_mins'].' mins';
                                $value['time_in_mins']= $freq_mins[0]['time_in_mins'];
                                $result[]=$value;
                            }
                        }else{
                            $Audio=$Video=$Chat=[];
                            foreach ($userdata as $key => $value) {
                                
                                $freq_mins = $this->CommonModel->getCollectionData('frequency',['_id'=> new \MongoId($value['frequency_id']->{'$id'})]);
                                $value['show_text']= $value['price_value'].' / '.$freq_mins[0]['time_in_mins'].' mins';
                                $value['time_in_mins']= $freq_mins[0]['time_in_mins'];
                                
                                if($value['type']=='Audio'){
                                    $Audio[]=$value;
                                }else if($value['type']=='Video'){
                                    $Video[]=$value;
                                }else if($value['type']=='Chat'){
                                    $Chat[]=$value;
                                }
                            }
                            $result['Audio']=$Audio;
                            $result['Video']=$Video;
                            $result['Chat']=$Chat;
                        $works = $this->CommonModel->getCollectionData('providerWorks',['userid'=> new \MongoId($params['userid'])],['name']);
                         if($works){
                                $w_search['userid']= new \MongoId($params['userid']);
                                $w_search['type']= 'Walkin';
                                foreach ($works as $key => $w_value) {
                                    $walkinData=$price_info=[];
                                    $w_search['work_id']= $w_value['_id'];
                                    $pricedata = $this->CommonModel->getCollectionData('providerPrices',$w_search,['price_value','frequency_id']);
                                    if($pricedata){
                                        foreach ($pricedata as $key => $value) {
                                            $freq_mins = $this->CommonModel->getCollectionData('frequency',['_id'=> new \MongoId($value['frequency_id']->{'$id'})]);
                                            $value['show_text']= $value['price_value'].' / '.$freq_mins[0]['time_in_mins'].' mins';
                                            $value['time_in_mins']= $freq_mins[0]['time_in_mins'];
                                            $price_info[]=$value;
                                        }
                                    }
                                    $walkinData['work_info']= $w_value;
                                    $walkinData['price_info']= $price_info;
                                    $walkinList[]=$walkinData;
                                }
                                
                                $result['Walkin']=$walkinList;
                            }else{
                                $result['Walkin']=[];
                            }
                        }
                        $response['data']= $result;

                    } else {
                        $response["status"] = 0;
                        $response['message'] = 'You have no any added price list';
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

}
