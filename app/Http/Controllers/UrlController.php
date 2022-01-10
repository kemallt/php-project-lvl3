<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DiDom\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UrlController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $urls = DB::table('urls')
            ->get();
        $checks = DB::table('url_checks')
            ->orderByDesc('created_at')
            ->get()
            ->unique('url_id')
            ->mapWithKeys(function ($check) {
                return [$check->url_id => ['last_check' => $check->created_at, 'status_code' => $check->status_code]];
            });
        return view('index', ['urls' => $urls, 'checks' => $checks]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'url.name' => 'required|max:255|url'
        ]);
        $url = $this->prepareUrl($validatedData['url']);
        $foundUrl = DB::table('urls')->where('name', '=', $url['name'])->first();
        if ($foundUrl !== null) {
            return redirect()
                ->route('urls.show', ['url' => $foundUrl->id])
                ->withStatus('Страница уже существует');
        }
        $id = DB::table('urls')->insertGetId($url);
        return redirect()->route('urls.show', ['url' => $id])->withSuccess('Страница успешно добавлена');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $url = DB::table('urls')->find($id);
        $checks = DB::table('url_checks')
            ->where('url_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($check) {
                $check->h1 = strlen($check->h1) > 100 ? substr($check->h1, 0, 50) . '...' : $check->h1;
                $check->title = strlen($check->title) > 100 ? substr($check->title, 0, 100) . '...' : $check->title;
                $check->description = strlen($check->description) > 100 ? substr($check->description, 0, 50) . '...' : $check->description;
                return $check;
            });
        $lastCheck = $checks->count() > 0 ? $checks[0]->created_at : '';
        return view('show', ['url' => $url, 'lastCheck' => $lastCheck, 'checks' => $checks]);
    }

    public function prepareUrl(array $url): array
    {
        $url['created_at'] = Carbon::now();
        $urlScheme = (string)parse_url($url['name'], PHP_URL_SCHEME);
        $urlName = (string)parse_url($url['name'], PHP_URL_HOST);
        $url['name'] = mb_strtolower($urlScheme) . "://" . mb_strtolower($urlName);
        return $url;
    }
}
