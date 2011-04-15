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
/*
 * I got an idea after reading http://lesscss.org/ so is good to have it for php
 *
 * @version 1.0 - variables
 * @documentation: http://www.dajnowski.net/wiki/index.php5/Lesscss & http://lesscss.org
 *
 */
class Lesscss
{
	/*
 	 * If true, output css will be minimalised cutting off unnecessary spaces and libe breaks
	 */
	public $minimalise = true;

	/*
	 * Input lesscss compatible code
	 */
	public $lesscss = null;

	/*
	 * parsed lesscss params from $lesscss
	 */
	public $variables = null;

	/*
	 * parsed css code from $lesscss
	 */
	public $statements = null;

	/*
	 * pure css code as result of compiled $lesscss
	 */
	public $css = null;

	function __construct( $filename = null, $minimalise = false )
	{
		$this->lesscss = file_get_contents( $filename );
		$this->minimalise = $minimalise;

		if( !$this->lesscss )
			return null;

		$this->RemoveComments();
		$this->RetrieveStatements();
		$this->Compile();

		return $this->css;
	}

	public function RemoveComments()
	{
		$pattern = '@<![\s\S]*?--[ \t\n\r]*>@';
		$this->lesscss = preg_replace( $pattern, '', $this->lesscss );
	}

	public function RetrieveStatements()
	{
		$pre_elements = explode( '}', $this->lesscss );
		if( $pre_elements ) foreach( $pre_elements as $pre_element )
		{
			$pre_element = explode( '{', $pre_element );
			$pre_element[ 0 ] = trim( $this->FilterVariables( $pre_element[ 0 ] ) );

			if( $this->minimalise )
			{
				$match = array( "; ", ": ",  "\n", "\r", "\t" );
				$replace = array( ";", ":", "", "", "" );
				$pre_element[ 1 ] = str_replace( $match, $replace, $pre_element[ 1 ] );
			}

			if( strlen( $pre_element[ 0 ] ) > 0 )
				$this->statements[ $pre_element[ 0 ] ] = trim( $pre_element[ 1 ] );
		}

		//var_dump( $this->statements );
	}

	public function FilterVariables( $string )
	{
		// get variable
		$pattern = '!@.+;!';
		preg_match_all( $pattern, $string, $variables );

		if( $variables[ 0 ] ) foreach( $variables[ 0 ] as $variable_line )
		{
			$this->ParseVariableLine( $variable_line );
		}

		// wipe variable from string
		$pattern = '!@[\w\W]+;!';
		$string = preg_replace( $pattern, '', $string );
		return $string;
	}

	public function Compile()
	{
		foreach( $this->statements as $element => $statement )
		{
			if( $this->minimalise )
				$statement = "{$element}{{$statement}}";
			else
				$statement = "{$element} { {$statement} }";

			if( $this->variables ) foreach( $this->variables as $variable => $value )
			{
				$statement = str_replace( $variable, $value, $statement );
			}

			$lines[] = $statement;
		}

		if( $this->minimalise )
			$glue = "";
		else
			$glue = "\n";

		$this->css = implode( $glue, $lines );
	}

	private function ParsevariableLine( $string )
	{
		$elements = explode( ':', $string );
		$this->variables[ trim( $elements[ 0 ] ) ] = trim( str_replace( ';', '', $elements[ 1 ] ) );
	}
}

