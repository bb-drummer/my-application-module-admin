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

namespace Admin\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Isallowed extends AbstractHelper
{
	/**
	 * @var AuthenticationService
	 */
	protected $authService;
	
	/**
	 * __invoke
	 *
	 * @access public
	 * @return \ZfcUser\Entity\UserInterface
	 */
	public function __invoke( $resource )
	{
		/** @var Zend\Permissions\Acl\Acl $acl **/
		$acl = $this->view->navigation()->getAcl();
		if ( empty($resource) || !$acl->hasResource($resource) ) {
			return true;
		}	
		/** @var \Admin\Entity\User $user **/
		$user = $this->view->zfcUserIdentity(); // ->getIdentity();
		if ($user) { // ($this->getAuthService()->hasIdentity()) {
			//$user = $this->getAuthService()->getIdentity();
			$role = $user->getAclrole();
		} else {
			$role = 'public';
		}
		return ( $acl->isAllowed($role, $resource) );
	}
	
	/**
	 * Get authService.
	 *
	 * @return AuthenticationService
	 */
	public function getAuthService()
	{
		return $this->authService;
	}
	
	/**
	 * Set authService.
	 *
	 * @param AuthenticationService $authService
	 * @return \ZfcUser\View\Helper\ZfcUserIdentity
	 */
	public function setAuthService(AuthenticationService $authService)
	{
		$this->authService = $authService;
		return $this;
	}
	
}