<?php
/**
 * overrides to ZFC-User's own 'user'-controller
 * 
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Admin for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Module as AdminModule;
use Admin\Form\RequestPasswordResetForm;
use Admin\Form\ResetPasswordForm;
use ZfcUser\Controller\UserController; 
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;

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
     * Register new user
     */
    public function requestpasswordresetAction()
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

        $options = $this->getServiceLocator()->get('zfcuser_module_options');
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
                'requestPasswordResetForm' => $form,
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
     * Register new user
     */
    public function resetpasswordAction()
    {
       	return $this->redirect()->toUrl($this->url()->fromRoute($config["zfcuser_registration_redirect_route"]) . ($redirect ? '?redirect='. rawurlencode($redirect) : ''));
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
