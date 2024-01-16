<?php

namespace Pis\Framework;

use Pis\Framework\Exception\LogoutException;
use Pis\Framework\Twig\TwigLoader;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Framework implements HttpKernelInterface
{
    /** @var ErrorHandler */
    protected $errorHandler;
    /** @var \Symfony\Component\EventDispatcher\EventDispatcher */
    protected $dispatcher;
    /** @var UrlMatcher */
    protected $matcher;
    /** @var ControllerResolver */
    protected $controllerResolver;
    /** @var ArgumentResolver */
    protected $argumentResolver;
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;
    /** @var Router\Router */
    protected $router;
    /** @var \Doctrine\Common\Annotations\Reader */
    protected $annotationReader;
    /** @var int */
    protected $sessionTimeout;
    /** @var string */
    protected $twigLoaderClass;
    /** @var \Twig\Environment */
    protected $twigEnvironment;
    /** @var string */
    protected $securityClass;
    /** @var  string */
    protected $sessionName;

    public function __construct(ErrorHandler $errorHandler, \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher, UrlMatcher $matcher, ControllerResolver $controllerResolver, ArgumentResolver $argumentResolver, \Doctrine\ORM\EntityManager $em, Router\Router $router, \Doctrine\Common\Annotations\Reader $annotationReader, $securityClass, $twigLoaderClass, \Twig\Environment $twigEnvironment, $sessionTimeout, $sessionName)
    {
        $this->errorHandler = $errorHandler;
        $this->dispatcher = $dispatcher;
        $this->matcher = $matcher;
        $this->controllerResolver = $controllerResolver;
        $this->argumentResolver = $argumentResolver;
        $this->em = $em;
        $this->router = $router;
        $this->annotationReader = $annotationReader;
        $this->sessionTimeout = $sessionTimeout;
        $this->twigLoaderClass = $twigLoaderClass;
        $this->twigEnvironment = $twigEnvironment;
        $this->securityClass = $securityClass;
        $this->sessionName = $sessionName;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        try {
            $request->attributes->add($this->matcher->match($request->getPathInfo()));
            $controllerWithAction = $this->controllerResolver->getController($request);
            $controllerName = $controllerWithAction[0];
            $actionName = $controllerWithAction[1];
            $arguments = $this->argumentResolver->getArguments($request, $controllerWithAction);
            $reflectionClass = new \ReflectionClass($controllerName);
            $reflectionMethod = new \ReflectionMethod($controllerName, $actionName);

            new Annotation\ControllerActionOptions();  //autoload
            new Annotation\ControllerActionSecurity(); //autoload

            $translationManager = new Translation\TranslationManager($this->em);

            $languagesAvailable = [];
            foreach ($translationManager->getLanguages() as $l) {
                if ($l->getAvailable() != 1)
                    continue;
                $languagesAvailable[] = $l;
            }
            usort($languagesAvailable, function ($a, $b) {
                return strcmp($a->getName(), $b->getName());
            });

            $languagesAvailableLocale = [];
            foreach ($languagesAvailable as $l) {
                $languagesAvailableLocale[] = $l->getLocale();
            }

            $requestLocale = $request->getPreferredLanguage($languagesAvailableLocale);
            $session = new Security\Session($this->sessionName, $requestLocale, $request, $this->sessionTimeout);

            $security = new $this->securityClass($session, $this->em);
            if (!$this->hasAccess($security, $reflectionClass, $reflectionMethod))
                throw new AccessDeniedException($request->getUri());
            /** @var Annotation\ControllerActionOptions $actionOptions */
            $actionOptions = $this->checkRequest($reflectionMethod, $controllerName, $actionName, $request);

            $locale = $session->getLocale();
            $translator = new Translation\Translator($locale, new \Symfony\Component\Translation\MessageSelector());
            $translator->addLoader('db', $translationManager);
            $translator->addResource('db', array(), $locale);
            $translator->setFallbackLocales(array('en_US'));

            if ($actionOptions->twig) {
                /** @var TwigLoader $twigLoader */
                $twigLoader = new $this->twigLoaderClass($request, $this->twigEnvironment, $this->em, $this->router, $this->annotationReader, $translator, $languagesAvailable, $locale);
                $controllerObject = $reflectionClass->newInstance($this->em, $security, $this->router, $translator, $twigLoader->getTwig(), $twigLoader->GetFormFactory());

                /** @var Response $response */
                $response = $controllerObject->{$actionName}($arguments[0]);
                //$response->headers->set('X-Debug-Token', true);

                return $response;
            } else {
                $controllerObject = $reflectionClass->newInstance($this->em, $security, $this->router, $translator);

                /** @var Response $response */
                $response = $controllerObject->{$actionName}($arguments[0]);
                //$response->headers->set('X-Debug-Token', true);

                return $response;
            }
        } catch (LogoutException $e) {
            return new \Symfony\Component\HttpFoundation\RedirectResponse($this->router->ParseRoute('Home::Index'));
        } catch (AccessDeniedException $e) {
            return new Response('Unauthorized', 401);
        } catch (Exception\WrongRequestTypeException $e) {
            return new Response('Forbidden', 403);
        } catch (ResourceNotFoundException $e) {
            return new Response('Not Found', 404);
        } catch (NotFoundHttpException $e) {
            return new Response('Not Found', 404);
        } catch (MethodNotAllowedException $e) {
            return new Response('Method Not Allowed', 405);
        } catch (\Twig\Error\RuntimeError $e) {
            if ($this->errorHandler !== null)
                $this->errorHandler->handle($e);
            return new Response('An error occurred', 500);
        } catch (\Exception $e) {
            if ($this->errorHandler !== null)
                $this->errorHandler->handle($e);
            return new Response('An error occurred', 500);
        }
    }

    protected function checkRequest($reflectionMethod, $controller, $action, Request $request)
    {
        /** @var Annotation\ControllerActionOptions $actionOptions */
        $actionOptions = $this->annotationReader->getMethodAnnotation($reflectionMethod, 'Pis\Framework\Annotation\ControllerActionOptions');
        if ($actionOptions === null)
            throw new Exception\AnnotationMissingException($controller . ' ' . $action);
        if (($request->getMethod() == "HEAD" && in_array("GET", $actionOptions->method)) ||
            in_array($request->getMethod(), $actionOptions->method)
        ) { /* method ok */
        } else
            throw new MethodNotAllowedException($actionOptions->method, 'Got ' . $request->getMethod() . ' expected ' . implode(',', $actionOptions->method));
        if ($request->isXmlHttpRequest() != $actionOptions->xhr)
            throw new Exception\WrongRequestTypeException('Got ' . $request->isXmlHttpRequest() . ' expected ' . $actionOptions->xhr);
        return $actionOptions;
    }

    protected function hasAccess(Security\Security $security, $reflectionClass, $reflectionMethod)
    {
        $classSecurity = $this->annotationReader->getClassAnnotation($reflectionClass, 'Pis\Framework\Annotation\ControllerActionSecurity');
        $methodSecurity = $this->annotationReader->getMethodAnnotation($reflectionMethod, 'Pis\Framework\Annotation\ControllerActionSecurity');
        /** Annotation\ControllerActionSecurity $actionSecurity */
        $actionSecurity = new Annotation\ControllerActionSecurity();

        if ($methodSecurity !== null && $methodSecurity->user !== null)
            $actionSecurity->user = $methodSecurity->user;
        else if ($classSecurity !== null && $classSecurity->user !== null)
            $actionSecurity->user = $classSecurity->user;

        if ($methodSecurity !== null && !empty($methodSecurity->roles))
            $actionSecurity->roles = $methodSecurity->roles;
        else if ($classSecurity !== null && !empty($classSecurity->roles))
            $actionSecurity->roles = $classSecurity->roles;

        if (empty($actionSecurity->roles) && $actionSecurity->user === null)
            return true;

        if (!empty($actionSecurity->roles)) {
            foreach ($actionSecurity->roles as $role)
                if ($security->hasRole($role))
                    return true;
            return false;
        }
        if ($actionSecurity->user === false && $security->isUser() === false)
            return true;
        if ($actionSecurity->user === true && $security->isUser() === true)
            return true;
        return false;
    }

}