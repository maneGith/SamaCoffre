<?php
namespace AppBundle\EventListener;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
/**
 * Description of ErrorRedirect
 *
 * @author idrissamane
 */
class ErrorRedirect {
    /**
     *
     *@var Router
     */
    protected $router;

    /**
     * @param Router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof NotFoundHttpException) {

            /** Choose your router here */
            $route = 'login';

            if ($route === $event->getRequest()->get('_route')) {
                return;
            }

            $url = $this->router->generate($route);
            $response = new RedirectResponse($url);
            $event->setResponse($response);

        }
    }
    
}
