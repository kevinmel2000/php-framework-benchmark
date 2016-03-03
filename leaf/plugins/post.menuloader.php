<?php
    if (Leafx2_Auth::instance()->is_login()) {
        $userdata = Leafx2::get_user_data();
        $menufile = "menu.".$userdata->usergroupname.".php";
    }
    else {
        $menufile = "menu.public.php";
    }

    ob_start();
    include_once('menu'.DIRECTORY_SEPARATOR.$menufile);
    $this->html_container->add('HTML_MENU_TOP',ob_get_contents());
    ob_clean();
