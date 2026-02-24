<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    #[OA\Get(
        path: '/api/profile',
        summary: 'Отримати дані свого профілю',
        security: [['bearerAuth' => []]],
        tags: ['Profile']
    )]
    #[OA\Response(
        response: 200,
        description: 'Успішна відповідь',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Test User'),
                        new OA\Property(property: 'email', type: 'string', example: 'test@example.com'),
                        new OA\Property(property: 'email_verified_at', type: 'string', nullable: true),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                    ]
                )
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Неавторизовано')]
    public function show(Request $request)
    {
        return response()->json([
            'data' => $request->user()
        ]);
    }


    #[OA\Put(
        path: '/api/profile',
        summary: 'Оновити дані профілю',
        security: [['bearerAuth' => []]],
        tags: ['Profile']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Супер Адмін'),
                new OA\Property(property: 'email', type: 'string', example: 'new_email@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'newpassword123'),
                new OA\Property(property: 'password_confirmation', type: 'string', example: 'newpassword123'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Профіль успішно оновлено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Профіль успішно оновлено'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Супер Адмін'),
                        new OA\Property(property: 'email', type: 'string', example: 'new_email@example.com'),
                    ]
                )
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Неавторизовано')]
    #[OA\Response(response: 422, description: 'Помилка валідації')]
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Профіль успішно оновлено',
            'data' => $user
        ]);
    }
}
