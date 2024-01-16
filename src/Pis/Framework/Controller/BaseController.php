<?php

namespace Pis\Framework\Controller;

use Doctrine\ORM\EntityManager;
use Pis\Framework\Controller\BreadCrumb;
use Pis\Framework\Exception\ControllerActionNameFormatException;
use Pis\Framework\Exception\TemplateNotFoundException;
use Pis\Framework\Router\Router;
use Pis\Framework\Security\Security;
use Pis\Framework\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController
{
    static function createInstanceWithoutConstructor($class){
        $reflector = new \ReflectionClass($class);
        $properties = $reflector->getProperties();
        $defaults = $reflector->getDefaultProperties();

        $serialized = "O:" . strlen($class) . ":\"$class\":".count($properties) .':{';
        foreach ($properties as $property){
            $name = $property->getName();
            if($property->isProtected()){
                $name = chr(0) . '*' .chr(0) .$name;
            } elseif($property->isPrivate()){
                $name = chr(0)  . $class.  chr(0).$name;
            }
            $serialized .= serialize($name);
            if(array_key_exists($property->getName(),$defaults) ){
                $serialized .= serialize($defaults[$property->getName()]);
            } else {
                $serialized .= serialize(null);
            }
        }
        $serialized .="}";
        return unserialize($serialized);
    }

    /** @var \Twig\Environment */
    protected $twig;

    /** @var EntityManager */
    protected $em;

    /** @var Security */
    protected $security;

    /** @var Router */
    protected $router;

    /** @var Translator */
    protected $translator;

    /** @var BreadCrumb */
    protected $breadCrumb;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    protected $formFactory;

    public function __construct(EntityManager $em, Security $security, Router $router, Translator $translator, $twig, $formFactory) {
        $this->em = $em;
        $this->security = $security;
        $this->router = $router;
        $this->translator = $translator;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->breadCrumb = new BreadCrumb($router);
        if ($this->twig != null)
            $this->twig->addGlobal('_security', $security);
    }

    protected function response($context, BreadCrumb $breadCrumb, $template = null, $caller = null) {
        if ($template === null) {
            if ($caller == null)
                $caller = $this->determineCaller();
            $controller = $caller['controller'];
            $action = $caller['action'];
            $template = substr($controller, 0, -10) . '/' . $action . '.html.twig';
        }
        $context['_breadCrumb'] = $breadCrumb->Get();
        return new Response($this->twig->render($template, $context));
    }

    protected function responsePlain($responseString) {
        return new Response($responseString);
    }

    protected function responseJson($object) {
        return new Response(json_encode($object), 200, $headers = array(
            'Content-Type' => 'application/json'
        ));
    }

    protected function determineCaller() {
        $callers = debug_backtrace();
        $controller = substr($callers[2]['class'], strrpos($callers[2]['class'], "\\") + 1);
        if (preg_match('/(.*)Action$/', $callers[2]['function']) == 0)
            throw new ControllerActionNameFormatException($callers[2]['function']);
        $action = substr($callers[2]['function'], 0, -6);
        return array(
            'controller' => $controller,
            'action' => $action
        );
    }

    function getChild($instance, $classname) {
        $class = $classname;
        $t = get_class($instance);
        while (($p = get_parent_class($t)) !== false) {
            if ($p == $class) {
                return $t;
            }
            $t = $p;
        }
        return false;
    }

}