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
class Common
{
	static function getSimpleXMLElementValue( $xml )
	{
		$fils = 0;
		$tab = false;
		$array = array();
		foreach($xml->children() as $key => $value)
		{
			$child = Common::getSimpleXMLElementValue( $value );

			//To deal with the attributes
			foreach( $xml->attributes() as $ak=>$av )
			{
				$child[$ak] = (string)$av;

			}

			//Let see if the new child is not in the array
			if($tab==false && in_array($key,array_keys($array)))
			{
				//If this element is already in the array we will create an indexed array
				$tmp = $array[$key];
				$array[$key] = NULL;
				$array[$key][] = $tmp;
				$array[$key][] = $child;
				$tab = true;
			}
			elseif($tab == true)
			{
				//Add an element in an existing array
				$array[$key][] = $child;
			}
			else
			{
				//Add a simple element
				$array[$key] = $child;
			}

			$fils++;
		  }


		if($fils==0)
		{
			return (string)$xml;
		}

		return $array;

	}

	public static function HttpPost( $endpoint, $postdata )
	{
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $endpoint );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata );
		$return = curl_exec( $ch );
		curl_close( $ch );

		return $return;
	}

	static function Inputs( $array, $input_type = INPUT_GET )
	{
		$input = new stdClass;

		foreach ( $array as $key )
		{
			if( strlen( $key ) < 1 )
				die( 'Input key empty in Common::Inputs.' );

			$input->$key = addslashes( filter_input( $input_type, $key ) );
		}

		return $input;
	}

}
