<?php
/**
 * BB's Zend Framework 2 Components
 *
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels <coding@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */

namespace Admin\Factory;

use Admin\Controller\ZfcuserController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ZfcuserControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     * /
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        return new ZfcuserController($realServiceLocator);
    }
    
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $controllerManager
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ControllerManager $controllerManager*/
        $serviceManager = $controllerManager->getServiceLocator();

        /* @var RedirectCallback $redirectCallback */
        $redirectCallback = $serviceManager->get('zfcuser_redirect_callback');

        /* @var UserController $controller */
        $controller = new ZfcuserController($serviceManager, $redirectCallback);

        return $controller;
    }
}