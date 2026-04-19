<?php
namespace Validators;

use Model\Room;
use Validator\AbstractValidator;

class UniqueRoomValidator extends AbstractValidator
{
    protected string $message = 'Room with this number already exists in dormitory';

    public function rule(): bool
    {
        $dormitory_id = $this->args[0] ?? null;
        $exclude_id = $this->args[1] ?? null; 

        $query = Room::where('dormitory_id', $dormitory_id)->where('room_number', $this->value);
    
        if ($exclude_id) {
            $query->where('room_id', '!=', $exclude_id);
        }

        return $query->count() === 0;
    }
}