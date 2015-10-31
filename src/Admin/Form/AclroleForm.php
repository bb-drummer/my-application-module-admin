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
                'label' => 'role slug',
            ),
        ));
        $this->add(array(
            'name' => 'rolename',
        	'type' => 'text',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'role name',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'save',
                'id' => 'submitbutton',
            ),
            'options' => array(
                'label' => 'save',
            ),
        ));

        $this->add(array(
        	'name' => 'reset',
        	'attributes' => array(
       			'type'  => 'reset',
      			'value' => 'reset',
      			'id' => 'resetbutton',
       		),
            'options' => array(
                'label' => 'reset',
            ),
        ));
    }
}