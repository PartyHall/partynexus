<?php

namespace App\State\Provider;

class ErrorProvider
{
}

/*
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ApiResource\Error;
use ApiPlatform\State\ProviderInterface;

class ErrorProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $context['request'];
        if (!$request || !($exception = $request->attributes->get('exception'))) {
            throw new \RuntimeException();
        }

*/
/* @var \ApiPlatform\Metadata\HttpOperation $operation */
/*$status = $operation->getStatus() ?? 500;

$error = Error::createFromException($exception, $status);

if ($status < 500) {

    $error->setDetail(
        str_replace(
            'something is not right',
            'les calculs ne sont pas bons',
            $exception->getMessage(),
        ),
    );
}

return $error;
    }
}
*/
