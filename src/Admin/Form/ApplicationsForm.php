<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package		[MyApplication]
 * @package		BB's Zend Framework 2 Components
 * @package		AdminModule
 * @author		Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 * @link		http://gitlab.dragon-projects.de:81/groups/zf2
 * @license		http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright	copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

namespace Admin\Form;

use Zend\Form\Form;
use Admin\Module;

class ApplicationsForm extends Form
{
	public function __construct($name = null)
	{
		
		// we want to ignore the name passed
		parent::__construct('applications');
		$oModule = new Module();
		$cfg = $oModule->getConfig();
		
		$this->setAttribute('method', 'post');
		$this->add(array(
			'name' => 'applications_id',
			'attributes' => array(
				'type'  => 'hidden',
			),
		));
		$this->add(array(
			'name' => 'name',
			'attributes' => array(
				'type'  => 'text',
			),
			'options' => array(
				'label' => 'name',
			),
		));
		$this->add(array(
			'name' => 'shortname',
			'attributes' => array(
				'type'  => 'text',
			),
			'options' => array(
				'label' => 'shortname',
			),
		));
		$this->add(array(
			'name' => 'path',
			'type'  => 'text',
			'attributes' => array(
				'type'  => 'text',
			),
			'options' => array(
				'label' => 'path',
			),
		));
		$this->add(array(
			'name' => 'url',
			'type'  => 'url',
			'attributes' => array(
				'type'  => 'url',
			),
			'options' => array(
				'label' => 'url',
			),
		));
		$this->add(array(
			'name' => 'email',
			'type'  => 'email',
			'attributes' => array(
				'type'  => 'email',
			),
			'options' => array(
				'label' => 'email',
			),
		));

		$this->add(array(
				'name' => 'clients_id',
				'type'  => 'select',
				'attributes' => array(
					'type'  => 'select',
					'options' => array(),
				),
				'options' => array(
					'label' => 'client',
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