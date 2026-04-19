<?php
namespace Validators;

use Model\Resident;
use Validator\AbstractValidator;

class PassportValidator extends AbstractValidator
{
    protected string $message = 'This passport already exists';

     public function rule(): bool
    {
        if (empty($this->value)) return true;

        $exclude_id = $this->args[0] ?? null;
        
        $query = Resident::where('passport', $this->value)
                         ->whereHas('residences', fn($q) => $q->whereNull('actual_date_of_departure'));

        if ($exclude_id) {
            $query->where('resident_id', '!=', $exclude_id);
        }

        return !$query->exists();
    }
}