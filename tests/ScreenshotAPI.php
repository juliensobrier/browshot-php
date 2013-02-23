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
 * @copyright 2012 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   1.9.0
 * @link      http://browshot.com/api/documentation#screenshot_create
 */
class ScreenshotAPI extends TestCase
{
// 	screenshot is not actually created for test account, so the reply may not match our parameters

	public function testScreenshotCreateWrong()
	{
		$screenshot = $this->browshot->screenshot_create();
		$this->assertTrue(array_key_exists('error', 	$screenshot), 	"Screenshot failed");

		$screenshot = $this->browshot->screenshot_create(array('url' => '-'));
		$this->assertTrue(array_key_exists('error', 	$screenshot), 	"Screenshot failed");
	}

	public function testScreenshotCreate()
	{
		$screenshot = $this->browshot->screenshot_create(array('url' => 'http://browshot.com/'));
		$this->assertFalse(array_key_exists('error', 	$screenshot));

		$this->assertTrue(array_key_exists('id', 		$screenshot), 	"Screenshot ID is present");
		$this->assertTrue(array_key_exists('status', 	$screenshot),	"Screenshot status is present");
		$this->assertTrue(array_key_exists('priority', 	$screenshot),	"Screenshot priority is present");

		if ($screenshot->{'status'} == 'finished') {
			$this->assertTrue(array_key_exists('screenshot_url',	$screenshot),	"Screenshot screenshot_url is present");
			$this->assertTrue(array_key_exists('url', 			 	$screenshot),	"Screenshot url is present");
			$this->assertTrue(array_key_exists('size', 			 	$screenshot),	"Screenshot size is present");
			$this->assertTrue(array_key_exists('width', 			$screenshot),	"Screenshot width is present");
			$this->assertTrue(array_key_exists('height', 			$screenshot),	"Screenshot height is present");
			$this->assertTrue(array_key_exists('request_time', 		$screenshot),	"Screenshot request_time is present");
			$this->assertTrue(array_key_exists('started', 			$screenshot),	"Screenshot started is present");
			$this->assertTrue(array_key_exists('load', 			 	$screenshot),	"Screenshot load is present");
			$this->assertTrue(array_key_exists('content', 			$screenshot),	"Screenshot content is present");
			$this->assertTrue(array_key_exists('finished', 			$screenshot),	"Screenshot finished is present");
			$this->assertTrue(array_key_exists('instance_id', 		$screenshot),	"Screenshot instance_id is present");
			$this->assertTrue(array_key_exists('response_code', 	$screenshot),	"Screenshot response_code is present");
			$this->assertTrue(array_key_exists('final_url', 		$screenshot),	"Screenshot final_url is present");
			$this->assertTrue(array_key_exists('content_type', 		$screenshot),	"Screenshot content_type is present");
			$this->assertTrue(array_key_exists('scale', 			$screenshot),	"Screenshot scale is present");
			$this->assertTrue(array_key_exists('cost', 				$screenshot),	"Screenshot cost is present");
		}
	}

