<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * LeafX2 Alert
 *

 * @copyright  (c) 2013
 */
class Leafx2_Alert {

    const SUCCESS   = "success";
    const ERROR     = "danger";
    const WARNING   = "warning";
    const INFO      = "info";

    public static $template = '<div class="alert alert-%s alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <strong>%s : </strong>%s
    </div>';

    /**
     * @var message - alert message
     */
    public static $message;
    /**
     * @var flag - type of flag (success, error, warning, info)
     */
    public static $flag;
    /**
     * @var headline
     */
    public static $headline;

    /**
     * Set alert message
     * @param $msg
     * @param string $flag
     * @param int $skip
     * @param int $type
     */
    public static function message($msg,$flag = "info",$skip = 0,$type = 0) {
        $session = Leafx2_Session::instance();
        $flag = $flag === TRUE? Leafx2_Alert::SUCCESS : ($flag === FALSE? Leafx2_Alert::ERROR : $flag);
        $data = array("message" => $msg, "flag" => $flag, "skip" => $skip, "type" => $type);
        $session->set("leafx2ma",$data);
    }

    /**
     * Check if any message and load it if available
     * @return bool
     */
    public static function available() {
        $session = Leafx2_Session::instance();
        $data = $session->get("leafx2ma");
        if ($data == null || $data["message"] == "") {
            return false;
        }
        else if ($data["skip"] > 0) {
            $data["skip"]--;
            $session->set("leafx2ma",$data);
            return false;
        }
        else {
            Leafx2_Alert::$message = $data["message"];
            Leafx2_Alert::$flag = $data["flag"];
            Leafx2_Alert::$headline = strtoupper($data["flag"]);
            Leafx2_Alert::$headline = Leafx2_Alert::$headline == "DANGER"? "ERROR" : Leafx2_Alert::$headline;
            $session->set("leafx2ma",null);
            return true;
        }
    }

    /**
     * display current notification
     */
    public static function display() {
        echo Leafx2_Alert::get();
    }

    /**
     * @return string Alert message
     */
    public static function get() {
        if (Leafx2_Alert::available()) {
            return sprintf(Leafx2_Alert::$template,Leafx2_Alert::$flag,Leafx2_Alert::$headline,Leafx2_Alert::$message);
        }
    }
}   // end of class