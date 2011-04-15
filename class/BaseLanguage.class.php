<?php
/*
 * Copyright (C) 2009 Marek Dajnowski <marek@dajnowski.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class BaseLanguage
{
	public $languageCode = null;

	public function __construct()
	{
		$base = DefaultLanguage::DefaultContent();

		foreach( $base as $key => $value )
		{
			$this->$key = $value;
		}

		$content = $this->Content();

		if( $content ) foreach( $content as $key => $value )
		{
			$counter++;

			if( !$this->$key )
			{
				$this->$key = $value;
			}
			else
			{
				Filelog::Write( "Warning! Lang {$key} duplicated around line {$couner}." );
			}
		}
	}

	function __get( $property )
	{
		if( !isset( $this->$property ) )
		{
			Filelog::Write( "[LanguageError::StringNotFound]: {$property} [{$this->languageCode}]" );
			return $property;
		}
		else
		{
			return $this->property;
		}
	}

	public function _( $item )
	{
		$this->Get( $item );
	}

	public function Get( $item )
	{
		$array = $this->Content();
		return $array[ $item ];
	}
}
