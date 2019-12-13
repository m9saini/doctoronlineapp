<?php
if (!function_exists('is_request')) {
    function is_request()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST' ? TRUE : FALSE;
    }
}

if (!function_exists('is_post')) {
    function is_post($print_post = null)
    {
        if (!empty($print_post)) {
            pr($print_post);
            //exit;
        }
        return $_SERVER['REQUEST_METHOD'] == 'POST' ? TRUE : FALSE;
    }
}

if (!function_exists('is_ajax')) {
    function is_ajax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            ? TRUE : FALSE;
    }
}

if (!function_exists('_input_post')) {

    function _input_post($key = null)
    {
        $CI = &get_instance();
        return $key !== null ? $CI->input->post($key) : null;
    }

}

if (!function_exists('_input_get')) {

    function _input_get($key = null)
    {
        $CI = &get_instance();
        return $key !== null ? $CI->input->get($key) : null;
    }

}

if (!function_exists('_input_request')) {

    function _input_request($key = null)
    {
        $CI = &get_instance();
        return $key !== null ? $CI->input->get_post($key) : null;
    }

}
if (!function_exists('_xss_clean')) {

    function _xss_clean($data = array())
    {
        $CI = &get_instance();
        $CI->load->library("security");
        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $CI->security->xss_clean($value, false);
            }
        }
        return $data;
    }

}
if (!function_exists('pr')) {

    function pr($data = null, $exit = false, $str = "")
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if ($exit === TRUE || $exit == 1) {
            die();
        }

        if ($str != "") {
            echo($str);
        }

    }

}
if (!function_exists('_ck_empty')) {

    function _ck_empty($value = null)
    {
        $data = '';
        if (!empty($value) && ($value != null)) {
            $data = $value;
        }
        return $data;
    }

}


if (!function_exists('sendNotification')) {

    function sendNotification($deviceToken, $title, $notification_msg = '', $type = '', $budge=0) {
        if (empty($deviceToken)) {
            return false;
        }

        //FCM api URL
        $url = 'https://fcm.googleapis.com/fcm/send';
        $server_key = FCM_KEY;
        $notify_msg = "{\"aps\":{\"content-available\":1,\"badge\":\"" . $budge . "\",\"sound\":\"default\",\"alert\":\"" . $notification_msg . "\",\"type\":\"" . $type . "\"}}";

        $fields = array
        (
            'priority' => "high",
            'collapse_key' => "Online Doctor Demand",
            'from' => "275241722730",
            'notification' => array("title" => $title, "body" => $notification_msg, 'sound' => 'default', 'badge' => $budge, 'type' => $type),
            'data' => array('notify_msg' => $notify_msg)
        );

        if (is_array($deviceToken)) {
            $fields['registration_ids'] = $deviceToken;
        } else {
            $fields['to'] = $deviceToken;
        }

        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $server_key
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result === FALSE) {
            return false;
        }else{
            $result=json_decode($result);

            if(isset($result->success))
                return true;    
            else
                return false;
        }
    }
}


function humanTiming($date)
{
    $time = strtotime($date);
    $time = time() - $time; // to get the time since that moment
    $time = ($time < 1) ? 1 : $time;
    $tokens = array(
        /*    31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',*/
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);

        if ($text == 'day' && $numberOfUnits > 1) {
            return date("M d, Y", strtotime($date));
        } else {
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
        }
    }

}


function sort_string($length, $x)
{
    if (strlen($x) <= $length) {
        echo $x;
    } else {
        $y = substr($x, 0, $length) . '...';
        echo $y;
    }
}


function limit_words($string, $word_limit, $u = '', $text = '')
{
    $words = explode(" ", $string);

    $t = 'Read More';
    if ($text) {
        $t = $text;
    }

    $url = '';
    if ($u) {
        $url = '<a  href="' . $u . '" class="read-more">' . $t . '</a>';
    }
    if (count($words) > $word_limit) {
        $r = implode(" ", array_splice($words, 0, $word_limit)) . $url;
    } else {
        $r = $string;
    }
    return $r;
}

