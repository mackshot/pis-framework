<?php

namespace Pis\Framework\Twig\Functions;

use \Pis\Framework\Annotation\TwigFunctionOptions as Options;
use Pis\Framework\Twig\Functions\BaseFunction;

class ElementarFunction extends BaseFunction
{

    /**
     * @Options(is_safe={"html"})
     * @param string $title
     * @param string $routeName
     * @param array $parameters
     * @return string
     */
    public static function Link($title, $routeName, $parameters = array()) {
        return sprintf('<a href="%s">' . $title . '</a>', self::$router->ParseRoute($routeName, $parameters));
    }

    /**
     * @Options(is_safe={"html"})
     * @param string $title
     * @param string $url
     * @return string
     */
    public static function LinkWithUrl($title, $url) {
        return sprintf('<a href="%s">' . $title . '</a>', $url);
    }

}