	public function testScreenshotListDetails()
	{
		$screenshots = $this->browshot->screenshot_list();
		$this->assertGreaterThan(0,  	count(array_keys((array)$screenshots)) );

		$screenshot_ids = array_keys((array)$screenshots);
		$screenshot_id = $screenshot_ids[0];
		$screenshot = $screenshots->{$screenshot_id};

		$this->assertGreaterThan(0, 						$screenshot_id, "Screenshot ID is correct");
		$this->assertTrue(array_key_exists('id', 			$screenshot),	"Screenshot ID is present");
		$this->assertTrue(array_key_exists('status', 		$screenshot),	"Screenshot status is present");
		$this->assertTrue(array_key_exists('priority', 		$screenshot),	"Screenshot priority is present");
		$this->assertTrue(array_key_exists('screenshot_url',$screenshot),	"Screenshot screenshot_url is present");
		$this->assertTrue(array_key_exists('url', 			$screenshot),	"Screenshot url is present");
		$this->assertTrue(array_key_exists('size', 			$screenshot),	"Screenshot size is present");
		$this->assertTrue(array_key_exists('width', 		$screenshot),	"Screenshot width is present");
		$this->assertTrue(array_key_exists('height', 		$screenshot),	"Screenshot height is present");
		$this->assertTrue(array_key_exists('request_time', 	$screenshot),	"Screenshot request_time is present");
		$this->assertTrue(array_key_exists('started', 		$screenshot),	"Screenshot started is present");
		$this->assertTrue(array_key_exists('load', 			$screenshot),	"Screenshot load is present");
		$this->assertTrue(array_key_exists('content', 		$screenshot),	"Screenshot content is present");
		$this->assertTrue(array_key_exists('finished', 		$screenshot),	"Screenshot finished is present");
		$this->assertTrue(array_key_exists('instance_id', 	$screenshot),	"Screenshot instance_id is present");
		$this->assertTrue(array_key_exists('final_url', 	$screenshot),	"Screenshot final_url is present");
		$this->assertTrue(array_key_exists('response_code', $screenshot),	"Screenshot response_code is NOT present");
		$this->assertTrue(array_key_exists('content_type', 	$screenshot),	"Screenshot content_type is NOT present");
		$this->assertTrue(array_key_exists('scale', 		$screenshot),	"Screenshot scale is present");
		$this->assertTrue(array_key_exists('cost', 		$screenshot),		"Screenshot cost is present");

		$this->assertFalse(array_key_exists('images', 		$screenshot),	"Screenshot images are NOT present");
		$this->assertFalse(array_key_exists('scripts', 		$screenshot),	"Screenshot scripts are NOT present");
	}

	public function testScreenshotListDetails0()
	{
		$screenshots = $this->browshot->screenshot_list(array('details' => 0));
		$this->assertGreaterThan(0,  	count(array_keys((array)$screenshots)) );

		$screenshot_ids = array_keys((array)$screenshots);
		$screenshot_id = $screenshot_ids[0];
		$screenshot = $screenshots->{$screenshot_id};

// 		var_dump($screenshot);
		$this->assertGreaterThan(0, 						$screenshot_id, "Screenshot ID is correct");
		$this->assertTrue(array_key_exists('id', 			$screenshot),	"Screenshot ID is present");
		$this->assertTrue(array_key_exists('final_url', 	$screenshot),	"Screenshot final_url is present");

		$this->assertFalse(array_key_exists('response_code', $screenshot),	"Screenshot response_code is NOT present");
		$this->assertFalse(array_key_exists('content_type', $screenshot),	"Screenshot content_type is NOT present");
		$this->assertFalse(array_key_exists('finished', 	$screenshot),	"Screenshot finished is NOT present");
		$this->assertFalse(array_key_exists('images', 		$screenshot),	"Screenshot images are NOT present");
	}

	public function testScreenshotInfoWrong()
	{
		$screenshot = $this->browshot->screenshot_info();
		$this->assertTrue(array_key_exists('error', 	$screenshot), 	"Screenshot ID is missing");
	}

