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

use Zend\Mvc\Application;
use Zend\Mvc\Router\RouteInterface;
use Zend\Mvc\Router\Exception;
use Zend\Http\PhpEnvironment\Response;
use ZfcUser\Options\ModuleOptions;


use ZfcUser\Controller\RedirectCallback as ZfcUserRedirectCallback;
use Zend\Http\Request;

/**
 * Buils a redirect response based on the current routing and parameters
 * @see \ZfcUser\Controller\RedirectCallback
 */
class RedirectCallback extends ZfcUserRedirectCallback
{

    /**
     * @var RouteInterface  
     * /
    private $router;

    /**
     * @var Application 
     * /
    private $application;

    /**
     * @var ModuleOptions 
     * /
    private $options;

    /**
     * @param Application    $application
     * @param RouteInterface $router
     * @param ModuleOptions  $options
     * /
    public function __construct(Application $application, RouteInterface $router, ModuleOptions $options)
    {
        $this->router = $router;
        $this->application = $application;
        $this->options = $options;
    }

    /**
     * @return Response
     * /
    public function __invoke()
    {
    	$routeMatch = $this->application->getMvcEvent()->getRouteMatch();
        $redirect = $this->getRedirect($routeMatch->getMatchedRouteName(), $this->determineRedirectRouteFromRequest());

        $response = $this->application->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $redirect);
        $response->setStatusCode(302);
        return $response;
    }

    /**
     * Return the redirect from param.
     * First checks GET then POST
     * @return string
     * /
    private function determineRedirectRouteFromRequest()
    {
        $request  = $this->application->getRequest();
        $redirect = $request->getQuery('redirect');
        if ($redirect && $this->checkIfRouteExists($redirect)) {
            return $redirect;
        }

        $redirect = $request->getPost('redirect');
        if ($redirect && $this->checkIfRouteExists($redirect)) {
            return $redirect;
        }

        return false;
    }

    /**
     * @param $route
     * @return bool
     * /
    private function checkIfRouteExists($route)
    {
        try {
            $this->router->assemble(array(), array('name' => $route));
        } catch (Exception\RuntimeException $e) {
            return false;
        }
        return true;
    }

    /**
     * Returns the url to redirect to based on current route.
     * If $redirect is set and the option to use redirect is set to true, it will return the $redirect url.
     *
     * @param  string $currentRoute
     * @param  bool   $redirect
     * @return mixed
     * /
    protected function getRedirect($currentRoute, $redirect = false)
    {
        $useRedirect = $this->options->getUseRedirectParameterIfPresent();
        $redirUrl = $redirect;
        $routeExists = ($redirect && $this->routeExists($redirect));
        if (!$useRedirect || !$routeExists) {
            $redirect = false;
        }

        switch ($currentRoute) {
        case 'zfcuser/register':
        case 'zfcuser/login':
        case 'zfcuser/authenticate':
            $route = ($redirect) ?: $this->options->getLoginRedirectRoute();
            if (!$routeExists && !empty($redirUrl)) {
                return $redirUrl;
            } else {
                return $this->router->assemble(array(), array('name' => $route));
            }
            break;
        case 'zfcuser/logout':
            $route = ($redirect) ?: $this->options->getLogoutRedirectRoute();
            return $this->router->assemble(array(), array('name' => $route));
          break;
        default:
            return $this->router->assemble(array(), array('name' => 'zfcuser'));
        }
    } 
    
    */
}
