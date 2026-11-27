<?php

require_once __DIR__ . '/TestCase.php';

class ArtifactAPI extends TestCase
{
	const SCREENSHOT_ID = '9007199254740991';
	protected $requiresApiKey = false;
	private static $server;
	private static $log;
	private static $base;

	public static function setUpBeforeClass(): void
	{
		$socket = stream_socket_server('tcp://127.0.0.1:0');
		$address = stream_socket_get_name($socket, false);
		fclose($socket);
		self::$base = 'http://' . $address . '/api/v1/';
		self::$log = tmpfile();

		self::$server = proc_open(
			array(PHP_BINARY, '-S', $address, __DIR__ . '/fixture.php'),
			array(0 => array('pipe', 'r'), 1 => self::$log, 2 => self::$log),
			$pipes
		);

		if (!is_resource(self::$server)) {
			throw new RuntimeException('Could not start the artifact fixture server');
		}

		fclose($pipes[0]);
		for ($attempt = 0; $attempt < 100; $attempt++) {
			$connection = @stream_socket_client('tcp://' . $address, $errno, $error, 0.1);
			if ($connection) {
				fclose($connection);
				return;
			}
			usleep(20000);
		}

		throw new RuntimeException('Artifact fixture server did not start');
	}

	public static function tearDownAfterClass(): void
	{
		if (is_resource(self::$server)) {
			proc_terminate(self::$server);
			proc_close(self::$server);
		}
		if (is_resource(self::$log)) {
			fclose(self::$log);
		}
	}

	protected function setUp(): void
	{
		$this->browshot = new Browshot('test key&value', 0, self::$base);
	}

	public static function artifactFormats(): array
	{
		return array(
			'PDF' => array('screenshot_pdf', array(), "%PDF-1.7\n\x00\x80\xff"),
			'WebM' => array('screenshot_video', array('format' => 'webm'), "\x1a\x45\xdf\xa3\x00\x80\xff"),
			'MP4' => array('screenshot_video', array('format' => 'mp4'), "\x00\x00\x00\x10ftyp\x00\x80\xff"),
			'GIF' => array('screenshot_video', array('format' => 'gif'), "GIF89a\x00\x80\xff")
		);
	}

	/**
	 * @dataProvider artifactFormats
	 */
	public function testDownloadsAndSavesBinaryArtifact($method, $parameters, $bytes)
	{
		$parameters['id'] = '1';
		$this->assertSame($bytes, $this->browshot->$method(self::SCREENSHOT_ID, $parameters));
		$this->assertSame('1', $parameters['id']);

		$file = tempnam(sys_get_temp_dir(), 'browshot-');
		try {
			$save = $method . '_file';
			$this->assertSame($file, $this->browshot->$save(self::SCREENSHOT_ID, $file, $parameters));
			$this->assertSame($bytes, file_get_contents($file));
		} finally {
			unlink($file);
		}
	}

	public function testAcceptsWebmFallbackForMp4()
	{
		$artifact = $this->browshot->screenshot_video(self::SCREENSHOT_ID, array('video_format' => 'mp4'));
		$this->assertSame("\x1a\x45\xdf\xa3\x00\x80\xff", $artifact);
	}

	public function testRejectsErrorEmptyAndTruncatedResponses()
	{
		foreach (array('screenshot_pdf', 'screenshot_video') as $method) {
			foreach (array(400, 401, 404, 503) as $status) {
				$this->assertSame('', $this->browshot->$method(self::SCREENSHOT_ID, array('status' => $status)));
			}
			foreach (array('empty', 'unexpected', 'truncated') as $response) {
				$this->assertSame('', $this->browshot->$method(self::SCREENSHOT_ID, array($response => 1)));
			}
		}
		$this->assertSame('', $this->browshot->screenshot_pdf(self::SCREENSHOT_ID, array('format' => 'gif')));
		$this->assertSame('', $this->browshot->screenshot_video(self::SCREENSHOT_ID, array('format' => 'pdf')));
	}

