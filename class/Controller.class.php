<?php

/**
 * @package anadvert
 * @subpackage framework
 */
class Controller
{
	function __construct()
	{
		Controller::Startup();
		Controller::SetArea();

		$this->entity = new Entity;
		$this->smarty = new Smarty;
		$this->smarty->compile_dir = SMARTY_COMPILE_DIR;

		if( !file_exists( $this->smarty->compile_dir ) )
			mkdir( $this->smarty->compile_dir );
	}

	function dispatch( $default )
	{
		$uri = explode( '?', $_SERVER['REQUEST_URI'] );
		$input = explode( '/', $uri[ 0 ] );

		// rewrite rule for numeric ads
		if( is_numeric( $input[ 1 ] ) )
		{
			$input[ 3 ] = $input[ 1 ];
			$input[ 1 ] = 'Advert';
			$input[ 2 ] = 'View';
		}
		elseif( $input[ 1 ] == 'robots.txt' )
		{
			$input[ 1 ] = 'Page';
			$input[ 2 ] = 'robots';
		}

		if( strlen( $input[ 1 ] ) < 1 ) // default Controller
			$input[ 1 ] = 'Index';

		if( strlen( $input[ 2 ] ) < 1 ) // default function
			$input[ 2 ] = 'Index';

		$this->controller = $input[ 1 ];
		$this->action = $input[ 2 ];
		$controller_name = "{$input[1]}Controller";

		if( class_exists( $controller_name ) )
		{
			$controller = new $controller_name;

			$method = $input[ 2 ];
			
			if( strlen( $method ) == 0 )
				$method = 'Index';

			if( method_exists( get_class( $controller ), $method ) ) // check if property exists
			{
				$controller->$method( $input[ 3 ], $input[ 4 ] );
			}
			else
			{
				$this->NotFound();
			}
		}
		else
		{
			$this->NotFound();
		}
	}

	function assign( $variable, $value )
	{
		$this->smarty->assign( $variable, $value );
	}

	function fetch( $template, $dir = null )
	{
		if( !$dir ) $dir = SMARTY_TEMPLATES_DIR;
		$output = $this->smarty->fetch( $dir . $template );
		return $output;
	}

	function decorate( $template, $dir = null )
	{
		if( !$dir ) $dir = SMARTY_TEMPLATES_DIR;
		$this->assign( 'input', $this->input );
		$content = $this->smarty->fetch( $dir . $template );
		$this->assign( 'content', $content );
		$generated = floor ( 10000 * ( microtime( true ) - TIMER ) ) / 10000;
		$this->smarty->assign( 'generated', $generated );
		$this->smarty->assign( 'entity_query', $_SESSION[ 'entity_query' ] );
		unset( $_SESSION[ 'entity_query' ] );
		echo $this->smarty->fetch( 'decoration.tpl' );
		unset( $_SESSION[ 'user_notification' ] );
	}

	static function Inputs( $array, $input_type = INPUT_GET )
	{
		$input = new stdClass;
		
		foreach ( $array as $key )
		{
			$input->$key = addslashes( filter_input( $input_type, $key ) );
		}

		return $input;
	}

	static function GetInput( $input_name, $input_type = INPUT_GET )
	{
		$input = Controller::Inputs( array( $input_name ), $input_type );
		if( $input->$input_name )
		{
			return $input->$input_name;
		}
	}

	static function Startup()
	{
	}

	static function SetArea()
	{
		
		if( !$_SESSION['area'] )
			$_SESSION['area'] = Area::Retrieve( 34 ); // London as default
	}
}
  
