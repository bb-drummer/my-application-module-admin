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

/**
 * SettingsController
 *
 * @author
 *
 * @version
 *
 */
class SettingsController extends BaseActionController {
	
	protected $settingsTable;
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() 
	{
		$tmplVars = $this->getTemplateVars();
		
        return new ViewModel(array(
            'settingsdata' => $this->getSettingsTable()->fetchAll(),
        ));
	}
	
    public function addAction()
    {
        $tmplVars = $this->getTemplateVars();
        
        //if (!class_exists('\Admin\Form\SettingsForm')) { require_once __DIR__ . '/../Form/SettingsForm.php'; }
        $form = new SettingsForm();

        $request = $this->getRequest();
        $settings = new Settings();
        if ($request->isPost()) {
            $form->setInputFilter($settings->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $settings->exchangeArray($form->getData());
                $this->getSettingsTable()->saveSettings($settings);
                // Redirect to list of settings
        		$this->flashMessenger()->addSuccessMessage($this->translate('setting has been saved'));
                return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
            }
	        $tmplVars["settings"] = $settings;
        }
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    public function editAction()
    {
		$tmplVars = $this->getTemplateVars();
        $id = (int) $this->params()->fromRoute('set_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/settingsedit', array(
                'action' => 'add'
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

                // Redirect to list of settings
        		$this->flashMessenger()->addSuccessMessage($this->translate("setting has been saved"));
                return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
            }
        } else {
       		$form->bind($settings); //->getArrayCopy());
        }
        $tmplVars["settings_id"] = $id;
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    public function deleteAction()
    {
    	$tmplVars = $this->getTemplateVars();
        $id = (int) $this->params()->fromRoute('set_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage($this->translate("missing parameters"));
            return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
                $id = (int) $request->getPost('id');
                $this->getSettingsTable()->deleteSettings($id);
        		$this->flashMessenger()->addSuccessMessage($this->translate("setting has been deleted"));
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
        }

        $tmplVars["settings_id"] = $id;
        $tmplVars["settings"] = $this->getSettingsTable()->getSettings($id);
        return new ViewModel($tmplVars);
    }

    public function getSettingsTable()
    {
        if (!$this->settingsTable) {
            $sm = $this->getServiceLocator();
            $this->settingsTable = $sm->get('Admin\Model\SettingsTable');
        }
        return $this->settingsTable;
    }
    
}