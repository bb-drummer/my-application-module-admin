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
        
        $this->setZfcUserValidAuthMock();
    }

    /**
     * @covers ::indexAction
     * @covers ::acllistAction
     */
    public function testIndexActionCanBeDispatched()
    {
        // dispatch index
        $this->routeMatch->setParam('action', 'index');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        
        // dispatch acllist
        $this->routeMatch->setParam('action', 'acllist');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::acldataAction
     */
    public function testAclDataActionRedirectsOnNonXmlHttpRequests()
    {
        // redirect
        $this->routeMatch->setParam('action', 'acldata');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }
    
    /**
     * @covers ::acldataAction
     */
    public function testAclDataActionCanBeDispatchedToReturnAclJsonDataOnXmlHttpRequest()
    {
        // set XHR header
        $headers = $this->getRequest()->getHeaders();
        if (!$headers) {
            $headers = new \Zend\Http\Headers();
        }
        $headers->addHeaderLine('X_REQUESTED_WITH: XMLHttpRequest');
        $this->getRequest()->setHeaders($headers);
        
        // 'display' acl json data
        $this->routeMatch->setParam('action', 'acldata');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
    
    /**
     * @covers ::addaclAction
     */
    public function testAddAclActionCanBeDispatched()
    {
        // display acl add form
        $this->routeMatch->setParam('action', 'addacl');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::editaclAction
     */
    public function testEditAclActionRedirectsIfNoAclIdIsGiven()
    {
        // redirect if no acl id is given
        $this->routeMatch->setParam('action', 'editacl');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        
        //$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::editaclAction
     */
    public function testEditAclActionRedirectsIfGivenIdIsNotFound()
    {
        // redirect if acl id could not be found
        $this->routeMatch->setParam('action', 'editacl');
        $this->routeMatch->setParam('acl_id', 999999999);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        
        //$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::editaclAction
     */
    public function testEditAclActionCanBeDispatched()
    {
        // display acl edit form
        $this->routeMatch->setParam('action', 'editacl');
        $this->routeMatch->setParam('acl_id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::deleteaclAction
     */
    public function testDeleteAclActionRedirectsIfNoAclIdIsGiven()
    {
        // redirect if no acl id is given
        $this->routeMatch->setParam('action', 'deleteacl');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        
        //$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::deleteaclAction
     */
    public function testDeleteAclActionRedirectsIfGivenIdIsNotFound()
    {
        // redirect if acl id could not be found
        $this->routeMatch->setParam('action', 'deleteacl');
        $this->routeMatch->setParam('acl_id', 999999999);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        
        //$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * is the action accessable per request/response action name ?
     *
     * @covers ::deleteaclAction
     */
    public function testDeleteAclActionCanBeDispatched()
    {
        // display delete acl confirmation form
        $this->routeMatch->setParam('action', 'deleteacl');
        $this->routeMatch->setParam('acl_id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::rolesAction
     */
    public function testRolesIndexActionCanBeDispatched()
    {
        // display roles overview page
        $this->routeMatch->setParam('action', 'roles');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::addroleAction
     */
    public function testAddRoleActionCanBeDispatched()
    {
        // show add role form
        $this->routeMatch->setParam('action', 'addrole');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::editroleAction
     */
    public function testEditRoleActionCanBeDispatched()
    {
        // show edit role form
        $this->routeMatch->setParam('action', 'editrole');
        $this->routeMatch->setParam('acl_id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::deleteroleAction
     */
    public function testDeleteRoleActionCanBeDispatched()
    {
        // show delete role confirmation form
        $this->routeMatch->setParam('action', 'deleterole');
        $this->routeMatch->setParam('acl_id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
     
    /**
     * @covers ::resourcesAction
     */
    public function testResourcesIndexActionCanBeDispatched()
    {
        // show resources overview page
        $this->routeMatch->setParam('action', 'resources');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::addresourceAction
     */
    public function testAddResourceActionCanBeDispatched()
    {
        // show add resource form
        $this->routeMatch->setParam('action', 'addresource');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::editresourceAction
     */
    public function testEditResourceActionCanBeDispatched()
    {
        // show edit resource form
        $this->routeMatch->setParam('action', 'editresource');
        $this->routeMatch->setParam('acl_id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::deleteresourceAction
     */
    public function testDeleteResourceActionCanBeDispatched()
    {
        // show delete resource confirmation form
        $this->routeMatch->setParam('action', 'deleteresource');
        $this->routeMatch->setParam('acl_id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
 
}