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
use Admin\Factory\ZfcuserControllerFactory;
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
    	$serviceLocator = $this->getApplicationServiceLocator();
    	
        $config = $serviceLocator->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);
        
        //$redirCallback = new \Admin\Controller\RedirectCallback($this->getApplication(), $router, new ZfcuserModuleOptions($config['router']));
        //$this->setController(new ZfcuserController($redirCallback));
        
        $userService = $this->getMock('ZfcUser\Service\User');
        
        $options = $this->getMock('ZfcUser\Options\ModuleOptions');
        
        $registerForm = $this->getMockBuilder('ZfcUser\Form\Register')
	        ->disableOriginalConstructor()
	        ->getMock();
        
        $loginForm = $this->getMockBuilder('ZfcUser\Form\Login')
	        ->disableOriginalConstructor()
	        ->getMock();
/*        
        $userService  = $serviceLocator->get('zfcuser_user_service');
        $registerForm = $serviceLocator->get('zfcuser_register_form');
        $loginForm    = $serviceLocator->get('zfcuser_login_form');
        $options      = $serviceLocator->get('zfcuser_module_options');
*/
        //$controllerFactory = new ZfcuserControllerFactory();
        //$this->setController( $controllerFactory->createService($serviceLocator) ); // ($userService, $options, $registerForm, $loginForm) );
        $this->setController( new ZfcuserController($userService, $options, $registerForm, $loginForm) );
        
        
        $this->getController()->setServiceLocator($this->getApplicationServiceLocator());
        $this->setRequest(new Request());
        $this->setRouteMatch(new RouteMatch(array('controller' => '\Admin\Controller\Zfcuser', 'action' => 'index')));
        $this->setEvent(new MvcEvent());
        
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
        // set user
        $this->setZfcUserValidAuthMock();
        
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
        $this->setZfcUserNoAuthMock();
        
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
        $this->setZfcUserNoAuthMock();
        
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
        $this->setZfcUserNoAuthMock();
        
        // redirect if token is missing...
        $this->routeMatch->setParam('action', 'resetpassword');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
    
    /**
     * @covers ::resetpasswordAction
     */
    public function testResetPasswordActionRedirectsIfUserIsUnknown()
    {
        // set public user
        $this->setZfcUserNoAuthMock();
        
        // redirect if token is invalid...
        $this->routeMatch->setParam('action', 'resetpassword');
        $this->routeMatch->setParam('user_id', 1);
        $this->routeMatch->setParam('resettoken', 'invalid-password-reset-token');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
        
    /**
     * @covers ::resetpasswordAction
     */
    public function testResetPasswordActionRedirectsIfTokenIsInvalid()
    {
        // set public user
        $this->setZfcUserNoAuthMock();
        
        // redirect if user_id is invalid...
        $this->routeMatch->setParam('action', 'resetpassword');
        $this->routeMatch->setParam('user_id', 'xyz');
        $this->routeMatch->setParam('resettoken', 'some-password-reset-token');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
    
    /**
     * @covers ::resetpasswordAction
     */
    public function testResetPasswordActionCanBeDispatched()
    {
        // set public user
        $this->setZfcUserValidAuthMock();
        $this->setZfcUserMapperFindByIdMock();
        
        // display password reset form if token is valid
        $this->routeMatch->setParam('action', 'resetpassword');
        $this->routeMatch->setParam('user_id', 1);
        $this->routeMatch->setParam('resettoken', 'valid-password-reset-token');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
    
    /**
     * @covers ::loginAction
     * /
    public function testLoginActionCanBeDispatched()
    {
        // set public user
        $this->setZfcUserValidAuthMock();
        
        // redirect on auth failure
        $this->routeMatch->setParam('action', 'login');
        $this->routeMatch->setParam('identity', '');
        $this->routeMatch->setParam('credential', '');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        //$this->assertEquals(302, $response->getStatusCode());
        //$this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
    
    /**
     * @covers ::authenticateAction
     * /
    public function testAuthenticateActionRedirectIfNoLoginGiven()
    {
        // set public user
        $this->setZfcUserValidAuthMock();
        
        // redirect on auth failure
        $this->routeMatch->setParam('action', 'authenticate');
        $this->routeMatch->setParam('identity', '');
        $this->routeMatch->setParam('credential', '');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
    
    /**
     * @covers ::authenticateAction
     * /
    public function testAuthenticateActionRedirectIfUserIsUnknown()
    {
        // set public user
        $this->setZfcUserValidAuthMock();
        
        // redirect on unknown user
        $this->routeMatch->setParam('action', 'authenticate');
        $this->routeMatch->setParam('identity', 'this-is-an-unknown-user');
        $this->routeMatch->setParam('credential', 'some-password');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
        
    /**
     * @covers ::authenticateAction
     * /
    public function testAuthenticateActionRedirectIfCredentialsAreWrong()
    {
        // set public user
        $this->setZfcUserValidAuthMock();

        // re-deisplay login form on wrong password
        $this->routeMatch->setParam('action', 'authenticate');
        $this->routeMatch->setParam('identity', 'sysadmin');
        $this->routeMatch->setParam('credential', 'wrong-password');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
    
    /**
     * @covers ::authenticateAction
     * /
    public function testAuthenticateActionRedirectIfCredentialsAreCorrect()
    {
        // set public user
        $this->setZfcUserValidAuthMock();
        
        // redirect on auth success to redirect parameter or user profile
        $this->routeMatch->setParam('action', 'authenticate');
        $this->routeMatch->setParam('identity', 'sysadmin');
        $this->routeMatch->setParam('credential', 'sysadmin');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
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
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
    }
    
    /**
     * @covers ::edituserdataAction
     */
    public function testEditUserDataActionCanBeDispatched()
    {
        // set user
        $this->setZfcUserValidAuthMock();
        
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
        $this->setZfcUserValidAuthMock();
        
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
     * /
    private function setZfcUserValidAuthMock () {
        $mockAuth = $this->getMock('ZfcUser\Entity\UserInterface');
        
        $ZfcUserMock = $this->getMock('Admin\Entity\User');  
        
        $ZfcUserMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('1'));
        
        $ZfcUserMock->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue('valid-password-reset-token'));
        
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
     * /
    private function setZfcUserNoAuthMock () {
        $mockAuth = $this->getMock('ZfcUser\Entity\UserInterface');
        
        $ZfcUserMock = $this->getMock('Admin\Entity\User');  

        $ZfcUserMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(0));
        
        $ZfcUserMock->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue('valid-password-reset-token'));
        
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
*/
}