<?php
	// made on kohana::system/helpers/valid.php
	class Form_Field
	{
		public $value;
		public $name;
		public $error;
		public $error_message;
		
		/*
		 * All available conditions:
		 * validate_email, validate_ip, validate_credit_card
		 */
		private $conditions = array();

		function __construct()
		{
				$this->name = get_class( $this );
		}

		function Validate()
		{
			if( $this->conditions ) foreach( $this->conditions as $condition )
			{
				$condition_name = $condition[ 'name' ];

				if( !$this->$condition_name( list( $condition[ 'parameters' ] ) ) )
				{
					$this->error = true;
					return false;
				}
				else
					return true;
			}
		}

		function Condition( $name, $parameters = null )
		{
			$this->conditions[] = array( 'name' => $name, 'parameters' => $parameters );
		}

		// Validators

		public static function validate_required()
		{
			return (bool) isset( $this->value );
		}

		public static function validate_email()
		{
			return (bool) preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', (string) $this->value );
		}

		public static function validate_ip( $ipv6 = FALSE, $allow_private = TRUE )
		{
			// By default do not allow private and reserved range IPs
	 		$flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
	 		if ($allow_private === TRUE)
 				$flags =  FILTER_FLAG_NO_RES_RANGE;
	
	 		if ($ipv6 === TRUE)
					return (bool) filter_var($this->value, FILTER_VALIDATE_IP, $flags);
	
			return (bool) filter_var($this->value, FILTER_VALIDATE_IP, $flags | FILTER_FLAG_IPV4);
		}
	}
