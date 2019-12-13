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
class AppointmentVisit extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='patientAppointments';
        $this->load->model('ApiModel', 'Api');
    }
     /**
     * @param null
     * @function is used to get appointment time list of particular data
     * @return true/false
     */
    public function select_appointment_time_post()
    {
        $response = [];
        $params = $this->post();
        $type=['Walkin','Audio','Video','Chat'];
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
         $required=['patient_id','provider_id','appointment_id','appointment_type','schedule_id','timezone'];
         $otherRules=['appointment_type'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRules); 

        if(empty($validation) && !in_array(null, $params) && is_array($params['appointment_type'])) {
            try{
                    $appmentWheres=['patient_id'=> new \MongoId($params['patient_id']),
                                    '_id'=> new \MongoId($params['appointment_id']),
                                    'appointment_type'=>['$in'=>$params['appointment_type']]
                                    ];

                    $checkValidAppment = $this->CommonModel->getCollectionData($this->collection,$appmentWheres,['patient_id','appointment_date','appointment_type','services_id','free_services_ids']);
                    if($checkValidAppment){ 
                    //Valid Schedule check
                    $wheres=['userid'=> new \MongoId($params['provider_id']),
                            'date'=>$checkValidAppment[0]['appointment_date'],
                            'type'=>['$in'=>$params['appointment_type']],
                            "_id"=> new \MongoId($params['schedule_id'])
                            ];
                    $dataList = $this->CommonModel->getCollectionData("providerSchedules",$wheres);
                    if ($dataList) { // schedule Check
                        $frq_id=(string)$dataList[0]['frequency_id'][$params['appointment_type'][0]];
                        $providerData= $this->CommonModel->getCollectionData('providers',['_id' => new \MongoId($params['provider_id'])],['sufix','firstname','lastname','gender','mobile','dob']);
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $providerData[0]['about']=isset($providerData[0]['about'])?$providerData[0]['about']:'';
                        $providerData[0]['rating']=3.5;
                       	$response['data']['speciality']= (isset($providerData[0]['speciality_ids']))?$this->Api->speciality_list($providerData[0]['speciality_ids']):[];
                       	unset($providerData[0]['speciality_ids']);
                        $response['data']['provider_info']= $providerData[0]; 
                        $params['appointment_type']=$checkValidAppment[0]['appointment_type'];
                        $response['data']['price']= $this->price_list($params,$frq_id);
                        $workswheres=['userid'=> new MongoId($params['provider_id'])];
                        $workList = $this->CommonModel->getCollectionData("providerWorks",$workswheres,['name','title','from','to','city','state','address'],['created'=> -1],2);
                        $response['data']['works']= ($workList)?$workList:[];
                        $eduList = $this->CommonModel->getCollectionData("providerEducations",$workswheres,['name'],['created'=> -1],2);
                        $response['data']['education']= ($eduList)?$eduList:[];
                        $response['data']['services']=isset($checkValidAppment[0]['services_id'])?$this->Api->services_list($checkValidAppment[0]['services_id']):[];
                        $response['data']['provider_free_services']= isset($checkValidAppment[0]['free_services_ids'])?$this->Api->services_list($checkValidAppment[0]['free_services_ids'],'Free'):[];
                        // List of appointments times slots
                        $endtime	= $dataList[0]['schedule_time'][$params['appointment_type'][0]]['endtime']; 
                        $starttime	= $dataList[0]['schedule_time'][$params['appointment_type'][0]]['starttime'];

                        $timeSlotList=[];
                        $freqData=$this->CommonModel->getCollectionData('frequency',['_id'=> new MongoId($frq_id)]);
                            if($freqData){
                                $timeSlot   =   $freqData[0]['time_in_mins'];
                                $timeSlotList=$this->schedule_slot_list($dataList[0],$starttime,$endtime,$timeSlot,$params['appointment_type'][0],$params['patient_id'],$params['appointment_id']);
                                    
                            }
                            $response['data']["appointment_time_slots"]=$timeSlotList;

                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'You have no any schedule.';
                    }
                } else {
                        $response["status"] = 0;
                        $response["message"] = 'Invalid Appointment.';
                    }
                }catch (MongoException $ex) {
                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

    public function price_list($params,$frq)
    {
        $type=['Walkin','Audio','Video','Chat'];
        $wheres['userid']= new \MongoId($params['provider_id']);
        $wheres['type']= $params['appointment_type'][0];
        $wheres['frequency_id']= new \MongoId($frq); 
        $userdata = $this->CommonModel->getCollectionData('providerPrices',$wheres);
       if ($userdata) {
            foreach ($userdata as $key => $value) {
                $freq_mins = $this->CommonModel->getCollectionData('frequency',['_id'=> new \MongoId($value['frequency_id']->{'$id'})],['time_in_mins']);

                //array_merge(['time_in_mins'=> $freq_mins[0]['time_in_mins']],$value['frequency_id']);
                $value['show_text']= $value['price_value'].' / '.$freq_mins[0]['time_in_mins'].' Minutes';
                $value['time_in_mins']=$freq_mins[0]['time_in_mins'];
                $result[]=$value;
            }
            return $result;

        } else {
            return [];
        }
    }

    public function schedule_slot_list($scheduledata=array() ,$starttime=null,$endtime=null,$timeSlot=null,$type=null,$patient_id=null,$appoint_id)
    {
        $itemList= [];
        $j=0;
        for($i=$starttime;$i< $endtime;)
        { 
                $timeSlotListAdd= [];
                $status_list=$this->Api->schedule_status_list('provider');
                if(in_array($i,$scheduledata['disable_schedule_time'][$type])){
                    //$timeSlotListAdd['appointment_status']=1;
                }
                else if(in_array($i,$scheduledata['booking_time'][$type])){
                    //$timeSlotListAdd['appointment_status']=2;
                }
                else
                {
                    $timeSlotListAdd['appointment_id']=$appoint_id;
                    $timeSlotListAdd['patient_id']=$patient_id;
                    $timeSlotListAdd['provider_id']=$scheduledata['userid']->{'$id'};
                    $timeSlotListAdd['schedule_id']=$scheduledata['_id']->{'$id'};
                    $timeSlotListAdd['appointment_time']=$i;
                    $timeSlotListAdd['appointment_status']=0;
                }

                $i=$i+($timeSlot*60);
                if(count($timeSlotListAdd)>0){
                    $itemList[]=$timeSlotListAdd;
                    $j++;
                }
                
        }
        return $itemList;
    }

    /**
     * @param array
     * @function is used to update schedule slot time Booked  from patient for appointment
     * @return true/false
     */
    public function appointment_booking_post()
    {
        $response = [];
        $type=['Walkin','Chat','Audio','Video'];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['patient_id','schedule_id','timezone','appointment_time','type','appointment_id','provider_id'];
        $otherRequired=['type'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRequired);
        if(empty($validation) &&  is_array($params['type'])) 
        { 
             $typeValue= array_intersect ($type,$params['type']);
                    if(empty($typeValue)) {
                        $response["status"] = 0;
                        $response["message"] = 'Please insert valid schedule type';
                        $this->response($response);
                    }
            $query=['patient_id'=>new \MongoId($params['patient_id']),'_id'=> new \MongoId($params['appointment_id']),'appointment_type'=>['$in'=>$params['type']],'patient_status'=>0,'provider_status'=>0];
            $checkAppointExists=$this->CommonModel->getCollectionData($this->collection,$query);
            if($checkAppointExists){
                $query=['userid'=>new \MongoId($params['provider_id']),'_id'=> new \MongoId($params['schedule_id']),'type'=>['$in'=>$params['type']]];
                $checkScheduleExists=$this->CommonModel->getCollectionData('providerSchedules',$query);
                        if($checkScheduleExists){
                            $appointment_time= (int)$params['appointment_time'];
                             array_push($checkScheduleExists[0]['booking_time'][$params['type'][0]],$appointment_time);
                             $updateSchedule['booking_time'] = array_merge($checkScheduleExists[0]['booking_time'],$checkScheduleExists[0]['booking_time']);
                            $this->CommonModel->upsert('providerSchedules',$updateSchedule,$params['schedule_id'],true);
                            $appointmentUpdated=['appointment_time'=>$appointment_time,'patient_id'=> new \MongoId($params['patient_id']),'provider_id'=> new \MongoId($params['provider_id']),'patient_status'=>1,'provider_status'=>1,'status'=>1,
                            'schedule_id'=> new \MongoId($params['schedule_id']),'frequency_id'=> $checkScheduleExists[0]['frequency_id'][$params['type'][0]] ];
                            $this->CommonModel->upsert($this->collection,$appointmentUpdated,$params['appointment_id'],true);
                            $response["status"] = 1;
                            $response["message"] = 'SuccessFully booked Appointment';
                        }else{
                                $response["status"] = 0;
                                $response["message"] = 'Schedule or User does not exists.';
                            }
            }else{
                $response["status"] = 0;
                $response["message"] = 'Appointment or User does not exists.';
            }
           
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response["message"] = 'Please insert valid schedule appointment time in array.';
        }
        $this->response($response);
    }
}
