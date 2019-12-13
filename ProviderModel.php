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

        if (!in_array(null, $params)) {
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
                "social_type" => '',
                "social_token_id" => '',
                "device_type" => $params['device_type'],
                "device_token" => $params['device_token'],
                "device_id" => $params['device_id'],
                "longitude" => $params['longitude'],
                "latitude" => $params['latitude'],
                "profile" => 0,
                "status" => 0,
                "deleted" => '',
                "created" => time()
            ];
            $posts = $this->mongo_db->insert('providers', $data);
            return ($posts) ? $posts : null;
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
                "gender" => $params['gender'],
                "mobile" => $params['mobile'],
                "language" => $params['language'],
                "country_code" => $params['country_code'],
                "latitude" => $params['latitude'],
                "longitude" => $params['longitude'],
                "street_add" => $params['street_add'],
                "city" => $params['city'],
                "state" => $params['state'],
                "zipcode" => $params['zipcode'],
                "updated" => time(),
                "image" => (isset($params['image']) && !empty($params['image'])) ? $params['image'] : '',
                "profile" => '1',
            ];

            $result = $this->mongo_db->where(array('_id' => new \MongoId($params['userid'])))->set($data)->update('providers');
            return ($result) ? $params : null;
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
                        "image" => (isset($resultData['data']['image']) && !empty($resultData['data']['image'])) ? $resultData['data']['image'] : '',
                        "msg_count" => $this->mongo_db->where(array('to' => $userid, 'is_read' => 1))->count('notifications'),
                        "noti_count" => $this->mongo_db->where(array('to' => $userid, 'is_read' => 0))->count('notifications')
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
