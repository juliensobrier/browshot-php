<?php

/**
 * PHPUnit3 framework
 */
require_once 'PHPUnit/Autoload.php';

/**
 * Browshot class to test
 */
require_once dirname(__FILE__).'/../src/Browshot.php';


/**
 * Base class for testing Browshot
 *
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{

    /**
     * @var Browshot
     */
    protected $browshot = null;

	public function __construct($name = null)
    {
        parent::__construct($name);

        if (!file_exists(dirname(__FILE__).'/config.php')) {
            throw new Exception('Unit test configuration file is missing. ' .
                'Please read the documentation in TestCase.php and create ' .
                'a configuration file. See the example configuration provided '.
                'in config.php.dist for an example.');
        }

        include_once dirname(__FILE__).'/config.php';

        $this->config = $GLOBALS['Browshot_Unittest_Config'];
    }


	public function setUp()
    {
        $this->_old_error_level = error_reporting(E_ALL | E_STRICT);
        $this->browshot = new Browshot($this->config['key'], $this->config['base'], $this->config['debug']);
    }


    public function tearDown()
    {
        unset($this->browshot);
        error_reporting($this->_old_error_level);
    }

}

?>