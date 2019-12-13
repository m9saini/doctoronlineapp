<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TestModel extends CI_Model {

    public function __construct($dbAddress='localhost')
    {
            $this->load->library('encryption');
            $this->load->library('encrypt');
    }

     /**
     * @param $params is an array
     * @function is used to inser and update in collection
     * @return bool|null
     */
    public function upsert($collection,$params,$id=NULL,$update=NULL)
    {
        try{
        		$resultData = 0;
            if($update){
            	if($id)
                $resultData=$this->mongo_db->where(array('_id'=>new \MongoId($id)))->set($params)->update($collection);
                return ($resultData==1)?$id:null;
            }else {
                    $this->mongo_db->insert($collection, $params);
                    return (isset($params['_id'])) ?$params['_id']->{'$id'}: null;
            }

        } catch (MongoException $ex) {

            return null;
        } 
    }

    /**
     * @param string vlaue given 
     * @function is used to get collections Data 
     * @return bool|null 
     */
    public function getCollectionData($collection,$wheres=array(),$id=NULL,$selects=array(),$limit=NULL,$sort=NULL)
    {
        if($id) $wheres['_id']= new MongoId($id);
	        $this->mongo_db->wheres    =  $wheres;
	        $this->mongo_db->selects   =  $selects;
	        $this->mongo_db->limit     =  $limit;
	        $this->mongo_db->sort      =  $sort;
	        $data=$this->mongo_db->get($collection);
        return ($data) ? $data : null;
    }
    
    /**
     * @param string vlaue given 
     * @function is used to already any value of docuemnt   
     * @return bool|null 
     */
    public function alreadyExists($collection,$wheres=array(),$id=0)
    {
        $this->mongo_db->wheres    = $wheres;
        $data=$this->mongo_db->get($collection); 
        if(!empty($id) && !empty($data) && $data[0]['_id']->{'$id'}!=$id){  
            return false;
        } else {
        return (!empty($data) && empty($id)) ? false : true ;
        }
    }
}
