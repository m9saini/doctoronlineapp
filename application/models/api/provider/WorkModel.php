<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class WorkModel extends CI_Model {

    public function __construct($dbAddress='localhost')
    {
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
             
            if(isset($params['work_id']) && $edit==1){
                $result=$this->mongo_db->where(array('_id'=>new \MongoId($params['work_id'])))->set($params)->update('providerWorks');
                return ($result)?$params:null;
            }else {
                    $posts =  $this->mongo_db->insert('providerWorks', $params);
                    print_r($posts);die;
                    $work_id=$posts->{'$id'};
                    return ($work_id) ? array('work_id'=>$work_id): null;
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
    public function workList($params)
    {
        try{ $id='5a6965b779f969da119fbfc1';

        if($id) $params['_id']= new MongoId($id);
        $this->mongo_db->wheres     =   $params;
        $this->mongo_db->selects     =  $selects=array();
        $data=$this->mongo_db->get('providerWorks');
        return ($data) ? $data : null;

        $resultData  =  getParovidersData($params['userid']);
        if ($resultData['status']) {
            $this->mongo_db->wheres     =   $params;
            $this->mongo_db->selects     =   array('name','from','to','completed');

            $data=$this->mongo_db->get('providerWorks');
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
    public function checkWorkId($params=array(),$id=NULL)
    {
        try{
            $collection='providerWorks';
            $resultData=[];
            if(count($where)>0 && !empty($id)){ 
                $where['_id']= new MongoId($id);
            }
            $resultData  = $this->mongo_db->get_where($collection,$where);
            return ($resultData) ? $resultData[0] : null;
        } catch (MongoException $ex) {

            return null;
        } 
    }

}
