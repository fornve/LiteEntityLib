<?php
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
					$this->$key = $value;
				else
				{
					Filelog:Write( "Warning! Lang {$key} duplicated around line {$couner}." );
				}
			}
		}

		function __get( $property )
		{
			if( !isset( $this->$property ) )
			{
				FileLog::Write( "[LanguageError::StringNotFound]: {$property} [{$this->languageCode}]" );
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
