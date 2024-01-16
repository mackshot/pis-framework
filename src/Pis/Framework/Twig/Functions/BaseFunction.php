<?php

namespace Pis\Framework\Twig\Functions;

use \Pis\Framework\Annotation\TwigFunctionOptions as Options;

class BaseFunction
{
    /** @var \Pis\Framework\Translation\Translator */
    public static $translator;

    /** @var  \Pis\Framework\Router\Router */
    public static $router;

    const NameUndefined = '---';

}