<?php

namespace models;
use lib\Core;
use lib\Config;
use lib\DB;



class Users
{
    protected $db;

    function __construct()
    {
        $this->db = \lib\DB::getInstance();
    }

    public function getUserByEmail($email)
    {
        $select = $this->db->select('users', 'email=:email AND is_active=1', array(':email' => $email));
        if( $select )
        {
            return $select[0];
        }
        return false;
    }

    public function getUser($id)
    {
        $select = $this->db->select('users', 'id=:id', array(':id' => $id));
        if( $select )
        {
            return $select[0];
        }
        return false;
    }

    public function createUser($user_data)
    {
        $insert = $this->db->insert('users', $user_data);
        if( $insert )
        {
            return true;
        }
        return false;
    }

    public function userExists($email)
    {
        $data = $this->db->select('users', 'email = :email', array(':email' => $email));
        if( $data )
        {
            return true;
        }
        return false;
    }

    //$table, $info, $where, $bind=''
    public function updateUser($data, $user_id)
    {
        $update = $this->db->update('users', $data, 'id=:user_id', array(':user_id' => $user_id));
        if( $update )
        {
            return true;
        }
        return false;
    }

    public function deleteUser($user_id)
    {
        $delete = $this->db->delete('users', 'id=:user_id', array(':user_id' => $user_id));
        return true;
    }

    public function passwordHash($string, $salt=false)
    {
        return password_hash(hash('sha512', $string, true),  PASSWORD_DEFAULT);
    }

    public function passwordVerifyHash($string, $hash)
    {
        return password_verify(hash('sha512', $string, true), $hash);
    }
}