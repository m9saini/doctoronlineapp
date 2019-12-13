<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class PatientModel extends CI_Model
{

    public function __construct($dbAddress = 'localhost')
    {
        $this->load->library('Mongo_db');
        $this->load->library('encryption');
        $this->load->library('encrypt');

    }

    /**
     * @param $params is an array
     * @function is used to save signup form data
     * @return boolean
     */
    public function signup($params)
    {
        $key = bin2hex($this->encryption->create_key(16));
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
                "firstname" => $this->encrypt->encode($params['firstname'], $key),
                "lastname" => $this->encrypt->encode($params['lastname'], $key),
                "email" => $params['email'],
                "password" => md5($params['password']),
                "user_key" => $key,
                "dob" => $this->encrypt->encode($params['dob'], $key),
                "gender" => $this->encrypt->encode($params['gender'], $key),
                "mobile" => $params['mobile'],
                "country_code" => $params['country_code'],
                "social_type" => '',
                "social_token_id" => '',
                "device_type" => $params['device_type'],
                "device_token" => $params['device_token'],
                "device_id" => $params['device_id'],
                "longitude" => $params['longitude'],
                "latitude" => $params['latitude'],
                "location"=>['type'=>"Point",'coordinates'=>[$params['longitude'],$params['latitude']]],
                "zipcode" => (isset($params['zipcode']))?$this->encrypt->encode($params['zipcode'], $key):"",
                "email_status" => 0,
                "mobile_status"=>0,
                "status" => 1,
                "deleted" => '',
                "created" => $date
            ];
            $posts = $this->mongo_db->insert('patients', $data);
            if($posts){
            $quickBlox['quickblox_info']=['email'=>(string)$posts.QUICKBLOX_PATIENT_EMAIL_EXTENSION,'password'=>QUICKBLOX_USER_PASSWORD,'quickblox_id'=>''];
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
     * @function is used to signin
     * @return boolean
     */
    public function login($params)
    {
        if ($params) {
            $data = [
                'email' => $params['email'],
                'password' => md5($params['password']),
            ];

            $result = $this->mongo_db->where($data)->set(array('device_id' => $params['device_id'], 'device_type' => $params['device_type'], 'device_token' => $params['device_token']))->update('patients');

            if ($result) {
                $posts = $this->mongo_db->get_where('patients', $data);
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
     * @function is used to update user profile
     * @return bool|null
     */
    public function update_profile($params)
    {
        $data = [];
        $resultData = getPatientData($params['userid']);
        if ($resultData['status']) {
            $key = $resultData['data']['user_key'];
            $data = [
                "firstname" => $this->encrypt->encode($params['firstname'], $key),
                "lastname" => $this->encrypt->encode($params['lastname'], $key),
                "dob" => $this->encrypt->encode($params['dob'], $key),
                "gender" => (isset($params['gender']))?$this->encrypt->encode($params['gender'], $key):'',
                "country_code" => $params['country_code'],
                "street_add" => (isset($params['street_add']) && !empty($params['plot_unit_no']) )?$this->encrypt->encode($params['street_add'], $key):'',
                "plot_unit_no" => (isset($params['plot_unit_no']) && !empty($params['plot_unit_no']))?$this->encrypt->encode($params['plot_unit_no'], $key):'',
                "city" => (isset($params['city']))?$this->encrypt->encode($params['city'], $key):'',
                "state" => (isset($params['state']))?$this->encrypt->encode($params['state'], $key):'',
                "zipcode" => (isset($params['zipcode']))?$this->encrypt->encode($params['zipcode'], $key):'',
                "updated" => time(),
                "location"=>['type'=>"Point",'coordinates'=>[$params['longitude'],$params['latitude']]],
            ];
            if(isset($params['image']) && !empty($params['image'])){
                $data["image"] = $this->encrypt->encode($params['image'], $key) ;
            }
            $this->mongo_db->where(array('_id' => new \MongoId($params['userid'])))->set($data)->update('patients');
            $email_otp=0;            
            $mobile_otp=0;
            $result = $this->mongo_db->get_where('patients',array('_id' => new \MongoId($params['userid'])));
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
               // $result[0]['change_data']=$mobile_data[0];
                $this->mongo_db->where(array('_id' => new \MongoId($params['userid'])))->set($save_data)->update('patients');
            } 

            $result = $this->mongo_db->get_where('patients',array('_id' => new \MongoId($params['userid']))); 
            
            if($result[0]['email_status']==1 && $params['email']!=$result[0]['email']){
                        $digits = 6;
                        $otp_or_sessionId = rand(pow(10, $digits-1), pow(10, $digits)-1);
                        $message= $otp_or_sessionId.' is your one time password(OTP) for email verification.';
                        send_email($params['email'],'Update Mobile','Welcome in Online Appointment.'.$message);
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
                   // $result[0]['change_data']=$email_data[0];
                    $this->mongo_db->where(array('_id' => new \MongoId($params['userid'])))->set($save_data)->update('patients');
            }
            $result = $this->mongo_db->get_where('patients',array('_id' => new \MongoId($params['userid'])));
           
            $result[0]['email_status']=isset($params['email_status'])?$params['email_status']:0;
            $result[0]['mobile_status']=isset($params['mobile_status'])?$params['mobile_status']:0;
            $result[0]['image']=isset($result[0]['image'])? base_url() . 'assets/upload/patients/' . $result[0]['image']:"";
            if($result[0]['email_status']==0){
                $digits = 6;
                $otp_or_sessionId = rand(pow(10, $digits-1), pow(10, $digits)-1);
                $message= $otp_or_sessionId.' is your one time password(OTP) for email verification.';
                send_email($params['email'],'Update Mobile','Welcome in Online Appointment. '.$message);
                //$otp_or_sessionId=send_sms('sent',$params['country_code'].$params['mobile']);
                $this->CommonModel->upsert('patients',['otp_or_sessionId'=>(string)$otp_or_sessionId],$params["userid"],true);
            }else{
                $result[0]['otp']=0;
            }
            if($result) {
                  $userdata   =   getPatientData($params['userid']);
                  if($email_otp){
                        $userdata['data']['change_data'][0]['email_status']=1;
                    }
                    if($mobile_otp){
                        $userdata['data']['change_data'][0]['mobile_status']=1;
                    }
                   unset($userdata['data']['user_key']);
                   return $userdata['data'];
             } else{
                return null;
             }
        } else {

            return null;
        }
    }

    /**
     * @param $email as string
     *
     * @return bool|null
     */
    public function getDashboardData($userid)
    {
        $data = [];
        $resultData = getPatientData($userid);
        if ($resultData['status']) {
            try {
                $userid= new \MongoId($userid);
                $inbox_search_unread=['to' =>['$elemMatch'=>['_id'=>$userid]] , 'status' => 0];
                $data = [
                    "firstname" => $resultData['data']['firstname'],
                    "lastname" => $resultData['data']['lastname'],
                    "email" => $resultData['data']['email'],
                    "image" => (isset($resultData['data']['image']) && !empty($resultData['data']['image'])) ?$resultData['data']['image'] : '',
                    "msg_count" => $this->mongo_db->where($inbox_search_unread)->count('messages'),
                    "noti_count" => $this->mongo_db->where(array('to' => $userid, 'status' => 0))->count('notifications')
                ];
                return ($data) ? $data : null;

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
    public function patientSetting($param)
    {
        try {
            $resultData = $this->mongo_db->get_where('patientSetting', array('userid' => $param['userid']));
            if ($resultData) {
                if (count($param) > 0) {
                    $result = $this->mongo_db->where(array('userid' => $param['userid']))
                        ->set($param)->update('patientSetting');
                    return ($result) ? $param : null;
                } else {
                    return $resultData;
                }
            } else {
                $resultData = getPatientData($param['userid']);
                if ($resultData['status']) {
                    $settinData = array('booking_status' => 1, 'medi_spel_status' => 1);
                    $posts = $this->mongo_db->insert('patientSetting', $settinData);
                    return ($posts) ? $settinData : null;
                } else {
                    return null;
                }
            }

        } catch (MongoException $ex) {

            return array('status' => false, 'message' => $ex->getMessage());
        }

    }


}
