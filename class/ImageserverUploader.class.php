<?php
/*
 * Copyright (C) 2009 Marek Dajnowski <marek@dajnowski.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class ImageserverUploader {
	protected $token;
	protected $service;

	private $upload_endpoint;
	private $delete_endpoint;

	public function __construct( $service, $token )
	{
		$this->upload_endpoint = Config::get( 'imageserver.endpoint.upload' );
		$this->delete_endpoint = Config::get( 'imageserver.endpoint.delete' );

		// Check requirements
		if (!extension_loaded('curl'))
		{
			throw new Exception('ImageserverUploader requires the cURL extension.');
		}

		$this->service = $service;
		$this->token = $token;
	}

	public function upload( $filename, $remoteDir='/' )
	{
		if (!file_exists($filename) or !is_file($filename) or !is_readable($filename))
		{
			throw new Exception("File '$filename' does not exist or is not readable.");
		}

		if (!is_string($remoteDir))
		{
			throw new Exception("Remote directory must be a string, is ".gettype($remoteDir)." instead.");
		}

		$data = $this->request( $this->upload_endpoint, true,
			array(
				'plain' => 'yes',
				'file' => '@'.$filename,
				'dest' => $remoteDir,
				'token' => $this->token,
				'service' => $this->service
			));

		error_log( 'Uploading file: '. $filename .' into dir '. $remoteDir );

		return unserialize( $data );

		/*if (strpos($data, 'HTTP/1.1 200 OK') === false)
			return false;

		return true;*/
	}

	public function delete( $filename, $remoteDir = '/' )
	{
		  $data = $this->request( $this->delete_endpoint, true,
			array(
				'plain' => 'yes',
				'file' => $filename,
				'dest' => $remoteDir,
				'token' => $this->token,
				'service' => $this->service
			));
		if (strpos($data, 'HTTP/1.1 200 OK') === false)
			return false;

		return true;
	}

	protected function request($url, $post=false, $postData=array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($post) {
			curl_setopt($ch, CURLOPT_POST, $post);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}

		$data = curl_exec($ch);

		if ($data === false)
			throw new Exception('Cannot execute request: '.curl_error($ch));

		curl_close($ch);

		return $data;
	}
}
