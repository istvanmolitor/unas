<?php

namespace Molitor\Unas\Tests\Feature;

use Molitor\Unas\Providers\UnasServiceProvider;
use Tests\TestCase;

class PackageSmokeTest extends TestCase
{
    public function test_service_provider_is_loaded(): void
    {
        $this->assertTrue(class_exists(UnasServiceProvider::class));
        $this->assertTrue($this->app->providerIsLoaded(UnasServiceProvider::class));
    }
}

