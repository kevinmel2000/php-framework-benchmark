<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * LeafX2 authentication module
 *

 */
class Leafx2_Auth_Internal extends Leafx2_Auth {

	/**
	 * Get the stored password for a username.
	 *
	 * @param   string username
	 * @return  string password
	 */
	protected function get_password($username)	{
		$db  = Leafx2_Db::instance(Leafx2::$dbinstance);
		// select a password from database
        $res = $db->query("select password from ".$this->config['internal']['tablename']." where usernamehash = '".md5($username)."'");
		if (empty($res)) return null;
		else return $res[0]->password;
	}
    
	/**
     * @param  $input_password string plain passsword
     * @param  $db_password password from database
     * @return bool true if correct
     */
	public function check_password($input_password,$db_password) {
	    if (empty($input_password))
			return false;
		if (is_string($input_password)) {
			// Get the salt from the stored password
			$salt = $this->find_salt($db_password);
			// Create a hashed password using the salt from the stored password
			$password = $this->hash_password($input_password, $salt);
			// compare it
			if ($password === $db_password) {
			    return true;
			}
		}
		return false;
	}
	
	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable autologin
	 * @return  boolean
	 */
	protected function do_login($username, $password, $remember) {
	    // load password from database
        $db  = Leafx2_Db::instance(Leafx2::$dbinstance);
	    $res = $db->query("select * from ".$this->config['internal']['tablename']." where usernamehash = '".md5($username)."'");
	    if (empty($res)) {
	        // no such user
	        return FALSE;
	    }
        $user = $res[0];
	    // do password matching
	    if ($user->password === $password) {
	        if ($remember === TRUE) {
	            //TODO: do nothing right now
	        }
	        // finish the login
	        $this->complete_login($user);
	        
	        return TRUE;
	    }
	    return FALSE;
	}

    /**
     * @param $userdata
     * @return bool
     */
    protected function do_sso_login($userdata) {
        // check data validity
        $userdata->sessionid = session_id();
        $userdata->lastlogin = time();
        // set to internal database
        $db  = Leafx2_Db::instance(Leafx2::$dbinstance);
        // delete then insert
        $res = $db->query("delete from ".$this->config['internal']['sessiontablename']." where username = ".$db->quote($userdata->username));
        $res = $db->query("insert into ".$this->config['internal']['sessiontablename']." values (".$db->quote($userdata->username)
            .",".$db->quote(md5($userdata->username))
            .",".$db->quote($userdata->name)
            .",".$db->quote($userdata->usergroupid)
            .",".$db->quote($userdata->lastlogin)
            .",".$db->quote($userdata->sessionid).")"
        );
        // complete the process
        return parent::complete_login($userdata);
    }

    /**
     * @param $username
     * @return bool true if success
     */
    protected function do_sso_logout($usernamehash) {
        // set to internal database
        $db  = Leafx2_Db::instance(Leafx2::$dbinstance);
        // delete
        $res = $db->query("delete from ".$this->config['internal']['sessiontablename']." where usernamehash = ".$db->quote($usernamehash));
        // return
        return $res;
    }


	/**
	 * Complete the login for a user by incrementing the logins and setting
	 * session data: user_id, username, groups.
	 *
	 * @param   object  user object
	 * @return  void
	 */
	protected function complete_login($user) {
	    // completing the login
        $db  = Leafx2_Db::instance(Leafx2::$dbinstance);

        // set session table
        // delete then insert
        $res = $db->query("delete from ".$this->config['internal']['sessiontablename']." where usernamehash = ".$db->quote(md5($user->username)));
        $res = $db->query("insert into ".$this->config['internal']['sessiontablename']." (username,usernamehash,name,usergroupid,lastlogin,sessionid) values (".$db->quote($user->username)
                .",".$db->quote(md5($user->username))
                .",".$db->quote($user->name)
                .",".$db->quote($user->usergroupid)
                .",".$db->quote(time())
                .",".$db->quote(session_id()).")"
        );
		// update last login
		$res = $db->query("update ".$this->config['internal']['tablename']." set lastlogin = '".time()."',islogin = '1' where usernamehash = ".$db->quote(md5($user->username)));
	    // load the user group
	    $res = $db->query("select * from ".$this->config['internal']['grouptablename']." where usergroupid = '$user->usergroupid'");
	    $user->usergroupname = $res[0]->name;
	    $user->userlevel = $res[0]->level;
	    $user->usergroupdesc = $res[0]->description;

        // clean up
        unset($user->password);
        unset($user->usernamehash);
	    // back to parent
		return parent::complete_login($user);
	}

    /**
	 * Complete the logout for a user
	 *
	 * @return  void
	 */
	protected function complete_logout() {
	    // completing the logout
        $db  = Leafx2_Db::instance(Leafx2::$dbinstance);
	    // TEMP: set flag is login = 0
	    $username = $this->session->get($this->session_key)->username;
	    $res = $db->query("update ".$this->config['internal']['tablename']." set islogin = 0 where usernamehash = '".md5($username)."'");
	    // back to parent
		return parent::complete_logout();
	}


	public function authenticate($username, $password) {
	    if (is_string($password)) {
			// Get the salt from the stored password
			$salt = $this->find_salt($this->get_password($username));
			// Create a hashed password using the salt from the stored password
			$password = $this->hash_password($password, $salt);
		}
	    // load password from database
        $db  = Leafx2_Db::instance(Leafx2::$dbinstance);
	    $res = $db->query("select * from ".$this->config['internal']['tablename']." where usernamehash = '".md5($username)."'");
	    if (empty($res)) {
	        // no such user
	        return FALSE;
	    }
        $user = $res[0];
	    // do password matching
	    if ($user->password === $password) {
	        return TRUE;
	    }
	    return FALSE;
	}
}