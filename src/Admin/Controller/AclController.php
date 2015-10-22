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
use Zend\View\Model\ViewModel;
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
use Zend\View\HelperPluginManager;
use Application\Controller\BaseActionController;

class AclController extends BaseActionController
{
	protected $AclTable;
	protected $AclroleTable;
	protected $AclresourceTable;

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

	public function rolesAction()
    {
        return new ViewModel(array(
            'acldata'	=> $this->getAclTable()->fetchAll(),
            'roles'		=> $this->getAclroleTable()->fetchAll(),
            'resources'	=> $this->getAclresourceTable()->fetchAll(),
        ));
    }

	public function resourcesAction()
    {
        return new ViewModel(array(
            'acldata'	=> $this->getAclTable()->fetchAll(),
            'roles'		=> $this->getAclroleTable()->fetchAll(),
            'resources'	=> $this->getAclresourceTable()->fetchAll(),
        ));
    }

    
	// acl actions 
    public function addaclAction()
    {
        $tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array(
	            'acldata'	=> $this->getAclTable()->fetchAll(),
	            'roles'		=> $this->getAclroleTable()->fetchAll(),
	            'resources'	=> $this->getAclresourceTable()->fetchAll(),
	        )
		);
        //if (!class_exists('\Admin\Form\AclForm')) { require_once __DIR__ . '/../Form/AclForm.php'; }
        $form = new AclForm();
        $form->get('submit')->setValue('Berechtigung anlegen');

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
        		$this->flashMessenger()->addSuccessMessage("ACL wurde angelegt.");
                return $this->redirect()->toRoute('admin/acledit', array());
            }
	        $tmplVars["acl"] = $Acl;
        }
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    public function editaclAction()
    {
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array(
	            'acldata'	=> $this->getAclTable()->fetchAll(),
	            'roles'		=> $this->getAclroleTable()->fetchAll(),
	            'resources'	=> $this->getAclresourceTable()->fetchAll(),
	        )
		);
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/acledit', array(
                'action' => 'addacl'
            ));
        }
        $Acl = $this->getAclTable()->getAcl($id);

        $form  = new AclForm();
        $form->bind($Acl);
        $form->get('submit')->setAttribute('value', 'speichern');

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
        		$this->flashMessenger()->addSuccessMessage("ACL wurde gespeichert.");
                return $this->redirect()->toRoute('admin/acledit', array());
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
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array(
	            'acldata'	=> $this->getAclTable()->fetchAll(),
	            'roles'		=> $this->getAclroleTable()->fetchAll(),
	            'resources'	=> $this->getAclresourceTable()->fetchAll(),
	        )
		);
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/acledit', array());
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getAclTable()->deleteAcl($id);
        		$this->flashMessenger()->addSuccessMessage("ACL wurde entfernt.");
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('admin/acledit', array());
        }

        $tmplVars["acl_id"] = $id;
        $tmplVars["acl"] = $this->getAclTable()->getAcl($id);
        return new ViewModel($tmplVars);
    }

    
	// role actions 
    public function addroleAction()
    {
        $tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array(
	            'acldata'	=> $this->getAclTable()->fetchAll(),
	            'roles'		=> $this->getAclroleTable()->fetchAll(),
	            'resources'	=> $this->getAclresourceTable()->fetchAll(),
	        )
		);
        $form = new AclroleForm();
        $form->get('submit')->setValue('Rolle anlegen');

        $request = $this->getRequest();
        $Aclrole = new Aclrole();
        if ($request->isPost()) {
            $form->setInputFilter($Aclrole->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $Aclrole->exchangeArray($form->getData());
                $this->getAclroleTable()->saveAclrole($Aclrole);
                // Redirect to list of Acl
        		$this->flashMessenger()->addSuccessMessage("Rolle wurde angelegt.");
                return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
            }
	        $tmplVars["acl"] = $Aclrole;
        }
        $tmplVars["form"] = $form;
        $tmplVars["roles"] = $this->getAclroleTable()->fetchAll();
        return new ViewModel($tmplVars);
    }

    public function editroleAction()
    {
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array(
	            'acldata'	=> $this->getAclTable()->fetchAll(),
	            'roles'		=> $this->getAclroleTable()->fetchAll(),
	            'resources'	=> $this->getAclresourceTable()->fetchAll(),
	        )
		);
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/acledit', array(
                'action' => 'addrole'
            ));
        }
        $Aclrole = $this->getAclroleTable()->getAclrole($id);

        $form  = new AclroleForm();
        $form->bind($Aclrole);
        $form->get('submit')->setAttribute('value', 'speichern');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($Aclrole->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getAclroleTable()->saveAclrole($Aclrole);

                // Redirect to list of Acl
        		$this->flashMessenger()->addSuccessMessage("Rolle wurde gespeichert.");
                return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
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
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array(
	            'acldata'	=> $this->getAclTable()->fetchAll(),
	            'roles'		=> $this->getAclroleTable()->fetchAll(),
	            'resources'	=> $this->getAclresourceTable()->fetchAll(),
	        )
		);
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
        		$id = (int) $request->getPost('id');
                $this->getAclroleTable()->deleteAclrole($id);
        		$this->flashMessenger()->addSuccessMessage("Rolle wurde entfernt.");
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
        }

        $tmplVars["acl_id"] = $id;
        $tmplVars["aclrole"] = $this->getAclroleTable()->getAclrole($id);
        $tmplVars["roles"] = $this->getAclroleTable()->fetchAll();
        return new ViewModel($tmplVars);
    }

    
	// resource actions 
    public function addresourceAction()
    {
        $tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array(
	            'acldata'	=> $this->getAclTable()->fetchAll(),
	            'roles'		=> $this->getAclroleTable()->fetchAll(),
	            'resources'	=> $this->getAclresourceTable()->fetchAll(),
	        )
		);
        //if (!class_exists('\Admin\Form\AclForm')) { require_once __DIR__ . '/../Form/AclForm.php'; }
        $form = new AclresourceForm();
        $form->get('submit')->setValue('Resource anlegen');

        $request = $this->getRequest();
        $Aclresource = new Aclresource();
        if ($request->isPost()) {
            $form->setInputFilter($Aclresource->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $Aclresource->exchangeArray($form->getData());
                $this->getAclresourceTable()->saveAclresource($Aclresource);
                // Redirect to list of Acl
        		$this->flashMessenger()->addSuccessMessage("Resource wurde angelegt.");
                return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
            }
	        $tmplVars["aclresource"] = $Aclresource;
        }
        $tmplVars["form"] = $form;
        $tmplVars["resources"] = $this->getAclresourceTable()->fetchAll();
        return new ViewModel($tmplVars);
    }

    public function editresourceAction()
    {
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array(
	            'acldata'	=> $this->getAclTable()->fetchAll(),
	            'roles'		=> $this->getAclroleTable()->fetchAll(),
	            'resources'	=> $this->getAclresourceTable()->fetchAll(),
	        )
		);
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/acledit', array(
                'action' => 'addresource'
            ));
        }
        $Aclresource = $this->getAclresourceTable()->getAclresource($id);

        $form  = new AclresourceForm();
        $form->bind($Aclresource);
        $form->get('submit')->setAttribute('value', 'speichern');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($Aclresource->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getAclresourceTable()->saveAclresource($Aclresource);

                // Redirect to list of Acl
        		$this->flashMessenger()->addSuccessMessage("Resource wurde speichert.");
                return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
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
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array(
	            'acldata'	=> $this->getAclTable()->fetchAll(),
	            'roles'		=> $this->getAclroleTable()->fetchAll(),
	            'resources'	=> $this->getAclresourceTable()->fetchAll(),
	        )
		);
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
                $id = (int) $request->getPost('id');
                $this->getAclresourceTable()->deleteAclresource($id);
        		$this->flashMessenger()->addSuccessMessage("Resource wurde entfernt.");
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
        }

        $tmplVars["acl_id"] = $id;
        $tmplVars["aclresource"] = $this->getAclresourceTable()->getAclresource($id);
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
