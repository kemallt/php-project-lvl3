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
        $foundedUrl = DB::table('urls')->where('name', '=', $validatedData['url']['name'])->get();
        if ($foundedUrl->count() !== 0) {
            return redirect()
                ->route('urls.show', ['url' => $foundedUrl->first()->id])
                ->withStatus('Страница уже существует');
        }
        try {
            $url = $validatedData['url'];
            $url['created_at'] = Carbon::now();
            $id = DB::table('urls')->insertGetId($url);
            return redirect()->route('urls.show', ['url' => $id])->withSuccess('Сайт успешно добавлен');
        } catch (\Exception $exception) {
            return redirect()
                ->route('urls.create')
                ->withInput()
                ->with(['error' => true, 'message' => $exception->getMessage()]);
        }
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
        return view('show', ['url' => $url]);
    }

//    /**
//     * Show the form for editing the specified resource.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function edit($id)
//    {
//        $url = DB::table('urls')->find($id);
//        return view('edit', ['url' => $url]);
//    }

//    /**
//     * Update the specified resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function update(Request $request, $id)
//    {
//        //
//    }
//
//    /**
//     * Remove the specified resource from storage.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function destroy($id)
//    {
//        //
//    }
}
