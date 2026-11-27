<?php

// namespace Browshot;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Browshot is a package to use the Browshot screenshot service REST API.
 *
 * Browshot is a service to take realtime screenshots of web pages in any 
 * screen size, as any device: Firefox, Chrome, iPhone, etc.
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2015 Julien Sobrier, Browshot
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @version   2.0.0
 * @link      http://browshot.com/
 * @link      http://browshot.com/api/documentation
 * @link      https://github.com/juliensobrier/browshot-php
 */

class Browshot
{
	const version = '2.0.0';

	public $_key;
	public $_base;
	public $_debug;
	private $auto_api;

	/**
	 * Constructor
	 *
	 * IMPORTANT: the order of the arguments base and debug has been switched
	 * @param  string API key
	 * @param  int    Set to 1 to print debug output to the standard output. 0 (disabled) by default.
	 * @param  string Optional. Base URL for all API requests. You should use the default base provided by the library. Be careful if you decide to use HTTP instead of HTTPS as your API key could be sniffed and your account could be used without your consent.
	 */	
	public function __construct($key, $debug = 0, $base = null)
	{
		$this->_key = $key;
		$this->auto_api = $base === null;
		$this->_base = $this->auto_api ? 'https://api.browshot.com/api/v2/' : $base;
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
	* Retrieve a screenshot in one function. Note: by default, screenshots are cached for 24 hours. You can tune this value with the cache=X parameter.
	*
	* @param array See <a href="https://browshot.com/api/documentation#simple">https://browshot.com/api/documentation#simple</a> for the list of possible arguments.
	*
	* @return array
	*/
	public function simple($parameters)
	{
		$res = $this->request('simple', $parameters);

		return array('code' => $res['http_code'], 'image' => $res['body'], 'error' => $res['error']);
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
			$this->error( $data['error'] );
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
		if ($id === 'chrome' || $id === 'firefox') {
			$parameters['browser'] = $id;
		} elseif ($id || !isset($parameters['browser'])) {
			$parameters['id'] = $id;
		}
		return $this->return_reply('browser/info', $parameters);
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
	* Get the HTML code of the rendered page.
	*
	* See <a href="https://browshot.com/api/documentation#screenshot_html">https://browshot.com/api/documentation#screenshot_html</a> for the response format and the list of arguments
	*
	* @param int   screenshot ID
	* @param array
	*
	* @return string
	*/
	public function screenshot_html($id = 0, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_string('screenshot/html', $parameters);
	}

	/**
	* Download the PDF generated by a finished screenshot requested with pdf=1.
	*
	* @param int|string Screenshot ID
	* @param array Optional request parameters
	* @return string PDF bytes, or an empty string on failure
	*/
	public function screenshot_pdf($id = 0, $parameters = array())
	{
		return $this->screenshot_artifact('screenshot/pdf', $id, $parameters, array('application/pdf'));
	}

	/**
	* Download the WebM, MP4 or GIF generated by a finished screenshot requested with video=1.
	*
	* @param int|string Screenshot ID
	* @param array Optional request parameters
	* @return string Recording bytes, or an empty string on failure
	*/
	public function screenshot_video($id = 0, $parameters = array())
	{
		return $this->screenshot_artifact('screenshot/video', $id, $parameters, array('video/webm', 'video/mp4', 'image/gif'));
	}

	/**
	* Download a PDF and save it to a local file.
	*
	* @param int|string Screenshot ID
	* @param string Destination file name
	* @param array Optional request parameters
	* @return string File name, or an empty string on failure
	*/
	public function screenshot_pdf_file($id, $file, $parameters = array())
	{
		return $this->screenshot_artifact_file('screenshot_pdf', $id, $file, $parameters);
	}

	/**
	* Download a video or GIF and save it to a local file.
	* An MP4 request can produce WebM. The supplied file name is used as given.
	*
	* @param int|string Screenshot ID
	* @param string Destination file name
	* @param array Optional request parameters
	* @return string File name, or an empty string on failure
	*/
	public function screenshot_video_file($id, $file, $parameters = array())
	{
		return $this->screenshot_artifact_file('screenshot_video', $id, $file, $parameters);
	}

	/**
	* Request multiple screenshots.
	*
	* See <a href="https://browshot.com/api/documentation#screenshot_multiple">https://browshot.com/api/documentation#screenshot_multiplel</a> for the response format and the list of arguments
	*
	* @param array   URLs
	* @param array   instances
	* @param array
	*
	* @return array
	*/
	public function screenshot_multiple($urls = array(), $instances = array(), $parameters = array())
	{
		$parameters['urls'] = $urls;
		$parameters['instances'] = $instances;
		return (array)$this->return_reply('screenshot/multiple', $parameters);
	}

	/**
	* Get details about screenshots requested.
	*
	* See <a href="https://browshot.com/api/documentation#screenshot_search">https://browshot.com/api/documentation#screenshot_search</a> for the response format and the list of arguments
	*
	* @param string   url
	* @param array
	*
	* @return array
	*/
	public function screenshot_search($url = '', $parameters = array())
	{
		$parameters['url'] = $url;
		return $this->return_reply('screenshot/search', $parameters);
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
			$this->error("Missing screenshot id in screenshot_thumbnail");
			return $this->generic_error("Missing id in screenshot_thumbnail");
		}

		$parameters['id'] = $id;
		$res = $this->request('screenshot/thumbnail', $parameters);

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
	public function screenshot_thumbnail_file($id, $file, $parameters = array())
	{
		if ($file == '') {
			$this->error("Missing file in screenshot_thumbnail_file");
			return $this->generic_error("Missing file in screenshot_thumbnail_file");
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
	 * Request multiple screenshots from a text file.
	 *
	 * See <a href="https://browshot.com/api/documentation#batch_create">https://browshot.com/api/documentation#batch_create</a> for the response format and the list of arguments
	 *
	 * @param string file name
	 * @param array
	 *
	 * @return array
	 */
	public function batch_create($file = '', $parameters = array())
	{
		if ($file == '') {
			$this->error("Missing file in batch_create");
			return $this->generic_error("Missing file in batch_create");
		}
	
		$parameters['file'] = $file;
		return $this->return_post_reply('batch/create', $parameters);
	}

	/**
	* Get details about screenshots requested.
	*
	* See <a href="https://browshot.com/api/documentation#batch_info">https://browshot.com/api/documentation#batch_info</a> for the response format and the list of arguments
	*
	* @param int    batch ID
	* @param array
	*
	* @return array
	*/
	public function batch_info($id = 0, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_reply('batch/info', $parameters);
	}

	/**
	 * Crawl a domain and screenshot all pages.
	 *
	 * See <a href="https://browshot.com/api/documentation#crawl_create">https://browshot.com/api/documentation#crawl_create</a> for the response format and the list of arguments
	 *
	 * @param string file name
	 * @param array
	 *
	 * @return array
	 */
	public function crawl_create($parameters = array())
	{
		return $this->return_reply('crawl/create', $parameters);
	}

	/**
	* Get details about screenshots requested.
	*
	* See <a href="https://browshot.com/api/documentation#crawl_info">https://browshot.com/api/documentation#crawl_info</a> for the response format and the list of arguments
	*
	* @param int    batch ID
	* @param array
	*
	* @return array
	*/
	public function crawl_info($id = 0, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_reply('crawl/info', $parameters);
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



	private function return_post_string($action, $parameters = array())
	{
		$file = $parameters['file'];
		unset($parameters['file']);

		$res = $this->request($action, $parameters, $file);

		if ($res['http_code'] != "200")
		{
			$this->error("Server sent back an error: " . $res['http_code']);
		}

		return $res['body'];
	}

	private function screenshot_artifact($action, $id, $parameters, $content_types)
	{
		if ((!is_int($id) && !is_string($id)) || !preg_match('/\A[1-9][0-9]{0,18}\z/', $id)) {
			$this->error('Invalid screenshot ID in ' . $action);
			return '';
		}

		$parameters['id'] = $id;
		$res = $this->request($action, $parameters);
		if ($res['error'] !== '' || $res['http_code'] != 200) {
			$this->error('Server sent back an error: ' . $res['http_code']);
			return '';
		}

		list($type) = explode(';', (string)$res['content_type'], 2);
		if (!is_string($res['body']) || $res['body'] === '' || !in_array(strtolower(trim($type)), $content_types, true)) {
			$this->error('Unexpected artifact response in ' . $action);
			return '';
		}

		return $res['body'];
	}

	private function screenshot_artifact_file($method, $id, $file, $parameters)
	{
		if (!is_string($file) || $file === '' || strpos($file, "\0") !== false) {
			$this->error('Invalid file in ' . $method);
			return '';
		}

		$content = $this->$method($id, $parameters);
		if ($content === '') {
			return '';
		}

		if (@file_put_contents($file, $content) !== strlen($content)) {
			$this->error('Could not save artifact in ' . $method);
			return '';
		}

		return $file;
	}

	private function return_string($action, $parameters = array())
	{
		$res = $this->request($action, $parameters);

		if ($res['http_code'] != "200")
		{
			$this->error("Server sent back an error: " . $res['http_code']);
		}

		$this->info($res['body']);
		return $res['body'];
	}

	private function return_reply($action, $parameters = array())
	{
		$res = $this->request($action, $parameters);
		$this->info($res['body']);

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

	private function return_post_reply($action, $parameters = array())
	{
		$file = $parameters['file'];
		unset($parameters['file']);

		$res = $this->request($action, $parameters, $file);
		$this->info($res['body']);

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

	public function device_list($parameters = array())
	{
		return $this->return_reply('device/list', $parameters);
	}

	public function app_list($parameters = array())
	{
		return $this->return_reply('app/list', $parameters);
	}

	public function app_info($id, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->return_reply('app/info', $parameters);
	}

	public function app_create($parameters)
	{
		return $this->app_request('app/create', $parameters);
	}

	public function app_update($id, $parameters)
	{
		$parameters['id'] = $id;
		return $this->app_request('app/update', $parameters);
	}

	public function app_delete($id, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->app_request('app/delete', $parameters);
	}

	public function app_test($id, $parameters = array())
	{
		$parameters['id'] = $id;
		return $this->app_request('app/test', $parameters);
	}

	public function app_run($screenshot_id, $apps, $parameters = array())
	{
		$parameters['screenshot_id'] = $screenshot_id;
		$parameters['apps'] = array_map('strval', $apps);
		return $this->app_request('app/run', $parameters);
	}

	private function app_request($action, $body)
	{
		$query = array();
		if (isset($body['api_version'])) {
			$query['api_version'] = $body['api_version'];
			unset($body['api_version']);
		}
		foreach (array('id', 'screenshot_id') as $field) {
			if (isset($body[$field]) && preg_match('/^[0-9]+$/D', (string)$body[$field])) {
				$body[$field] = (int)$body[$field];
			}
		}
		if (isset($body['isActive']) && in_array($body['isActive'], array(0, 1, '0', '1'), true)) {
			$body['isActive'] = (bool)$body['isActive'];
		}
		$res = $this->request($action, $query, null, $body);
		$reply = json_decode($res['body']);
		return $reply === null ? $this->generic_error($res['error']) : $reply;
	}

	private function version_for($action, $parameters)
	{
		if (isset($parameters['api_version']) && preg_match('/^[12]$/D', (string)$parameters['api_version'])) {
			return (string)$parameters['api_version'];
		}
		if (!$this->auto_api) {
			return null;
		}
		if (preg_match('~^(instance/|browser/create$|screenshot/host$)~', $action)) {
			return '1';
		}
		if ($action === 'browser/info' && isset($parameters['id']) &&
		    !in_array((string)$parameters['id'], array('40', '42'), true)) {
			return '1';
		}
		$legacy = '/^(instance_id|instance_ids|instances|screen|details|ids|hook(_.+)?|hosting(_.+)?|device_name|device_touch|device_mobile|device_landscape|user_agent)$/D';
		foreach ($parameters as $key => $value) {
			if ($value !== null && $value !== array() && preg_match($legacy, $key)) {
				return '1';
			}
		}
		return '2';
	}

	private function request($action, $parameters, $file = null, $body = null)
	{
		$url = $this->make_url($action, $parameters);
		$res = $file === null && $body === null ? $this->http_get($url) : $this->http_post($url, $file, $body);
		if (!$this->auto_api || isset($parameters['api_version']) ||
		    $this->version_for($action, $parameters) !== '2' ||
		    preg_match('~^(app/|device/|wait$|batch/download$|ping$|simple/)~', $action) ||
		    $res['http_code'] != 404 || parse_url($url, PHP_URL_PATH) !== parse_url($res['last_url'], PHP_URL_PATH) ||
		    !preg_match('~^application/json(?:;|$)~i', (string)$res['content_type'])) {
			return $res;
		}
		foreach ($parameters as $key => $value) {
			if ($value !== null && preg_match('/^(browser|country|screen_width|screen_height|mobile|device|profiles|apps(\[.*\])?)$/D', $key)) {
				return $res;
			}
		}
		$error = json_decode($res['body'], true);
		if (is_array($error) && isset($error['success'], $error['error'], $error['message']) &&
		    $error['success'] === false && $error['error'] === 'NOT_FOUND' && $error['message'] === 'Page not found') {
			$parameters['api_version'] = 1;
			$url = $this->make_url($action, $parameters);
			return $file === null ? $this->http_get($url) : $this->http_post($url, $file);
		}
		return $res;
	}

	private function make_url($action, $parameters)
	{
		$version = $this->version_for($action, $parameters);
		$base = rtrim($this->_base, '/') . '/';
		if ($version !== null) {
			$base = preg_replace('~/api/v[12]/$~', '/api/v' . $version . '/', $base);
		}
		$url = $base . $action . '?key=' . urlencode($this->_key);
		foreach ($parameters as $name => $value) {
			if ($name === 'api_version' || $value === null) {
				continue;
			}
			$key = $name === 'urls' ? 'url' : ($name === 'instances' ? 'instance_id' : $name);
			if ($key === 'profiles' && is_array($value)) {
				$value = json_encode($value);
			}
			if ($key === 'apps' && (is_object($value) || (is_array($value) && array_values($value) !== $value))) {
				foreach ($value as $app => $template) {
					if ($template !== null) {
						$url .= '&' . urlencode('apps[' . $app . ']') . '=' . urlencode($template);
					}
				}
				continue;
			}
			$values = is_array($value) && in_array($key, array('url', 'instance_id', 'apps'), true) ? $value : array($value);
			foreach ($values as $item) {
				if ($item !== null) {
					$url .= '&' . urlencode($key) . '=' . urlencode(is_bool($item) ? (int)$item : $item);
				}
			}
		}
		$this->info($base . $action);
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
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_HEADER, false); 
	    curl_setopt($ch, CURLOPT_TIMEOUT, 250); 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_MAXREDIRS, 32);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: 'PHP Browshot " . Browshot::version, "Connection: Keep-Alive"));
	    //curl_setopt($ch, CURLOPT_VERBOSE, true);

	    $response = curl_exec($ch);
	    
	    $error = curl_error($ch);
	    $result = array( 
              'body' => '',
              'error' => '',
              'http_code' => '',
              'last_url' => '',
              'content_type' => ''
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
	    $result['content_type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

	    curl_close($ch);
	    return $result;
	}

	private function http_post($url, $file = '', $body = null)
	{
		$data = array('file' => "@$file");
			
			
		if ($body !== null) {
			$data = json_encode((object)$body);
		} elseif (class_exists('CURLFile')) {
			$mime = mime_content_type($file);
			$info = pathinfo($file);
			$name = $info['basename'];
			$output = new CURLFile($file, $mime, $name);
				
			$data = array('file' => $output);
		}
	
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST,true);
		curl_setopt($ch, CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 250); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $body !== null);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
		$headers = array("User-Agent: 'PHP Browshot " . Browshot::version, "Connection: Keep-Alive");
		if ($body !== null) {
			$headers[] = 'Content-Type: application/json';
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$response = curl_exec($ch);
		
		$error = curl_error($ch);
		$result = array( 
						'body' => '',
						'error' => '',
						'http_code' => '',
						'last_url' => '',
						'content_type' => ''
		);
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);
	  $result['body'] = $body;
	    

		if ( $error != "" ) {
			$result['error'] = $error;
			curl_close($ch);

			return $result;
		}
		
		$result['http_code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		$result['last_url'] = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
		$result['content_type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		
		if ($result['http_code'] != 200) {
		try {
			$json = json_decode($result['body']);
			$result['error'] = $json->{'error'} || $error;
		}
		catch(Exception $e) {
			$this->error($e);
		}
		}

		curl_close($ch);
		return $result;
	}
}

?>
