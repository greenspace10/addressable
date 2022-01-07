<?php

namespace Grnspc\Addresses\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Address
 * @package Grnspc\Addresses\Facades
 */
class Address extends Facade
{
    /** @inheritdoc */
    protected static function getFacadeAccessor(): string
    {
        return 'grnspc.addresses.address';
    }
}
