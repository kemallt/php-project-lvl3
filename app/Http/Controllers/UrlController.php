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
            ->select(
                'id as id',
                'name as name',
                'created_at as created_at'
            )
            ->get();
        $lastChecks = DB::table('url_checks')
            ->select(
                'url_id',
                DB::raw('MAX(created_at) as last_check')
            )
            ->groupBy('url_id');
        $lastChecksWithStatuses = DB::table('url_checks')
            ->leftJoinSub($lastChecks, 'lastChecks', function ($join) {
                $join
                    ->on('lastChecks.url_id', '=', 'url_checks.url_id')
                    ->on('lastChecks.last_check', '=', 'url_checks.created_at');
            })
            ->select(
                'url_checks.url_id as url',
                'url_checks.status_code as status_code',
                'lastChecks.last_check as last_check'
            )
            ->get();
        $lastCheskByUrl = $lastChecksWithStatuses->reduce(
            function ($carry, $item) {
                $carry->put($item->url, $item);
                return $carry;
            },
            collect()
        );
        return view('index', ['urls' => $urls, 'checks' => $lastCheskByUrl]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('create');
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
        try {
            $id = DB::table('urls')->insertGetId($url);
        } catch (\Exception $exception) {
            return redirect()
                ->route('urls.blankCreate')
                ->withInput()
                ->with(['error' => true, 'message' => $exception->getMessage()]);
        }
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
        $checks = DB::table('url_checks')->where('url_id', $id)->orderBy('created_at', 'desc')->get();
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
