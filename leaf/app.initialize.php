<?php defined('SYSPATH') OR die('No direct access allowed.');
    /**
     * Initializing application
     *
     * @author
     * @copyright  (c) 2010
     */

    // settings
    I18n::lang(Kohana::$config->load("leafx2.lang"));
    if (Leafx2::check(3)) {
        Leafx2::$theme = "default";
    }
    // start timer
    list($usec, $sec) = explode(" ", microtime()); 
    $this->benchmark['controller_start'] = ((float)$usec + (float)$sec);



