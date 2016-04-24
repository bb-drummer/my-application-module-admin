<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels <coding@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */


namespace Admin\Controller;

use Admin\Module as AdminModule;
use Admin\Form\RequestPasswordResetForm;
use Admin\Form\ResetPasswordForm;
use Admin\Form\User;
use Admin\Form\UserData;
use Admin\Form\UserDataForm;
use Admin\Form\UserProfileForm;
use Admin\Model\UserProfile;

use Zend\Crypt\Password\Bcrypt;
use Zend\Stdlib\ResponseInterface as Response;

use ZfcUser\Controller\UserController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

use Application\Controller\Traits\ControllerTranslatorTrait;
use Application\Controller\Traits\ControllerActiontitlesTrait;
use Application\Controller\Traits\ControllerToolbarTrait;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * overrides to ZFC-User's own 'user'-controller
 * 
 * @method \ZfcUserAuthentication zfcUserAuthentication()
 */
class ZfcuserController extends UserController
{
	use ControllerTranslatorTrait;
	use ControllerActiontitlesTrait;
	use ControllerToolbarTrait;
	
    /**
     * 
     * @var array|\Admin\Model\AclroleTable
     */
    protected $aclroleTable;
    
    /**
     * 
     * @var array|\Admin\Model\UserTable
     */
    protected $userTable;
    
    /**
     * @param callable $redirectCallback
     * @param callable $redirectCallback
     */
    //public function __construct(ServiceLocatorInterface $serviceLocator, $redirectCallback)
    public function __construct($userService, $options, $registerForm, $loginForm)
    {
        $this->userService = $userService;
        $this->options = $options;
        $this->registerForm = $registerForm;
        $this->loginForm = $loginForm;
        
        /*if ( $serviceLocator ) {
    		$this->setServiceLocator($serviceLocator);
    	}
    	if (!is_callable($redirectCallback)) {
            throw new \InvalidArgumentException('You must supply a callable redirectCallback');
        }
        $this->redirectCallback = $redirectCallback;*/
        
    }

    /**
     * set current action titles
     * @return self
     */
    public function defineActionTitles() 
    {
        $this->setActionTitles(
            array(
                'login'                 => $this->translate("login"),
                'authenticate'          => $this->translate("login"),
                'logout'                => $this->translate("logout"),
                'register'              => $this->translate("register user"),
                'requestpasswordreset'  => $this->translate("reset password"),
                'changeemail'           => $this->translate("change email"),
                'changepassword'        => $this->translate("change password"),
                'resetpassword'         => $this->translate("reset password"),
                'userdata'              => $this->translate("userdata"),
                'edituserdata'          => $this->translate("edit userdata"),
                'userprofile'           => $this->translate("user profile"),
                'index'                 => $this->translate("user profile"),
                'edituserprofile'       => $this->translate("edit profile"),
            )
        );
        return $this;
    }

    /**
     * set current toolbar items
     * @return self
     */
    public function defineToolbarItems() 
    {
        $this->setToolbarItems(
            array(
                "index" => array(
            array(
                'label'            => 'edit profile',
                'icon'            => 'edit',
                'class'            => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
                'route'            => 'zfcuser/edituserprofile',
                'resource'        => 'mvc:user',
            ),
            array(
                'label'            => 'edit userdata',
                'icon'            => 'user',
                'class'            => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
                'route'            => 'zfcuser/edituserdata',
                'resource'        => 'mvc:user',
            ),
            array(
                'label'         => 'change email',
                'icon'            => 'envelope',
                'class'            => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
                'route'            => 'zfcuser/changeemail',
                'resource'        => 'mvc:user',
            ),
            array(
                'label'         => 'change password',
                'icon'            => 'lock',
                'class'            => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
                'route'            => 'zfcuser/changepassword',
                'resource'        => 'mvc:user',
            ),
            array(
                'label'            => "",
                'class'            => 'btn btn-none small btn-sm',
                'uri'            => "#",
                'active'        => false,
            ),
            array(
                'label'         => 'logout',
                'icon'            => 'power-off',
                'class'            => 'button btn btn-default small btn-sm',
                'route'            => 'zfcuser/logout',
                'resource'        => 'mvc:user',
            ),
            ),
            )
        );
        return $this;
    }

