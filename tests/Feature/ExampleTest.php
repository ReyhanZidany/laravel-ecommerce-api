<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_api_is_reachable(): void
    {
        $this->postJson('/api/login', [])->assertUnprocessable();
    }
}
