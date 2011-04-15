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
class Sitemap
{
	public $elements;
	public $version = "1.0";
	public $encoding = "UTF-8";
	public $urlset_xmlns = "http://www.sitemaps.org/schemas/sitemap/0.9";

	function AddElement( $element )
	{
		$this->elements[] = $element;
	}

	function GenerateXML()
	{
		$xmldom = new DomDocument( $this->version, $this->encoding );
		$urlset = $xmldom->createElement( 'urlset' );
		$urlset->setAttribute( 'xmlns', $this->encoding );

		if( $this->elements ) foreach( $this->elements as $element )
		{
			$url = $xmldom->createElement( 'url' );

			foreach( $element->schema as $node )
			{
				if( isset( $element->$node ) )
				{
					$node_xml = $xmldom->createElement( $node, $element->$node );
					$url->appendChild( $node_xml );
				}
			}

			$urlset->appendChild( $url );
		}

		$xmldom->appendChild( $urlset );

		return str_replace( "><", ">\n<", $xmldom->SaveXML() );

	}

}
