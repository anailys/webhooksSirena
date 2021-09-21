<?php

namespace App;

use Illuminate\Support\Facades\Facade as FacadesFacade;

class SirenaFacade extends FacadesFacade
{
    protected static function getFacadeAccessor()
    {
        return 'Sirena';
    }
}
