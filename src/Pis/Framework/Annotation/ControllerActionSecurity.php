<?php

namespace Pis\Framework\Annotation;

/**
 * @Annotation
 */
class ControllerActionSecurity
{
    /** @var bool */
    public $user = null;
    /** @var array */
    public $roles = null;
}