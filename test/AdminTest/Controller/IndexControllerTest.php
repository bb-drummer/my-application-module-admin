<?php
namespace ApplicationTest\Controller;

use \Application\Controller\SetupController,
    \ApplicationTest\Framework\ActionControllerTestCase,
    Zend\Http\Request,
    Zend\Http\Response,
    Zend\Http\Router,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Router\RouteMatch,
    Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter
;

/**
 * @coversDefaultClass \Application\Controller\SetupController
 */
class IndexControllerTest extends ActionControllerTestCase
{
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setupController()
    {
        $this->setController(new SetupController());
        $this->getController()->setServiceLocator($this->getApplicationServiceLocator());
        $this->setRequest(new Request());
        $this->setRouteMatch(new RouteMatch(array('controller' => '\Application\Controller\Setup', 'action' => 'index')));
        $this->setEvent(new MvcEvent());
        $config = $this->getApplicationServiceLocator()->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);
        $this->getEvent()->setRouter($router);
        $this->getEvent()->setRouteMatch($this->getRouteMatch());
        $this->getController()->setEvent($this->getEvent());
        $this->setResponse(new Response());
    }

    /**
     * is the action accessable per request/response action name ?
  *
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
    
        // Test the parameters contained in the View model
        //$vars = $result->getVariables();
        //$this->assertTrue(isset($vars['var']));
    }
    
    /**
     * 404 page
     */
    public function test404()
    {
        $this->routeMatch->setParam('action', 'not-implemented-yet');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }
    
}