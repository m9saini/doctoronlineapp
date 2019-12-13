<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */

require(APPPATH . 'libraries/REST_Controller.php');

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardstatusd array
 *
 * @package         statusIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/statusigniter-restserver
 */
class ImageUpload extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library(array('encryption','encrypt'));
        $this->load->model('EmailModel', 'Email');
        $this->load->model('ApiModel', 'Api');
        $this->load->model('NotificationModel', 'Notification');
    }


public function testjoin_get(){
/*
$wheres["to"]= new \MongoId("5abc7c3f80996ce7048b4567");
$update['status']=1;
$list=$this->CommonModel->getCollectionData('notifications',$wheres);
$result=$this->CommonModel->updateAll('notifications',$wheres,$update);
/*    
$id=new \MongoId("5ad5efcb80996ca7058b4568");
$wheres["_id"]=['$in'=>[$id]];
$result=$this->CommonModel->delete_all('notifications',$wheres,true);*/

/* 
    db.providerSchedules.aggregate([
    { '$match':{'userid':{'$in':[ObjectId("5b30c6198ead0e1c5e1a230d")]},'type':{'$in':["Audio"]}}
    },
     {
          $lookup: {
             from: "providerPrices",
             let: {
                firstUser: "$frequency_id",
                secondUser: "$userid",
                type: "$type",
             },
             pipeline: [
                {
                   $match: {
                      $expr: {
                         $and: [
                            {
                               $eq: [
                                  "$frequency_id",
                                  "$$firstUser.Audio"
                               ]
                            },
                            {
                               $eq: [
                                  "$userid",
                                  "$$secondUser"
                               ]
                            },
                             {$in: ['$type',['Audio'] ]}
                         ]
                      }
                   }
                }
             ],
             as: "result"
     
     }
     
 },
 {'$match':{"result":{'$ne':[]}}},

])
*/
 //$ops =[['$project' => ["_id" => 1,"to"   => 1]]];
$userid=new \MongoId("5b30c6198ead0e1c5e1a230d");
 $ops=[['$match'=>['userid'=>['$in'=>[$userid]],'type'=>['$in'=>["Audio"]]]],
 [
          '$lookup'=> [
             'from'=> "providerPrices",
             'let'=> [
                'firstUser'=> '$frequency_id',
                'secondUser'=> '$userid',
                'type'=> '$type',
             ],
             'pipeline'=> [
                [
                   '$match'=> [
                      '$expr'=> [
                         '$and'=> [
                            [
                               '$eq'=> [
                                  '$frequency_id',
                                  '$$firstUser.Audio'
                               ]
                            ],
                            [
                               '$eq'=> [
                                  '$userid',
                                  '$$secondUser'
                               ]
                            ],
                             ['$in'=> ['$type',['Audio'] ]]
                         ]
                      ]
                   ]
                ]
             ],
             'as'=> 'result'
     
     ]
     
 ],

        ['$match'=>['result'=>['$ne'=>[]]]],
      ];
$result0=$this->CommonModel->joinCollectionData('providerSchedules',$ops); 
pr($result0);

    die;
}



    public function index_get(){


$config = Array(
    'protocol' => 'smtp',
    'smtp_host' => 'ssl://smtp.googlemail.com',
    'smtp_port' => 465,
    'smtp_user' => 'developer@brsoftech.com',
    'smtp_pass' => 'brsoft@123',
    'mailtype'  => 'html', 
    'charset'   => 'iso-8859-1'
);
        $this->load->library('email',$config);

$subject = 'This is a test';
$message = '<p>This message has been sent for testing purposes.</p>';

// Get full html:
$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=' . strtolower(config_item('charset')) . '" />
    <title>' . html_escape($subject) . '</title>
    <style type="text/css">
        body {
            font-family: Arial, Verdana, Helvetica, sans-serif;
            font-size: 16px;
        }
    </style>
</head>
<body>
' . $message . '
</body>
</html>';
// Also, for getting full html you may use the following internal method:
//$body = $this->email->full_html($subject, $message);
$this->email->initialize($config);
$this->email->set_mailtype("html");
$this->email->set_newline("\r\n");
$result = $this->email
    ->from('developer@brsoftech.com')
    ->reply_to('team@odd.com')    // Optional, an account where a human being reads.
    ->to('m9saini@gmail.com')
    ->subject('test')
    ->message('ttt')
    ->send();

var_dump($result);
echo '<br />';
echo $this->email->print_debugger();


        die;
    }    
    public function test_push_get(){

        $params     =   $this->get();
         //FCM api URL
        $firebase_token = $params['firebase_token'];
        sendNotification($firebase_token, 'test', 'test', 'new', 0);
        $url = 'https://fcm.googleapis.com/fcm/send';
        $title=  "my title";
        $notification_msg=  "my message";
        $requestData["image"]= "http://www.androiddeft.com/wp-content/uploads/2017/11/Shared-Preferences-in-Android.png";
        $requestData["action"]= "url";
        $requestData["action_destination"]= "http://androiddeft.com";
        $budge=0;
        $type="Test";
        $server_key = FCM_KEY; "AIzaSyBcU3YVhWkDBM_Hi_u-2Z8t2EQ4F2AN_vs";
        $notify_msg = "{\"aps\":{\"content-available\":1,\"badge\":\"" . $budge . "\",\"sound\":\"default\",\"alert\":\"" . $notification_msg . "\",\"type\":\"" . $type . "\"}}";

        $fields = array
        (
            'priority' => "high",
            'collapse_key' => "Online Doctor Demand",
            'from' => "275241722730",
            'notification' => array("title" => $title, "body" => $notification_msg, 'sound' => 'default', 'badge' => $budge, 'type' => $type),
            'data' => array('notify_msg' => $notify_msg)
        );

        if (is_array($firebase_token)) {
            $fields['registration_ids'] = $firebase_token;
        } else {
            $fields['to'] = $firebase_token;
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
        pr($result);
        curl_close($ch);
        if ($result === FALSE) {
            echo 'fail';
        }else{
            $result=json_decode($result);
            pr($result);
            if(isset($result->success))
                echo true;    
            else
                echo 'fail';
        }
        /*
        $response   =   [];
        $token      =   $params['token'];
        $budge      =   5;
        $true       =   sendNotification($token,'Notification','tsdfdsfs','booking_confirm',$budge);
        echo $true; */
    }
    
    /**
     * @param null
     * @function is used to provider edit profile
     * @return array
     */
    public function test_email_get()
    {
        //$this->Email->sendEmail('m9saini@gmail.com','test new ','test new ');
        send_email('manoj.saini@brsoftech.org','test new ','test new ');
    }

    public function image_upload_post()
    {
        $response = [];
        $params = $this->post(); 
        $locData=explode(',',FILE_UPLOADS);
        /*         * ******* CEHCK MANDATORY FIELDS ************* */
        if (!in_array(null, $params) && in_array($params['location_name'], $locData) 
                    && isset($params['userid']) && isset($params['upload_type']) 
                    && in_array($params['upload_type'],array('vedio','image'))          
                    ) 
        {
            $whers=array();
            $collection = camelize($params['location_name']);
            $documentExists=$this->CommonModel->getCollectionData($collection,$whers,$params['userid']);
            $error  =   0; 
            /*      * *** Profile Image Update *******      */ 
            $image = isset($_FILES['image']['name'])?$_FILES['image']['name']:'';
            if(count($documentExists)>0 ){

                $upload_location=str_replace('_','/',$params['location_name']);
                $this->load->library('upload');
                $config['upload_path'] = "./assets/upload/$upload_location/";
                $config['allowed_types'] = 'jpg|jpeg|png';
                $config['max_size'] = '1000000000000000';
                $config['overwrite'] = TRUE;

                $title = date('YmdHis');
                $rand = rand(100000,999999);
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $fileName = $rand .$title.'.'.$ext;
                $image = $fileName;
                $config['file_name'] = $fileName;
                $this->upload->initialize($config);

                if ($this->upload->do_upload('image'))
                {
                    $this->upload->data();
                    $image = $fileName;
                    /*** Image resize ****/
                    $this->load->library('image_lib');
                    $resize['image_library'] = 'gd2';
                    $resize['source_image'] = "./assets/upload/$upload_location/$image";
                    $tnumb = $rand .$title.'_thumb.'.$ext;
                    $resize['new_image'] = "./assets/upload/$upload_location/$tnumb";
                    //$resize['maintain_ratio'] = TRUE;
                    $resize['width']         = 150;
                    $resize['height']       = 150;
                    $this->image_lib->initialize($resize);
                    $this->image_lib->resize();
                }else{
                    echo $error  =   1;
                }
                /* ****** update document  *****     */
                 if($error ==0 && $image !='' ){
                    $wheres['img_extension']=$ext;
                    if($collection =='patients'){ 
                        $wheres['image']=$this->encrypt->enstatus($image, $documentExists[0]['user_key']);
                    }
                    else{
                        $wheres['image']=$image;
                    }
                    $dataResult = $this->CommonModel->upsert($collection,$wheres,$params['userid'],true);
                    if ($dataResult) {
                        $response["status"] = 1;
                        $response['message'] = 'SuccessFully Image Upoload';
                        $response['data'] = array('image_name'=>$image);
                    } else {
                        $response["status"] = 0;
                        $response['error_data']=['error_message' => 'Not Save.'];
                    }
                }else{
                    $response["status"] = 1;
                    $response["message"] = 'Please upload valid image.';
                }

            }else{
                    $response["status"] = 1;
                    $response["error"] = true;
                    $response['message'] = 'User id not found.';
                }
            
            } else {
                //$msg = $model->getErrors();
                $response["status"] = 1;
                $response["error"] = true;
                $response['message'] = 'Mandatory fields are required.';
            }

        $this->response($response);
    }
    
    
   
}
