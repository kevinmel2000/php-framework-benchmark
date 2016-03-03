<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * LeafX2 authentication module
 *

 */
abstract class Leafx2_Auth {
    
    // Singleton static instance
	protected static $instance;
	// session handler, menggunakan Kohana Session
	protected $session;
	// configuration container
	protected $config;
	// session key - using the leafx2 app name
	protected $session_key;
    
    /**
     * Returns a singleton instance of class
     *
     * @return  Leafx2_Auth
     */
    public static function instance() {
        if (!isset(Leafx2_Auth::$instance)) {
			// Load the configuration for this type
			$config = Kohana::$config->load('auth');
			if (!$mode = $config->get('mode')) {
				$mode = 'internal';
			}
			// set the auth mode
			$class = 'Leafx2_Auth_'.ucfirst($mode);
			// Create a new auth instance
			Leafx2_Auth::$instance = new $class($config);
		}
        return Leafx2_Auth::$instance;
    }
    
    /**
     * Returns a singleton instance of class
     * alias of instance
     * @return  Leafx2_Auth
     */
    public static function factory() {
        return Leafx2_Auth::instance();
    }
    
    /**
     * Constructing class
     * @return void
     */
    public function __construct($config = array()) {
        // split pattern into array
		$config['salt_pattern'] = preg_split('/,\s*/', Kohana::$config->load('auth.salt_pattern'));
		// Save the config in the object
		$this->config = $config;
        // initiating session
		$this->session = Leafx2_Session::instance();
		// set session key
		$this->session_key = Kohana::$config->load("leafx2.app_name");
    }

	abstract protected function get_password($username);

	abstract public function check_password($input_password,$db_password);
	
	abstract protected function do_login($username, $password, $remember);

    abstract protected function do_sso_login($userdata);

    abstract protected function do_sso_logout($usernamehash);

	abstract public function authenticate($username,$password);
	
	
	/**
	 * Gets the currently logged in user from the session.
	 * Returns FALSE if no user is currently logged in.
	 *
	 * @return  mixed
	 */
	public function get_user() {
		return $this->session->get($this->session_key, FALSE);
	}
	
	/**
	 * Check if there is an active session
	 *
	 * @param   string   group name
	 * @return  mixed
	 */
	public function is_login() {
		return FALSE !== $this->get_user();
	}
	
	/**
	 * Attempt to log in a user 
	 *
	 * @param   string   username to log in
	 * @param   string   password to check against
	 * @param   boolean  enable autologin
	 * @return  boolean
	 */
	public function login($username, $password, $remember = FALSE) {
        // Regenerate session_id
        $this->session->regenerate();

        // Check
		if (empty($password))
			return FALSE;

		if (is_string($password)) {
			// Get the salt from the stored password
			$salt = $this->find_salt($this->get_password($username));
			// Create a hashed password using the salt from the stored password
			$password = $this->hash_password($password, $salt);
		}

		return $this->do_login($username, $password, $remember);
	}

    /**
     * @param $userdata array of user data
     */
    public function sso_login($userdata) {
        // regenerate
        $this->session->regenerate();
        // execute
        return $this->do_sso_login($userdata);
    }
	
	
	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean  completely destroy the session
	 * @param   boolean  remove all tokens for user
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE) {
		// completing logout, weird isn't it? starting this 'complete' blah blah at the first line of code
		$this->complete_logout();
		
		if ($destroy === TRUE) {
			// Destroy the session completely
			$this->session->destroy();
		}
		else {
			// Remove the user from the session
			$this->session->delete($this->session_key);

			// Regenerate session_id
			$this->session->regenerate();
		}
		
		// Double check
		return ! $this->is_login();
	}

    /**
     * @param $username
     * @param bool $destroy
     * @return bool
     */
    public function sso_logout($usernamehash) {
        // Remove the user from the session
        $this->do_sso_logout($usernamehash);
        // return
        return TRUE;
    }
	
	/**
	 * Creates a hashed password from a plaintext password, inserting salt
	 * based on the configured salt pattern.
	 *
	 * @param   string  plaintext password
	 * @return  string  hashed password string
	 */
	public function hash_password($password, $salt = FALSE)	{
		if ($salt === FALSE) {
			// Create a salt seed, same length as the number of offsets in the pattern
			$salt = substr($this->hash(uniqid(NULL, TRUE)), 0, count($this->config['salt_pattern']));
		}

		// Password hash that the salt will be inserted into
		$hash = $this->hash($salt.$password);

		// Change salt to an array
		$salt = str_split($salt, 1);

		// Returned password
		$password = '';

		// Used to calculate the length of splits
		$last_offset = 0;

		foreach ($this->config['salt_pattern'] as $offset) {
			// Split a new part of the hash off
			$part = substr($hash, 0, $offset - $last_offset);

			// Cut the current part out of the hash
			$hash = substr($hash, $offset - $last_offset);

			// Add the part to the password, appending the salt character
			$password .= $part.array_shift($salt);

			// Set the last offset to the current offset
			$last_offset = $offset;
		}

		// Return the password, with the remaining hash appended
		return $password.$hash;
	}

    /**
	 * Perform a hash, using the configured method.
	 *
	 * @param   string  string to hash
	 * @return  string
	 */
	protected function hash($str) {
		return hash($this->config['hash_method'], $str);
	}	
	
	/**
	 * Finds the salt from a password, based on the configured salt pattern.
	 *
	 * @param   string  hashed password
	 * @return  string
	 */
	protected function find_salt($password) {
		$salt = '';

		foreach ($this->config['salt_pattern'] as $i => $offset) {
			// Find salt characters, take a good long look...
			$salt .= substr($password, $offset + $i, 1);
		}

		return $salt;
	}
	
	protected function complete_login($user) {
    	// Store username in session
		$this->session->set($this->session_key,$user);

		return TRUE;
	}
	
	protected function complete_logout() {
		return TRUE;
	}

    public function generate_token($seed = null) {
        $feed = "ars";
        return md5($feed);
    }

    public function validate_token($token,$seed = null) {
        $feed = "ars";
        return $token == md5($feed);
    }

    /**
     * Wrap any data with supplied password
     * @param $userdata
     * @param $password
     * @return string
     */
    public function wrap_userdata($userdata,$password) {
        try {
            // create init vector
            $iv = mcrypt_create_iv(
                mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC),
                MCRYPT_DEV_URANDOM
            );
            // encrypt
            $res =  base64_encode($iv . mcrypt_encrypt(
                        MCRYPT_RIJNDAEL_256,
                        hash('sha256', $password, true),
                        serialize($userdata),
                        MCRYPT_MODE_CBC,
                        $iv
                    )
             );
        }
        catch (Exception $e) {
            $res =  false;
        }
        return $res;
    }

    /**
     * Unwrap packed data
     * @param $encrypted
     * @param $password
     * @return mixed
     */
    public function unwrap_userdata($encrypted,$password) {
        try {
            $data = base64_decode($encrypted);
            $iv  = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));
            $res = unserialize(rtrim(mcrypt_decrypt(
                    MCRYPT_RIJNDAEL_256,
                    hash('sha256', $password, true),
                    substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)),
                    MCRYPT_MODE_CBC,
                    $iv
                ),
                "\0"
            ));
        }
        catch (Exception $e) {
            $res = false;
        }
        return $res;
    }
}