<?php
/**
 * @package skeleton 
 * @subpackage framework
 */
define( 'PRODUCTION', false );
define( 'PROJECT_PATH', substr( __file__, 0, strlen( __file__ ) - 18 ) );
define( 'PROJECT_NAME', 'skeleton' );
define( 'SMARTY_TEMPLATES_DIR', PROJECT_PATH ."/templates/" );

define( 'DB_TYPE', 'mysql' ); // { 'mysql', 'sqlite' }
// define( 'DB_FILE', '/var/db/sqlite/'. PROJECT_NAME .'.db' ); // if sqlite

if( PRODUCTION )
{
	define( 'DB_HOST', 'localhost' );
    define( 'DB_NAME', 'dbname' );
    define( 'DB_USERNAME', 'some_password' );
    define( 'DB_PASSWORD', 'dbname' );

    define( 'ADMIN_EMAIL', 'admin@domainname.com' );
	define( 'TIMER', microtime( true ) );
	define( 'SMARTY_COMPILE_DIR', '/tmp/'. PROJECT_NAME );
	define( 'IMAGE_DIR', PROJECT_PATH .'/images/' );
	require_once( PROJECT_PATH .'/smarty/Smarty.class.php' );
}
else
{
	define( 'DB_HOST', 'localhost' );
//	define( 'DB_HOST', 'dajnowski.net' );
//	define( 'DB_NAME', 'fornve_lads' );
//    define( 'DB_USERNAME', 'some_password' );
//    define( 'DB_PASSWORD', 'fornve_lads' );

    define( 'DB_NAME', 'skeleton_test' );
    define( 'DB_USERNAME', 'skeleton_test' );
    define( 'DB_PASSWORD', 'skeleton_test' );

    define( 'ADMIN_EMAIL', 'marek@localhost' );
	define( 'TIMER', microtime( true ) );
	define( 'SMARTY_COMPILE_DIR', '/tmp/'. PROJECT_NAME );
	require_once( PROJECT_PATH .'/smarty/Smarty.class.php' );	
}
