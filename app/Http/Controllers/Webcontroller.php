<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Webcontroller extends Controller
{
    public function index()
    {
        $data = [
                'title' => "Pemetaan",
            ];
        return view('v_web', $data);
    }
}
