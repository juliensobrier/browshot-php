<?php

/**
 * Base test case class
 */
require_once 'TestCase.php';

/**
 * Tests the Browser Browser API implementation of Browshot
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2012 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   1.8.0
 * @link      http://browshot.com/api/documentation#browser_list
 */
class BrowserAPI extends TestCase
{

	public function testBrowserList()
	{
		$browsers = $this->browshot->browser_list();

		//var_dump($browsers);
		$this->assertGreaterThan(10,  	count((array)$browsers), "Browsers are available");

		$browser_ids = array_keys((array)$browsers);
		$browser = $browsers->{$browser_ids[0]};

		//var_dump($browser);
		$this->assertGreaterThan(0, $browser_ids[0],	"Browser ID is correct");
		$this->assertTrue(array_key_exists('name', 		  $browser), "Browser name exists");
		$this->assertTrue(array_key_exists('user_agent',  $browser), "Browser user_agent exists");
		$this->assertTrue(array_key_exists('appname', 	  $browser), "Browser appname exists");
		$this->assertTrue(array_key_exists('vendorsub',   $browser), "Browser vendorsub exists");
		$this->assertTrue(array_key_exists('appcodename', $browser), "Browser appcodename exists");
		$this->assertTrue(array_key_exists('platform', 	  $browser), "Browser platform exists");
		$this->assertTrue(array_key_exists('vendor', 	  $browser), "Browser vendor exists");
		$this->assertTrue(array_key_exists('appversion',  $browser), "Browser appversion exists");
		$this->assertTrue(array_key_exists('javascript',  $browser), "Browser javascript exists");
		$this->assertTrue(array_key_exists('mobile', 	  $browser), "Browser mobile exists");
		$this->assertTrue(array_key_exists('flash', 	  $browser), "Browser flash exists");
	}

	public function testBrowserInfo()
	{
		$browsers = $this->browshot->browser_list();

		$browser_ids = array_keys((array)$browsers);
		$browser_id = $browser_ids[0];
		$this->assertGreaterThan(0, $browser_id,	"Browser ID is correct");

		$browser = $this->browshot->browser_info($browser_id);

		//var_dump($browser);
		$this->assertTrue(array_key_exists('name', 		  $browser), "Browser name exists");
		$this->assertTrue(array_key_exists('user_agent',  $browser), "Browser user_agent exists");
		$this->assertTrue(array_key_exists('appname', 	  $browser), "Browser appname exists");
		$this->assertTrue(array_key_exists('vendorsub',   $browser), "Browser vendorsub exists");
		$this->assertTrue(array_key_exists('appcodename', $browser), "Browser appcodename exists");
		$this->assertTrue(array_key_exists('platform', 	  $browser), "Browser platform exists");
		$this->assertTrue(array_key_exists('vendor', 	  $browser), "Browser vendor exists");
		$this->assertTrue(array_key_exists('appversion',  $browser), "Browser appversion exists");
		$this->assertTrue(array_key_exists('javascript',  $browser), "Browser javascript exists");
		$this->assertTrue(array_key_exists('mobile', 	  $browser), "Browser mobile exists");
		$this->assertTrue(array_key_exists('flash', 	  $browser), "Browser flash exists");
	}

// 	Browswer creation is disabled for most accounts
	public function testBrowserCreate()
	{
		$browser = $this->browshot->browser_create(array('mobile' => 1, 'flash' => 1, 'user_agent' => 'test'));

// 		var_dump($browser);
// 		$this->assertTrue(array_key_exists('user_agent',  $browser), "Browser user_agent exists");
// 		$this->assertTrue(array_key_exists('appname', 	  $browser), "Browser appname exists");
// 		$this->assertTrue(array_key_exists('vendorsub',   $browser), "Browser vendorsub exists");
// 		$this->assertTrue(array_key_exists('appcodename', $browser), "Browser appcodename exists");
// 		$this->assertTrue(array_key_exists('platform', 	  $browser), "Browser platform exists");
// 		$this->assertTrue(array_key_exists('vendor', 	  $browser), "Browser vendor exists");
// 		$this->assertTrue(array_key_exists('appversion',  $browser), "Browser appversion exists");
// 		$this->assertTrue(array_key_exists('javascript',  $browser), "Browser javascript exists");
// 		$this->assertTrue(array_key_exists('mobile', 	  $browser), "Browser mobile exists");
// 		$this->assertTrue(array_key_exists('flash', 	  $browser), "Browser flash exists");
	}

}

?>
