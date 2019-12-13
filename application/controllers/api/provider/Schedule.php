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
class Schedule extends REST_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='providerSchedules';
        $this->load->model('ApiModel', 'Api');
    }

    /**
     * @param array
     * @function is used to insert schedule
     * @return true/false
     */
    public function insert_put()
    {
        $response = $schedule_time = $disable_schedule_time= [];
        $dataResult='';
        $type=['Audio','Video','Chat'];
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2)); //pr($params);
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','startdate','enddate','starttime','endtime','includer','type','frequency_id','timezone'];
        $otherRequired=['type'=>'','includer'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRequired);
        if(empty($validation) && is_array($params['type']) && is_array($params['includer']) && count($params['includer'])>0) {

        try {
                $typeValue= array_intersect ($type,$params['type']);
                if(empty($typeValue)) {
                    $response["status"] = 0;
                    $response["message"] = 'Please insert valid schedule type';
                    $this->response($response);
                }
                
                $freqData=$this->CommonModel->getCollectionData('frequency',['_id'=> new MongoId($params['frequency_id'])]);
                if($freqData){

                    date_default_timezone_set($params['timezone']);
                    $startdate  =   strtotime(date('Y-m-d',$params['startdate']));
                    $enddate    =   strtotime(date('Y-m-d',$params['enddate']).' + 1 days');
                   $starttime  =   strtotime(date('Y-m-d').' '.$params['starttime']); 
                    $endtime    =   strtotime(date('Y-m-d').' '.$params['endtime']);  
                    $timeSlot   =   $freqData[0]['time_in_mins'];
                   $newStarttime   =   $starttime + ($timeSlot * 60); 
                    if($enddate  >= $startdate && $endtime  >= $newStarttime ){
                        $parovidreExists = getProviderData($params['userid']);
                        if($parovidreExists['status']){
                            $dateStart=date('Y-m-d',$startdate);
                            $dateEnd=date('Y-m-d',$enddate);
                                // schedule array vlaues 
                                $addSchedule['userid']= new \MongoId($params['userid']);
                                $addSchedule['type']            =   array_values($typeValue);
                                foreach ($addSchedule['type'] as $key => $value) {
                                    $disable_schedule_time[$value]=[];
                                    $booking_time[$value]=[];
                                    $frequency_id[$value]= new \MongoId($params['frequency_id']);

                                }
                                $addSchedule['disable_schedule_time'] = $disable_schedule_time;
                                $addSchedule['booking_time']    =   $booking_time;
                                $addSchedule['frequency_id']    = $frequency_id;
                                $addSchedule['deleted']         =   '';
                                $addSchedule['created']         =  strtotime(date('Y-m-d H:i:s'));
                                $updated                        =  strtotime(date('Y-m-d H:i:s'));
                                $addSchedule['status']          =   0;
                                $period =   new \DatePeriod(
                                            new \DateTime($dateStart), new \DateInterval('P1D'), (new \DateTime($dateEnd))
                                );
                                $dates = iterator_to_array($period); //pr($dates);
                                $batchInsert = $addedSchedule= array();
                                $i=0;
                                foreach ($dates as $key=> $val) {
         
                                    date_default_timezone_set($params['timezone']);
                                    $date = strtotime($val->format('Y-m-d')); 
                                    $updateStime  =  strtotime(date('Y-m-d',$date).' '.(date('H:i',$starttime)));
                                    $updateEtime    =  strtotime(date('Y-m-d',$date).' '.(date('H:i',$endtime)));
                                     //format date
                                    $ScheduleDateInUTC = utc_date($val->format('Y-m-d'),$params['timezone'],true);
                                    date_default_timezone_set("UTC");
                                    //echo $ScheduleDateInUTC=strtotime(date('Y-m-d',$date)); echo '</br>';
                                    $query=['userid'=> new MongoId($params['userid']),'date'=>$ScheduleDateInUTC ];
                                    $checkScheduleExists=$this->CommonModel->getCollectionData($this->collection,$query);
                                    $updateStime  =  strtotime(date('Y-m-d H:iP',$updateStime));
                                    $updateEtime    =  strtotime(date('Y-m-d H:iP',$updateEtime));
                                    if($checkScheduleExists){

                                        $paramsType=array_diff ($params['type'] ,$checkScheduleExists[0]['type']);
                                        $newInsertType=(array_intersect ($type,$paramsType));
                                        if($newInsertType)
                                        {
                                             
                                             $newInsertType=array_values($newInsertType);
                                             foreach ($newInsertType as $key => $value_type) { 
                                                    $disable_schedule_time[$value_type]=[];
                                                     $booking_time[$value_type]=[];
                                                    $schedule_time[$value_type]=['starttime'=>$updateStime,'endtime'=>$updateEtime];
                                                    $frequency_id[$value_type]= new \MongoId($params['frequency_id']);
                                                }
                                             $addSchedule['type']            =   array_merge($checkScheduleExists[0]['type'],$newInsertType);
                                            $addSchedule['disable_schedule_time']=array_merge($checkScheduleExists[0]['disable_schedule_time'],$disable_schedule_time);
                                            $addSchedule['booking_time']  =   array_merge($checkScheduleExists[0]['booking_time'],$booking_time);
                                             $addSchedule['schedule_time'] = array_merge($checkScheduleExists[0]['schedule_time'],$schedule_time);
                                             $addSchedule['frequency_id']    = array_merge($checkScheduleExists[0]['frequency_id'],$frequency_id);
                                              date_default_timezone_set($params['timezone']);
                                             if(in_array(date('N', $date),$params['includer'])) {
                                                $addSchedule['date']            =   $ScheduleDateInUTC;
                                                $addSchedule['updated']         =   strtotime(date('Y-m-d',$updated));
                                                $dataResult=$this->CommonModel->upsert($this->collection,$addSchedule,$checkScheduleExists[0]['_id']->{'$id'},true);
                                            }
                                        }else{
                                            $addedSchedule[]=$date;
                                        }
                                    }else{
                                        
                                        
                                        foreach ($addSchedule['type'] as $key => $value_type) { 
                                                    $schedule_time[$value_type]=['starttime'=>$updateStime,'endtime'=>$updateEtime];
                                                }
                                        $addSchedule['schedule_time'] = $schedule_time;
                                        date_default_timezone_set($params['timezone']);
                                        if(in_array(date('N', $date),$params['includer'])) {
                                            $addSchedule['date']   =  $ScheduleDateInUTC;
                                            $addSchedule['created'] =   strtotime(date('Y-m-d H:i:s',$addSchedule['created']));
                                             //$batchInsert[]=$addSchedule;

                                            //pr($addSchedule); die;
                                          $dataResult=$this->CommonModel->upsert($this->collection,$addSchedule);  
                                        }
                                    
                                    //$dataResult = $this->CommonModel->batchInsert($this->collection,$batchInsert);
                                    }

                                    
                                } 
                                
                                if ($dataResult) {
                                    $response["status"] = 1;
                                     $response['message'] = 'ScucessFully added schedule';
                                    /* if(count($addedSchedule)>0){
                                        $response['message'] = 'ScucessFully added schedule and you have already added schedule list';
                                        $response["data"][]["already_added_date"]=$addedSchedule;

                                    }*/

                                } else {
                                    if(count($addedSchedule)>0){
                                    $response["status"] = 0;  
                                    $response['message'] = 'Data not added and you have already added schedule';
                                   // $response["data"][]["already_added_date"]=$addedSchedule;
                                    }else{
                                        $response["status"] = 0;
                                        $response['message'] = 'Data not inserted.';
                                    }
                                }
                        }else{
                                $response["status"] = 0;
                                $response['message'] = 'Provider id does not exists.';
                            }
                        }else{
                                $response["status"] = 0;
                                $response["message"] = 'Please enter valid date or time.';
                            }
                }else {

                $response["status"] = 0;
                $response["message"] = 'Invalid Valid Frequency id';
            } 
            }catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = 'Invalid Valid Frequency';
            } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response["message"] = 'Please insert valid schedule type or includer.';
        }
        $this->response($response);
    }

    
   
    public function update_put()
    {
        $response = [];
        $type=['Audio','Video','Chat'];
        $params = $this->put();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','schedule_id','starttime','endtime','type','frequency_id','timezone'];
        $otherRequired=['type'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRequired);
        if(empty($validation) && is_array($params['type'])) 
        { 
            try {
                    $typeValue= array_intersect ($type,$params['type']);
                    if(empty($typeValue)) {
                        $response["status"] = 0;
                        $response["message"] = 'Please insert valid schedule type';
                        $this->response($response);
                    }
                    $freqData=$this->CommonModel->getCollectionData('frequency',['_id'=> new MongoId($params['frequency_id'])]);
                    if($freqData) {
                        date_default_timezone_set($params['timezone']);
                        $starttime  =   strtotime(date('Y-m-d').' '.$params['starttime']); 
                        $endtime    =   strtotime(date('Y-m-d').' '.$params['endtime']);
                        $timeSlot   =   $freqData[0]['time_in_mins']; 
                        $newStarttime   =   $starttime + ($timeSlot * 60);
                        if($endtime >= $newStarttime) {
                            $query=['userid'=>new \MongoId($params['userid']),'_id'=> new \MongoId($params['schedule_id'])];
                            $checkScheduleExists=$this->CommonModel->getCollectionData($this->collection,$query);
                           // pr($checkScheduleExists); die;
                            if($checkScheduleExists){
                                        $date = $checkScheduleExists[0]['date'];
                                        $updateStime  =  strtotime(date('Y-m-d',$date).' '.(date('H:i',$starttime)));
                                        $updateEtime    =  strtotime(date('Y-m-d',$date).' '.(date('H:i',$endtime))); 
                                        date_default_timezone_set("UTC");
                                        $updateStime  =  strtotime(date('Y-m-d H:iP',$updateStime));
                                        $updateEtime    =  strtotime(date('Y-m-d H:iP',$updateEtime));
                                          if(count($checkScheduleExists[0]['booking_time'][$params['type'][0]])>0){
                                                $response["status"] = 0;
                                                $response['message']='Schedule not edited because some booking are confirmed.';
                                                $this->response($response);
                                            } else{

                                                   $disable_schedule_time[$params['type'][0]]=[];
                                                    $updateSchedule['disable_schedule_time'] = array_merge($checkScheduleExists[0]['disable_schedule_time'],$disable_schedule_time);
                                                   $schedule_time[$params['type'][0]]=['starttime'=>$updateStime,'endtime'=>$updateEtime];
                                                    $updateSchedule['schedule_time'] = array_merge($checkScheduleExists[0]['schedule_time'],$schedule_time);
                                                    $frequency_id[$params['type'][0]]   =   new \MongoId($params['frequency_id']);
                                                    $updateSchedule['frequency_id']=array_merge($checkScheduleExists[0]['frequency_id'],$frequency_id);
                                                    $this->CommonModel->upsert($this->collection,$updateSchedule,$params['schedule_id'],true);
                                                $response["status"] = 1;
                                                $response["message"] = 'Successfully Schedule updated.'; 
                                                $response['data']["slots_status"]=$this->Api->schedule_status_list('provider');
                                                $response["data"]['appointment_time_slots'] = $this->schedule_slot_list($checkScheduleExists[0],$updateStime,$updateEtime,$timeSlot,$params['type'][0]);
                                            }
                                }else{
                                        $response["status"] = 0;
                                        $response["message"] = 'Provider id or schedule does not exists.';
                                    }
                            }else{
                                    $response["status"] = 0;
                                    $response["message"]= 'Please enter valid date or time.';
                                }
                    }else {

                        $response["status"] = 0;
                        $response["message"] = 'Invalid Valid Frequency id';
                        } 
            }catch (MongoException $ex) {

                $response["status"] = 0;
                $response["message"] = 'Invalid Valid Frequency';
            } 
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response["message"] = 'Please insert valid schedule type.';
        }
        $this->response($response);
    } 
     /**
     * @param array
     * @function is used to insert and update Walkin Schedule 
     * @return true/false
     */
    public function insert_walkin_put()
    {
        $response = [];
        $type=['Walkin'];
        $dataResult='';
        $params = $this->put();
        $url=explode('-',$this->uri->segment(2)); //pr($params);
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','startdate','enddate','starttime','endtime','includer','type','frequency_id','work_id','timezone'];
        $otherRequired=['type'=>'','includer'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRequired);
        if(empty($validation) && is_array($params['type']) && is_array($params['includer'])) {	
            try{ 
            
		            $typeValue= array_intersect ($type,$params['type']);
                    if(empty($typeValue)) {
                        $response["status"] = 0;
                        $response["message"] = 'Please insert valid schedule type';
                        $this->response($response);
                    }
                    $freqData=$this->CommonModel->getCollectionData('frequency',['_id'=> new MongoId($params['frequency_id'])]);
			        if($freqData){  
                         date_default_timezone_set($params['timezone']);
			            $startdate  =   strtotime(date('Y-m-d',$params['startdate']));
                        $enddate    =   strtotime(date('Y-m-d',$params['enddate']).' + 1 days');
                        $starttime  =   strtotime(date('Y-m-d').' '.$params['starttime']); 
                        $endtime    =   strtotime(date('Y-m-d').' '.$params['endtime']);  
			            $timeSlot   =   $freqData[0]['time_in_mins'];
                        $newStarttime   =   $starttime + ($timeSlot * 60);  
			            if($enddate  >= $startdate && $endtime  >= $newStarttime) {
			            	$workSearch=['_id'=> new \MongoId($params['work_id']),'userid'=> new \MongoId($params['userid'])];
			                $parovidreExists = $this->CommonModel->getCollectionData('providerWorks',$workSearch);
			                if($parovidreExists){
				                    $dateStart=date('Y-m-d',$startdate);
				                    $dateEnd=date('Y-m-d',$enddate);
			                        // schedule array vlaues 
			                        $addSchedule['userid']= new \MongoId($params['userid']);
			                        $addSchedule['work_id'] = new \MongoId($params['work_id']);
			                        $addSchedule['type']            =  array_values($typeValue);
			                        foreach ($addSchedule['type'] as $key => $value) {
                                        $disable_schedule_time[$value]=[];
                                        $booking_time[$value]=[];
                                        $frequency_id[$value]= new \MongoId($params['frequency_id']);
                                    }
                                    $addSchedule['disable_schedule_time'] = $disable_schedule_time;
                                    $addSchedule['booking_time']    =   $booking_time;
			                        $addSchedule['deleted']         =   '';
			                        $addSchedule['frequency_id']    =   $frequency_id;
                                    $addSchedule['created']         =   strtotime(date('Y-m-d H:i:s'));
                                    $updated                        =   strtotime(date('Y-m-d H:i:s'));
			                        $addSchedule['status']          =   0;
			                        $period =   new \DatePeriod(
			                                    new \DateTime($dateStart), new \DateInterval('P1D'), (new \DateTime($dateEnd))
			                        );
			                        $dates = iterator_to_array($period); //pr($dates);
			                        $batchInsert = $addedSchedule= array();
			                        $i=0;

			                        foreach ($dates as $key=> $val) {
			                            
			                           date_default_timezone_set($params['timezone']);
                                        $date = strtotime($val->format('Y-m-d')); //format date
                                        $updateStime  =  strtotime(date('Y-m-d',$date).' '.(date('H:i',$starttime)));
                                        $updateEtime    =  strtotime(date('Y-m-d',$date).' '.(date('H:i',$endtime)));
                                        
                                        $ScheduleDateInUTC = utc_date($val->format('Y-m-d'),$params['timezone'],true);
                                         date_default_timezone_set("UTC");
			                            $query=['userid'=>new MongoId($params['userid']),'date'=>$ScheduleDateInUTC ];
                                        $updateStime  =  strtotime(date('Y-m-d H:iP',$updateStime));
                                        $updateEtime    =  strtotime(date('Y-m-d H:iP',$updateEtime));
			                            $checkScheduleExists=$this->CommonModel->getCollectionData($this->collection,$query);
                                        //pr($checkScheduleExists); die;
 										if($checkScheduleExists){

                                            $paramsType=array_diff ($params['type'] ,$checkScheduleExists[0]['type']);
                                            $newInsertType=(array_intersect ($type,$paramsType));
                                            if($newInsertType)
                                            {
                                                 
                                                 $frequency_id=[];
                                                 $newInsertType=array_values($newInsertType);
                                                 foreach ($newInsertType as $key => $value_type) { 
                                                        $disable_schedule_time[$value_type]=[];
                                                         $booking_time[$value_type]=[];
                                                         $frequency_id[$value_type]= new \MongoId($params['frequency_id']);
                                                        $schedule_time[$value_type]=['starttime'=>$updateStime,'endtime'=>$updateEtime];
                                                    }
                                                    $addSchedule['type']            =   array_merge($checkScheduleExists[0]['type'],$newInsertType);
                                                    $addSchedule['frequency_id']    =   array_merge($checkScheduleExists[0]['frequency_id'],$frequency_id);
                                                     $addSchedule['disable_schedule_time']=array_merge($checkScheduleExists[0]['disable_schedule_time'],$disable_schedule_time);
                                                    $addSchedule['booking_time']  =   array_merge($checkScheduleExists[0]['booking_time'],$booking_time);
                                                    $addSchedule['schedule_time'] = array_merge($checkScheduleExists[0]['schedule_time'],$schedule_time);
                                                    $addSchedule['date']            =   $ScheduleDateInUTC;
                                                    $addSchedule['updated']         =   strtotime(date('Y-m-d',$updated));
                                                    //pr($addSchedule); die;
                                                     date_default_timezone_set($params['timezone']);
                                                 if(in_array(date('N', $date),$params['includer'])) {
                                                    
                                                    $dataResult=$this->CommonModel->upsert($this->collection,$addSchedule,$checkScheduleExists[0]['_id']->{'$id'},true);
                                                }
                                            }
											
										}else{

                                            foreach ($addSchedule['type'] as $key => $value_type) { 
                                                $schedule_time[$value_type]=['starttime'=>$updateStime,'endtime'=>$updateEtime];
                                                }
                                            $addSchedule['schedule_time'] = $schedule_time;
                                            $addSchedule['date']   =  $ScheduleDateInUTC;
                                            $addSchedule['created'] =   strtotime(date('Y-m-d H:i:s',$addSchedule['created']));
                                            date_default_timezone_set($params['timezone']);
                                            //pr($addSchedule); die;
                                            if(in_array(date('N', $date),$params['includer'])) {
                                               
                                                //$batchInsert[]=$addSchedule;
                                              $dataResult=$this->CommonModel->upsert($this->collection,$addSchedule);  
                                            }
                                            //$batchInsert[]=$addSchedule;

                                        }	            
			                        }
                                   // pr($batchInsert); die;
				                        //$dataResult = $this->CommonModel->batchInsert($this->collection,$batchInsert);
				                        if ($dataResult) {
				                            $response["status"] = 1;
				                             $response['message'] = 'SuccessFully Schedule ';
				                          /*  if(count($addedSchedule)>0){
				                                $response['message'] = 'SuccessFully added schedule and you have already added schedule list';
				                                $response["data"][]["already_added_date"]=$addedSchedule;

				                            } */

				                        } else {
				                           /* if(count($addedSchedule)>0){
				                            $response["status"] = 1;  
				                            $response['message'] = 'Data not added and you have already added schedule list';
				                            $response["data"][]["already_added_date"]=$addedSchedule;
				                            }else{
				                                $response["status"] = 0;
				                                $response['message'] = 'Data not inserted.';
				                            } */
                                            $response["status"] = 0;
                                            $response['message'] = 'Data not inserted.';
				                        }

					                }else{
					                        $response["status"] = 0;
					                        $response['message'] = 'Provider or Work id does not exists.';
					                    }
					                }else{
					                        $response["status"] = 0;
					                        $response["message"] = 'Please enter valid date or time.';
					                    }
					            }else{
					                        $response["status"] = 0;
					                        $response["message"] = 'Frequency id does not exists.';
					            }
		            }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or work id';
                }
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response["message"] = 'Please insert valid schedule type or includer.';
        }
        $this->response($response);
    }

   
    /**
     * @param array
     * @function is used to update schedule 
     * @return true/false
     */
    public function edit_walkin_put()
    {
        $response = [];
        $type=['Walkin'];
        $params = $this->put();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','schedule_id','starttime','endtime','type','frequency_id','work_id','timezone'];
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
            $freqData=$this->CommonModel->getCollectionData('frequency',['_id'=> new MongoId($params['frequency_id'])]);
			if($freqData){
                date_default_timezone_set($params['timezone']);
                $starttime  =   strtotime(date('Y-m-d').' '.$params['starttime']); 
                $endtime    =   strtotime(date('Y-m-d').' '.$params['endtime']);
                $timeSlot   =   $freqData[0]['time_in_mins']; 
                $newStarttime   =   $starttime + ($timeSlot * 60); 
                if($endtime >= $newStarttime) {
                        $query=['userid'=>new \MongoId($params['userid']),'_id'=> new \MongoId($params['schedule_id'])];
                        $checkScheduleExists=$this->CommonModel->getCollectionData($this->collection,$query);
                        if($checkScheduleExists){
                        $date = $checkScheduleExists[0]['date'];
                        $updateStime  =  strtotime(date('Y-m-d',$date).' '.(date('H:i',$starttime)));
                        $updateEtime  =  strtotime(date('Y-m-d',$date).' '.(date('H:i',$endtime))); 
                        date_default_timezone_set("UTC");
                        $updateStime  =  strtotime(date('Y-m-d H:iP',$updateStime));
                        $updateEtime    =  strtotime(date('Y-m-d H:iP',$updateEtime));
                        if(count($checkScheduleExists[0]['booking_time'][$params['type'][0]])>0){
                            $response["status"] = 0;
                            $response['message']='Schedule not edited because some booking are confirmed.';
                            $this->response($response);
                        } else{
                            $disable_schedule_time[$params['type'][0]]=[];
                            $updateSchedule['disable_schedule_time'] = array_merge($checkScheduleExists[0]['disable_schedule_time'],$disable_schedule_time);
                            $schedule_time[$params['type'][0]]=['starttime'=>$updateStime,'endtime'=>$updateEtime];
                            $updateSchedule['schedule_time'] = array_merge($checkScheduleExists[0]['schedule_time'],$schedule_time);
                            $frequency_id[$params['type'][0]]   =   new \MongoId($params['frequency_id']);
                            $updateSchedule['frequency_id']=array_merge($checkScheduleExists[0]['frequency_id'],$frequency_id);
                            $updateSchedule['work_id']   =  new MongoId($params['work_id']);
                            
                            $this->CommonModel->upsert($this->collection,$updateSchedule,$params['schedule_id'],true);
                            $response["status"] = 1;
                            $response["message"] = 'Successfully Schedule updated.'; 
                            $response['data']["slots_status"]=$this->Api->schedule_status_list('provider');
                            $response["data"]['appointment_time_slots'] = $this->schedule_slot_list($checkScheduleExists[0],$updateStime,$updateEtime,$timeSlot,$params['type'][0]);
                        }

                    }else{
                            $response["status"] = 0;
                            $response["message"] = 'Schedule or User does not exists.';
                        }
                }else{
                        $response["status"] = 0;
                        $response["message"]= 'Please enter valid date or time.';
                    }
             }else{
                        $response["status"] = 0;
                        $response["message"]= 'Please valid frequency id.';
                }
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response["message"] = 'Please insert valid schedule type.';
        }
        $this->response($response);
    }

    /**
     * @param array 
     * @function is used to get schedule list
     * @return true/false
     */
    public function list_post()
    {
        $response = [];
        $params = $this->post();
        $type=explode(',', SCHEDULE_TYPE_LIST);
        $required=['userid','date','list_type','timezone'];
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
         $validation = $this->CommonModel->validation($params,$required);
        if(empty($validation)) 
        { 

            try{
                    date_default_timezone_set($params['timezone']);
                    if($params['list_type']=='Day'){
                        $startdate=strtotime(date('Y-m-d',$params['date']));
                        $enddate=strtotime(date('Y-m-d',$params['date']).'+1 days');
                    }else if($params['list_type']=='Month'){

                        $startdate=strtotime(date('Y-m-01',$params['date']));
                        $enddate=strtotime(date('Y-m-t',$params['date']));
                    }else{
                        $response["status"] = 0;
                        $response["message"] = 'Please enter valid list type';
                         $this->response($response);
                    }

                    date_default_timezone_set("UTC");
                    $startdate=strtotime(date('Y-m-d',$startdate));
                    $enddate=strtotime(date('Y-m-d',$enddate));
                    $wheres['date']=['$gte'=>$startdate ,'$lte'=>$enddate];
                    if(isset($params['userid']))
                    $wheres['userid']= new \MongoId($params['userid']);
                    if(isset($params['type']) && is_array($params['type'])){
                        $wheres['type']=['$in'=>$params['type']];
                    } 
                   //pr($wheres); die;
                    $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres,array('date','starttime','endtime','type'));

                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $response['data'] = $dataList;
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'You have no any schedule.';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
        } else {
            $response["status"] = 0;
            $response['message']='Mandatory fields are required.';
            if($validation)
                $response["error_data"] = $validation;
            else
                $response["message"] = 'Please insert valid schedule type.';
        }
        $this->response($response);
    }

    /**
     * @param null
     * @function is used to get schedule details
     * @return true/false
     */
    public function view_post()
    {
        $response = [];
        $params = $this->post();
        $type=['Walkin','Chat','Video','Audio'];
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','schedule_id','timezone','type'];
        $otherRequired=['type'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRequired); 
        if(empty($validation)) {
                    try{
                     $typeValue= array_intersect ($type,$params['type']);
                    if(empty($typeValue)) {
                        $response["status"] = 0;
                        $response["message"] = 'Please insert valid schedule type';
                        $this->response($response);
                    }

                    $wheres=['userid'=> new \MongoId($params['userid']),'_id'=> new \MongoId($params['schedule_id']),'type'=>['$in'=>$params['type']]];
                    $dataList = $this->CommonModel->getCollectionData($this->collection,$wheres); 
                    if ($dataList) {
                        $response["status"] = 1;
                        $response['message'] = 'success';
                        $result['schedule_id']=$params['schedule_id'];
                        $frq_id=$dataList[0]['frequency_id'][$params['type'][0]]; 
                        if(isset($dataList[0]['work_id'])){
                        $work_data=$this->CommonModel->getCollectionData('providerWorks',['_id'=>$dataList[0]['work_id']],['name']); 
                         $result['work_id']=($work_data)?$work_data:[] ;
                        }else
                        $result['work_id']=[];
                        $result['date']=$dataList[0]['date'];
                        $result['starttime']=$dataList[0]['schedule_time'][$params['type'][0]]['starttime'];
                        $result['endtime']=$dataList[0]['schedule_time'][$params['type'][0]]['endtime'];
                        $frq_id= $this->CommonModel->getCollectionData('frequency',['_id'=>$frq_id],['time_in_mins']);
                        $result['frequency_id']= ($frq_id)?$frq_id:[];
                        $response['data']= $result; 

                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'You have no any schedule.';
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

     /**
     * @param array or object id
     * @function is used to soft delete
     * @return true/false
     */
    public function delete_post()
    {
        $response = [];
        $params = $this->post();
        $type=['Audio','Video','Chat','Walkin'];
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','schedule_id','timezone','type'];
        $otherRequired=['type'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRequired); 
        if(empty($validation)) {
             try{

                $typeValue= array_intersect ($type,$params['type']);
                if(empty($typeValue)) {
                    $response["status"] = 0;
                    $response["message"] = 'Please insert valid schedule type';
                    $this->response($response);
                }
                $query=['userid'=>new \MongoId($params['userid']),'_id'=>new \MongoId($params['schedule_id']),'type'=>['$in'=>$params['type']]];
                    $checkScheduleExists=$this->CommonModel->getCollectionData($this->collection,$query);
                    if($checkScheduleExists){

                        if(count($checkScheduleExists[0]['booking_time'][$params['type'][0]])>0){
                            $response["status"] = 0;
                            $response['message'] = 'Schedule not deleted because some booking are confirmed.';
                            $this->response($response);
                        }else{
                            unset($checkScheduleExists[0]['type'][array_search($params['type'][0],$checkScheduleExists[0]['type'])]);
                            unset($checkScheduleExists[0]['disable_schedule_time'][$params['type'][0]]);
                            unset($checkScheduleExists[0]['booking_time'][$params['type'][0]]);
                            unset($checkScheduleExists[0]['schedule_time'][$params['type'][0]]);
                            unset($checkScheduleExists[0]['frequency_id'][$params['type'][0]]);
                            $updated['type']            =   array_values($checkScheduleExists[0]['type']);
                            $updated['disable_schedule_time']=$checkScheduleExists[0]['disable_schedule_time'];
                            $updated['booking_time']  =   $checkScheduleExists[0]['booking_time'];
                             $updated['schedule_time'] = $checkScheduleExists[0]['schedule_time'];
                             $updated['frequency_id']    = $checkScheduleExists[0]['frequency_id'];
                             $dataList =  $this->CommonModel->upsert($this->collection,$updated,$params['schedule_id'],true);
                            if ($dataList) {
                                if(count($checkScheduleExists[0]['type'])<1){
                                  $this->CommonModel->delete($this->collection,['_id'=> new \MongoId($params['schedule_id'])],null,'schedule');
                                }
                                $response["status"] = 1;
                                $response['message'] = 'SuccessFully deleted';
                            } else {
                                $response["status"] = 0;
                                $response['message'] = 'Schedule not deleted.';
                            }
                        }
                       
                    }else{
                         $response["status"] = 0;
                        $response['message'] = 'Invalid Schedule';
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid User or schedule id';
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
     * @function is used to get schedule list 
     * @return array
     */
    public function schedule_list_post()
    {
        $response = [];
        $params = $this->post();
        $type=explode(',',SCHEDULE_TYPE_LIST);
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','date','list_type','timezone','type'];
        $otherRequired=['type'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRequired); 

        if(empty($validation) && !in_array(null, $params) && in_array($params['list_type'],['Day','Month'])) {
            try{
                   $typeValue= array_intersect ($type,$params['type']);
                    if(empty($typeValue)) {
                        $response["status"] = 0;
                        $response["message"] = 'Please insert valid schedule type';
                        $this->response($response);
                    }
                   $wheres['userid']= new \MongoId($params['userid']);
                   date_default_timezone_set($params['timezone']);
                    if($params['list_type']=='Day'){

                        $startdate=strtotime(date('Y-m-d',$params['date']));
                        $enddate=strtotime(date('Y-m-d',$params['date']).'+1 days');
                    }else if($params['list_type']=='Month'){

                        $startdate=strtotime(date('Y-m-01',$params['date']));
                        $enddate=strtotime(date('Y-m-t',$params['date']).'+1 days');
                    }else{
                        $response["status"] = 0;
                        $response["message"] = 'Please enter valid list type';
                         $this->response($response);
                    }

                    date_default_timezone_set("UTC");
                    $startdate=strtotime(date('Y-m-d',$startdate));
                    $enddate=strtotime(date('Y-m-d',$enddate));
                    $wheres['date']=['$gte'=>$startdate ,'$lt'=>$enddate];
                    if(isset($params['type']) && is_array($params['type'])){
                        $wheres['type']=['$in'=>$params['type']];
                    }else{
                        $wheres['type']=['$in'=>$type];
                    } 
                    
                    $dataList = $this->CommonModel->getCollectionData("providerSchedules",$wheres,[],['date'=>1],31);
                    if ($dataList) { 
                        // List of appointments times slots
                        $response["status"] = 1;
                        $response["message"] = 'Schedule time slot list.';
                        $objectList=[];
                        $srh_appointment=['provider_id'=> new \MongoId($params['userid']),'appointment_date'=>$dataList[0]['date']];
                        $appBooked = $this->CommonModel->getCollectionData("patientAppointments",$srh_appointment);
                        $timeSlotList=[];
                        foreach ($dataList as $key => $value) {
                                $type_set=$params['type'][0];
                                $timeSlotAddList['schedule_id']=$value['_id']->{'$id'};
                                $timeSlotAddList['date']=$value['date'];
                                $freqData=$this->CommonModel->getCollectionData('frequency',['_id'=>$value['frequency_id'][$type_set]],['time_in_mins']);
                                if($freqData){
                                    $timeSlotAddList['frequency_id']=$freqData[0];
                                    //date_default_timezone_set($params['timezone']);
                                    $endtime= $value['schedule_time'][$type_set]['endtime']; 
                                    $starttime= $value['schedule_time'][$type_set]['starttime']; 
                                    $timeSlot   =   $freqData[0]['time_in_mins'];                    
                                    $timeSlotAddList['slot']['type']=[$type_set];
                                    $timeSlotAddList['slot']['starttime']=$starttime;
                                    $timeSlotAddList['slot']['endtime']=$endtime;
                                    $timeSlotAddList['slot']['list']= $this->schedule_slot_list($value,$starttime,$endtime,$timeSlot,$type_set);
                                        
                                    } // Frequecy check
                                $objectList[]=$timeSlotAddList;
                            } // time slot loop close
                      
                        $response['data']["slots_status"]=$this->Api->schedule_status_list('provider');
                        $response['data']["appointment_time_slots"]=$objectList;

                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'You have no any schedule.';
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

    /**
     * @param null
     * @function is used to get appointment time list of particular data
     * @return true/false
     */
    public function schedule_calendar_month_post()
    {
        $response = [];
        $params = $this->post();
        $type=explode(',',SCHEDULE_TYPE_LIST);
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','date','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 

        if(empty($validation) && !in_array(null, $params)) {
            try{
                    date_default_timezone_set($params['timezone']);
                    $startdate=date('Y-m-01',$params['date']);
                    $enddate=strtotime(date('Y-m-t',$params['date']).'+1 days');
                    $enddate=date('Y-m-d',$enddate);
                    $startdateUTC = utc_date($startdate,$params['timezone'],true);
                    $enddateUTC   = utc_date($enddate,$params['timezone'],true);
                        $response["status"] = 1;
                        $response["message"] = 'Schedule Calendar View Status List.';
                        $objectList=[];
                        $timeSlotList=[];
                        $wheres['userid']= new \MongoId($params['userid']);
                        $wheres['date']=['$gte'=>$startdateUTC,'$lt'=>$enddateUTC];
                        $dataList = $this->CommonModel->getCollectionData("providerSchedules",$wheres ,[],['date'=>1],31);
                        if($dataList){

                            foreach ($dataList as $key => $value) { 
                                $addList=$itemList=[];
                                $itemList['date']=$value['date'];
                                $addList['schedule_id']=$value['_id']->{'$id'};
                                $AV_Status=(in_array("Audio",$value['type']))?'A':'';
                                $AV_Status=(in_array("Video",$value['type']))?$AV_Status.'V':$AV_Status.'';
                                $CW_Status=(in_array("Chat",$value['type']))?'C':'';
                                $CW_Status=(in_array("Walkin",$value['type']))?$CW_Status.'W':$CW_Status.'';

                                $addList['audio_video_status']=$AV_Status;
                                $addList['chat_walkin_status']=$CW_Status;
                                $itemList['date_status']=$addList;
                                $objectList[]=$itemList;
                            }
                                                              
                        }
                        /*
                        for ($i=$startdateUTC;$i<$enddateUTC;) {

                                $addList=[];
                                $itemList['date']=$i;
                                                                //$searchSdate = utc_date($startdate,$params['timezone'],true);
                               // $searchEdate   = utc_date($enddate,$params['timezone'],true);
                                $wheres['userid']= new \MongoId($params['userid']);
                                $wheres['date']=$i;
                                $dataList = $this->CommonModel->getCollectionData("providerSchedules",$wheres ,[],['date'=>1]);
                                if($dataList){
                                    $value=$dataList[0];
                                    $addList['schedule_id']=$value['_id']->{'$id'};
                                    $AV_Status=(isset($value['booking_time']['Audio']) && count($value['booking_time']['Audio'])>0)?'A':'';
                                    $AV_Status=(isset($value['booking_time']['Video']) && count($value['booking_time']['Video'])>0)?$AV_Status.'V':$AV_Status.'';
                                    $CW_Status=(isset($value['booking_time']['Chat']) && count($value['booking_time']['Chat'])>0)?'C':'';
                                    $CW_Status=(isset($value['booking_time']['Walkin']) && count($value['booking_time']['Walkin'])>0)?$CW_Status.'W':$CW_Status.'';

                                    $addList['audio_video_status']=$AV_Status;
                                    $addList['chat_walkin_status']=$CW_Status;
                                                                      
                                }
                                if(count($addList)>0)
                                    $itemList['date_status']=$addList;
                                $objectList[]=$itemList;
                                $loopPlusonday=strtotime(date('Y-m-d H:i:s',$i).'+ 1 Days');
                                $i=$loopPlusonday; 
                            } */
                       $response['data']["appointment_time_slots"]=$objectList;
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

    /**
     * @param null
     * @function is used to get appointment time list of particular data
     * @return true/false
     */
    public function schedule_calendar_day_post()
    {
        $response = [];
        $params = $this->post();
        $type=explode(',',SCHEDULE_TYPE_LIST);
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','date','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && !in_array(null, $params)) {
            try{
                   
                   $wheres['userid']= new \MongoId($params['userid']);
                   date_default_timezone_set($params['timezone']);
                   $startdate=date('Y-m-d',$params['date']);
                   $enddate=strtotime(date('Y-m-d',$params['date']).'+1 days');
                   $enddate=date('Y-m-d',$enddate);
                   date_default_timezone_set("UTC");
                   $startdateUTC = utc_date($startdate,$params['timezone'],true);
                   $enddateUTC   = utc_date($enddate,$params['timezone'],true);
                   $wheres['date']=['$gte'=>$startdateUTC ,'$lt'=>$enddateUTC];
                   $dataList = $this->CommonModel->getCollectionData("providerSchedules",$wheres);
                    if ($dataList) { 
                        // List of appointments times slots
                        $response["status"] = 1;
                        $response["message"] = 'Schedule time slot list.';
                        $itemList=[];
                        $value=$dataList[0];
                        $type_count=count($value['type']);
                        $itemList['schedule_id']=$value['_id']->{'$id'};
                        $itemList['date']=$value['date'];
                        $itemList['schedule_id']=$value['_id']->{'$id'};
                        for($appointments_type_loop=0; $appointments_type_loop<$type_count ; $appointments_type_loop++) {
                                $j=$appointments_type_loop;
                                $value=$dataList[0];
                                
                                $timeSlotListAdd= $timeSlotList= [];
                                 $freqData=$this->CommonModel->getCollectionData('frequency',['_id'=>$value['frequency_id'][$value['type'][$j]]],['time_in_mins']);

                                if($freqData){
                                 $timeSlot   =   $freqData[0]['time_in_mins'];  

                                $endtime= $value['schedule_time'][$value['type'][$j]]['endtime']; 
                                $starttime= $value['schedule_time'][$value['type'][$j]]['starttime']; 
                                $timeSlotList=$this->schedule_slot_list($value,$starttime,$endtime,$timeSlot,$value['type'][$j]);
                                }
                                        $itemList['slot'][$j]['type']=$value['type'][$j];
                                        $itemList['slot'][$j]['starttime']=$starttime;
                                        $itemList['slot'][$j]['endtime']=$endtime;
                                        $itemList['slot'][$j]['frequency_id']=$freqData[0];
                                        $itemList['slot'][$j]['list']=$timeSlotList;
                                
                        } // type close 
                        $paramsType=array_diff ($type,$dataList[0]['type']);
                        $newInsertType=(array_intersect ($type,$paramsType));
                        foreach ($newInsertType as $key => $value) {
                            $additemList['slot'][$key]['type']=$value;
                            $additemList['slot'][$key]['starttime']='';
                            $additemList['slot'][$key]['endtime']='';
                            $additemList['slot'][$key]['frequency_id']='';
                            $additemList['slot'][$key]['list']=[];
                        } 
                        //$itemList['slot']=array_merge($itemList['slot'],$additemList['slot']);
                        $response['data']["slots_status"]=$this->Api->schedule_status_list('provider'); 
                        $response['data']["appointment_time_slots"]=$itemList;

                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'You have no any schedule.';
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

    /**
     * @param array
     * @function is used to update schedule slot time Active or Deactive
     * @return true/false
     */
    public function schedule_slot_time_update_post()
    {
        $response = [];
        $type=['Walkin','Chat','Audio','Video'];
        $params = $this->post();
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','schedule_id','timezone','appointment_time','type'];
        $otherRequired=['type'=>'','appointment_time'=>''];
        $validation = $this->CommonModel->validation($params,$required,$otherRequired);
        if(empty($validation) &&  is_array($params['type']) && is_array($params['appointment_time'])) 
        { 
             $typeValue= array_intersect ($type,$params['type']);
                    if(empty($typeValue)) {
                        $response["status"] = 0;
                        $response["message"] = 'Please insert valid schedule type';
                        $this->response($response);
                    }
            date_default_timezone_set($params['timezone']);
            $query=['userid'=>new \MongoId($params['userid']),'_id'=> new \MongoId($params['schedule_id'])];
            $checkScheduleExists=$this->CommonModel->getCollectionData($this->collection,$query);
                    if($checkScheduleExists){

                    foreach ($params['appointment_time'] as $key => $value) {
                            
                        if(in_array($params['appointment_time'],$checkScheduleExists[0]['booking_time'][$params['type'][0]])){
                            $response["status"] = 0;
                            $response['message']='Schedule not Active/Deactive because some booking are confirmed.';
                            //$this->response($response);
                        } else{

                            
                            if(in_array($value,$checkScheduleExists[0]['disable_schedule_time'][$params['type'][0]])){

                                unset($checkScheduleExists[0]['disable_schedule_time'][$params['type'][0]][array_search($value,$checkScheduleExists[0]['disable_schedule_time'][$params['type'][0]])]);
                                $updateSchedule['disable_schedule_time'] = $checkScheduleExists[0]['disable_schedule_time'];
                                //$response["message"] = 'SuccessFully Schedule activated';
                            } else
                            {
                               array_push($checkScheduleExists[0]['disable_schedule_time'][$params['type'][0]],$value);
                                $updateSchedule['disable_schedule_time'] = array_merge($checkScheduleExists[0]['disable_schedule_time'],$checkScheduleExists[0]['disable_schedule_time']);
                                //$response["message"] = 'SuccessFully Schedule deactivated';
                             }
                            
                            $this->CommonModel->upsert($this->collection,$updateSchedule,$params['schedule_id'],true);
                            
                            
                        }
                    }

                    $response["status"] = 1;
                    $response["message"] = 'SuccessFully Schedule Updated';
                }else{
                        $response["status"] = 0;
                        $response["message"] = 'Schedule or User does not exists.';
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

    public function schedule_slot_list($scheduledata=array() ,$starttime=null,$endtime=null,$timeSlot=null,$type=null)
    {
        $timeSlotListAdd= $itemList= [];
        for($i=$starttime;$i< $endtime;)
        { 
                $timeSlotListAdd['appointment_time']=$i;
                $status_list=$this->Api->schedule_status_list('provider');
                if(in_array($i,$scheduledata['disable_schedule_time'][$type]))
                    $timeSlotListAdd['appointment_status']=1;
                else if(in_array($i,$scheduledata['booking_time'][$type]))
                    $timeSlotListAdd['appointment_status']=2;
                else
                    $timeSlotListAdd['appointment_status']=0;

                $i=$i+($timeSlot*60);
                $itemList[]=$timeSlotListAdd;
                
        }
        return $itemList;
    }

    /**
     * @param null
     * @function is used to get booked appointment type count 
     * @return true/false
     */
    public function booked_appointment_count_list_get()
    {
        $response = [];
        $params = $this->get();
        $type=['Audio','Video','Chat','Walkin'];
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid'];
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && !in_array(null, $params)) {
            try{
                   $provider_id= new \MongoId($params['userid']);
                   $userexists=$this->CommonModel->getCollectionData('providers',['_id'=>$provider_id]);
                   if ($userexists) { 
                        $wheres['provider_id']=$provider_id; 
                        $wheres['patient_status']=1;
                        $wheres['patient_status']=1;
                        $response["status"] = 1;
                        $response["message"] = 'Appointment type with Cout list';
                        $itemList=[];
                        foreach ($type as $key => $value) {
                            $wheres['appointment_type']=['$in'=>[$value]];
                            $dataList = $this->CommonModel->getCollectionData("patientAppointments",$wheres); 
                            $itemList[]=['key_name'=>$value,'key_count'=>count($dataList)];
                        }
                       
                        $response['data']["list"]=$itemList;

                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Invalid User';
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

    public function upcoming_schedule_post()
    {
        $response = [];
        $params = $this->post();
        $type=explode(',',SCHEDULE_TYPE_LIST);
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','date','timezone'];
        $validation = $this->CommonModel->validation($params,$required); 

        if(empty($validation) && !in_array(null, $params)) {
            try{
                     date_default_timezone_set($params['timezone']);
                     if(isset($params["list_type"] ) && $params['list_type']=="day") {
                        $startdate=date('Y-m-d',$params["date"]);
                        $enddate=strtotime(date('Y-m-d',$params["date"]).'+1 days');
                        $enddate=date('Y-m-d',$enddate);
                    } else {
                       $startdate=date('Y-m-d');
                       $enddate=strtotime(date('Y-m-t').'+1 days');
                       $enddate=date('Y-m-d',$enddate);
                    }
                    date_default_timezone_set("UTC");
                    $startdateUTC = utc_date($startdate,$params['timezone'],true);
                    $enddateUTC   = utc_date($enddate,$params['timezone'],true);
                    $objectList=[];
                    $wheres['userid']= new \MongoId($params['userid']);
                    $wheres['date']=['$gte'=>$startdateUTC,'$lt'=>$enddateUTC];
                    $dataList = $this->CommonModel->getCollectionData("providerSchedules",$wheres ,[],['date'=>1]);
                     if($dataList){
                        $timeList=[];
                        if(isset($params['device_type']) && $params['device_type']=="IOS"){

                            $objectList=$this->booking_schedule($params['userid'],$dataList);
                        }else{
                        foreach ($dataList as $key => $value) { 
                            $addList=[];
                            $addList=$this->booked_patient_deatils($value);
                     
                                if(count($addList)>0){
                                    if(count($objectList)>0)
                                        $objectList=array_merge($objectList,$addList);
                                    else
                                        $objectList=$addList;
                                }
                            }

                        }
                        if(count($objectList)>0){
                            $response["status"] = 1;
                            $response["message"] = 'Your Upcoming booked Schedule List.';
                            $response['data']["appointment_time_slots"]=$objectList;                                    
                        } else{
                            $response["status"] = 0;
                            $response["message"] = 'You have no any schedule booked.';
                        }
                        }else{
                            $response["status"] = 0;
                            $response["message"] = 'You have no any schedule booked.';
                        }
                     
                       
                } catch (MongoException $ex) {
                    $response["status"] = 0;
                    $response["message"] = 'Invalid User';
                }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
        }
        $this->response($response);
    }

    public function booking_schedule($provider_id,$dataList=array()){
        $objectList=[];
    foreach ($dataList as $key => $value) { 
        $addList=[];
        $dataList=$this->booked_patient_deatils($value);
        
            if(count($dataList)>0){
                $addList['date']=$value['date'];
                $addList['booked_schedule']=$dataList;
                $objectList[]=$addList;
            }
        }
        return $objectList;
    }

    public function booked_patient_deatils($schedule=array()){
       $addList=$timeList=[];
        $type_count=count($schedule['type']);

       for($type_loop=0; $type_loop<$type_count ; $type_loop++) {

         $appointments_time=$schedule['booking_time'][$schedule['type'][$type_loop]];
         if(count($appointments_time)>0){
            if(count($timeList)>0)
                $timeList=array_merge($timeList,$appointments_time);
            else
                $timeList=$appointments_time;
         }
         
       }
        $searchAppoint=['provider_id'=> $schedule['userid'],'appointment_time'=>['$in'=>$timeList]];
        $existAppmt=$this->CommonModel->getCollectionData('patientAppointments',$searchAppoint,[],['appointment_time'=>1]);
        if($existAppmt){
            foreach ($existAppmt as $key => $value) {
                    $itemList=[];
                    $freqData=$this->CommonModel->getCollectionData('frequency',['_id'=>$value['frequency_id']],['time_in_mins']);
                    $itemList['appointment_id']=(string)$value['_id'];
                    $itemList['patient_id']=(string)$value['patient_id'];
                    $itemList['date']=$value['appointment_date'];
                    $itemList['type']=$value['appointment_type'][0];
                    $itemList['frequency']= $value['appointment_type'][0].' for '. $freqData[0]['time_in_mins']." mins";
                    $patient_data=getPatientData((string)$value['patient_id']);
                    $itemList['time']=$value['appointment_time'];
                    $itemList['patient_name']=$value['firstname'].' '.$value['lastname'];
                    $itemList['notes']=isset($value['notes'])?$value['notes']:'';
                    if($value['appointment_for']=='Self'){
                        $itemList['gender']=isset($patient_data['data']['gender'])?$patient_data['data']['gender']:0;
                        $itemList['dob']=isset($patient_data['data']['dob'])?$patient_data['data']['dob']:0;
                    }else{
                         $itemList['dob']=isset($value['dob'])?$value['dob']:0;
                        $itemList['gender']=isset($value['gender'])?$value['gender']:0;
                    }
                $addList[]=$itemList;

            }
       }
       return $addList;
    }

}






