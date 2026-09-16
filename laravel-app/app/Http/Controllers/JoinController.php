<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JoinController extends Controller
{
    public function show()
    {
        return redirect()->route('beyond.membership');
    }

    public function store(Request $request)
    {
        return redirect()->route('beyond.membership');
    }
}
