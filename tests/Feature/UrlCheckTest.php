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

    protected array $urls;
    protected array $url;
    protected int $statusCode;

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

    public function testCheckFailed(): void
    {
        $response = $this->post(route('urls.checks', ['url' => 11]));
        $response->assertSessionHasErrors(['resourceCheck' => "could not find url by id"]);
        $response->assertRedirect();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCheck(string $html, string $seeText): void
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
    }

    public function dataProvider(): array
    {
        return [
            'testFilled' => [file_get_contents(__DIR__ . '/../fixtures/test.html'), 'h1 mock text'],
            'testNulls' => [file_get_contents(__DIR__ . '/../fixtures/testnull.html'), 'body']
        ];
    }
}