    /**
     * initialize titles and toolbar items
     * 
     * {@inheritDoc}
     * @see \Zend\Mvc\Controller\AbstractActionController::onDispatch()
     */
    public function onDispatch(MvcEvent $e)
    {
        $oEvent = $this->applyToolbarOnDispatch($e);
        $result = parent::onDispatch($oEvent);
        return $result;
    }
    
    /**
     * view user's profile data
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function userprofileAction()
    {
        // if the user is logged in...
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            // ...redirect to the login redirect route
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
    	$oIdentity = $this->zfcUserAuthentication()->getIdentity();
        $oProfile = new \Admin\Model\UserProfile();
        $oProfile->load($oIdentity->getId());
        
        return new ViewModel(
            array(
                "userProfile" => $oProfile,
                "toolbarItems" => $this->getToolbarItems(),
            )
        );
    }
    
    /**
     * User page
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        // if the user is logged in...
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            // ...redirect to the login redirect route
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
        return $this->userprofileAction();
        
    }

    /**
     * Register new user
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     * /
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
        
        $service = $this->getUserService();
        $config = $this->getServiceLocator()->get('Config');
        $translator    = $this->getTranslator();
        $oModule = new AdminModule();
        $oModule->setAppConfig($config);
        
        /** @var \Zend\Http\Response $registrationResponse * /
        $registrationResponse = parent::registerAction();
        
        if ($registrationResponse instanceof Response) {
        	$statusCode = $registrationResponse->getStatusCode();
        	if ($statusCode != 303) {
        		$this->flashMessenger()->addSuccessMessage($translator->translate("registration succeeded"));
		        if ($config['zfcuser_user_must_confirm']) {
		            $this->flashMessenger()->addInfoMessage($translator->translate("you have been sent an email with further instructions to follow"));
		        }
		        if ($config['zfcuser_admin_must_activate']) {
		        	$this->flashMessenger()->addInfoMessage($translator->translate("admin has been notified for activation"));
		        }
		        if ($service->getOptions()->getLoginAfterRegistration()) {
	            	//$oModule->sendActivationNotificationMail($user);
	            	$this->flashMessenger()->addSuccessMessage($translator->translate("registration and activation succeeded"));
		        }
        	}
	    }
        return $registrationResponse;
    }

    /**
     * request a user's password reset link
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function requestpasswordresetAction()
    {
        // if the user is logged in, we don't need to 'reset' the password
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            // redirect to the login redirect route
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }

        $config        = $this->getServiceLocator()->get('Config');
        $options    = $this->getServiceLocator()->get('zfcuser_module_options');
        /**
         * @var \Zend\Http\PhpEnvironment\Request|\Zend\Http\Request $request
         */
        $request    = $this->getRequest();
        $service    = $this->getUserService();
        $form        = new RequestPasswordResetForm(null, $options);
        $translator    = $this->getTranslator();
        
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
        
        if (!$request->isPost()) {
            return array(
            'requestPasswordResetForm' => $form,
            'enablePasswordReset' => !!$config['zfcuser']['enable_passwordreset'], // $this->getOptions()->getEnablePasswordreset(),
            'redirect' => $redirect,
            );
        }
        
        $oModule = new AdminModule();
        $oModule->setAppConfig($config);
        $identity = $this->params()->fromPost('identity');

        /** @var \Admin\Entity\User $user */
        $user = false;
        
