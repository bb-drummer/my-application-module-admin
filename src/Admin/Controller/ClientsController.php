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
use Admin\Model\Clients;
use Admin\Form\ClientsForm;

class ClientsController extends BaseActionController 
{
	protected $clientsTable;
	
	public function onDispatch(\Zend\Mvc\MvcEvent $e)
	{
		$this->setActionTitles(array(
			'index' => $this->translate("manage clients"),
			'add' => $this->translate("add client"),
			'edit' => $this->translate("edit client"),
			'delete' => $this->translate("delete client"),
		));
		return parent::onDispatch($e);
	}

	public function indexAction() 
	{
		$tmplVars = $this->getTemplateVars();
		$aClientslist = $this->getClientsTable()->fetchAll();
		if ( $this->getRequest()->isXmlHttpRequest() ) {
			$datatablesData = array('data' => $aClientslist->toArray());
			$oController = $this;
			$datatablesData['data'] = array_map( function ($row) use ($oController) {
				$actions = '<div class="btn-group btn-group-xs">'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/clientsedit',
							array('action'=>'edit', 'client_id' => $row["clients_id"])).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/clientsedit',
							array('action'=>'delete', 'client_id' => $row["clients_id"])).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'.
				'</div>';
				$row["_actions_"] = $actions;
				return $row;
			}, $datatablesData['data'] );
			return $this->getResponse()->setContent(json_encode($datatablesData));
		}
		return new ViewModel(array(
			'clientsdata' => $aClientslist,
		));
	}
	
	public function addAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'showForm'	=> true,
				'title'		=> $this->translate("add client")
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
				if ( $this->getRequest()->isXmlHttpRequest() ) {
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

	public function editAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'showForm'	=> true,
				'title'		=> $this->translate("edit client")
			)
		);
		$this->layout()->setVariable('title', $this->translate("edit client"));
		$id = (int) $this->params()->fromRoute('client_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/clientsedit', array(
				'action' => 'index'
			));
		}
		$clients = $this->getClientsTable()->getClients($id);

		$form  = new ClientsForm();
		$form->bind($clients);

		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setInputFilter($clients->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getClientsTable()->saveClients($clients);
				$this->flashMessenger()->addSuccessMessage($this->translate("client has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
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

	public function deleteAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'showForm'	=> true,
				'title'		=> $this->translate("delete client")
			)
		);
		$this->layout()->setVariable('title', $this->translate("delete client"));
		$id = (int) $this->params()->fromRoute('client_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/clientsedit', array('action' => 'index'));
		}

		$tmplVars["clients_id"] = $id;
		$tmplVars["clients"] = $this->getClientsTable()->getClients($id);
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', '');

			if (!empty($del)) {
				$id = (int) $request->getPost('id');
				$this->getClientsTable()->deleteClients($id);
				$this->flashMessenger()->addSuccessMessage($this->translate("client has been deleted"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/clientsedit', array('action' => 'index'));
				}
			}
		}

		return new ViewModel($tmplVars);
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