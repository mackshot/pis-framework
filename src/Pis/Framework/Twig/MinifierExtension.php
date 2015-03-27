<?php

namespace Pis\Framework\Twig;

use JShrink\Minifier;
use Pis\Framework\Twig\Parsers\MinJsTokenParser;

class MinifierExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'minifier';
    }


    public function getNodeVisitors()
    {
        return array(
        );
    }


    public function getTokenParsers()
    {
        return array(
            new MinJsTokenParser(),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('minjs', array($this, 'minjs')),
        );
    }

    public function minjs($message, array $arguments = array())
    {
        $message = strip_tags($message);
        $message = Minifier::minify($message, array('flaggedComments' => false));
        $message = '<script type="text/javascript">' . $message . '</script>';
        return $message;
    }

}
