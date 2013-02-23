<?php

/**
 * Base test case class
 */
require_once 'TestCase.php';

/**
 * Tests the Account API implementation of Browshot
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2012 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   1.6.0
 */
class AccountAPI extends TestCase
{

	public function testAccountWrong()
	{
		$account = $this->browshot->account_info();
        $this->assertFalse(array_key_exists('error', 	  			$account), 	"Correct account");

		$this->assertTrue(array_key_exists('balance', 	  			$account), 	"Account balance is present");
		$this->assertEquals(0, 	  $account->{'balance'}, 						"Balance is empty");

		$this->assertTrue(array_key_exists('active', 	  			$account), 	"Account active is present");
		$this->assertEquals(1, 	  $account->{'active'}, 						"Account is active");

		$this->assertTrue(array_key_exists('instances', 	  		$account), 	"Account instances is present");

		$this->assertTrue(array_key_exists('free_screenshots_left', $account), 	"Free screenshots is present");
		$this->assertGreaterThan(0, 	  $account->{'free_screenshots_left'}, 	"Free screenshots left");
	}


}

?>
