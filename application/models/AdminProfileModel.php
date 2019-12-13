<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AdminProfileModel extends CI_Model {

        public function __construct($dbAddress='localhost')
        {
                $this->load->library('Mongo_db');
        }

        public function login($param)
        {
           if($param){
           $result =  $this->mongo_db->get_where('admin',array('email' =>$param['email'] ,'password'=>md5($param['password'])));
           return ($result)?$result:null;
            } else {
                return null;    
            }
        } 
        public function update_profile($email,$post=array())
        {
                $result=$this->mongo_db->where(array('email'=>$email))->set($post)->update('admin');
                return ($result)?$result:null;
        }

        public function change_password($email,$pass)
        {
                $result=$this->mongo_db->where(array('email'=>$email))->set('password',md5($pass))->update('admin');
                return ($result)?$result:null;
        }
        public function get_data($email)
        {
                $result=  $this->mongo_db->find_one('admin',array('email' =>$email));
                return ($result)?$result:null;
        }
        
}
