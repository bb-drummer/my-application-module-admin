<?php

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
     * Get token string.
     *
     * @return STRING
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set token string.
     *
     * @param STRING $aclrole
     * @return UserInterface
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
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
        );
    }
    
}
