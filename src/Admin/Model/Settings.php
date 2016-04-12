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

namespace Admin\Model;

use Zend\Crypt\Password\Bcrypt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Settings implements InputFilterAwareInterface, ServiceLocatorAwareInterface
{
    public $settings_id;
    public $scope;
    public $ref_id;
    public $type;
    public $name;
    public $value;

    protected $inputFilter;
    protected $userService;
    protected $serviceManager;
    protected $serviceLocator;
    
    public function exchangeArray($data)
    {
        $this->settings_id    = (isset($data['settings_id'])) ? $data['settings_id'] : null;
        $this->scope        = (isset($data['scope'])) ? $data['scope'] : null;
        $this->ref_id        = (isset($data['ref_id'])) ? $data['ref_id'] : null;
        $this->type            = (isset($data['type'])) ? $data['type'] : null;
        $this->name            = (isset($data['name'])) ? $data['name'] : null;
        $this->value        = (isset($data['value'])) ? $data['value'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'settings_id',
                    'required' => true,
                    'filters'  => array(
                    array('name' => 'Int'),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'scope',
                    'required' => true,
                    'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                    array(
                    'name'    => 'StringLength',
                    'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 32,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'ref_id',
                    'required' => true,
                    'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                    array(
                    'name'    => 'StringLength',
                    'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 32,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'type',
                    'required' => true,
                    'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                    array(
                    'name'    => 'StringLength',
                    'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 32,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'name',
                    'required' => true,
                    'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                    array(
                    'name'    => 'StringLength',
                    'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 32,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'value',
                    'required' => false,
                    'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                    ),
                    )
                )
            );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
    /**
     * Getters/setters for DI stuff
     */

    public function getUserService()
    {
        if (!$this->userService) {
            $this->userService = $this->getServiceManager()->get('zfcuser_user_service');
        }
        return $this->userService;
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager 
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Set serviceManager instance
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return void
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Retrieve serviceManager instance
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

}