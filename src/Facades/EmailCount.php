<?php

namespace Messerli90\Hunterio\Facades;

use Illuminate\Support\Facades\Facade;

class EmailCount extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hunter-email-count';
    }
}