            /** @var \Admin\Model\UserTable $userTable */
            $userTable = $this->getServiceLocator()->get('\Admin\Model\UserTable');
            /** @var \Admin\Entity\User $selectedUser */
            $selectedUser = $userTable->getUserByEmailOrUsername($identity);
            if ($selectedUser) {
                /** @var \ZfcUser\Mapper\User $userMapper */
                $userMapper = $this->getServiceLocator()->get('zfcuser_user_mapper');
                $user = $userMapper->findByUsername($selectedUser->username);
                if (!$user) {
                    $user = $userMapper->findByEmail($selectedUser->email);
                }
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
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function resetpasswordAction()
    {
        // if the user is logged in, we don't need to 'reset' the password
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            // redirect to the login redirect route
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }

        $config        = $this->getServiceLocator()->get('Config');
        $options    = $this->getServiceLocator()->get('zfcuser_module_options');
        /**
         * @var \Zend\Http\PhpEnvironment\Request|\Zend\Http\Request $request
         */
        $request    = $this->getRequest();
        $service    = $this->getUserService();
        $form        = new ResetPasswordForm(null, $options);
        $translator    = $this->getTranslator();
        
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
        
        if (!$request->isPost() ) {
            
            $user = false;
            $userId = (int) $this->params()->fromRoute('user_id');
            $resetToken = $this->params()->fromRoute('resettoken');
            
            $userTable = $this->getServiceLocator()->get('zfcuser_user_mapper');
            $user = $userTable->findById($userId);
            
            if (!$user ) {
                $this->flashMessenger()->addWarningMessage(
                    sprintf($translator->translate("invalid request"), '')
                );
                return $this->redirect()->toUrl($redirectUrl);
            }
            
            if (empty($resetToken) || ($resetToken != $user->getToken()) ) {
                $this->flashMessenger()->addWarningMessage(
                    sprintf($translator->translate("invalid request"), '')
                );
                return $this->redirect()->toUrl($redirectUrl);
            }
            
            return array(
                'user' => $user,
                'userId' => $userId,
                'resetToken' => $resetToken,
                'resetPasswordForm' => $form,
                'enablePasswordReset' => !!$config['zfcuser']['enable_passwordreset'],
                'redirect' => $redirect,
            );
            
        }
            
        $user = false;
        $userId = $this->params()->fromPost('identity');
        $resetToken = $this->params()->fromPost('token');
        
        $oModule = new AdminModule();
        $oModule->setAppConfig($config);
        $user = false;
        
        $userTable = $this->getServiceLocator()->get('zfcuser_user_mapper');
        $user = $userTable->findByEmail($userId);
            
        if (!$user ) {
            $this->flashMessenger()->addWarningMessage(
                sprintf($translator->translate("invalid request"), $userId)
            );
            return $this->redirect()->toUrl($redirectUrl);
        }
        
        if (empty($resetToken) || ($resetToken != $user->getToken()) ) {
            $this->flashMessenger()->addWarningMessage(
                sprintf($translator->translate("invalid request"), $resetToken)
            );
            return $this->redirect()->toUrl($redirectUrl);
        }
        
        $form->setData((array)$this->params()->fromPost());
        
        if (!$form->isValid() ) {
            
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
            
            $bcrypt        = new Bcrypt;
            $bcrypt->setCost($options->getPasswordCost());
            $user->setPassword($bcrypt->create($newCredential));
            $user->setToken('');
            $service->getUserMapper()->update($user);
        
            $this->flashMessenger()->addSuccessMessage(
                sprintf($translator->translate("password has been set"), $resetToken)
            );
            return $this->redirect()->toUrl(
                $this->url()->fromRoute($config["zfcuser_registration_redirect_route"]) 
                . ($redirect ? '?redirect='. rawurlencode($redirect) : '')
            );
            
        }
        
    }

    /**
     * view user's basic data
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function userdataAction()
    {
        // if the user is logged in...
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            // ...redirect to the login redirect route
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
        
        return $this->redirect()->toRoute("zfcuser");
    }
    
    /**
     * edit user's basic data
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function edituserdataAction()
    {
        
        // if the user is not logged in...
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            // ...redirect to the login redirect route
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
        
        $form        = new UserDataForm();
        $translator    = $this->getTranslator();
        
        /** @var \Admin\Entity\User $oIdentity */
        $oIdentity        = $this->zfcUserAuthentication()->getIdentity();
        /** @var \Admin\Model\UserData $oUser */
        $oUser         = new \Admin\Model\UserData();
        
        $oUser->exchangeArray($oIdentity->__getArrayCopy());
        $userId        = (int) $oIdentity->getId();

