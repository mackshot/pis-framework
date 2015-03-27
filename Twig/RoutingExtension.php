<?php

namespace Pis\Framework\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RoutingExtension extends \Symfony\Bridge\Twig\Extension\RoutingExtension
{
    protected $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function getPath($name, $parameters = array(), $relative = true)
    {
        return '/' . $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function getUrl($name, $parameters = array(), $schemeRelative = true)
    {
        return $this->generator->generate($name, $parameters, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }

}
