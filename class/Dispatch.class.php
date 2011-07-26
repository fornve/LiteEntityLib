<?php
/*
 * Copyright (C) 2011 Marek Dajnowski <marek@dajnowski.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @package LiteEntityLib
 */

class Dispatch
{
	public function __construct( $default = 'Index', $uri = null )
	{
		$full_uri = str_replace( '/framework.php', '', $_SERVER[ 'REQUEST_URI' ] );

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
		$controller_file = "includes/controllers/{$controller_name}.class.php";

		if( file_exists( $controller_file ) )
		{
			require_once( $controller_file );
		}
		else
		{
			$controller = new Controller();
		}

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
					catch( ApplicationException $e )
					{
						$controller->error = $e;
						$controller->genericError();
					}
					catch( ApiException $e )
					{
						echo json_encode( array( 'error' => true, 'message' => $e->getMessage() ) );
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

		$controller->notFound();
	}
}

if( !function_exists( 'lcfirst' ) )
{
	function lcfirst($string) {
		$string{0} = strtolower($string{0});
		return $string;
	}
}

class ApiException extends Exception {}
class ApplicationException extends Exception{}
