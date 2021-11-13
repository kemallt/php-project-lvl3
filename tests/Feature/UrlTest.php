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

    public function testCheck(): void
    {
        Http::fake([
            $this->url['name'] => Http::response('html body', $this->statusCode)
        ]);
        $mockHtmlReturn = $this->getMockHtmlReturn();
        $this->getDocumentMock($mockHtmlReturn);
        $response = $this->post(route('urls.checks', ['url' => $this->url['id']]));
        $this->assertDatabaseHas('url_checks', [
            'url_id' => $this->url['id'],
            'status_code' => $this->statusCode
        ]);
        $response->assertRedirect(route('urls.show', ['url' => $this->url['id']]));
        $this->followRedirects($response)->assertStatus($this->statusCode)->assertSee($this->statusCode)->assertSee('h1 mock text');
    }

    public function testCheckWithoutH1(): void
    {
        Http::fake([
            $this->url['name'] => Http::response('html body', $this->statusCode)
        ]);
        $this->getDocumentMockWithNulls();
        $response = $this->post(route('urls.checks', ['url' => $this->url['id']]));
        $this->assertDatabaseHas('url_checks', [
            'url_id' => $this->url['id'],
            'status_code' => $this->statusCode,
            'h1' => null,
            'title' => null,
            'description' => null
        ]);
        $this->followRedirects($response)->assertStatus($this->statusCode)->assertSee($this->statusCode);
    }

    private function getDocumentMockWithNulls(): void
    {
        $documentMock = Mockery::mock('overload:\DiDom\Document');
        $documentMock
            ->shouldReceive('first')
            ->with('h1')
            ->once()
            ->andReturn(null);
        $documentMock
            ->shouldReceive('first')
            ->with('title')
            ->once()
            ->andReturn(null);
        $documentMock
            ->shouldReceive('first')
            ->with('meta[name=description]')
            ->once()
            ->andReturn(null);
    }

    private function getDocumentMock($mockHtmlReturn): void
    {
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
    }

    private function getMockHtmlReturn(): callable
    {
        return function ($retText) {
            return new class ($retText) {
                private $retText;
                public function __construct($retText)
                {
                    $this->retText = $retText;
                }
                public function attr()
                {
                    return $this->retText;
                }
                public function text()
                {
                    return $this->retText;
                }
            };
        };
    }
}
