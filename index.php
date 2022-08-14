<?php


$allowed_host = array (
    '127\.0\.0\.1(\:\d+)?'
);

$isValidHost = false;
foreach ($allowed_host as $host) {
    if (preg_match('/^' . $host . '$/i', $_SERVER['HTTP_HOST'])) {
        $isValidHost = true;
        break;
    }
}
if (!$isValidHost) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}





function _dir_($append = '') {
    $dir = rtrim(__DIR__, '/');
    $dir = rtrim($dir, '\\');
    if (!empty($append) && $append != null) {
        $append = trim($append);
        $dir .= '/' . ltrim($append, '/');
    }
    $dir = str_replace('\\', '/', $dir);
    return $dir;
}


// Configuration file
if (!file_exists(_dir_('/config.php'))) 
    die('Configuration file not found :(');
require_once _dir_('/config.php');


/**
 * Check if the minimum php version is met
 */
$required_php_version = '7.0.0';
if (version_compare(PHP_VERSION, $required_php_version, '<')) 
    die('You need to use PHP version ' . $required_php_version . ' or higher to run this app.');



// load all functions
if (is_dir(_dir_('/includes/functions'))) {
    foreach (glob(_dir_('/includes/functions/*.php')) as $filename) {
        require_once $filename;
    }
}


// Autoload Classes
require_once _dir_('/includes/ClassLoader.php');
Alkane\ClassLoader\ClassLoader::add_paths(array(
    _dir_('/includes'),
    _dir_('/app/includes')
));
Alkane\ClassLoader\ClassLoader::is_strip_namespace(true);
Alkane\ClassLoader\ClassLoader::init();


// Init Mail Configuration
if (isset($config['mail']['smtp'])) {
    Alkane\Mail\Mail::set_smtp_host($config['mail']['smtp']['host']);
    Alkane\Mail\Mail::set_smtp_user($config['mail']['smtp']['user']);
    Alkane\Mail\Mail::set_smtp_password($config['mail']['smtp']['password']);
    Alkane\Mail\Mail::set_smtp_encryption($config['mail']['smtp']['encryption']);
    Alkane\Mail\Mail::set_smtp_port($config['mail']['smtp']['port']);
    Alkane\Mail\Mail::set_smtp_from($config['mail']['smtp']['from']);
}

// Init Database Config
if (isset($dbconf)) {
    foreach ($dbconf as $key => $val) {
        Alkane\Database\Database::setConfig($key, $val);
    }
}

/**
 * on Databse connection failure 
 */
Alkane\Database\Database::onErrorConnection(function($errMsg) {
    die('Something went wrong :(');
});



/**
 * Initialize Router
 */
Alkane\Router\Router::route_from_json(_dir_('/route.json'));

Alkane\Router\Router::mime_types_from_file(_dir_('/mime.types'));

Alkane\Router\Router::onError(function ($errCode, $errMsg) {

    die('<br/><center><h2>' . $errCode . ' ' . $errMsg . '</h2></center>');

});

Alkane\Router\Router::run(Alkane\Router\Router::current_path());


