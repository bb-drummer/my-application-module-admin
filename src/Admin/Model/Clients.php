<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

namespace Admin\Model;

use Zend\Crypt\Password\Bcrypt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Clients implements InputFilterAwareInterface, ServiceLocatorAwareInterface
{
    public $clients_id;
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
        $this->clients_id    = (isset($data['clients_id'])) ? $data['clients_id'] : null;
        $this->name            = (isset($data['name'])) ? $data['name'] : null;
        $this->extraname    = (isset($data['extraname'])) ? $data['extraname'] : null;
        $this->homepage        = (isset($data['homepage'])) ? $data['homepage'] : null;
        $this->email        = (isset($data['email'])) ? $data['email'] : null;
        $this->contact        = (isset($data['contact'])) ? $data['contact'] : null;
        $this->phone        = (isset($data['phone'])) ? $data['phone'] : null;
        $this->statistics    = (isset($data['statistics'])) ? $data['statistics'] : null;
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
                    'name'     => 'clients_id',
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
                            'max'      => 255,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'extraname',
                    'required' => false,
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
                            'max'      => 255,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'homepage',
                    'required' => false,
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
                            'max'      => 255,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'email',
                    'required' => false,
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
                            'max'      => 255,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'contact',
                    'required' => false,
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
                            'max'      => 255,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'phone',
                    'required' => false,
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
                            'max'      => 255,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'statistics',
                    'required' => false,
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
                            'max'      => 255,
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