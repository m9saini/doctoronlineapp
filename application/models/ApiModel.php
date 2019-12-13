<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ApiModel extends CI_Model {

    public function __construct($dbAddress='localhost')
    {
            $this->load->library('Mongo_db');
            $this->load->library('encryption');
            $this->load->library('encrypt');
            
    }

    
    
    public function schedule_status_list($type=null)
    {
        $satusListUpdated=[];
        if($type=='provider')
                    $satusList=explode(',',SCHEDULE_SLOT_STATUS);
        else
            $satusList=explode(',',SCHEDULE_SLOT_STATUS);
            foreach ($satusList as $key => $value) {

                //$dataList = $this->CommonModel->getCollectionData('patientAppointments', [$type.'status' =>$key ]);
                $satusListUpdated[]=['key_value'=>$key,'key_name'=>$satusList[$key]];
            }

            return $satusListUpdated;
     }

    public function appointment_status_list($type=null,$appointment_type=null)
    {
        $satusListUpdated=[];
        if($appointment_type){
            $satusList=explode(',',CAll_APPOINTMENT_STATUS_LIST);
            foreach ($satusList as $key => $value) {
                    $satusListUpdated[]=['key_value'=>$key,'key_name'=>$satusList[$key]];
                }

        }else{
            if($type=='provider')
                        $satusList=explode(',',APPOINTMENT_PROVIDER_STATUS_LIST);
            else
                $satusList=explode(',',APPOINTMENT_PATIENT_STATUS_LIST);
                foreach ($satusList as $key => $value) {
                    $satusListUpdated[]=['key_value'=>$key,'key_name'=>$satusList[$key]];
                }
        }

            return $satusListUpdated;
     }


    public function services_list($ids,$type=null){
        $result=[];
        if(is_array($ids)){

            try{
            foreach ($ids as $key => $value) { 
                
            $wheres=['_id'=> $value,'status'=>1];
            if($type=='Free'){
                $wheres['type']='Free';
            }
            
            $data= $this->CommonModel->getCollectionData('providerServices',$wheres,['name']);
            if(isset($data[0])) $result[]=$data[0];

        }
        return $result;
        }catch(MongoException $ex){
            return $result;
        }
    }
        else{
            return $result;
        }

    }

    public function speciality_list($ids=array()){
        $result=[];
        if(is_array($ids) && count($ids)>0){

            try{
            foreach ($ids as $key => $value) { 
            $wheres=['_id'=> $value,'status'=>1];
            $data= $this->CommonModel->getCollectionData('speciality',$wheres,['name']);
            if(isset($data[0])) $result[]=$data[0];
        } 
        return $result;
        }catch(MongoException $ex){
            return $result;
        }
    }
        else{
            return $result;
        }

    }

    public function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

  /* public  function deg2rad(deg) {
        return deg * (Math.PI/180);
    } */


    
}
