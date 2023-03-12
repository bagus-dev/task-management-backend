<?php
    
namespace App\Http\Library;

use Illuminate\Http\JsonResponse;

trait ApiHelpers
{
    protected function isAdmin($user): bool
    {
        if(!empty($user)) {
            return $user->tokenCan('admin');
        }

        return false;
    }

    protected function isUser($user): bool
    {
        if(!empty($user)) {
            return $user->tokenCan('user');
        }

        return false;
    }

    protected function onSuccess($data, string $message = '', int $code = 200, $other = ''): JsonResponse
    {
        return response()->json([
            'status' => $code,
            'message' => $message,
            'data' => $data,
            'other' => $other
        ], $code);
    }

    protected function onError(int $code, string $message = ''): JsonResponse
    {
        return response()->json([
            'status' => $code,
            'message' => $message
        ], $code);
    }

    protected function taskValidationRules(): array
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d'
        ];
    }

    protected function itemValidationRules(): array
    {
        return [
            'title' => 'required|string',
            'priority' => 'required|numeric'
        ];
    }
}