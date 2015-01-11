<?php

/**
 * PHPUnit test suite for the Browshot package.
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; either version 2.1 of the
 * License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category  Services
 * @package   Browshot
 * @author    Julien Sobrier <julien@sobrier.net>
 * @copyright 2015 Browshot
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   1.14.0
 */

chdir(dirname(__FILE__));

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Browshot_AllTests::main');
}

require_once 'PHPUnit/Autoload.php';

require_once 'Version.php';
require_once 'AccountAPI.php';
require_once 'BrowserAPI.php';
require_once 'InstanceAPI.php';
require_once 'ScreenshotAPI.php';
require_once 'SimpleAPI.php';
require_once 'ThumbnailAPI.php';

class Browshot_AllTests
{

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Browshot Tests');
        $suite->addTestSuite('Version');
        $suite->addTestSuite('AccountAPI');
        $suite->addTestSuite('BrowserAPI');
        $suite->addTestSuite('InstanceAPI');
        $suite->addTestSuite('ScreenshotAPI');
        $suite->addTestSuite('SimpleAPI');
        $suite->addTestSuite('ThumbnailAPI');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Browshot_AllTests::main') {
    Browshot_AllTests::main();
}

?>