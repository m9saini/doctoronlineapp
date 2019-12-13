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
            }
            $date=strtotime(date('Y-m-d H:i:s',$date));
            $data = [
                "sufix" => "Dr.",
                "firstname" => $params['firstname'],
                "lastname" => $params['lastname'],
                "email" => $params['email'],
                "password" => md5($params['password']),
                "dob" => $params['dob'],
                "gender" => $params['gender'],
                "mobile" => $params['mobile'],
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
                "status" => 0,
                "language"=>"",
                "about"=>"",
                "deleted" => '',
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
        $resultData = getParovidersData($params['userid']);
        if ($resultData['status']) {
            $data = [
                "firstname" => $params['firstname'],
                "lastname" => $params['lastname'],
                "email" => $params['email'],
                "dob" => $params['dob'],
                "gender" => isset($params['gender'])?$params['gender']:0,
                "mobile" => $params['mobile'],
                "language" => isset($params['language'])?$params['language']:'',
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
               if(isset($params['image']) && !empty($params['image']))
                $data["image"] = $params['image'] ;
            $result= $this->mongo_db->where(array('_id' => new \MongoId($params['userid'])))->set($data)->update('providers');
            $result = $this->mongo_db->get_where('providers',array('_id' => new \MongoId($params['userid'])));
            $result[0]['email_status']=isset($result[0]['email_status'])?$result[0]['email_status']:0;
            $result[0]['mobile_status']=isset($result[0]['mobile_status'])?$result[0]['mobile_status']:0;
            $result[0]['about']=isset($result[0]['about'])?$result[0]['about']:"";
            $result[0]['image']=isset($result[0]['image'])? base_url() . 'assets/upload/providers/' . $result[0]['image']:"";
            if($result[0]['email_status']==0){
                send_email($params['email'],'test','test');
                send_sms($params['email'],'test');
                $result[0]['otp']=123456;
            }else{
                $result[0]['otp']=0;
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
                $resultData = getParovidersData($userid);
                if ($resultData['status']) {
                    //$noti_count=$this->mongo_db->count('notifications',array('to'=>$userid,'is_read'=>0));

                    $data = [
                        "firstname" => $resultData['data']['firstname'],
                        "lastname" => $resultData['data']['lastname'],
                        "email" => $resultData['data']['email'],
                        "image" => $resultData['data']['image'],
                        "msg_count" => $this->mongo_db->where(array('to' => $userid, 'status' => 0))->count('messages'),
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
                $resultData = getParovidersData($param['userid']);
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
