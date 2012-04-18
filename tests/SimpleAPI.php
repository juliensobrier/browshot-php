<?php

/**
 * Base test case class
 */
require_once 'TestCase.php';

/**
 * Tests the Simple API implementation of Browshot
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2012 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   1.6.0
 * @link      http://browshot.com/api/documentation#simple
 */
class SimpleAPI extends TestCase
{

	public function testSimpleCreate()
	{
		$info = $this->browshot->simple(array('url' => 'http://mobilito.net/', 'cache' => 60 * 60 * 24 * 365)); # cached for a year

		$this->assertEquals("200", $info['code']);
		$this->assertGreaterThan(100,  strlen($info['image']) );
	}


	public function testSimpleFile()
	{
		$info = $this->browshot->simple_file('mobilito.png', array('url' => 'http://mobilito.net/', 'cache' => 60 * 60 * 24 * 365)); # cached for a year

		$this->assertEquals("200", $info['code']);
		$this->assertEquals("mobilito.png", $info['file']);
		$this->assertTrue( file_exists($info['file']) );
		unlink($info['file']);
	}

}

?>
