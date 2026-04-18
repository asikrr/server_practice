<?php

namespace Validators;

use Src\Validator\AbstractValidator;

class MinNumberValidator extends AbstractValidator
{

    protected string $message = 'Field :field cannot be negative';

    public function rule(): bool
    {
        return $this->value >= 0;
    }
}