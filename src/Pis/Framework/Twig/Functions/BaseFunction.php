<?php

namespace Pis\Framework\Twig\Functions;

use \Pis\Framework\Annotation\TwigFunctionOptions as Options;
use Pis\Framework\Helper\GetClassName;

class BaseFunction
{

    use GetClassName;

    /** @var \Pis\Framework\Translation\Translator */
    public static $translator;

    /** @var  \Pis\Framework\Router\Router */
    public static $router;

    const NameUndefined = '---';

}