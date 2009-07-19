<?php
	class SitemapController extends Controller
	{
		function Index()
		{
		}

		function XML()
		{
			$sitemap = new Sitemap();

			$element = new SitemapUrl();
			$element->url =  'http://telljack.com';
			$element->lastmod = time();
			$element->changefreq = 'monthly';
			$element->priority = 0.8;

			$sitemap->AddElement( $element );
			$sitemap->AddElement( $element );


			echo $sitemap->GenerateXML();
		}
	}
