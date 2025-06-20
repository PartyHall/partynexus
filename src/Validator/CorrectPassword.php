<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CorrectPassword extends Constraint
{
    public string $message = 'The current password is incorrect';
}
