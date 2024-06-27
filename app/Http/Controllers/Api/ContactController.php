<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function send(Request $request) {
        $request->validate([
            "name" => "required",
            "email" => "required|email",
            "subject" => "required",
            "text" => "required|min:50",
        ]);

        // Send email
    }
}
