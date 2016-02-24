<?php defined('ALT_PATH') OR die('No direct access allowed.');

Alt::$output = Alt::OUTPUT_HTML;

echo 'Hello World!';

require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
