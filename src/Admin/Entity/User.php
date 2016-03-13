<?php
/**
 * BB's Zend Framework 2 Components
 *
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels <development@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <development@bjoernbartels.earth>
 */

namespace Admin\Entity;

use \ZfcUser\Entity\User as ZfcUser;
use Zend\Crypt\Password\Bcrypt;

class User extends ZfcUser
{
    
    /**
     * user's confirmation/activation token
  *
     * @var string
     */
    protected $token;

    /**
     * user's ACL role
  *
     * @var string
     */
    protected $aclrole;

    /**
     * user's ID
  *
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
        if (empty($this->aclrole)) {
            $this->setAclrole('public');
        }
        return $this->aclrole;
    }

    /**
     * Set ACL role.
     *
     * @param  STRING $aclrole
     * @return \Admin\Entity\User
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
     * @param  STRING $aclrole
     * @return \Admin\Entity\User
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
     * @param  STRING $aclrole
     * @return \Admin\Entity\User
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }


    
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
    
    /**
     * get copy of user's object properties in an assosiative array
  *
     * @return array
     */
    public function __getArrayCopy()
    {
        return array(
        "id"            => $this->getId(),
        "user_id"        => $this->getUserId(),
        "username"        => $this->getUsername(),
        "email"            => $this->getEmail(),
        "display_name"    => $this->getDisplayName(),
        "displayName"    => $this->getDisplayName(),
        "password"        => $this->getPassword(),
        "state"            => $this->getState(),
        "aclrole"        => $this->getAclrole(),
        "token"            => $this->getToken(),
        );
    }
    
    /**
     * get copy of user's object properties in an assosiative array
  *
     * @return array
     * /
    public function getArrayCopy() 
    {
        return $this->__getArrayCopy();
    } // collides with some magic "__get" method in ZfcUser parent ?! 
*/
    
}
