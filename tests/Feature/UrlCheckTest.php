<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UrlCheckTest extends TestCase
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

    /**
     * @dataProvider dataProvider
     */
    public function testCheck($html, $seeText): void
    {
        Http::fake([
            $this->url['name'] => Http::response($html, $this->statusCode)
        ]);
        $response = $this->post(route('urls.checks', ['url' => $this->url['id']]));
        $this->assertDatabaseHas('url_checks', [
            'url_id' => $this->url['id'],
            'status_code' => $this->statusCode
        ]);
        $response->assertRedirect(route('urls.show', ['url' => $this->url['id']]));
        $this->followRedirects($response)->assertStatus($this->statusCode)->assertSee($this->statusCode)->assertSee($seeText);
    }

    public function dataProvider()
    {
        return [
            'testFilled' => [file_get_contents(__DIR__ . '/../fixtures/test.html'), 'h1 mock text'],
            'testNulls' => [file_get_contents(__DIR__ . '/../fixtures/testnull.html'), 'body']
        ];
    }
}
