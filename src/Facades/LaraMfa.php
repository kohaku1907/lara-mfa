<?php

namespace Kohaku1907\LaraMfa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kohaku1907\LaraMfa\LaraMfa
 */
class LaraMfa extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Kohaku1907\LaraMfa\LaraMfa::class;
    }
}
