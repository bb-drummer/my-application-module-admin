<?php
namespace Admin\Form;

use Zend\Form\Form;

class AclmatrixForm extends Form
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
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'aclresources_id',
            'attributes' => array(
                'type'  => 'hidden',
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