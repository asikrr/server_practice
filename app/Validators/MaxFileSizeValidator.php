<?php
namespace Validators;

use Src\Validator\AbstractValidator;

class MaxFileSizeValidator extends AbstractValidator
{
    protected string $message = 'File :field is too big (max 2MB)';

    public function rule(): bool
    {
        if (!isset($this->value['size'])) {
            return true;
        }

        return $this->value['size'] <= 2097152;
    }
}