	public function testScreenshotInfo()
	{
		$screenshots = $this->browshot->screenshot_list(array('details' => 0));
		$screenshot_ids = array_keys((array)$screenshots);
		$screenshot_id = $screenshot_ids[0];
		$screenshot = $screenshots->{$screenshot_id};

		$info = $this->browshot->screenshot_info($screenshot_id);
		$this->assertFalse(array_key_exists('error', 	$info), 	"Screenshot ID is correct");

		$this->assertTrue(array_key_exists('id', 		$info), 		"Screenshot ID is present");
		$this->assertTrue(array_key_exists('status', 	$info), 		"Screenshot status is present");
		$this->assertTrue(array_key_exists('priority', 	$info), 		"Screenshot priority is present");

		if ($info->{'status'} == 'finished') {
			$this->assertTrue(array_key_exists('screenshot_url',$info), "Screenshot screenshot_url is present");
			$this->assertTrue(array_key_exists('url', 			$info), "Screenshot url is present");
			$this->assertTrue(array_key_exists('size', 			$info), "Screenshot size is present");
			$this->assertTrue(array_key_exists('width', 		$info), "Screenshot width is present");
			$this->assertTrue(array_key_exists('height', 		$info), "Screenshot height is present");
			$this->assertTrue(array_key_exists('request_time', 	$info), "Screenshot request_time is present");
			$this->assertTrue(array_key_exists('started', 		$info), "Screenshot started is present");
			$this->assertTrue(array_key_exists('load', 			$info), "Screenshot load is present");
			$this->assertTrue(array_key_exists('content', 		$info), "Screenshot content is present");
			$this->assertTrue(array_key_exists('finished', 		$info), "Screenshot finished is present");
			$this->assertTrue(array_key_exists('instance_id', 	$info), "Screenshot instance_id is present");
			$this->assertTrue(array_key_exists('response_code', $info), "Screenshot response_code is present");
			$this->assertTrue(array_key_exists('final_url', 	$info), "Screenshot final_url is present");
			$this->assertTrue(array_key_exists('content_type', 	$info), "Screenshot content_type is present");
			$this->assertTrue(array_key_exists('scale', 		$info), "Screenshot scale is present");
			$this->assertTrue(array_key_exists('cost', 			$info), "Screenshot cost is present");
		
			$this->assertFalse(array_key_exists('images', 		$info), "Screenshot images are NOT present");
		}
	}

	public function testScreenshotInfoDetails0()
	{
		$screenshots = $this->browshot->screenshot_list(array('details' => 0));
		$screenshot_ids = array_keys((array)$screenshots);
		$screenshot_id = $screenshot_ids[0];
		$screenshot = $screenshots->{$screenshot_id};

		$info = $this->browshot->screenshot_info($screenshot_id, array('details' => 0));
		$this->assertFalse(array_key_exists('error', 	$info), 	"Screenshot ID is correct");

		if ($info->{'status'} == 'finished') {
			$this->assertTrue(array_key_exists('screenshot_url',$info), "Screenshot screenshot_url is present");
			$this->assertTrue(array_key_exists('final_url', 	$info), "Screenshot final_url is present");

			$this->assertFalse(array_key_exists('response_code', $info), "Screenshot response_code is NOT present");
			$this->assertFalse(array_key_exists('content_type', $info), "Screenshot content_type is NOT present");
			$this->assertFalse(array_key_exists('finished', 	$info), "Screenshot finished is NOT present");
			$this->assertFalse(array_key_exists('images', 		$info), "Screenshot images are NOT present");
		}
	}

	public function testScreenshotInfoDetails1()
	{
		$screenshots = $this->browshot->screenshot_list(array('details' => 0));
		$screenshot_ids = array_keys((array)$screenshots);
		$screenshot_id = $screenshot_ids[0];
		$screenshot = $screenshots->{$screenshot_id};

		$info = $this->browshot->screenshot_info($screenshot_id, array('details' => 1));
		$this->assertFalse(array_key_exists('error', 	$info), 	"Screenshot ID is correct");

		if ($info->{'status'} == 'finished') {
			$this->assertTrue(array_key_exists('screenshot_url',$info), "Screenshot screenshot_url is present");
			$this->assertTrue(array_key_exists('final_url', 	$info), "Screenshot final_url is present");
			$this->assertTrue(array_key_exists('response_code', $info), "Screenshot response_code is present");
			$this->assertTrue(array_key_exists('content_type', 	$info), "Screenshot content_type is present");

			$this->assertFalse(array_key_exists('started', 		$info), "Screenshot started is NOT present");
			$this->assertFalse(array_key_exists('finished', 	$info), "Screenshot finished is NOT present");
			$this->assertFalse(array_key_exists('images', 		$info), "Screenshot images are NOT present");
			$this->assertFalse(array_key_exists('iframes', 		$info), "Screenshot iframes are NOT present");
		}
	}

