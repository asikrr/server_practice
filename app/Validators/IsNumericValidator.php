<?php

namespace Validators;

use Src\Validator\AbstractValidator;

class IsNumericValidator extends AbstractValidator
{

    protected string $message = 'Field :field must be a number';

    public function rule(): bool
    {
        return is_numeric($this->value);
    }
}