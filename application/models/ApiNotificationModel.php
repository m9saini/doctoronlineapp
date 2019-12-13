<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ApiNotificationModel extends CI_Model {

    public function __construct($dbAddress='localhost')
    {
            $this->load->library('Mongo_db');
            $this->load->library('encryption');
            $this->load->library('encrypt');
    }

    
     /**
     * @param array value
     * @function is used to patient notification list and details  
     * @return array/null
     */
    public function notificationList($param,$type=NULL){
        try
        {
            $k=0;
            $imageDir=($type)?'providers':'users';
            $resultData =[];
            if(isset($param['notification_id'])){
                $resultData  =  $this->mongo_db->get_where('notifications', 
                                                     array('userid' => $param['userid'],
                                                            '_id'=> new \MongoId($param['notification_id'])));
            }else{
                $notificationList  =  $this->mongo_db->get_where('notifications', array('to' => $param['userid']));
                if(count($notificationList)>0){
                    foreach ($notificationList as $key => $value) { 
                        $messageFromProviders  =  ($imageDir=='providers')?checkValidUser($value['from']):getParovidersData($value['from']);
                        if($messageFromProviders['status']){
                            $provider_image=(isset($messageFromProviders['data']['image']))?base_url()."assets/upload/$imageDir/".$resultData['data']['image']:'';
                            
                            $resultData[$k]=  ['msg_from' => $value['from'],
                                             'image'    => $provider_image,
                                             'message'  => $value['message'],
                                             'is_read'  => $value['is_read'],
                                             'status'   => (($value['status']==1)?'Approved':($value['status']==0)?'Unapproved':'Decline'),
                                             'created'  => $value['created']
                                            ];
                        }
                     $k++;   
                    }
                }
            }
            return ($resultData)?$resultData:null; 
            
        } catch(MongoException $ex){

            return array('status'=>false,'message'=>$ex->getMessage());         
        }

    }
   
 /**
     * @param array value
     * @function is used to patient notification list and details  
     * @return array/null
     */
    public function add($params,$type=NULL){
        try
        {
            date_default_timezone_set($params['timezone']);
            $addNoti['to']= new \MongoId($params['to']);
            $addNoti['from']= new \MongoId($params['from']);
            $addNoti['message']=$params['message'];
            $addNoti['type']=$params['type'];
            $addNoti['created']=strtotime(date('Y-m-d H:i:s'));
            $addNoti['deleted']='';$add['updated']='';
            $addNoti['status']=0;
            $id=$this->mongo_db->insert('notifications', $addNoti);
            return isset($id)?(string)$id:null;
        } catch(MongoException $ex){

            return null;         
        }

    }
    
}
