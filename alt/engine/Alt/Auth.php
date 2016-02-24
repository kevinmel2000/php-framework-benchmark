<?php defined('ALT_PATH') OR die('No direct access allowed.');

class Alt_Auth {
    /**
     * Login to application
     *
     * @param $data
     * @return object
     * @throws Alt_Exception
     */
    public function login($data){
        // extracting variables
        extract($data,EXTR_PREFIX_ALL,"var");

        // ambil dulu dari database apakah ada user dengan username yang aktif
        $obj = new Dbo_User();
        $obj->where('username = ' . $obj->quote($var_username));
        $obj->where('isenabled = "1"');
        $obj->retrieve(true);

        if($obj->userid == '')
            throw new Alt_Exception("Login gagal. Username tidak ditemukan", 52);

        // authenticate username dan password
        $res = Leafx2_Auth::instance()->authenticate($var_username, $var_password);

        if($res !== TRUE)
            throw new Alt_Exception("Login gagal. Username / password salah", 58);

        // cek dari session apakah sudah pernah login dari ip yg sama
        $var_ipaddress = $var_ipaddress ?: Request::$client_ip;
        $obj_session = new Dbo_Session();
        $obj_session->where("username = " . $obj_session->quote($obj->username));
        $obj_session->where("ipaddress = " . $obj_session->quote($var_ipaddress));
        $session = $obj_session->get();
        $session = count($session) > 0 ? $session[0] : new stdClass();

        $token = '';
        $islogin = false;
        try{
            if($session->token) {
                $token = $session->token;
                $userdata = System::get_user_data($token);
                $islogin = true;
            }
        }catch (Exception $e){
            $islogin = false;
        }

        if(!$islogin) {
            // belum pernah login, login dari ip yg berbeda, atau token sudah expired, buat lagi
            $userdata = new stdClass();
            $fields = $obj->get_fields();
            foreach($fields as $field => $default){
                $userdata->$field = $obj->$field ?: '';
            }
            unset($userdata->usernamehash);
            unset($userdata->password);

            // tambah field dari usergroup
            $obj_usergroup = new Dbo_Usergroup();
            $obj_usergroup->usergroupid = $obj->usergroupid;
            $obj_usergroup->retrieve();

            $userdata->usergroupname = $obj_usergroup->name;
            $userdata->userlevel = $obj_usergroup->level;
            $userdata->usergroupdesc = $obj_usergroup->description;
            $userdata->usermodules = $obj->modules ?: $obj_usergroup->modules;

            $modules = System_Module::instance()->retrieve_multi(array('isactive' => 1));
            if($userdata->usermodules == '*'){
                $userdata->modules = $modules;
            }else{
                $usermodules = explode(",", $userdata->usermodules);
                $availablemodules = array();
                foreach($usermodules as $module){
                    $module = trim($module);
                    $availablemodules[$module] = $modules[$module];
                }
                $userdata->modules = $availablemodules;
            }

            $token = System::generate_token($userdata);
        }

        // hapus dari session
        $obj_session = new Dbo_Session();
        $obj_session->where("username = " . $obj_session->quote($obj->username));
        $obj_session->where("ipaddress = " . $obj_session->quote($var_ipaddress));
        $obj_session->delete_multi();

        // simpan ke tabel session
        $obj_session->username = $obj->username;
        $obj_session->lastlogin = time();
        $obj_session->lastactive = time();
        $obj_session->ipaddress = $var_ipaddress;
        $obj_session->token = $token;
        $obj_session->insert();

        // update table user
        $obj->islogin = 1;
        $obj->update();

        // untuk testing, simpan token ke dalam $_REQUEST
        if(Kohana::$environment == Kohana::TESTING){
            $_REQUEST['token'] = $token;
        }

        return $token;
    }

    /**
     * Logout from application
     *
     * @param $data
     * @return bool
     * @throws Alt_Exception
     */
    public function logout($data){
        // extracting variables
        extract($data,EXTR_PREFIX_ALL,"var");

        // ambil userdata
        $userdata           = System::get_user_data();

        if($userdata->username == '')
            throw new Alt_Exception("Gagal logout, user tidak dikenal", 83);

        // hapus dari session
        $var_ipaddress = $var_ipaddress ?: Request::$client_ip;
        $obj = new Dbo_Session();
        $obj->where("username = " . $obj->quote($userdata->username));
        $obj->where("ipaddress = " . $obj->quote($var_ipaddress));
        $obj->delete_multi();

        // update islogin
        $obj = new Dbo_Session();
        $obj->where("username = " . $obj->quote($userdata->username));
        $islogin = $obj->count() > 0;

        $obj_user = new Dbo_User();
        $obj_user->where("username = " . $obj_user->quote($userdata->username));
        $obj_user->islogin = $islogin;
        $obj_user->update_multi();

        if(Kohana::$environment == Kohana::TESTING){
            $_REQUEST['token'] = '';
        }

        return true;
    }

    /**
     * Check if user still login
     *
     * @param $data
     * @return bool
     * @throws Alt_Exception
     */
    public function islogin($data){
        // extracting variables
        extract($data,EXTR_PREFIX_ALL,"var");

        $obj = new Dbo_Session();
        $obj->where("username = " . $obj->quote($var_username));
        if(Valid::not_empty($var_ipaddress)) $obj->where("ipaddress = " . $obj->quote($var_ipaddress));

        return $obj->count() > 0;
    }

    /**
     * Force logout
     *
     * @param $data
     * @return bool
     * @throws Alt_Exception
     */
    public function force_logout($data){
        // extracting variables
        extract($data,EXTR_PREFIX_ALL,"var");

        $obj = new Dbo_Session();
        $obj->where("username = " . $obj->quote($var_username));
        $obj->delete_multi();

        $obj_user = new Dbo_User();
        $obj_user->where("username = " . $obj_user->quote($var_username));
        $obj_user->islogin = 0;
        $obj_user->update_multi();

        return $obj->count() == 0;

    }

    /**
     * Generate token
     *
     * @param $data
     * @return object
     * @throws Alt_Exception
     */
    public function token($data){
        // extracting variables
        extract($data,EXTR_PREFIX_ALL,"var");

        // update ke database
        $userdata = System::get_user_data();

        if($userdata->username == '') {
            if($var_token != ''){
                // ambil dari leaf_session jika ada
                $obj= new Dbo_Session();
                $obj->where("token = " . $obj->quote($var_token));
                $tmp = $obj->get();

                if(count($tmp > 0) && $tmp[0]->username != ''){
                    // update tabel user, set islogin 0
                    $obj_user = new Dbo_User();
                    $obj_user->where("username = " . $obj_user->quote($obj->username));
                    $obj_user->islogin = 0;
                    $obj_user->update_multi();
                }

                $obj->delete_multi();
            }
            return '';
        }

        $token = System::generate_token($userdata);
        $var_ipaddress = $var_ipaddress ?: Request::$client_ip;

        $obj = new Dbo_Session();
        $obj->where("username = " . $obj->quote($userdata->username));
        $obj->where("ipaddress = " . $obj->quote($var_ipaddress));
        $tmp = $obj->get();

        if(count($tmp) <= 0)
            return '';

        $obj->lastactive = time();
        $obj->token = $token;
        $obj->update_multi();

        return $token;
    }
}