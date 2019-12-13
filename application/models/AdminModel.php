<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AdminModel extends CI_Model {

        public function __construct($dbAddress='localhost')
        {
                $this->load->library('Mongo_db');
        }

        public function apppointment_view($app_id)
        {
            $provider=[];
            $wheres['_id']= new MongoId($app_id);
            $app_view= $this->CommonModel->getCollectionData($this->collection,$wheres);
            $data['appointment_view']=$app_view[0];
            $p_info=getPatientData((string)$app_view[0]['patient_id']);
            $data['patient_info']=($p_info)?$p_info['data']:[];
            if(isset($app_view[0]['provider_id'])){
                $provider=getProviderData((string)$app_view[0]['provider_id']);
                $provider=($provider['status'])?$provider['data']:[];
            }
            $data['provider_info']=$provider;
            return $data;
        }
}
