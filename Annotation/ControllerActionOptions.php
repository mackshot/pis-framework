<?php

namespace Pis\Framework\Annotation;

/**
 * @Annotation
 */
class ControllerActionOptions
{
    public $method;
    public $xhr = false;
    public $twig = true;
}