<?php

/**
 * Base test case class
 */
require_once 'TestCase.php';

/**
 * Tests the API version implementation of Browshot
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2014 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   1.14.0
 */
class Version extends TestCase
{

	public function testApiVersion()
	{
		$version = $this->browshot->api_version();
		$this->assertEquals("1.14", $version);
	}

}

?>
