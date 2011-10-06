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
class View
{
	protected $template_data = array();
	protected $template = null;

	public function __construct( $template = null )
	{
		$this->template = $template;
	}

	public function __set( $name, $value )
	{
		$this->template_data[ $name ] = $value;
	}

	public function __toString()
	{
		return 'use $view->fetch() instead';
	}

	public function fetch( $template = null )
	{
		$template = $template ? $template : $this->template;

		if( !$template )
		{
			throw new Exception( "Template can not be null." );
		}

		if( !file_exists( $template ) )
		{
			throw new Exception( "Template {$template} not found." );
		}

		foreach( $this->template_data as $variable_name => $variable_value )
		{
			$$variable_name = $variable_value;
			unset( $variable_name );
			unset( $variable_value );
		}

		ob_start();
		include( $template );
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
