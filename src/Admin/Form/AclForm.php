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
                'label' => 'role',
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
                'label' => 'resource',
            ),
        ));
        $this->add(array(
            'name' => 'state',
        	'type' => 'select',
            'attributes' => array(
                'type'  => 'select',
            	'options'	=> array(
            		''	=> '---',
            		'allow'	=> 'allow',
            		'deny'	=> 'deny',
            	),
            ),
            'options' => array(
                'label' => 'state',
            ),
        ));

        $this->add(array(
        	'name' => 'reset',
        	'attributes' => array(
        		'type'  => 'reset',
        		'value' => 'reset',
        		'id' => 'resetbutton',
        	),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'save',
                'id' => 'submitbutton',
            ),
        ));
    }
}