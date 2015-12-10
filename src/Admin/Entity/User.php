<?php

namespace Admin\Entity;

use \Admin\Model\UserProfile;
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
    
    
    
    /**
     * Get user's (basic) profile data.
     *
     * @return STRING
     */
    public function getProfile()
    {
		$oProfile = new UserProfile();
		$oProfile->load($this->getId());
		return $oProfile;
    }
    
    
    
    
    public function getArrayCopy()
    {
    	return $this->__getArrayCopy();
    }
    
    
    
    
    public function __getArrayCopy()
    {
		return array(
        	"id"			=> $this->getId(),
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
