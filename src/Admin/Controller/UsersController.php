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

namespace Admin\Controller;

use Application\Controller\BaseActionController;
use Zend\View\Model\ViewModel;
use Admin\Module as AdminModule;
use Admin\Model\User;
use Admin\Form\UserForm;
use Admin;

class UsersController extends BaseActionController
{
	protected $userTable;
	protected $AclroleTable;

	public function indexAction()
	{
		$tmplVars = $this->getTemplateVars();
		$aUserlist = $this->getUserTable()->fetchAll();
		if ( $this->getRequest()->isXmlHttpRequest() ) {
			$this->layout('layout/json');
			$datatablesData = array('data' => $aUserlist->toArray());
			$datatablesData = array_map( function ($row) {
				$actions = '<div class="btn-group btn-group-xs">'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr" href="'.$this->url('admin/default',
						array('controller'=>'users', 'action'=>'edit', 'user_id' => $row["user_id"])).'"><span class="fa fa-pencil"></span> '.$this->translate("edit").'</a>'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr" href="'.$this->url('admin/default',
						array('controller'=>'users', 'action'=>'delete', 'user_id' => $row["user_id"])).'"><span class="fa fa-trash-o"></span> '.$this->translate("delete").'</a>'.
				'</div>';
				$row->_actions_ = $actions;
			}, $datatablesData );
			echo json_encode($datatablesData); die();
			return array_merge_recursive($tmplVars, array("content" => json_encode($datatablesData)));
		}
		return new ViewModel(array(
			'userdata' => $aUserlist,
		));
	}

	public function setButtons($row)
	{
		$actions = '';
		$row['_action_'] = $actions;
		return $row;
	}

	public function addAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array('showForm' => true,)
		);
		
		$form = new UserForm();

		$roles = $this->getAclroleTable()->fetchAll()->toArray();
		$valueoptions = array();
		foreach ($roles as $role) {
			//$valueoptions[$role["aclroles_id"]] = $role["rolename"];
			$valueoptions[$role["roleslug"]] = $role["rolename"];
		}
		$form->get('aclrole')->setValueOptions($valueoptions);
				
		$request = $this->getRequest();
		$user = new User();
		if ($request->isPost()) {
			$form->setInputFilter($user->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$user->exchangeArray($form->getData());
				$this->getUserTable()->saveUser($user);
				$this->flashMessenger()->addSuccessMessage($this->translate("user has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/default', array('controller' => 'users'));
				}
				
			}
			$tmplVars["user"] = $user;
		}
		$tmplVars["form"] = $form;
		return new ViewModel($tmplVars);
	}

	public function editAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array('showForm' => true,)
		);
		$id = (int) $this->params()->fromRoute('user_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/default', array(
				'controller' => 'users',
			));
		}
		$user = $this->getUserTable()->getUser($id);

		$form	= new UserForm();
		$form->bind($user);

		$roles = $this->getAclroleTable()->fetchAll()->toArray();
		$valueoptions = array();
		foreach ($roles as $role) {
			//$valueoptions[$role["aclroles_id"]] = $role["rolename"];
			$valueoptions[$role["roleslug"]] = $role["rolename"];
		}
		$form->get('aclrole')->setValueOptions($valueoptions);
				
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setInputFilter($user->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				
				$this->getUserTable()->saveUser($user);

				// Redirect to list of users
				$this->flashMessenger()->addSuccessMessage($this->translate("user has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/default', array('controller' => 'users'));
				}
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
		$tmplVars = $this->getTemplateVars( 
			array('showForm' => true,)
		);
		$id = (int) $this->params()->fromRoute('user_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/default', array('controller' => 'users', 'action' => 'index'));
		}

		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', '');

			if (!empty($del)) {
				$id = (int) $request->getPost('id');
				$this->getUserTable()->deleteUser($id);
				$this->flashMessenger()->addSuccessMessage($this->translate("user has been deleted"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/default', array('controller' => 'users'));
				}
			}

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
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
		}
		
		if ( is_numeric($user_id) ) {
			$oUser = $users->findById($user_id);
		} else {
			$oUser = $users->findByUsername($user_id);
		}
		if ( !$oUser ) {
			$this->flashMessenger()->addWarningMessage($this->translate("user could not be found"));
			return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
		}
		if ( ($oUser->getState() != 0) || ($oUser->getToken() != $token) ) {
			$this->flashMessenger()->addWarningMessage($this->translate("confirmation token is invalid"));
			return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
		}
		
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
		$this->flashMessenger()->addSuccessMessage($this->translate("user's registration has been confirmed"));
		if ($config["zfcuser_admin_must_activate"]) {
			$oModule->sendActivationMail($oUser);
				$this->flashMessenger()->addInfoMessage($this->translate("admin has been notified for activation"));
			return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
		} else {
			$this->flashMessenger()->addSuccessMessage($this->translate("user has been activated"));
			return $this->redirect()->toRoute('zfcuser/login', array());
		}
		
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
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
		}

		if ( is_numeric($user_id) ) {
			$oUser = $users->findById($user_id);
		} else {
			$oUser = $users->findByUsername($user_id);
		}
		if ( !$oUser ) {
			$this->flashMessenger()->addWarningMessage($this->translate("user could not be found"));
			return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
		}
		if ( ($oUser->getState() != 0) || ($oUser->getToken() != $token) ) {
			$this->flashMessenger()->addWarningMessage($this->translate("activation token is invalid"));
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
		$this->flashMessenger()->addSuccessMessage($this->translate("user has been activated"));
		$oModule->sendActivationNotificationMail($oUser);
		
		return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
		
	}

	public function getUserTable()
	{
		if (!$this->userTable) {
			$sm = $this->getServiceLocator();
			$this->userTable = $sm->get('Admin\Model\UserTable');
		}
		return $this->userTable;
	}
	
	public function getAclroleTable()
	{
		if (!$this->AclroleTable) {
			$sm = $this->getServiceLocator();
			$this->AclroleTable = $sm->get('Admin\Model\AclroleTable');
		}
		return $this->AclroleTable;
	}

}
