<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Database\Type;

use Cake\Database\DriverInterface;
use MikeWeb\CakeText\Exception\UuidParsableException;
use MikeWeb\CakeText\Utility\Uuid;
use Ramsey\Uuid\UuidInterface;
use Exception;

trait UuidTypeTrait
{
    public function newId(): string
    {
        return Uuid::generate();
    }

    public function toPHP($value, DriverInterface $driver): UuidInterface
    {
        return Uuid::parse((string)parent::toPHP($value, $driver));
    }
}
