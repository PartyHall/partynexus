<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\Language;
use App\Model\OAuthUserInfos;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OAuthUserManager
{
    public function __construct(
        private UserRepository         $userRepository,
        private EntityManagerInterface $emi,
    )
    {
    }

    public function generateUniqueUsername(OAuthUserInfos $userInfos): ?string
    {
        $baseUsername = '';
        if (\strlen($userInfos->username) > 0) {
            $user = $this->userRepository->findOneByUsername($userInfos->username);
            if ($user === null) {
                return $userInfos->username;
            }

            $baseUsername = $userInfos->username;
        }

        if (\strlen($userInfos->firstName) > 0 && \strlen($userInfos->lastName) > 0) {
            $username = "{$userInfos->firstName}_{$userInfos->lastName}";

            $user = $this->userRepository->findOneByUsername($username);
            if ($user === null) {
                return $username;
            }

            if (\strlen($baseUsername) === 0) {
                $baseUsername = $username;
            }
        }

        if (\strlen($baseUsername) > 0) {
            $tries = 0;

            while ($tries < 20) {
                $randomSuffix = \random_int(1, 99999);

                $username = \sprintf('%s_%05d', $baseUsername, $randomSuffix);
                $user = $this->userRepository->findOneByUsername($username);
                if ($user === null) {
                    return $username;
                }

                $tries++;
            }
        }

        return null;
    }

    public function createUser(OAuthUserInfos $userInfos): User
    {
        if (\strlen($userInfos->oauthUserId) === 0) {
            throw new BadRequestHttpException('No OAuth user ID from the IDP');
        }

        if (\strlen($userInfos->email) === 0) {
            throw new BadRequestHttpException('No email from the IDP');
        }

        $username = $this->generateUniqueUsername($userInfos);
        if ($username === null) {
            throw new BadRequestHttpException('Cannot generate a unique username');
        }

        $user = new User();
        $user->setUsername($username);
        $user->setOauthUserId($userInfos->oauthUserId);

        $user->setFirstname($userInfos->firstName);
        $user->setLastname($userInfos->lastName);
        $user->setEmail($userInfos->email);
        $user->setLanguage(Language::fromAlpha2($userInfos->locale) ?? Language::AMERICAN_ENGLISH);
        $user->setRoles(\array_unique($userInfos->roles));

        $this->emi->persist($user);
        $this->emi->flush();

        return $user;
    }

    public function updateUser(User $user, OAuthUserInfos $userInfos): User
    {
        $user->setFirstname($userInfos->firstName);
        $user->setLastname($userInfos->lastName);
        $user->setEmail($userInfos->email);

        // If the admin uses an idp that does not support custom roles (e.g. Google)
        // we want the roles that he manually set to remain unchanged
        // As the USER role is hardcoded anyway, thats not an issue
        if (\count($userInfos->roles) > 0) {
            $user->setRoles(\array_unique($userInfos->roles));
        }

        return $user;
    }
}
