<?php
namespace AdminTest\Controller;

use \Admin\Controller\IndexController,
    \AdminTest\Framework\TestCase as ActionControllerTestCase,
    Zend\Http\Request,
    Zend\Http\Response,
    Zend\Http\Router,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Router\RouteMatch,
    Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter
;

/**
 * @coversDefaultClass \Application\Controller\IndexController
 */
class IndexControllerTest extends ActionControllerTestCase
{
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setupController()
    {
    	$serviceLocator = $this->getApplicationServiceLocator();
    	
        $this->setController(new IndexController( $serviceLocator ));
        $this->getController()->setServiceLocator( $serviceLocator );
        $this->setRequest(new Request());
        $this->setRouteMatch(new RouteMatch(array('controller' => '\Admin\Controller\Index', 'action' => 'index')));
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
     * is the action accessable per request/response action name ?
  *
     * @covers ::indexAction
     */
    public function testIndexActionCanBeDispatched()
    {
        // redirect to whatever is set in route/navigation configuration
        $this->routeMatch->setParam('action', 'index');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
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