<?php
namespace Admin\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Isdenied extends AbstractHelper
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
		/** @var \Zend\Permissions\Acl\Acl $acl **/
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
		return ( !$acl->isAllowed($role, $resource) );
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