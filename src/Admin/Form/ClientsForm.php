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
use Admin\Module;

class ClientsForm extends Form
{
    public function __construct($name = null)
    {
        
        // we want to ignore the name passed
        parent::__construct('clients');
        $oModule = new Module();
        $cfg = $oModule->getConfig();
        
        $this->setAttribute('method', 'post');
        $this->add(
            array(
            'name' => 'clients_id',
            'attributes' => array(
            'type'  => 'hidden',
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
            'name' => 'extraname',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'extraname',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'homepage',
            'type'  => 'url',
            'attributes' => array(
            'type'  => 'url',
            ),
            'options' => array(
            'label' => 'homepage',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'email',
            'type'  => 'email',
            'attributes' => array(
            'type'  => 'email',
            ),
            'options' => array(
            'label' => 'email',
            ),
            )
        );
        $this->add(
            array(
            'name' => 'contact',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'contact',
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
            'name' => 'statistics',
            'attributes' => array(
            'type'  => 'text',
            ),
            'options' => array(
            'label' => 'statistics',
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