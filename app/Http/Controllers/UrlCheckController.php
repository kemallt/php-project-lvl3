<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DiDom\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UrlCheckController extends Controller
{
    /**
     * Check the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function check($id)
    {
        $url = DB::table('urls')->find($id);
        try {
            $checkResult = $this->performCheck($url);
        } catch (\Exception $exception) {
            return redirect()
                ->back()
                ->withErrors(['resourceCheck' => $exception->getMessage()]);
        }
        try {
            DB::table('url_checks')->insert($checkResult);
        } catch (\Exception $exception) {
            return redirect()
                ->back()
                ->withErrors(['message' => $exception->getMessage()]);
        }
        return redirect()->route('urls.show', ['url' => $id])->withSuccess('Страница успешно проверена');
    }

    public function checkStatus(string $url): int
    {
        $response = Http::get($url);
        return $response->status();
    }

    public function getHtmlData(object $document)
    {
        $h1 = optional($document->first('h1'))->text();
        $title = optional($document->first('title'))->text();
        $description = optional($document->first('meta[name=description]'))->attr('content');
        return [
            'h1' => $h1,
            'title' => $title,
            'description' => $description
        ];
    }

    public function performCheck(object $url): array
    {
        $document = new Document($url->name, true);
        return array_merge(
            [
                'url_id' => $url->id,
                'status_code' => $this->checkStatus($url->name),
                'created_at' => Carbon::now()
            ],
            $this->getHtmlData($document)
        );
    }
}
