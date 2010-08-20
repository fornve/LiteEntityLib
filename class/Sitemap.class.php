<?php
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
