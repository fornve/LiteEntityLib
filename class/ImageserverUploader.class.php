<?php
/**
 * Imageserver Uploader
 * 
 * Copyright (c) 2009 Marek Dajnowski
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Marek Dajnowski [marek@dajnowski.net] [http://sum-e.com/]
 * @version 0.1 
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
