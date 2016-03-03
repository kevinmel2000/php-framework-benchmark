<?php defined('SYSPATH') or die('No direct script access.');

if (Leafx2::check(1023) && Leafx2::$controller.".".Leafx2::$action != "auth.switch") {
    // check if user login is expired or re-login on different browser
    $timeout = 15 * 60;

    $sid = session_id();
    $username = Leafx2::get_user_data()->username;

    $usr = new Leafuser();
    $usr->where("username = ".$usr->quote($username));
    $res = $usr->get();
    $user = $res[0];

    $is_switch = Session::instance()->get("auth.is_switch");

    // check if sid is different = logged in from different computer
    if ($user->sessionid != $sid && !$is_switch) {
        // destroy session
        Leafx2_Session::instance()->destroy();

        $auth = Leafx2_Auth::instance();
        $auth->logout();
        $this->redirect("public/error?e=".base64_encode("Already logged in from another computer"));
    }
    // or, timed-out
    /*else if (($user->lastlogin + $timeout) < time()) {
        // destroy session
        Leafx2_Session::instance()->destroy();

        $auth = Leafx2_Auth::instance();
        $auth->logout();
        $this->request->redirect("public/warning?e=".base64_encode("time-out"));
    }*/

}