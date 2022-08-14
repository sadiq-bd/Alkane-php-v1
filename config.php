<?php


/**
 * Configuration file
 */
//init config
$config = array();

$config['base_url'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/';

define('_BASE_URL_', $config['base_url']);

/**
 * Mail Configuration
 */
// init mail
$config['mail'] = array();

/**
 * Mail SMTP Host
 */
$config['mail']['smtp']['host'] = 'smtp.gmail.com';

/**
 * Mail SMTP User
 */
$config['mail']['smtp']['user'] = 'user@gmail.com';

/**
 * Mail SMTP Password
 */
$config['mail']['smtp']['password'] = 'bjoohfruckiowvht';

/**
 * Mail SMTP Encryption
 */
$config['mail']['smtp']['encryption'] = 'tls';

/**
 * Mail SMTP Port
 */
$config['mail']['smtp']['port'] = 587;

/**
 * Mail SMTP From
 */
$config['mail']['smtp']['from'] = 'user@gmail.com';




/**
 * Database configurations
 */

// Init Database Config
$dbconf = array();


/**
 * Database type (Optional; Default mysql)
 */
# $dbconf['type'] = 'mysql';


/**
 * Database Hostname
 */
$dbconf['host'] = '127.0.0.1';

/**
 * Database Username
 */
$dbconf['user'] = 'root';

/**
 * Database password
 */
$dbconf['password'] = '4616';

/**
 * Database port (Optional; Default 3306)
 */
# $dbconf['port'] = 3306;

/**
 * Database name
 */
$dbconf['dbname'] = 'sadiqserver';

/**
 * Database Character set
 */
$dbconf['charset'] = 'utf8mb4';

/**
 * Database data fetch mode (Default: obj)
 */
$dbconf['fetch_mode'] = 'obj';

/**
 * Database Error Mode (Default: exception)
 */
$dbconf['errmode'] = 'exception';

/**
 * Database Emulate Prepares (Default: false)
 */
$dbconf['emulate_prepares'] = false;




/**
* defaut timezone
*/
date_default_timezone_set('Asia/Dhaka');





  
