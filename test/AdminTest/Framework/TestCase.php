<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link	  http://github.com/zendframework/Admin for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace AdminTest\Framework;

use \ApplicationTest\Framework\TestCase as ApplicationTestCase;

class TestCase extends ApplicationTestCase
{

	public static $locator;

	public static function setLocator($locator)
	{
		self::$locator = $locator;
	}

	public function getLocator()
	{
		return self::$locator;
	}
}
