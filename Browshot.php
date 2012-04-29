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
 * @version   1.8.0
 * @link      http://browshot.com/
 * @link      http://browshot.com/api/documentation
 */

class Browshot
{
	const version = '1.8.0';

	public function __construct($key, $base = 'https://api.browshot.com/api/v1/', $debug = 0)
    {
		$this->_key = $key;
		$this->_base = $base;
		$this->_debug = $debug;


	}

	public function api_version()
	{
		list($major, $minor, $other) = split( "\.", Browshot::version, 3 );

		return $major . "." . $minor;
	}

	public function simple($parameters)
	{
		$url = $this->make_url('simple', $parameters);
		$res = $this->http_get($url);

		return array('code' => $res->getResponseCode(), 'image' => $res->getBody());
	}

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


	public function instance_list($parameters = array())
	{
		return $this->return_reply('instance/list', $parameters );
	}

	public function instance_info($id = 0, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_reply('instance/info', $parameters);
	}

	public function instance_create($parameters)
	{
		return $this->return_reply('instance/create', $parameters);
	}


	public function browser_list($parameters = array())
	{
		return $this->return_reply('browser/list', $parameters);
	}

	public function browser_info($id = 0, $parameters = array())
	{
 		$parameters['id'] = $id;
		return $this->return_reply('browser/info', $parameters);
	}

	public function browser_create($parameters = array())
	{
		return $this->return_reply('browser/create', $parameters);
	}


	public function screenshot_create($parameters = array())
	{
		if (array_key_exists('url', $parameters) == false) {
			$this->error("Missing url in screenshot_create");
			return $this->generic_error("Missing url in screenshot_create");
		}

		return $this->return_reply('screenshot/create', $parameters);
	}

	public function screenshot_info($id = 0, $parameters = array())
	{
 		$parameters['id'] = $id;
		return $this->return_reply('screenshot/info', $parameters);
	}

	public function screenshot_list($parameters = array())
	{
		return $this->return_reply('screenshot/list', $parameters);
	}

	public function screenshot_host($id = 0, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_reply('screenshot/host', $parameters);
	}

	public function screenshot_thumbnail($id = 0, $parameters = array())
	{
		if ($id == 0) {
			$this->error("Missing screenshit id in screenshot_thumbnail");
			return $this->generic_error("Missing id in screenshot_thumbnail");
		}

		$parameters['id'] = $id;
		$url = $this->make_url('screenshot/thumbnail', $parameters);
		$res = $this->http_get($url);

		if ($res->getResponseCode() == "200")
		{
			return $res->getBody();

		} 
		else
		{
			$this->error("Server sent back an error: " . $res->getResponseCode());
			return '';
		}
	}

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


	public function account_info($parameters = array())
	{
		return $this->return_reply('account/info', $parameters);
	}




	private function return_reply($action, $parameters = array())
	{
		$url = $this->make_url($action, $parameters);

		$res = $this->http_get($url);

		if ($res->getResponseCode() == "200")
		{
			return json_decode($res->getBody());

		} 
		else
		{
			$this->error("Server sent back an error: " . $res->getResponseCode());
			return $self->generic_error($res->getBody());

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
		$request = new HttpRequest($url, HttpRequest::METH_GET);
		$options = array(
			'timeout'	=> 90,
            'redirect'	=> 32,
			'useragent'	=> 'PHP Browshot ' . Browshot::version
		);
		$request->setOptions($options);

		$response = $request->send();
		return $response;
	}
}

?>