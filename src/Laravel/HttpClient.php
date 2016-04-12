<?php

namespace OpiloClient\Laravel;

use Illuminate\Support\Facades\Facade;
use OpiloClient\V2\OpiloServiceProvider;

class HttpClient extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return OpiloServiceProvider::class;
    }
}
