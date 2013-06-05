<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Browshot is a package to use the Browshot screenshot service REST API.
 *
 * Browshot is a service to take realtime screemshots of web pages in any 
 * screen size, as any device: iPhone©, iPad©, Android©, Nook©, PC, etc.
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2012 Julien Sobrier, Browshot
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @version   1.10.0
 * @link      http://browshot.com/
 * @link      http://browshot.com/api/documentation
 * @link      https://github.com/juliensobrier/browshot-php
 */

class Browshot
{
	const version = '1.10.1';

	/**
	 * Constructor
     *
	 * @param  string API key
	 * @param  string Optional. Base URL for all API requests. You should use the default base provided by the library. Be careful if you decide to use HTTP instead of HTTPS as your API key could be sniffed and your account could be used without your consent.
     * @param  int    Set to 1 to print debug output to the standard output. 0 (disabled) by default.
	 */	
	public function __construct($key, $base = 'https://api.browshot.com/api/v1/', $debug = 0)
    {
		$this->_key = $key;
		$this->_base = $base;
		$this->_debug = $debug;


	}

    /**
     * Get API version.
     *
     * The library version matches closely the API version it handles: Browshot 1.0.0 is the first release for the API 1.0, Browshot 1.1.1 is the second release for the API 1.1, etc.
     *
     * Browshot can handle most the API updates within the same major version, e.g. WebService::Browshot 1.0.0 should be compatible with the API 1.1 or 1.2.
     *
     * @return string
     */
	public function api_version()
	{
		list($major, $minor, $other) = explode( ".", Browshot::version, 3 );

		return $major . "." . $minor;
	}

    /**
     * Retrieve a screenshot in one function. Note: by default, screenshots are cached for 24 hours. You can tune this valu with the cache=X parameter.
     *
     * @param array See <a href="https://browshot.com/api/documentation#simple">https://browshot.com/api/documentation#simple</a> for the list of possible arguments.
     *
     * @return array
     */
	public function simple($parameters)
	{
		$url = $this->make_url('simple', $parameters);
		$res = $this->http_get($url);

		return array('code' => $res['http_code'], 'image' => $res['body']);
	}

    /**
     * Retrieve a screenshot and save it localy in one function. Note: by default, screenshots are cached for 24 hours. You can tune this valu with the cache=X parameter.
     *
     * @param array See <a href="https://browshot.com/api/documentation#simple">https://browshot.com/api/documentation#simple</a> for the list of possible arguments.
     *
     * @return array
     */
	public function simple_file($file, $parameters)
	{
		$data = $this->simple($parameters);

		if ($data['image'] != '') {
			$fp = fopen($file, 'w');
			fwrite($fp, $data['image']);
			fclose($fp);
	
			return array('code' => $data['code'], 'file' => $file);
		}
		else {
			$this->error("No thumbnail retrieved");
			return array('code' => $data['code'], 'file' => '');
		}
	}

    /**
     * Return the list of instances.
     *
     * See <a href="https://browshot.com/api/documentation#instance_list">https://browshot.com/api/documentation#instance_list</a> for the response format and the list of arguments
     *
     * @param array
     *
     * @return array
     */
	public function instance_list($parameters = array())
	{
		return $this->return_reply('instance/list', $parameters );
	}

    /**
     * Return the details of an instance.
     *
     * See <a href="https://browshot.com/api/documentation#instance_info">https://browshot.com/api/documentation#instance_info</a> for the response format and the list of arguments
     *
     * @param int   instance ID
     * @param array
     *
     * @return array
     */
	public function instance_info($id = 0, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_reply('instance/info', $parameters);
	}

    /**
     * Create a private instance.
     *
     * See <a href="https://browshot.com/api/documentation#instance_create">https://browshot.com/api/documentation#instance_create</a> for the response format and the list of arguments
     *
     * @param array
     *
     * @return array
     */
	public function instance_create($parameters)
	{
		return $this->return_reply('instance/create', $parameters);
	}

    /**
     * Return the list of browsers.
     *
     * See <a href="https://browshot.com/api/documentation#browser_list">https://browshot.com/api/documentation#browser_list</a> for the response format and the list of arguments
     *
     * @param array
     *
     * @return array
     */
	public function browser_list($parameters = array())
	{
		return $this->return_reply('browser/list', $parameters);
	}

