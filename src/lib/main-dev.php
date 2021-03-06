<?php
/**
 * Main.php file
 * Initialize main engine, define constants, and object instantiated
 * include functions needed by application
 * 
 * @category library\main.php file
 * @author   M.Noermoehammad
 * @license  https://opensource.org/licenses/MIT MIT License
 * @version  1.0
 * @since    Since Release 1.0
 * 
 */

ini_set('memory_limit', "5M");
error_reporting(-1);
#ini_set("session.cookie_secure", 1);  
#ini_set("session.cookie_lifetime", 0);  
ini_set("session.cookie_httponly", 1);
#ini_set("session.use_cookies", 1);
ini_set("session.use_only_cookies", 1);
#ini_set("session.use_strict_mode", 1);
#ini_set("session.use_trans_sid", 0);
ini_set('session.gc_maxlifetime', 1200);
ini_set('session.gc_probability',100);
ini_set('session.gc_divisor', 1);
#header("Content-Security-Policy: default-src https:; font-src 'unsafe-inline' data: https:; form-action 'self' https://yourdomain.com;img-src data: https:; child-src https:; object-src 'self' www.google-analytics.com ajax.googleapis.com platform-api.sharethis.com yourusername.disqus.com; script-src 'unsafe-inline' https:; style-src 'unsafe-inline' https:;");
#header('X-Frame-Options: DENY);
#date_default_timezone_set("GMT");

require __DIR__ . '/common.php';

if (!defined('PHP_EOL')) define('PHP_EOL', strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? "\r\n" : "\n");

$is_secure = false;

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {

    $is_secure = true;

} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    
    $is_secure = true;
    
}

if (!defined('APP_PROTOCOL')) define('APP_PROTOCOL', $protocol = $is_secure ? 'https' : 'http');

if (!defined('APP_HOSTNAME')) define('APP_HOSTNAME', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);

$config = null;

if (file_exists(APP_ROOT . 'config.php')) {

    $config = require __DIR__ . '/../config.php';

} else {

    $config = require __DIR__ . '/../config.sample.php';
    
}

#================================== call functions in directory lib/utility ===========================================
$function_directory = new RecursiveDirectoryIterator(__DIR__ . DS .'utility'. DS, FilesystemIterator::FOLLOW_SYMLINKS);
$filter_iterator = new RecursiveCallbackFilterIterator($function_directory, function ($current, $key, $iterator){
    
    // skip hidden files and directories
    if ($current->getFilename()[0] === '.') {
        return false;
    }
    
    if ($current->isDir()) {
        
        # only recurse into intended subdirectories
        return $current->getFilename() === __DIR__ . DS .'utility'. DS;
        
    } else {
        
        # only invoke files of interest
        return strpos($current->getFilename(), '.php');
        
    }
    
});
    
$files_dir_iterator = new RecursiveIteratorIterator($filter_iterator); 

foreach ($files_dir_iterator as $file) {
    
    include $file->getPathname();
    
}

#====================End of call functions in directory lib/utility=====================================================

// check if loader is exists
if (is_dir(APP_ROOT . APP_LIBRARY) && is_file(APP_ROOT . APP_LIBRARY . DS . 'Scriptloader.php')) {
 
    require __DIR__ . DS . 'Scriptloader.php';
      
}

if (is_readable(APP_ROOT.APP_LIBRARY.DS.'vendor/autoload.php')) {

    require __DIR__ . DS . 'vendor/autoload.php';
    
}

// load all libraries 
$library = array(
    APP_ROOT . APP_LIBRARY . DS . 'core'    . DS,
    APP_ROOT . APP_LIBRARY . DS . 'dao'     . DS,
    APP_ROOT . APP_LIBRARY . DS . 'event'   . DS,
    APP_ROOT . APP_LIBRARY . DS . 'app'     . DS,
    APP_ROOT . APP_LIBRARY . DS . 'plugins' . DS
);

get_server_load();

load_engine($library);

call_htmlpurifier();

#===================== RULES ===========================

// rules adapted by dispatcher to route request

/*******************************************************  

     ### '/picture/some-text/51' 
    'picture' => "/picture/(?'text'[^/]+)/(?'id'\d+)",    
    
     ### '/album/album-slug'
    'album' => "/album/(?'album'[\w\-]+)",              
    
     ### '/category/category-slug'
    'category' => "/category/(?'category'[\w\-]+)",        
    
     ### '/blog?p=255'
    'blog' => "/blog([^/]*)",                       
    
     ### '/page/about', '/page/contact'
    'page' => "/page/(?'page'[^/]+)
     
    ### '/post/60/post-slug'
    'post' => "/post/(?'id'\d+)/(?'post'[\w\-]+)",     
    
     ### '/'
    'home' => "/"                                        

 *******************************************************/

$rules = array(
    'home'     => "/",                               
    'category' => "/category/(?'category'[\w\-]+)",
    'blog'     => "/blog([^/]*)",
    'page'     => "/page/(?'page'[^/]+)",
    'single'   => "/post/(?'id'\d+)/(?'post'[\w\-]+)",
    'search'   => "(?'search'[\w\-]+)"
);

#==================== END OF RULES ======================

// an instantiation of Database connection
$dbc = DbFactory::connect(['mysql:host='.$config['db']['host'].';dbname='.$config['db']['name'], $config['db']['user'], $config['db']['pass']]);

// Register rules and an instance of database connection
Registry::setAll(array('dbc' => $dbc, 'route' => $rules));

/* an instances of class that necessary for the system
 * please do not change this below variable 
 * 
 * @var $searchPost invoked by search functionality
 * @var $frontPaginator called by front pagination funtionality
 * @var $postFeeds run by rss feed functionality
 * @var $sanitizer adapted by sanitize functionality
 * @var $userDao, $validator, $authenticator --
 * these are collection of objects or instances of classes 
 * that will be run by the system.
 * 
 */
$searchPost = new SearchFinder($dbc);
$frontPaginator = new Paginator(10, 'p');
$sanitizer = new Sanitize();
$userDao = new UserDao();
$userToken = new UserTokenDao();
$validator = new FormValidator();
$authenticator = new Authentication($userDao, $userToken, $validator);
$ubench = new Ubench();

// These line (179 and 180) are experimental code. You do not need it.
# $bones = new Bones();
# $request = new RequestHandler($bones);

# set_exception_handler('LogError::exceptionHandler');
# set_error_handler('LogError::errorHandler');
# register_shutdown_function('scriptlog_shutdown_fatal');

if (!start_session_on_site()) {
     
    ob_start();
    
}

$errors = [];