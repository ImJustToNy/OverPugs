<?php

namespace OverwatchLounge\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use OverwatchLounge\User;

class UserController extends Controller
{
    public function changeRegion(Request $request)
    {
        $this->validate($request, [
            'region' => [
                'required',
                Rule::in(['us', 'eu', 'kr']),
            ],
        ]);

        $user = Auth::user();

        $user->prefered_region = $request->region;

        $user->save();

        return response()->json([
            'status' => 'ok',
        ]);
    }
}