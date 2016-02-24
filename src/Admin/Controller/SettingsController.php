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
use Admin\Model\Settings;
use Admin\Form\SettingsForm;

class SettingsController extends BaseActionController 
{
	protected $settingsTable;
	
	public function onDispatch(\Zend\Mvc\MvcEvent $e)
	{
		$this->setToolbarItems(array(
			"index" => array(
				array(
					'label'			=> 'add setting',
					'icon'			=> 'plus',
					'class'			=> 'btn btn-default btn-sm btn-cta-xhr cta-xhr-modal',
					'route'			=> 'admin/settingsedit',
					'action'		=> 'add',
					'resource'		=> 'mvc:user',
				),
			),
		));
		$this->setActionTitles(array(
			'index' => $this->translate("manage settings"),
			'add' => $this->translate("add setting"),
			'edit' => $this->translate("edit setting"),
			'delete' => $this->translate("delete setting"),
		));
		return parent::onDispatch($e);
	}

	public function indexAction() 
	{
		$tmplVars = $this->getTemplateVars();
		$aSettingslist = $this->getSettingsTable()->fetchAll();
		if ( $this->getRequest()->isXmlHttpRequest() ) {
			$datatablesData = array('data' => $aSettingslist->toArray());
			$oController = $this;
			$datatablesData['data'] = array_map( function ($row) use ($oController) {
				$actions = '<div class="btn-group btn-group-xs">'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/settingsedit',
							array('action'=>'edit', 'set_id' => $row["settings_id"])).'"><span class="fa fa-pencil"></span> '.$oController->translate("edit").'</a>'.
					'<a class="btn btn-default btn-xs btn-clean btn-cta-xhr cta-xhr-modal" href="'.$oController->url()->fromRoute('admin/settingsedit',
							array('action'=>'delete', 'set_id' => $row["settings_id"])).'"><span class="fa fa-trash-o"></span> '.$oController->translate("delete").'</a>'.
				'</div>';
				$row["_actions_"] = $actions;
				return $row;
			}, $datatablesData['data'] );
			return $this->getResponse()->setContent(json_encode($datatablesData));
		}
		return new ViewModel(array(
			'settingsdata' => $aSettingslist,
		));
	}
	
	public function addAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'showForm'	=> true,
				'title'		=> $this->translate("add setting")
			)
		);
		$this->layout()->setVariable('title', $this->translate("add setting"));
		
		$form = new SettingsForm();

		$request = $this->getRequest();
		$settings = new Settings();
		if ($request->isPost()) {
			$form->setInputFilter($settings->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$settings->exchangeArray($form->getData());
				$this->getSettingsTable()->saveSettings($settings);
				$this->flashMessenger()->addSuccessMessage($this->translate('setting has been saved'));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
				}
			}
			$tmplVars["settings"] = $settings;
		}
		$tmplVars["form"] = $form;
		return new ViewModel($tmplVars);
	}

	public function editAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'showForm'	=> true,
				'title'		=> $this->translate("edit setting")
			)
		);
		$this->layout()->setVariable('title', $this->translate("edit setting"));
		$id = (int) $this->params()->fromRoute('set_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/settingsedit', array(
				'action' => 'index'
			));
		}
		$settings = $this->getSettingsTable()->getSettings($id);

		$form  = new SettingsForm();
		$form->bind($settings);

		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setInputFilter($settings->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getSettingsTable()->saveSettings($settings);
				$this->flashMessenger()->addSuccessMessage($this->translate("setting has been saved"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
				}
			}
		} else {
	   		$form->bind($settings);
		}
		$tmplVars["settings_id"] = $id;
		$tmplVars["form"] = $form;
		return new ViewModel($tmplVars);
	}

	public function deleteAction()
	{
		$tmplVars = $this->getTemplateVars( 
			array(
				'showForm'	=> true,
				'title'		=> $this->translate("delete setting")
			)
		);
		$this->layout()->setVariable('title', $this->translate("delete setting"));
		$id = (int) $this->params()->fromRoute('set_id', 0);
		if (!$id) {
			$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
			return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
		}

		$tmplVars["settings_id"] = $id;
		$tmplVars["settings"] = $this->getSettingsTable()->getSettings($id);
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', '');

			if (!empty($del)) {
				$id = (int) $request->getPost('id');
				$this->getSettingsTable()->deleteSettings($id);
				$this->flashMessenger()->addSuccessMessage($this->translate("setting has been deleted"));
				if ( $this->getRequest()->isXmlHttpRequest() ) {
					$tmplVars["showForm"] = false;
				} else {
					return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
				}
			}
		}

		return new ViewModel($tmplVars);
	}

	/**
	 * @return Admin\Model\SettingsTable
	 */
	public function getSettingsTable()
	{
		if (!$this->settingsTable) {
			$sm = $this->getServiceLocator();
			$this->settingsTable = $sm->get('AdminSettingsTable');
		}
		return $this->settingsTable;
	}
	
}