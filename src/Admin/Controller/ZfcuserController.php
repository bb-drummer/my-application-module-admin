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

/**
 * overrides to ZFC-User's own 'user'-controller
 */

namespace Admin\Controller;

use Admin\Module as AdminModule;
use Admin\Form\RequestPasswordResetForm;
use Admin\Form\ResetPasswordForm;
use Admin\Form\UserDataForm;
use Admin\Form\UserProfileForm;
use Admin\Model\UserProfile;

use Zend\Crypt\Password\Bcrypt;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;

use ZfcUser\Controller\UserController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;

class ZfcuserController extends UserController
{
	
	protected $translator;

	protected $actionTitles = array();

	protected $toolbarItems = array();
	
    public function defineActionTitles () {
		$this->setActionTitles(array(
			'login'					=> $this->translate("login"),
			'authenticate'			=> $this->translate("login"),
			'logout'				=> $this->translate("logout"),
			'register'				=> $this->translate("register user"),
			'requestpasswordreset'	=> $this->translate("reset password"),
			'changeemail'			=> $this->translate("change email"),
			'changepassword'		=> $this->translate("change password"),
			'resetpassword'			=> $this->translate("reset password"),
			'userdata'				=> $this->translate("userdata"),
			'edituserdata'			=> $this->translate("edit userdata"),
			'userprofile'			=> $this->translate("user profile"),
			'index'					=> $this->translate("user profile"),
			'edituserprofile'		=> $this->translate("edit profile"),
		));
    	return $this;
    }

    public function defineToolbarItems () {
		$this->setToolbarItems(array(
			"index" => array(
				array(
					'label'			=> 'edit profile',
					'icon'			=> 'edit',
					'classnames'	=> 'btn btn-default btn-sm btn-cta-xhr cta-xhr-modal',
					'route'			=> 'zfcuser/edituserprofile',
					'resource'		=> 'mvc:user',
				),
				array(
					'label'			=> 'edit userdata',
					'icon'			=> 'user',
					'classnames'	=> 'btn btn-default btn-sm btn-cta-xhr cta-xhr-modal',
					'route'			=> 'zfcuser/edituserdata',
					'resource'		=> 'mvc:user',
				),
				array(
					'label' 		=> 'change email',
					'icon'			=> 'envelope',
					'classnames'	=> 'btn btn-default btn-sm btn-cta-xhr cta-xhr-modal',
					'route'			=> 'zfcuser/changeemail',
					'resource'		=> 'mvc:user',
				),
				array(
					'label' 		=> 'change password',
					'icon'			=> 'lock',
					'classnames'	=> 'btn btn-default btn-sm btn-cta-xhr cta-xhr-modal',
					'route'			=> 'zfcuser/changepassword',
					'resource'		=> 'mvc:admin',
				),
				array(
					'label'			=> "",
					'classnames'	=> 'btn btn-default btn-sm',
					'uri'			=> "#",
					'active'		=> false,
				),
				array(
					'label' 		=> 'logout',
					'icon'			=> 'power-off',
					'classnames'	=> 'btn btn-default btn-sm',
					'route'			=> 'zfcuser/logout',
					'resource'		=> 'mvc:nouser',
				),
			)	
		));
    	return $this;
    }

    public function onDispatch(MvcEvent $e)
    {
    	\Zend\Navigation\Page\Mvc::setDefaultRouter($this->getServiceLocator()->get('router'));
    	$this->defineActionTitles();
    	$this->defineToolbarItems();
    	
		$action = $e->getRouteMatch()->getParam('action'); // $this->get->getParam('action', 'index');
		$this->layout()->setVariable("title", $this->getActionTitle($action));
		
		$serviceManager = $this->getServiceLocator();
		$toolbarNav = $serviceManager->get('toolbarnavigation');
		$toolbarNav->setConfig($this->getToolbarItem($action));
		// $toolbarNav = new \Zend\Navigation\Navigation($this->getToolbarItem($action));
		// $toolbarNav->
		$this->layout()->setVariable("toolbar", $toolbarNav->getPages($serviceManager));
		
		$result = parent::onDispatch($e);
		return $result;
	}

