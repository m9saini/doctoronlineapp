<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UsersModel extends CI_Model {

        public function __construct($dbAddress='127.0.0.1')
        {
                $this->load->library('Mongo_db');
        }

        public function get_list()
        {
                $posts = $this->mongo_db->select(array())->get('users');
                return ($posts)?$posts:null; 
        }

        public function insert($post=NULL)
        {
                $result=$this->mongo_db->insert('users',$post);
                return ($result)?true:false;
        }

        public function update_entry()
        {
                $this->email    = $_POST['email'];
                $this->password  = $_POST['password'];
                $this->db->update('users', $this, array('id' => $_POST['id']));
        }

}
