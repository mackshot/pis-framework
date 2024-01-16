<?php

namespace Pis\Framework\Twig\Parsers;

use Symfony\Bridge\Twig\Node\TransNode;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;

class TransPluralTokenParser extends TransTokenParser
{
    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'transplural';
    }
}
