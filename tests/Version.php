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
 * @copyright 2016 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   2.0.0
 */
class Version extends TestCase
{
	protected $requiresApiKey = false;


	public function testApiVersion()
	{
		$version = $this->browshot->api_version();
		$this->assertEquals("2.0", $version);
	}

}

?>