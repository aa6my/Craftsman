<?php 

error_reporting(-1);
ini_set('display_errors', 1);

$system_path = 'system';
$application_folder = 'application';
$view_folder = '';

# Make sure some config variables are set correctly
$assign_to_config['subclass_prefix'] = 'MY_';

if (($_temp = realpath($system_path)) !== FALSE)
{
	$system_path = $_temp.'/';
}
else
{
	$system_path = rtrim($system_path, '/').'/';
}

if (! is_dir($system_path))
{
	header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
	echo "Your system folder path does not appear to be set correctly.\n";
	exit(3);
}

define('ENVIRONMENT', 'development');
define('BASEPATH', str_replace('\\', '/', $system_path));

if (is_dir($application_folder))
{
	if (($_temp = realpath($application_folder)) !== FALSE)
	{
		$application_folder = $_temp;
	}
	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);
}
else
{
	if ( ! is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your application folder path does not appear to be set correctly.' 
		.' Please open the following file and correct this: '.SELF;
		exit(3);
	}
	define('APPPATH', BASEPATH.$application_folder.DIRECTORY_SEPARATOR);
}

define('VIEWPATH', APPPATH.'views'.DIRECTORY_SEPARATOR);

#########################################################################
# CodeIgniter instance
#########################################################################

require_once(BASEPATH.'core/Common.php');
require_once(ROOTPATH.'src/Extend/core/security.php');

get_config(array('subclass_prefix' => $assign_to_config['subclass_prefix']));

$composer_autoload = 'vendor/autoload.php';

if (! file_exists($composer_autoload)) 
{
	header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
	echo 'Your composer autoload file path does not appear to be set correctly.';
	exit(3);
}
else
{
	require_once($composer_autoload);
}

$CFG  =& load_class('Config', 'core');

require_once(ROOTPATH.'src/Extend/core/charset.php');
require_once(BASEPATH.'core/compat/standard.php');

$UNI  =& load_class('Utf8', 'core');
#$RTR  =& load_class('Router', 'core', NULL);
$SEC  =& load_class('Security', 'core');
#$IN	  =& load_class('Input', 'core');
$LANG =& load_class('Lang', 'core');

require_once BASEPATH.'core/Controller.php';

function &get_instance() {
    return CI_Controller::get_instance();
}

$instance = new CI_Controller();

return $instance;
