<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UrlController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $urls = DB::table('urls')->get();
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
        $check = [
            'url_id' => $id,
            'status_code' => 0,
            'h1' => '',
            'title' => '',
            'description' => '',
            'created_at' => Carbon::now()
        ];
        try {
            DB::table('url_checks')->insert($check);
        } catch (\Exception $exception) {
            return redirect()
                ->route('urls.create')
                ->withInput()
                ->with(['error' => true, 'message' => $exception->getMessage()]);
        }
        return redirect()->route('urls.show', ['url' => $id])->withSuccess('Страница успешно проверена');
    }

    public function prepareUrl($url)
    {
        $url['created_at'] = Carbon::now();
        $urlScheme = parse_url($url['name'], PHP_URL_SCHEME);
        $urlName = parse_url($url['name'], PHP_URL_HOST);
        $url['name'] = mb_strtolower($urlScheme) . "://" . mb_strtolower($urlName);
        return $url;
    }
}
