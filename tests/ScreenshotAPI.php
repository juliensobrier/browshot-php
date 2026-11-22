<?php

/**
 * Base test case class
 */
require_once 'TestCase.php';

/**
 * Tests the Screenshot API implementation of Browshot
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2015 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   1.14.0
 * @link      http://browshot.com/api/documentation#screenshot_create
 */
class ScreenshotAPI extends TestCase
{
	public function testScreenshotCreateWrong()
	{
		$screenshot = $this->browshot->screenshot_create();
		$this->assertArrayHasKey('error', (array)$screenshot);

		$screenshot = $this->browshot->screenshot_create(array('url' => 'http://'));
		$this->assertArrayHasKey('error', (array)$screenshot);
	}

	public function testScreenshotCreate()
	{
		$screenshot = $this->browshot->screenshot_create(array(
			'url' => 'https://browshot.com/',
			'instance_id' => 12,
			'details' => 2,
			'cache' => 86400
		));
		$this->assertFields($screenshot, array('id', 'status', 'priority', 'url', 'size', 'instance_id'));
		$this->assertContains($screenshot->status, array('in_queue', 'in_process', 'finished'));
		$this->assertEmpty($screenshot->error ?? '');
		if ($screenshot->status === 'finished') {
			$this->assertFields($screenshot, array('screenshot_url', 'width', 'height', 'request_time',
				'started', 'load', 'content', 'finished', 'response_code', 'final_url', 'content_type', 'scale'));
			$this->assertNotEmpty($screenshot->screenshot_url);
		}
	}

	public function testScreenshotListDetails()
	{
		$screenshot = $this->firstScreenshot($this->browshot->screenshot_list(array('details' => 2)));
		$this->assertFields($screenshot, array('id', 'status', 'priority', 'screenshot_url', 'url', 'size',
			'width', 'height', 'started', 'load', 'content', 'finished', 'instance_id', 'final_url',
			'response_code', 'content_type', 'scale'));
		$this->assertNoFields($screenshot, array('images', 'scripts'));
	}

	public function testScreenshotListDetails0()
	{
		$screenshot = $this->firstScreenshot($this->browshot->screenshot_list(array('details' => 0)));
		$this->assertFields($screenshot, array('id', 'status', 'screenshot_url', 'url', 'size'));
		$this->assertNoFields($screenshot, array('final_url', 'response_code', 'content_type', 'finished', 'images'));
	}

	public function testScreenshotInfoWrong()
	{
		$screenshot = $this->browshot->screenshot_info();
		$this->assertArrayHasKey('error', (array)$screenshot);
	}

	public function testScreenshotInfo()
	{
		$id = $this->fixtureId('BROWSHOT_SCREENSHOT_ID');
		$info = $this->browshot->screenshot_info($id);
		$this->assertFields($info, array('id', 'status', 'priority', 'url', 'size', 'instance_id'));
		$this->assertSame($id, (string)$info->id);
		$this->assertSame('finished', $info->status);
		$this->assertEmpty($info->error ?? '');
	}

	public function testScreenshotInfoDetails0()
	{
		$info = $this->finishedFixtureInfo(0);
		$this->assertFields($info, array('screenshot_url'));
		$this->assertNoFields($info, array('final_url', 'response_code', 'content_type', 'finished', 'images'));
	}

	public function testScreenshotInfoDetails1()
	{
		$info = $this->finishedFixtureInfo(1);
		$this->assertFields($info, array('screenshot_url', 'final_url', 'response_code', 'content_type'));
		$this->assertNoFields($info, array('started', 'finished', 'images', 'iframes'));
	}

	public function testScreenshotInfoDetails2()
	{
		$info = $this->finishedFixtureInfo(2);
		$this->assertFields($info, array('screenshot_url', 'final_url', 'response_code', 'content_type',
			'started', 'finished', 'load', 'content', 'request_time', 'scale', 'width', 'height'));
		$this->assertNoFields($info, array('images', 'iframes'));
	}

	public function testScreenshotHost()
	{
		$screenshot_id = $this->fixtureId('BROWSHOT_HOST_ID');
		foreach (array(array(), array('hosting' => 'browshot'), array('hosting' => 's3'),
			array('hosting' => 's3', 'bucket' => 'mine'), array('hosting' => 'cdn')) as $parameters) {
			$hosting = $this->browshot->screenshot_host($screenshot_id, $parameters);
			$this->assertTrue((is_array($hosting) && isset($hosting['error'])) || (is_object($hosting) && $hosting->{'status'} === 'error'));
		}
	}

	public function testScreenshotShare()
	{
		$share = $this->browshot->screenshot_share(0);
		$this->assertArrayHasKey('error', (array)$share, "Wrong screenshot ID");
	}

	public function testScreenshotSearch()
	{
		$fixture = $this->finishedFixtureInfo(2);
		$matches = (array)$this->browshot->screenshot_search($fixture->url);
		$this->assertArrayHasKey($fixture->id, $matches);
		$this->assertFields($matches[$fixture->id], array('final_url', 'response_code', 'content_type'));
	}

	public function testScreenshotMultiple()
	{
		$screenshots = $this->browshot->screenshot_multiple(array('http://mobilito.net/', 'http://browshot.com/'), array(12, 27));
		$this->assertEquals(4, count($screenshots), "Wrong number of screenshots");
	}
	private function finishedFixtureInfo($details)
	{
		$id = $this->fixtureId('BROWSHOT_SCREENSHOT_ID');
		$info = $this->browshot->screenshot_info($id, array('details' => $details));
		$this->assertFields($info, array('id', 'status'));
		$this->assertSame($id, (string)$info->id);
		$this->assertSame('finished', $info->status);

		return $info;
	}

	private function firstScreenshot($screenshots)
	{
		$results = (array)$screenshots;
		$this->assertNotEmpty($results);
		$this->assertGreaterThan(0, (int)key($results));

		return reset($results);
	}

	private function assertFields($data, $fields)
	{
		$this->assertIsObject($data);
		foreach ($fields as $field) {
			$this->assertArrayHasKey($field, (array)$data);
		}
	}

	private function assertNoFields($data, $fields)
	{
		foreach ($fields as $field) {
			$this->assertArrayNotHasKey($field, (array)$data);
		}
	}

}

?>
