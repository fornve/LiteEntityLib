<?php

/**
 * @package LiteEntityLib
 */
class Controller
{
	public $uri = 'index/index';
	public $action = 'index';
	public $controller = 'index';
	public $lang = 'default';
	public $params = null;

	public function __construct()
	{
		$this->startup();
		$this->visitHandle();
		$this->smarty = new Smarty();

		$this->smarty->compile_dir = SMARTY_COMPILE_DIR;
		//$this->smarty->template_dir = SMARTY_TEMPLATES_DIR . $this->lang .'/';
		$this->smarty->template_dir = SMARTY_TEMPLATES_DIR;

		if( !file_exists( $this->smarty->compile_dir ) )
		{
			mkdir( $this->smarty->compile_dir );
		}

		/*$lang = 'pl';

		I18n::load( $lang );
		I18n::lang( $lang );*/

	}

	public static function dispatch( $default = 'Index', $uri = null )
	{
		$full_uri = $_SERVER[ 'REQUEST_URI' ];

		$uri = explode( '?', $full_uri );

		$input = explode( '/', $uri[ 0 ] );

		//$input = Controller::Rewrite( $input );

		if( !empty( $input[ 1 ] ) )
		{

			$controller = str_replace( '-', ' ', $input[ 1 ] );
			$controller = str_replace( ' ', '', ucwords( $controller ) );
		}
		else
		{
			$controller = 'index';
		}

		if( !empty( $input[ 2 ] ) )
		{
			$action = lcfirst( $input[ 2 ] );
		}
		else
		{
			$action = 'index';
		}

		$controller_name = ucfirst( "{$controller}Controller" );

		try
		{
			if( class_exists( $controller_name ) )
			{
				$controller = new $controller_name( true );
				$controller->action = $action;
				$controller->controller = $controller_name;
				$controller->uri = $full_uri;

				if( method_exists( get_class( $controller ), $action ) ) // check if property exists
				{
					try
					{
						if( !isset( $input[ 3 ] ) ) { $input[ 3 ] = null; }
						if( !isset( $input[ 4 ] ) ) { $input[ 4 ] = null; }
						if( !isset( $input[ 5 ] ) ) { $input[ 5 ] = null; }
						if( !isset( $input[ 6 ] ) ) { $input[ 6 ] = null; }
						if( !isset( $input[ 7 ] ) ) { $input[ 7 ] = null; }
						if( !isset( $input[ 8 ] ) ) { $input[ 8 ] = null; }
						if( !isset( $input[ 9 ] ) ) { $input[ 9 ] = null; }
						if( !isset( $input[ 10 ] ) ) { $input[ 10 ] = null; }
						if( !isset( $input[ 11 ] ) ) { $input[ 11 ] = null; }
						if( !isset( $input[ 12 ] ) ) { $input[ 12 ] = null; }

						$controller->$action( $input[ 3 ], $input[ 4 ], $input[ 5 ], $input[ 6 ], $input[ 7 ], $input[ 8 ], $input[ 9 ], $input[ 10 ], $input[ 11 ], $input[ 12 ] );
					}
					catch( EntityException $e )
					{
						$controller->error = $e;
						$controller->EntityError();
					}
					catch( GenericException $e )
					{
						$controller->error = $e;
						$controller->genericError();
					}
					catch( Exception $e )
					{
						$controller->error = $e;
						$controller->genericError();
					}

					exit;
				}

			}
			else
			{
				$controller = new Controller();
			}
		}
		catch( Exception $e )
		{
			$controller = new Controller();
			$controller->error = $e;
			$controller->genericError();
			
			exit;
		}

		$controller->NotFound();
	}

	public function rewrite( $input )
	{
		if( is_numeric( $input[ 1 ] ) )
		{
			$input[ 3 ] = $input[ 1 ];
			$input[ 1 ] = 'Business';
			$input[ 2 ] = 'View';
		}
		elseif( $input[ 1 ] == 'robots.txt' )
		{
			$input[ 1 ] = 'Page';
			$input[ 2 ] = 'robots';
		}

		if( strlen( $input[ 1 ] ) < 1 ) // default Controller
			$input[ 1 ] = 'Index';

		if( !isset( $input[ 2 ] ) || strlen( $input[ 2 ] ) < 1 ) // default function
			$input[ 2 ] = 'Index';

		return $input;
	}

	public function catchableError()
	{
		Filelog::Write( "[Catchable error]: ". $this->error->getMessage() ."\n\n" );

		$this->assign( 'breadcrumbs', array( array( 'name' => 'Error' ) ) );
		$this->assign( 'error', $this->error->getMessage() );
		$this->assign( 'e', $this->error );
		echo $this->Decorate( "catchable-error.tpl" );
	}

