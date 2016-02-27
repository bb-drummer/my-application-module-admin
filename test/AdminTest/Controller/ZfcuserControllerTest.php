<?php
namespace AdminTest\Controller;

use \Admin\Controller\ZfcuserController,
    \Admin\Controller\RedirectCallback,
    \AdminTest\Framework\TestCase as ActionControllerTestCase,
    Zend\Http\Request,
    Zend\Http\Response,
    Zend\Http\Router,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Router\RouteMatch,
    Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter,
    ZfcUser\Options\ModuleOptions as ZfcuserModuleOptions;
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
        
        $config = $this->getApplicationServiceLocator()->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);
        
    	$redirCallback = new \Admin\Controller\RedirectCallback($this->getApplication(), $router, new ZfcuserModuleOptions($config['router']));
    	
        $this->setController(new ZfcuserController($redirCallback));
        $this->getController()->setServiceLocator($this->getApplicationServiceLocator());
        $this->setRequest(new Request());
        $this->setRouteMatch(new RouteMatch(array('controller' => '\Admin\Controller\Zfcuser', 'action' => 'index')));
        $this->setEvent(new MvcEvent());
        
        $this->getEvent()->setRouter($router);
        $this->getEvent()->setRouteMatch($this->getRouteMatch());
        $this->getController()->setEvent($this->getEvent());
        $this->setResponse(new Response());
        
        // set mock for ZfcUserAuthentication
		/*$mockAuth = $this->getMock('ZfcUser\Entity\UserInterface');
		
		$ZfcUserMock = $this->getMock('Admin\Entity\User');  
		
		$ZfcUserMock->expects($this->any())
			->method('getId')
			->will($this->returnValue('1'));
		
		$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');
		
		$authMock->expects($this->any())
			->method('hasIdentity')
			-> will($this->returnValue(true));  
		
		$authMock->expects($this->any())
			->method('getIdentity')
			->will($this->returnValue($ZfcUserMock));
		
		$this->getController()->getPluginManager()
			->setService('zfcUserAuthentication', $authMock);*/
    }

    /**
     * @covers ::indexAction
     * @covers ::userprofileAction
     */
    public function testUserProfileIndexActionCanBeDispatched()
    {
    	// set user
    	$this->setZfcuserValidAuthMock();
    	
        // display user profile
        $this->routeMatch->setParam('action', 'index');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::registerAction
     */
    public function testRegisterActionCanBeDispatched()
    {
    	// set public user
    	$this->setZfcuserNoAuthMock();
    	
        // display registration form
        $this->routeMatch->setParam('action', 'register');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::requestpasswordresetAction
     */
    public function testRequestPasswordResetActionCanBeDispatched()
    {
    	// set public user
    	$this->setZfcuserNoAuthMock();
    	
        // dispay request password reset form
        $this->routeMatch->setParam('action', 'requestpasswordreset');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::resetpasswordAction
     */
    public function testResetPasswordActionRedirectIfTokenIsMissing()
    {
    	// set public user
    	$this->setZfcuserNoAuthMock();
    	
        // redirect if token is missing...
        $this->routeMatch->setParam('action', 'resetpassword');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }
    
    /**
     * @covers ::resetpasswordAction
     */
    public function testResetPasswordActionRedirectsIfTokenIsInvalid()
    {
    	// set public user
    	$this->setZfcuserNoAuthMock();
    	
        // redirect if token is invalid...
        $this->routeMatch->setParam('action', 'resetpassword', 'resettoken', 'invalid-password-reset-token');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }
    
    /**
     * @covers ::resetpasswordAction
     */
    public function testResetPasswordActionCanBeDispatched()
    {
    	// set public user
    	$this->setZfcuserNoAuthMock();
    	
        // display password reset form if token is valid
        $this->routeMatch->setParam('action', 'resetpassword', 'resettoken', 'valid-password-reset-token');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::authenticateAction
     */
    public function testAuthenticateActionRedirectIfNoLoginGiven()
    {
    	// set public user
    	$this->setZfcuserValidAuthMock();
    	
        // redirect on auth failure
        $this->routeMatch->setParam('action', 'authenticate');
        $this->routeMatch->setParam('username', '');
        $this->routeMatch->setParam('password', '');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }
    
    /**
     * @covers ::authenticateAction
     */
    public function testAuthenticateActionRedirectIfCredentialsAreWrong()
    {
    	// set public user
    	$this->setZfcuserValidAuthMock();
    	
        // redirect on unknown user
        $this->routeMatch->setParam('action', 'authenticate');
        $this->routeMatch->setParam('username', 'this-is-an-unknown-user');
        $this->routeMatch->setParam('password', 'some-password');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        
        // re-deisplay login form on wrong password
        $this->routeMatch->setParam('action', 'authenticate');
        $this->routeMatch->setParam('username', 'sysadmin');
        $this->routeMatch->setParam('password', 'wrong-password');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    /**
     * @covers ::authenticateAction
     */
    public function testAuthenticateActionRedirectIfCredentialsAreCorrect()
    {
    	// set public user
    	$this->setZfcuserValidAuthMock();
    	
        // redirect on auth failure
        $this->routeMatch->setParam('action', 'authenticate');
        $this->routeMatch->setParam('username', 'sysadmin');
        $this->routeMatch->setParam('password', 'sysadmin');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }
    
    /**
     * @covers ::userdataAction
     */
    public function testUserDataActionRedirectsToUserProfilePage()
    {
    	// set user
    	$this->setZfcuserValidAuthMock();
    	
    	// redirect to user profile/index page
        $this->routeMatch->setParam('action', 'userdata');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }
    
    /**
     * @covers ::edituserdataAction
     */
    public function testEditUserDataActionCanBeDispatched()
    {
    	// set user
    	$this->setZfcuserValidAuthMock();
    	
    	// display edit user data form
        $this->routeMatch->setParam('action', 'edituserdata');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }
    
    /**
     * @covers ::edituserprofileAction
     */
    public function testEditUserProfileActionCanBeDispatched()
    {
    	// set user
    	$this->setZfcuserValidAuthMock();
    	
    	// display edit user profile form
        $this->routeMatch->setParam('action', 'edituserprofile');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
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
     * @covers ::setTranslator
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
     * @covers ::setToolbarItem
     */
    public function testSetToolbarItemStoresAnInstanceOfZendNavigationPageInProperty()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getToolbarItem
     */
    public function testGetToolbarItemReturnsAnInstanceOfZendNavigationPage()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::setToolbarItems
     */
    public function testSetToolbarItemsStoresAnArrayOfZendNavigationPageInstancesInProperty()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getToolbarItems
     */
    public function testGetToolbarItemsReturnsAnArrayOfZendNavigationPageInstances()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getUserTable
     */
    public function testGetUserTableReturnsAclroleTableInstanceFromService()
    {
    	$this->assertTrue(true);
    }
    
    /**
     * @covers ::getAclroleTable
     */
    public function testGetAclroleTableReturnsAclroleTableInstanceFromService()
    {
    	$this->assertTrue(true);
    }
    
    
    //
    // helpers
    //
    
    
    /**
     *  set mock for ZfcUserAuthentication
     */
    private function setZfcUserValidAuthMock () {
		$mockAuth = $this->getMock('ZfcUser\Entity\UserInterface');
		
		$ZfcUserMock = $this->getMock('Admin\Entity\User');  
		
		$ZfcUserMock->expects($this->any())
			->method('getId')
			->will($this->returnValue('1'));
		
		$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');
		
		$authMock->expects($this->any())
			->method('hasIdentity')
			-> will($this->returnValue(true));  
		
		$authMock->expects($this->any())
			->method('getIdentity')
			->will($this->returnValue($ZfcUserMock));
		
		$this->getController()->getPluginManager()
			->setService('zfcUserAuthentication', $authMock);
    }
    
    /**
     *  set mock for ZfcUserAuthentication
     */
    private function setZfcUserNoAuthMock () {
		$mockAuth = $this->getMock('ZfcUser\Entity\UserInterface');
		
		$ZfcUserMock = $this->getMock('Admin\Entity\User');  
		
		$ZfcUserMock->expects($this->any())
			->method('getId')
			->will($this->returnValue(0));
		
		$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');
		
		$authMock->expects($this->any())
			->method('hasIdentity')
			-> will($this->returnValue(false));  
		
		$authMock->expects($this->any())
			->method('getIdentity')
			->will($this->returnValue($ZfcUserMock));
		
		$this->getController()->getPluginManager()
			->setService('zfcUserAuthentication', $authMock);
    }
}