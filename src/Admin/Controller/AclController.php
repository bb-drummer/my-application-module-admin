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
	
	/**
	 * @var \Admin\Model\AclTable
	 */
	protected $AclTable;
	
	/**
	 * @var \Admin\Model\AclroleTable
	 */
	protected $AclroleTable;
	
	/**
	 * @var \Admin\Model\AclresourceTable
	 */
	protected $AclresourceTable;

    /**
     * initialize titles and toolbar items
     * 
     * {@inheritDoc}
     * @see \Zend\Mvc\Controller\AbstractActionController::onDispatch()
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->setToolbarItems(
            array(
                "index" => array(
                    array(
                        'label' => 'add role',
                        'icon' => 'plus',
                        'class' => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
                        'route' => 'admin/acledit',
                        'action' => 'addrole',
                        'resource' => 'mvc:user',
                    ),
                    array(
                        'label' => 'manage roles',
                        'icon' => 'user',
                        'class' => 'button btn btn-default small btn-sm btn-cta',
                        'route' => 'admin/acledit',
                        'action' => 'roles',
                        'resource' => 'mvc:user',
                    ),
                    array(
                        'label' => "",
                        'class' => 'btn btn-none small btn-sm',
                        'uri' => "#",
                        'active' => false,
                    ),
                    array(
                        'label' => 'add resource',
                        'icon' => 'plus',
                        'class' => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
                        'route' => 'admin/acledit',
                        'action' => 'addresource',
                        'resource' => 'mvc:user',
                    ),
                    array(
                        'label' => 'manage resources',
                        'icon' => 'list-alt',
                        'class' => 'button btn btn-default small btn-sm btn-cta',
                        'route' => 'admin/acledit',
                        'action' => 'roles',
                        'resource' => 'mvc:user',
                    ),
                  ),
               "roles" => array(
                    array(
                        'label' => 'add role',
                        'icon' => 'plus',
                        'class' => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
                        'route' => 'admin/acledit',
                        'action' => 'addrole',
                        'resource' => 'mvc:user',
                    ),
                    array(
                        'label' => "",
                        'class' => 'btn btn-none small btn-sm',
                        'uri' => "#",
                        'active' => false,
                    ),
                    array(
                        'label' => 'ACL',
                        'icon' => 'asterisk',
                        'class' => 'button btn btn-default small btn-sm btn-cta',
                        'route' => 'admin/acledit',
                        'resource' => 'mvc:user',
                    ),
                    array(
                        'label' => 'manage resources',
                        'icon' => 'list-alt',
                        'class' => 'button btn btn-default small btn-sm btn-cta',
                        'route' => 'admin/acledit',
                        'action' => 'resources',
                        'resource' => 'mvc:user',
                    ),
               ),
               "resources" => array(
                    array(
                        'label' => 'add resource',
                        'icon' => 'plus',
                        'class' => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
                        'route' => 'admin/acledit',
                        'action' => 'addresource',
                        'resource' => 'mvc:user',
                    ),
                    array(
                        'label' => "",
                        'class' => 'btn btn-none small btn-sm',
                        'uri' => "#",
                        'active' => false,
                    ),
                    array(
                        'label' => 'ACL',
                        'icon' => 'asterisk',
                        'class' => 'button btn btn-default small btn-sm btn-cta',
                        'route' => 'admin/acledit',
                        'resource' => 'mvc:user',
                    ),
                    array(
                        'label' => 'manage roles',
                        'icon' => 'user',
                        'class' => 'button btn btn-default small btn-sm btn-cta',
                        'route' => 'admin/acledit',
                        'action' => 'roles',
                        'resource' => 'mvc:user',
                    ),
               ),
            )
        );
        $this->setActionTitles(
            array(
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
            )
        );
        return parent::onDispatch($e);
    }

    /**
     * list acl
     * {@inheritDoc}
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        return new ViewModel(
            array(
                'acldata' => $this->getAclTable()->fetchAll(),
                'acltable' => $this->getAclTable(),
                'roles' => $this->getAclroleTable()->fetchAll()->toArray(),
                'resources' => $this->getAclresourceTable()->fetchAll()->toArray(),
                'form' => new AclmatrixForm(),
            )
        );
    }

    /**
     * list acl in a table
     * @return \Zend\View\Model\ViewModel
     */ 
    public function acllistAction()
    {
        return $this->indexAction();
    }

    /**
     * list acl in json
     * @return mixed|\Zend\Http\Response
     */
    public function acldataAction()
    {
        if ( $this->isXHR() ) {
            $roles = $this->getAclroleTable()->fetchAll()->toArray();
            $resources = $this->getAclresourceTable()->fetchAll()->toArray();
            $acls = array();
            foreach ($resources as $resource) {
                $resourceacl = [];
                foreach ($roles as $role) {
                    $aclstate = $this->getAclTable()
                        ->getAclByRoleResource($role['aclroles_id'], $resource['aclresources_id']);
                    $acls[] = array( 
                        'acl_id' => ($aclstate && !empty($aclstate->acl_id) ? ($aclstate->acl_id) : ''), 
                        'roleslug' => $role['roleslug'], 
                        'resourceslug' => $resource['resourceslug'], 
                        'status' => ($aclstate && !empty($aclstate->state) ? ($aclstate->state) : 'allow')
                    );
                }
            }
            $datatablesData = array(
                'tableid' => 'acltable',
                'data' => $acls
            );
            $oController = $this;
            $datatablesData['data'] = array_map(
                function ($row) use ($oController) {
                    $actions = '<div class="button-group tiny btn-group btn-group-xs">'.
                        (empty($row["acl_id"]) ? 
                            '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                                'admin/acledit',
                                array('action'=>'addacl', 'acl_id' => '')
                            ).'"><span class="fa fa-pencil"></span> '.$oController->translate("add acl").'</a>'
                            :
                            '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                                'admin/acledit',
                                array('action'=>'editacl', 'acl_id' => $row["acl_id"])
                            ).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
                            '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                                'admin/acledit',
                                array('action'=>'deleteacl', 'acl_id' => $row["acl_id"])
                            ).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'
                        ).                            
                    '</div>';
                    $row["_actions_"] = $actions;
                    return $row;
                }, $datatablesData['data'] 
            );
            return $this->getResponse()->setContent(json_encode($datatablesData));
        }
        return $this->redirect()->toRoute('admin/acledit', array());
    }

    /**
     * list roles
     * @return \Zend\View\Model\ViewModel
     */
    public function rolesAction()
    {

        if ( $this->isXHR() ) {
            $datatablesData = array('data' => $this->getAclroleTable()->fetchAll()->toArray());
            $oController = $this;
            $datatablesData['data'] = array_map(
                function ($row) use ($oController) {
                    $actions = '<div class="button-group tiny btn-group btn-group-xs">'.
                        '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                            'admin/acledit',
                            array('action'=>'editrole', 'acl_id' => $row["aclroles_id"])
                        ).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
                        '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                            'admin/acledit',
                            array('action'=>'deleterole', 'acl_id' => $row["aclroles_id"])
                        ).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'.
                    '</div>';
                    $row["_actions_"] = $actions;
                    return $row;
                }, $datatablesData['data'] 
            );
            return $this->getResponse()->setContent(json_encode($datatablesData));
        }
        return new ViewModel(
            array(
                'acldata' => $this->getAclTable()->fetchAll(),
                'roles' => $this->getAclroleTable()->fetchAll(),
                'resources' => $this->getAclresourceTable()->fetchAll(),
            )
        );
    }

    /**
     * list resources
     * @return \Zend\View\Model\ViewModel
     */
    public function resourcesAction()
    {
        if ( $this->isXHR() ) {
            $datatablesData = array('data' => $this->getAclresourceTable()->fetchAll()->toArray());
            $oController = $this;
            $datatablesData['data'] = array_map(
                function ($row) use ($oController) {
                    $actions = '<div class="button-group tiny btn-group btn-group-xs">'.
                    '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                        'admin/acledit',
                        array('action'=>'editresource', 'acl_id' => $row["aclresources_id"])
                    ).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
                    '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                        'admin/acledit',
                        array('action'=>'deleteresource', 'acl_id' => $row["aclresources_id"])
                    ).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'.
                    '</div>';
                    $row["_actions_"] = $actions;
                    return $row;
                }, $datatablesData['data'] 
            );
            return $this->getResponse()->setContent(json_encode($datatablesData));
        }
        return new ViewModel(
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            )
        );
    }

    /**
     * add acl entry
     * @return \Zend\View\Model\ViewModel
     */
    public function addaclAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            'showForm' => true,
            )
        );
        $form = new AclForm();

        $roles = $this->getAclroleTable()->fetchAll()->toArray();
        $valueoptions = array();
        foreach ($roles as $role) {
            $valueoptions[$role["aclroles_id"]] = $role["rolename"];
        }
        /**
         * @var \Zend\Form\Element\Select $selectRoles
         */
        $selectRoles = $form->get('aclroles_id');
        $selectRoles->setValueOptions($valueoptions);
        
        $resources = $this->getAclresourceTable()->fetchAll()->toArray();
        $valueoptions = array();
        foreach ($resources as $resource) {
            $valueoptions[$resource["aclresources_id"]] = $resource["resourcename"];
        }
        /**
         * @var \Zend\Form\Element\Select $selectResources
         */
        $selectResources = $form->get('aclresources_id');
        $selectResources->setValueOptions($valueoptions);
        
        $request = $this->getRequest();
        $Acl = new Acl();
        if ($request->isPost()) {
            $form->setInputFilter($Acl->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $Acl->exchangeArray($form->getData());
                $this->getAclTable()->saveAcl($Acl);
                
                $this->reinitACLCache();
                
                // Redirect to list of Acl
                $this->flashMessenger()->addSuccessMessage($this->translate("permission has been saved"));
                if ( $this->isXHR() ) {
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

    /**
     * edit acl entry
     * @return \Zend\View\Model\ViewModel
     */
    public function editaclAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            'showForm' => true,
            )
        );
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute(
                'admin/acledit', array(
                'action' => 'addacl'
                )
            );
        }
        try {
            $Acl = $this->getAclTable()->getAcl($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/acledit', array());
        }

        $form  = new AclForm();
        $form->bind($Acl);

        $roles = $this->getAclroleTable()->fetchAll()->toArray();
        $valueoptions = array();
        foreach ($roles as $role) {
            $valueoptions[$role["aclroles_id"]] = $role["rolename"];
        }
        /**
         * @var \Zend\Form\Element\Select $selectRoles
         */
        $selectRoles = $form->get('aclroles_id');
        $selectRoles->setValueOptions($valueoptions);
        
        $resources = $this->getAclresourceTable()->fetchAll()->toArray();
        $valueoptions = array();
        foreach ($resources as $resource) {
            $valueoptions[$resource["aclresources_id"]] = $resource["resourcename"];
        }
        /**
         * @var \Zend\Form\Element\Select $selectResources
         */
        $selectResources = $form->get('aclresources_id');
        $selectResources->setValueOptions($valueoptions);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($Acl->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getAclTable()->saveAcl($Acl);

                $this->reinitACLCache();
                
                // Redirect to list of Acl
                $this->flashMessenger()->addSuccessMessage($this->translate("permission has been saved"));
                if ( $this->isXHR() ) {
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

    /**
     * delete acl entry
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteaclAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            'showForm' => true,
            )
        );
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/acledit', array());
        }

        $tmplVars["acl_id"] = $id;
        try {
            $Acl = $this->getAclTable()->getAcl($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/acledit', array());
        }
        $tmplVars["acl"] = $Acl;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getAclTable()->deleteAcl($id);
                $this->flashMessenger()->addSuccessMessage($this->translate("permission has been deleted"));
                
                $this->reinitACLCache();
            }

            // Redirect to list of albums
            if ( $this->isXHR() ) {
                $tmplVars["showForm"] = false;
            } else {
                return $this->redirect()->toRoute('admin/acledit', array());
            }
        }

        return new ViewModel($tmplVars);
    }

    /**
     * add role item
     * @return \Zend\View\Model\ViewModel
     */
    public function addroleAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            'showForm' => true,
            )
        );
        $form = new AclroleForm();

        $request = $this->getRequest();
        $Aclrole = new Aclrole();
        if ($request->isPost()) {
            $form->setInputFilter($Aclrole->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $Aclrole->exchangeArray($form->getData());
                $this->getAclroleTable()->saveAclrole($Aclrole);
                
                $this->reinitACLCache();
                
                // Redirect to list of Acl
                $this->flashMessenger()->addSuccessMessage($this->translate("role has been saved"));
                if ( $this->isXHR() ) {
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

    /**
     * edit role item
     * @return \Zend\View\Model\ViewModel
     */
    public function editroleAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            'showForm' => true,
            )
        );
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
        }
        try {
            $Aclrole = $this->getAclroleTable()->getAclrole($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
        }

        $form  = new AclroleForm();
        $form->bind($Aclrole);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($Aclrole->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getAclroleTable()->saveAclrole($Aclrole);

                $this->reinitACLCache();
                
                // Redirect to list of Acl
                $this->flashMessenger()->addSuccessMessage($this->translate("role has been saved"));
                if ( $this->isXHR() ) {
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

    /**
     * delete role item
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteroleAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            'showForm' => true,
            )
        );
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
        }

        $tmplVars["acl_id"] = $id;
        try {
            $Aclrole = $this->getAclroleTable()->getAclrole($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
        }
        $tmplVars["aclrole"] = $Aclrole;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
                $id = (int) $request->getPost('id');
                $this->getAclroleTable()->deleteAclrole($id);
                $this->flashMessenger()->addSuccessMessage($this->translate("role has been deleted"));
            
                $this->reinitACLCache();
            }

            // Redirect to list of albums
            if ( $this->isXHR() ) {
                $tmplVars["showForm"] = false;
            } else {
                return $this->redirect()->toRoute('admin/acledit', array('action' => 'roles'));
            }
            
        }

        $tmplVars["roles"] = $this->getAclroleTable()->fetchAll();
        return new ViewModel($tmplVars);
    }

    /**
     * add resource item
     * @return \Zend\View\Model\ViewModel
     */
    public function addresourceAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            'showForm' => true,
            )
        );
        $form = new AclresourceForm();

        $request = $this->getRequest();
        $Aclresource = new Aclresource();
        if ($request->isPost()) {
            $form->setInputFilter($Aclresource->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $Aclresource->exchangeArray($form->getData());
                $this->getAclresourceTable()->saveAclresource($Aclresource);
                
                $this->reinitACLCache();
                
                // Redirect to list of Acl
                $this->flashMessenger()->addSuccessMessage($this->translate("resource has been saved"));
                if ( $this->isXHR() ) {
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

    /**
     * edit resource item
     * @return \Zend\View\Model\ViewModel
     */
    public function editresourceAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            'showForm' => true,
            )
        );
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
        }
        try {
            $Aclresource = $this->getAclresourceTable()->getAclresource($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
        }

        $form  = new AclresourceForm();
        $form->bind($Aclresource);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($Aclresource->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getAclresourceTable()->saveAclresource($Aclresource);

                $this->reinitACLCache();
                
                // Redirect to list of Acl
                $this->flashMessenger()->addSuccessMessage($this->translate("resource has been saved"));
                if ( $this->isXHR() ) {
                    $tmplVars["showForm"] = false;
                } else {
                    return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
                }
            }
        } else {
            $form->bind($Aclresource);
        }
        $tmplVars["acl_id"] = $id;
        $tmplVars["aclresource"] = $Aclresource;
        $tmplVars["form"] = $form;
        $tmplVars["resources"] = $this->getAclresourceTable()->fetchAll();
        return new ViewModel($tmplVars);
    }

    /**
     * delete resource item
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteresourceAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'acldata' => $this->getAclTable()->fetchAll(),
            'roles' => $this->getAclroleTable()->fetchAll(),
            'resources' => $this->getAclresourceTable()->fetchAll(),
            'showForm' => true,
            )
        );
        $id = (int) $this->params()->fromRoute('acl_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
        }

        $tmplVars["acl_id"] = $id;
        try {
            $Aclresource = $this->getAclresourceTable()->getAclresource($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
        }
        $tmplVars["aclresource"] = $Aclresource;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
                $id = (int) $request->getPost('id');
                $this->getAclresourceTable()->deleteAclresource($id);
                $this->flashMessenger()->addSuccessMessage($this->translate("resource has been deleted"));

                $this->reinitACLCache();
            }
            
            // Redirect to list of albums
            if ( $this->isXHR() ) {
                $tmplVars["showForm"] = false;
            } else {
                return $this->redirect()->toRoute('admin/acledit', array('action' => 'resources'));
            }
        }

        $tmplVars["resources"] = $this->getAclresourceTable()->fetchAll();
        return new ViewModel($tmplVars);
    }
    
    /**
     * re-initialize, respectively clear ACL cache entries
     * @return self
     */
    private function reinitACLCache () {
        if ( \Application\Module::getService('CacheService') ) {
            \Application\Module::getService('CacheService')->removeItem('ACL');
            \Admin\Module::initACL($this->getServiceLocator());
        }
        return ($this);
    }

    
    // table getters
    
    /**
     * retrieve ACL entry table
     * @return Admin\Model\AclTable
     */
    public function getAclTable()
    {
        if (!$this->AclTable) {
            $sm = $this->getServiceLocator();
            $this->AclTable = $sm->get('AdminAclTable');
        }
        return $this->AclTable;
    }

    /**
     * retrieve role item table
     * @return Admin\Model\AclroleTable
     */
    public function getAclroleTable()
    {
        if (!$this->AclroleTable) {
            $sm = $this->getServiceLocator();
            $this->AclroleTable = $sm->get('AdminAclroleTable');
        }
        return $this->AclroleTable;
    }

    /**
     * retrieve resource item table
     * @return Admin\Model\AclresourceTable
     */
    public function getAclresourceTable()
    {
        if (!$this->AclresourceTable) {
            $sm = $this->getServiceLocator();
            $this->AclresourceTable = $sm->get('AdminAclresourceTable');
        }
        return $this->AclresourceTable;
    }
    
}
