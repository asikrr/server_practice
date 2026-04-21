<?php
namespace Validators;

use Validator\AbstractValidator;

class MaxLengthValidator extends AbstractValidator
{
    protected string $message = 'Поле :field превышает лимит символов';

    public function rule(): bool
    {
        return mb_strlen($this->value, 'UTF-8') <= $this->args[0];
    }
}