<?php
/*
 * Copyright (C) 2009 Marek Dajnowski <marek@dajnowski.net>
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
class Form
{
	public $fields = array();
	public $submit = array( 'value' => 'Submit' );
	public $posted = false;

	function __construct( $action = "/", $method = 'get' )
	{
		$this->action = $action;
		$this->method = strtolower( $method );

		if( $this->method == strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) )
		{
			$this->posted = true;
		}
	}

	function validate()
	{
		$error = 0;

		if( !$this->posted )
		{
			return false;
		}

		if( $this->fields ) foreach( $this->fields as $field )
		{
			if( isset( $field->error ) )
				$error += count( $field->error );
		}

		if( $error > 0 )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}

/*
 * Translate function wrapper
 */
if( !function_exists( '__' ) )
{
	function __( $string )
	{
		return $string;
	}
}