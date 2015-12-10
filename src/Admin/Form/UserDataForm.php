<?php
namespace Admin\Form;

use Zend\Form\Form;

class UserDataForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('user');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'user_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'display_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'display name',
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