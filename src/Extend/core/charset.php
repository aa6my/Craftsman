<?php

$charset = strtoupper(config_item('charset'));

ini_set('default_charset', $charset);
if (extension_loaded('mbstring'))
{
	define('MB_ENABLED', TRUE);
	@ini_set('mbstring.internal_encoding', $charset);
	mb_substitute_character('none');
}
else
{
	define('MB_ENABLED', FALSE);
}
if (extension_loaded('iconv'))
{
	define('ICONV_ENABLED', TRUE);
	@ini_set('iconv.internal_encoding', $charset);
}
else
{
	define('ICONV_ENABLED', FALSE);
}
if (is_php('5.6'))
{
	ini_set('php.internal_encoding', $charset);
}