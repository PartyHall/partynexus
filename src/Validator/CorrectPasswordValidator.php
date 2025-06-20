<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CorrectPasswordValidator extends ConstraintValidator
{
    public function __construct(
        private Security                    $security,
        private UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof CorrectPassword) {
            throw new UnexpectedTypeException($constraint, CorrectPassword::class);
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new BadRequestException('Current user should be a User');
        }

        if ($user->isPasswordSet()) {
            if (!$this->passwordHasher->isPasswordValid($user, $value)) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}
