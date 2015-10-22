<?php
namespace Admin\Form;

use Zend\Form\Form;

class UserForm extends Form
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
            'name' => 'username',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Benutzername',
            ),
        ));
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'E-Mail',
            ),
        ));
        $this->add(array(
            'name' => 'display_name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Anzeigename',
            ),
        ));
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type'  => 'password',
            ),
            'options' => array(
                'label' => 'Passwort',
            ),
        ));
        $this->add(array(
            'name' => 'state',
        	'type' => 'select',
            'attributes' => array(
                'type'  => 'select',
            	'options'	=> array(
            		'1'	=> 'aktiv',
            		'0'	=> 'inaktiv',
            	),
            ),
            'options' => array(
                'label' => 'Status',
            ),
        ));
        $this->add(array(
            'name' => 'aclrole',
        	'type' => 'select',
            'attributes' => array(
                'type'  => 'select',
            	'options'	=> array(
            		'public'	=> 'keine Rolle',
            		'swimmer'	=> 'Schwimmer',
            		'manager'	=> 'Manager',
            		'admin'		=> 'Administrator',
            	),
            ),
            'options' => array(
                'label' => 'Rolle',
            ),
        ));
        

        $this->add(array(
        		'name' => 'street',
        		'attributes' => array(
        				'type'  => 'text',
        		),
        		'options' => array(
        				'label' => 'Strasse',
        		),
        ));
        $this->add(array(
            'name' => 'city',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array( 
                'label' => 'Ort',
            ),
        ));
        $this->add(array(
            'name' => 'phone',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Telefon',
            ),
        ));
        

        $this->add(array(
        		'name' => 'swimteam',
        		'attributes' => array(
        				'type'  => 'text',
        		),
        		'options' => array(
        				'label' => 'Schwimm-Team',
        		),
        ));
        $this->add(array(
            'name' => 'teamemail',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'E-Mail',
            ),
        ));
        $this->add(array(
            'name' => 'teamstreet',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Strasse',
            ),
        ));
        $this->add(array(
            'name' => 'teamcity',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Ort',
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