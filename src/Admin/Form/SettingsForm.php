<?php
namespace Admin\Form;

use Zend\Form\Form;
use Admin\Module;

class SettingsForm extends Form
{
    public function __construct($name = null)
    {
    	
        // we want to ignore the name passed
        parent::__construct('settings');
    	$oModule = new Module();
    	$cfg = $oModule->getConfig();
    	
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'settings_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'type',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Type',
            ),
        ));
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));
        $this->add(array(
            'name' => 'value',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Wert',
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'absenden',
                'id' => 'submitbutton',
            ),
        ));
        
        $this->add(array(
            'name' => 'reset',
            'attributes' => array(
                'type'  => 'reset',
                'value' => 'zurücksetzen',
                'id' => 'resetbutton',
            ),
        ));
    }
}