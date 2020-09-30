<?php

namespace Ag84ark\LaravelMinimalTranslation\Tests;

use Orchestra\Testbench\TestCase;
use Ag84ark\LaravelMinimalTranslation\LaravelMinimalTranslationServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelMinimalTranslationServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
