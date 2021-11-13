<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UrlController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $urls = DB::table('urls')->get();
        $urls = DB::table('urls')
            ->leftJoin('url_checks', 'urls.id', '=', 'url_checks.url_id')
            ->select(
                'urls.id as id',
                'urls.name as name',
                'urls.created_at as created_at',
                DB::raw('max(url_checks.created_at) as last_check')
            )
            ->groupBy('urls.id', 'urls.name', 'urls.created_at')
            ->get();
        return view('index', ['urls' => $urls]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'url.name' => 'required|max:255|url'
        ]);
        $url = $this->prepareUrl($validatedData['url']);
        $foundedUrl = DB::table('urls')->where('name', '=', $url['name'])->get();
        if ($foundedUrl->count() !== 0) {
            return redirect()
                ->route('urls.show', ['url' => $foundedUrl->first()->id])
                ->withStatus('Страница уже существует');
        }
        try {
            $id = DB::table('urls')->insertGetId($url);
        } catch (\Exception $exception) {
            return redirect()
                ->route('urls.create')
                ->withInput()
                ->with(['error' => true, 'message' => $exception->getMessage()]);
        }
        return redirect()->route('urls.show', ['url' => $id])->withSuccess('Страница успешно добавлен');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $url = DB::table('urls')->find($id);
        $checks = DB::table('url_checks')->where('url_id', $id)->orderBy('created_at', 'desc')->get();
        $url->last_check = $checks->count() > 0 ? $checks[0]->created_at : '';
        return view('show', ['url' => $url, 'checks' => $checks]);
    }

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

    public function performCheck(object $url): array
    {
        $response = Http::get($url->name);
        $check = [
            'url_id' => $url->id,
            'status_code' => $response->status(),
            'h1' => '',
            'title' => '',
            'description' => '',
            'created_at' => Carbon::now()
        ];
        return $check;
    }

    public function prepareUrl(array $url): array
    {
        $url['created_at'] = Carbon::now();
        $urlScheme = parse_url($url['name'], PHP_URL_SCHEME);
        $urlName = parse_url($url['name'], PHP_URL_HOST);
        $url['name'] = mb_strtolower($urlScheme) . "://" . mb_strtolower($urlName);
        return $url;
    }
}
