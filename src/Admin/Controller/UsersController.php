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

use Application\Controller\BaseActionController;
use Zend\View\Model\ViewModel;
use Admin\Module as AdminModule;
use Admin\Model\User;
use Admin\Form\UserForm;
use Admin;
use Admin\Model\AclroleTable;

class UsersController extends BaseActionController
{
	
	/**
	 * @var array|\Admin\Model\UserTable
	 */
	protected $userTable;
	
	/**
	 * @var array|\Admin\Model\AclroleTable
	 */
	protected $AclroleTable;

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
            'label'            => 'add user',
            'icon'            => 'plus',
            'class'            => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
            'route'            => 'admin/default',
            'controller'    => 'users',
            'action'        => 'add',
            'resource'        => 'mvc:user',
            ),
            ),
            )
        );
        $this->setActionTitles(
            array(
            'index' => $this->translate("manage users"),
            'add' => $this->translate("add user"),
            'edit' => $this->translate("edit user"),
            'delete' => $this->translate("delete user"),
            )
        );
        return parent::onDispatch($e);
    }

    /**
     * list users in a table
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */ 
    public function indexAction()
    {
        $tmplVars = $this->getTemplateVars();
        $aUserlist = $this->getUserTable()->fetchAll();
        if ( $this->isXHR() ) {
            $datatablesData = array('data' => $aUserlist->toArray());
            $oController = $this;
            $datatablesData['data'] = array_map(
                function ($row) use ($oController) {
                    $actions = '<div class="button-group tiny btn-group btn-group-xs">'.
                    '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                        'admin/default',
                        array('controller'=>'users', 'action'=>'edit', 'user_id' => $row["user_id"])
                    ).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
                    '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                        'admin/default',
                        array('controller'=>'users', 'action'=>'delete', 'user_id' => $row["user_id"])
                    ).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'.
                    '</div>';
                    $row["password"] = "*********";
                    $row["_actions_"] = $actions;
                    return $row;
                }, $datatablesData['data'] 
            );
            return $this->getResponse()->setContent(json_encode($datatablesData));
        }
        return new ViewModel(
            array(
                'userdata' => $aUserlist,
            )
        );
    }

    /**
     * add user entry
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
                'showForm'    => true,
            )
        );
        $form = new UserForm();

        $roles = $this->getAclroleTable()->fetchAll()->toArray();
        $valueoptions = array();
        foreach ($roles as $role) {
            $valueoptions[$role["roleslug"]] = $role["rolename"];
        }
        /** @var \Zend\Form\Element\Select $aclroleSelect */
        $aclroleSelect = $form->get('aclrole');
        $aclroleSelect->setValueOptions($valueoptions);
        
        $request = $this->getRequest();
        $user = new User();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $this->getUserTable()->saveUser($user);
                $this->flashMessenger()->addSuccessMessage($this->translate("user has been saved"));
                if ( $this->isXHR() ) {
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

    /**
     * edit user entry
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
                'showForm'    => true,
            )
        );
        $id = (int) $this->params()->fromRoute('user_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/default', array('controller' => 'users'));
        }
        try {
            $user = $this->getUserTable()->getUser($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/default', array('controller' => 'users'));
        }

        $form    = new UserForm();
        $form->bind($user);

        $roles = $this->getAclroleTable()->fetchAll()->toArray();
        $valueoptions = array();
        foreach ($roles as $role) {
            $valueoptions[$role["roleslug"]] = $role["rolename"];
        }
        $form->get('aclrole')->setValueOptions($valueoptions);
                
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getUserTable()->saveUser($user);
                $this->flashMessenger()->addSuccessMessage($this->translate("user has been saved"));
                if ( $this->isXHR() ) {
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

    /**
     * delete user entry
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
                'showForm'    => true,
            )
        );
        $id = (int) $this->params()->fromRoute('user_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/default', array('controller' => 'users'));
        }

        $tmplVars["user_id"] = $id;
        try {
            $user = $this->getUserTable()->getUser($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/default', array('controller' => 'users'));
        }
        $tmplVars["user"] = $user;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
                $id = (int) $request->getPost('id');
                $this->getUserTable()->deleteUser($id);
                $this->flashMessenger()->addSuccessMessage($this->translate("user has been deleted"));
                if ( $this->isXHR() ) {
                    $tmplVars["showForm"] = false;
                } else {
                    return $this->redirect()->toRoute('admin/default', array('controller' => 'users'));
                }
            }

        }
        return new ViewModel($tmplVars);
    }

    /**
     * confirm user registration
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function confirmAction()
    {
        $tmplVars = array_merge( 
            $this->params()->fromRoute(), 
            $this->params()->fromPost(),
            array()
        );
        $config = $this->getServiceLocator()->get('Config');
        $users = $this->getServiceLocator()->get('zfcuser_user_mapper');
        
        $user_id    = $this->params()->fromRoute('user_id', '');
        $token        = $this->params()->fromRoute('confirmtoken', '');
        if (empty($user_id) || empty($token)) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }
        
        if (is_numeric($user_id) ) {
            $oUser = $users->findById($user_id);
        } else {
            $oUser = $users->findByUsername($user_id);
        }
        if (!$oUser ) {
            $this->flashMessenger()->addWarningMessage($this->translate("user could not be found"));
            return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }
        if (($oUser->getState() != 0) || ($oUser->getToken() != $token) ) {
            $this->flashMessenger()->addWarningMessage($this->translate("confirmation token is invalid"));
            return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }
        
        // all ok, do stuff...
        $oModule = new AdminModule();
        $oModule->setAppConfig($config);
        $this->getUserTable()->getTableGateway()->update(
            array(
            "state"        => ($config["zfcuser_admin_must_activate"]) ? "0" : "1",
            "token"        => $oModule->createUserToken($oUser),
            ), array(
            "user_id"    => $oUser->getId(),
            )
        );
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

    /**
     * active user registration
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function activateAction()
    {
        $tmplVars = array_merge( 
            $this->params()->fromRoute(), 
            $this->params()->fromPost(),
            array()
        );
        $config    = $this->getServiceLocator()->get('Config');
        $users    = $this->getServiceLocator()->get('zfcuser_user_mapper');
        
        $user_id    = $this->params()->fromRoute('user_id', '');
        $token        = $this->params()->fromRoute('activatetoken', '');
        if (empty($user_id) || empty($token)) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }

        if (is_numeric($user_id) ) {
            $oUser = $users->findById($user_id);
        } else {
            $oUser = $users->findByUsername($user_id);
        }
        if (!$oUser ) {
            $this->flashMessenger()->addWarningMessage($this->translate("user could not be found"));
            return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }
        if (($oUser->getState() != 0) || ($oUser->getToken() != $token) ) {
            $this->flashMessenger()->addWarningMessage($this->translate("activation token is invalid"));
            return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        }
        
        // all ok, do stuff...
        $oModule = new AdminModule();
        $oModule->setAppConfig($config);
        $this->getUserTable()->getTableGateway()->update(
            array(
                "state"        => "1",
                "token"        => $user->token,
            ), array(
                "user_id"    => $oUser->getId(),
            )
        );
        $oUser = $users->findById($user_id);
        $this->flashMessenger()->addSuccessMessage($this->translate("user has been activated"));
        $oModule->sendActivationNotificationMail($oUser);
        
        return $this->redirect()->toRoute($config["zfcuser_registration_redirect_route"], array());
        
    }

    /**
     * retrieve user data table
     * @return array|\Admin\Model\UserTable
     */
    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('AdminUserTable');
        }
        return $this->userTable;
    }
    
    /**
     * retrieve role item table
     * @return array|\Admin\Model\AclroleTable
     */
    public function getAclroleTable()
    {
        if (!$this->AclroleTable) {
            $sm = $this->getServiceLocator();
            $this->AclroleTable = $sm->get('AdminAclroleTable');
        }
        return $this->AclroleTable;
    }

}
