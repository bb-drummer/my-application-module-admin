<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
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
        return new ViewModel(array(
            'settingsdata' => $this->getSettingsTable()->fetchAll(),
        ));
	}
	
    public function addAction()
    {
        $tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array()
		);
        //if (!class_exists('\Admin\Form\SettingsForm')) { require_once __DIR__ . '/../Form/SettingsForm.php'; }
        $form = new SettingsForm();
        $form->get('submit')->setValue('Einstellung anlegen');

        $request = $this->getRequest();
        $settings = new Settings();
        if ($request->isPost()) {
            $form->setInputFilter($settings->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $settings->exchangeArray($form->getData());
                $this->getSettingsTable()->saveSettings($settings);
                // Redirect to list of settings
        		$this->flashMessenger()->addSuccessMessage('Einstellung wurde gespeichert.');
                return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
            }
	        $tmplVars["settings"] = $settings;
        }
        $tmplVars["form"] = $form;
        return new ViewModel($tmplVars);
    }

    public function editAction()
    {
		$tmplVars = array_merge( 
			$this->params()->fromRoute(), 
			$this->params()->fromPost(),
			array()
		);
        $id = (int) $this->params()->fromRoute('set_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/settingsedit', array(
                'action' => 'add'
            ));
        }
        $settings = $this->getSettingsTable()->getSettings($id);

        $form  = new SettingsForm();
        $form->bind($settings);
        $form->get('submit')->setValue('Einstellung speichern');
        $form->get('submit')->setAttribute('value', 'Einstellung speichern');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($settings->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getSettingsTable()->saveSettings($settings);

                // Redirect to list of settings
        		$this->flashMessenger()->addSuccessMessage("Einstellung wurde gespeichert.");
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
        $id = (int) $this->params()->fromRoute('set_id', 0);
        if (!$id) {
        	$this->flashMessenger()->addWarningMessage("Fehlende Parameter");
            return $this->redirect()->toRoute('admin/settingsedit', array('action' => 'index'));
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', '');

            if (!empty($del)) {
                $id = (int) $request->getPost('id');
                $this->getSettingsTable()->deleteSettings($id);
        		$this->flashMessenger()->addSuccessMessage("Einstellung wurde entfernt.");
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