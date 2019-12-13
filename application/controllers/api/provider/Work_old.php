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
class Work extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='providerWorks';
    }

    /**
     * @param array
     * @function is used to insert and update Work 
     * @return true/false
     */
    public function upsert_post()
    {
        $response = [];
        $params = $this->post();
        $url=explode('-',$this->uri->segment(2)); 
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','name','title','from','to','city','state','address','facility_takecare','is_currently','total_work'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('add','edit'))) { 
            $parovidreExists = getProviderData($params['userid']);
            if($parovidreExists['status']){
                try{
                    $id=null;
                    $update=($url[1]=='edit')?true:false;
                    
                    $params['userid'] = new \MongoId($params['userid']);

                    $image=$this->imageUpload($_FILES);
                    if($url[1]=='edit' && isset($params['work_id'])){
                        $id=$params['work_id'];
                        $wheres['userid'] = new \MongoId($params['userid']);
                        $wheres['_id']= new MongoId($params['work_id']);
                        $workImages = $this->CommonModel->getCollectionData($this->collection,$wheres);
                        if(!empty($workImages) && isset($workImages[0]['image']) && count($workImages[0]['image'])>0){
                            $params['image']=(count($image)>0)?array_merge($workImages[0]['image'],$image):$workImages[0]['image'];
                        }else{
                            $params['image']=(count($image)>0)?$image:[];
                        }
                    }else{
                    $params['image']=(count($image)>0)?$image:[];
                    }
                    unset($params['work_id']);
                    $dataResult = $this->CommonModel->upsert($this->collection,$params,$id,$update);
                    if ($dataResult) {
                        $response["status"] = 1;
                        $response['message'] = 'ScucessFully Data '.(($update)?'updated':'added');
                        $response['data'] = array('work_id'=>$dataResult);
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Data not '.(($update)?'updated':'added');
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid Work id';
                } 
            }else{
                    $response["status"] = 0;
                    $response["message"] = 'Provider id does not exists.';
                }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
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
                $wheres['userid'] = new \MongoId($params['userid']);
                $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres);
                if ($dataList) {
                    $response["status"] = 1;
                    $response['message'] = 'success';
                    $response['data']['base_url']= base_url().'assets/upload/providers/works/';
                    $response['data']['work_list']= $dataList;
                } else {
                    $response["status"] = 0;
                    $response["message"] = 'You have no any Work .';
                }
            }catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = 'Invalid Valid User';
            } 
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
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
        if (!in_array(null, $params) && isset($params['userid']) && isset($params['work_id'])) {
            try{
                    $wheres['userid'] = new \MongoId($params['userid']);
                    $wheres['_id']= new MongoId($params['work_id']);
                    $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres);
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $dataList[0]['base_url']=base_url().'assets/upload/providers/works/';
                        $response['data'] = $dataList;
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'You have no any Work.';
                    }
                }catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = 'Invalid Valid work_id or userid';
            } 
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
        }
        $this->response($response);
    }
    

    public function imageUpload($files)
    {
         /*      * *** Profile Image Update *******      */
         $imageArray=[];
        if (!empty($files) && is_array($files) && isset($_FILES['photo']['name'][0]) ) {
            $this->load->library('upload');
            $files['photo']['name'][0];
            $img_count= count($files['photo']['name']); 
            //pr($value);
            for($i=0;$i<$img_count;$i++) {
                $image_info=$config=[];
               
                $config['upload_path'] = './assets/upload/providers/works/';
                $config['allowed_types'] = 'jpg|jpeg|png';
                $config['max_size'] = '1000000000000000';
                $config['overwrite'] = FALSE;
                $title = date('YmdHis');
                $rand = rand(100000, 999999);
                $ext = pathinfo($files['photo']['name'][$i], PATHINFO_EXTENSION);
                $fileName = $rand . $title . '.' . $ext;
                $image = $fileName;
                $config['file_name'] = $fileName;
                $_FILES['photo']['name']= $files['photo']['name'][$i];
                $_FILES['photo']['type']= $files['photo']['type'][$i];
                $_FILES['photo']['tmp_name']= $files['photo']['tmp_name'][$i];
                $_FILES['photo']['error']= $files['photo']['error'][$i];
                $_FILES['photo']['size']= $files['photo']['size'][$i]; 
                $this->upload->initialize($config);
               // $this->upload->do_upload(); 
                //$this->upload->data(); 
                $image = $fileName;
                /*** Image resize ****/
                if($this->upload->do_upload('photo')){
                
                $this->upload->data();
                $this->load->library('image_lib');
                $resize['image_library'] = 'gd2';
                $resize['source_image'] = './assets/upload/providers/works/' . $image;

                $tnumb = $rand . $title . '_thumb.' . $ext;
                $resize['new_image'] = "./assets/upload/providers/works/$tnumb";
                $resize['maintain_ratio'] = TRUE;
                $resize['width'] = 150;
                $resize['height'] = 150;
                $this->image_lib->initialize($resize);
               
                if($this->image_lib->resize()){
                    $image_info['thumb']=$tnumb;
                }else{
                    $image_info['thumb']="";
                }
                $image_info['img_extension'] = $ext;
                $image_info['name']=$image;
                $image_info['path']='/assets/upload/providers/works/';
                $imageArray[] = $image_info;
                
            }
                
            }
            return $imageArray;

        }else{
            return $imageArray;
        }
                    /* ****** End Profile Imahe Upoload Section *****     */
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
        $required=['userid','work_id','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
             try{
                    $wheres['userid']= new MongoId($params['userid']);
                    $wheres['_id']= new MongoId($params['work_id']);
                    $schedule['userid']= new MongoId($params['userid']);
                    $schedule['work_id']= new MongoId($params['work_id']);
                    $scheduleData =  $this->CommonModel->getCollectionData('providerSchedules',$schedule);
                    if($scheduleData){

                        $response["status"] = 0;
                        $response['message'] = 'You have not deleted.';
                        
                    } else{
                        $dataList =  $this->CommonModel->delete($this->collection,$wheres,$params['timezone']);
                        if ($dataList) {
                            $response["status"] = 1;
                            $response['message'] = 'success';
                        } else {
                            $response["status"] = 0;
                            $response['message'] = 'Work not deleted.';
                        }
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
    public function image_delete_post()
    {
        $response = [];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','work_id','timezone','image_name'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation)) {
            try{
                    $wheres['userid']= new \MongoId($params['userid']);
                    $wheres['_id']= new \MongoId($params['work_id']);
                    $dataList =  $this->CommonModel->getCollectionData($this->collection,$wheres,['image']);
                     if ($dataList) {
                        if(isset($dataList[0]['image']) && count($dataList[0]['image'])){
                          
                            $key = array_search($params['image_name'], array_column($dataList[0]['image'], 'name'));
                            if($key !== false){ 
                                    unset($dataList[0]['image'][$key]);
                                    $updated['image']=array_merge($dataList[0]['image'],[]);
                                    $this->CommonModel->upsert($this->collection,$updated,$params['work_id'],true);
                                    $response["status"] = 1;
                                    $response['message'] = 'SuccessFully image deleted';
                                }else{
                                    $response["status"] = 0;
                                    $response['message'] = 'Image does not exists.';    
                                }
                            
                        }else{
                            $response["status"] = 0;
                            $response['message'] = 'Image does not exists.';
                        }
                    } else {
                        $response["status"] = 0;
                        $response["message"]='Invalid Work';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                } 
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
        }
        $this->response($response);
    }
}
