<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $urls = [
            ['name' => 'https://google.com', 'created_at' => Carbon::now()],
            ['name' => 'https://yandex.ru', 'created_at' => Carbon::now()]
        ];
        DB::table('urls')->insert($urls);
    }

    public function testCreate()
    {
        $response = $this->get(route('urls.create'));
        $response->assertStatus(200);
        $response->assertSee('Проверить');
    }

    public function testIndex()
    {
        $response = $this->get(route('urls.index'));
        $response->assertStatus(200);
        $response->assertSee('https://google.com');
        $response->assertSee('https://yandex.ru');
    }

    public function testShow()
    {
        $response = $this->get(route('urls.show', ['url' => 1]));
        $response->assertSee('https://google.com');
        $response->assertSee('Имя');
    }

    public function testStore()
    {
        $url = ['name' => 'https://mail.ru'];
        $response = $this->post(route('urls.store', ['url' => $url]));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('urls', $url);
    }

    public function testStoreError()
    {
        $url = ['name' => ''];
        $response = $this->post(route('urls.store', ['url' => $url]));
        $response->assertSessionHasErrors(['url.name']);
        $response->assertRedirect();
    }

    public function testStoreDouble()
    {
        $url = ['name' => 'https://google.com'];
        $response = $this->post(route('urls.store', ['url' => $url]));
        $status = session('status');
        $this->assertEquals('Страница уже существует', $status);
        $response->assertRedirect();
    }
}
