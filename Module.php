<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package		[MyApplication]
 * @package		BB's Zend Framework 2 Components
 * @package		AdminModule
 * @author		Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 * @link		http://gitlab.dragon-projects.de:81/groups/zf2
 * @license		http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright	copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

namespace Admin;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\View\HelperPluginManager;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

use Application\Model\Callbacks;

use Admin\Controller\RedirectCallback;
use Admin\Controller\ZfcuserController;
use Admin\Model\UserProfile;
use Admin\Model\UserProfileTable;
use Admin\Model\Settings;
use Admin\Model\SettingsTable;
use Admin\Model\User;
use Admin\Model\UserTable;
use Admin\Model\Acl;
use Admin\Model\AclTable;
use Admin\Model\Aclrole;
use Admin\Model\AclroleTable;
use Admin\Model\Aclresource;
use Admin\Model\AclresourceTable;

class Module implements AutoloaderProviderInterface, ServiceLocatorAwareInterface
{

	protected $appobj;
	protected $appconfig;
	/** @var $serviceLocator \Zend\Di\ServiceLocator */
	protected static $serviceLocator;

	/** @var $serviceManager \Zend\ServiceManager\ServiceManager */
	protected static $serviceManager;
	
	public function init(ModuleManager $oModuleManager)
	{
		// init layout
		$oModuleManager
			->getEventManager()
			->getSharedManager()
			->attach(
				__NAMESPACE__, 
				'dispatch', 
				('Application\Model\Callbacks::initLayout') 
			)
		;
		
	}
	
	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php',
			),
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
			// if we're in a namespace deeper than one level we need to fix the \ in the path
					__NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
				),
			),
		);
	}

	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'isallowed' => function(HelperPluginManager $pm) {
					return $pm->get('Admin\View\Helper\Isallowed');
				},
				'isdenied' => function(HelperPluginManager $pm) {
					return $pm->get('Admin\View\Helper\Isdenied');
				},
			),
		);
	}
	
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function onBootstrap(MvcEvent $e)
	{
		/** @var $application \Zend\Mvc\Application */
		$application = $e->getApplication();
		$this->appobj = $application;
		
		/** @var $serviceManager \Zend\ServiceManager\ServiceManager */
		$serviceManager = $application->getServiceManager();
		$this->setServiceManager( $serviceManager );
		
		/** @var $pluginManagerViewHelper \Zend\View\HelperPluginManager */
		$viewHelperManager = $serviceManager->get('ViewHelperManager');
		
		/** @var $eventManager \Zend\EventManager\EventManager */
		$eventManager		= $application->getEventManager();
		
		/** @var $staticEventManager \Zend\EventManager\StaticEventManager */
		$staticEventManager		= \Zend\EventManager\StaticEventManager::getInstance();
		
		/** @var $moduleRouteListener \Zend\Mvc\ModuleRouteListener */
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		
		$this->setAppConfig($e->getApplication()->getConfig());
		
		// setup acl
		$this->initAcl($e);
		$eventManager->getSharedManager()->attach(__NAMESPACE__, 'dispatch', array($this, 'checkAcl'));
		
		// setup user registration mails
		$staticEventManager->attach('ZfcUser\Service\User', 'register', array($this, 'userRegisterBeforeInsert'));
		$staticEventManager->attach('ZfcUser\Service\User', 'register.post', array($this, 'userRegisterAfterInsert'));
		
		
		// override or add a view helper ... or setup in 'getViewHelperConfig' method
		//$viewHelperManager->get('navigation')->getPluginManager()->setInvokableClass('isallowed', '\Admin\View\Helper\Isallowed');
		//$viewHelperManager->get('navigation')->getPluginManager()->setInvokableClass('isdenied', '\Admin\View\Helper\Isdenied');
		
	}
	
	public function userRegisterBeforeInsert($e) {
		$user = $e->getParam('user');  // User account object
		$form = $e->getParam('form');  // Form object

		if (empty($user->getAclrole())) { 
			$user->setAclrole('user');
		}
		if (empty($user->getToken())) { 
			$user->setToken($this->createUserToken($user));
		}
		
		$config = $this->getAppConfig();
		$user->setState(0);
		if (!$config["zfcuser_user_must_confirm"] && !$config["zfcuser_admin_must_activate"]) {
			$user->setState(1);
		}
	}
	
	public function userRegisterAfterInsert($e) {
		$user = $e->getParam('user');  // User account object
		$form = $e->getParam('form');  // Form object
		
		$config = $this->getAppConfig();
		if ($config["zfcuser_user_must_confirm"]) {
			$this->sendConfirmationMail($user);
		} else if ($config["zfcuser_admin_must_activate"]) {
			$this->sendActivationMail($user);
		} else {
			$user->setState(1);
		}
	}
	
	public function createUserToken (\Admin\Entity\User $user) {
		$slug = uniqid( md5( time().$_SERVER["REMOTE_ADDR"].$user->getEmail() ), true );
		return $slug;
	}
	
	public function sendConfirmationMail (\Admin\Entity\User $user) {

		$config = $this->getAppConfig();

		$viewRender = new PhpRenderer();
		$viewRender->resolver()->addPath(__DIR__.'/view/');
		
		$mailModel = new ViewModel();
		$mailModel->setVariables($user->__getArrayCopy());
		$mailModel->setVariable('confirmation_url', $config["zfcuser_mail_http_basepath"].'/confirmuserregistration/' . $user->getId() . '/' . $user->getToken());

		$mailModel->setTemplate('mails/userconfirm_html');
		$htmlMarkup = $viewRender->render($mailModel);
		
		$html = new MimePart($htmlMarkup);
		$html->type = "text/html";
		
		$body = new MimeMessage();
		$body->setParts(array($html));
		
		$message = new Message();
		$message->addFrom($config["zfcuser_admin_from_email"])
		        ->addTo($user->getEmail())
		        ->addBcc($config["zfcuser_admin_to_email"])
		        ->setSubject($config["zfcuser_confirm_subject"]);
		$message->getHeaders()->addHeaderLine('X-Mailer', '[myApplication]/php');
		$message->setBody($body);		

		$transport = new SmtpTransport();
		$options   = new SmtpOptions($config["zfcuser_smtp"]);
		$transport->setOptions($options);
		$transport->send($message);
		
	}
	
	public function sendActivationMail (\Admin\Entity\User $user) {

		$config = $this->getAppConfig();

		$viewRender = new PhpRenderer();
		$viewRender->resolver()->addPath(__DIR__.'/view/');
		
		$mailModel = new ViewModel();
		$mailModel->setVariables($user->__getArrayCopy());
		$mailModel->setVariable('activation_url', $config["zfcuser_mail_http_basepath"].'/activateuser/' . $user->getId() . '/' . $user->getToken());

		$mailModel->setTemplate('mails/useractivate_html');
		$htmlMarkup = $viewRender->render($mailModel);
		
		$html = new MimePart($htmlMarkup);
		$html->type = "text/html";
		
		$body = new MimeMessage();
		$body->setParts(array($html));
		
		$message = new Message();
		$message->addFrom($config["zfcuser_admin_from_email"])
		        ->addReplyTo($user->getEmail())
		        ->addTo($config["zfcuser_admin_to_email"])
		        ->setSubject($config["zfcuser_activate_subject"]);
		$message->getHeaders()->addHeaderLine('X-Mailer', '[myApplication]/php');
		$message->setBody($body);		

		$transport = new SmtpTransport();
		$options   = new SmtpOptions($config["zfcuser_smtp"]);
		$transport->setOptions($options);
		$transport->send($message);
		
	}
	
	public function sendActivationNotificationMail (\Admin\Entity\User $user) {

		$config = $this->getAppConfig();

		$viewRender = new PhpRenderer();
		$viewRender->resolver()->addPath(__DIR__.'/view/');
		
		$mailModel = new ViewModel();
		$mailModel->setVariables($user->__getArrayCopy());
		$mailModel->setVariable('login_url', $config["zfcuser_mail_http_basepath"].'/user/login');

		$mailModel->setTemplate('mails/useractivatecomplete_html');
		$htmlMarkup = $viewRender->render($mailModel);
		
		$html = new MimePart($htmlMarkup);
		$html->type = "text/html";
		
		$body = new MimeMessage();
		$body->setParts(array($html));
		
		$message = new Message();
		$message->addFrom($config["zfcuser_admin_from_email"])
		        ->addTo($user->getEmail())
		        ->addBcc($config["zfcuser_admin_to_email"])
		        ->setSubject($config["zfcuser_activate_subject"]);
		$message->getHeaders()->addHeaderLine('X-Mailer', '[myApplication]/php');
		$message->setBody($body);		

		$transport = new SmtpTransport();
		$options   = new SmtpOptions($config["zfcuser_smtp"]);
		$transport->setOptions($options);
		$transport->send($message);
		
	}
	
	public function sendPasswordResetMail (\Admin\Entity\User $user) {

		$config = $this->getAppConfig();

		$viewRender = new PhpRenderer();
		$viewRender->resolver()->addPath(__DIR__.'/view/');
		
		$mailModel = new ViewModel();
		$mailModel->setVariables($user->__getArrayCopy());
		
		$mailModel->setVariable('login_url', $config["zfcuser_mail_http_basepath"].'/user/login');
		$mailModel->setVariable('resetpassword_url', $config["zfcuser_mail_http_basepath"].'/resetpassword/' . $user->getId() . '/' . $user->getToken());
		
		$mailModel->setTemplate('mails/userresetpassword_html');
		$htmlMarkup = $viewRender->render($mailModel);
		
		$html = new MimePart($htmlMarkup);
		$html->type = "text/html";
		
		$body = new MimeMessage();
		$body->setParts(array($html));
		
		$message = new Message();
		$message->addFrom($config["zfcuser_admin_from_email"])
		        ->addTo($user->getEmail())
		        ->addBcc($config["zfcuser_admin_to_email"])
		        ->setSubject($config["zfcuser_resetpassword_subject"]);
		$message->getHeaders()->addHeaderLine('X-Mailer', $config["app"]["title"].'/php');
		$message->setBody($body);		

		$transport = new SmtpTransport();
		$options   = new SmtpOptions($config["zfcuser_smtp"]);
		$transport->setOptions($options);
		$transport->send($message);
		
	}
	
	public function initAcl(MvcEvent $e) {
		$sm = $e->getApplication()->getServiceManager();
		$acl = \Application\Model\Callbacks::initACL($sm);
		
		$e->getViewModel()->acl = $acl;
	}
	
	public function checkAcl(MvcEvent $e) {
		$oAcl = $e->getViewModel()->acl;
		$oSM = $e->getApplication()->getServiceManager();
		
		$sAclRole = 'public';
		$oAuth = $oSM->get('zfcuser_auth_service');
		if ( $oAuth->hasIdentity() ) {
			$oUser = $oAuth->getIdentity();
			$sAclRole = $oUser->getAclrole();
		}

		$oNavigation = $oSM->get('navigation');
		$activePage = $oNavigation->findBy('active', 1);
		if ($activePage) {
			$sAclResource = $activePage->getResource();
			if (!empty($sAclResource) && $oAcl->hasResource($sAclResource)) {
				if ( !$oAcl->isAllowed($sAclRole, $sAclResource) ) {
					$response = $e->getResponse();
					//location to page or what ever
					$response->getHeaders()->addHeaderLine('Location', $e->getRequest()->getBaseUrl() . '/user/login?redirect=' . $e->getRequest()->getRequestUri() );
					$response->setStatusCode(301);
				}
			}
		}
	}
	
	/**
	 * fetch user's profile data
	 *
	 * @param $appconfig
	 * @return Module
	 */
	public function getUserProfile( $user_id )
	{	
		$oProfile = new UserProfile();
		$oProfile->load( $user_id );
		return $oProfile;
	}
	
	
	/**
	 * Set app config
	 *
	 * @param $appconfig
	 * @return Module
	 */
	public function setAppConfig($appconfig)
	{
		$this->appconfig = $appconfig;
		return $this;
	}

	/**
	 * Retrieve app config
	 *
	 * @return $appconfig
	 */
	public function getAppConfig()
	{
		return $this->appconfig;
	}
	
	/**
	 * Set serviceManager instance
	 *
	 * @param  ServiceLocatorInterface $serviceLocator
	 * @return \Admin\Module
	 */
	public function setServiceManager($serviceManager)
	{
		self::$serviceManager = $serviceManager;
		return $this;
	}

	/**
	 * Retrieve serviceManager instance
	 *
	 * @return \Zend\ServiceManager\ServiceManager
	 */
	public function getServiceManager()
	{
		if (!self::$serviceManager) {
			
			self::$serviceManager = new \Zend\ServiceManager\ServiceManager();
		}
		return self::$serviceManager;
	}
	
	/**
	 * Set serviceManager instance
	 *
	 * @param  ServiceLocatorInterface $serviceLocator
	 * @return void
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		self::$serviceLocator = $serviceLocator;
		return $this;
	}

	/**
	 * Retrieve serviceManager instance
	 *
	 * @return \Zend\Di\ServiceLocator
	 */
	public function getServiceLocator()
	{
		if (!self::$serviceLocator) {
			self::$serviceLocator = new \Zend\Di\ServiceLocator();
			//$this->serviceLocator = new \Zend\Di\ServiceLocator();
		}
		return self::$serviceLocator;
	}
	
    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'zfcuser' => function($controllerManager) {
                        /** @var ControllerManager $controllerManager*/
                        $serviceManager = $controllerManager->getServiceLocator();

                        /** @var RedirectCallback $redirectCallback */
                        $redirectCallback = $serviceManager->get('zfcuser_redirect_callback');

                        /** @var UserController $controller */
                        $controller = new ZfcuserController($redirectCallback);

                        return $controller;
                    },
            ),
        );
    }

	public function getServiceConfig()
	{
		return array(
			'factories' => array(
					
				
					
				'Admin\Model\UserTable' =>  function($sm) {
					$tableGateway = $sm->get('AdminUserTableGateway');
					$table = new UserTable($tableGateway);
					return $table;
				},
				'AdminUserTableGateway' => function ($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new User());
					return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
				},
				
				'Admin\Model\SettingsTable' =>  function($sm) {
					$tableGateway = $sm->get('AdminSettingsTableGateway');
					$table = new SettingsTable($tableGateway);
					return $table;
				},
				'AdminSettingsTableGateway' => function ($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Settings());
					return new TableGateway('settings', $dbAdapter, null, $resultSetPrototype);
				},
				
				
				'Admin\Model\AclTable' =>  function($sm) {
					$tableGateway = $sm->get('AdminAclTableGateway');
					$table = new AclTable($tableGateway);
					return $table;
				},
				'AdminAclTableGateway' => function ($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Acl());
					return new TableGateway('acl', $dbAdapter, null, $resultSetPrototype);
				},
				'Admin\Model\AclroleTable' =>  function($sm) {
					$tableGateway = $sm->get('AdminAclroleTableGateway');
					$table = new AclroleTable($tableGateway);
					return $table;
				},
				'AdminAclroleTableGateway' => function ($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Aclrole());
					return new TableGateway('aclrole', $dbAdapter, null, $resultSetPrototype);
				},
				'Admin\Model\AclresourceTable' =>  function($sm) {
					$tableGateway = $sm->get('AdminAclresourceTableGateway');
					$table = new AclresourceTable($tableGateway);
					return $table;
				},
				'AdminAclresourceTableGateway' => function ($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Aclresource());
					return new TableGateway('aclresource', $dbAdapter, null, $resultSetPrototype);
				},

				'Admin\Model\UserProfileTable' =>  function($sm) {
					$tableGateway = $sm->get('AdminUserProfileTableGateway');
					$table = new UserProfileTable($tableGateway);
					return $table;
				},
				'AdminUserProfileTableGateway' => function ($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new UserProfile());
					return new TableGateway('userprofile', $dbAdapter, null, $resultSetPrototype);
				},

                'zfcuser_redirect_callback' => function ($sm) {
                    /** @var RouteInterface $router */
                    $router = $sm->get('router');
                    
                    /** @var Application $application */
                    $application = $sm->get('Application');

                    /** @var ModuleOptions $options */
                    $options = $sm->get('zfcuser_module_options');

                    return new RedirectCallback($application, $router, $options);
				},
            ),
		);
	}
}
