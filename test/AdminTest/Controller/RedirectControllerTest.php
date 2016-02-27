<?php
namespace AdminTest\Controller;

use \Admin\Controller\RedirectCallback,
    \AdminTest\Framework\TestCase as ActionControllerTestCase,
    Zend\Http\Request,
    Zend\Http\Response,
    Zend\Http\Router,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Router\RouteMatch,
    Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter
;

/**
 * @coversDefaultClass \Admin\Controller\RedirectCallback
 */
class RedirectControllerTest extends ActionControllerTestCase
{
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setupController()
    {
        // special callback object, for now, no setup...
    }

    /**
     * @covers ::getRedirect
     */
    public function testGetRedirectReturnsLoginRouteWithRedirectAppendixWhenSessionTimedOutAndNewLoginIsRequired()
    {
        $this->assertTrue(true);
    }
    
}