        $form->bind($oUser);
    
        if (!$this->getRequest()->isPost() ) {
            
            return new ViewModel(
                array(
                    'showForm'        => true,
                    'user'            => $oIdentity,
                    'userId'          => $userId,
                    'userdataForm'    => $form,
                )
            );
            
        }
        
        $data = (array)$this->params()->fromPost();
        $form->setData($data);
        
        if (!$form->isValid() ) {
            
            $this->flashMessenger()->addWarningMessage(
                $translator->translate("user data could not be changed")
            );
            
            return new ViewModel(
                array(
                'showForm'        => true,
                'user'            => $oIdentity,
                'userId'        => $userId,
                'userdataForm'    => $form,
                )
            );
                
        } else {
            
            $oIdentity->setDisplayName($data["display_name"]);
            $oUser->exchangeArray($oIdentity->__getArrayCopy());
            
            $this->getUserTable()->saveUser($oUser);
            
            $this->flashMessenger()->addSuccessMessage(
                $translator->translate("user data has been changed")
            );

            if ($this->getRequest()->isXmlHttpRequest() ) {
                return new ViewModel(
                    array(
                    'showForm'      => false,
                    'user'            => $oIdentity,
                    'userId'        => $userId,
                    'userdataForm'    => $form,
                    )
                );
            } else {
                return $this->redirect()->toRoute('zfcuser');
            }
    
        }

    }
    
    /**
     * edit user's profile data
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function edituserprofileAction()
    {
        
        // if the user is not logged in...
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            // ...redirect to the login redirect route
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
        
        $form        = new UserProfileForm();
        $translator    = $this->getTranslator();
        /**
         * @var \Zend\Http\PhpEnvironment\Request|\Zend\Http\Request $request
         */
        $request    = $this->getRequest();
        $user        = $this->zfcUserAuthentication()->getIdentity();
        $userId        = (int) $user->getId();
        $profile    = new UserProfile;
        $profile->load($userId);
        $form->bind($profile);
        
        if (!$this->getRequest()->isPost() ) {
            
            return array(
                'showForm'        => true,
                'user'            => $user,
                'userId'          => $userId,
                'userprofileForm' => $form,
            );
            
        }
        
        $data = (array)$this->params()->fromPost();
        $form->setData($data);
        
        if (!$form->isValid() ) {
            
            $this->flashMessenger()->addWarningMessage(
                $translator->translate("user profile data could not be changed")
            );
            return array(
                'showForm'        => true,
                'user'            => $user,
                'userId'          => $userId,
                'userprofileForm' => $form,
            );
                
        } else {
        
            $profile->exchangeArray($data);
            $profile->save();

            $this->flashMessenger()->addSuccessMessage(
                $translator->translate("user profile data has been changed")
            );
            
            if ($request->isXmlHttpRequest() ) {
                $response = array(
                    'showForm'          => false,
                    'user'                => $user,
                    'userId'            => $userId,
                    'userprofileForm'    => $form,
                );
            } else {
                return $this->redirect()->toRoute('zfcuser');
            }
                
        }
        
    }
    

    // // db mappers

    
    /**
     * retrieve user table mapper
     *
     * @return array|\Admin\Model\UserTable
     * @throws \Exception
     */
    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
            if (!$this->userTable instanceof \Admin\Model\UserTable) {
            	throw new \Exception("invalid user table object: ".gettype($this->userTable));
            }
        }
        return $this->userTable;
    }
    
    /**
     * retrieve ACL roles table mapper
     *
     * @return array|\Admin\Model\AclroleTable
     * @throws \Exception
     */
    public function getAclroleTable()
    {
        if (!$this->aclroleTable) {
            $sm = $this->getServiceLocator();
            $this->aclroleTable = $sm->get('Admin\Model\AclroleTable');
            if (!$this->aclroleTable instanceof \Admin\Model\AclroleTable) {
            	throw new \Exception("invalid ACL role table object: ".gettype($this->aclroleTable));
            }
        }
        return $this->aclroleTable;
    }
    
}
    