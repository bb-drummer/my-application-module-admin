<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Admin for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Controller\BaseActionController;
use Zend\View\Model\ViewModel;
use Admin\Module as AdminModule;
use Admin\Model\User;
use Admin\Form\UserForm;
use Admin;

class UsersController extends BaseActionController
{
	protected $userTable;

	public function indexAction()
    {
        return new ViewModel(array(
            'userdata' => $this->getUserTable()->fetchAll(),
        ));
    }

    public function addAction()
    {
        $tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array()
		);
        //if (!class_exists('\Admin\Form\UserForm')) { require_once __DIR__ . '/../Form/UserForm.php'; }
        $form = new UserForm();
        $form->get('submit')->setValue('Benutzer anlegen');

        $request = $this->getRequest();
        $user = new User();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $this->getUserTable()->saveUser($user);
                // Redirect to list of users
        		$this->flashMessenger()->addSuccessMessage("Benutzer wurde angelegt.");
                return $this->redirect()->toRoute('admin/default', array('controller' => 'users'));
            }
	        $tmplVars["user"] = $user;
        }
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    public function editAction()
    {
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array()
		);
        $id = (int) $this->params()->fromRoute('user_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/default', array(
            	'controller' => 'users',
                'action' => 'add'
            ));
        }
        $user = $this->getUserTable()->getUser($id);

        $form  = new UserForm();
        $form->bind($user);
        $form->get('submit')->setAttribute('value', 'speichern');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
            	
                $this->getUserTable()->saveUser($user);

                // Redirect to list of users
        		$this->flashMessenger()->addSuccessMessage("Benutzer wurde gespeichert.");
                return $this->redirect()->toRoute('admin/default', array('controller' => 'users', 'action' => 'index'));
            }
        } else {
       		$form->bind($user);
        }
        $tmplVars["user_id"] = $id;
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('user_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/default', array('controller' => 'users', 'action' => 'index'));
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
                $id = (int) $request->getPost('id');
                $this->getUserTable()->deleteUser($id);
        		$this->flashMessenger()->addSuccessMessage("Benutzer wurde entfernt.");
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('admin/default', array('controller' => 'users', 'action' => 'index'));
        }

        $tmplVars["user_id"] = $id;
        $tmplVars["user"] = $this->getUserTable()->getUser($id);
        return new ViewModel($tmplVars);
    }

    public function confirmAction()
    {
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array()
		);
        $config = $this->getServiceLocator()->get('Config');
        $users = $this->getServiceLocator()->get('zfcuser_user_mapper');
		
        $user_id	= $this->params()->fromRoute('user_id', '');
        $token		= $this->params()->fromRoute('confirmtoken', '');
        if (empty($user_id) || empty($token)) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }
        
		if ( is_numeric($user_id) ) {
			$oUser = $users->findById($user_id);
		} else {
			$oUser = $users->findByUsername($user_id);
		}
        if ( !$oUser ) {
        	$this->flashMessenger()->addWarningMessage("Benutzer nicht gefunden");
        	return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }
        if ( ($oUser->getState() != 0) || ($oUser->getToken() != $token) ) {
        	$this->flashMessenger()->addWarningMessage("Bestätigung ungültig");
        	return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }

        //echo '<pre>'. print_r($this->getServiceLocator()->get('Config'), true) .'</pre>'; die();
        
        // all ok, do stuff...
        $oModule = new AdminModule();
        $oModule->setAppConfig($config);
        $this->getUserTable()->getTableGateway()->update(array(
        	"state"		=> ($config["zfcuser_admin_must_activate"]) ? "0" : "1",
        	"token"		=> $oModule->createUserToken($oUser),
        ), array(
        	"user_id"	=> $oUser->getId(),
        ));
        $oUser = $users->findById($user_id);
        $this->flashMessenger()->addSuccessMessage("Benutzer-Bestätigung erfolgreich");
        if ($config["zfcuser_admin_must_activate"]) {
        	$oModule->sendActivationMail($oUser);
       		$this->flashMessenger()->addInfoMessage("Der Administrator wird zur Aktivierung benachrichtigt");
        	return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        } else {
        	$this->flashMessenger()->addSuccessMessage("Benutzer-Aktivierung erfolgreich");
        	return $this->redirect()->toRoute('zfcuser/login', array());
        }
        
        $tmplVars["user_id"]	= $user_id;
        $tmplVars["user"]		= $oUser;
        $tmplVars["token"]		= $token;
        return new ViewModel($tmplVars);
    }

    public function activateAction()
    {
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array()
		);
        $config	= $this->getServiceLocator()->get('Config');
        $users	= $this->getServiceLocator()->get('zfcuser_user_mapper');
		
        $user_id	= $this->params()->fromRoute('user_id', '');
        $token		= $this->params()->fromRoute('activatetoken', '');
        if (empty($user_id) || empty($token)) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }

		if ( is_numeric($user_id) ) {
			$oUser = $users->findById($user_id);
		} else {
			$oUser = $users->findByUsername($user_id);
		}
        if ( !$oUser ) {
        	$this->flashMessenger()->addWarningMessage("Benutzer nicht gefunden");
        	return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }
        if ( ($oUser->getState() != 0) || ($oUser->getToken() != $token) ) {
        	$this->flashMessenger()->addWarningMessage("Aktivierung ungültig");
        	return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }
        
        // all ok, do stuff...
        $oModule = new AdminModule();
        $oModule->setAppConfig($config);
        $this->getUserTable()->getTableGateway()->update(array(
        	"state"		=> "1",
        	"token"		=> $user->token,
        ), array(
        	"user_id"	=> $oUser->getId(),
        ));
        $oUser = $users->findById($user_id);
        $this->flashMessenger()->addSuccessMessage("Benutzer-Aktivierung erfolgreich");
        $oModule->sendActivationNotificationMail($oUser);
        return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        
        $tmplVars["user_id"]	= $user_id;
        $tmplVars["user"]		= $oUser;
        $tmplVars["token"]		= $token;
        return new ViewModel($tmplVars);
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }
}
