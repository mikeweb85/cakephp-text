<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Database\Type;

use Cake\Database\Type\UuidType as CakeUuidType;

class UuidType extends CakeUuidType
{
    use UuidTypeTrait;
}
