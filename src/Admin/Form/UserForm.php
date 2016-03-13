<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels <development@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <development@bjoernbartels.earth>
 */

namespace Admin\Form;

use Zend\Form\Form;

class UserForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('user');
        
        $this->setAttribute('method', 'post');
        $this->add(
            array(
            'name' => 'user_id',
            'attributes' => array(
            'type'  => 'hidden',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'username',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'user name',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'email',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'email',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'display_name',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'display name',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'password',
            'attributes' => array(
            'type'  => 'password',
            ),
            'options' => array(
            'label' => 'password',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'state',
            'type' => 'select',
            'attributes' => array(
            'type'  => 'select',
            'options'    => array(
                    '1'    => 'active',
                    '0'    => 'inactive',
            ),
            ),
            'options' => array(
            'label' => 'status',
            ),
            )
        );
        
        $this->add(
            array(
            'name' => 'aclrole',
            'type' => 'select',
            'attributes' => array(
            'type'  => 'select',
            'options'    => array(
                    'public' => 'no user',
                    'user' => 'user',
                    'admin' => 'admin',
            ),
            ),
            'options' => array(
            'label' => 'role',
            ),
            )
        );
        
        
        $this->add(
            array(
            'name' => 'submit',
            'attributes' => array(
            'type'  => 'submit',
            'value' => 'save',
            'id' => 'submitbutton',
            ),
            'options' => array(
            'label' => 'save',
            ),
            )
        );
        
        $this->add(
            array(
            'name' => 'reset',
            'attributes' => array(
            'type'  => 'reset',
            'value' => 'reset',
            'id' => 'resetbutton',
            ),
            'options' => array(
            'label' => 'reset',
            ),
            )
        );
    }
}