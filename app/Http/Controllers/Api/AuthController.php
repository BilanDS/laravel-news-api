<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/register',
        summary: 'Реєстрація нового користувача',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Ivan Ivanov'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Успішна реєстрація')]
    #[OA\Response(response: 422, description: 'Помилка валідації')]
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Успішна реєстрація',
            'user' => new UserResource($user),
            'token' => $token
        ], 201);
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Вхід в систему (отримання токена)',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'test@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Успішний вхід',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'token', type: 'string', example: '1|abc123token...')
            ]
        )
    )]
    #[OA\Response(response: 422, description: 'Неправильні дані')]
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неправильний email або пароль.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Успішний вхід',
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Вихід (деактивація токена)',
        security: [['bearerAuth' => []]],
        tags: ['Auth']
    )]
    #[OA\Response(response: 200, description: 'Успішний вихід')]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Успішний вихід'
        ]);
    }
}