	public function entityError()
	{
		Filelog::Write( "[Catchable error]: ". $this->error->getMessage() ."\n\n" );

		$this->assign( 'breadcrumbs', array( array( 'name' => 'Error' ) ) );
		$this->assign( 'error', $this->error->getMessage() );
		$this->assign( 'e', $this->error );
		echo $this->Decorate( "entity-error.tpl" );
	}

	public function genericError()
	{
		Filelog::Write( "[Catchable error]: ". $this->error->getMessage() ."\n\n" );

		$this->assign( 'breadcrumbs', array( array( 'name' => 'Error' ) ) );
		$this->assign( 'error', $this->error->getMessage() );
		echo $this->Decorate( "generic-error.tpl" );
	}

	public function notFound()
	{
		header( "HTTP/1.0 404 Not Found" );

		$this->assign( 'breadcrumbs', array( array( 'name' => 'Not found' ) ) );

		if( PRODUCTION )
		{
			echo $this->Decorate( "404.tpl" );
		}
		else
		{
			echo $this->Decorate( "404-development.tpl" );
		}
	}

	public function assign( $variable, $value )
	{
		$this->smarty->assign( $variable, $value );
	}

	public function fetch( $template, $dir = null )
	{
		if( !$dir ) $dir = $this->smarty->template_dir;
		$output = $this->smarty->fetch( $dir . $template );
		return $output;
	}

	public function decorate( $template, $dir = null )
	{
		if( !$dir ) $dir = $this->smarty->template_dir;

		$this->assign( 'logged_user', Session::get( 'logged_user' ) );

		try
		{
			$content = $this->smarty->fetch( $dir . $template );

			if( !filter_input( INPUT_GET, 'ajax' ) )
			{
				$this->assign( 'content', $content );

				$this->PreDecorate();

				if( !Config::get( 'site', 'production' ) )
				{
					$this->smarty->assign( 'memory_peak', round( memory_get_peak_usage() / 1024, 2 ) );
				}
				$content = $this->smarty->fetch( $dir .'decoration.tpl' );
				$this->PostDecorate();

			}
		}
		catch( SmartyException $e )
		{
			throw new Exception( $e->getMessage() );
		}

		Session::delete( 'user_notification' );

		return $content;
	}

	public static function getInput( $input_name, $input_type = INPUT_GET )
	{
		$input = Controller::inputs( array( $input_name ), $input_type );
		if( $input->$input_name )
		{
			return $input->$input_name;
		}
	}

	public function startup()
	{
	}

	public function preDecorate()
	{
		if( Session::is( 'logged_user' ) )
		{
			$user = User::retrieve( Session::get( 'logged_user' )->id );
			$this->smarty->assign( 'logged_user', $user );
		}

		if( !Config::get( 'site', 'production' ) )
		{
			$generated = floor ( 10000 * ( microtime( true ) - TIMER ) ) / 10000;
			$this->smarty->assign( 'generated', $generated );
			$this->smarty->assign( 'entity_query', Session::get( 'entity_query' ) );
			$this->smarty->assign( 'cache_query', Session::get( 'cache_query' ) );
			Session::delete( 'entity_query' );
			Session::delete( 'cache_query' );
		}
	}

	public function postDecorate()
	{
		Session::delete( 'user_notification' );
		//$cleaner = new PeriodicDaily();
	}

	public static function redirect( $url )
	{
		header( "Location: $url" );
		exit;
	}

	public static function redirectReferer()
	{
		self::Redirect( $_SERVER[ 'HTTP_REFERER' ] );
	}

	public static function visitHandle()
	{
		if( !Session::is( 'visitor' ) )
		{
		}
	}

	public static function userError( $text )
	{
		$notification = Session::get( 'user_notification' );
		$notification[] = array( 'type' => 'error', 'text' => $text );
		Session::set( 'user_notification', $notification );
	}

	public static function userNotice( $text )
	{
		$notification = Session::get( 'user_notification' );
		$notification[] = array( 'type' => 'notice', 'text' => $text );
		Session::set( 'user_notification', $notification );
	}

	public function translate( $params, $content )
	{
		if ( $content ) return __( $content );
	}
}

/**
 * Kohana translation/internationalization function. The PHP function
 * [strtr](http://php.net/strtr) is used for replacing parameters.
 *
 *    __('Welcome back, :user', array(':user' => $username));
 *
 * [!!] The target language is defined by [I18n::$lang]. The default source
 * language is defined by [I18n::$source].
 *
 * @uses    I18n::get
 * @param   string  text to translate
 * @param   array   values to replace in the translated text
 * @param   string  source language
 * @return  string
 */
function __($string, array $values = NULL, $source = NULL)
{
	if ( ! $source)
	{
		// Use the default source language
		$source = I18n::$source;
	}

	if ($source !== I18n::$lang)
	{
		// The message and target languages are different
		// Get the translation for this message
		$string = I18n::get($string);
	}

	return empty($values) ? $string : strtr($string, $values);
}
