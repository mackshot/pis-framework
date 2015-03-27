<?php

namespace Pis\Framework\Twig\Functions;

use \Pis\Framework\Annotation\TwigFunctionOptions as Options;

class BreadCrumbFunction extends BaseFunction
{

    private static $breadCrumb = array();

    /**
     * @Options()
     * @param string $text
     * @param string $route
     */
    public static function Add($text, $route) {
        array_push(self::$breadCrumb, array('text' => $text, 'route' => $route));
    }

    /**
     * @Options()
     * @param $breadcrumb
     */
    public static function Set($breadcrumb) {
        self::$breadCrumb = $breadcrumb;
    }

    /**
     * @Options(is_safe={"html"})
     * @return string
     */
    public static function Get() {
        $return = '';
        foreach(self::$breadCrumb as $item) {
            $text = $item['text'];
            $route = $item['route'];
            $return .= ' &#187; <a href="' . $route . '" alt="' . $text . '" title="' . $text . '">' . $text . '</a>';
        }
        return substr($return, 8);
    }
}