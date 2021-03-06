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
use Admin\Model\Applications;
use Admin\Form\ApplicationsForm;

class ApplicationsController extends BaseActionController
{
	
	/**
	 * @var array|\Admin\Model\ClientsTable
	 */
	protected $clientsTable;
	
	/**
	 * @var array|\Admin\Model\ApplicationsTable
	 */
	protected $applicationsTable;
    
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
            'label'            => 'add application',
            'icon'            => 'plus',
            'class'            => 'button btn btn-default small btn-sm btn-cta-xhr cta-xhr-modal',
            'route'            => 'admin/applicationsedit',
            'action'        => 'add',
            'resource'        => 'mvc:user',
            ),
            ),
            )
        );
        $this->setActionTitles(
            array(
            'index' => $this->translate("manage applications"),
            'add' => $this->translate("add application"),
            'edit' => $this->translate("edit application"),
            'delete' => $this->translate("delete application"),
            )
        );
        return parent::onDispatch($e);
    }

    /**
     * list applications in a table
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */ 
    public function indexAction() 
    {
        $oController = $this;
        $tmplVars = $this->getTemplateVars();
        /**
         * @var \Zend\Db\Adapter\Driver\Pdo\Result $aApplicationslist 
         */
        $aApplicationslist = $this->getApplicationsTable()->fetchAllFull();

        $data = array();
        $dataObj = array();
        foreach ($aApplicationslist as $row) {
            $data[] = $row;
            $dataObj[] = (object)$row;
        }
        if ( $this->isXHR() ) {
            $datatablesData = array('data' => $data);
            $datatablesData['data'] = array_map(
                function ($row) use ($oController) {
                    $actions = '<div class="button-group tiny btn-group btn-group-xs">'.
                    '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                        'admin/applicationsedit',
                        array('action'=>'edit', 'application_id' => $row["application_id"])
                    ).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
                    '<a class="button btn btn-default tiny btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute(
                        'admin/applicationsedit',
                        array('action'=>'delete', 'application_id' => $row["application_id"])
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
            'applicationsdata' => $dataObj,
            )
        );
    }
    
    /**
     * add application entry
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'showForm'    => true,
            )
        );
        
        $form = new ApplicationsForm();
        
        $clients = $this->getClientsTable()->fetchAll()->toArray();
        $valueoptions = array();
        foreach ($clients as $client) {
            $valueoptions[$client["clients_id"]] = $client["name"];
        }
        $form->get('client_id')->setValueOptions($valueoptions);
        
        $request = $this->getRequest();
        $applications = new Applications();
        if ($request->isPost()) {
            $form->setInputFilter($applications->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $applications->exchangeArray($form->getData());
                $this->getApplicationsTable()->saveApplication($applications);
                $this->flashMessenger()->addSuccessMessage($this->translate('application has been saved'));
                if ( $this->isXHR() ) {
                    $tmplVars["showForm"] = false;
                } else {
                    return $this->redirect()->toRoute('admin/applicationsedit', array('action' => 'index'));
                }
            }
            $tmplVars["applications"] = $applications;
        }
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    /**
     * edit application entry
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'showForm'    => true,
            )
        );
        
        $id = (int) $this->params()->fromRoute('application_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute(
                'admin/applicationsedit', array(
                'action' => 'index'
                )
            );
        }
        try {
            $applications = $this->getApplicationsTable()->getApplication($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/applicationsedit');
        }

        $form  = new ApplicationsForm();
        $form->bind($applications);

        $clients = $this->getClientsTable()->fetchAll()->toArray();
        $valueoptions = array();
        foreach ($clients as $client) {
            $valueoptions[$client["clients_id"]] = $client["name"];
        }
        $form->get('client_id')->setValueOptions($valueoptions);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($applications->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getApplicationsTable()->saveApplication($applications);
                $this->flashMessenger()->addSuccessMessage($this->translate("application has been saved"));
                if ( $this->isXHR() ) {
                    $tmplVars["showForm"] = false;
                } else {
                    return $this->redirect()->toRoute('admin/applicationsedit', array('action' => 'index'));
                }
            }
        } else {
            $form->bind($applications);
        }
        $tmplVars["application_id"] = $id;
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    /**
     * delete application entry
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $tmplVars = $this->getTemplateVars( 
            array(
            'showForm'    => true,
            )
        );
        
        $id = (int) $this->params()->fromRoute('application_id', 0);
        if (!$id) {
            $this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/applicationsedit', array('action' => 'index'));
        }

        $tmplVars["application_id"] = $id;
        try {
            $applications = $this->getApplicationsTable()->getApplication($id);
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage($this->translate("invalid parameters"));
            return $this->redirect()->toRoute('admin/applicationsedit');
        }
        $tmplVars["applications"] = $applications;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
                $id = (int) $request->getPost('id');
                $this->getApplicationsTable()->deleteApplication($id);
                $this->flashMessenger()->addSuccessMessage($this->translate("application has been deleted"));
                if ( $this->isXHR() ) {
                    $tmplVars["showForm"] = false;
                } else {
                    return $this->redirect()->toRoute('admin/applicationsedit', array('action' => 'index'));
                }
            }
        }

        return new ViewModel($tmplVars);
    }

    /**
     * retrieve application entry table
     * @return array|\Admin\Model\ApplicationsTable
     */
    public function getApplicationsTable()
    {
        if (!$this->applicationsTable) {
            $sm = $this->getServiceLocator();
            $this->applicationsTable = $sm->get('AdminApplicationsTable');
        }
        return $this->applicationsTable;
    }

    /**
     * retrieve client entry table
     * @return array|\Admin\Model\ClientsTable
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