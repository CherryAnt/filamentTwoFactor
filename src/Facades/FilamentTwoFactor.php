<?php

namespace CherryAnt\FilamentTwoFactor\Facades;

use Illuminate\Support\Facades\Facade;

class FilamentTwoFactor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \CherryAnt\FilamentTwoFactor\FilamentTwoFactor::class;
    }
}
