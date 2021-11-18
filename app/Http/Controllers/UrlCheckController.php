<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DiDom\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UrlCheckController extends Controller
{
    /**
     * Check the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function check($id)
    {
        $url = DB::table('urls')->find($id);
        if ($url === null) {
            return redirect()
                ->back()
                ->withErrors(['resourceCheck' => "could not find url by id"]);
        }
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

    public function performCheck(mixed $url): array
    {
        $response = Http::get($url->name);
        $document = new Document($response->body());
        return [
            'url_id' => $url->id,
            'status_code' => $response->status(),
            'created_at' => Carbon::now(),
            'h1' => optional($document->first('h1'))->text(),
            'title' => optional($document->first('title'))->text(),
            'description' => optional($document->first('meta[name=description]'))->attr('content')
        ];
    }
}
