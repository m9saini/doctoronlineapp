<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class NotificationModel extends CI_Model {
    //put your code here
    public function __construct() {
		$this->load->helper('url');
		$this->load->library('email');
    }
 
	
	//-------------------------------------------------------------------------------------------
	
	public function android($firebase_token,$requestData=array())
	{
		$firebase_api = FCM_KEY ;
						
		$requestData["title"]= "my title";
        $requestData["message"]= "my message";
        $requestData["image"]= "http://www.androiddeft.com/wp-content/uploads/2017/11/Shared-Preferences-in-Android.png";
        $requestData["action"]= "url";
        $requestData["action_destination"]= "http://androiddeft.com";	
        		
			$fields = array(
				'to' => $firebase_token,
				'data' => $requestData,
			);

		// Set POST variables
		$url = 'https://fcm.googleapis.com/fcm/send';

		$headers = array(
			'Authorization: key=' . $firebase_api,
			'Content-Type: application/json'
		);
		
		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Disabling SSL Certificate support temporarily
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$result = curl_exec($ch);
		if($result === FALSE){
			die('Curl failed: ' . curl_error($ch));
		}

		// Close connection
		curl_close($ch);

		pr($result);
		die;
						
	}
	
	public function ios($firebase_token,$requestData=array())
	{
		 
        $requestData["title"]= "my title";
        $requestData["message"]= "my message";
        $requestData["image"]= "http://www.androiddeft.com/wp-content/uploads/2017/11/Shared-Preferences-in-Android.png";
        $requestData["action"]= "url";
        $requestData["action_destination"]= "http://androiddeft.com";
    	
		$firebase_api = FCM_KEY ;
		$fields = array(
				'to' => $firebase_token,
				'data' => $requestData,
			);

		// Set POST variables
		$url = 'https://fcm.googleapis.com/fcm/send';

		$headers = array(
			'Authorization: key=' . $firebase_api,
			'Content-Type: application/json'
		);
		
		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Disabling SSL Certificate support temporarily
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$result = curl_exec($ch);
		if($result === FALSE){
			die('Curl failed: ' . curl_error($ch));
		}

		// Close connection
		curl_close($ch);

		pr($result);
		die;
						
	}
	
		
}
?>


					
						
						
					