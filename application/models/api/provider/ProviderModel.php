<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class ProviderModel extends CI_Model
{

    public function __construct($dbAddress = 'localhost')
    {
        $this->load->library('Mongo_db');
        $this->load->library('encryption');
    }

    /**
     * @param $params is an array
     * @function is used to save provider signup form data
     * @return boolean
     */
    public function signup($params)
    {

        $data = [];

        if (is_array($params)) {
            $date=strtotime(date('Y-m-d H:i:s'));
            if(isset($params['timezone'])){
                date_default_timezone_set($params['timezone']);
                $date=strtotime(date('Y-m-d H:i:s'));
                $params['dob']=strtotime(date('Y-m-d',$params['dob']));
                $timezone=$params['timezone'];
            }else{
                $timezone="UTC";
            }
            $date=strtotime(date('Y-m-d H:i:s',$date));
            $params['dob']=utc_date(date('Y-m-d',$params['dob']),$timezone,true);
            $data = [
                "sufix" => "Dr.",
                "firstname" => $params['firstname'],
                "lastname" => $params['lastname'],
                "email" => $params['email'],
                "password" => md5($params['password']),
                "dob" => $params['dob'],
                "gender" => $params['gender'],
                "mobile" => (string)$params['mobile'],
                "country_code" => $params['country_code'],
                "zipcode" => (isset($params['zipcode']))?$params['zipcode']:"",
                "social_type" => '',
                "social_token_id" => '',
                "device_type" => $params['device_type'],
                "device_token" => $params['device_token'],
                "device_id" => $params['device_id'],
                "longitude" => $params['longitude'],
                "latitude" => $params['latitude'],
                "location"=>['type'=>"Point",'coordinates'=>[$params['longitude'],$params['latitude']]],
                "email_status" => 0,
                "mobile_status"=>0,
                "status" => 1,
                "language"=>[],
                "about"=>"",
                "imr"=>["registeration_number"=>'', "state_medical_council"=>'', "date_of_registeration"=>'', "year_of_info"=>'', "UPRN"=>''],
                "adhar_number"=>'',
                "isBlackList"=>'',
                "Enabled"=>'',
                "deleted" =>'',
                "created" => $date
            ];
            $posts = $this->mongo_db->insert('providers', $data);
            if($posts){
            $quickBlox['quickblox_info']=['email'=>(string)$posts.QUICKBLOX_PROVIDER_EMAIL_EXTENSION,'password'=>QUICKBLOX_USER_PASSWORD,'quickblox_id'=>''];
            $this->mongo_db->where(array('_id' => $posts))->set($quickBlox)->update('patients');
            return $posts;
            }else{
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param $params is an array
     * @function is used to provider signin
     * @return boolean
     */
    public function login($params)
    {
        if ($params) {
            $data = [
                'email' => $params['email'],
                'password' => md5($params['password']),
            ];

            $result = $this->mongo_db->where($data)->set(array('device_id' => $params['device_id'], 'device_type' => $params['device_type'], 'device_token' => $params['device_token']))->update('providers');

            if ($result) {

                $posts = $this->mongo_db->get_where('providers', $data);
                return ($posts) ? $posts : null;

            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param $params is an array
     * @function is used to update provider profile
     * @return bool|null
     */
    public function update_profile($params)
    {
        $data = [];
        $resultData = getProviderData($params['userid']);
        if ($resultData['status']) {
            $data = [
                "firstname" => $params['firstname'],
                "lastname" => $params['lastname'],
                "dob" => $params['dob'],
                "gender" => isset($params['gender'])?$params['gender']:"",
                "language" => isset($params['language'])?$params['language']:[],
                "country_code" => $params['country_code'],
                "latitude" => $params['latitude'],
                "longitude" => $params['longitude'],
                "street_add" => isset($params['street_add'])?$params['street_add']:'',
                "location"=>['type'=>"Point",'coordinates'=>[$params['longitude'],$params['latitude']]],
                "city" => $params['city'],
                "state" => $params['state'],
                "zipcode" => $params['zipcode'],
                "updated" => time()
                
            ];
            
            $email_otp=0;
            if(isset($params['image']) && !empty($params['image']))
                $data["image"] = $params['image'] ;
            $this->mongo_db->where(array('_id' => new \MongoId($params['userid'])))->set($data)->update('providers');
            $result = $this->mongo_db->get_where('providers',array('_id' => new \MongoId($params['userid'])));
            if($result[0]['email_status']==1 && $params['email']!=$result[0]['email']){
                
                        $digits = 6;
                        $otp_or_sessionId = rand(pow(10, $digits-1), pow(10, $digits)-1);
                        $message= $otp_or_sessionId.' is your one time password(OTP) for email verification.';
                        send_email($params['email'],'Update Mobile','Welcome in Online Appointment <\br>'.$message);
                    $email_otp=$otp_or_sessionId;
                    if(isset($result[0]['change_data'])){
                    	if(isset($result[0]['change_data'][0]['mobile'])){
                    		$email_data[0]['mobile']= (string)$result[0]['change_data'][0]['mobile'];
                            $email_data[0]['mobile_otp']= (!empty($result[0]['change_data'][0]['mobile_otp']))?$result[0]['change_data'][0]['mobile_otp']:'';
                        }
                    	else
                    		$email_data[0]['mobile']= 0;

                    		$email_data[0]['email']=$params['email'];
                            $email_data[0]['email_otp']=$otp_or_sessionId;
                	} else{
                		$email_data=[['email'=>$params['email'],'mobile'=>0,'email_otp'=>(string)$otp_or_sessionId]];
                	}
                    $save_data['change_data']=$email_data;
                    $result[0]['change_data']=$email_data[0];
                    $this->mongo_db->where(array('_id' => new \MongoId($params['userid'])))->set($save_data)->update('providers');
            }
            $result = $this->mongo_db->get_where('providers',array('_id' => new \MongoId($params['userid']))); 
            $mobile_otp=0;
            if($result[0]['mobile_status']==1 && $params['mobile']!=$result[0]['mobile']){
               $mobile_otp=$otp_or_sessionId=send_sms('sent',$params['country_code'].$params['mobile']);
                 if(isset($result[0]['change_data'])){
                	if(isset($result[0]['change_data'][0]['email'])){
                		$mobile_data[0]['email']= $result[0]['change_data'][0]['email'];
                        $mobile_data[0]['email_otp']= (!empty($result[0]['change_data'][0]['email_otp']))?$result[0]['change_data'][0]['email_otp']:'';
                    }
                	else
                		$mobile_data[0]['email']= "";

                		$mobile_data[0]['mobile']=(string)$params['mobile'];
                        $mobile_data[0]['mobile_otp']=$otp_or_sessionId;
            	} else{
            		$mobile_data=[['mobile'=>$params['mobile'],'email'=>'','mobile_otp'=>(string)$otp_or_sessionId]];
            	}
                $save_data['change_data']=$mobile_data;
                $result[0]['change_data']=$mobile_data[0];
                $this->mongo_db->where(array('_id' => new \MongoId($params['userid'])))->set($save_data)->update('providers');
            } 
            $result = $this->mongo_db->get_where('providers',array('_id' => new \MongoId($params['userid']))); 
            if($email_otp){
                    $result[0]['change_data'][0]['email_status']=1;
                }
            if($mobile_otp){
                    $result[0]['change_data'][0]['mobile_status']=1;
                }
            $result[0]['email_status']=isset($result[0]['email_status'])?$result[0]['email_status']:0;
            $result[0]['mobile_status']=isset($result[0]['mobile_status'])?$result[0]['mobile_status']:0;
            $result[0]['about']=isset($result[0]['about'])?$result[0]['about']:"";
            $result[0]['image']=isset($result[0]['image'])? base_url() . 'assets/upload/providers/' . $result[0]['image']:"";
            if($result[0]['email_status']==0){
                $digits = 6;
                $otp_or_sessionId = rand(pow(10, $digits-1), pow(10, $digits)-1);
                $message= $otp_or_sessionId.' is your one time password(OTP) for email verification.';
                send_email($params['email'],'Update Mobile','Welcome in Online Appointment <\br>'.$message);
                //$otp_or_sessionId=send_sms('sent',$params['country_code'].$params['mobile']);
                $this->CommonModel->upsert('providers',['otp_or_sessionId'=>(string)$otp_or_sessionId],$params["userid"],true);
            }

            return ($result) ? $result[0] : null;
        } else {

            return null;
        }
    }

    /**
     * @param $email as string
     * @function is used to show provider dashboard data
     * @return bool|null
     */
    public function getDashboardData($userid)
    {
        $data = [];
        if ($userid != '' && $userid > 0) {
            try {
                $resultData = getProviderData($userid);
                if ($resultData['status']) {
                    //$noti_count=$this->mongo_db->count('notifications',array('to'=>$userid,'is_read'=>0));
                    $userid= new \MongoId($userid);
                    $inbox_search_unread=['to' =>['$elemMatch'=>['_id'=>$userid]] , 'status' => 0];
                    $data = [
                        "firstname" => $resultData['data']['firstname'],
                        "lastname" => $resultData['data']['lastname'],
                        "email" => $resultData['data']['email'],
                        "image" => $resultData['data']['image'],
                        "msg_count" => $this->mongo_db->where($inbox_search_unread)->count('messages'),
                        "noti_count" => $this->mongo_db->where(array('to' => $userid, 'status' => 0))->count('notifications')
                    ];

                    return ($data) ? $data : null;
                } else {
                    return null;
                }

            } catch (MongoException $ex) {

                return null;
            }
        } else {

            return null;
        }

    }


    /**
     * @param array value
     * @function is used to update patient settings
     * @return array/null
     */
    public function providerSetting($param)
    {
        try {
            $resultData = $this->mongo_db->get_where('patientSetting', array('userid' => new \MongoId($param['userid'])));
            if ($resultData) {
                if (count($param) > 0) {
                    $result = $this->mongo_db->where(array('userid' => new \MongoId($param['userid'])))
                        ->set($param)->update('providerSetting');
                    return ($result) ? $param : null;
                } else {
                    return $resultData;
                }
            } else {
                $resultData = getProviderData($param['userid']);
                if ($resultData['status']) {
                    $param['userid'] = new MongoId($param['userid']);
                    $posts = $this->mongo_db->insert('providerSetting', $param);
                    return ($posts) ? $param : null;
                } else {
                    return null;
                }
            }

        } catch (MongoException $ex) {

            return array('status' => false, 'message' => $ex->getMessage());
        }

    }

}
