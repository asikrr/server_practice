<?php
namespace Validators;

use Validator\AbstractValidator;

class DateValidator extends AbstractValidator
{
    protected string $message = 'Field :field cannot be earlier than the entry date';


    public function rule(): bool
    {
        return $this->value > $this->args[0];
    }
}