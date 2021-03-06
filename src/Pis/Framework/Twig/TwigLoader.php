<?php

namespace Pis\Framework\Twig;

use DebugBar\Bridge\Twig\TraceableTwigEnvironment;
use DebugBar\Bridge\Twig\TwigCollector;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Pis\Framework\Annotation\TwigFunctionOptions;
use Pis\Framework\Exception\AnnotationMissingException;
use Pis\Framework\Router\Router;
use Pis\Framework\Translation\Translator;
use Pis\Framework\Twig\FormatExtension;
use Pis\Framework\Twig\Functions\BaseFunction;
use Pis\Framework\Twig\Functions\BreadCrumbFunction;
use Pis\Framework\Twig\Functions\CodeFormatterFunction;
use Pis\Framework\Twig\Functions\ElementarFunction;
use Pis\Framework\Twig\Functions\EmailEncodeFunction;
use Pis\Framework\Twig\MinifierExtension;
use Pis\Framework\Twig\RoutingExtension;
use Pis\Framework\Twig\TranslationExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

class TwigLoader
{

    /** @var TraceableTwigEnvironment */
    protected $twig;
    /** @var Router */
    protected $router;
    /** @var Reader */
    protected $annotationReader;
    /** @var \DebugBar\DebugBar|null */
    protected $debugBar;
    /** @var \DebugBar\JavascriptRenderer|null */
    protected $debugBarRenderer = null;
    /** @var \Symfony\Component\Form\FormFactory */
    protected $formFactory;
    /** @var EntityManager */
    protected $em;

    public function __construct(Request $request, \Twig_Environment $twigEnvironment, EntityManager $em, Router $router, Reader $annotationReader, Translator $translator, $languages, $locale, $debugBar, $additionalFunctionClasses) {
        $this->em = $em;
        $this->router = $router;
        $this->annotationReader = $annotationReader;
        $this->debugBar = $debugBar;
        if ($this->debugBar !== null)
            $this->debugBarRenderer = $this->debugBar->getJavascriptRenderer('/vendor/debugbar');

        if ($this->debugBar !== null) {
            $twig = new TraceableTwigEnvironment($twigEnvironment, $this->debugBar['time']);
            $this->debugBar->addCollector(new TwigCollector($twig));
        } else {
            $twig = $twigEnvironment;
        }

        $twig->addGlobal('_develop', DEVELOP);
        $twig->addGlobal('_left', 'left');
        $twig->addGlobal('_right', 'right');
        $twig->addGlobal('_debugBar', $this->debugBarRenderer);
        $twig->addGlobal('_translator', $translator);
        $twig->addGlobal('_languages', $languages);
        $twig->addGlobal('_locale', $locale);

        $twig->addGlobal('_attributes', $request->attributes->all());
        $twig->addGlobal('_get', $request->query->all());
        $twig->addGlobal('_post', $request->request->all());
        $twig->addGlobal('_queryString', $request->getQueryString());

        BaseFunction::$translator = $translator;
        $functionClasses = array(
            ElementarFunction::GetClassName(),
            BreadCrumbFunction::GetClassName(),
            CodeFormatterFunction::GetClassName(),
            EmailEncodeFunction::GetClassName()
        );
        $functionClasses = array_merge($functionClasses, $additionalFunctionClasses);

        // to enable auto-load for annotation
        new TwigFunctionOptions();

        foreach ($functionClasses as $class) {
            $reflectionClass = new \ReflectionClass($class);
            $className = preg_replace('/Function$/', '', $reflectionClass->getShortName());
            $methods = $reflectionClass->getMethods();
            $class::$router = $this->router;
            foreach ($methods as $method) {
                $methodName = $method->getName();
                $reflectionMethod = new \ReflectionMethod($class, $methodName);
                /** @var TwigFunctionOptions $options */
                $options = $this->annotationReader->getMethodAnnotation($reflectionMethod, '\Pis\Framework\Annotation\TwigFunctionOptions');
                if ($options === null)
                    throw new AnnotationMissingException($className . ' ' . $methodName);

                $twig->addFunction(
                    new \Twig_SimpleFunction(
                        $className . $methodName,
                        '\\' . $class . '::' . $methodName,
                        (array) $options
                    )
                );
            }
        }

        $csrfTokenManager = new \Symfony\Component\Security\Csrf\CsrfTokenManager(new \Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator(), new \Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage());
        $validator = Validation::createValidator();

        $formEngine = new TwigRendererEngine(array('_layout/form_div_layout.html.twig'));
        $formEngine->setEnvironment($twig);
        $twig->addExtension(new FormExtension(new TwigRenderer($formEngine, $csrfTokenManager)));

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new CsrfExtension($csrfTokenManager))
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();

        $twig->addExtension(new \Twig_Extension_Debug());
        $twig->addExtension(new FormatExtension());
        $twig->addExtension(new TranslationExtension($translator));
        $twig->addExtension(new RoutingExtension($this->router->urlGenerator));
        $twig->addExtension(new MinifierExtension());
        /** @var \Twig_Extension_Core $twigCore */
        $twigCore = $twig->getExtension('core');
        $twigCore->setDateFormat($_ENV['LOCALE']['FORMAT']['date'], '%d');
        $twigCore->setTimezone($_ENV['LOCALE']['timezone']);
        $twigCore->setNumberFormat(0, $translator->trans('number_separatorDecimal', array(), 'format'), $translator->trans('number_separatorThousands', array(), 'format'));
        /** @var FormatExtension $twigFormat */
        $twigFormat = $twig->getExtension('format');
        $twigFormat->setDateFormat($_ENV['LOCALE']['FORMAT']['date'], $_ENV['LOCALE']['FORMAT']['datetime'], '%d');

        $this->twig = $twig;
    }

    public function getTwig() {
        return $this->twig;
    }

    public function GetDebugRenderer() {
        return $this->debugBarRenderer;
    }

    public function GetFormFactory() {
        return $this->formFactory;
    }

}