<?php
namespace Admin\Model;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Crypt\Password\Bcrypt;

class User implements InputFilterAwareInterface
{
    public $user_id;
    public $display_name;
    public $username;
    public $email;
    public $street;
    public $city;
    public $phone;
    public $password;
    public $state;
    public $aclrole;
    public $token;

    protected $inputFilter;
    protected $userService;
    protected $serviceManager;
    protected $serviceLocator;
    
    public function exchangeArray($data)
    {
        $this->user_id		= (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->username		= (isset($data['username'])) ? $data['username'] : null;
        $this->email		= (isset($data['email'])) ? $data['email'] : null;
        $this->street		= (isset($data['street'])) ? $data['street'] : '';
        $this->city			= (isset($data['city'])) ? $data['city'] : '';
        $this->phone		= (isset($data['phone'])) ? $data['phone'] : '';
        /* $bcrypt = new Bcrypt;
        $bcrypt->setCost(null); // @TODO $this->getUserService()->getOptions()->getPasswordCost());
        $this->password		= (isset($data['password'])) ? $bcrypt->create($data['password']) : null; */
        $this->password		= (isset($data['password'])) ? $data['password'] : null;
        $this->display_name	= (isset($data['display_name'])) ? $data['display_name'] : '';
        $this->state		= (isset($data['state'])) ? $data['state'] : 0;
        $this->aclrole		= (isset($data['aclrole'])) ? $data['aclrole'] : 'public';
        $this->token		= (isset($data['token'])) ? $data['token'] : '';
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

            $inputFilter->add($factory->createInput(array(
                'name'     => 'user_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
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
            )));

            $inputFilter->add($factory->createInput(array(
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
            )));

            $inputFilter->add($factory->createInput(array(
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
            )));

            $inputFilter->add($factory->createInput(array(
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
            )));

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
     * @param ServiceManager $serviceManager
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