<?php
/*
 * I got an idea after reading http://lesscss.org/ so is good to have it for php
 * Marek Dajnowski <marek@dajnowski.net>
 *
 * version 1.0 - variables
 */
class Lesscss
{
	/*
	 * Input lesscss compatible code
	 */
	public $lesscss = null;

	/*
	 * parsed lesscss params from $lesscss
	 */
	public $lesscss_code = null;

	/*
	 * parsed css code from $lesscss
	 */
	public $lesscss_css = null;

	/*
	 * pure css code as result of compiled $lesscss
	 */
	public $purecss = null;

	function __construct( $filename = null )
	{
		$this->lesscss = file_get_contents( $filename );

		if( !$this->lesscss )
			return null;

		$this->lesscss_code = $this->ParseLessCode();
		$this->lesscss_css = $this->ParseCssCode();
		return $this->purecss = $this->Compile();
	}

	public function ParseLessCode()
	{
		$variables = self::RetrieveVariables( $this->ParseLessCode );
	}

	public function ParseCssCode()
	{

	}

	public function Compile()
	{

	}

	private static function RetrieveVariables( $code )
	{
		

		return $variables;
	}
}

