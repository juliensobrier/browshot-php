<?php

require_once dirname(__FILE__) . '/../src/Browshot.php';
require_once dirname(__FILE__) . '/config.php';

/**
 * Base class for testing Browshot.
 */
abstract class TestCase extends PHPUnit\Framework\TestCase
{
	protected $browshot = null;
	protected $requiresApiKey = true;

	protected function setUp(): void
	{
		$config = $GLOBALS['Browshot_Unittest_Config'];
		if ($this->requiresApiKey && !$config['key']) {
			$this->markTestSkipped('Set BROWSHOT_API_KEY to run live tests.');
		}

		$this->browshot = new Browshot($config['key'] ?: 'test-key', $config['debug'], $config['base']);
	}

	protected function fixtureId($variable)
	{
		$id = getenv($variable);
		if (!$id || !preg_match('/\A[1-9][0-9]{0,18}\z/', $id)) {
			$this->markTestSkipped('Set ' . $variable . ' to an owned test capture ID.');
		}

		return $id;
	}
}