	public function testFileErrorsReturnAnEmptyStringAndFailedDownloadsDoNotOverwriteFiles()
	{
		$file = tempnam(sys_get_temp_dir(), 'browshot-');
		file_put_contents($file, 'original');
		try {
			$this->assertSame('', $this->browshot->screenshot_pdf_file(self::SCREENSHOT_ID, $file, array('status' => 404)));
			$this->assertSame('original', file_get_contents($file));
			$this->assertSame('', $this->browshot->screenshot_video_file(self::SCREENSHOT_ID, $file . '/unwritable.gif'));
		} finally {
			unlink($file);
		}

		$this->assertSame('', $this->browshot->screenshot_pdf_file(self::SCREENSHOT_ID, ''));
		$this->assertSame('', $this->browshot->screenshot_video_file(self::SCREENSHOT_ID, "bad\0file"));
	}

	public function testRejectsInvalidIds()
	{
		foreach (array(0, -1, '', 'abc', '1.5', '1e5', '9999999999999999999999', array(), null) as $id) {
			$this->assertSame('', $this->browshot->screenshot_pdf($id));
			$this->assertSame('', $this->browshot->screenshot_video($id));
		}
	}

	public function testForwardsNewOptionsThroughExistingCaptureMethods()
	{
		$options = array(
			'pdf' => '1', 'pdf_format' => 'a4', 'pdf_background' => '1', 'pdf_landscape' => '0',
			'pdf_one_page' => '0', 'pdf_margin' => '10mm', 'pdf_margin_top' => '0.4in',
			'pdf_margin_bottom' => '1cm', 'pdf_margin_left' => '6px', 'pdf_margin_right' => '2mm',
			'video' => '1', 'video_format' => 'gif', 'video_duration' => '5', 'video_quality' => '24',
			'video_fps' => '20', 'video_scale' => '0.5', 'device_scale' => '1', 'hide_ads' => '1'
		);
		$url = 'https://example.com/?a=1&b=two words';

		$single = $this->browshot->screenshot_create(array('url' => $url, 'instance_id' => 373) + $options);
		$this->assertCaptureOptions($single, '/api/v1/screenshot/create', $options);
		$this->assertSame($url, $single->query->url);
		$this->assertSame('373', $single->query->instance_id);

		$multiple = $this->browshot->screenshot_multiple(array($url, 'https://example.net/'), array(373, 377), $options);
		$this->assertCaptureOptions((object)$multiple, '/api/v1/screenshot/multiple', $options);
		$this->assertStringContainsString('&url=' . urlencode($url) . '&url=' . urlencode('https://example.net/'), $multiple['rawQuery']);
		$this->assertStringContainsString('&instance_id=373&instance_id=377', $multiple['rawQuery']);

		$crawl = $this->browshot->crawl_create(array('url' => $url, 'domain' => 'example.com', 'instance_id' => 381) + $options);
		$this->assertCaptureOptions($crawl, '/api/v1/crawl/create', $options);
		$this->assertSame('381', $crawl->query->instance_id);

		$file = tempnam(sys_get_temp_dir(), 'browshot-');
		file_put_contents($file, $url);
		try {
			$batch = $this->browshot->batch_create($file, array('instance_id' => 385) + $options);
			$this->assertCaptureOptions($batch, '/api/v1/batch/create', $options);
			$this->assertSame('POST', $batch->method);
			$this->assertSame('385', $batch->query->instance_id);
			$this->assertSame($url, $batch->file);
		} finally {
			unlink($file);
		}
	}

	public function testAutomaticRoutingForCapturesAndInfo()
	{
		$client = $this->automaticClient();
		$legacy = $client->screenshot_create(array('url' => 'https://example.com', 'instance_id' => 373));
		$this->assertSame('/api/v1/screenshot/create', $legacy->path);
		$shared = $client->screenshot_info(123);
		$this->assertSame('/api/v2/screenshot/info', $shared->path);
		$this->assertFalse(property_exists($shared->query, 'details'));
		$this->assertSame('/api/v1/screenshot/info', $client->screenshot_info(123, array('details' => 0))->path);
		$forced = $client->screenshot_info(123, array('api_version' => 1));
		$this->assertSame('/api/v1/screenshot/info', $forced->path);
		$this->assertFalse(property_exists($forced->query, 'api_version'));
		$this->assertSame('/api/v1/screenshot/info', $client->screenshot_info(123, array('offline' => 1))->path);
		$this->assertIsArray($client->screenshot_info(123, array('offline' => 1, 'api_version' => 2)));
	}

