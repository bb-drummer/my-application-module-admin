<?php
namespace AdminTest\Controller;

use \Admin\Controller\SettingsController,
    \AdminTest\Framework\TestCase as ActionControllerTestCase,
    Zend\Http\Request,
    Zend\Http\Response,
    Zend\Http\Router,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Router\RouteMatch,
    Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter
;

/**
 * @coversDefaultClass \Admin\Controller\SettingsController
 */
class SettingsControllerTest extends ActionControllerTestCase
{
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setupController()
    {
    	$serviceLocator = $this->getApplicationServiceLocator();
    	
        $this->setController(new SettingsController( $serviceLocator ));
        $this->getController()->setServiceLocator( $serviceLocator );
        $this->setRequest(new Request());
        $this->setRouteMatch(new RouteMatch(array('controller' => '\Admin\Controller\Settings', 'action' => 'index')));
        $this->setEvent(new MvcEvent());
        $config = $serviceLocator->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);
        $this->getEvent()->setRouter($router);
        $this->getEvent()->setRouteMatch($this->getRouteMatch());
        $this->getController()->setEvent($this->getEvent());
        $this->setResponse(new Response());
        
        $this->setZfcUserValidAuthMock();
    }

    /**
     * @covers ::indexAction
     */
    public function testIndexActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'index');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::addAction
     */
    public function testAddSettingActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'add');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::editAction
     */
    public function testEditSettingActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'edit');
        $this->routeMatch->setParam('set_id', 1);
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::deleteAction
     */
    public function testDeleteSettingActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'delete');
        $this->routeMatch->setParam('set_id', 1);
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }

}