    /**
     * Return the details of a browser.
     *
     * See <a href="https://browshot.com/api/documentation#browser_info">https://browshot.com/api/documentation#browser_info</a> for the response format and the list of arguments
     *
     * @param int   browser ID
     * @param array
     *
     * @return array
     */
	public function browser_info($id = 0, $parameters = array())
	{
 		$parameters['id'] = $id;
		return $this->return_reply('browser/info', $parameters);
	}

    /**
     * Create a custom browser.
     *
     * See <a href="https://browshot.com/api/documentation#browser_create">https://browshot.com/api/documentation#browser_create</a> for the response format and the list of arguments
     *
     * @param array
     *
     * @return array
     */
	public function browser_create($parameters = array())
	{
		return $this->return_reply('browser/create', $parameters);
	}

    /**
     * Request a screenshot. Note: by default, screenshots are cached for 24 hours. You can tune this valu with the cache=X parameter.
     *
     * See <a href="https://browshot.com/api/documentation#screenshot_create">https://browshot.com/api/documentation#screenshot_create</a> for the response format and the list of arguments
     *
     * @param array  Must contain the url parameter
     *
     * @return array
     */
	public function screenshot_create($parameters = array())
	{
		if (array_key_exists('url', $parameters) == false) {
			$this->error("Missing url in screenshot_create");
			return $this->generic_error("Missing url in screenshot_create");
		}

		return $this->return_reply('screenshot/create', $parameters);
	}

    /**
     * Get information about a screenshot requested previously.
     *
     * See <a href="https://browshot.com/api/documentation#screenshot_info">https://browshot.com/api/documentation#screenshot_info</a> for the response format and the list of arguments
     *
     * @param int   screenshot ID
     * @param array
     *
     * @return array
     */
	public function screenshot_info($id = 0, $parameters = array())
	{
 		$parameters['id'] = $id;
		return $this->return_reply('screenshot/info', $parameters);
	}

    /**
     * Get details about screenshots requested.
     *
     * See <a href="https://browshot.com/api/documentation#screenshot_list">https://browshot.com/api/documentation#screenshot_list</a> for the response format and the list of arguments
     *
     * @param array
     *
     * @return array
     */
	public function screenshot_list($parameters = array())
	{
		return $this->return_reply('screenshot/list', $parameters);
	}

    /**
     * Host a screenshot or thumbnail.
     *
     * See <a href="https://browshot.com/api/documentation#screenshot_host">https://browshot.com/api/documentation#screenshot_host</a> for the response format and the list of arguments
     *
     * @param int   screenshot ID
     * @param array
     *
     * @return array
     */
	public function screenshot_host($id = 0, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_reply('screenshot/host', $parameters);
	}

    /**
     * Share a screenshot.
     *
     * See <a href="https://browshot.com/api/documentation#screenshot_share">https://browshot.com/api/documentation#screenshot_share</a> for the response format and the list of arguments
     *
     * @param int   screenshot ID
     * @param array
     *
     * @return array
     */
	public function screenshot_share($id = 0, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_reply('screenshot/share', $parameters);
	}

    /**
     * Delete details of a screenshot.
     *
     * See <a href="https://browshot.com/api/documentation#screenshot_delete">https://browshot.com/api/documentation#screenshot_delete</a> for the response format and the list of arguments
     *
     * @param int   screenshot ID
     * @param array
     *
     * @return array
     */
	public function screenshot_delete($id = 0, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_reply('screenshot/delete', $parameters);
	}

    /**
     * Retrieve the screenshot, or a thumbnail.
     *
     * See <a href="https://browshot.com/api/documentation#screenshot_thumbnail">https://browshot.com/api/documentation#screenshot_thumbnail</a> for the response format and the list of arguments
     *
     * @param int   screenshot ID
     * @param array
     *
     * @return string Return an empty string if the image could not be retrieved, otherwise return the PNG content.
     */
	public function screenshot_thumbnail($id = 0, $parameters = array())
	{
		if ($id == 0) {
			$this->error("Missing screenshit id in screenshot_thumbnail");
			return $this->generic_error("Missing id in screenshot_thumbnail");
		}

		$parameters['id'] = $id;
		$url = $this->make_url('screenshot/thumbnail', $parameters);
		$res = $this->http_get($url);

		if ($res['error'] == '' && $res['http_code'] == "200")
		{
			return $res['body'];

		} 
		else
		{
			$this->error("Server sent back an error: " . $res['http_code']);
			return '';
		}
	}

