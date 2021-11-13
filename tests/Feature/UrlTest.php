<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use DatabaseMigrations;

    protected $urls, $url, $statusCode;

    protected function setUp(): void
    {
        $this->urls = [
            ['name' => 'https://google.com', 'created_at' => Carbon::now()],
            ['name' => 'https://yandex.ru', 'created_at' => Carbon::now()]
        ];
        $this->url = $this->urls[0];
        $this->url['id'] = 1;
        $this->statusCode = 200;
        parent::setUp();
        DB::table('urls')->insert($this->urls);
    }

    public function testCreate(): void
    {
        $response = $this->get(route('urls.create'));
        $response->assertStatus($this->statusCode);
        $response->assertSee('Проверить');
    }

    public function testIndex(): void
    {
        $response = $this->get(route('urls.index'));
        $response->assertStatus($this->statusCode);
        $response->assertSee($this->urls[0]['name']);
        $response->assertSee($this->urls[1]['name']);
    }

    public function testShow(): void
    {
        $response = $this->get(route('urls.show', ['url' => $this->url['id']]));
        $response->assertSee($this->url['name']);
        $response->assertSee('Имя');
    }

    public function testStore(): void
    {
        $url = ['name' => 'https://mail.ru'];
        $response = $this->post(route('urls.store', ['url' => $url]));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('urls', $url);
    }

    public function testStoreError(): void
    {
        $url = ['name' => ''];
        $response = $this->post(route('urls.store', ['url' => $url]));
        $response->assertSessionHasErrors(['url.name']);
        $response->assertRedirect();
    }

    public function testStoreDouble(): void
    {
        $response = $this->post(route('urls.store', ['url' => $this->url]));
        $this->assertEquals('Страница уже существует', session('status'));
        $response->assertRedirect(route('urls.show', ['url' => $this->url['id']]));
    }
}
