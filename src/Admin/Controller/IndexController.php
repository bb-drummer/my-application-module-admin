<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels <coding@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
//use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    /**
     * redirect to acl section
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
	public function indexAction()
    {
        return $this->redirect()->toRoute('admin/acledit', array());
    }
}
