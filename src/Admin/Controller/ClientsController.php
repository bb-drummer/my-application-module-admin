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
use Admin\Model\Clients;
use Admin\Form\ClientsForm;

class ClientsController extends BaseActionController
{
	
	/**
	 * @var \Admin\Model\ClientsTable
	 */
	protected $clientsTable;
    
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
                        'label'            => 'add client',
                        'icon'            => 'plus',
                        'class'            => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
                        'route'            => 'admin/clientsedit',
                        'action'        => 'add',
                        'resource'        => 'mvc:user',
                    ),
                ),
            )
        );
        $this->setActionTitles(
            array(
                'index' => $this->translate("manage clients"),
                'add' => $this->translate("add client"),
                'edit' => $this->translate("edit client"),
                'delete' => $this->translate("delete client"),
            )
        );
        return parent::onDispatch($e);
    }

    /**
     * list clients in a table
     * @return \Zend\View\Model\ViewModel
     */ 
    public function indexAction() 
    {
        $tmplVars = $this->getTemplateVars();
        $aClientslist = $this->getClientsTable()->fetchAll();
        if ( $this->isXHR() ) {
            $datatablesData = array('data' => $aClientslist->toArray());
            $oController = $this;
            $datatablesData['data'] = array_map(
                function ($row) use ($oController) {
                    $actions = '<div class="button-group tiny btn-group btn-group-xs">'.
                        '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                            'admin/clientsedit',
                            array('action'=>'edit', 'client_id' => $row["clients_id"])
                        ).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
                        '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                            'admin/clientsedit',
                            array('action'=>'delete', 'client_id' => $row["clients_id"])
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
            'clientsdata' => $aClientslist,
            )
        );
    }
    
    /**
     * add client entry
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'showForm'    => true,
            'title'        => $this->translate("add client")
            )
        );
        $this->layout()->setVariable('title', $this->translate("add client"));
        
        $form = new ClientsForm();

        $request = $this->getRequest();
        $clients = new Clients();
        if ($request->isPost()) {
            $form->setInputFilter($clients->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $clients->exchangeArray($form->getData());
                $this->getClientsTable()->saveClients($clients);
                $this->flashMessenger()->addSuccessMessage($this->translate('client has been saved'));
                if ( $this->isXHR() ) {
                    $tmplVars["showForm"] = false;
                } else {
                    return $this->redirect()->toRoute('admin/clientsedit', array('action' => 'index'));
                }
            }
            $tmplVars["clients"] = $clients;
        }
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    /**
     * edit client entry
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'showForm'    => true,
            'title'        => $this->translate("edit client")
            )
        );
        $this->layout()->setVariable('title', $this->translate("edit client"));
        $id = (int) $this->params()->fromRoute('client_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute(
                'admin/clientsedit', array(
                'action' => 'index'
                )
            );
        }
        try {
            $clients = $this->getClientsTable()->getClients($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/clientsedit');
        }

        $form  = new ClientsForm();
        $form->bind($clients);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($clients->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getClientsTable()->saveClients($clients);
                $this->flashMessenger()->addSuccessMessage($this->translate("client has been saved"));
                if ( $this->isXHR() ) {
                    $tmplVars["showForm"] = false;
                } else {
                    return $this->redirect()->toRoute('admin/clientsedit', array('action' => 'index'));
                }
            }
        } else {
            $form->bind($clients);
        }
        $tmplVars["clients_id"] = $id;
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    /**
     * delete client entry
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'showForm'    => true,
            'title'        => $this->translate("delete client")
            )
        );
        $this->layout()->setVariable('title', $this->translate("delete client"));
        $id = (int) $this->params()->fromRoute('client_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/clientsedit', array('action' => 'index'));
        }

        $tmplVars["clients_id"] = $id;
        try {
            $clients = $this->getClientsTable()->getClients($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/clientsedit');
        }
        $tmplVars["clients"] = $clients;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
                $id = (int) $request->getPost('id');
                $this->getClientsTable()->deleteClients($id);
                $this->flashMessenger()->addSuccessMessage($this->translate("client has been deleted"));
                if ( $this->isXHR() ) {
                    $tmplVars["showForm"] = false;
                } else {
                    return $this->redirect()->toRoute('admin/clientsedit', array('action' => 'index'));
                }
            }
        }

        return new ViewModel($tmplVars);
    }

    /**
     * retrieve client entry table
     * @return Admin\Model\ClientsTable
     */
    public function getClientsTable()
    {
        if (!$this->clientsTable) {
            $sm = $this->getServiceLocator();
            $this->clientsTable = $sm->get('AdminClientsTable');
        }
        return $this->clientsTable;
    }
    
}