	public function testUsesV2ForBulkCapturesAndAppRequests()
	{
		$client = $this->automaticClient();
		$urls = array('https://example.com', 'https://example.net/');
		$profiles = array(array('browser' => 'firefox', 'country' => 'fr'), array('device' => 'iphone_15_pro'));
		$result = $client->screenshot_multiple($urls, array(), array('profiles' => $profiles, 'apps' => array('uploads' => 'shot.png')));
		$this->assertSame('/api/v2/screenshot/multiple', $result['path']);
		$this->assertStringContainsString('url=' . urlencode($urls[0]) . '&url=' . urlencode($urls[1]), $result['rawQuery']);
		$this->assertSame($profiles, json_decode($result['query']->profiles, true));
		$this->assertSame('shot.png', $result['query']->apps->uploads);
		$this->assertSame('/api/v2/crawl/create', $client->crawl_create(array('domain' => 'example.com', 'country' => 'fr'))->path);
		$this->assertSame('/api/v2/device/list', $client->device_list()->path);
		$this->assertSame('/api/v2/app/list', $client->app_list()->path);
		$this->assertSame('37', $client->app_info(37)->query->id);

		$create = $client->app_create(array('type' => 'webhook', 'app_id' => 'notify', 'name' => 'Notifications',
			'config' => array('url' => 'https://example.com/capture-events')));
		$this->assertSame('/api/v2/app/create', $create->path);
		$this->assertSame('POST', $create->method);
		$this->assertSame('https://example.com/capture-events', $create->json->config->url);
		$update = $client->app_update('37', array('isActive' => 0));
		$this->assertSame(37, $update->json->id);
		$this->assertFalse($update->json->isActive);
		$this->assertSame(37, $client->app_delete(37)->json->id);
		$this->assertSame('/api/v2/app/test', $client->app_test(37)->path);
		$run = $client->app_run('123', array('uploads'));
		$this->assertSame(123, $run->json->screenshot_id);
		$this->assertSame(array('uploads'), $run->json->apps);
	}

	public function testV2BatchUploadsWithoutInstanceId()
	{
		$client = $this->automaticClient();
		$file = tempnam(sys_get_temp_dir(), 'browshot-');
		try {
			file_put_contents($file, 'https://example.com/');
			$result = $client->batch_create($file, array('country' => 'fr'));
			$this->assertSame('/api/v2/batch/create', $result->path);
			$this->assertSame('POST', $result->method);
			$this->assertSame('fr', $result->query->country);
			$this->assertFalse(property_exists($result->query, 'instance_id'));
			$this->assertSame('https://example.com/', $result->file);
		} finally {
			unlink($file);
		}
	}

	public function testV2SimpleFollowsWaitAndResultRedirects()
	{
		$client = $this->automaticClient();
		$options = array('url' => 'https://example.com/', 'country' => 'fr');
		$png = "\x89PNG\r\n\x1a\n\xff";
		$screenshot = $client->simple($options);
		$this->assertSame(200, $screenshot['code']);
		$this->assertSame($png, $screenshot['image']);
		$file = tempnam(sys_get_temp_dir(), 'browshot-');
		try {
			$result = $client->simple_file($file, $options);
			$this->assertSame($file, $result['file']);
			$this->assertSame($png, file_get_contents($file));
		} finally {
			unlink($file);
		}
	}

	private function automaticClient()
	{
		$client = new Browshot('test key&value');
		$client->_base = str_replace('/v1/', '/v2/', self::$base);
		return $client;
	}

	private function assertCaptureOptions($request, $path, $options)
	{
		$this->assertSame($path, $request->path);
		$this->assertSame('test key&value', $request->query->key);
		foreach ($options as $key => $value) {
			$this->assertSame($value, $request->query->$key, $key);
		}
	}
}
