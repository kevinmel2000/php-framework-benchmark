<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * LeafX2 base class
 *

 * @copyright  (c) 2010
 */
class Leafx2 {
    
    // define application flow
    const FLOW_HTML     = 'html';
    const FLOW_REDIRECT = 'redirect';
    
    // define application output
    const OUTPUT_CONTROLLER = 'controller';
    const OUTPUT_RESPONSE   = 'response';
    const OUTPUT_HTML   = 'html';
    const OUTPUT_AJAX   = 'ajax';
    const OUTPUT_NONE   = 'none';
    const OUTPUT_XLS    = 'xls';
    const OUTPUT_PDF    = 'pdf';
    const OUTPUT_CSV    = 'csv';
    const OUTPUT_XML    = 'xml';

    // define service type
    const SERVICE_REST = 'REST';
    const SERVICE_SOAP = 'SOAP';
    
    // current requested action
    public static $action;
    
    // base url
    public static $base;
    
    // url base for request controller and action
    public static $baseaction;
    public static $basecontroller;
    
    // current requested controller
    public static $controller;

    // database instance
    public static $dbinstance = "default";
    
    // directory
    public static $directory;
    
    // leafx2 special data container for "global" variables
    public static $data = array();
    
    // default error page handler
    public static $error_page;

    // default no auth page
    public static $error_noauth_page = '/auth/login';
    
    // the application flow
    public static $flow = Leafx2::FLOW_HTML;
    
    // application output
    public static $output = Leafx2::OUTPUT_HTML;

    // theme
    public static $theme;
    
    
    /**
     * get base url
     * @return string base url
     */
    public static function base() {
        return Leafx2::$base;
    }
    
    /**
     * get registered variable
     * @param string key identifier
     * @return object data related to the key
     */
    public static function get($key) {
        return Leafx2::$data[$key];
    }
    
    /**
     * get the user data from session
     * this function just for easiness
     * @return object user data
     */
    public static function get_user_data() {
       return Leafx2_Session::instance()->get(Kohana::$config->load("leafx2.app_name"),FALSE);
    }

    public static function invoke_error($error_message) {
        HTTP::redirect("public/error/?msg=".base64_encode($error_message));
    }

    /**
     * register a variable for "global" access, overwrite if key exists
     * @param string key identifier
     * @param object any kind of data
     */
    public static function register($key,$data) {
        Leafx2::$data[$key] = $data;
    }
    
    /**
     * set the controller/action permission
     * @param int permission code
     * @param int return action
     */
    public static function set_permission ($permission, $redirect = true) {
        if (Leafx2::check($permission)) {
            return true;
        }
        else {
            if ($redirect) {
                // check if user has insufficient privileges, or just not logged-in
                if (Leafx2::get_user_data() === FALSE)
                    // no-login
                    HTTP::redirect(Leafx2::$error_noauth_page);
                else
                    // no permission
                    HTTP::redirect(Leafx2::$error_page."?e=".base64_encode("insufficient"));
            }
            return false;
        }
    }

    /**
     * check the permission of the user
     * @static
     * @param  $permission
     * @return bool true if is allowed
     */
    public static function check($permission) {
        $usrdata = Leafx2::get_user_data();

        $level = isset($usrdata->userlevel)? $usrdata->userlevel : 0;

        if (((int)$level & (int)$permission) > 0) {
            return true;
        }
        else {
            return false;
        }
    }
    
} // End 
