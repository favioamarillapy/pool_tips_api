<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($success, $message, $result, $status){
        $response = collect([
            // 'success'    => $success,
            'message'   => $message,
        ]);

        // if (!collect($result)->has('current_page')) { $result = ['data' => $result]; }

        $combined = $response->union($result);

        return response()->json($combined, $status);
    }
}
