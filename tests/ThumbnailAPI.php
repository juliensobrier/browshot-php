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
 * @version   1.8.0
 * @link      http://browshot.com/api/documentation#screenshot_thumbnail
 */
class ThumbnailAPI extends TestCase
{
// 	screenshot is not actually created for test account, so the reply may not match our parameters

	public function testThumbnailWrong()
	{
		$thumbnail = $this->browshot->screenshot_thumbnail();
		$this->assertTrue(array_key_exists('error', 	(array)$thumbnail), 	"Thumbnail failed");

	}

	public function testThumbnail()
	{
		$screenshot_id = $this->fixtureId('BROWSHOT_SCREENSHOT_ID');
		$thumbnail = $this->browshot->screenshot_thumbnail($screenshot_id, array('width' => 320));
		$this->assertFalse($thumbnail == '', "Thumbnail succeeded");
		$this->assertEquals(substr($thumbnail, 1, 3), 'PNG', "Thumbnail is a valid PNG file");
	}
}

?>
