<?php
namespace Admin\Form;

use Zend\Form\Form;

class AclForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('acl');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'acl_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'aclroles_id',
        	'type' => 'select',
            'attributes' => array(
                'type'  => 'select',
            	'options'	=> array(
            	),
            ),
            'options' => array(
                'label' => 'Rolle',
            ),
        ));
        $this->add(array(
            'name' => 'aclresources_id',
        	'type' => 'select',
            'attributes' => array(
                'type'  => 'select',
            	'options'	=> array(
            	),
            ),
            'options' => array(
                'label' => 'Resource',
            ),
        ));
        $this->add(array(
            'name' => 'state',
        	'type' => 'select',
            'attributes' => array(
                'type'  => 'select',
            	'options'	=> array(
            		''	=> '---',
            		'allow'	=> 'erlauben',
            		'deny'	=> 'verbieten',
            	),
            ),
            'options' => array(
                'label' => 'Status',
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
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'absenden',
                'id' => 'submitbutton',
            ),
        ));
    }
}