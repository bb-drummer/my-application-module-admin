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
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use ZfcUser\Options\AuthenticationOptionsInterface;

class RequestPasswordResetForm extends Form
{
	public function __construct($name, AuthenticationOptionsInterface $options)
	{
		$this->setAuthenticationOptions($options);
		parent::__construct($name);
		
		$this->setAttribute('method', 'post');
		$this->add(array(
			'name'		=> 'identity',
			'type'			=> 'text',
			'attributes'	=> array(
				'type'	=> 'text',
			),
		));
		
		$emailElement = $this->get('identity');
		$label = $emailElement->getLabel('label');
		// @TODO: make translation-friendly
		foreach ($this->getAuthenticationOptions()->getAuthIdentityFields() as $mode) {
			$label = (!empty($label) ? $label . ' or ' : '') . ucfirst($mode);
		}
		$emailElement->setLabel($label);
		
		$this->add(array(
			'name'			=> 'submit',
			'attributes'	=> array(
				'type'	=> 'submit',
				'value'	=> 'request password reset',
				'id'	=> 'submitbutton',
			),
			'options'		=> array(
				'label'	=> 'request password reset',
			),
		));
		
	}
	
	public function getInputFilter()
	{
		$inputFilter = new InputFilter();
		$factory	 = new InputFactory();

		$inputFilter->add($factory->createInput(array(
			'name'			=> 'identity',
			'required'		=> true,
			'filters'		=> array(
				array('name'	=> 'StripTags'),
				array('name'	=> 'StringTrim'),
			),
			'validators'	=> array(
				array(
					'name'		=> 'StringLength',
					'options'	=> array(
						'encoding'	=> 'UTF-8',
						'min'		=> 1,
						'max'		=> 100,
					),
				),
				array(
					'name'		=> 'EmailAddress',
				),
			),
		)));

		$this->filter = $inputFilter;
	
		return $this->filter;
	}
	
	/**
	 * Set Authentication-related Options
	 *
	 * @param AuthenticationOptionsInterface $authOptions
	 * @return Login
	 */
	public function setAuthenticationOptions(AuthenticationOptionsInterface $authOptions)
	{
		$this->authOptions = $authOptions;
		return $this;
	}

	/**
	 * Get Authentication-related Options
	 *
	 * @return AuthenticationOptionsInterface
	 */
	public function getAuthenticationOptions()
	{
		return $this->authOptions;
	}
}