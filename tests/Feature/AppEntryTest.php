<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AppEntryTest extends TestCase
{
    public function testAppEntry(): void
    {
        $response = $this->get(route('urls.appEntry'));
        $response->assertSee('Проверить');
        $response->assertOk();
    }
}
