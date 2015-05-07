<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

    use UserTrait, RemindableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password', 'remember_token');


    public static $rules = array(
        'firstname'=>'required|between:2,20',
        'lastname'=>'required|between:2,20',
        'email'=>'required|email',
        'username'=>'required|between:4,40',
        'password'=>'required|alpha_num|between:6,20',
    );

    public static function getUser($id = 0){
        $conn = ldap_connect(Config::get('sso.LDAP.SERVER'))or die("Couldn't connect to LDAP!");
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION,3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        $bind = ldap_bind($conn,Config::get('sso.LDAP.ADMIN'),Config::get('sso.LDAP.PASS'));

        if($id){ // get specific user

        } else { // get all users
            $filter_uid = "(uid=11)";
            $result_uid = ldap_search($conn,Config::get('sso.LDAP.BASE_DN'),$filter_uid,array("mail","entryUUID","name")); 
            $entries_uid = ldap_get_entries($conn, $result_uid);
            return json_encode($entries_uid);
        }
    }

    public static function updatePassword($data = array()){
        $password = $data['password'];
        
        $conn = ldap_connect(Config::get('sso.LDAP.SERVER'))or die("Couldn't connect to LDAP!");
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION,3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        $bind = ldap_bind($conn,Config::get('sso.LDAP.ADMIN'),Config::get('sso.LDAP.PASS'));

        $filter_name = "(cn=".$data['user_cn'].")";
        $attr = array('email');
        $result_name = ldap_search($conn,Config::get('sso.LDAP.BASE_DN'),$filter_name,$attr);
        $entries_name = ldap_get_entries($conn,$result_name);

        $user = $entries_name[0];
        $dn = $user['dn'];
        $new = array();
        $new['cn'] = $data['user_cn'];
        $new['userPassword'] = '{MD5}' . base64_encode(pack('H*',md5($data['password'])));
        ldap_modify($conn, $dn, $new);
        ldap_unbind($conn);

    }

    public static function validateUser($data = array()){

        $conn = ldap_connect(Config::get('sso.LDAP.SERVER'))or die("Couldn't connect to LDAP!");
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION,3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
//        echo 'conn : '.$conn;
//        echo 'ADMIN : '.Config::get('sso.LDAP.ADMIN');
//        echo 'PASS : '.Config::get('sso.LDAP.PASS');
//        exit;
        $bind = ldap_bind($conn,Config::get('sso.LDAP.ADMIN'),Config::get('sso.LDAP.PASS'));

        $filter_mail = "(mail=".$data['email'].")";
        $filter_name = "(cn=".$data['username'].")";

        $attr = array('email');

        $result_mail = ldap_search($conn,Config::get('sso.LDAP.BASE_DN'),$filter_mail,$attr);
        $entries_mail = ldap_get_entries($conn,$result_mail);

        $result_name = ldap_search($conn,Config::get('sso.LDAP.BASE_DN'),$filter_name,$attr);
        $entries_name = ldap_get_entries($conn,$result_name);


        if($entries_name['count'] == 0 && $entries_mail['count'] == 0)
        {
            $new_user["cn"] = $data['username'];
            //$new_user["displayName"] = $data['firstname'].' '.$data['lastname'];
            $new_user["mail"] = $data['email'];
            $new_user['objectclass'][0] = "inetOrgPerson";
            $new_user['objectclass'][1] = "top";

            $new_user["employeeType"] = 'Administrator';
            $new_user["givenName"] = $data['firstname'];
            $new_user['userPassword'] = '{MD5}' . base64_encode(pack('H*',md5($data['password'])));
            $new_user["sn"] = $data['lastname'];
            $new_user["uid"] = '11';

            ldap_add($conn, 'cn='.$data['username'].','.Config::get('sso.LDAP.BASE_DN'), $new_user);

            Return "user has been added";
        }
        else
        {
            Return "user email/username already exists";
        }

        ldap_unbind($conn);
    }
}
