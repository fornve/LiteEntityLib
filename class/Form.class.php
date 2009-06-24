<?php
	class Form
	{
			function __controller( $fields, $method = 'GET' )
			{
					if( $method == 'GET' )
							$this->request_method = INPUT_GET;
					elseif( $method == 'POST' )
							$this->request_method = INPUT_POST;

					$this->fields = $fields;

					if( $this->fields ) foreach( $this->fields as $field )
					{
							$this->$field = new Form_Field();
							$this->$field->value = filter_input( $this->request_method, $field );
					}
			}

			function Validate()
			{
				if( $this->fields ) foreach( $this->fields as $field )
				{
					if( !$this->$field->Validate() )
						$errors++;
				}

				return $errors;
			}	

	}

