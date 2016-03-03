<?php defined('SYSPATH') or die('No direct script access.');

return array(

// application name
'app_name'  => 'leaf',

// language
'lang'  => 'id-id',

// application type
'type'  => 'html',

// theme for LeafX2 application
'theme' => 'default',


// ********************************************************
// Plugins - for HTML output mode
// ********************************************************
'pre_execution' => array(
        //'pre.singlelogin',
        //'pre.functions',
    ),

'post_execution' => array(
//        'post.general',
//        'post.moduleloader',
//        'post.menuloader',
    ),


// ********************************************************
// Plugins - for REST based service
// ********************************************************
'pre_service' => array(
//        'pre.functions',
//        'pre.checkservice',
    ),

);