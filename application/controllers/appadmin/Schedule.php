<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Schedule extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct()
    {
		parent::__construct();
		$this->load->helper(array('url','form'));
    	$this->output->set_template('main');
    	$this->load->library('session');  
    	$this->collection='providerSchedules';
    	$result=$this->session->userdata('admin');
		if(empty($result))
			redirect('/');
		else {
			$this->sessionData=$result;
		}  
        	
    }

	 
	public function schedule_list()
	{ 
		$data['provider_id']= $provider_id =$this->uri->segment(4);
		$list_startdate = $this->uri->segment(5);
		date_default_timezone_set($this->sessionData['timezone']);
        $data['startdate']=$data['enddate']=strtotime(date('m/d/Y'));
        if($list_startdate){
            $data['startdate']=$list_startdate;
        }
		$data['title']       = 'Provider Schedules list';
		$data['search_action']=0;
        $data['search_type']=0;
		$data['search_type_list']=$search_type_list = ["All","Audio","Chat","Video","Walkin"];
		try{
			if($provider_id){
				$wheres['userid']= $provider_id;
			}
			if($this->input->post()){

				$params= $this->input->post(); 
				if(isset($params['startdate']) && isset($params['enddate']) ) {

					$data['startdate'] 	= strtotime($params['startdate']);
					$data['enddate'] 	= strtotime($params['enddate']);
					if(isset($params['type']) && !empty($params['type']) && $params['type']!=0 )
	                	$wheres['type']=['$in'=>[$search_type_list[$params['type']]]];

					$data['search_type']=((isset($params['type']) && !empty($params['type']))?$params['type']:0);
				}

			}
	        $startdate=utc_date(date('Y-m-d',$data['startdate']),$this->sessionData['timezone'],true);
	        $enddate=utc_date(date('Y-m-d',$data['enddate']),$this->sessionData['timezone'],true);
	        $wheres['date']=['$gte'=>$startdate,'$lte'=>$enddate];
			$data['DataList']=$this->CommonModel->getCollectionData($this->collection,$wheres,[],['date'=>-1]);
			date_default_timezone_set($this->sessionData['timezone']);
		    $data['startdate']=date('m/d/Y',$data['startdate']);
		    $data['enddate']=date('m/d/Y',$data['enddate']);
			$this->load->view('admin/providers/schedule/list',$data);
		}catch (MongoException $ex) {
    		$data['heading']='Error';
            $data['message']=$ex->getMessage();
            $this->load->view('errors/cli/error_db',$data);
        }
	} 

	public function index()
	{ 
		$listing_type =$this->uri->segment(3);
		$listing_type=explode('-',$listing_type);
		$data['second_tab_link']=(isset($listing_type[1]) && $listing_type=='list')?$listing_type[1]:'';
		$data['provider_id']= $provider_id =$this->uri->segment(4);
		$list_startdate = $this->uri->segment(5);
		date_default_timezone_set($this->sessionData['timezone']);
        $data['startdate']=$data['enddate']=strtotime(date('m/d/Y'));
        if($list_startdate){
            $data['startdate']=$list_startdate;
        }
		$data['title']       = 'Provider Schedules list';
		try{
			if($this->input->post()){

				$params= $this->input->post(); 
				if(isset($params['startdate']) && isset($params['enddate']) ) {

					$data['startdate'] 	= strtotime($params['startdate']);
					$data['enddate'] 	= strtotime($params['enddate']);
				}

			}
			$schedule_search=['timezone'=>$this->sessionData['timezone'],'date'=>$data['startdate'],'userid'=>$provider_id,'list_type'=>'year','operate'=>'m'];
			$data['events']=$this->get_schedule_availability($schedule_search);
			if($data['events']){
				date_default_timezone_set($this->sessionData['timezone']);
			    $this->load->view('admin/providers/schedule/month',$data);
			}else{
				$data['heading']='Schedule';
	            $data['message']='You have no any schedule';
	            $this->load->view('errors/cli/error_db',$data);
			}
		}catch (MongoException $ex) {
    		$data['heading']='Error';
            $data['message']=$ex->getMessage();
            $this->load->view('errors/cli/error_db',$data);
        }
	}
	 /**
     * @param null
     * @function is used to get schedule availability and booking day
     * @return true/false
     */
	public function get_schedule_availability($params=array()){
		$objectList=[];
        $timeSlotList=[];
        date_default_timezone_set($this->sessionData['timezone']);
        if($params['list_type']=='months'){
	        $startdate=strtotime(date('Y-m-01',$params['date']).'-1 months');
	        $enddate=strtotime(date('Y-m-d',$params['date']).'+1 months');
	        $enddate=strtotime(date('Y-m-t',$enddate).'+1 days');
    	}else if($params['list_type']=='day'){
    		$startdate=strtotime(date('Y-m-d',$params['date']));
	        $enddate=strtotime(date('Y-m-d',$params['date']));
    	}else{
    		
    		$y=date('Y',$params['date']);
    		$startdate=strtotime(date($y.'-01-01'));
	        $enddate=strtotime(date($y.'-12-31'));
    	}
	   	$startdate=date('Y-m-d',$startdate);
    	$enddate=date('Y-m-d',$enddate);
        $startdateUTC = utc_date($startdate,$params['timezone'],true);
        $enddateUTC   = utc_date($enddate,$params['timezone'],true);
        if(isset($params['userid']))
        	$wheres['userid']= new \MongoId($params['userid']);
        $wheres['date']=['$gte'=>$startdateUTC,'$lt'=>$enddateUTC];
        $dataList = $this->CommonModel->getCollectionData("providerSchedules",$wheres ,[],['date'=>1],500);
        if($dataList){
        	$i=0;
        	$color=['Url'=>["backgroundColor"=> '#3c8dbc',"borderColor"=>'#3c8dbc'],
        			'Audio'=>["backgroundColor"=> '##3c8dbc',"borderColor"=>'##3c8dbc'],
        			'Video'=>["backgroundColor"=> '#00a65a',"borderColor"=>'#00a65a'],
        			'Chat'=>["backgroundColor"=> '#00c0ef',"borderColor"=>'#00c0ef'],
        			'Call'=>["backgroundColor"=> '#0073b7',"borderColor"=>'#0073b7'],
        			'Walkin'=>["backgroundColor"=> '#f39c12',"borderColor"=>'#f39c12']
        			];
            foreach ($dataList as $key => $value) { 
                $itemList=[];
                foreach ($value['type'] as $key=>$schedule_type){
                	$start=date('Y-m-d H:i:s',$value['schedule_time'][$schedule_type]['starttime']);
                	$end=date('Y-m-d H:i:s',$value['schedule_time'][$schedule_type]['endtime']);
                	$date=date('Y-m-d H:i:s',$value['date']);
                	$objectList[$i]=["allDay"=>false,'schedule_id'=>$value['_id']->{'$id'},'date'=> $date,"title"=> $schedule_type,"start"=> $start,"end"=> $end,"backgroundColor"=> $color[$schedule_type]['backgroundColor'],"borderColor"=> $color[$schedule_type]['borderColor']]; 
            	$i++;
                }
            }                            
        }
        return (count($objectList)>0)?json_encode($objectList):'';
	}

	public function ajax_list()
	{ 
		date_default_timezone_set($this->sessionData['timezone']);
		$event_type=$this->uri->segment(4);
		$event_obj=explode('-',$event_type);
		$provider_id=$this->uri->segment(5);
		$date=$this->uri->segment(6);
		if($event_obj[0]=='months'){
			$event_type='months';
	        $date=strtotime(date('Y-m-d',strtotime($date)));
    	}else if($event_obj[0]=='days'){
    		$event_type='days';
	        $date=strtotime(date('Y-m-d',strtotime($date)));
    	}else{
    		$date=strtotime(date('Y-m-d',strtotime($date)));
    	}
    	if($event_obj[1]=='p')
    		$event_value='-1 ';
    	else
    		$event_value='+1 ';
    	$date=strtotime(date('Y-m-d',$date).$event_value.$event_type);
        $date=date('Y-m-d',$date);
		$date   = utc_date($date,$this->sessionData['timezone'],true);
		$schedule_search=['timezone'=>$this->sessionData['timezone'],'date'=>$date,'userid'=>$provider_id,'list_type'=>$event_obj[0],'operate'=>$event_obj[1]];
		echo $data=$this->get_schedule_availability($schedule_search);
		die;
	}
	public function view()
	{
		$schedule_id= $this->uri->segment(4);
		$provider_id = $this->uri->segment(5);
		try{
		$result=$this->CommonModel->getCollectionData($this->collection,['userid'=> new MongoId($provider_id),'_id'=> new MongoId($schedule_id)]); 
			if($result){
				$data['title']      = 'Provider Schedule View';
				$data['schedule']= $result[0];
				$data['type']=$this->schedule_show($result[0]);
				$this->load->view('admin/providers/schedule/view',$data);
			}else{
			$data['heading']='Schedule';
            $data['message']='Provider Schedule not valid';
            $this->load->view('errors/cli/error_db',$data);
			}
		}catch (MongoException $ex) {
    		$data['heading']='Error';
            $data['message']=$ex->getMessage();
            $this->load->view('errors/cli/error_db',$data);
        }
	}

	public function model_slot()
	{
		$params = $this->input->post();
		if(count($params)>0){
			try{
			$result=$this->CommonModel->getCollectionData($this->collection,['type'=>['$in'=>[$params['type']]],'_id'=> new MongoId($params['schedule_id'])]); 
				if($result){
					$data['title']      = 'Provider Schedule Time Slot';
					$data['schedule']= $result[0];
					$result[0]['type']=[$params['type']];
					echo $this->schedule_show($result[0]);
				}else{
				
	            echo '';
				}
			}catch (MongoException $ex) {
	    		
	            echo '';
	        }
	    }else
	    echo '';
	    die;
	}
	public function schedule_show($value){

		$objectList=[];
		foreach ($value['type'] as $key => $type) {
	            $type_set=$type;
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
	            $objectList[$type]=$timeSlotAddList;
    	} 
    	return (count($objectList)>0)?json_encode($objectList):'';
	}
   
   public function schedule_slot_list($scheduledata=array() ,$starttime=null,$endtime=null,$timeSlot=null,$type=null)
    {
        date_default_timezone_set($this->sessionData['timezone']);
        $timeSlotListAdd= $itemList= [];
        for($i=$starttime;$i< $endtime;)
        { 
                $timeSlotListAdd=[];
                $timeSlotListAdd['appointment_time']=date('h:i a',$i);
                if(in_array($i,$scheduledata['disable_schedule_time'][$type])){
                    $timeSlotListAdd['appointment_status']=1;
                }
                else if(in_array($i,$scheduledata['booking_time'][$type])){
                    $timeSlotListAdd['appointment_status']=2;
                    $appointment=$this->CommonModel->getCollectionData('patientAppointments',['appointment_time'=>$i],['_id']);
                    if($appointment)
                    	$timeSlotListAdd['appointment_id']=(string)$appointment[0]['_id'];
                }
                else{
                    $timeSlotListAdd['appointment_status']=0;
                }

                $i=$i+($timeSlot*60);
                $itemList[]=$timeSlotListAdd;
                
        }
        return $itemList;
    }
}
