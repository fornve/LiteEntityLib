<?php

class Input
{
	public $input_method;

	/*
	 * If this is true, values does not have to be fpecified first
	 */
	public $fetch_values_on_fly = false;

	function __get( $value )
	{
		if( $this->fetch_values_on_fly && !isset( $this->value ) )
		{
			// It requires this bodge due to weird behaviour of my php
			if( $this->input_method === 0 )
			{
				$this->$value = $_POST[ $value ];
			}
			elseif( $this->input_method === 1 )
			{
				$this->$value = filter_input( INPUT_GET, $value );
			}
			else
			{
				$this->$value = filter_input( $this->input_method, $value );
			}
		}
	}
}
