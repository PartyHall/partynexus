<?php

namespace App\Model;

use App\Entity\ForgottenPassword;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordReset
{
    #[Assert\Email]
    #[Assert\NotBlank]
    #[Groups([ForgottenPassword::API_CREATE])]
    public string $email;
}