    /**
     * Retrieve the screenshot, or a thumbnail, and save it to a file.
     *
     * See <a href="https://browshot.com/api/documentation#screenshot_thumbnail">https://browshot.com/api/documentation#screenshot_thumbnail</a> for the response format and the list of arguments
     *
     * @param int    screenshot ID
     * @param string file name
     * @param array
     *
     * @return string Return an empty string if the image could not be retrieved or saved, otherwise return the file name.
     */
	public function screenshot_thumbnail_file($id = 0, $file, $parameters = array())
	{
		if ($file == '') {
			$this->error("Missing file in screenshot_thumbnail_file");
			return $this->generic_error("MMissing file in screenshot_thumbnail_file");
		}
	
		if ($id == 0) {
			$this->error("Missing screenshot id in screenshot_thumbnail_file");
			return $this->generic_error("Missing id in screenshot_thumbnail_file");
		}

		$content = $this->screenshot_thumbnail($id, $parameters);
		if ($content != '') {
			$fp = fopen($file, 'w');
			fwrite($fp, $content);
			fclose($fp);
	
			return $file;
		}
		else {
			$this->error("No thumbnail retrieved");
			return '';
		}
	}

    /**
     * Return information about the user account.
     *
     * See <a href="https://browshot.com/api/documentation#account_info">https://browshot.com/api/documentation#account_info</a> for the response format and the list of arguments
     *
     * @param array
     *
     * @return array
     */
	public function account_info($parameters = array())
	{
		return $this->return_reply('account/info', $parameters);
	}




	private function return_reply($action, $parameters = array())
	{
		$url = $this->make_url($action, $parameters);

		$res = $this->http_get($url);

		if ($res['error'] == '' && $res['http_code'] == "200")
		{
			return json_decode($res['body']);

		} 
		else
		{
			$this->error("Server sent back an error: " . $res['http_code']);
			return $this->generic_error($res['error']);

		}
	}

	private function make_url($action, $parameters)
	{
		$url = $this->_base . $action . "?key=" . urlencode($this->_key);
		
		foreach ($parameters as $key => $value) {
			$url .= '&' . urlencode($key) . '=' . urlencode($value);
		}

		$this->info($url);
		return $url;
	}

	private function generic_error($message)
	{
		return array('error' => 1, 'message' => $message);
	}

	private function info($message)
	{
		if ($this->_debug == 1) {
			echo $message . "\n";
		}
	
		return '';
	}

	private function error($message)
	{
// 		$this->last_error = $message;

		if ($this->_debug == 1) {
			echo $message . "\n";
		}
	
		return '';
	}

	private function http_get($url)
	{
		/*$request = new HttpRequest($url, HttpRequest::METH_GET);
		$options = array(
			'timeout'		=> 90,
			'connecttimeout'	=> 30,
			'redirect'		=> 32,
			'useragent'		=> 'PHP Browshot ' . Browshot::version
		);
		$request->setOptions($options);

		$response = $request->send();
		return $response;*/

	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_HEADER, 0); 
	    curl_setopt($ch, CURLOPT_TIMEOUT, 90); 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_MAXREDIRS, 32);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: 'PHP Browshot " . Browshot::version)); 

	    $response = curl_exec($ch);
	    
	    $error = curl_error($ch);
	    $result = array( 
              'body' => '',
              'error' => '',
              'http_code' => '',
              'last_url' => ''
	    );

	    if ( $error != "" ) {
	      $result['error'] = $error;
	      curl_close($ch);

	      return $result;
	    }

	    $header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
	    $result['body'] = $response;
	    $result['http_code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	    $result['last_url'] = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);

	    curl_close($ch);
	    return $result;
	}
}

?>