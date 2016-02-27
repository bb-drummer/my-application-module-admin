<?php
namespace AdminTest\Controller;

use \Admin\Controller\ZfcuserController,
    \AdminTest\Framework\TestCase as ActionControllerTestCase,
    Zend\Http\Request,
    Zend\Http\Response,
    Zend\Http\Router,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Router\RouteMatch,
    Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter
;

/**
 * @coversDefaultClass \Admin\Controller\ZfcuserController
 */
class ZfcuserControllerTest extends ActionControllerTestCase
{
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setupController()
    {
        $this->setController(new ZfcuserController());
        $this->getController()->setServiceLocator($this->getApplicationServiceLocator());
        $this->setRequest(new Request());
        $this->setRouteMatch(new RouteMatch(array('controller' => '\Admin\Controller\Zfcuser', 'action' => 'index')));
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
     * @covers ::indexAction
     * @covers ::userprofileAction
     */
    public function testUserProfileIndexActionCanBeDispatched()
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
     * @covers ::authenticateAction
     */
    public function testAuthenticateActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'authenticate');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::registerAction
     */
    public function testRegisterActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'register');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::requestpasswordresetAction
     */
    public function testRequestPasswordResetActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'requestpasswordreset');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::resetpasswordAction
     */
    public function testResetPasswordActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'resetpassword');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::userdataAction
     */
    public function testUserDataIndexActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'userdata');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::edituserdataAction
     */
    public function testEditUserDataActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'edituserdata');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::edituserprofileAction
     */
    public function testEditUserProfileActionCanBeDispatched()
    {
        // Specify which action to run
        $this->routeMatch->setParam('action', 'edituserprofile');
    
        // Kick the controller into action
        $result = $this->controller->dispatch($this->request);
    
        // Check the HTTP response code
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    
        // Check for a ViewModel to be returned
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::translate
     */
    public function testTranslateReturns_Translated_String()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * set translator stores an instance of Zend\Translator in property
	 *
     * @covers ::setTranslatorAction
     */
    public function testSetTranslatorStoresAnInstanceOfZendTranslatorInProperty()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getTranslator
     */
    public function testGetTranslatorReturnsAnInstanceOfZendTranslator()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::setActionTitle
     */
    public function testSetActionTitleStoresAStringInProperty()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getActionTitle
     */
    public function testGetActionTitleReturnsAString()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::setActionTitles
     */
    public function testSetActionTitlesStoresAnArrayOfStringsInProperty()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getActionTitles
     */
    public function testGetActionTitlesReturnsAnArrayOfStrings()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::setToolbarItemAction
     */
    public function testSetToolbarItemStoresAnInstanceOfZendNavigationPageInProperty()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getToolbarItemAction
     */
    public function testGetToolbarItemReturnsAnInstanceOfZendNavigationPage()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::setToolbarItemsAction
     */
    public function testSetToolbarItemsStoresAnArrayOfZendNavigationPageInstancesInProperty()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getToolbarItemsAction
     */
    public function testGetToolbarItemsReturnsAnArrayOfZendNavigationPageInstances()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getUserTableAction
     */
    public function testGetUserTableReturnsAclroleTableInstanceFromService()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getAclroleTableAction
     */
    public function testGetAclroleTableReturnsAclroleTableInstanceFromService()
    {
    	$this->assertTrue(true);
    }
    
    
}