<?php

namespace Pis\Framework\Twig\Functions;

use \Pis\Framework\Annotation\TwigFunctionOptions as Options;
use Pis\Framework\Twig\Functions\BaseFunction;

class CodeFormatterFunction extends BaseFunction
{

    /**
     * @Options(is_safe={"html"})
     * @param string $code
     * @return string
     */
    public static function SQL($code) {
        $geshi = new \GeSHi($code, 'sql');
        return $geshi->parse_code();
    }

}