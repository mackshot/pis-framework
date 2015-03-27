<?php

namespace Pis\Framework\View\TwigExtension\Functions;

use \Pis\Framework\Annotation\TwigFunctionOptions as Options;
use Pis\Framework\Twig\Functions\BaseFunction;

class EmailEncodeFunction extends BaseFunction
{

    /**
     * @Options()
     * @param string $box
     * @param string $host
     * @param string $tld
     * @return string
     */
    public static function Url($box, $host, $tld) {
        $email = "mailto:" . $box . "@" . $host . "." . $tld;
        $output = '';
        for ($i = 0; $i < strlen($email); $i++)
            $output .= '&#'.ord($email[$i]).';';
        return $output;
    }

    /**
     * @Options(is_safe={"html"})
     * @param string $box
     * @param string $host
     * @param string $tld
     * @return string
     */
    public static function Link($box, $host, $tld) {
        $link = self::Url($box, $host, $tld);
        $name = $box . ' [at] ' . $host . ' [dot] ' . $tld;
        return '<a href="'.$link.'">'.$name.'</a>';
    }

}