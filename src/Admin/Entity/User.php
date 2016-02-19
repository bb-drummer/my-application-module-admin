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

namespace Admin\Entity;

use \ZfcUser\Entity\User as ZfcUser;

class User extends ZfcUser
{
	
	/**
	 * user's confirmation/activation token
	 * @var string
	 */
	protected $token;

	/**
	 * user's ACL role
	 * @var string
	 */
	protected $aclrole;

	/**
	 * user's ID
	 * @var int
	 */
	protected $user_id;
	
	/**
	 * user's street
	 * @var string
	 */
	protected $street;
	
	/**
	 * user's city
	 * @var string
	 */
	protected $city;
	
	/**
	 * user's phonenumber
	 * @var string
	 */
	protected $phone;
	
	
	
	/**
	 * Get ACL role.
	 *
	 * @return STRING
	 */
	public function getAclrole()
	{
		return $this->aclrole;
	}

	/**
	 * Set ACL role.
	 *
	 * @param STRING $aclrole
	 * @return UserInterface
	 */
	public function setAclrole($aclrole)
	{
		$this->aclrole = $aclrole;
		return $this;
	}
	
	/**
	 * Get token string.
	 *
	 * @return STRING
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * Set token string.
	 *
	 * @param STRING $aclrole
	 * @return UserInterface
	 */
	public function setToken($token)
	{
		$this->token = $token;
		return $this;
	}

	/**
	 * Get user-id string.
	 *
	 * @return STRING
	 */
	public function getUserId()
	{
		if (null === $this->user_id) {
			$this->setUserId($this->getId());
		}
		return $this->user_id;
	}

	/**
	 * Set user-id string.
	 *
	 * @param STRING $aclrole
	 * @return UserInterface
	 */
	public function setUserId($user_id)
	{
		$this->user_id = $user_id;
		return $this;
	}


	
	/**
	 * Get user's street string.
	 *
	 * @return STRING
	 */
	public function getStreet()
	{
		return $this->street;
	}
	
	/**
	 * Set user's street string.
	 *
	 * @param STRING $street
	 * @return UserInterface
	 */
	public function setStreet($street)
	{
		$this->street = $street;
		return $this;
	}
	
	/**
	 * Get user's city string.
	 *
	 * @return STRING
	 */
	public function getCity()
	{
		return $this->city;
	}
	
	/**
	 * Set user's city string.
	 *
	 * @param STRING $city
	 * @return UserInterface
	 */
	public function setCity($city)
	{
		$this->city = $city;
		return $this;
	}
	
	/**
	 * Get user's phonenumber string.
	 *
	 * @return STRING
	 */
	public function getPhone()
	{
		return $this->phone;
	}
	
	/**
	 * Set user's phonenumber string.
	 *
	 * @param STRING $phone
	 * @return UserInterface
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
		return $this;
	}
	
	
	
	public function exchangeArray($data)
	{
		$this->id		    = (isset($data['id'])) ? $data['id'] : null;
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

	/*public function getArrayCopy()
	{
		return get_object_vars($this);
	}*/
	
	
	
	public function __getArrayCopy()
	{
		return array(
			"id"			=> $this->getId(),
			"user_id"	    => $this->getUserId(),
			"username"		=> $this->getUsername(),
			"email"			=> $this->getEmail(),
			"display_name"	=> $this->getDisplayName(),
			"password"		=> $this->getPassword(),
			"state"			=> $this->getState(),
			"aclrole"		=> $this->getAclrole(),
			"token"			=> $this->getToken(),

			"street"		=> $this->getStreet(),
			"city"			=> $this->getCity(),
			"phone"			=> $this->getPhone(),
		);
	}
	
}
