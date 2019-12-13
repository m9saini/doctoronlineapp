<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ApiEducationModel extends CI_Model {

    public function __construct($dbAddress='localhost')
    {
            $this->load->library('Mongo_db');
            $this->load->library('encryption');
     }

    /**
     * @param $params is an array
     * @function is used to inser and update provider education
     * @return bool|null
     */
    public function upsert($params,$edit=null)
    {
        try{
            if(isset($params['education_id']) && $edit==1){
                //$this->checkEducationID($params['education_id']);
                $result=$this->mongo_db->where(array('_id'=>new \MongoId($params['education_id'])))->set($params)->update('providerEducations');
                return ($result)?$params:null;
            }else {
                    $posts =  $this->mongo_db->insert('providerEducations', $params);
                    $education_id=$posts->{'$id'};
                    return ($education_id) ? array('education_id'=>$education_id): null;
            }

        } catch (MongoException $ex) {

            return null;
        } 
    }

    /**
     * @param $array
     * @function is used to get list of education
     * @return bool|null 
     */
    public function educationList($params)
    {
        try{
        $resultData  =  getParovidersData($params['userid']);
        if ($resultData['status']) {
            $data=$this->mongo_db->select(array('name','from','to','completed'))->get_where('providerEducations',array('userid'=>$params['userid']));
            return ($data) ? $data : null;
        } else {
        return null;
        }

        } catch (MongoException $ex) {

            return null;
        } 
    }

    /**
     * @param $email as string
     * @function is used to get list of education
     * @return bool|null 
     */
    public function checkEducationId($id)
    {
        try{
            
            $resultData  = $this->mongo_db->get_where('providerEducations',array('_id'=> new MongoId($id)));
            return ($resultData) ? $data : null;

        } catch (MongoException $ex) {

            return null;
        } 
    }

}
