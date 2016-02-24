<?php defined('ALT_PATH') OR die('No direct access allowed.');

return array (
    'app' => array(
        'id' => '',
        'name' => 'alt',
        'environment' => 'development'
    ),
    'session' => array(
        'lifetime' => 43200,
    ),
    'security' => array(
        'algorithm' => MCRYPT_RIJNDAEL_128,
        'mode' => MCRYPT_MODE_CBC,
        'key' => 'u/Gu5posvwDsXUnV5Zaq4g==',
        'iv' => '5D9r9ZVzEYYgha93/aUK2w==',
    ),
    'database' => array(
        'default' => array (
            'type'       => 'Mysql',
            'connection' => array(
                'hostname'   => 'localhost',
                'username'   => 'root',
                'password'   => '',
                'persistent' => FALSE,
                'database'   => 'alt-php',
            )
        ),
    ),
);