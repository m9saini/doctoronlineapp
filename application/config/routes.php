<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes with
| underscores in the controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
//$route['404_override'] = '';
//$route['translate_uri_dashes'] = FALSE;
$route['api-test']							= 'imageUpload/index';
$route['admin/login']						= 'welcome/login';
$route['admin/logout']						= 'appadmin/admin/logout';
$route['admin/dashboard'] 					= 'appadmin/admin/index';
$route['admin/profile'] 					= 'appadmin/admin/profile';
$route['admin/change-password'] 			= 'appadmin/admin/change_password';
$route['admin/update-status'] 				= 'appadmin/admin/statusUpdatedByAdmin';
$route['admin/deleted-by-admin'] 			= 'appadmin/admin/deletedByAdmin';
$route['admin/restore-by-admin'] 			= 'appadmin/admin/restoreByAdmin';

// Services add/edit/ list
$route['admin/providers/services-list']		= 'appadmin/services/index'; 
$route['admin/providers/services-edit']		= 'appadmin/services/edit'; 

// Admin Patients 
$route['admin/patients'] 					= 'appadmin/patients/patient/index'; 
$route['admin/patients/deleted'] 			= 'appadmin/patients/patient/index'; 
$route['admin/patients-view/:any'] 			= 'appadmin/patients/patient/view/$1'; 

//Patients Appointments
$route['admin/patients/appointment-view/:any'] = 'appadmin/patients/appointment/view/$1';
$route['admin/patients/appointment-view/:any/:any'] = 'appadmin/patients/appointment/view/$1/$2';
$route['admin/patients/appointment-view/:any/:any/:any'] = 'appadmin/patients/appointment/view/$1/$2/$3';
$route['admin/provider/appointment-view/:any'] = 'appadmin/patients/appointment/view/$1';
$route['admin/provider/appointment-view/:any/:any'] = 'appadmin/patients/appointment/view/$1/$2';
$route['admin/provider/appointment-view/:any/:any/:any'] = 'appadmin/patients/appointment/view/$1/$2/$3';
$route['admin/patients/appointments-:any-list'] = 'appadmin/patients/appointment/index';
$route['admin/patients/appointments-:any-list/:any/:any'] = 'appadmin/patients/appointment/index/$1/$2'; 
$route['admin/patients/appointments-:any-list/:any'] = 'appadmin/patients/appointment/index/$1'; 



// Admin Providers 
$route['admin/providers'] 					= 'appadmin/providers/provider/index'; 
$route['admin/providers/deleted'] 			= 'appadmin/providers/provider/index'; 
$route['admin/providers-view/:any'] 		= 'appadmin/providers/provider/view/$1'; 

// Admin Schedule routes
$route['admin/provider/schedule/slot'] 		= 'appadmin/schedule/model_slot';
$route['admin/provider/schedule'] 			= 'appadmin/schedule/schedule_list'; 
$route['admin/provider/schedule-list/:any'] = 'appadmin/schedule/index/$1';
$route['admin/provider/schedule/:any'] 		= 'appadmin/schedule/index/$1'; 
$route['admin/provider/schedule/:any/:any'] = 'appadmin/schedule/index/$1/$2';
$route['admin/provider/schedule_ajax_list']	= 'appadmin/schedule/ajax_list_body'; 
$route['admin/provider/schedule-view/:any'] = 'appadmin/schedule/view/$1';
$route['admin/provider/schedule-view/:any/:any'] = 'appadmin/schedule/view/$1/$2';
$route['admin/provider/schedule-view/:any/:any/:any'] = 'appadmin/schedule/view/$1/$2/$3';
$route['admin/provider/schedule/:any-:any/:any/:any'] 	= 'appadmin/schedule/ajax_list/$1/$2';


//static pages 
$route['api-static-pages/:any'] 	= 'page/index/$1';
$route['admin/pages'] 				= 'appadmin/page/index';
$route['admin/pages/create']		= 'appadmin/page/add';
$route['admin/pages/edit/:any']		= 'appadmin/page/edit/$1';
// Providers and Patients Notifications APIs
$route['api-notification/list']				= 'api/apiNotification/notification';
$route['api-notification/view']				= 'api/apiNotification/notification_view';
$route['api-notification/add']				= 'api/apiNotification/notification_add';
$route['api-notification/delete']			= 'api/apiNotification/delete';


// Providers and Patients Message APIs
$route['api-provider/send-message']			= 'api/message/upsert';
$route['api-patient/send-message']			= 'api/message/upsert';
$route['api-provider/reply-message']		= 'api/message/reply';
$route['api-patient/reply-message']			= 'api/message/reply';
$route['api-provider/inbox-list']			= 'api/message/list';
$route['api-patient/inbox-list']			= 'api/message/list';
$route['api-provider/sent-list']			= 'api/message/list';
$route['api-patient/sent-list']				= 'api/message/list';
$route['api-provider/message-view']			= 'api/message/view';
$route['api-patient/message-view']			= 'api/message/view';


