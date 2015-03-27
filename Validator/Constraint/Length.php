<?php

namespace Pis\Framework\Validator\Constraint;

class Length extends \Symfony\Component\Validator\Constraints\Length
{
    public $maxMessage = 'length_too_long';
    public $minMessage = 'length_too_short';
    public $exactMessage = 'length_exact';

    public function __construct($options = null)
    {
        parent::__construct($options);
    }

    public function validatedBy()
    {
        return '\Symfony\Component\Validator\Constraints\LengthValidator';
    }

}