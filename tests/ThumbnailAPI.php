<?php

/**
 * Base test case class
 */
require_once 'TestCase.php';

/**
 * Tests the Thumbnail API implementation of Browshot
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2012 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   1.7.0
 * @link      http://browshot.com/api/documentation#simple
 */
class ThumbnailAPI extends TestCase
{
// 	screenshot is not actually created for test account, so the reply may not match our parameters

	public function testThumbnailWrong()
	{
		$thumbnail = $this->browshot->screenshot_thumbnail();
		$this->assertTrue(array_key_exists('error', 	$thumbnail), 	"Thumbnail failed");

	}

	public function testThumbnail()
	{
		$screenshots = $this->browshot->screenshot_list();
		$this->assertGreaterThan(0,  	count(array_keys((array)$screenshots)) );

		$screenshot_ids = array_keys((array)$screenshots);
		$screenshot_id = $screenshot_ids[0];
		$screenshot = $screenshots->{$screenshot_id};


		$thumbnail = $this->browshot->screenshot_thumbnail(array('url' => $screenshot->{'screenshot_url'}, 'width' => 320));
// 		$this->assertFalse($thumbnail == '', "Thumbnail succeeded");

	}
}

?>