	public function testScreenshotInfoDetails2()
	{
		$screenshots = $this->browshot->screenshot_list(array('details' => 0));
		$screenshot_ids = array_keys((array)$screenshots);
		$screenshot_id = $screenshot_ids[0];
		$screenshot = $screenshots->{$screenshot_id};

		$info = $this->browshot->screenshot_info($screenshot_id, array('details' => 2));
		$this->assertFalse(array_key_exists('error', 	$info), 	"Screenshot ID is correct");

		if ($info->{'status'} == 'finished') {
			$this->assertTrue(array_key_exists('screenshot_url',$info), "Screenshot screenshot_url is present");
			$this->assertTrue(array_key_exists('final_url', 	$info), "Screenshot final_url is present");
			$this->assertTrue(array_key_exists('response_code', $info), "Screenshot response_code is present");
			$this->assertTrue(array_key_exists('content_type', 	$info), "Screenshot content_type is present");
			$this->assertTrue(array_key_exists('started', 		$info), "Screenshot started is present");
			$this->assertTrue(array_key_exists('finished', 		$info), "Screenshot finished is present");

			$this->assertFalse(array_key_exists('images', 		$info), "Screenshot images are NOT present");
			$this->assertFalse(array_key_exists('iframes', 		$info), "Screenshot iframes are NOT present");
		}
	}

	public function testScreenshotInfoDetails3()
	{
		$screenshots = $this->browshot->screenshot_list(array('details' => 0));
		$screenshot_ids = array_keys((array)$screenshots);
		$screenshot_id = $screenshot_ids[0];
		$screenshot = $screenshots->{$screenshot_id};

		$info = $this->browshot->screenshot_info($screenshot_id, array('details' => 3));
		$this->assertFalse(array_key_exists('error', 	$info), 	"Screenshot ID is correct");

		if ($info->{'status'} == 'finished') {
			$this->assertTrue(array_key_exists('screenshot_url',$info), "Screenshot screenshot_url is present");
			$this->assertTrue(array_key_exists('final_url', 	$info), "Screenshot final_url is present");
			$this->assertTrue(array_key_exists('response_code', $info), "Screenshot response_code is present");
			$this->assertTrue(array_key_exists('content_type', 	$info), "Screenshot content_type is present");
			$this->assertTrue(array_key_exists('started', 		$info), "Screenshot started is present");
			$this->assertTrue(array_key_exists('finished', 		$info), "Screenshot finished is present");

			$this->assertTrue(array_key_exists('images', 		$info), "Screenshot images are present");
			$this->assertTrue(array_key_exists('iframes', 		$info), "Screenshot iframes are present");
			$this->assertTrue(array_key_exists('scripts', 		$info), "Screenshot scripts are present");
		}
	}

	public function testScreenshotHost()
	{
		$screenshots = $this->browshot->screenshot_list(array('details' => 0));
		$screenshot_ids = array_keys((array)$screenshots);
		$screenshot_id = $screenshot_ids[0];

		$hosting = $this->browshot->screenshot_host($screenshot_id);
		$this->assertEquals("error", $hosting->{'status'}, "Default hosting option not enabled for this account");

		$hosting = $this->browshot->screenshot_host($screenshot_id,  array('hosting' => 'browshot'));
		$this->assertEquals("error", $hosting->{'status'}, "Browshot hosting option not enabled for this account");

		$hosting = $this->browshot->screenshot_host($screenshot_id,  array('hosting' => 's3'));
		$this->assertEquals("error", $hosting->{'status'}, "S3hosting option not enabled for this account");

		$hosting = $this->browshot->screenshot_host($screenshot_id,  array('hosting' => 's3', 'bucket' => 'mine'));
		$this->assertEquals("error", $hosting->{'status'}, "S3 hosting option not enabled for this account");

		$hosting = $this->browshot->screenshot_host($screenshot_id,  array('hosting' => 'cdn'));
		$this->assertEquals("error", $hosting->{'status'}, "CDN hosting option not enabled for this account");
	}

	public function testScreenshotShare()
	{
		$hosting = $this->browshot->screenshot_share(0);
		$this->assertEquals("error", $hosting->{'status'}, "Wrong screenshot ID");
	}
}

?>
