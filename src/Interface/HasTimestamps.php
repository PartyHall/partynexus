<?php

namespace App\Interface;

interface HasTimestamps
{
    public const string API_GET = 'api:timestamps:get';

    public function getCreatedAt(): \DateTimeImmutable;

    public function setCreatedAt(\DateTimeImmutable $createdAt): void;

    public function getUpdatedAt(): ?\DateTimeImmutable;

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void;
}
