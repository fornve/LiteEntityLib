<?php
/*
 * Example configuraion files
 *
 * TODO: all defines should be changed to Config::set as in database config
 */

define( 'INCLUDE_PATH', '/var/www/include' );

require_once( INCLUDE_PATH .'/class/Config.class.php' );
require_once( 'database.php' );

define( 'PRODUCTION', false );
define( 'PROJECT_PATH', substr( __file__, 0, strlen( __file__ ) - 18 ) );
define( 'PROJECT_NAME', 'project_name' );

/*
 * Memcache setup
 */
/*
define( 'CACHE_TYPE', 'memcache' );
define( 'CACHE_HOST', '127.0.0.1' );
define( 'CACHE_PORT', 11211 );
define( 'CACHE_LIFETIME', 12000 ); // in seconds
define( 'CACHE_PREFIX', 'BOOKING' );
*/

/*
 * Smarty libs - needed when application uses Smarty (http://smarty.net)
 */
// define( 'SMARTY_DIR', INCLUDE_PATH .'/Smarty-3.0.6/libs/' );

define( 'REGENERATE_SESSION', 1000 );
define( 'COOKIE_LIFETIME', 60*60*24*7 ); // a week

define( 'LOG_DIRECTORY', PROJECT_PATH .'/log' );

define( 'SMTP_SERVER', 'smtp.hostname' );
define( 'SMTP_USERNAME', 'smtp.user' );
define( 'SMTP_PASSWORD', 'smtp.password' );

define( 'DEVELOPER_EMAIL', 'developer@example.com' );

define( 'NOREPLY_NAME', 'Website Administrator (no reply)' );
define( 'NOREPLY_ADMIN_EMAIL', 'developer@example.com' );


/*
 * TODO: Move these lines into separate files
 */

spl_autoload_register( 'autoload' );

function autoload( $name )
{
	$path_array = array(
		'classes/',
		'entities/',
		'controllers/',
		INCLUDE_PATH .'/class/'
	 );

	foreach( $path_array as $path )
	{
		if( file_exists( $path . $name .'.class.php' ) )
		{
			include_once( $path . $name .'.class.php' );
			return true;
		}
		elseif( file_exists( $path . $name .'.php' ) )
		{
			include_once( $path . $name .'.php' );
			return true;
		}
	}
}


if( defined( 'SMARTY_DIR' ) )
{
	require_once( SMARTY_DIR .'/Smarty.class.php' );

	if( !file_exists( SMARTY_COMPILE_DIR ) )
	{
		mkdir( SMARTY_COMPILE_DIR );
	}
}