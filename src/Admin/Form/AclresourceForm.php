<?php
namespace Admin\Form;

use Zend\Form\Form;

class AclresourceForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('aclresource');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'aclresources_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'resourceslug',
        	'type' => 'text',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'resource slug',
            ),
        ));
        $this->add(array(
            'name' => 'resourcename',
        	'type' => 'text',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'resource name',
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
    }
}