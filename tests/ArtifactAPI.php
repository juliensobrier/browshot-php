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

	private function assertCaptureOptions($request, $path, $options)
	{
		$this->assertSame($path, $request->path);
		$this->assertSame('test key&value', $request->query->key);
		foreach ($options as $key => $value) {
			$this->assertSame($value, $request->query->$key, $key);
		}
	}
}
