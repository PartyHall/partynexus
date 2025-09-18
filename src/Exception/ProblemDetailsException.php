<?php

namespace App\Exception;

use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;

class ProblemDetailsException extends \Exception implements ProblemExceptionInterface
{
    public function __construct(
        private readonly int $status,
        private readonly string $title,
        private readonly string $details,
    ) {
        parent::__construct($title, $status);
    }

    public function getType(): string
    {
        return 'partynexus/errors/generic';
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getDetail(): ?string
    {
        return $this->details;
    }

    public function getInstance(): ?string
    {
        // Filled by the listener
        return '';
    }
}
