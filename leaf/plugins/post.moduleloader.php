<?php defined('SYSPATH') or die('No direct script access.');
/**
 * modules loaders
 *
 * @author
 * @copyright  (c) 2010
 */

    // replacement var for the static HTML
    $static_replacer = array("APP_BASE" => Leafx2::$base);

    $cachefile = "cache/_modules.cache_";
    if (Kohana::$environment == Kohana::PRODUCTION && file_exists($cachefile)) {
        // use cache file
    }
    else {
        // rebuild cache
    }
    
    // create database instance
    $db = Leafx2_Db::instance();
    $res = $db->query("select * from leaf_module where isenabled = 1 order by displayorder asc");

    // create authentication instance
    $auth = Leafx2_Auth::instance();

    if (is_array($res) && count($res)>0) foreach ($res as $module) {
        // decide if it needed to display
        if (($module->displaylogin == "L" && $auth->is_login() && Leafx2::set_permission($module->displaypermission,false))
            || ($module->displaylogin == "U" && !$auth->is_login())
            || $module->displaylogin == "A"
            ) {
            $dsplogin = TRUE;
        }
        else  {
            $dsplogin = FALSE;
        }

        // run forth
        $arrdisplayat = explode(";",$module->displayedat);
        if (($module->displayedat == "ALL" || in_array(Leafx2::$controller."/".Leafx2::$action,$arrdisplayat)) && $dsplogin) {
            $output = "";
            if (file_exists("modules/$module->moduleid.php")) {
                ob_start();
                include_once("modules/$module->moduleid.php");
                $output = ob_get_contents();
                ob_end_clean();
                $this->html_container->add($module->placement,$output);
            }
        }
    }
                