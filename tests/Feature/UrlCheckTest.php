<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Mockery;
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
