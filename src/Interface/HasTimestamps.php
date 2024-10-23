<?php

namespace App\Interface;

interface HasTimestamps
{
    function getCreatedAt(): \DateTimeImmutable;
    function setCreatedAt(\DateTimeImmutable $createdAt): void;

    function getUpdatedAt(): ?\DateTimeImmutable;
    function setUpdatedAt(?\DateTimeImmutable $updatedAt): void;
}
