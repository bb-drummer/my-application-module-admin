<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels <coding@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */

namespace Admin\Form;

use Zend\Form\Form;

class UserProfileForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('userprofile');
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
            'name' => 'street',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'street',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'city',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'city',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'phone',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'phone',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'cell',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'mobile',
            ),
            )
        );
        

        $this->add(
            array(
            'name' => 'facebook',
            'attributes' => array(
                   'type'  => 'url',
               ),
               'options' => array(
                   'label' => 'Facebook',
               ),
            )
        );
        $this->add(
            array(
            'name' => 'twitter',
            'attributes' => array(
            'type'  => 'url',
            ),
            'options' => array(
            'label' => 'Twitter',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'skype',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'Skype',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'icq',
            'attributes' => array(
            'type'  => 'number',
            ),
            'options' => array(
            'label' => 'ICQ',
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