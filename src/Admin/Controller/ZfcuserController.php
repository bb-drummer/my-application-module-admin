<?php
/**
 * overrides to ZFC-User's own 'user'-controller
 * 
 * Zend Framework (http://framework.zend.com/)
 *
 * @link		http://github.com/zendframework/Admin for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license	http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Module as AdminModule;
use Admin\Form\RequestPasswordResetForm;
use Admin\Form\ResetPasswordForm;
use Admin\Form\UserDataForm;
use Admin\Form\UserProfileForm;

use Zend\Crypt\Password\Bcrypt;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;

use ZfcUser\Controller\UserController;

class ZfcuserController extends UserController
{
	
	protected $translator;
	
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
		$user = false;
		
		try {
    		$userTable = $this->getServiceLocator()->get('\Admin\Model\UserTable');
    		$selectedUser = $userTable->getUserByEmailOrUsername($identity);
    		if ($selectedUser) {
    			$userTable = $this->getServiceLocator()->get('zfcuser_user_mapper');
    			$user = $userTable->findByUsername($selectedUser->username);
    			if (!user) {
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
		
		$config		= $this->getServiceLocator()->get('Config');
		$options	= $this->getServiceLocator()->get('zfcuser_module_options');
		$request	= $this->getRequest();
		$service	= $this->getUserService();
		$translator	= $this->getTranslator();
		
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
		
		$user = $this->zfcUserAuthentication()->getIdentity();
		$oUser = new \Admin\Model\User();
		$oUser->exchangeArray($user->getArrayCopy());
		$userId = (int) $user->getId();

		$form->bind( $oUser );
		
		if ( !$this->getRequest()->isPost() ) {
			
			return array(
				'user' => $user,
				'userId' => $userId,
				'userdataForm'  => $form,
			);
			
		}
		
		$data = (array)$this->params()->fromPost();
		$form->setData( $data );
		
		if ( !$form->isValid() ) {
			
			return array(
				'user' => $user,
				'userId' => $userId,
				'userdataForm'  => $form,
			);
				
		} else {
		
			$profile->exchangeArray( $data );
			$result = $profile->save();
			if ( $result === true ) {
				$this->flashMessenger()->addSuccessMessage(
					$translator->translate("user data has been changed")
				);
			} else {
				$this->flashMessenger()->addSuccessMessage(
					$translator->translate("user data could not be changed")
				);
			}
			return $this->redirect()->toRoute('zfcuser');
				
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
		
		$user = $this->zfcUserAuthentication()->getIdentity();
		$userId = (int) $user->getId();

		$profile = $user->getProfile();
		$form->bind( $profile );
		
		if ( !$this->getRequest()->isPost() ) {
			
			return array(
				'user' => $user,
				'userId' => $userId,
				'userprofileForm'  => $form,
			);
			
		}
		
		$data = (array)$this->params()->fromPost();
		$form->setData( $data );
		
		if ( !$form->isValid() ) {
			
			return array(
				'user' => $user,
				'userId' => $userId,
				'userprofileForm'  => $form,
			);
				
		} else {
		
			$profile->exchangeArray( $data );
			$result = $profile->save();

			if ( $result === true ) {
				$this->flashMessenger()->addSuccessMessage(
					$translator->translate("user profile data has been changed")
				);
			} else {
				$this->flashMessenger()->addSuccessMessage(
					$translator->translate("user profile data could not be changed")
				);
			}
			
			if ( $this->getRequest()->isXmlHttpRequest() ) {
				$sAccept = $this->getRequest()->getHeaders()->get('Accept')->toString();
				if ( strpos($sAccept, 'text/html') !== false ) {
					$this->layout('layout/ajax');
					echo $this->flashMessenger()->render('error', array('error alert flashmessages'));
					echo $this->flashMessenger()->render('warning', array('warning alert flashmessages'));
					echo $this->flashMessenger()->render('success', array('success alert flashmessages'));
					echo $this->flashMessenger()->render('info', array('info alert flashmessages'));
				} else {
					$this->layout('layout/json');
					echo json_encode($this->flashMessenger()->getMessages());
				}
				exit();
			} else {
				//$oController->layout('layout/layout');
				return $this->redirect()->toRoute('zfcuser');
			}
				
			
				
		}
		
		
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


}