/**
 * @param $objects id as string
 * @function is used to check valid object id
 * @return array/null
 */
function getPatientData($objectId, $search = array(),$selected=array())
{
    try {
        $ci = &get_instance();
        $ci->load->library('Mongo_db');
        $ci->load->library('encryption');
        $ci->load->library('encrypt');
        $wheres = ['_id' => new \MongoId($objectId)];
        if (is_array($search) && count($search) > 0) $wheres = array_merge($wheres, $search);
        $ci->mongo_db->wheres = $wheres;
        $resultData = $ci->mongo_db->get('patients', $wheres);
        if ($resultData) {
            $key = $resultData[0]['user_key'];
            $data = [
                "_id"   => $resultData[0]['_id'],
                "firstname" => $ci->encrypt->decode($resultData[0]['firstname'], $key),
                "lastname" => $ci->encrypt->decode($resultData[0]['lastname'], $key),
                "email" => $resultData[0]['email'],
                "user_key" => $resultData[0]['user_key'],
                "dob" => $ci->encrypt->decode($resultData[0]['dob'], $key),
                "gender" => (isset($resultData[0]['gender']) && !empty($resultData[0]['gender']) )?$ci->encrypt->decode($resultData[0]['gender'], $key):'',
                "mobile" => $resultData[0]['mobile'],
                "zipcode" => (isset($resultData[0]['zipcode']) && !empty($resultData[0]['zipcode']) )?$ci->encrypt->decode($resultData[0]['zipcode'], $key):"",
                "country_code" => (isset($resultData[0]['country_code'])) ? $resultData[0]['country_code']:'',
                "city" => (isset($resultData[0]['city']) && !empty($resultData[0]['city'])) ? $ci->encrypt->decode($resultData[0]['city'], $key) : '',
                "state" => (isset($resultData[0]['state']) && !empty($resultData[0]['state'])) ? $ci->encrypt->decode($resultData[0]['state'], $key) : '',
                "plot_unit_no" => (isset($resultData[0]['plot_unit_no']) && !empty($resultData[0]['plot_unit_no'])) ? $ci->encrypt->decode($resultData[0]['plot_unit_no'], $key) : '',
                "street_add" => (isset($resultData[0]['street_add']) && !empty($resultData[0]['street_add']) ) ? $ci->encrypt->decode($resultData[0]['street_add'], $key) : '',
                "image" => (isset($resultData[0]['image']) && !empty($resultData[0]['image'])) ? base_url() . 'assets/upload/patients/' . $ci->encrypt->decode($resultData[0]['image'], $key) : '',
                "email_status" => (isset($resultData[0]['email_status'])) ? $resultData[0]['email_status']:0,
                "mobile_status" => (isset($resultData[0]['mobile_status'])) ? $resultData[0]['mobile_status']:0,
                "quickblox_info" => (isset($resultData[0]['quickblox_info'])) ? $resultData[0]['quickblox_info']:null,
                "change_data" => (isset($resultData[0]['change_data'])) ? $resultData[0]['change_data']:[],
                "status" => (isset($resultData[0]['status'])) ? $resultData[0]['status']:0,
                "deleted" => (isset($resultData[0]['deleted'])) ? $resultData[0]['deleted']:"",
            ];
            if(isset($resultData[0]['location']) && !empty($resultData[0]['location'])){
                $data['location']= $resultData[0]['location'];
            }
            if(count($selected)>0){
                foreach ($selected as $key => $value) {
                    $selectedData[$value]=$data[$value];
                }
                 $data=$selectedData;
            }
            if(isset($resultData[0]['quickblox_info'])){
            $data['quickblox_info']=$resultData[0]['quickblox_info'];
            }
           return array('status' => true, 'message' => 'sucess', 'data' => $data);
        } else {
            return array('status' => false, 'message' => 'Object id not found');
        }
    } catch (MongoException $ex) {

        return array('status' => false, 'message' => $ex->getMessage());
    }

}


