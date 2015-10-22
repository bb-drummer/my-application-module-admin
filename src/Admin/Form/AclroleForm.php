<?php
namespace Admin\Form;

use Zend\Form\Form;

class AclroleForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('aclrole');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'aclroles_id',
            'attributes' => array(
                'type'  => 'hidden' ,
            ),
        ));
        $this->add(array(
            'name' => 'roleslug',
        	'type' => 'text',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Rollen-Slug',
            ),
        ));
        $this->add(array(
            'name' => 'rolename',
        	'type' => 'text',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Rollen-Name',
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