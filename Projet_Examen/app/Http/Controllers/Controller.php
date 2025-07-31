<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Portail Scolaire API",
 *     version="1.0.0",
 *     description="Documentation de l'API du portail scolaire",
 *     @OA\Contact(
 *         email="diabydaj@gmail.com"
 *     )
 * )
 *
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