// Providers and Patient Common APIs
$route['api-user/logout']					= 'api/commonApi/logout';
$route['api-services/list']					= 'api/commonApi/servicesList';
$route['api-speciality/list']				= 'api/commonApi/speciality_list';
$route['api-frequency/list']				= 'api/commonApi/frequency';
$route['api-appointment-status-list']		= 'api/commonApi/status_common_list';
$route['api-appointment-status/update']		= 'api/commonApi/appointment_status_update';
$route['api-send-otp']						= 'api/commonApi/otp_sent';
$route['api-resend-otp']					= 'api/commonApi/otp_sent';
$route['api-user-verify/mobile']			= 'api/commonApi/verify';
$route['api-user-verify/email']				= 'api/commonApi/verify';
$route['api-user-change/email']				= 'api/commonApi/update';
$route['api-user-change/mobile']			= 'api/commonApi/update';
$route['api-user-password/:any']			= 'api/commonApi/password_update';
$route['api-contact-us']					= 'api/commonApi/contact_us';
$route['api-country/list']					= 'api/commonApi/countries';
$route['api-gender/list']					= 'api/commonApi/gender';
$route['api-login-with-social']				= 'api/commonApi/social_signup';
$route['api-custom-list']					= 'api/commonApi/custom_list';
$route['api-email-list']					= 'api/commonApi/email_list';
$route['api-quickblox-user-info']			= 'api/commonApi/quickblox';
$route['api-quickblox-user-id-update']		= 'api/commonApi/quickblox';
$route['api-quickblox-user-dialogid-update']	= 'api/commonApi/quickblox';
$route['api-device-info-update']			= 'api/commonApi/device_token_update';
$route['api-profile-image-upload']			= 'api/commonApi/imageUpload';

// Providers and Patient AT HOME Process APIs
$route['api-at-home-appointment-list']		= 'api/atHomeProcess/appointment_filter_list';
$route['api-at-home-status-update']			= 'api/atHomeProcess/appointment_status_update';
$route['api-at-home-price-update']			= 'api/atHomeProcess/appointment_price_update';
$route['api-at-home/dob-verify']			= 'api/atHomeProcess/verify';
$route['api-at-home/send-otp']				= 'api/atHomeProcess/verify';
$route['api-at-home/otp-verify']			= 'api/atHomeProcess/verify';
$route['api-at-home/add-suggestion']		= 'api/atHomeProcess/home_process_suggestion';
$route['api-at-home/charges']				= 'api/atHomeProcess/home_process_charges';
$route['api-at-home/review']				= 'api/atHomeProcess/home_process_review';
$route['api-at-home/completed']				= 'api/atHomeProcess/verify';



//Patient Home Process APIs
$route['api-at-home/provider-info']			= 'api/atHomeProcess/provider_info';
$route['api-at-home-doctor-list']			= 'api/atHomeProcess/doctor_list';


// Patient APIs
$route['api-patient/login']					= 'api/patient/patient/login';
$route['api-patient/signup']				= 'api/patient/patient/signup';
$route['api-patient/profile-view']			= 'api/patient/patient/profile_view';
$route['api-patient/profile-edit']			= 'api/patient/patient/profile_edit';
$route['api-patient/dashboard']				= 'api/patient/patient/dashboard';
$route['api-patient/setting']				= 'api/patient/patient/patient_setting';

// Patient  Card APIs 
$route['api-patient/card-add']				= 'api/patient/card/upsert';
$route['api-patient/card-edit']				= 'api/patient/card/upsert';
$route['api-patient/card-list']				= 'api/patient/card/list';
$route['api-patient/card-view']				= 'api/patient/card/view';
$route['api-patient/card-del']				= 'api/patient/card/delete';

// Patient  Appointment APIs 
$route['api-patient/appointment-add']		= 'api/patient/appointment/upsert';
$route['api-patient/appointment-edit']		= 'api/patient/appointment/edit';
$route['api-patient/appointment-list']		= 'api/patient/appointment/list';
$route['api-patient/appointment-view']		= 'api/patient/appointment/view';
$route['api-patient/appointment-del']		= 'api/patient/appointment/delete';
$route['api-patient/appointment-doctor-list']= 'api/patient/appointment/doctor_list';
$route['api-patient/appointment-image-del'] = 'api/patient/appointment/image_delete';

// Patient  Appointment Visits APIs 
$route['api-patient-visit-appointment-booked']	= 'api/patient/appointmentVisit/appointment_booking';
$route['api-patient-visit/appointment-time']= 'api/patient/appointmentVisit/select_appointment_time';


//Providers APIs
$route['api-provider/login']				= 'api/provider/provider/login';
$route['api-provider/signup']				= 'api/provider/provider/signup';
$route['api-provider/profile-view']			= 'api/provider/provider/profile_view';
$route['api-provider/profile-edit']			= 'api/provider/provider/profile_edit';
$route['api-provider/dashboard']			= 'api/provider/provider/dashboard';
$route['api-provider/setting']				= 'api/provider/provider/provider_setting';
$route['api-provider/about']				= 'api/provider/provider/about';


