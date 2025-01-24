<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/home",
 *     summary="Home data",
 *     tags={"Home"},
 *
 *     @OA\Parameter(
 *     name="name",
 *     in="query",
 *     description="Provide your name",
 *     required=true
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *
 *     @OA\MediaType(
 *         mediaType="application/json"
 *     )
 * ),
 *     )
 */
class HomeController extends Controller
{
    public function index(Request $request)
    {
        $name = $request->input('name');

        return response()->json([
            'name' => $name,
            'message' => 'Hello '.$name,
        ]);

    }
}
