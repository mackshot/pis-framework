<?php

namespace Pis\Framework\Validator\Constraint;

class NotBlank extends \Symfony\Component\Validator\Constraints\NotBlank
{
    public $message = 'notBlank';

    public function validatedBy()
    {
        return '\Symfony\Component\Validator\Constraints\NotBlankValidator';
    }
}