/**
 * @param array value
 * @function is used to get providers inforamation
 * @return array/null
 */
function getProviderData($objectId, $search =array(),$selected =array())
{
    try {
        $ci = &get_instance();
        $ci->load->library('Mongo_db');
        $wheres = ['_id' => new \MongoId($objectId)];
        if (is_array($search) && count($search) > 0) $wheres = array_merge($wheres, $search);
        $ci->mongo_db->wheres = $wheres;
        $ci->mongo_db->selects = $selected;
        $resultData = $ci->mongo_db->get('providers', $wheres);
        if ($resultData) {
            $resultData[0]['image'] = (isset($resultData[0]['image']) && !empty($resultData[0]['image'])) ? base_url() . 'assets/upload/providers/' . $resultData[0]['image'] : '';
            if(count($selected)>0){
                if(isset($selected['_id'])){
                }else{
                    unset($resultData[0]["_id"]);
                }
            }
            return array('status' => true, 'message' => 'sucess', 'data' => $resultData[0]);
        } else {
            return array('status' => false, 'message' => 'Object id not found');
        }
    } catch (MongoException $ex) {

        return array('status' => false, 'message' => $ex->getMessage());
    }

}

/**
 * @param string value
 * @function is used to get providers inforamation
 * @return true/false
 */
function getDbvs($key)
{

    $collections = array('PDS' => 'providers', 'PTS' => 'patients', 'PDSE' => 'providerServices', 'PDSH' => 'providerSchedules', 'PDW' => 'providerWorks', 'PDA' => 'providerAccounts', 'PDE' => 'providerEducations', 'PTC' => 'patientCards', 'PTA' => 'patientAppointments','SPS'=>'pages');
    return (array_key_exists($key, $collections)) ? $collections[$key] : false;

}


/*** Set date in UTC from your timezone  ***/
if (!function_exists('utc_date')) {
    function utc_date($date=NULL,$timezone=NULL,$type=NULL)
    {
        $date = new DateTime($date, new DateTimeZone($timezone));
        $date->setTimezone(new DateTimeZone("UTC"));
        if($type)
          $dateInUTC=strtotime($date->format('Y-m-d H:i:sP'));
        else
         $dateInUTC=strtotime($date->format('Y-m-d H:i:s'));
        return $dateInUTC;
    }
}
/* Export Excel Function */
if (!function_exists('export_excel')) {
    function export_excel($data)
    {
        $filename = isset($data['filename']) ? $data['filename'] : 'file_' . time();
        $fp = fopen('php://output', 'w');
        header('Content-type: application/excel');
        header('Content-Disposition: attachment; filename=' . $filename . '.csv');
        if (count($data['heading']) > 0) {
            foreach ($data['heading'] as $key => $value):
                fputcsv($fp, $data['heading'][$key]);
            endforeach;
        } else {
            fputcsv($fp, $data['heading']);
        }
        for ($i = 0; $i < sizeof($data['data']); $i++) {
            fputcsv($fp, $data['data'][$i]);
        }
        exit;
    }
}
if (!function_exists('save_excel')) {
    function save_excel($data)
    {
        $filename = isset($data['filename']) ? $data['filename'] : 'file_' . time();
        $file_path = NEWFCPATH . 'assets/upload/session-generate-files/' . $filename;
        $fp = fopen($file_path, 'w');
        header('Content-type: application/excel');
        header('Content-Disposition: attachment; filename=' . $filename);
        if ($data['heading'] > 0) {
            foreach ($data['heading'] as $key => $value):
                fputcsv($fp, $data['heading'][$key]);
            endforeach;
        } else {
            fputcsv($fp, $data['heading']);
        }
        for ($i = 0; $i < sizeof($data['data']); $i++) {
            fputcsv($fp, $data['data'][$i]);
        }
        return true;
    }
}
// Send SMS 
if (!function_exists('send_sms')) {
    function send_sms($type=null,$value=null,$sessionId=null)
    {
         // 2Factor Credentials
        $YourAPIKey=SMS_API_KEY;
        if($type=='sent'){
            $SentTo='+'.$value; //Customer's phone number in International number format ( with leading + sign)
            $url = "https://2factor.in/API/V1/$YourAPIKey/SMS/$SentTo/AUTOGEN"; 
        }
        else if($type=='otp-match'){
            $url = "https://2factor.in/API/V1/$YourAPIKey/SMS/VERIFY/$sessionId/$value";
        } else{
            return false;
        }
        // ### Sending OTP to Customer's Number
        $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        $Response= curl_exec($ch); 
        curl_close($ch);
        $Response_json=json_decode($Response,false);
        if(isset($Response_json->Status) && $Response_json->Status=='Success'){
            if($Response_json->Details=='OTP Matched')
                return true;
            else
                return $Response_json->Details;
         }   
        else{
        return false;
        }
    }
}

