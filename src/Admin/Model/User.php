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
 * @link      http://gitlab.dragon-projects.de:81/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

namespace Admin\Model;

use Zend\Crypt\Password\Bcrypt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\ServiceManager\ServiceLocator;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class User implements InputFilterAwareInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    public $id;
    public $user_id;
    public $display_name;
    public $username;
    public $email;
    public $password;
    public $state;
    public $aclrole;
    public $token;

    protected $inputFilter;
    protected $userService;
    protected $serviceManager;
    
    /**
     * set user's object property data
  *
     * @param  array   $data
     * @param  boolean $forceEncryptPassword
     * @return \Admin\Entity\User
     */
    public function exchangeArray($data = array(), $forceEncryptPassword = false)
    {
        if (isset($data['id']) && !empty($data["id"]) ) {
            $this->id            = ($data['id']);
            $this->user_id        = ($data['id']);
        } elseif (isset($data['user_id']) && !empty($data["user_id"]) ) {
            $this->id            = ($data['user_id']);
            $this->user_id        = ($data['user_id']);
        }
        $this->username        = (isset($data['username'])) ? $data['username'] : $this->username;
        $this->email        = (isset($data['email'])) ? $data['email'] : $this->email;
        if (isset($data['displayName']) ) {
            $this->display_name    = $data['displayName'];
            $this->displayName    = $data['displayName'];
        } elseif (isset($data['display_name']) ) {
            $this->display_name    = $data['display_name'];
            $this->displayName    = $data['display_name'];
        }
        if (isset($data["password"]) && $forceEncryptPassword ) {
            $bcrypt = new Bcrypt;
            $bcrypt->setCost(null); // @TODO $this->getUserService()->getOptions()->getPasswordCost());
            $data["password"] = $bcrypt->create($data['password']);
        }
        $this->password        = (isset($data['password'])) ? $data['password'] : $this->password;
        $this->state        = (isset($data['state'])) ? $data['state'] : $this->state;
        $this->aclrole        = (isset($data['aclrole'])) ? $data['aclrole'] : $this->aclrole;
        $this->token        = (isset($data['token'])) ? $data['token'] : $this->token;
        
        return $this;
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
                    'name'     => 'user_id',
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
                    'name'     => 'username',
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
                            'max'      => 100,
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
                            'max'      => 100,
                    ),
                    ),
                    array(
                    'name'    => 'EmailAddress',
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'display_name',
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
                            'max'      => 100,
                    ),
                    ),
                    ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'password',
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
                            'min'      => 6,
                            'max'      => 32,
                    ),
                    ),
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
    public function setServiceManager(ServiceManagerInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

}