<?php

// define('DB_HOST', 'localhost');
// define('DB_BASE', 'rusport2ru');
// define('DB_USER', 'rusport2ru');
// define('DB_PASS', 'ruAG26ruAG26');
// define('DB_PREF', 'ps');

define('DB_HOST', 'localhost');
define('DB_BASE', 'russport');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_PORT', '3306');
define('DB_PREF', 'ps');

$_SESSION['sql'] = 0;
$_SESSION['sql_log'] = array();

QF('mysqli://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.':'.DB_PORT.'/'.DB_BASE.'?encoding=utf8')->connect()->alias('default')->tablePrefix(DB_PREF);