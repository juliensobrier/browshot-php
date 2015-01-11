<?php

/**
 * Base test case class
 */
require_once 'TestCase.php';

/**
 * Tests the Instance API implementation of Browshot
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2012 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   1.8.0
 * @link      http://browshot.com/api/documentation#instance_list
 */
class InstanceAPI extends TestCase
{

	public function testInstanceList()
	{
		$info = $this->browshot->instance_list();

		// var_dump($info);
		$this->assertGreaterThan(20,  	count($info->{'shared'}) );
		$this->assertGreaterThan(1,  	count($info->{'free'}) );
		$this->assertEquals(1,  		count($info->{'private'}) );

		$free = $info->{'free'}[0];
		$this->assertTrue(array_key_exists('id', 			$free));
		$this->assertTrue(array_key_exists('width', 			$free));
		$this->assertTrue(array_key_exists('height', 			$free));
		$this->assertTrue(array_key_exists('load', 			$free));
		$this->assertTrue(array_key_exists('browser', 			$free));
		$this->assertTrue(array_key_exists('id', 			$free->{'browser'}));
		$this->assertTrue(array_key_exists('name', 			$free->{'browser'}));
		$this->assertTrue(array_key_exists('javascript', 		$free->{'browser'}));
		$this->assertTrue(array_key_exists('flash', 			$free->{'browser'}));
		$this->assertTrue(array_key_exists('mobile', 			$free->{'browser'}));
		$this->assertTrue(array_key_exists('type', 			$free));
		$this->assertTrue(array_key_exists('screenshot_cost', 	$free));
		$this->assertEquals(0,			$free->{'screenshot_cost'}, "Screenshot is free");
	}


	public function testInstanceInfo()
	{
		$info = $this->browshot->instance_list();

		$free = $info->{'free'}[0];
		$info = $this->browshot->instance_info($free->{'id'});

		$this->assertEquals($free->{'id'}, 						$info->{'id'}, 		"Correct instance ID");
		$this->assertEquals($free->{'width'}, 					$info->{'width'}, 		"Correct instance width");
		$this->assertEquals($free->{'height'}, 					$info->{'height'}, 		"Correct instance height");
// 		$this->assertEquals($free->{'load'}, 					$info->{'load'}, 		"Correct instance load"); // too dynamic
		$this->assertEquals($free->{'browser'}->{'id'}, 		$info->{'browser'}->{'id'}, 		"Correct instance browser ID");
		$this->assertEquals($free->{'browser'}->{'name'}, 		$info->{'browser'}->{'name'}, 		"Correct instance browser ID");
		$this->assertEquals($free->{'browser'}->{'javascript'},	$info->{'browser'}->{'javascript'}, 		"Correct instance browser javascript");
		$this->assertEquals($free->{'browser'}->{'flash'}, 		$info->{'browser'}->{'flash'}, 		"Correct instance browser javascript");
		$this->assertEquals($free->{'browser'}->{'mobile'}, 	$info->{'browser'}->{'mobile'}, 		"Correct instance browser javascript");
		$this->assertEquals($free->{'type'}, 					$info->{'type'}, 		"Correct instance type");
		$this->assertEquals($free->{'screenshot_cost'}, 		$info->{'screenshot_cost'}, 		"Correct instance screenshot_cost");
	}

	public function testInstanceInfoMissing()
	{
		$missing = $this->browshot->instance_info(-1);

		$this->assertTrue(array_key_exists('error',   $missing), "Instance was not found");
		$this->assertTrue(array_key_exists('status', $missing),	"Instance was not found");
	}
}

?>
