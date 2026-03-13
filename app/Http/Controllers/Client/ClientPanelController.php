<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPanelController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('client.panel', [
            'user' => $request->user(),
        ]);
    }
}
