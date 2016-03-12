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
use Zend\ServiceManager\ServiceManager;
use Zend\Di\ServiceLocator;

class Applications implements InputFilterAwareInterface, ServiceLocatorAwareInterface
{
    public $application_id;
    public $name;
    public $shortname;
    public $path;
    public $url;
    public $email;
    public $client_id;

    protected $inputFilter;
    protected $userService;
    protected $serviceManager;
    protected $serviceLocator;
    
    public function exchangeArray($data)
    {
        $this->application_id    = (isset($data['application_id'])) ? $data['application_id'] : $this->application_id;
        $this->name                = (isset($data['name'])) ? $data['name'] : $this->name;
        $this->shortname        = (isset($data['shortname'])) ? $data['shortname'] : $this->shortname;
        $this->path                = (isset($data['path'])) ? $data['path'] : $this->path;
        $this->url                = (isset($data['url'])) ? $data['url'] : $this->url;
        $this->email            = (isset($data['email'])) ? $data['email'] : $this->email;
        $this->client_id        = (isset($data['client_id'])) ? $data['client_id'] : $this->client_id;
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
                    'name'     => 'application_id',
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
                    'name'     => 'shortname',
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
                    'name'     => 'path',
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
                            'max'      => 1024,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'url',
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
                            'max'      => 1024,
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
                    'name'     => 'client_id',
                    'required' => true,
                    'filters'  => array(
                    array('name' => 'Int'),
                    ),
                    )
                )
            );
            
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
    
    public function getClient() 
    {
        /**
 * @var \Admin\Model\ClientsTable $clients 
*/
        if ($this->client_id) {
            $clients = $this->getServiceLocator()->get('AdminClientsTable');
            return $clients->getClients($this->client_id);
        } else {
            return null;
        }
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
        if (null === $this->serviceManager) {
            $this->serviceManager = new ServiceManager();
        }
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return void
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Set serviceLocator instance
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return void
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Retrieve serviceManager instance
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (null === $this->serviceLocator) {
            $this->serviceLocator = new ServiceLocator();
        }
        return $this->serviceLocator;
    }

}