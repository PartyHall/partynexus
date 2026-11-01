<?php

namespace App\Mercure;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\RegisteredClaims;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;

/**
 * Mints Mercure 1.x (OAuth 2.0 / RFC 9068) publisher access tokens.
 *
 * The stock Symfony factory only emits the legacy "mercure" claim and a
 * "typ: JWT" header, which the modern Mercure hub rejects. This factory emits
 * the claims required by the modern protocol: iss, aud, exp, a "typ: at+jwt"
 * header and the RFC 9396 authorization_details claim.
 */
final class PublisherTokenFactory implements TokenFactoryInterface
{
    private const SIGN_ALGORITHMS = [
        'hmac.sha256' => Signer\Hmac\Sha256::class,
        'hmac.sha384' => Signer\Hmac\Sha384::class,
        'hmac.sha512' => Signer\Hmac\Sha512::class,
        'ecdsa.sha256' => Signer\Ecdsa\Sha256::class,
        'ecdsa.sha384' => Signer\Ecdsa\Sha384::class,
        'ecdsa.sha512' => Signer\Ecdsa\Sha512::class,
        'rsa.sha256' => Signer\Rsa\Sha256::class,
        'rsa.sha384' => Signer\Rsa\Sha384::class,
        'rsa.sha512' => Signer\Rsa\Sha512::class,
    ];

    private const AUTHORIZATION_DETAIL_TYPE = 'https://mercure.rocks/authorization-detail';

    private readonly Configuration $configuration;
    private readonly int $jwtLifetime;

    public function __construct(
        #[Autowire('%env(MERCURE_PUBLISHER_JWT_KEY)%')]
        string $secret,
        #[Autowire('%env(PUBLIC_URL)%')]
        private readonly string $issuer,
        #[Autowire('%env(PUBLIC_URL)%/.well-known/mercure')]
        private readonly string $audience,
        string $algorithm = 'hmac.sha256',
        ?int $jwtLifetime = 3600,
    ) {
        if (!\array_key_exists($algorithm, self::SIGN_ALGORITHMS)) {
            throw new \InvalidArgumentException(\sprintf('Unsupported JWT algorithm "%s".', $algorithm));
        }

        $signerClass = self::SIGN_ALGORITHMS[$algorithm];
        $signer = is_a($signerClass, Signer\Ecdsa::class, true) && method_exists(Signer\Ecdsa::class, 'create')
            ? $signerClass::create()
            : new $signerClass();

        $this->configuration = Configuration::forSymmetricSigner($signer, InMemory::plainText($secret));
        $this->jwtLifetime = $jwtLifetime ?? 3600;
    }

    public function create(?array $subscribe = [], ?array $publish = [], array $additionalClaims = []): string
    {
        $builder = $this->configuration->builder()
            ->withHeader('typ', 'at+jwt')
            ->issuedBy($this->issuer)
            ->permittedFor($this->audience)
            ->issuedAt(new \DateTimeImmutable())
            ->identifiedBy(bin2hex(random_bytes(16)))
        ;

        $details = $this->buildAuthorizationDetails($subscribe, $publish);
        if ([] !== $details) {
            $builder = $builder->withClaim('authorization_details', $details);
        }

        if (!\array_key_exists(RegisteredClaims::EXPIRATION_TIME, $additionalClaims)) {
            $additionalClaims[RegisteredClaims::EXPIRATION_TIME] = new \DateTimeImmutable(\sprintf('+%d seconds', $this->jwtLifetime));
        }

        foreach ($additionalClaims as $name => $value) {
            switch ($name) {
                case RegisteredClaims::AUDIENCE:
                    $builder = $builder->permittedFor(...(array) $value);
                    break;
                case RegisteredClaims::EXPIRATION_TIME:
                    if (null !== $value) {
                        $builder = $builder->expiresAt($value);
                    }
                    break;
                case RegisteredClaims::ISSUED_AT:
                    $builder = $builder->issuedAt($value);
                    break;
                case RegisteredClaims::ISSUER:
                    $builder = $builder->issuedBy($value);
                    break;
                case RegisteredClaims::SUBJECT:
                    $builder = $builder->relatedTo($value);
                    break;
                case RegisteredClaims::ID:
                    $builder = $builder->identifiedBy($value);
                    break;
                case RegisteredClaims::NOT_BEFORE:
                    $builder = $builder->canOnlyBeUsedAfter($value);
                    break;
                default:
                    $builder = $builder->withClaim($name, $value);
            }
        }

        return $builder
            ->getToken($this->configuration->signer(), $this->configuration->signingKey())
            ->toString()
        ;
    }

    /**
     * @param string[]|null $subscribe
     * @param string[]|null $publish
     *
     * @return list<array{type: string, actions: list<string>, topics: list<array{match: string}>}>
     */
    private function buildAuthorizationDetails(?array $subscribe, ?array $publish): array
    {
        $details = [];

        foreach (['subscribe' => $subscribe, 'publish' => $publish] as $action => $topics) {
            if (null === $topics || [] === $topics) {
                continue;
            }

            $details[] = [
                'type' => self::AUTHORIZATION_DETAIL_TYPE,
                'actions' => [$action],
                'topics' => array_map(
                    static fn (string $topic): array => ['match' => $topic],
                    array_values($topics),
                ),
            ];
        }

        return $details;
    }
}