    /**
     * User page
     */
    public function indexAction()
    {
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
		return new ViewModel(array(
			"toolbarItems" => $this->getToolbarItems(),
		));
        return $this->redirect()->toRoute("admin/userprofile");
        return new ViewModel();
    }

	/**
	 * General-purpose authentication action
	 */
	public function authenticateAction()
	{
		if ($this->zfcUserAuthentication()->hasIdentity()) {
			return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
		}

		$translator = $this->getTranslator();
		$adapter = $this->zfcUserAuthentication()->getAuthAdapter();
		$redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

		$result = $adapter->prepareForAuthentication($this->getRequest());

		// Return early if an adapter returned a response
		if ($result instanceof Response) {
			return $result;
		}

		$auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);

		if (!$auth->isValid()) {
			$this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);
			$adapter->resetAdapters();
			return $this->redirect()->toUrl(
				$this->url()->fromRoute(static::ROUTE_LOGIN) .
				($redirect ? '?redirect='. rawurlencode($redirect) : '')
			);
		}

		$this->flashMessenger()->addSuccessMessage($translator->translate("login succeeded"));
		$redirect = $this->redirectCallback;

		return $redirect();
	}

	/**
	 * Register new user
	 */
	public function registerAction()
	{
		// if the user is logged in, we don't need to register
		if ($this->zfcUserAuthentication()->hasIdentity()) {
			// redirect to the login redirect route
			return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
		}
		// if registration is disabled
		if (!$this->getOptions()->getEnableRegistration()) {
			return array('enableRegistration' => false);
		}

		$request = $this->getRequest();
		$service = $this->getUserService();
		$form = $this->getRegisterForm();
		$translator = $this->getTranslator();
		
		if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
			$redirect = $request->getQuery()->get('redirect');
		} else {
			$redirect = false;
		}

		$redirectUrl = $this->url()->fromRoute(static::ROUTE_REGISTER)
			. ($redirect ? '?redirect=' . rawurlencode($redirect) : '');
		$prg = $this->prg($redirectUrl, true);

		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			return array(
				'registerForm' => $form,
				'enableRegistration' => $this->getOptions()->getEnableRegistration(),
				'redirect' => $redirect,
			);
		}

		$post = $prg;
		$user = $service->register($post);

		$redirect = isset($prg['redirect']) ? $prg['redirect'] : null;

		if (!$user) {
			return array(
				'registerForm' => $form,
				'enableRegistration' => $this->getOptions()->getEnableRegistration(),
				'redirect' => $redirect,
			);
		}

		// ... form input valid, do stuff...

		$config = $this->getServiceLocator()->get('Config');
		$oModule = new AdminModule();
		$oModule->setAppConfig($config);
		
		$this->flashMessenger()->addSuccessMessage($translator->translate("registration succeeded"));
		if ($config['zfcuser_user_must_confirm']) {
			$this->flashMessenger()->addInfoMessage($translator->translate("you have been sent an email with further instructions to follow"));
			return $this->redirect()->toUrl($this->url()->fromRoute($config["zfcuser_registration_redirect_route"]) . ($redirect ? '?redirect='. rawurlencode($redirect) : ''));
		} else if ($config['zfcuser_admin_must_activate']) {
			$this->flashMessenger()->addInfoMessage($translator->translate("admin has been notified for activation"));
			return $this->redirect()->toUrl($this->url()->fromRoute($config["zfcuser_registration_redirect_route"]) . ($redirect ? '?redirect='. rawurlencode($redirect) : ''));
		}
		
		if ($service->getOptions()->getLoginAfterRegistration()) {
			$identityFields = $service->getOptions()->getAuthIdentityFields();
			if (in_array('email', $identityFields)) {
				$post['identity'] = $user->getEmail();
			} elseif (in_array('username', $identityFields)) {
				$post['identity'] = $user->getUsername();
			}
			$post['credential'] = $post['password'];
			$request->setPost(new Parameters($post));
			$oModule->sendActivationNotificationMail($user);
			$this->flashMessenger()->addSuccessMessage($translator->translate("registration and activation succeeded"));
			return $this->forward()->dispatch(static::CONTROLLER_NAME, array('action' => 'authenticate'));
		}
		
		return $this->redirect()->toUrl($this->url()->fromRoute($config["zfcuser_registration_redirect_route"]) . ($redirect ? '?redirect='. rawurlencode($redirect) : ''));
	}

	/**
	 * request a user's password reset link
	 */
	public function requestpasswordresetAction()
	{
		// if the user is logged in, we don't need to 'reset' the password
		if ($this->zfcUserAuthentication()->hasIdentity()) {
			// redirect to the login redirect route
			return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
		}

		$config		= $this->getServiceLocator()->get('Config');
		$options	= $this->getServiceLocator()->get('zfcuser_module_options');
		$request	= $this->getRequest();
		$service	= $this->getUserService();
		$form		= new RequestPasswordResetForm(null, $options); // $this->getRegisterForm();
		$translator	= $this->getTranslator();
		
		// if password reset is disabled
		if (!$config['zfcuser']['enable_passwordreset']) {
			return array('enableRegistration' => false);
		}
		
		if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
			$redirect = $request->getQuery()->get('redirect');
		} else {
			$redirect = false;
		}

		$redirectUrl = $this->url()->fromRoute('userrequestpasswordreset') . ($redirect ? '?redirect=' . rawurlencode($redirect) : '');
		
		if (!$this->getRequest()->isPost()) {
			return array(
				'requestPasswordResetForm' => $form,
				'enablePasswordReset' => !!$config['zfcuser']['enable_passwordreset'], // $this->getOptions()->getEnablePasswordreset(),
				'redirect' => $redirect,
			);
		}
		
		$oModule = new AdminModule();
		$oModule->setAppConfig($config);
		$identity = $this->params()->fromPost('identity');

		/** @var \Admin\Entity\User $user **/
		/** @var \Admin\Entity\User $selectedUser **/
		$user = false;
		try {
			/** @var \Admin\Model\UserTable $userTable **/
			$userTable = $this->getServiceLocator()->get('\Admin\Model\UserTable');
			$selectedUser = $userTable->getUserByEmailOrUsername($identity);
			if ($selectedUser) {
				/** @var \ZfcUser\Mapper\User $userTable **/
				$userTable = $this->getServiceLocator()->get('zfcuser_user_mapper');
				$user = $userTable->findByUsername($selectedUser->username);
				if (!$user) {
					$user = $userTable->findByEmail($selectedUser->email);
				}
			}
		} catch (\Exception $e) {
		}
		
		if (!$user) {
			$this->flashMessenger()->addWarningMessage(
				sprintf($translator->translate("user '%s' not found"), $identity)
			);
			return $this->redirect()->toUrl($redirectUrl);
		}

		// user found, create token and send link via email
		
		$user->setToken($oModule->createUserToken($user));
		print_r($user);
		$service->getUserMapper()->update($user);
		
		
		$oModule->sendPasswordResetMail($user);
		$this->flashMessenger()->addSuccessMessage(
			sprintf($translator->translate("password reset email has been sent to user '%s'"), $identity)
		);
		
		return $this->redirect()->toUrl($this->url()->fromRoute($config["zfcuser_registration_redirect_route"]) . ($redirect ? '?redirect='. rawurlencode($redirect) : ''));
	}

	/**
	 * reset a user's password
	 */
	public function resetpasswordAction()
	{
		// if the user is logged in, we don't need to 'reset' the password
		if ($this->zfcUserAuthentication()->hasIdentity()) {
			// redirect to the login redirect route
			return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
		}

		$config		= $this->getServiceLocator()->get('Config');
		$options	= $this->getServiceLocator()->get('zfcuser_module_options');
		$request	= $this->getRequest();
		$service	= $this->getUserService();
		$form		= new ResetPasswordForm(null, $options); // $this->getRegisterForm();
		$translator	= $this->getTranslator();
		
		// if password reset is disabled
		if (!$config['zfcuser']['enable_passwordreset']) {
			return array('enableRegistration' => false);
		}
		
		if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
			$redirect = $request->getQuery()->get('redirect');
		} else {
			$redirect = false;
		}

		$redirectUrl = $this->url()->fromRoute(static::ROUTE_LOGIN) . ($redirect ? '?redirect=' . rawurlencode($redirect) : '');
		
		if ( !$this->getRequest()->isPost() ) {
			
			$user = false;
			$userId = (int) $this->params()->fromRoute('user_id');
			$resetToken = $this->params()->fromRoute('resettoken');
			
			try {
				$userTable = $this->getServiceLocator()->get('zfcuser_user_mapper');
				$user = $userTable->findById($userId);
			} catch (Exception $e) {}
			
			if ( !$user ) {
				$this->flashMessenger()->addWarningMessage(
					sprintf($translator->translate("invalid request"), $identity)
				);
				return $this->redirect()->toUrl($redirectUrl);
			}
			
			if ( empty($resetToken) || ($resetToken != $user->getToken()) ) {
				$this->flashMessenger()->addWarningMessage(
					sprintf($translator->translate("invalid request"), $resetToken)
				);
				return $this->redirect()->toUrl($redirectUrl);
			}
			
			return array(
				'user' => $user,
				'userId' => $userId,
				'resetToken' => $resetToken,
				'resetPasswordForm' => $form,
				'enablePasswordReset' => !!$config['zfcuser']['enable_passwordreset'], // $this->getOptions()->getEnablePasswordreset(),
				'redirect' => $redirect,
			);
			
		}
			
		$user = false;
		$userId = $this->params()->fromPost('identity');
		$resetToken = $this->params()->fromPost('token');
		
		$oModule = new AdminModule();
		$oModule->setAppConfig($config);
		$user = false;
		
		try {
			$userTable = $this->getServiceLocator()->get('zfcuser_user_mapper');
			$user = $userTable->findByEmail($userId);
		} catch (\Exception $e) {}
			
		if ( !$user ) {
			$this->flashMessenger()->addWarningMessage(
				sprintf($translator->translate("invalid request"), $userId)
			);
			return $this->redirect()->toUrl($redirectUrl);
		}

		//print_r($userId); print_r($user); die;
		
		if ( empty($resetToken) || ($resetToken != $user->getToken()) ) {
			$this->flashMessenger()->addWarningMessage(
				sprintf($translator->translate("invalid request"), $resetToken)
			);
			return $this->redirect()->toUrl($redirectUrl);
		}
		
		$form->setData( (array)$this->params()->fromPost() );
		
		if ( !$form->isValid() ) {
			
			return array(
				'user' => $user,
				'userId' => $userId,
				'resetToken' => $resetToken,
				'resetPasswordForm' => $form,
				'enablePasswordReset' => !!$config['zfcuser']['enable_passwordreset'], // $this->getOptions()->getEnablePasswordreset(),
				'redirect' => $redirect,
			);
			
		} else {
		
			$newCredential = $this->params()->fromPost('newCredential');
			
			$bcrypt		= new Bcrypt;
			$bcrypt->setCost($options->getPasswordCost());
			$user->setPassword($bcrypt->create($newCredential));
			$user->setToken('');
			$service->getUserMapper()->update($user);
		
			$this->flashMessenger()->addSuccessMessage(
				sprintf($translator->translate("password has been set"), $resetToken)
			);
			return $this->redirect()->toUrl($this->url()->fromRoute($config["zfcuser_registration_redirect_route"]) 
				. ($redirect ? '?redirect='. rawurlencode($redirect) : ''));
			
		}
		
	}

	/**
	 * view user's basic data
	 */
	public function userdataAction()
	{
		// if the user is logged in...
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
			// ...redirect to the login redirect route
			return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
		}
		
		$config		= $this->getServiceLocator()->get('Config');
		$options	= $this->getServiceLocator()->get('zfcuser_module_options');
		$request	= $this->getRequest();
		$service	= $this->getUserService();
		$translator	= $this->getTranslator();
		
	}
	
	/**
	 * view user's profile data
	 */
	public function userprofileAction()
	{
		// if the user is logged in...
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
			// ...redirect to the login redirect route
			return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
		}
        return $this->redirect()->toRoute("zfcuser/userprofile");
		
		$config		= $this->getServiceLocator()->get('Config');
		$options	= $this->getServiceLocator()->get('zfcuser_module_options');
		$request	= $this->getRequest();
		$service	= $this->getUserService();
		$translator	= $this->getTranslator();
		
		return new ViewModel(array(
			"toolbarItems" => $this->getToolbarItems(),
		));
	}

	/**
	 * edit user's basic data
	 */
	public function edituserdataAction()
	{
		
		// if the user is not logged in...
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
			// ...redirect to the login redirect route
			return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
		}
		
		$config		= $this->getServiceLocator()->get('Config');
		$options	= $this->getServiceLocator()->get('zfcuser_module_options');
		$request	= $this->getRequest();
		$service	= $this->getUserService();
		$form		= new UserDataForm();
		$translator	= $this->getTranslator();
		
		$user		= $this->zfcUserAuthentication()->getIdentity();
		$oUser		= new \Admin\Model\User();
		$oUser->exchangeArray($user->__getArrayCopy());
		$userId		= (int) $user->getId();

		$form->bind( $oUser );
		
		if ( !$this->getRequest()->isPost() ) {
			
			return array(
				'showForm'		=> true,
				'user'			=> $user,
				'userId'		=> $userId,
				'userdataForm'	=> $form,
			);
			
		}
		
		$data = (array)$this->params()->fromPost();
		$form->setData( $data );
		
		if ( !$form->isValid() ) {
			
			return array(
				'showForm'		=> true,
				'user'			=> $user,
				'userId'		=> $userId,
				'userdataForm'	=> $form,
			);
				
		} else {
		
			$profile->exchangeArray( $data );
			$result = $profile->save();
			if ( $result === true ) {
				$this->flashMessenger()->addSuccessMessage(
					$translator->translate("user data has been changed")
				);
			} else {
				$this->flashMessenger()->addWarningMessage(
					$translator->translate("user data could not be changed")
				);
			}
			if ( $this->getRequest()->isXmlHttpRequest() ) {
				$response = array(
					'showForm' => false,
				);
				$sAccept = $this->getRequest()->getHeaders()->get('Accept')->toString();
				$sFancybox = $this->getRequest()->getHeaders()->get('X-Fancybox')->toString();
				if ( ( strpos($sAccept, 'text/html') !== false ) || ( strpos($sFancybox, 'true') !== false ) ) {
					$viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
					$flashMessenger = $viewHelperManager->get('flashmessenger');
					$messages = $flashMessenger()->renderCurrent('warning', array('warning alert flashmessages'));
					$messages .= $flashMessenger()->renderCurrent('success', array('success alert flashmessages'));
					$this->flashMessenger()->clearCurrentMessagesFromContainer();
					return array_merge_recursive($response, array("content" => preg_replace('/<button(.*)<\/button>/i', "", $messages)));
					
				} else {
					$messages = $this->flashMessenger()->getCurrentErrorMessages();
					return array_merge_recursive($response, array("content" => json_encode(array_merge_recursive(
						$this->flashMessenger()->getCurrentWarningMessages(),
						$this->flashMessenger()->getCurrentSuccessMessages()
					))));
				}
			} else {
				return $this->redirect()->toRoute('zfcuser');
			}
				
		}
		
		
	}
	
	/**
	 * edit user's profile data
	 */
	public function edituserprofileAction()
	{
		
		// if the user is not logged in...
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
			// ...redirect to the login redirect route
			return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
		}
		
		$config		= $this->getServiceLocator()->get('Config');
		$options	= $this->getServiceLocator()->get('zfcuser_module_options');
		$request	= $this->getRequest();
		$service	= $this->getUserService();
		$form		= new UserProfileForm();
		$translator	= $this->getTranslator();
		
		$user		= $this->zfcUserAuthentication()->getIdentity();
		$userId		= (int) $user->getId();
		$profile	= new UserProfile;
		$profile->load($userId);
		$form->bind( $profile );
		
		if ( !$this->getRequest()->isPost() ) {
			
			return array(
				'showForm' => true,
				'user' => $user,
				'userId' => $userId,
				'userprofileForm'	=> $form,
			);
			
		}
		
		$data = (array)$this->params()->fromPost();
		$form->setData( $data );
		
		if ( !$form->isValid() ) {
			
			return array(
				'showForm'			=> true,
				'user'				=> $user,
				'userId'			=> $userId,
				'userprofileForm'	=> $form,
			);
				
		} else {
		
			$profile->exchangeArray( $data );
			$result = $profile->save();

			if ( $result === true ) {
				$this->flashMessenger()->addSuccessMessage(
					$translator->translate("user profile data has been changed")
				);
			} else {
				$this->flashMessenger()->addWarningMessage(
					$translator->translate("user profile data could not be changed")
				);
			}
			
			if ( $this->getRequest()->isXmlHttpRequest() ) {
				$response = array(
					'showForm' => false,
				);
				$sAccept = $this->getRequest()->getHeaders()->get('Accept')->toString();
				$sFancybox = $this->getRequest()->getHeaders()->get('X-Fancybox')->toString();
				if ( ( strpos($sAccept, 'text/html') !== false ) || ( strpos($sFancybox, 'true') !== false ) ) {
					$viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
					$flashMessenger = $viewHelperManager->get('flashmessenger');
					$messages = $flashMessenger()->renderCurrent('warning', array('warning alert flashmessages'));
					$messages .= $flashMessenger()->renderCurrent('success', array('success alert flashmessages'));
					$this->flashMessenger()->clearCurrentMessagesFromContainer();
					return array_merge_recursive($response, array("content" => preg_replace('/<button(.*)<\/button>/i', "", $messages)));
					
				} else {
					$messages = $this->flashMessenger()->getCurrentErrorMessages();
					return array_merge_recursive($response, array("content" => json_encode(array_merge_recursive(
						$this->flashMessenger()->getCurrentWarningMessages(),
						$this->flashMessenger()->getCurrentSuccessMessages()
					))));
				}
			} else {
				return $this->redirect()->toRoute('zfcuser');
			}
				
		}
		
	}
	
	/**
	 * @param string $translator
	 * @param string $textdomain
	 * @param string $locale
	 */
	public function translate($message, $textdomain = 'default', $locale = null) {
		return ( $this->getTranslator()->translate($message, $textdomain, $locale) );
	}
	
	/**
	 * @return the $translator
	 */
	public function getTranslator() {
		if (!$this->translator) {
			$this->setTranslator( $this->getServiceLocator()->get('translator') );
		}
		return $this->translator;
	}

	/**
	 * @param field_type $translator
	 */
	public function setTranslator($translator) {
		$this->translator = $translator;
	}
	
	
	// //	action titles
	
	/**
	 * @return the $actionTitles
	 */
	public function getActionTitles() {
		return $this->actionTitles ;
	}

	/**
	 * @param array $actionTitles
	 */
	public function setActionTitles($actionTitles = array()) {
		$this->actionTitles = $actionTitles;
		return $this;
	}

	/**
	 * @return the $actionTitles
	 */
	public function getActionTitle($action) {
		return ($this->actionTitles[$action] ?: '');
	}
	
	/**
	 * @param string $action
	 * @param string $title
	 */
	public function setActionTitle($action, $title) {
		$this->actionTitles[$action] = $title;
		return $this;
	}
	
	
	// //	toolbar items

	/**
	 * @return the $toolbarItems
	 */
	public function getToolbarItems() {
		return $this->toolbarItems ;
	}
	
	/**
	 * @param array $toolbarItems
	 */
	public function setToolbarItems($toolbarItems = array()) {
		$this->toolbarItems = $toolbarItems;
		return $this;
	}
	
	/**
	 * @return the $actionTitles
	 */
	public function getToolbarItem($action) {
		return (isset($this->toolbarItems[$action]) ? $this->toolbarItems[$action] : '');
	}
	
	/**
	 * @param string $action
	 * @param string $title
	 */
	public function setToolbarItem($action, $item) {
		$this->toolbarItems[$action] = $item;
		return $this;
	}


}
