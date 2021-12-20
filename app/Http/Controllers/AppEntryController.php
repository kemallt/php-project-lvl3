<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppEntryController extends Controller
{
    public function showAppEntry()
    {
        return view('create');
    }
}
