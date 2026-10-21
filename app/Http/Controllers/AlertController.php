<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function markAsRead(Alert $alert): JsonResponse
    {
        $this->authorizeAlert($alert);

        if (is_null($alert->read_at)) {
            $alert->forceFill(['read_at' => now()])->save();
        }

        return response()->json([
            'status' => 'ok',
            'message' => __('messages.Alert marked as read.'),
        ]);
    }

    public function destroy(Alert $alert): JsonResponse
    {
        $this->authorizeAlert($alert);

        $alert->forceFill([
            'read_at' => now(),
        ])->save();

        return response()->json([
            'status' => 'ok',
            'message' => __('messages.Alert dismissed.'),
        ]);
    }

    protected function authorizeAlert(Alert $alert): void
    {
        $user = auth()->user();

        abort_if(
            !$user || $user->entreprise_id !== $alert->entreprise_id,
            403,
            'You are not authorized to access this alert.'
        );
    }
}

