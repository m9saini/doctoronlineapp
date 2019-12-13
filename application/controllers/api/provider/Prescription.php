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
class Prescription extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->collection='patientAppointments';
    }

    /**
     * @param array
     * @function is used to insert,update ,delete,list of Laboratory Test 
     * @return true/false
     */
    public function upsert_post()
    {
        $response = [];
        $params = $this->post();
        $url=explode('-',$this->uri->segment(2)); 
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id','timezone']; //notes not require
        if(isset($url[2]) && in_array($url[2], array('add','edit'))){
        	array_push($required, 'name_of_test','date');
        	if($url[2]=='edit'){
        		array_push($required, 'obj_id');
        	}
        }
        if(isset($url[2]) && in_array($url[2], array('delete')))
        		array_push($required, 'obj_id');
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && isset($url[2]) && in_array($url[2], array('add','edit','delete','list'))) { 

        	$update=($url[2]=='edit')?true:false;
        	$params['date']=(int)$params['date'];
        	$wheres['provider_id'] = new \MongoId($params['userid']);
            $wheres['_id']= new MongoId($params['appointment_id']);
            if(isset($params['obj_id']) && $url[2]=='edit'){
                       		$obj_id= new \MongoId($params['obj_id']);
							$wheres['prescriptions.laboratory_test']=['$elemMatch'=>['_id'=>$obj_id]];
			}
            $Exists = $this->CommonModel->getCollectionData($this->collection,$wheres);
            if($Exists){
            	$Exists=$Exists[0];
            	date_default_timezone_set($params['timezone']);
            	$test_date=strtotime(date('Y-m-d H:i:s'));
                try{
                       
                	if($url[2]=='delete'){
                        $response["status"] = 1;
                        $response['message'] = 'Successfully laboratory test deleted.';
                        $obj_id= new \MongoId($params['obj_id']);
                            $set=['prescriptions.laboratory_test.$.deleted'=>$test_date,
                                  'prescriptions.laboratory_test.$.updated'=>$test_date
                                 ];
                        $rs=$this->CommonModel->elementUpdate($this->collection,$wheres,$set,'pull');
                        pr($rs);
                		$this->response($response);
                	}
                	if($url[2]=='list'){
                		
                		$response["status"] = 1;
                        $response['message'] = 'Successfully laboratory test list';
                        $response['data']['laboratory_test']=(isset($Exists['prescriptions']['laboratory_test']))?$Exists['prescriptions']['laboratory_test']:[];
                		$this->response($response);
                	}
                       if(isset($params['obj_id']) && $url[2]=='edit'){
                       		$obj_id= new \MongoId($params['obj_id']);
                        	$set=['prescriptions.laboratory_test.$.name'=>$params['name_of_test'],
                        		  'prescriptions.laboratory_test.$.date'=>$params['date'],
                        		  'prescriptions.laboratory_test.$.notes'=>(isset($params['notes']))?$params['notes']:'',
                        		  'prescriptions.laboratory_test.$.updated'=>$test_date
                        		 ];
                        	$dataResult=$this->CommonModel->elementUpdate($this->collection,$wheres,$set);
                        }else{
                        	$obj_id= new MongoId();
                        	$item=["_id"=>$obj_id,'name'=>$params['name_of_test'],'date'=>$params['date'],'created'=>$test_date,'deleted'=>'','updated'=>'','status'=>1,'notes'=>(isset($params['notes']))?$params['notes']:''];

                            if(isset($Exists['prescriptions'])){
                            	if(isset($Exists['prescriptions']['laboratory_test']) && count($Exists['prescriptions']['laboratory_test'])>0){
	                            	$add[]=$item;
	                            	$laboratory_test['laboratory_test']=array_merge($Exists['prescriptions']['laboratory_test'],$add);
	                        	}else{
	                        		$laboratory_test['laboratory_test'][]=$item;
	                        	}
	                        	$set['prescriptions']=array_merge($Exists['prescriptions'],$laboratory_test);
	                        }else{
	                        	$set['prescriptions']['laboratory_test'][]=$item;
	                        }
                        $dataResult = $this->CommonModel->upsert($this->collection,$set,$params['appointment_id'],true);
                        }
                    if ($dataResult) {
                        $response["status"] = 1;
                        $response['message'] = 'Successfully Prescription '.(($update)?'updated':'added');
                        $response['data'] = array('laboratory_test_id'=>(string)$obj_id);
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Prescription not '.(($update)?'updated':'added');
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid laboratory test id';
                } 
            }else{
                    $response["status"] = 0;
                    $response["message"] = 'Provider id does not exists.';
                }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

  /**
     * @param array
     * @function is used to insert,update ,delete,list of drugs 
     * @return true/false
     */
    public function drugs_post()
    {
        $response = [];
        $params = $this->post();
        $url=explode('-',$this->uri->segment(2)); 
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        $required=['userid','appointment_id']; 
        if(isset($url[1]) && in_array($url[1], array('add','edit'))){
        	array_push($required,'days','amount','method','frequency','instruction','rebills'); //medication ,notes
        	if($url[1]=='edit'){
        		array_push($required, 'obj_id');
        	}
        }
        if(isset($url[1]) && in_array($url[1], array('delete')))
        		array_push($required, 'obj_id');
        $validation = $this->CommonModel->validation($params,$required); 
        if(empty($validation) && isset($url[1]) && in_array($url[1], array('add','edit','delete','list'))) { 
        	$update=($url[1]=='edit')?true:false;
        	$wheres['provider_id'] = new \MongoId($params['userid']);
            $wheres['_id']= new MongoId($params['appointment_id']);
            if(isset($params['obj_id']) && $url[1]=='edit'){
                       		$obj_id= new \MongoId($params['obj_id']);
							$wheres['prescriptions.drugs']=['$elemMatch'=>['_id'=>$obj_id]];
			}
            $Exists = $this->CommonModel->getCollectionData($this->collection,$wheres);
            if($Exists){
            	$Exists=$Exists[0];
            	date_default_timezone_set($params['timezone']);
            	$test_date=strtotime(date('Y-m-d H:i:s'));
                try{
                       
                	if($url[1]=='delete'){

                		$this->response($response);
                	}
                	if($url[1]=='list'){
                		
                		$response["status"] = 1;
                        $response['message'] = 'Successfully laboratory test list';
                        $response['data']['drugs']=(isset($Exists['prescriptions']['drugs']))?$Exists['prescriptions']['drugs']:[];
                		$this->response($response);
                	}
                       if(isset($params['obj_id']) && $url[1]=='edit'){
                         	$set=['prescriptions.drugs.$.days'=>$params['days'],
                        		 'prescriptions.drugs.$.amount'=>$params['amount'],
                        		 'prescriptions.drugs.$.method'=>$params['method'],
                        		 'prescriptions.drugs.$.frequency'=>$params['frequency'],
                        		 'prescriptions.drugs.$.instruction'=>$params['instruction'],
                        		 'prescriptions.drugs.$.rebills'=>$params['rebills'],
                        		 'prescriptions.drugs.$.update'=>$test_date,
                        		 ];
                        	$dataResult=$this->CommonModel->elementUpdate($this->collection,$wheres,$set);
                        }else{
                        	$obj_id= new MongoId();
                        	$item=["_id"=>$obj_id,'days'=>$params['days'],'amount'=>$params['amount'],'method'=>$params['method'],'frequency'=>$params['frequency'],'instruction'=>$params['instruction'],'rebills'=>$params['rebills'],
                        		'created'=>$test_date,'deleted'=>'','updated'=>'','status'=>1,
                        		"notes"=>(isset($params['notes']))?$params['notes']:'',
                        		];
                        	if(isset($params['medication']))
                        		$item['medication']=$params['medication'];
                            if(isset($Exists['prescriptions'])) { 
                            	if(isset($Exists['prescriptions']['drugs']) && count($Exists['prescriptions']['drugs'])>0) {
	                            	$add[]=$item;
	                            	$drugs['drugs']=array_merge($Exists['prescriptions']['drugs'],$add);
	                            	
	                        	}else{
	                        		$drugs['drugs'][]=$item;

	                        	}
	                      	$set['prescriptions']=array_merge($Exists['prescriptions'],$drugs);
	                        }else{
	                        	$set['prescriptions']['drugs'][]=$item;
	                        }
                        	$dataResult = $this->CommonModel->upsert($this->collection,$set,$params['appointment_id'],true);
                        }
            
                    if ($dataResult) {
                        $response["status"] = 1;
                        $response['message'] = 'Successfully drugs '.(($update)?'updated':'added');
                        $response['data'] = array('drugs_id'=>(string)$obj_id);
                    } else {
                        $response["status"] = 0;
                        $response["message"] = 'Drugs not '.(($update)?'updated':'added');
                    }
                }catch (MongoException $ex) {

                    $response["status"] = 0;
                    $response["message"] = 'Invalid drugs id';
                } 
            }else{
                    $response["status"] = 0;
                    $response["message"] = 'Provider id does not exists.';
                }
        } else {
            $response["status"] = 0;
            $response["message"] = 'Mandatory fields are required.';
            $response["error_data"] = $validation;
        }
        $this->response($response);
    }

    
   
}
