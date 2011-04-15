<?php
/*
 * Copyright (C) 2010 Marek Dajnowski <marek@dajnowski.net>
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

require_once( 'SessionDriver.class.php' );

class SessionGeneric implements SessionDriver
{
	public function get( $name )
	{
		return $_SESSION[ $name ];
	}

	public function set( $name, $value )
	{
		$_SESSION[ $name ] = $value;
	}

	public function is( $name )
	{
		return isset( $_SESSION[ $name ] );
	}

	public function delete( $name )
	{
		unset( $_SESSION[ $name ] );
	}
}
