<?php

namespace App\DataFixtures;

class ReflectionUtils
{
    public static function setId(mixed $obj, mixed $id): void {
        $rc = new \ReflectionClass($obj);
        $rc->getProperty('id')->setValue($obj, $id);
    }
}
