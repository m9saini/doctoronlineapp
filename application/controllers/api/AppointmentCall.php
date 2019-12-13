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
class AppointmentCall extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='patientAppointments';
        $this->load->model('ApiModel', 'Api');
    }

    /**
     * @param array 
     * @function is used to insert and update Work 
     * @return true/false
     */

    
    public function start_post()
    {
        $response = $Objectids =[];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone','user_type'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && in_array($params['user_type'], ['provider','patient']) )
          { 	
          	try{     
                    $user_type=$params['user_type'];
                    $app_search=[$user_type.'_id'=> new \MongoId($params['userid']),'_id'=> new \MongoId($params['appointment_id']),'appointment_type'=>['$in'=>['Audio','Video','Chat','Walkin']],'patient_status'=>1,'provider_status'=>1];
                    $existApp=$this->CommonModel->getCollectionData($this->collection,$app_search);
		            if($existApp){

                        //if(strtotime($existApp[0]['appointment_time']))
                        $patient_id=(string)$existApp[0]['patient_id'];
                        $provider_id=(string)$existApp[0]['provider_id'];
                        $frequency=$this->CommonModel->getCollectionData('frequency',['_id'=>$existApp[0]['frequency_id']],['time_in_mins']);
                        $app_info['frequency']=($frequency)?$frequency[0]['time_in_mins']:0;
                        $response["status"] = 1;
                        $response["message"] = 'Appointment information.';
                        if(isset($existApp[0]['call_details']['app_starttime']))
                        {
                            $app_start=$existApp[0]['call_details']['app_starttime'];
                        }else{
                            $app_start=$existApp[0]['appointment_time'];
                        }
                        if(isset($existApp[0]['call_details']['total_time']))
                        {
                            $total_time=$existApp[0]['call_details']['total_time'];
                        }else{
                            $total_time=$app_info['frequency'];
                        }
                        if(isset($existApp[0]['call_details']['remaining']))
                        {
                            $remaining=$existApp[0]['call_details']['remaining'];
                        }else{
                            date_default_timezone_set($params['timezone']);
                            $current_time= strtotime(date('Y-m-d H:i:s'));
                            date_default_timezone_set("UTC");
                            $current_time= strtotime(date('Y-m-d H:i:s',$current_time));
                            if($current_time<=$existApp[0]['appointment_time']){
                                $duration=($existApp[0]['appointment_time']-$current_time);
                                $duration=($duration/60);
                                $remaining=$duration*60;
                            }else{
                                $remaining=0;
                            }
                        }
                        if(isset($existApp[0]['call_details']['frequency']))
                        {
                            $first_time_save_frequency=$existApp[0]['call_details']['frequency'];
                        }else{
                            $first_time_save_frequency=$app_info['frequency'];
                        }
                        $selected=['firstname','lastname','email','image','quickblox_info'];
                        $patient_info= getPatientData($patient_id,[],$selected);
                        $app_info['patient_info']=$patient_info['data'];
                        $provider_info=getProviderData($provider_id,[],$selected);
                        $app_info['parovider_info']=$provider_info['data'];
                        $app_info['patient_id']= $patient_id;
                        $app_info['provider_id']= $provider_id;
                        $app_info['appointment_time']=$existApp[0]['appointment_time'];
                        $app_info['remaining_time']=$remaining;
                        $app_info['total_time']=$total_time;
                        $app_info['start_btn_active_before_min']= APPOINTMENT_START_BUTTON_BEFORE_ACTIVE_MIN;
                        $response['data']['appointment_info']=$app_info;
                        

                        $save_data=['call_details'=>['app_starttime'=>$app_start,'total_time'=>$total_time,'frequency'=>$first_time_save_frequency]];
                       $this->CommonModel->upsert($this->collection,$save_data,$params['appointment_id'],true);
                    }else{
                        $response["status"] = 0;
                        $response["message"] = 'Your appointment does not exists.';
                    }
		         }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'You no any appointment';
                } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response['message']='Appointment type Invalid.';
        }
        $this->response($response);
    }

    public function disconnect_post()
    {
        $response = $Objectids =[];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone','user_type'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && in_array($params['user_type'], ['provider','patient']))
          {     
            try{     
                   $user_type=$params['user_type'];
                    $app_search=[$user_type.'_id'=> new \MongoId($params['userid']),'_id'=> new \MongoId($params['appointment_id']),'appointment_type'=>['$in'=>['Audio','Video','Chat','Walkin']],'patient_status'=>1,'provider_status'=>1];
                    $existApp=$this->CommonModel->getCollectionData($this->collection,$app_search);
                    if($existApp){
                        $response["status"] = 1;
                        $response["message"] = 'Call Disconnect.';
                        $call_summary=$this->save_summary($existApp,$params);
                       $this->CommonModel->upsert($this->collection,$call_summary,$params['appointment_id'],true);                         

                    }else{
                        $response["status"] = 0;
                        $response["message"] = 'Your appointment does not exists.';
                    }
                 }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'You no any appointment';
                } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response['message']='Appointment type Invalid.';
        }
        $this->response($response);
    }
  
    public function extend_post()
    {
        $response = $Objectids =[];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone','user_type'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && in_array($params['user_type'], ['provider','patient']))
          {     
            try{     
                   $user_type=$params['user_type'];
                    $app_search=[$user_type.'_id'=> new \MongoId($params['userid']),'_id'=> new \MongoId($params['appointment_id']),'appointment_type'=>['$in'=>['Audio','Video','Chat','Walkin']],'patient_status'=>1,'provider_status'=>1];
                    $existApp=$this->CommonModel->getCollectionData($this->collection,$app_search);
                    if($existApp){
                        $response["status"] = 1;
                        $response["message"] = 'Call Extend.';
                        date_default_timezone_set($params['timezone']);
                        $created= strtotime(date('Y-m-d H:i:s'));
                        $frequency=$this->CommonModel->getCollectionData('frequency',['_id'=>$existApp[0]['frequency_id']],['time_in_mins']);
                        $frequency=($frequency)?$frequency[0]['time_in_mins']:0;
                        date_default_timezone_set("UTC");
                        $extened['time']=$frequency;
                        $extened['created']= strtotime(date('Y-m-d H:i:s',$created));
                        $extened['extend_by']=$user_type;
                        if(isset($existApp[0]['call_details']['total_time'])){
                            $extend_data['call_details']['total_time']=$existApp[0]['call_details']['total_time']+$frequency;
                        }else{
                            $extend_data['call_details']['total_time']= $frequency*2;
                        }
                            if(isset($existApp[0]['call_details']['extened'])){
                                $extend_data['call_details']['extened']=array_merge($existApp[0]['call_details']['extened'],[$extened]);
                            }else{
                                $extend_data['call_details']['extened'][]=$extened;
                            }

                             if(isset($existApp[0]['call_details']['summary']))
                                $extend_data['call_details']['summary']=$existApp[0]['call_details']['summary'];
                            if(isset($existApp[0]['call_details']['remaining']))
                                $extend_data['call_details']['remaining']=$existApp[0]['call_details']['remaining']+$frequency;
                            if(isset($existApp[0]['call_details']['frequency']))
                                $extend_data['call_details']['frequency']=$existApp[0]['call_details']['frequency'];
                            if(isset($existApp[0]['call_details']['app_starttime']))
                                $extend_data['call_details']['app_starttime']=$existApp[0]['call_details']['app_starttime'];
                        $this->CommonModel->upsert($this->collection,$extend_data,$params['appointment_id'],true);

                    }else{
                        $response["status"] = 0;
                        $response["message"] = 'Your appointment does not exists.';
                    }
                 }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'You no any appointment';
                } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response['message']='Appointment type Invalid.';
        }
        $this->response($response);
    }
   
    public function completed_post()
    {
        $response = $Objectids =[];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone','user_type'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && in_array($params['user_type'], ['provider','patient']))
          {     
            try{     
                    $user_type=$params['user_type'];
                    $app_search=[$user_type.'_id'=> new \MongoId($params['userid']),'_id'=> new \MongoId($params['appointment_id']),'appointment_type'=>['$in'=>['Audio','Video','Chat','Walkin']],'patient_status'=>1,'provider_status'=>1];
                    $existApp=$this->CommonModel->getCollectionData($this->collection,$app_search);
                    if($existApp){
                        $response["status"] = 1;
                        $response["message"] = 'Call Completed.';
                        $call_summary=$this->save_summary($existApp,$params);
                        $save_data=['patient_status'=>2,'provider_status'=>2,'call_details'=>$call_summary['call_details']];
                       $this->CommonModel->upsert($this->collection,$save_data,$params['appointment_id'],true);                         

                    }else{
                        $response["status"] = 0;
                        $response["message"] = 'Your appointment does not exists.';
                    }
                 }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'You no any appointment';
                } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response['message']='Appointment type Invalid.';
        }
        $this->response($response);
    }

    public function save_summary($existApp=array(),$params=array()){
        $user_type=$params['user_type'];
        $frequency=$this->CommonModel->getCollectionData('frequency',['_id'=>$existApp[0]['frequency_id']],['time_in_mins']);
        $frequency=($frequency)?$frequency[0]['time_in_mins']:0;
        date_default_timezone_set($params['timezone']);
        $starttime= strtotime(date('Y-m-d H:i:s',$existApp[0]['call_details']['app_starttime']));
        $endtime= strtotime(date('Y-m-d H:i:s'));
        $duration=($endtime-$starttime);
        $duration=($duration/60);
        $duration=($duration*60);
        date_default_timezone_set("UTC");
        $summary['starttime']= strtotime(date('Y-m-d H:i:s',$starttime));
        $summary['endtime']= strtotime(date('Y-m-d H:i:s',$endtime));
        $summary['duration_in_second']=$duration;
        $summary['disconnect_by']=$user_type;
        if(isset($existApp[0]['call_details']['summary'])){
            $call_summary['call_details']['summary']=array_merge($existApp[0]['call_details']['summary'],[$summary]);
        }else{
            $call_summary['call_details']['summary'][]=$summary;
        }

        if(isset($existApp[0]['call_details']['remaining']))
        {
            $remaining=$existApp[0]['call_details']['remaining']-$duration;
        }else{
            date_default_timezone_set($params['timezone']);
            $current_time= strtotime(date('Y-m-d H:i:s'));
            if($current_time<=$existApp[0]['appointment_time']){
                $duration=($existApp[0]['appointment_time']-$current_time);
                $duration=($duration/60);
                $remaining=$duration*60;
            }else{
                $remaining=0;
            }
        }
        $call_summary['call_details']['remaining']=$remaining;
        if(isset($existApp[0]['call_details']['extened']))
            $call_summary['call_details']['extened']=$existApp[0]['call_details']['extened'];
        if(isset($existApp[0]['call_details']['total_time']))
            $call_summary['call_details']['total_time']=$existApp[0]['call_details']['total_time'];
        if(isset($existApp[0]['call_details']['frequency']))
            $call_summary['call_details']['frequency']=$existApp[0]['call_details']['frequency'];
        $call_summary['call_details']['app_starttime']=$summary['endtime'];
        return $call_summary;
    }

    public function server_time_get(){

        $response = $Objectids =[];
        $params = $this->get();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation))
          { 
            date_default_timezone_set($params['timezone']);
            $current_time= strtotime(date('Y-m-d H:i:s'));
            date_default_timezone_set("UTC");
            $current_time= strtotime(date('Y-m-d H:i:s',$current_time));
            $response["status"] = 1;
            $response['message']='Server Current Time';
            $response['data']=['server_current_time_in_UTC'=>$current_time];
        }else{
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
        }
        $this->response($response);
        
    }
    
    
}
