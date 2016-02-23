<?php
/**
 * BB's Zend Framework 2 Components
 *
 * AdminModule
 *
 * @package		[MyApplication]
 * @package		BB's Zend Framework 2 Components
 * @package		AdminModule
 * @author		Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 * @link		http://gitlab.dragon-projects.de:81/groups/zf2
 * @license		http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright	copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

namespace Admin\Factory;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Admin\Model\Applications;
use Admin\Model\ApplicationsTable;

/**
 * Class Admin\Model\ApplicationsTableFactory
 *
 * @package Admin\Factory\ApplicationsTableFactory
 */
class ApplicationsTableFactory implements FactoryInterface
{
	/**
	 * Create service
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 *
	 * @return Admin\Model\ApplicationsTable
	 */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$dbAdapter			= $serviceLocator->get('Zend\Db\Adapter\Adapter');
		$resultSetPrototype	= new ResultSet();
		$resultSetPrototype->setArrayObjectPrototype(new Applications());
		$tableGateway		= new TableGateway('applications', $dbAdapter, null, $resultSetPrototype);
		$tableGateway		= $sm->get('AdminApplicationsTableGateway');
		$table				= new ApplicationsTable($tableGateway);
		return $table;
	}
}