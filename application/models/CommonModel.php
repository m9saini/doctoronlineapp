<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CommonModel extends CI_Model {

    public function __construct($dbAddress='localhost')
    {
            $this->load->library('encryption');
            $this->load->library('encrypt');
            $this->load->library('Form_validation');
    }

     /**
    @param $params is array
    @function is used to validate data from api
    @return array/false
    **/
    public function validation($params=array(),$required=array(),$otherRules=array())
    {
        if(is_array($params) && is_array($required)){
            $validation=[];
            foreach($required as $filedData){
                if(isset($otherRules[$filedData])){
                    if($otherRules[$filedData]=='MongoId'){

                      $validation[]=['field'=>$filedData.'[$id]','rules'=>'trim|required'];

                    }else{
                    $other=$otherRules[$filedData];
                    $validation[]=['field'=>$filedData.'[0]','rules'=>'trim|required'.$other];
                    }
                    
                }else{
                $validation[]=['field'=>$filedData,'rules'=>'trim|required'];
                }
            }
               //pr($validation); die;
                $this->form_validation->set_data($params);
                $this->form_validation->set_rules($validation);
                //pr($this->form_validation->run()); die;
                //pr($this->form_validation->error_array()); die;
            if($this->form_validation->run()==FALSE){
                //$error=[];
                $error=$this->form_validation->error_array();
                $error['error_message']='Mandatory fields are required.' ;
                //$error_data[]=$error;
                return $error; //_data;
            }
            else{
                return false;
            }
        }else{
            $error['error_message']='Please valid Josn Data.' ;
            return $error;
        }
    }

     /**
     * @param $params is an array
     * @function is used to insert and update in collection
     * @return bool|null
     */
    public function upsert($collection,$params=array(),$id=NULL,$update=NULL)
    {
        try{

        	$params['deleted']='';$params['updated']='';
          $resultData = 0;
            if(isset($params['timezone'])){
                date_default_timezone_set($params['timezone']);
                unset($params['timezone']);
            }

            if($update){
                $params['updated']=strtotime(date('Y-m-d H:i:s'));
            	if($id)
                    $resultData=$this->mongo_db->where(array('_id'=>new \MongoId($id)))->set($params)->update($collection);
                  return ($resultData==1)?$id:null;
            }else {
               $params['status']=(isset($params['status']) && $params['status']==1)?1:0;
                    $params['created']=strtotime(date('Y-m-d H:i:s'));
                    $this->mongo_db->insert($collection,$params);
                    return (isset($params['_id'])) ?$params['_id']->{'$id'}: null;
            }

        } catch (MongoException $ex) {

            return null;
        } 
    }

     /**
     * @param $params is an array
     * @function is used to insert  multipal documents in collection
     * @return bool|null
     */
    public function batchInsert($collection,$insert)
    {
        try{
            if(count($insert)>0){
                    $resultData=$this->mongo_db->batch_insert($collection,$insert);
                return (count($resultData)>0) ? true :null;
            }else {
                    return null;
            }

        } catch (MongoException $ex) {

            return null;
        } 
    }

    public function elementUpdate($collection,$wheres=array(),$set=array())
    {
      try{
        $result=$this->mongo_db->where($wheres)->set($set)->update($collection);
        return ($result)?true:false;
      }catch (MongoException $ex) {

            return null;
        } 
    }

     /**
     * @param $params is an array
     * @function is used to update all document .
     * @return bool|null
     */
    public function updateAll($collection=NULL, $wheres=array(),$update=array())
    {
        try{
            $resultData = 0;
            if(count($wheres)>0 && count($update)>0 ){
                $wheres['deleted']='';
                $this->mongo_db->wheres    = $wheres;
                $this->mongo_db->updates   = $update;
                $resultData=$this->mongo_db->update_all($collection,$update);
           }
        return ($resultData==1)?true:null;

        } catch (MongoException $ex) {

            return null;
        } 
    }

     /**
     * @param $params is an array
     * @function is used to insert  mutipal documents in collection
     * @return bool|null
     */
    public function callAggregate($collection,$type,$wheres=array(),$sort=1,$limit=0,$offset=0)
    {
        try{
            $wheres['deleted']=(isset($wheres['deleted']))?$wheres['deleted']:'';
            $this->mongo_db->offset    =  $offset;
            $this->mongo_db->limit     =  $limit;
            $this->mongo_db->sorts     =  $sort;
            $count = $this->mongo_db->where($wheres)->$type($collection);
            return ($count);
        } catch (MongoException $ex) {

            return null;
        } 
    }

    
    /**
     * @param $params is an array
     * @function is used to social signin
     * @return boolean
     */
    public function updateDevice($collection ='',$wheres= array(), $updatedata)
    {
        $posts  =   '';
        if(!empty($updatedata) && !empty($wheres) && $collection){
            $resultData =   $this->mongo_db->where($wheres)->set($updatedata)->update($collection);
            if($resultData) {
                $posts = $this->mongo_db->get_where($collection, $wheres);
            }
            return ($posts)? $posts:null;
        } else {
            return null;
        }
    }

    /**
     * @param $params is an array
     * @function is used to social signin
     * @return boolean
     */
    public function login($collection,$wheres)
    {
        if($wheres){
            $posts =  $this->mongo_db->get_where($collection,$wheres);
            return ($posts)? $posts:null;
        } else {
            return null;
        }
    }

    /**
     * @param string vlaue given 
     * @function is used to get collections Data 
     * @return bool|null 
     */
    public function getCollectionData($collection, $wheres=array(), $selects=array(), $sort=array(), $limit = 20, $offset=0)
    {
     //   echo $testlimt;die;
        try{
          $wheres['deleted']='';
	        $this->mongo_db->wheres    =  $wheres;
	        $this->mongo_db->selects   =  $selects;
            $this->mongo_db->offset    =  $offset;
            $this->mongo_db->limit     =  $limit;
	        $this->mongo_db->sorts     =  $sort;
	        $data=$this->mongo_db->get($collection);
        return ((!empty($data)) && isset($data[0])) ? $data :null;
        }catch (MongoException $ex) {

            return null;
        } 
    }
    
    /**
     * @param string vlaue given 
     * @function is used to get collections Data 
     * @return bool|null 
     */
    public function getMultipalCollectionsData($collection=array(), $wheres=array(), $selects=array())
    {
     //   echo $testlimt;die;
        try{
          $data=null;
          if(is_array($collection)){
            foreach ($collection as $key => $value) {

              $wheres['deleted']='';
              $this->mongo_db->wheres    =  $wheres[$key];
              $this->mongo_db->selects   =  $selects[$key];
              $result=$this->mongo_db->get($value);
              if ((!empty($result)) && isset($result[0])){
               $data[]=$result[0];
              }
            }
             
          }
        return ((!empty($data)) && isset($data[0])) ? $data :null;
        }catch (MongoException $ex) {

            return null;
        } 
    }
    
    /**
     * @param string vlaue given 
     * @function is used to get collections Data 
     * @return bool|null 
     */
    public function getCollectionList($collection, $wheres=array(), $selects=array(), $sort=array(), $limit = 20, $offset=0)
    {
     //   echo $testlimt;die;
        try{
          $wheres['deleted']='';
            $this->mongo_db->wheres    =  $wheres;
            $this->mongo_db->selects   =  $selects;
            $this->mongo_db->offset    =  $offset;
            $this->mongo_db->limit     =  $limit;
            $this->mongo_db->sorts     =  $sort;
            $data=$this->mongo_db->get($collection);
        return ((!empty($data)) && isset($data[0])) ? $data :null;
        }catch (MongoException $ex) {

            return null;
        } 
    }

    /**
     * @param string vlaue given 
     * @function is used to get collections Data 
     * @return bool|null 
     */
    public function getOr($collection, $and=array(), $or=array(), $sort=array(), $limit = 20, $offset=0)
    {
     //   echo $testlimt;die;
        try{
          $this->mongo_db->where_or($or)->get($collection);;
          $this->mongo_db->where($and)->get($collection);;
          $data=$this->mongo_db->get($collection);
          pr($data);die;
        return ((!empty($data)) && isset($data[0])) ? $data :null;
        }catch (MongoException $ex) {

            return null;
        } 
    }
    /**
     * @param string vlaue given 
     * @function is used to already any value of docuemnt   
     * @return bool|null 
     */
    public function alreadyExists($collection,$wheres=array(),$id=NULL,$deleted=NULL)
    {
        try{
            if($id) { 
                    $id=['_id'=>['$ne'=> new MongoId($id)]];
                    $wheres=array_merge($wheres,$id);
            }
            $this->mongo_db->wheres    = $wheres;
            $data=$this->mongo_db->get($collection); 
            if($deleted){
                  if(!empty($data)){
                    return (!empty($data[0]['deleted']) || $data[0]['status']!=1) ? true : $data[0]['_id']->{'$id'} ;
                  } else{
                    return false;
                  }
            } else{
                  return (!empty($data) && isset($data[0]['_id'])) ? $data[0]['_id']->{'$id'} : false ;
              }
        }catch (MongoException $ex) {

            return false;
        } 
    }

    /**
     * @param string vlaue given 
     * @function is used to already any value of docuemnt   
     * @return bool|null 
     */
    public function delete($collection,$wheres=array(),$timezone=null,$permanently=NULL)
    {
        try{
            $resultData=0;
            $this->mongo_db->wheres    = $wheres;
            if($permanently){

                if(in_array($permanently,["schedule","page"]))
                    $data=$this->mongo_db->delete($collection); 
                $data=1;
                return ($data) ? true : false ;
            }else{
                    $record=$this->mongo_db->get($collection);
                    if($record){
                        if($timezone){
                            date_default_timezone_set($timezone);
                        }
                        $softDelete['deleted']=strtotime(date('Y-m-d H:i:s'));
                        date_default_timezone_set("UTC");
                        $softDelete['deleted']=strtotime(date('Y-m-d H:i:s',$softDelete['deleted']));
                        $resultData=$this->mongo_db->where($wheres)->set($softDelete)->update($collection);
                        return ($resultData==1)?$record[0]['_id']->{'$id'} :false;
                    }
                    return false;
                }
        }catch(MongoException $e){
            return false;
        }
        
    }
    
    /**
     * @param string vlaue given 
     * @function is used to delete all documeant    
     * @return bool|null 
     */
    public function delete_all($collection,$wheres=array(),$permanently=NULL)
    {
        if(count($wheres)>0){
            try{
                
                if($permanently){
                    $this->mongo_db->wheres    = $wheres;
                    $data=$this->mongo_db->delete_all($collection); 
                    pr($data);die;
                    if(!empty($id) && !empty($data) && $data[0]['_id']->{'$id'}!=$id){  
                        return false;
                    } else {
                    return (!empty($data) && empty($id)) ? false : true ;
                    }

                }else{
                    $softDelete['deleted']=strtotime(date('Y-m-d H:i:s'));
                     $wheres=$this->getCollectionData($collection,array(),NULL,array('id'));
                    $this->mongo_db->wheres= array('_id'=>$wheres);
                    $this->mongo_db->updates=$softDelete;
                    $resultData=$this->mongo_db->update_all($collection);
                    pr($resultData);die;
                return ($resultData==1)?$id:null;
                        
                    }
            }catch(MongoException $e){
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * @param string vlaue given 
     * @function is used to get collections Data 
     * @return bool|null 
     */
    public function getDeletedDocument($collection,$field,$wheres=array(),$selects=array(),$limit=NULL,$sort=NULL)
    {
        $this->mongo_db->selects   =  $selects;
        $this->mongo_db->limit     =  $limit;
        $this->mongo_db->sort      =  $sort;
        $data=$this->mongo_db->where_not_in($field, $wheres)->get($collection);
        return ($data) ? $data : null;
    }

    public function joinCollectionData($collection,$match=array()){

        $rst= $this->mongo_db->aggregate($collection,$match);
        return (!empty($rst) && isset($rst['ok']) && $rst['ok']==1)?$rst['cursor']:null;

    }

    
    
}
