<?php
/**
 * BB's Zend Framework 2 Components
 *
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

namespace Admin\Factory;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Admin\Model\Acl;
use Admin\Model\AclTable;

/**
 * Class Admin\Model\AclTableFactory
 *
 * @package Admin\Factory\AclTableFactory
 */
class AclTableFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return Admin\Model\AclTable
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dbAdapter            = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype    = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Acl());
        $tableGateway        = new TableGateway('acl', $dbAdapter, null, $resultSetPrototype);
        $table                = new AclTable($tableGateway);
        return $table;
    }
}