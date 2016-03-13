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

class UserDataForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('userdata');
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
            'name' => 'display_name',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'Display Name',
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