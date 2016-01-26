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

namespace Admin\Model;

use Admin\Module as AdminModule;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class UserProfile implements InputFilterAwareInterface
{
    public $user_id;
    
    public $street;
    public $city;
    public $phone;
    public $cell;

    public $twitter;
    public $facebook;
    public $skype;
    public $icq;

    protected $inputFilter;
    protected $userService;
    protected $serviceManager;
    protected $serviceLocator;
    
    public function load( $id )
    {
    	if ( !$id ) {
    		return $this;
    	}
    	$this->user_id = $id;
    	try {
    		$oModule = new AdminModule();
    		$oSM = $oModule->getServiceManager();
    		$table = $oSM->get('Admin\Model\UserProfileTable'); // $this->getServiceManager()->get('Admin\Model\UserProfileTable');
    		$profile = $table->getUserProfile($id);
    		if ($profile) {
    			$this->exchangeArray( $profile->getArrayCopy() );
    		}
    	} catch (\Exception $ex) { }
    	
        return $this;
    }
    
    public function save()
    {
    	try {
    		$oModule = new AdminModule();
    		$oSM = $oModule->getServiceManager();
    		$table = $oSM->get('Admin\Model\UserProfileTable'); // $this->getServiceManager()->get('Admin\Model\UserProfileTable');
    		$table->saveUserProfile( $this );
        	return true;
    	} catch (\Exception $ex) {
        	return $ex->getMessage();
		}
    }
    
    public function exchangeArray($data)
    {
        $this->user_id		= (isset($data['user_id'])) ? $data['user_id'] : null;
        
        $this->street		= (isset($data['street'])) ? $data['street'] : '';
        $this->city			= (isset($data['city'])) ? $data['city'] : '';
        $this->country		= (isset($data['country'])) ? $data['country'] : '';
        $this->phone		= (isset($data['phone'])) ? $data['phone'] : '';
        $this->cell			= (isset($data['cell'])) ? $data['cell'] : '';
        
        $this->twitter		= (isset($data['twitter'])) ? $data['twitter'] : '';
        $this->facebook		= (isset($data['facebook'])) ? $data['facebook'] : '';
        $this->skype		= (isset($data['skype'])) ? $data['skype'] : '';
        $this->icq			= (isset($data['icq'])) ? $data['icq'] : '';
        
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
                'name'     => 'street',
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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'city',
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
                            'max'      => 100,
                        ),
                    ),
                    array(
                        'name'    => 'EmailAddress',
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'country',
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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'cell',
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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'twitter',
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
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'facebook',
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
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'skype',
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
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'icq',
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
    
	/**
	 * @return the $user_id
	 */
	public function getId() {
		return $this->user_id;
	}

	/**
	 * @param Ambigous <unknown, NULL> $user_id
	 */
	public function setId($user_id) {
		$this->user_id = $user_id;
	}

	/**
	 * @return the $street
	 */
	public function getStreet() {
		return $this->street;
	}

	/**
	 * @param Ambigous <string, unknown> $street
	 */
	public function setStreet($street) {
		$this->street = $street;
	}

	/**
	 * @return the $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @param Ambigous <string, unknown> $city
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * @return the $phone
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * @param Ambigous <string, unknown> $phone
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
	}

	/**
	 * @return the $cell
	 */
	public function getCell() {
		return $this->cell;
	}

	/**
	 * @param Ambigous <string, unknown> $cell
	 */
	public function setCell($cell) {
		$this->cell = $cell;
	}

	/**
	 * @return the $twitter
	 */
	public function getTwitter() {
		return $this->twitter;
	}

	/**
	 * @param Ambigous <string, unknown> $twitter
	 */
	public function setTwitter($twitter) {
		$this->twitter = $twitter;
	}

	/**
	 * @return the $facebook
	 */
	public function getFacebook() {
		return $this->facebook;
	}

	/**
	 * @param Ambigous <string, unknown> $facebook
	 */
	public function setFacebook($facebook) {
		$this->facebook = $facebook;
	}

	/**
	 * @return the $skype
	 */
	public function getSkype() {
		return $this->skype;
	}

	/**
	 * @param Ambigous <string, unknown> $skype
	 */
	public function setSkype($skype) {
		$this->skype = $skype;
	}

	/**
	 * @return the $icq
	 */
	public function getIcq() {
		return $this->icq;
	}

	/**
	 * @param Ambigous <string, unknown> $icq
	 */
	public function setIcq($icq) {
		$this->icq = $icq;
	}


    
}