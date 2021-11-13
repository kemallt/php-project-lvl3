<?php

namespace Tests\Feature;

use Carbon\Carbon;
use DiDom\Document;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use DatabaseMigrations;

    protected $urls;

    protected function setUp(): void
    {
        $this->urls = [
            ['name' => 'https://google.com', 'created_at' => Carbon::now()],
            ['name' => 'https://yandex.ru', 'created_at' => Carbon::now()]
        ];
        parent::setUp();
        DB::table('urls')->insert($this->urls);
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
        $response->assertSee($this->urls[0]['name']);
        $response->assertSee($this->urls[1]['name']);
    }

    public function testShow()
    {
        $response = $this->get(route('urls.show', ['url' => 1]));
        $response->assertSee($this->urls[0]['name']);
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
        $url = $this->urls[0];
        $response = $this->post(route('urls.store', ['url' => $url]));
        $this->assertEquals('Страница уже существует', session('status'));
        $response->assertRedirect(route('urls.show', ['url' => 1]));
    }

    public function testCheck()
    {
        $url = $this->urls[0];
        $url['id'] = 1;
        $statusCode = 200;
        $html = file_get_contents(__DIR__ . '/../fixtures/test.html');
        Http::fake([
            $url['name'] => Http::response($html, 200)
        ]);
        $mockHtmlReturn = function ($retText) {
            $parsingStub = new class($retText) {
                private $retText;
                public function __construct($retText)
                {
                    $this->retText = $retText;
                }
                public function attr() {
                    return $this->retText;
                }
                public function text() {
                    return $this->retText;
                }
            };
            return $parsingStub;
        };
        $documentMock = Mockery::mock('overload:\DiDom\Document');
        $documentMock
            ->shouldReceive('first')
            ->with('h1')
            ->once()
            ->andReturnUsing(fn () => $mockHtmlReturn('h1 mock text'));
        $documentMock
            ->shouldReceive('first')
            ->with('title')
            ->once()
            ->andReturnUsing(fn () => $mockHtmlReturn('title mock text'));
        $documentMock
            ->shouldReceive('first')
            ->with('meta[name=description]')
            ->once()
            ->andReturnUsing(fn () => $mockHtmlReturn('description mock text'));
        $response = $this->post(route('urls.checks', ['url' => $url['id']]));
        $this->assertDatabaseHas('url_checks', ['url_id' => $url['id'], 'status_code' => $statusCode]);
        $response->assertRedirect(route('urls.show', ['url' => $url['id']]));
        $this->followRedirects($response)->assertStatus(200)->assertSee($statusCode);
    }
}
