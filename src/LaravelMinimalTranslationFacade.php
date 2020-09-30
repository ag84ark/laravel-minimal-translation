<?php

namespace Ag84ark\LaravelMinimalTranslation;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ag84ark\LaravelMinimalTranslation\Skeleton\SkeletonClass
 */
class LaravelMinimalTranslationFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-minimal-translation';
    }
}
