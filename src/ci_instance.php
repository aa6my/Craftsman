<?php 
/**
 * Part of Attire Library
 *
 * @author     David Sosa Valdes <https://github.com/davidsosavaldes>
 * @license    MIT License
 * @copyright  2016 David Sosa Valdes
 * @link       https://github.com/davidsosavaldes/Attire
 *
 * Based on https://raw.githubusercontent.com/kenjis/codeigniter-ss-twig/master/ci_instance.php
 * Thanks Kenji!
 */

error_reporting(-1);
ini_set('display_errors', 1);

define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

$system_path = 'vendor/codeigniter/framework/system';
$application_folder = 'vendor/codeigniter/framework/application';

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
	print("Your system folder path does not appear to be set correctly.\n");
	exit(3);
}

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
		print('Your application folder path does not appear to be set correctly.' 
		.' Please open the following file and correct this: '.SELF);
		exit(3);
	}
	define('APPPATH', BASEPATH.$application_folder.DIRECTORY_SEPARATOR);
}

define('VIEWPATH', APPPATH.'views'.DIRECTORY_SEPARATOR);

#########################################################################
# CodeIgniter instance
#########################################################################

require_once(BASEPATH.'core/Common.php');

if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/constants.php')) {
    require(APPPATH . 'config/' . ENVIRONMENT . '/constants.php');
} else {
    require(APPPATH . 'config/constants.php');
}

# Make sure some config variables are set correctly
get_config(array(
	'subclass_prefix' => 'Craftsman_',
	#'permitted_uri_chars' => '(\"[^\"]+\"|[^\\s\"]+)'
));

$composer_autoload = 'vendor/autoload.php';

if (! file_exists($composer_autoload)) 
{
	print('Your composer autoload file path does not appear to be set correctly.');
	exit(3);
}
else
{
	require_once($composer_autoload);
}

$charset = strtoupper(config_item('charset'));
ini_set('default_charset', $charset);

if (extension_loaded('mbstring')) {
    define('MB_ENABLED', TRUE);
    // mbstring.internal_encoding is deprecated starting with PHP 5.6
    // and it's usage triggers E_DEPRECATED messages.
    @ini_set('mbstring.internal_encoding', $charset);
    // This is required for mb_convert_encoding() to strip invalid characters.
    // That's utilized by CI_Utf8, but it's also done for consistency with iconv.
    mb_substitute_character('none');
} else {
    define('MB_ENABLED', FALSE);
}

// There's an ICONV_IMPL constant, but the PHP manual says that using
// iconv's predefined constants is "strongly discouraged".
if (extension_loaded('iconv')) {
    define('ICONV_ENABLED', TRUE);
    // iconv.internal_encoding is deprecated starting with PHP 5.6
    // and it's usage triggers E_DEPRECATED messages.
    @ini_set('iconv.internal_encoding', $charset);
} else {
    define('ICONV_ENABLED', FALSE);
}

$CFG  =& load_class('Config', 'core');
$UNI  =& load_class('Utf8', 'core');
$SEC  =& load_class('Security', 'core');
#$RTR  =& load_class('Router', 'core');
$IN   =& load_class('Input', 'core');
$LANG =& load_class('Lang', 'core');

require_once BASEPATH.'core/Controller.php';

function &get_instance() 
{
    return CI_Controller::get_instance();
}

return new CI_Controller();
