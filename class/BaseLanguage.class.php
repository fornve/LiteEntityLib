<?php
	class BaseLanguage
	{
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
					echo "Warning! Lang {$key} duplicated around line {$couner}.";
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
