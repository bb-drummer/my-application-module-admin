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
use Admin\Model\Applications;
use Admin\Form\ApplicationsForm;

class ApplicationsController extends BaseActionController 
{
	protected $clientsTable;
	protected $applicationsTable;
	
	public function onDispatch(\Zend\Mvc\MvcEvent $e)
	{
		$this->setToolbarItems(array(
			"index" => array(
				array(
					'label'			=> 'add application',
					'icon'			=> 'plus',
					'class'			=> 'btn btn-default btn-sm btn-cta-xhr cta-xhr-modal',
					'route'			=> 'admin/applicationsedit',
					'action'		=> 'add',
					'resource'		=> 'mvc:user',
				),
			),
		));
		$this->setActionTitles(array(
			'index' => $this->translate("manage applications"),
			'add' => $this->translate("add application"),
			'edit' => $this->translate("edit application"),
			'delete' => $this->translate("delete application"),
		));
		return parent::onDispatch($e);
	}

	public function indexAction() 
	{
		$oController = $this;
		$tmplVars = $this->getTemplateVars();
		/** @var \Zend\Db\Adapter\Driver\Pdo\Result $aApplicationslist */
		$aApplicationslist = $this->getApplicationsTable()->fetchAllFull();

		$data = array();
		$dataObj = array();
		foreach ($aApplicationslist as $row) {
			$data[] = $row;
			$dataObj[] = (object)$row;
		}
		if ( $this->getRequest()->isXmlHttpRequest() ) {
			$datatablesData = array('data' => $data);
			$datatablesData['data'] = array_map( function ($row) use ($oController) {
				$actions = '<div class="btn-group btn-group-xs">'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/applicationsedit',
							array('action'=>'edit', 'application_id' => $row["application_id"])).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/applicationsedit',
							array('action'=>'delete', 'application_id' => $row["application_id"])).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'.
				'</div>';
				$row["_actions_"] = $actions;
				return $row;
			}, $datatablesData['data'] );
			return $this->getResponse()->setContent(json_encode($datatablesData));
		}
		return new ViewModel(array(
			'applicationsdata' => $dataObj,
		));
	}
	
	public function addAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'showForm'	=> true,
				'title'		=> $this->translate("add application")
			)
		);
		$this->layout()->setVariable('title', $this->translate("add application"));
		
		$form = new ApplicationsForm();
		
		$clients = $this->getClientsTable()->fetchAll()->toArray();
		$valueoptions = array();
		foreach ($clients as $client) {
			//$valueoptions[$role["aclroles_id"]] = $role["rolename"];
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
				if ( $this->getRequest()->isXmlHttpRequest() ) {
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

	public function editAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'showForm'	=> true,
				'title'		=> $this->translate("edit application")
			)
		);
		$this->layout()->setVariable('title', $this->translate("edit application"));
		$id = (int) $this->params()->fromRoute('application_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/applicationsedit', array(
				'action' => 'index'
			));
		}
		$applications = $this->getApplicationsTable()->getApplication($id);

		$form  = new ApplicationsForm();
		$form->bind($applications);

		$clients = $this->getClientsTable()->fetchAll()->toArray();
		$valueoptions = array();
		foreach ($clients as $client) {
			//$valueoptions[$role["aclroles_id"]] = $role["rolename"];
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
				if ( $this->getRequest()->isXmlHttpRequest() ) {
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

	public function deleteAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'showForm'	=> true,
				'title'		=> $this->translate("delete application")
			)
		);
		$this->layout()->setVariable('title', $this->translate("delete application"));
		$id = (int) $this->params()->fromRoute('application_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/applicationsedit', array('action' => 'index'));
		}

		$tmplVars["application_id"] = $id;
		$app = $this->getApplicationsTable()->getApplication($id);
		$app->setServiceLocator($this->getServiceLocator());
		$tmplVars["applications"] = $app;
		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', '');

			if (!empty($del)) {
				$id = (int) $request->getPost('id');
				$this->getApplicationsTable()->deleteApplication($id);
				$this->flashMessenger()->addSuccessMessage($this->translate("application has been deleted"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/applicationsedit', array('action' => 'index'));
				}
			}
		}

		return new ViewModel($tmplVars);
	}

	/**
	 * @return Admin\Model\ApplicationsTable
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