// Providers Account insert /updated and listing
$route['api-provider/account-add']			= 'api/provider/account/upsert';
$route['api-provider/account-edit']			= 'api/provider/account/upsert';
$route['api-provider/account-list']			= 'api/provider/account/list';
$route['api-provider/account-view']			= 'api/provider/account/view';
$route['api-provider/account-del']			= 'api/provider/account/delete';

// Providers Education insert /updated and listing
$route['api-provider/education-add']		= 'api/provider/education/upsert';
$route['api-provider/education-edit']		= 'api/provider/education/upsert';
$route['api-provider/education-list']		= 'api/provider/education/list'; 
$route['api-provider/education-view']		= 'api/provider/education/view';
$route['api-provider/education-del']		= 'api/provider/education/delete';

// Providers Work insert /updated and listing
$route['api-provider/work-add']				= 'api/provider/work/upsert';
$route['api-provider/work-edit']			= 'api/provider/work/upsert';
$route['api-provider/work-list']			= 'api/provider/work/list';
$route['api-provider/work-view']			= 'api/provider/work/view';
$route['api-provider/work-del']				= 'api/provider/work/delete';
$route['api-provider/work-image-del']		= 'api/provider/work/image_delete';

// Providers Work insert /updated and listing
$route['api-provider/schedule-add']			= 'api/provider/schedule/insert';
$route['api-provider/schedule-edit']		= 'api/provider/schedule/update';
$route['api-provider/schedule-list']		= 'api/provider/schedule/list';
$route['api-provider/schedule-view']		= 'api/provider/schedule/view'; 
$route['api-provider/schedule-list-view']	= 'api/provider/schedule/schedule_list';
$route['api-provider/schedule-calendar-month'] = 'api/provider/schedule/schedule_calendar_month';
$route['api-provider/schedule-calendar-day'] = 'api/provider/schedule/schedule_calendar_day';
$route['api-provider/schedule-delete']		= 'api/provider/schedule/delete'; 
$route['api-provider/schedule-add-walkin']	= 'api/provider/schedule/insert_walkin'; 
$route['api-provider/schedule-edit-walkin']	= 'api/provider/schedule/edit_walkin';
$route['api-provider/schedule-slot-active-deactive'] = 'api/provider/schedule/schedule_slot_time_update';
$route['api-provider/upcoming-booked-schedule-list'] = 'api/provider/schedule/upcoming_schedule';
$route['api-provider/booked-appointment-count-list'] = 'api/provider/schedule/booked_appointment_count_list';

// Providers Schedule Text insert /updated and listing
$route['api-provider/text-put']				= 'api/provider/text/text_data';
$route['api-provider/text-add']				= 'api/provider/text/text';
$route['api-provider/text-edit']			= 'api/provider/text/text';
$route['api-provider/text-view']			= 'api/provider/text/view';
$route['api-provider/text-list']			= 'api/provider/text/list';

// Providers Price insert /updated and listing
$route['api-provider/price-add']			= 'api/provider/price/price';
$route['api-provider/price-edit']			= 'api/provider/price/price';
$route['api-provider/price-list']			= 'api/provider/price/list';
$route['api-provider/price-view']			= 'api/provider/price/view';

// Providers Speciality insert /updated and listing
$route['api-provider/speciality-put']		= 'api/provider/speciality/speciality';
$route['api-provider/speciality-add']		= 'api/provider/speciality/speciality';
$route['api-provider/speciality-edit']		= 'api/provider/speciality/speciality';
$route['api-provider/speciality-list']		= 'api/provider/speciality/list';

// Appointment Calling APIs
$route['api-appointment/start']				= 'api/appointmentCall/start';
$route['api-appointment/extend-time']		= 'api/appointmentCall/extend';
$route['api-appointment/disconnect']		= 'api/appointmentCall/disconnect';
$route['api-appointment/completed']			= 'api/appointmentCall/completed';
$route['api-appointment/server-time']		= 'api/appointmentCall/server_time';

// Providers Suggestion 
$route['api-provider/add-suggestion']		= 'api/provider/suggestion/upsert';

// Providers prescriptions
$route['api-provider/lab-test-add']		= 'api/provider/prescription/upsert';
$route['api-provider/lab-test-edit']	= 'api/provider/prescription/upsert';
$route['api-provider/lab-test-delete']	= 'api/provider/prescription/upsert';
$route['api-provider/lab-test-list']	= 'api/provider/prescription/upsert';
$route['api-provider/drugs-add']		= 'api/provider/prescription/drugs';
$route['api-provider/drugs-edit']		= 'api/provider/prescription/drugs';
$route['api-provider/drugs-delete']		= 'api/provider/prescription/drugs';
$route['api-provider/drugs-list']		= 'api/provider/prescription/drugs';
