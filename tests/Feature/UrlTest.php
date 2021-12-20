<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UrlTest extends TestCase
{
    protected array $urls;
    protected array $url;

    protected function setUp(): void
    {
        $this->urls = [
            ['name' => 'https://google.com', 'created_at' => Carbon::now()],
            ['name' => 'https://yandex.ru', 'created_at' => Carbon::now()]
        ];
        $this->url = $this->urls[0];
        $this->url['id'] = 1;
        parent::setUp();
        DB::table('urls')->insert($this->urls);
    }

    public function testIndex(): void
    {
        $response = $this->get(route('urls.index'));
        $response->assertSee($this->urls[0]['name']);
        $response->assertSee($this->urls[1]['name']);
        $response->assertOk();
    }

    public function testShow(): void
    {
        $response = $this->get(route('urls.show', ['url' => $this->url['id']]));
        $response->assertSee($this->url['name']);
        $response->assertViewIs('show');
        $response->assertViewHas('url');
        $response->assertViewHas('checks');
        $response->assertOk();
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
