<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

abstract readonly class AbstractPermissionsFilter implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        protected Security $security,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $param = 'true' === \strtolower($context['request']->query->get($this->getParamName()) ?? 'false');

        $this->addWhere($queryBuilder, $resourceClass, $param);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->addWhere($queryBuilder, $resourceClass, false);
    }

    protected function isCorrectClass(string $resourceClass): bool
    {
        if ($resourceClass !== $this->getClassName() || null === ($user = $this->security->getUser())) {
            return false;
        }

        $classNames = $this->getUserClassNames();
        if (!\is_array($classNames)) {
            $classNames = [$classNames];
        }

        $isCorrectClass = false;
        foreach ($classNames as $className) {
            // Not sure why it makes phpstan angry but there seems to have
            // a few issues on phpstan's github relating is_subclass_of
            // so meh
            // @phpstan-ignore-next-line
            if (\get_class($user) === $className || \is_subclass_of($user, $className)) {
                $isCorrectClass = true;
                break;
            }
        }

        return $isCorrectClass;
    }

    protected function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $mine): void
    {
        if (!$this->isCorrectClass($resourceClass)) {
            return;
        }

        $isAdmin = $this->security->isGranted('ROLE_ADMIN');
        $rootAlias = $queryBuilder->getRootAliases()[0];
        if (!$isAdmin || $mine) {
            $this->addCondition($queryBuilder, $rootAlias);
        }
    }

    abstract protected function getParamName(): string;

    /**
     * @return class-string
     */
    abstract protected function getClassName(): string;

    /**
     * @return class-string|array<class-string>
     */
    protected function getUserClassNames(): string|array
    {
        return User::class;
    }

    protected function addCondition(
        QueryBuilder $queryBuilder,
        string $rootAlias,
    ): void {
        $queryBuilder->andWhere(\sprintf('%s.owner = :current_user', $rootAlias));
        $queryBuilder->setParameter('current_user', $this->security->getUser());
    }
}
