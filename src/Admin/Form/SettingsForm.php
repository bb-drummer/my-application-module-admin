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
use Admin\Module;

class SettingsForm extends Form
{
    public function __construct($name = null)
    {
        
        // we want to ignore the name passed
        parent::__construct('settings');
        $oModule = new Module();
        $cfg = $oModule->getConfig();
        
        $this->setAttribute('method', 'post');
        $this->add(
            array(
            'name' => 'settings_id',
            'attributes' => array(
            'type'  => 'hidden',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'scope',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'scope',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'ref_id',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'reference',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'type',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'type',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'name',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'name',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'value',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'value',
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