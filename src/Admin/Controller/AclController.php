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

use Admin\Model\Acl;
use Admin\Model\Aclresource;
use Admin\Model\Aclrole;
use Admin\Model\AclTable;
use Admin\Model\AclresourceTable;
use Admin\Model\AclroleTable;
use Admin\Form\AclForm;
use Admin\Form\AclmatrixForm;
use Admin\Form\AclresourceForm;
use Admin\Form\AclroleForm;
use Application\Controller\BaseActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class AclController extends BaseActionController
{
	protected $AclTable;
	protected $AclroleTable;
	protected $AclresourceTable;

	public function onDispatch(\Zend\Mvc\MvcEvent $e)
	{
		$this->setActionTitles(array(
			'index' => $this->translate("manage permissions"),
			'roles' => $this->translate("manage roles"),
			'resources' => $this->translate("manage resources"),
			'addacl' => $this->translate("add permission"),
			'addrole' => $this->translate("add role"),
			'addresource' => $this->translate("add resource"),
			'editacl' => $this->translate("edit acl"),
			'editrole' => $this->translate("edit role"),
			'editresource' => $this->translate("edit resource"),
			'deleteacl' => $this->translate("delete acl"),
			'deleterole' => $this->translate("delete role"),
			'deleteresource' => $this->translate("delete resource"),
		));
		return parent::onDispatch($e);
	}

	// list actions 
	public function indexAction()
	{
		return new ViewModel(array(
			'acldata'			=> $this->getAclTable()->fetchAll(),
			'acltable'			=> $this->getAclTable(),
			'roles'				=> $this->getAclroleTable()->fetchAll()->toArray(),
			'resources'			=> $this->getAclresourceTable()->fetchAll()->toArray(),
			'form'				=> new AclmatrixForm(),
		));
	}

	// list actions 
	public function acllistAction()
	{
		return new ViewModel(array(
			'acldata'			=> $this->getAclTable()->fetchAll(),
			'acltable'			=> $this->getAclTable(),
			'roles'				=> $this->getAclroleTable()->fetchAll()->toArray(),
			'resources'			=> $this->getAclresourceTable()->fetchAll()->toArray(),
			'form'				=> new AclmatrixForm(),
		));
	}

	// list actions 
	public function acldataAction()
	{
		if ( $this->getRequest()->isXmlHttpRequest() ) {
			$roles = $this->getAclroleTable()->fetchAll()->toArray();
			$resources = $this->getAclresourceTable()->fetchAll()->toArray();
			$acls = array();
			foreach ($resources as $resource) {
				$resourceacl = [];
				foreach ($roles as $role) {
					$aclstate = $this->getAclTable()
						->getAclByRoleResource($role['aclroles_id'],$resource['aclresources_id']);
					$acls[] = array( 
						'acl_id' => ($aclstate && !empty($aclstate->acl_id) ? ($aclstate->acl_id) : ''), 
						'roleslug' => $role['roleslug'], 
						'resourceslug' => $resource['resourceslug'], 
						'status' => ($aclstate && !empty($aclstate->state) ? ($aclstate->state) : 'allow')
					);
				}
			}
			$datatablesData = array(
				'tableid'	=> 'acltable',
				'data'		=> $acls
			);
			$oController = $this;
			$datatablesData['data'] = array_map( function ($row) use ($oController) {
				$actions = '<div class="btn-group btn-group-xs">'.
					(empty($row["acl_id"]) ? 
						'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/acledit',
							array('action'=>'addacl', 'acl_id' => '')).'"><span class="fa fa-pencil"></span> '.$oController->translate("add acl").'</a>'
					:
						'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/acledit',
							array('action'=>'editacl', 'acl_id' => $row["acl_id"])).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
						'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/acledit',
							array('action'=>'deleteacl', 'acl_id' => $row["acl_id"])).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'
					).							
					'</div>';
				$row["_actions_"] = $actions;
				return $row;
			}, $datatablesData['data'] );
			return $this->getResponse()->setContent(json_encode($datatablesData));
		}
		return $this->redirect()->toRoute('admin/acledit', array());
	}

	public function rolesAction()
	{

		if ( $this->getRequest()->isXmlHttpRequest() ) {
			$datatablesData = array('data' => $this->getAclroleTable()->fetchAll()->toArray());
			$oController = $this;
			$datatablesData['data'] = array_map( function ($row) use ($oController) {
				$actions = '<div class="btn-group btn-group-xs">'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/acledit',
						array('action'=>'editrole', 'acl_id' => $row["aclroles_id"])).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/acledit',
						array('action'=>'deleterole', 'acl_id' => $row["aclroles_id"])).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'.
					'</div>';
				$row["_actions_"] = $actions;
				return $row;
			}, $datatablesData['data'] );
			return $this->getResponse()->setContent(json_encode($datatablesData));
		}
		return new ViewModel(array(
			'acldata'	=> $this->getAclTable()->fetchAll(),
			'roles'		=> $this->getAclroleTable()->fetchAll(),
			'resources'	=> $this->getAclresourceTable()->fetchAll(),
		));
	}

	public function resourcesAction()
	{
		if ( $this->getRequest()->isXmlHttpRequest() ) {
			$datatablesData = array('data' => $this->getAclresourceTable()->fetchAll()->toArray());
			$oController = $this;
			$datatablesData['data'] = array_map( function ($row) use ($oController) {
				$actions = '<div class="btn-group btn-group-xs">'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/acledit',
						array('action'=>'editresource', 'acl_id' => $row["aclresources_id"])).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/acledit',
						array('action'=>'deleteresource', 'acl_id' => $row["aclresources_id"])).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'.
					'</div>';
				$row["_actions_"] = $actions;
				return $row;
			}, $datatablesData['data'] );
			return $this->getResponse()->setContent(json_encode($datatablesData));
		}
		return new ViewModel(array(
			'acldata'	=> $this->getAclTable()->fetchAll(),
			'roles'		=> $this->getAclroleTable()->fetchAll(),
			'resources'	=> $this->getAclresourceTable()->fetchAll(),
		));
	}

	
	// acl actions 
	public function addaclAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'acldata'	=> $this->getAclTable()->fetchAll(),
				'roles'		=> $this->getAclroleTable()->fetchAll(),
				'resources'	=> $this->getAclresourceTable()->fetchAll(),
				'showForm'	=> true,
			)
		);
		$this->layout()->setVariable('title', $this->translate("add permission"));
		//if (!class_exists('\Admin\Form\AclForm')) { require_once __DIR__ . '/../Form/AclForm.php'; }
		$form = new AclForm();

		$roles = $this->getAclroleTable()->fetchAll()->toArray();
		$valueoptions = array();
		foreach ($roles as $role) {
			$valueoptions[$role["aclroles_id"]] = $role["rolename"];
		}
		$form->get('aclroles_id')->setValueOptions($valueoptions);
		
		$resources = $this->getAclresourceTable()->fetchAll()->toArray();
		$valueoptions = array();
		foreach ($resources as $resource) {
			$valueoptions[$resource["aclresources_id"]] = $resource["resourcename"];
		}
		$form->get('aclresources_id')->setValueOptions($valueoptions);
		
		$request = $this->getRequest();
		$Acl = new Acl();
		if ($request->isPost()) {
			$form->setInputFilter($Acl->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$Acl->exchangeArray($form->getData());
				$this->getAclTable()->saveAcl($Acl);
				// Redirect to list of Acl
				$this->flashMessenger()->addSuccessMessage($this->translate("permission has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/acledit', array());
				}
			}
			$tmplVars["acl"] = $Acl;
		}
		$tmplVars["form"] = $form;
		return new ViewModel($tmplVars);
	}

	public function editaclAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'acldata'	=> $this->getAclTable()->fetchAll(),
				'roles'		=> $this->getAclroleTable()->fetchAll(),
				'resources'	=> $this->getAclresourceTable()->fetchAll(),
				'showForm'	=> true,
			)
		);
		$this->layout()->setVariable('title', $this->translate("change permission"));
		$id = (int) $this->params()->fromRoute('acl_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/acledit', array(
				'action' => 'addacl'
			));
		}
		$Acl = $this->getAclTable()->getAcl($id);

		$form  = new AclForm();
		$form->bind($Acl);

		$roles = $this->getAclroleTable()->fetchAll()->toArray();
		$valueoptions = array();
		foreach ($roles as $role) {
			$valueoptions[$role["aclroles_id"]] = $role["rolename"];
		}
		$form->get('aclroles_id')->setValueOptions($valueoptions);
		
		$resources = $this->getAclresourceTable()->fetchAll()->toArray();
		$valueoptions = array();
		foreach ($resources as $resource) {
			$valueoptions[$resource["aclresources_id"]] = $resource["resourcename"];
		}
		$form->get('aclresources_id')->setValueOptions($valueoptions);
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setInputFilter($Acl->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getAclTable()->saveAcl($Acl);

				// Redirect to list of Acl
				$this->flashMessenger()->addSuccessMessage($this->translate("permission has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/acledit', array());
				}
			}
		} else {
				$form->bind($Acl);
		}
		$tmplVars["acl_id"] = $id;
		$tmplVars["form"] = $form;
		return new ViewModel($tmplVars);
	}

	public function deleteaclAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'acldata'	=> $this->getAclTable()->fetchAll(),
				'roles'		=> $this->getAclroleTable()->fetchAll(),
				'resources'	=> $this->getAclresourceTable()->fetchAll(),
				'showForm'	=> true,
			)
		);
		$this->layout()->setVariable('title', $this->translate("delete permission"));
		$id = (int) $this->params()->fromRoute('acl_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/acledit', array());
		}

		$tmplVars["acl_id"] = $id;
		$tmplVars["acl"] = $this->getAclTable()->getAcl($id);
		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', 'No');

			if ($del == 'Yes') {
				$id = (int) $request->getPost('id');
				$this->getAclTable()->deleteAcl($id);
				$this->flashMessenger()->addSuccessMessage($this->translate("permission has been deleted"));
			}

			// Redirect to list of albums
			if ( $this->getRequest()->isXmlHttpRequest() ) {
				$tmplVars["showForm"] = false;
			} else {
				return $this->redirect()->toRoute('admin/acledit', array());
			}
		}

		return new ViewModel($tmplVars);
	}

	
	// role actions 
	public function addroleAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'acldata'	=> $this->getAclTable()->fetchAll(),
				'roles'		=> $this->getAclroleTable()->fetchAll(),
				'resources'	=> $this->getAclresourceTable()->fetchAll(),
				'showForm'	=> true,
			)
		);
		$this->layout()->setVariable('title', $this->translate("add role"));
		$form = new AclroleForm();

		$request = $this->getRequest();
		$Aclrole = new Aclrole();
		if ($request->isPost()) {
			$form->setInputFilter($Aclrole->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$Aclrole->exchangeArray($form->getData());
				$this->getAclroleTable()->saveAclrole($Aclrole);
				// Redirect to list of Acl
				$this->flashMessenger()->addSuccessMessage($this->translate("role has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
				}
			}
			$tmplVars["acl"] = $Aclrole;
		}
		$tmplVars["form"] = $form;
		$tmplVars["roles"] = $this->getAclroleTable()->fetchAll();
		return new ViewModel($tmplVars);
	}

	public function editroleAction()
	{
		$this->layout()->setVariable('title', $this->translate("edit role"));
		$tmplVars = $this->getTemplateVars( 
			array(
				'acldata'	=> $this->getAclTable()->fetchAll(),
				'roles'		=> $this->getAclroleTable()->fetchAll(),
				'resources'	=> $this->getAclresourceTable()->fetchAll(),
				'showForm'	=> true,
			)
		);
		$id = (int) $this->params()->fromRoute('acl_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
		}
		$Aclrole = $this->getAclroleTable()->getAclrole($id);

		$form  = new AclroleForm();
		$form->bind($Aclrole);

		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setInputFilter($Aclrole->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getAclroleTable()->saveAclrole($Aclrole);

				// Redirect to list of Acl
				$this->flashMessenger()->addSuccessMessage($this->translate("role has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
				}
			}
		} else {
				$form->bind($Aclrole);
		}
		$tmplVars["acl_id"] = $id;
		$tmplVars["form"] = $form;
		$tmplVars["roles"] = $this->getAclroleTable()->fetchAll();
		return new ViewModel($tmplVars);
	}

	public function deleteroleAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'acldata'	=> $this->getAclTable()->fetchAll(),
				'roles'		=> $this->getAclroleTable()->fetchAll(),
				'resources'	=> $this->getAclresourceTable()->fetchAll(),
				'showForm'	=> true,
			)
		);
		$this->layout()->setVariable('title', $this->translate("delete role"));
		$id = (int) $this->params()->fromRoute('acl_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
		}

		$tmplVars["acl_id"] = $id;
		$tmplVars["aclrole"] = $this->getAclroleTable()->getAclrole($id);
		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', '');

			if (!empty($del)) {
				$id = (int) $request->getPost('id');
				$this->getAclroleTable()->deleteAclrole($id);
				$this->flashMessenger()->addSuccessMessage($this->translate("role has been deleted"));
			}

			// Redirect to list of albums
			if ( $this->getRequest()->isXmlHttpRequest() ) {
				$tmplVars["showForm"] = false;
			} else {
				return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
			}
			
		}

		$tmplVars["roles"] = $this->getAclroleTable()->fetchAll();
		return new ViewModel($tmplVars);
	}

	
	// resource actions 
	public function addresourceAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'acldata'	=> $this->getAclTable()->fetchAll(),
				'roles'		=> $this->getAclroleTable()->fetchAll(),
				'resources'	=> $this->getAclresourceTable()->fetchAll(),
				'showForm'	=> true,
			)
		);
		$this->layout()->setVariable('title', $this->translate("add resource"));
		//if (!class_exists('\Admin\Form\AclForm')) { require_once __DIR__ . '/../Form/AclForm.php'; }
		$form = new AclresourceForm();

		$request = $this->getRequest();
		$Aclresource = new Aclresource();
		if ($request->isPost()) {
			$form->setInputFilter($Aclresource->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$Aclresource->exchangeArray($form->getData());
				$this->getAclresourceTable()->saveAclresource($Aclresource);
				// Redirect to list of Acl
				$this->flashMessenger()->addSuccessMessage($this->translate("resource has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
				}
			}
			$tmplVars["aclresource"] = $Aclresource;
		}
		$tmplVars["form"] = $form;
		$tmplVars["resources"] = $this->getAclresourceTable()->fetchAll();
		return new ViewModel($tmplVars);
	}

	public function editresourceAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'acldata'	=> $this->getAclTable()->fetchAll(),
				'roles'		=> $this->getAclroleTable()->fetchAll(),
				'resources'	=> $this->getAclresourceTable()->fetchAll(),
				'showForm'	=> true,
			)
		);
		$this->layout()->setVariable('title', $this->translate("edit resource"));
		$id = (int) $this->params()->fromRoute('acl_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/acledit', array(
				'action' => 'addresource'
			));
		}
		$Aclresource = $this->getAclresourceTable()->getAclresource($id);

		$form  = new AclresourceForm();
		$form->bind($Aclresource);

		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setInputFilter($Aclresource->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getAclresourceTable()->saveAclresource($Aclresource);

				// Redirect to list of Acl
				$this->flashMessenger()->addSuccessMessage($this->translate("resource has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
				}
			}
		} else {
				$form->bind($Aclresource); //->getArrayCopy());
		}
		$tmplVars["acl_id"] = $id;
		$tmplVars["aclresource"] = $Aclresource;
		$tmplVars["form"] = $form;
		$tmplVars["resources"] = $this->getAclresourceTable()->fetchAll();
		return new ViewModel($tmplVars);
	}

	public function deleteresourceAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'acldata'	=> $this->getAclTable()->fetchAll(),
				'roles'		=> $this->getAclroleTable()->fetchAll(),
				'resources'	=> $this->getAclresourceTable()->fetchAll(),
				'showForm'	=> true,
			)
		);
		$this->layout()->setVariable('title', $this->translate("delete resource"));
		$id = (int) $this->params()->fromRoute('acl_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
		}

		$tmplVars["acl_id"] = $id;
		$tmplVars["aclresource"] = $this->getAclresourceTable()->getAclresource($id);
		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', '');

			if (!empty($del)) {
				$id = (int) $request->getPost('id');
				$this->getAclresourceTable()->deleteAclresource($id);
				$this->flashMessenger()->addSuccessMessage($this->translate("resource has been deleted"));
			}

			// Redirect to list of albums
			if ( $this->getRequest()->isXmlHttpRequest() ) {
				$tmplVars["showForm"] = false;
			} else {
				return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
			}
		}

		$tmplVars["resources"] = $this->getAclresourceTable()->fetchAll();
		return new ViewModel($tmplVars);
	}

	
	// table getters
	
	public function getAclTable()
	{
		if (!$this->AclTable) {
			$sm = $this->getServiceLocator();
			$this->AclTable = $sm->get('Admin\Model\AclTable');
		}
		return $this->AclTable;
	}

	public function getAclroleTable()
	{
		if (!$this->AclroleTable) {
			$sm = $this->getServiceLocator();
			$this->AclroleTable = $sm->get('Admin\Model\AclroleTable');
		}
		return $this->AclroleTable;
	}

	public function getAclresourceTable()
	{
		if (!$this->AclresourceTable) {
			$sm = $this->getServiceLocator();
			$this->AclresourceTable = $sm->get('Admin\Model\AclresourceTable');
		}
		return $this->AclresourceTable;
	}
	
}
