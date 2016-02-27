<?php
namespace AdminTest\Controller;

use \Admin\Controller\AclController,
    \AdminTest\Framework\TestCase as ActionControllerTestCase,
    Zend\Http\Request,
    Zend\Http\Response,
    Zend\Http\Router,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Router\RouteMatch,
    Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter
;

/**
 * @coversDefaultClass \Admin\Controller\AclController
 */
class AclControllerTest extends ActionControllerTestCase
{
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setupController()
    {
        $this->setController(new AclController());
        $this->getController()->setServiceLocator($this->getApplicationServiceLocator());
        $this->setRequest(new Request());
        $this->setRouteMatch(new RouteMatch(array('controller' => '\Admin\Controller\Acl', 'action' => 'index')));
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
     * @covers ::acllistAction
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

        
        // Specify which action to run
        $this->routeMatch->setParam('action', 'acllist');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
  	 *
     * @covers ::indexAction
     * @covers ::acllistAction
     */
    public function testAclDataActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'acldata');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::addaclAction
     */
    public function testAddAclActionCanBeDispatched()
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
     * is the action accessable per request/response action name ?
     *
     * @covers ::editaclAction
     */
    public function testEditAclActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'edit');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::deleteaclAction
     */
    public function testDeleteAclActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'delete');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::rolesAction
     */
    public function testRolesIndexActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'roles');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::addroleAction
     */
    public function testAddRoleActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'addrole');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::editroleAction
     */
    public function testEditRoleActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'editrole');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::deleteroleAction
     */
    public function testDeleteRoleActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'deleterole');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
     
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::resourcesAction
     */
    public function testResourcesIndexActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'resources');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::addresourceAction
     */
    public function testAddResourceActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'addresource');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::editresourceAction
     */
    public function testEditResourceActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'editresource');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::deleteresourceAction
     */
    public function testDeleteResourceActionCanBeDispatched()
    {
    	// Specify which action to run
    	$this->routeMatch->setParam('action', 'deleteresource');
    
    	// Kick the controller into action
    	$result = $this->controller->dispatch($this->request);
    
    	// Check the HTTP response code
    	$response = $this->controller->getResponse();
    	$this->assertEquals(200, $response->getStatusCode());
    
    	// Check for a ViewModel to be returned
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
 
}