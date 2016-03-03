<?php defined('SYSPATH') or die('No direct script access.');
return array(

    'mode'         => 'internal',
	'driver'       => 'ORM',
	'hash_method'  => 'sha1',
	'salt_pattern' => '1, 3, 5, 9, 14, 15, 20, 21, 28, 30',
	'lifetime'     => 1209600,
	
	'internal'  => array(
	    'tablename'     => 'leaf_user',
	    'grouptablename'=> 'leaf_usergroup',
        'sessiontablename'=> 'leaf_session'
	),
	
);