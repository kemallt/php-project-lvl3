<?php

namespace Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use App\Http\Controllers\UrlController;

class PrepareUrlTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testPrepareUrl()
    {
        $url = [
            'name' => 'https://GooGle.COM'
        ];
        $expectedName = 'https://google.com';
        $urlController = new UrlController();
        $preparedUrl = $urlController->prepareUrl($url);
        $this->assertEquals($expectedName, $preparedUrl['name']);
        $this->assertInstanceOf(Carbon::class, $preparedUrl['created_at']);
    }
}
