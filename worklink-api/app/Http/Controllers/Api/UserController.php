<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;

class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/usuarios",
     *     summary="Get all users",
     *     operationId="getAllUsers",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     security={}
     * )
     */
    public function getAllUsers()
    {
        $users = Usuario::get()->toJson(JSON_PRETTY_PRINT);
        return response($users, 200);
    }
}
