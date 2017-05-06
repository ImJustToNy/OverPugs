<?php

namespace OverPugs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OverPugs\User;

class UserController extends Controller
{
    /**
     * Push new prefered_region to user's database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeRegion(Request $request)
    {
        $this->validate($request, [
            'region' => ['required', 'in:us,eu,kr'],
        ]);

        $user = Auth::user();

        $user->prefered_region = $request->region;

        $user->save();

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
