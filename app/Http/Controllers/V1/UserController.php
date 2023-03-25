<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User API Endpoints"
 * )
 */
class UserController extends Controller
{

    /**
     * Login User With Provider Credentials.
     */
    public function login(UserRequest $request)
    {
        //
    }

    /**
     * Return Authenticated user Instance.
     */
    public function user()
    {
        //
    }

    /**
     *
     * @OA\Put(
     *     path="api/v1/user/create",
     *     summary="Create a User account",
     *     tags={"User"},
     *     @OA\RequestBody (
     *          required=true,
     *          name="first_name",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     *
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $input = $request->validated();

        dd($input);
        try {
            $data = User::create($input);

//            $data

            $responseData = array_merge($input->toArray(), [

            ]);
            return response()->json([
                'status' => true,
            ], 500);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return response()->json([], 500);
        }
    }

    /**
     * Return Auth User Orders.
     */
    public function orders(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