if (!function_exists('send_email')) {
    function send_email($email=null,$subject=null,$message_body=NULL)
    {
        $ci = &get_instance();
        $ci->load->library('email');
        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'developer@brsoftech.com',
            'smtp_pass' => 'brsoft@123',
            'mailtype'  => 'text', 
            'charset'   => 'iso-8859-1'
        );
        $ci->email->initialize($config);
        $ci->email->set_mailtype("html");
        $ci->email->set_newline("\r\n");
        $ci->email
                ->from("developer@brsoftech.com")
                ->reply_to('team@odd.com')    
                ->to($email)
                ->subject($subject)
                ->message($message_body)
                ->send();
          /* $headers = "From: team@odd.com" . "\r\n" ."Reply-To: support@odd.com";
        $simaplemail=mail($email,$subject,$message_body,$headers);
         if($simaplemail)
          return true;
        else
            return false; */     
    }
}

if (!function_exists('moneyFormatIndia')) {
    function moneyFormatIndia($num = NULL)
    {
        $explrestunits = "";
        $num_array = explode('.', $num);
        if (count($num_array) > 0) {
            $num = $num_array[0];
        }
        if (strlen($num) > 3) {
            $lastthree = substr($num, strlen($num) - 3, strlen($num));
            $restunits = substr($num, 0, strlen($num) - 3); // extracts the last three digits
            $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
            $expunit = str_split($restunits, 2);
            for ($i = 0; $i < sizeof($expunit); $i++) {
                // creates each of the 2's group and adds a comma to the end
                if ($i == 0) {
                    $explrestunits .= (int)$expunit[$i] . ","; // if is first value , convert into integer
                } else {
                    $explrestunits .= $expunit[$i] . ",";
                }
            }
            $thecash = $explrestunits . $lastthree;
        } else {
            $thecash = $num;
        }
        return isset($num_array[1]) ? $thecash . '.' . $num_array[1] : $thecash;
        // writes the final format where $currency is the currency symbol.
    }
}

function _customDate($date=NULL,$timezone=NULL,$date_format=NULL)
{
    try{
    date_default_timezone_set($timezone);
    if($date!=='0000-00-00 00:00:00' && $date!='')
        return date($date_format, $date);
    else 
        return '';
    }catch(Exception $e){
        return '';
    }

}

function getCollectionData($collections=NULL,$wheres=NULL,$selected=NULL)
{
    try{
    $ci = &get_instance();
    $ci->load->library('Mongo_db');
    $ci->mongo_db->wheres = $wheres;
    $ci->mongo_db->selects = $selected;
    $resultData = $ci->mongo_db->get($collections, $wheres);
        if ($resultData) {
            return $resultData[0];
        } else {
            return false;
        }
    } catch (MongoException $ex) {

        return false;
    }
    
}

?>